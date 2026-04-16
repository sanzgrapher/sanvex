<?php

namespace Sanvex\Drivers\Slack\Webhooks;

use Sanvex\Core\DTOs\WebhookResult;

class SlackWebhookHandler
{
    public function handle(array $headers, array|string $payload, callable $storeEntity): WebhookResult
    {
        $data = is_string($payload) ? json_decode($payload, true) : $payload;

        if (!is_array($data)) {
            return WebhookResult::fail('Invalid Slack payload.', 400, 'slack');
        }

        // Handle URL verification challenge
        if (($data['type'] ?? '') === 'url_verification') {
            return WebhookResult::ok(['challenge' => $data['challenge'] ?? ''], 'slack', 'url_verification');
        }

        $eventType = $data['event']['type'] ?? $data['type'] ?? 'unknown';

        // Handle event callbacks
        if (($data['type'] ?? '') === 'event_callback') {
            $event = $data['event'] ?? [];

            match ($event['type'] ?? '') {
                'message' => $storeEntity('message', $event['ts'] ?? uniqid(), $event),
                'channel_created' => $storeEntity('channel', $event['channel']['id'] ?? uniqid(), $event['channel'] ?? $event),
                default => null,
            };
        }

        return WebhookResult::ok(['status' => 'ok'], 'slack', $eventType);
    }
}
