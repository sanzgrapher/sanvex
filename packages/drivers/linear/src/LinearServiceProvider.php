<?php

namespace Sanvex\Drivers\Linear;

use Illuminate\Support\ServiceProvider;
use Sanvex\Core\ConnectorManager;

class LinearServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->bound(ConnectorManager::class)) {
            $this->app->make(ConnectorManager::class)->registerDriver(LinearDriver::class);
        }
    }
}
