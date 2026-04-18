<?php

namespace Sanvex\Drivers\Slack\Auth;

use Sanvex\Core\Auth\KeyBuilder;

class SlackKeyBuilder extends KeyBuilder
{
    public function setBotToken(string $token): void
    {
        $this->set('bot_token', $token);
    }

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
        return $this->get('bot_token') ?? $this->get('api_key') ?? $this->get('access_token') ?? '';
    }
}
