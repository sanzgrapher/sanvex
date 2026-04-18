<?php

namespace Sanvex\Core\Auth;

use Sanvex\Core\Encryption\KeyManager;

class KeyBuilder
{
    protected array $inMemory = [];

    public function __construct(
        protected readonly string $driver,
        protected readonly ?KeyManager $keyManager = null,
    ) {}

    public function set(string $key, string $value): void
    {
        $this->inMemory[$key] = $value;

        if ($this->keyManager) {
            $this->keyManager->storeCredential($this->driver, $key, $value);
        }
    }

    public function get(string $key): ?string
    {
        if (isset($this->inMemory[$key])) {
            return $this->inMemory[$key];
        }

        if ($this->keyManager) {
            $value = $this->keyManager->getCredential($this->driver, $key);
            if ($value !== null) {
                $this->inMemory[$key] = $value;
            }
            return $value;
        }

        return null;
    }

    public function getToken(): string
    {
        return $this->get('api_key') ?? $this->get('bot_token') ?? $this->get('access_token') ?? '';
    }

    public function setApiKey(string $token): void
    {
        $this->set('api_key', $token);
    }
}
