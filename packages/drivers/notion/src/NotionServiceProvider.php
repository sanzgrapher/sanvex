<?php

namespace Sanvex\Drivers\Notion;

use Illuminate\Support\ServiceProvider;
use Sanvex\Core\ConnectorManager;

class NotionServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->bound(ConnectorManager::class)) {
            $this->app->make(ConnectorManager::class)->registerDriver(NotionDriver::class);
        }
    }
}
