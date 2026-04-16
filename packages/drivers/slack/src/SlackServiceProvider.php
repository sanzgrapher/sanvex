<?php

namespace Sanvex\Drivers\Slack;

use Illuminate\Support\ServiceProvider;
use Sanvex\Core\ConnectorManager;

class SlackServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->bound(ConnectorManager::class)) {
            $this->app->make(ConnectorManager::class)->registerDriver(SlackDriver::class);
        }
    }
}
