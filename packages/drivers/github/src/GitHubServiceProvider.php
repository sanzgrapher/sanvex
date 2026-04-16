<?php

namespace Sanvex\Drivers\GitHub;

use Illuminate\Support\ServiceProvider;
use Sanvex\Core\ConnectorManager;

class GitHubServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->bound(ConnectorManager::class)) {
            $this->app->make(ConnectorManager::class)->registerDriver(GitHubDriver::class);
        }
    }
}
