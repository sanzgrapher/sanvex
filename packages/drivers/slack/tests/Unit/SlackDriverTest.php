<?php

use Sanvex\Drivers\Slack\SlackDriver;

it('creates a slack driver', function () {
    $driver = new SlackDriver();
    expect($driver->id)->toBe('slack');
    expect($driver->name)->toBe('Slack');
    expect($driver->authTypes)->toContain('api_key');
});

it('verifies valid slack webhook signature', function () {
    $driver = new SlackDriver();
    $timestamp = time();
    $body = 'test_body';
    $secret = 'test_secret';
    $sigBase = "v0:{$timestamp}:{$body}";
    $sig = 'v0=' . hash_hmac('sha256', $sigBase, $secret);
    $result = $driver->verifySignature(
        ['x-slack-signature' => [$sig], 'x-slack-request-timestamp' => [(string) $timestamp]],
        $body,
        $secret
    );
    expect($result)->toBeTrue();
});

it('rejects slack webhook with bad signature', function () {
    $driver = new SlackDriver();
    $result = $driver->verifySignature(
        ['x-slack-signature' => ['v0=badhash'], 'x-slack-request-timestamp' => [(string) time()]],
        'body',
        'secret'
    );
    expect($result)->toBeFalse();
});

it('handles url_verification challenge', function () {
    $driver = new SlackDriver();
    $payload = ['type' => 'url_verification', 'challenge' => 'test-challenge-123'];
    $result = $driver->handleWebhook([], $payload);
    expect($result->success)->toBeTrue();
    expect($result->response['challenge'])->toBe('test-challenge-123');
    expect($result->eventType)->toBe('url_verification');
});

it('handles event_callback webhook', function () {
    $driver = new SlackDriver();
    $payload = [
        'type' => 'event_callback',
        'event' => [
            'type' => 'message',
            'ts' => '1234567890.000001',
            'text' => 'Hello World',
            'channel' => 'C12345',
        ],
    ];
    $result = $driver->handleWebhook([], $payload);
    expect($result->success)->toBeTrue();
});

it('rejects stale slack webhook timestamp', function () {
    $driver = new SlackDriver();
    $oldTimestamp = time() - 600; // 10 minutes ago
    $body = 'stale_body';
    $secret = 'test_secret';
    $sigBase = "v0:{$oldTimestamp}:{$body}";
    $sig = 'v0=' . hash_hmac('sha256', $sigBase, $secret);
    $result = $driver->verifySignature(
        ['x-slack-signature' => [$sig], 'x-slack-request-timestamp' => [(string) $oldTimestamp]],
        $body,
        $secret
    );
    expect($result)->toBeFalse();
});
