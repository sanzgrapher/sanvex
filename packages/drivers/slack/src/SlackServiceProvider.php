<?php

namespace Sanvex\Drivers\Slack;

use Illuminate\Support\ServiceProvider;
use Sanvex\Core\SanvexManager;

class SlackServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->bound(SanvexManager::class)) {
            $this->app->make(SanvexManager::class)->registerDriver(SlackDriver::class);
        }
    }
}
