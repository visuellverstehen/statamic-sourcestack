<?php

namespace VV\SourceStack;

use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{
    protected $tags = [
        \VV\SourceStack\Sourcestack::class,
    ];

    public function bootAddon()
    {
        parent::boot();
        
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'sourcestack');
        
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('sourcestack.php'),
            ], 'sourcestack');
        }
        
        Statamic::afterInstalled(function ($command) {
            $command->call('vendor:publish', ['--tag' => 'sourcestack']);
        });
    }
}
