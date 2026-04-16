<?php

namespace Sanvex\Core\Auth;

use Sanvex\Core\Encryption\KeyManager;

class OAuthManager
{
    public function __construct(
        private readonly string $driver,
        private readonly string $tenantId,
        private readonly ?KeyManager $keyManager = null,
    ) {}

    public function storeTokens(array $tokens): void
    {
        foreach ($tokens as $key => $value) {
            if ($this->keyManager && is_string($value)) {
                $this->keyManager->storeCredential($this->tenantId, $this->driver, $key, $value);
            }
        }
    }

    public function getAccessToken(): ?string
    {
        return $this->keyManager?->getCredential($this->tenantId, $this->driver, 'access_token');
    }

    public function getRefreshToken(): ?string
    {
        return $this->keyManager?->getCredential($this->tenantId, $this->driver, 'refresh_token');
    }
}
