<?php

namespace Sanvex\Drivers\Linear;

use Sanvex\Core\Auth\KeyBuilder;
use Sanvex\Core\BaseDriver;
use Sanvex\Core\DTOs\WebhookResult;
use Sanvex\Drivers\Linear\Auth\LinearKeyBuilder;
use Sanvex\Drivers\Linear\Resources\IssuesResource;
use Sanvex\Drivers\Linear\Resources\ProjectsResource;

class LinearDriver extends BaseDriver
{
    public string $id = 'linear';
    public string $name = 'Linear';
    public array $authTypes = ['api_key', 'oauth2'];
    public string $defaultAuthType = 'api_key';

    public function issues(): IssuesResource
    {
        return new IssuesResource($this);
    }

    public function projects(): ProjectsResource
    {
        return new ProjectsResource($this);
    }

    public function handleWebhook(array $headers, array|string $payload): WebhookResult
    {
        $data = is_string($payload) ? json_decode($payload, true) : $payload;
        $eventType = $data['type'] ?? 'unknown';

        if (!is_array($data)) {
            return WebhookResult::fail('Invalid Linear payload.', 400, 'linear');
        }

        if (isset($data['data']['id'])) {
            $this->storeEntity($eventType, (string) $data['data']['id'], $data['data']);
        }

        return WebhookResult::ok(['status' => 'ok'], 'linear', $eventType);
    }

    public function verifySignature(array $headers, string $rawBody, string $secret): bool
    {
        $normalizedHeaders = array_change_key_case($headers, CASE_LOWER);
        $signature = is_array($normalizedHeaders['linear-signature'] ?? null)
            ? ($normalizedHeaders['linear-signature'][0] ?? '')
            : ($normalizedHeaders['linear-signature'] ?? '');

        $expected = hash_hmac('sha256', $rawBody, $secret);

        return hash_equals($expected, $signature);
    }

    protected function makeKeyBuilder(): KeyBuilder
    {
        return new LinearKeyBuilder(
            driver: $this->id,
            keyManager: $this->keyManager,
        );
    }
}
