<?php

namespace Jaydeep\GuardDog;

use Illuminate\Support\ServiceProvider;
use Jaydeep\GuardDog\Commands\GuardDogScanCommand;

class GuardDogServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GuardDogScanCommand::class,
            ]);

            $this->publishes([
                __DIR__ . '/../config/guarddog.php' => config_path('guarddog.php'),
            ], 'guarddog-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/guarddog'),
            ], 'guarddog-views');
        }

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'guarddog');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/guarddog.php',
            'guarddog'
        );
    }
}
