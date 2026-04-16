<?php

namespace Sanvex\Drivers\Notion;

use Sanvex\Core\Auth\KeyBuilder;
use Sanvex\Core\BaseDriver;
use Sanvex\Core\DTOs\WebhookResult;
use Sanvex\Drivers\Notion\Auth\NotionKeyBuilder;
use Sanvex\Drivers\Notion\Resources\DatabasesResource;
use Sanvex\Drivers\Notion\Resources\PagesResource;

class NotionDriver extends BaseDriver
{
    public string $id = 'notion';
    public string $name = 'Notion';
    public array $authTypes = ['api_key'];
    public string $defaultAuthType = 'api_key';

    public function pages(): PagesResource
    {
        return new PagesResource($this);
    }

    public function databases(): DatabasesResource
    {
        return new DatabasesResource($this);
    }

    public function handleWebhook(array $headers, array|string $payload): WebhookResult
    {
        return WebhookResult::ok(['status' => 'ok'], 'notion', 'notification');
    }

    public function verifySignature(array $headers, string $rawBody, string $secret): bool
    {
        return true;
    }

    protected function makeKeyBuilder(): KeyBuilder
    {
        return new NotionKeyBuilder(
            driver: $this->id,
            tenantId: $this->tenantId ?? 'default',
            keyManager: $this->keyManager,
        );
    }
}
