<?php

namespace Sanvex\Core;

use Illuminate\Support\ServiceProvider;

class ConnectorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/sanvex.php', 'sanvex');

        $this->app->singleton(ConnectorManager::class, function ($app) {
            return ConnectorManager::make($app['config']['sanvex']);
        });

        $this->app->alias(ConnectorManager::class, 'sanvex.connector');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/sanvex.php' => config_path('sanvex.php'),
            ], 'sanvex-config');

            $this->publishes([
                __DIR__ . '/Database/migrations' => database_path('migrations'),
            ], 'sanvex-migrations');
        }

        $this->loadMigrationsFrom(__DIR__ . '/Database/migrations');
    }
}
