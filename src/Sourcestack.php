<?php

namespace VV\SourceStack;

use Statamic\Facades\Blink;
use Statamic\Tags\Parameters;
use Statamic\Tags\Tags;
use Statamic\Tags\Vite;
use Stringy\StaticStringy as Stringy;

class Sourcestack extends Tags
{
    const BLINK_KEY = 'sourcestack-sources';
    
    protected static $aliases = ['srcstk'];
    
    protected $extension;
    protected $folder;
    protected $stack;
    
    public function __construct()
    {
        $this->folder = config('sourcestack.base_dir');
    }
    
    /**
     * {{ sourcestack file="[src]" }}.
     *
     * Where `src` is the path to a css/js asset file.
     * Stores the source with the supplied filename.
     */
    public function index()
    {
        if (! $file = $this->params->get(['file', 'src', 'source'])) {
            return;
        }
        
        $this->resolveStack();
        $this->store($file);
    }
    
    /**
     * {{ sourcestack:render }}
     *
     * Renders vitejs link/script tags for all stored sources.
     */
    public function render(): string
    {
        return $this->output();
    }
    
    /**
     * {{ sourcestack:vite }}
     *
     * Alias for render()
     */
    public function vite(): string
    {
        return $this->output();
    }
    
    private function getKey(): string
    {
        $key = self::BLINK_KEY;
            
        if ($this->stackExists()) {
            $key .= "-{$this->stack}";
        }
        
        return $key;
    }
    
    private function getViteSource(string $file): string|array
    {
        $vite = new Vite();
        
        $vite->method = 'index';
        $vite->tag = 'glide:index';
        $vite->isPair = false;
        $vite->context = $this->context;
        $vite->params = Parameters::make(
            ['src' => $file],
            $this->context,
        );
    
        return $vite->index();
    }
    
    private function output(): string
    {
        $this->resolveStack(true);
        
        return collect(Blink::get($this->getKey()) ?? [])
            ->map(function ($file) {
                return $this->getViteSource($file);
            })
            ->filter()
            ->implode("\n");
    }
    
    private function resolveStack(bool $skipSetup = false)
    {
        if (! $this->stack && $stack = $this->params->get('stack')) {
            $this->stack = $stack;
        }
        
        if ($skipSetup || ! $this->stackExists()) {
            return;
        }
        
        $stack = config('sourcestack.stacks')[$this->stack];
        
        if (is_string($stack)) {
            $this->setFolder($stack);
            
            return;
        }
        
        if (array_key_exists('extension', $stack)) {
            $this->extension = $stack['extension'];
        }
        
        if (array_key_exists('base_dir', $stack)) {
            $this->setFolder($stack['base_dir']);
        }
    }
    
    private function setFolder(?string $folder = null): void
    {
        $this->folder = $folder ?? config('sourcestack.base_dir');
        $this->folder = Stringy::ensureRight($this->folder, '/');
    }
    
    private function stackExists(): bool
    {   
        return 
            is_string($this->stack) && 
            ! empty($this->stack) && 
            array_key_exists($this->stack, config('sourcestack.stacks'));
    }
    
    private function store(string|array $file): void
    {
        if (is_string($file)) {
            $file = str_replace(' ', '', $file);
    
            if (! str_contains($file, ',')) {
                $this->storeSourceInBlink($file);
    
                return;
            }
    
            $file = explode(',', $file);
        }
    
        foreach ($file as $f) {
            $this->storeSourceInBlink($f);
        }
    }
    
    private function storeSourceInBlink(string $file)
    {
        $key = $this->getKey();
        
        $sources = Blink::get($key) ?? [];
        $file = $this->folder . $file;
        
        if ($this->extension) {
            $file .= ".{$this->extension}";
        }
    
        if (! in_array($file, $sources)) {
            $sources[] = $file;
            Blink::put($key, $sources);
        }
    }
    
    /**
     * {{ sourcestack:[stack] }}
     *
     * Stores a file within a dedicated stack.
     * @see config/sourcestack.php
     */
    public function __call($method, $args): void
    {
        $stack = explode(':', $this->tag, 2)[1];
        
        if (! array_key_exists($stack, config('sourcestack.stacks'))) {
            // TODO: exception
            return;
        }
        
        $this->stack = $stack;
        $this->index();
    }
}