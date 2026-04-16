<?php

namespace Sanvex\Drivers\Gmail;

use Illuminate\Support\ServiceProvider;
use Sanvex\Core\ConnectorManager;

class GmailServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->bound(ConnectorManager::class)) {
            $this->app->make(ConnectorManager::class)->registerDriver(GmailDriver::class);
        }
    }
}
