<?php

use Sanvex\Drivers\GitHub\GitHubDriver;

it('creates a github driver', function () {
    $driver = new GitHubDriver();
    expect($driver->id)->toBe('github');
    expect($driver->name)->toBe('GitHub');
    expect($driver->authTypes)->toContain('api_key');
});

it('verifies valid github webhook signature', function () {
    $driver = new GitHubDriver();
    $body = 'test_body';
    $secret = 'test_secret';
    $sig = 'sha256=' . hash_hmac('sha256', $body, $secret);
    $result = $driver->verifySignature(
        ['x-hub-signature-256' => [$sig]],
        $body,
        $secret
    );
    expect($result)->toBeTrue();
});

it('rejects github webhook with bad signature', function () {
    $driver = new GitHubDriver();
    $result = $driver->verifySignature(
        ['x-hub-signature-256' => ['sha256=badhash']],
        'body',
        'secret'
    );
    expect($result)->toBeFalse();
});

it('handles push event webhook', function () {
    $driver = new GitHubDriver();
    $payload = [
        'repository' => ['id' => 123, 'name' => 'test-repo', 'full_name' => 'owner/test-repo'],
    ];
    $result = $driver->handleWebhook(['x-github-event' => ['push']], $payload);
    expect($result->success)->toBeTrue();
    expect($result->eventType)->toBe('push');
});

it('handles issues event webhook', function () {
    $driver = new GitHubDriver();
    $payload = [
        'action' => 'opened',
        'issue' => ['id' => 456, 'number' => 1, 'title' => 'Test Issue'],
    ];
    $result = $driver->handleWebhook(['x-github-event' => ['issues']], $payload);
    expect($result->success)->toBeTrue();
    expect($result->eventType)->toBe('issues');
});
