<?php
/**
 * Webhook processing example.
 * This is documentation — not a standalone executable script.
 *
 * In a Laravel app, the WebhookController handles this automatically.
 * Register the route:
 *   Route::post('/api/webhooks', [WebhookController::class, 'handle']);
 */

use Sanvex\Core\ConnectorManager;
use Sanvex\Drivers\Slack\SlackDriver;
use Sanvex\Drivers\GitHub\GitHubDriver;

$connector = ConnectorManager::make([
    'drivers' => [SlackDriver::class, GitHubDriver::class],
    'kek' => env('SANVEX_KEK'),
]);

// Simulate incoming Slack webhook
$slackHeaders = [
    'x-slack-signature'         => ['v0=' . hash_hmac('sha256', 'v0:'.time().':payload', 'your-signing-secret')],
    'x-slack-request-timestamp' => [(string) time()],
];
$slackPayload = [
    'type'     => 'event_callback',
    'event'    => [
        'type'    => 'message',
        'ts'      => '1234567890.000001',
        'text'    => 'Hello World!',
        'channel' => 'C01234567',
        'user'    => 'U01234567',
    ],
];

$result = $connector->processWebhook($slackHeaders, $slackPayload);
echo "Webhook processed: " . ($result->success ? 'success' : 'failed') . PHP_EOL;
echo "Driver: " . $result->driver . PHP_EOL;
echo "Event type: " . $result->eventType . PHP_EOL;

// Simulate GitHub webhook
$githubPayload = [
    'repository' => ['id' => 123, 'name' => 'my-repo', 'full_name' => 'owner/my-repo'],
    'commits'    => [['id' => 'abc123', 'message' => 'Fix bug']],
    'pusher'     => ['name' => 'developer'],
];
$githubResult = $connector->processWebhook(
    ['x-github-event' => ['push']],
    $githubPayload
);
echo "GitHub webhook: " . ($githubResult->success ? 'success' : 'failed') . PHP_EOL;
