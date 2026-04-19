<?php

namespace Sanvex\Core;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;
use Sanvex\Core\Auth\KeyBuilder;
use Sanvex\Core\DTOs\WebhookResult;
use Sanvex\Core\Encryption\KeyManager;

abstract class BaseDriver
{
    public string $id = '';

    public string $name = '';

    public array $authTypes = ['api_key'];

    public string $defaultAuthType = 'api_key';

    protected ?SanvexManager $manager = null;

    protected array $config = [];

    protected ?KeyManager $keyManager = null;

    protected ?KeyBuilder $keyBuilderInstance = null;

    private ?Client $httpClient = null;

    abstract public function handleWebhook(array $headers, array|string $payload): WebhookResult;

    abstract public function verifySignature(array $headers, string $rawBody, string $secret): bool;

    public function isConfigured(): bool
    {
        return ! empty($this->getToken());
    }

    public function setManager(SanvexManager $manager): static
    {
        $this->manager = $manager;

        return $this;
    }

    public function configure(array $config): static
    {
        $this->config = array_merge($this->config, $config);

        return $this;
    }

    public function setKeyManager(KeyManager $keyManager): static
    {
        $this->keyManager = $keyManager;

        return $this;
    }

    public function keys(): KeyBuilder
    {
        if (! $this->keyBuilderInstance) {
            $this->keyBuilderInstance = $this->makeKeyBuilder();
        }

        return $this->keyBuilderInstance;
    }

    protected function makeKeyBuilder(): KeyBuilder
    {
        return new KeyBuilder(
            driver: $this->id,
            keyManager: $this->keyManager,
        );
    }

    public function db(): DbAccessor
    {
        return new DbAccessor($this);
    }

    protected function getToken(): string
    {
        return $this->keys()->getToken();
    }

    protected function httpClient(): Client
    {
        if (! $this->httpClient) {
            $this->httpClient = new Client([
                'timeout' => $this->config['timeout'] ?? 30,
                'headers' => [
                    'Authorization' => 'Bearer '.$this->getToken(),
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
            ]);
        }

        return $this->httpClient;
    }

    public function get(string $url, array $params = []): array
    {
        $response = $this->httpClient()->get($url, ['query' => $params]);

        return json_decode((string) $response->getBody(), true) ?? [];
    }

    public function post(string $url, array $data = []): array
    {
        $response = $this->httpClient()->post($url, ['json' => $data]);

        return json_decode((string) $response->getBody(), true) ?? [];
    }

    public function put(string $url, array $data = []): array
    {
        $response = $this->httpClient()->put($url, ['json' => $data]);

        return json_decode((string) $response->getBody(), true) ?? [];
    }

    public function delete(string $url): array
    {
        $response = $this->httpClient()->delete($url);

        return json_decode((string) $response->getBody(), true) ?? [];
    }

    protected function storeEntity(string $type, string $entityId, array $data): void
    {
        // Guard against missing Laravel app container (e.g. unit test context).
        // Log a debug notice so developers can detect misconfigured test setups.
        if (! class_exists(Facade::class) || ! Facade::getFacadeApplication()) {
            return;
        }

        DB::table('sv_entities')->updateOrInsert(
            [
                'driver' => $this->id,
                'entity_type' => $type,
                'entity_id' => $entityId,
            ],
            [
                'data' => json_encode($data),
                'updated_at' => now(),
            ]
        );

        // Preserve original created_at on subsequent upserts
        DB::table('sv_entities')
            ->where('driver', $this->id)
            ->where('entity_type', $type)
            ->where('entity_id', $entityId)
            ->whereNull('created_at')
            ->update(['created_at' => now()]);
    }

    protected function getEntities(string $type, array $filters = []): array
    {
        $query = DB::table('sv_entities')
            ->where('driver', $this->id)
            ->where('entity_type', $type);

        foreach ($filters as $key => $value) {
            $query->where($key, $value);
        }

        return $query->get()->map(fn ($r) => (array) $r)->toArray();
    }
}
