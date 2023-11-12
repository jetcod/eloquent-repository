<?php

namespace Jetcod\LaravelRepository;

use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

// use Jetcod\LaravelRepository\Console\RepositoryMakeCommand;

class ServiceProvider extends IlluminateServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        // if ($this->app->runningInConsole()) {
        //     $this->commands([
        //         RepositoryMakeCommand::class,
        //     ]);
        // }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
    }
}
