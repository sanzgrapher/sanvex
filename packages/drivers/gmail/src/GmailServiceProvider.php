<?php

namespace Sanvex\Drivers\Gmail;

use Illuminate\Support\ServiceProvider;
use Sanvex\Core\SanvexManager;

class GmailServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->bound(SanvexManager::class)) {
            $this->app->make(SanvexManager::class)->registerDriver(GmailDriver::class);
        }
    }
}
