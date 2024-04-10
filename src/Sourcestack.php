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
        
        $this->setFolder();
        $this->store($file);
    }
    
    /**
     * {{ sourcestack:out }}
     *
     * Displays vitejs link/script tags for all stored sources.
     */
    public function out(): string
    {
        return $this->output();
    }
    
    /**
     * {{ sourcestack:vite }}
     *
     * Displays vitejs link/script tags for all stored sources.
     */
    public function vite(): string
    {
        return $this->output();
    }
    
    private function setFolder(?string $folder = null): void
    {
        if (! $this->folder) {
            $this->folder = $folder ?? config('sourcestack.base_dir');
        }
        
        $this->folder = Stringy::ensureRight($this->folder, '/');
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
    
    private function resolvePreset(string|array $preset): ?string
    {
        if (is_string($preset)) {
            return $preset;
        }
        
        $ext = array_key_exists('extension', $preset) ? 
            $preset['extension'] :
            null;
        
        if (array_key_exists('path', $preset)) {
            $this->extension = $ext;
            
            return $preset['path'];
        }
        
        return null;
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
        $sources = Blink::get(self::BLINK_KEY) ?? [];
        $file = $this->folder . $file;
        
        if ($this->extension) {
            $file .= ".{$this->extension}";
        }
    
        if (! in_array($file, $sources)) {
            $sources[] = $file;
            Blink::put(self::BLINK_KEY, $sources);
        }
    }
    
    private function output(): string
    {
        return collect(Blink::get(self::BLINK_KEY) ?? [])
            ->map(function ($file) {
                return $this->getViteSource($file);
            })
            ->filter()
            ->implode("\n");
    }
    
    /**
     * {{ sourcestack:[presetname] }}
     *
     * Stores a file within a preset folder.
     * @see config/sourcestack.php
     */
    public function __call($method, $args): void
    {
        $preset = explode(':', $this->tag, 2)[1];
        
        if (! array_key_exists($preset, config('sourcestack.presets'))) {
            // TODO: exception
            return;
        }
        
        $this->setFolder(
            $this->resolvePreset(config('sourcestack.presets')[$preset])
        );
        $this->index();
    }
}