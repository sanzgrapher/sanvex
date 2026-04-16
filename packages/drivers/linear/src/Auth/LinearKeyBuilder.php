<?php

namespace Sanvex\Drivers\Linear\Auth;

use Sanvex\Core\Auth\KeyBuilder;

class LinearKeyBuilder extends KeyBuilder
{
    public function setOAuthToken(string $token): void
    {
        $this->set('access_token', $token);
    }

    public function getToken(): string
    {
        return $this->get('api_key') ?? $this->get('access_token') ?? '';
    }
}
