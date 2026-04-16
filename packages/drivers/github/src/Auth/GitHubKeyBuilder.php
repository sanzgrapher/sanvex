<?php

namespace Sanvex\Drivers\GitHub\Auth;

use Sanvex\Core\Auth\KeyBuilder;

class GitHubKeyBuilder extends KeyBuilder
{
    public function setPersonalAccessToken(string $token): void
    {
        $this->set('api_key', $token);
    }

    public function setOAuthToken(string $token): void
    {
        $this->set('access_token', $token);
    }

    public function setWebhookSecret(string $secret): void
    {
        $this->set('webhook_secret', $secret);
    }

    public function getWebhookSecret(): ?string
    {
        return $this->get('webhook_secret');
    }
}
