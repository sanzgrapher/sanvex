<?php

namespace Sanvex\Drivers\Notion;

use Illuminate\Support\ServiceProvider;
use Sanvex\Core\SanvexManager;

class NotionServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->bound(SanvexManager::class)) {
            $this->app->make(SanvexManager::class)->registerDriver(NotionDriver::class);
        }

        // Load OAuth routes if configured to use OAuth (or if client_id is present)
        $authType = config('sanvex.driver_configs.notion.auth_type');
        $clientId = config('sanvex.driver_configs.notion.oauth.client_id');
        
        if ($authType === 'oauth_2' || !empty($clientId)) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/oauth.php');
        }
    }
}
