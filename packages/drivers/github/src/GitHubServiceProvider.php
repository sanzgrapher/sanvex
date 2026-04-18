<?php

namespace Sanvex\Drivers\GitHub;

use Illuminate\Support\ServiceProvider;
use Sanvex\Core\SanvexManager;

class GitHubServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->bound(SanvexManager::class)) {
            $this->app->make(SanvexManager::class)->registerDriver(GitHubDriver::class);
        }
    }
}
