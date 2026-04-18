<?php

namespace Sanvex\Drivers\Linear;

use Illuminate\Support\ServiceProvider;
use Sanvex\Core\SanvexManager;

class LinearServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->bound(SanvexManager::class)) {
            $this->app->make(SanvexManager::class)->registerDriver(LinearDriver::class);
        }
    }
}
