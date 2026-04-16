<?php

namespace Sanvex\Drivers\Notion\Auth;

use Sanvex\Core\Auth\KeyBuilder;

class NotionKeyBuilder extends KeyBuilder
{
    public function setIntegrationToken(string $token): void
    {
        $this->set('api_key', $token);
    }
}
