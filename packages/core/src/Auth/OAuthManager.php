<?php

namespace Sanvex\Core\Auth;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Sanvex\Core\Encryption\KeyManager;
use Sanvex\Core\Tenancy\Owner;
use Throwable;

class OAuthManager
{
    public function __construct(
        private readonly string $driver,
        private readonly ?KeyManager $keyManager = null,
        private readonly ?Owner $owner = null,
    ) {}

    public function storeTokens(array $tokens): void
    {
        foreach ($tokens as $key => $value) {
            if ($this->keyManager && is_scalar($value)) {
                $this->keyManager->storeCredential($this->driver, $key, (string) $value, $this->owner ?? Owner::global());
            }
        }
    }

    public function getAccessToken(): ?string
    {
        return $this->keyManager?->getCredential($this->driver, 'access_token', $this->owner ?? Owner::global());
    }

    public function getRefreshToken(): ?string
    {
        return $this->keyManager?->getCredential($this->driver, 'refresh_token', $this->owner ?? Owner::global());
    }

    public function getExpiresAt(): ?int
    {
        $val = $this->keyManager?->getCredential($this->driver, 'expires_at', $this->owner ?? Owner::global());
        return $val ? (int) $val : null;
    }

    public function getAuthorizationUrl(OAuthProviderConfig $config, ?string $state = null): string
    {
        $query = http_build_query([
            'client_id' => $config->clientId,
            'redirect_uri' => $config->redirectUri,
            'response_type' => 'code',
            'scope' => implode(' ', $config->scopes),
            'state' => $state ?? $this->buildState(),
        ]);

        $separator = str_contains($config->authorizationUrl, '?') ? '&' : '?';
        
        return $config->authorizationUrl . $separator . $query;
    }

    public function buildState(?Owner $owner = null): string
    {
        $owner = $owner ?? $this->owner ?? Owner::global();
        $nonce = Str::random(24);
        $payload = implode(':', [$owner->type(), $owner->id(), $nonce]);
        $signature = hash_hmac('sha256', $payload, $this->resolveAppKey(), true);

        Cache::put($this->nonceCacheKey($nonce), true, 600);

        return $this->base64UrlEncode($payload).'.'.$this->base64UrlEncode($signature);
    }

    /**
     * Verify a state token produced by buildState(). Returns the embedded Owner on
     * success, or null when the signature is invalid, the nonce has already been
     * consumed (replay), or the state is malformed.
     */
    public function verifyState(string $state): ?Owner
    {
        $parts = explode('.', $state);

        if (count($parts) !== 2) {
            return null;
        }

        [$encodedPayload, $encodedSig] = $parts;

        $payload   = base64_decode(strtr($encodedPayload, '-_', '+/'));
        $signature = base64_decode(strtr($encodedSig, '-_', '+/'));

        if ($payload === false || $signature === false) {
            return null;
        }

        $expected = hash_hmac('sha256', $payload, $this->resolveAppKey(), true);

        if (! hash_equals($expected, $signature)) {
            return null;
        }

        $segments = explode(':', $payload);

        if (count($segments) < 3) {
            return null;
        }

        // nonce is the last segment; type and id may contain colons (e.g. FQCN)
        $nonce = array_pop($segments);
        $id    = array_pop($segments);
        $type  = implode(':', $segments);

        $cacheKey = $this->nonceCacheKey($nonce);

        if (Cache::pull($cacheKey) === null) {
            return null;
        }
        try {
            return Owner::fromTypeAndId($type, $id);
        } catch (\Throwable) {
            return null;
        }
    }

    private function nonceCacheKey(string $nonce): string
    {
        return 'sanvex:oauth:nonce:'.$nonce;
    }

    private function resolveAppKey(): string
    {
        $appKey = config('app.key');

        if (! is_string($appKey) || trim($appKey) === '') {
            throw new \RuntimeException('OAuth state signing requires a non-empty app.key configuration value.');
        }

        if (str_starts_with($appKey, 'base64:')) {
            $decoded = base64_decode(substr($appKey, 7), true);

            if ($decoded === false || $decoded === '') {
                throw new \RuntimeException('OAuth state signing requires a valid base64-encoded app.key configuration value.');
            }

            return $decoded;
        }

        return $appKey;
    }

    private function base64UrlEncode(string $input): string
    {
        return rtrim(strtr(base64_encode($input), '+/', '-_'), '=');
    }

    public function exchangeCode(string $code, OAuthProviderConfig $config): bool
    {
        // Notion requires Basic Auth for exchanging the token as per their docs
        // So we will use withBasicAuth to encode clientId:clientSecret in the header
        $response = Http::withBasicAuth($config->clientId, $config->clientSecret)
            ->asForm()
            ->post($config->tokenUrl, [
                'grant_type' => 'authorization_code',
                'redirect_uri' => $config->redirectUri,
                'code' => $code,
            ]);

        if ($response->successful()) {
            $data = $response->json();
            $this->saveTokenResponse($data);
            return true;
        }

        // Output error to logs if you need to debug the response
        // \Illuminate\Support\Facades\Log::error('OAuth Exchange Failed', $response->json());

        return false;
    }

    public function refreshIfExpired(OAuthProviderConfig $config): bool
    {
        $accessToken = $this->getAccessToken();
        $refreshToken = $this->getRefreshToken();
        $expiresAt = $this->getExpiresAt();

        // If we don't have a token, or we don't know when it expires, or no refresh token... skip
        if (!$accessToken || !$refreshToken || !$expiresAt) {
            return false;
        }

        // Give it a 60 second buffer to ensure request doesn't fail mid-flight
        if (time() >= ($expiresAt - 60)) {
            try {
                $response = Http::withBasicAuth($config->clientId, $config->clientSecret)
                    ->asForm()
                    ->post($config->tokenUrl, [
                        'grant_type' => 'refresh_token',
                        'refresh_token' => $refreshToken,
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    // Sometimes IDPs don't return a new refresh token, keep the old one
                    if (!isset($data['refresh_token'])) {
                        $data['refresh_token'] = $refreshToken;
                    }
                    
                    $this->saveTokenResponse($data);
                    return true;
                }
            } catch (Throwable $e) {
                // Log or handle refresh failure if needed
            }
        }

        return false; // Not expired, or refresh failed silently
    }

    protected function saveTokenResponse(array $data): void
    {
        $tokens = [];
        
        if (isset($data['access_token'])) {
            $tokens['access_token'] = $data['access_token'];
        }
        
        if (isset($data['refresh_token'])) {
            $tokens['refresh_token'] = $data['refresh_token'];
        }
        
        if (isset($data['expires_in'])) {
            $tokens['expires_at'] = time() + (int) $data['expires_in'];
        }

        $this->storeTokens($tokens);
    }
}
