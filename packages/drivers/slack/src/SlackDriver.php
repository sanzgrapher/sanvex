<?php

namespace Sanvex\Drivers\Slack;

use Sanvex\Core\Auth\KeyBuilder;
use Sanvex\Core\BaseDriver;
use Sanvex\Core\DbAccessor;
use Sanvex\Core\DTOs\WebhookResult;
use Sanvex\Drivers\Slack\Auth\SlackKeyBuilder;
use Sanvex\Drivers\Slack\Resources\ChannelsResource;
use Sanvex\Drivers\Slack\Resources\Db\ChannelsDbResource;
use Sanvex\Drivers\Slack\Resources\Db\MessagesDbResource;
use Sanvex\Drivers\Slack\Resources\MessagesResource;
use Sanvex\Drivers\Slack\Resources\UsersResource;
use Sanvex\Drivers\Slack\Webhooks\SlackWebhookHandler;

class SlackDriver extends BaseDriver
{
    public string $id = 'slack';
    public string $name = 'Slack';
    public array $authTypes = ['api_key', 'oauth2'];
    public string $defaultAuthType = 'api_key';

    public function messages(): MessagesResource
    {
        return new MessagesResource($this);
    }

    public function channels(): ChannelsResource
    {
        return new ChannelsResource($this);
    }

    public function users(): UsersResource
    {
        return new UsersResource($this);
    }

    public function db(): SlackDbAccessor
    {
        return new SlackDbAccessor($this);
    }

    public function handleWebhook(array $headers, array|string $payload): WebhookResult
    {
        $handler = new SlackWebhookHandler();
        return $handler->handle($headers, $payload, fn($type, $id, $data) => $this->storeEntity($type, $id, $data));
    }

    public function verifySignature(array $headers, string $rawBody, string $secret): bool
    {
        $normalizedHeaders = array_change_key_case($headers, CASE_LOWER);

        $signature = is_array($normalizedHeaders['x-slack-signature'] ?? null)
            ? ($normalizedHeaders['x-slack-signature'][0] ?? '')
            : ($normalizedHeaders['x-slack-signature'] ?? '');

        $timestamp = is_array($normalizedHeaders['x-slack-request-timestamp'] ?? null)
            ? ($normalizedHeaders['x-slack-request-timestamp'][0] ?? '')
            : ($normalizedHeaders['x-slack-request-timestamp'] ?? '');

        if (abs(time() - (int) $timestamp) > 300) {
            return false;
        }

        $sigBase = "v0:{$timestamp}:{$rawBody}";
        $expected = 'v0=' . hash_hmac('sha256', $sigBase, $secret);

        return hash_equals($expected, $signature);
    }

    protected function makeKeyBuilder(): KeyBuilder
    {
        return new SlackKeyBuilder(
            driver: $this->id,
            tenantId: $this->tenantId ?? 'default',
            keyManager: $this->keyManager,
        );
    }
}
