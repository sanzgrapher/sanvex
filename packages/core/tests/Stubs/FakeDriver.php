<?php

namespace Sanvex\Core\Tests\Stubs;

use Sanvex\Core\BaseDriver;
use Sanvex\Core\DTOs\WebhookResult;

class FakeDriver extends BaseDriver
{
    public string $id = 'fake';

    public string $name = 'Fake';

    public function handleWebhook(array $headers, array|string $payload): WebhookResult
    {
        return WebhookResult::ok(['ok' => true], $this->id, 'fake.event');
    }

    public function verifySignature(array $headers, string $rawBody, string $secret): bool
    {
        return true;
    }

    public function putEntity(string $type, string $entityId, array $data): void
    {
        $this->storeEntity($type, $entityId, $data);
    }

    public function listEntities(string $type): array
    {
        return $this->getEntities($type);
    }
}
