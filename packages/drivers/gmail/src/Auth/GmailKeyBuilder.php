<?php

namespace Sanvex\Drivers\Gmail\Auth;

use Sanvex\Core\Auth\KeyBuilder;

class GmailKeyBuilder extends KeyBuilder
{
    public function setOAuthCredentials(array $creds): void
    {
        foreach ($creds as $key => $value) {
            if (is_string($value)) {
                $this->set($key, $value);
            }
        }
    }

    public function getToken(): string
    {
        return $this->get('access_token') ?? '';
    }
}
