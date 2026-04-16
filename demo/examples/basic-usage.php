<?php
/**
 * Basic sanvex usage example.
 * This is documentation — not a standalone executable script.
 * In a Laravel app, use the service container instead.
 */

use Sanvex\Core\ConnectorManager;
use Sanvex\Drivers\Slack\SlackDriver;
use Sanvex\Drivers\GitHub\GitHubDriver;

// Initialize the connector manager
$connector = ConnectorManager::make([
    'drivers' => [SlackDriver::class, GitHubDriver::class],
    'kek' => env('SANVEX_KEK', base64_encode(random_bytes(32))),
]);

// Store credentials (in-memory if no DB configured, encrypted in DB if KEK+DB available)
$connector->slack()->keys()->setApiKey('xoxb-your-bot-token');
$connector->github()->keys()->setApiKey('ghp_your-pat-token');

// Send a Slack message
$result = $connector->slack()->messages()->post([
    'channel' => 'C01234567',
    'text'    => 'Hello from sanvex!',
]);
echo "Message sent: " . ($result['ok'] ? 'yes' : 'no') . PHP_EOL;

// List GitHub repositories
$repos = $connector->github()->repositories()->list(['type' => 'owner', 'per_page' => 10]);
foreach ($repos as $repo) {
    echo " - " . ($repo['full_name'] ?? $repo['name'] ?? 'unknown') . PHP_EOL;
}

// Query local DB (requires migrations to be run)
// $messages = $connector->slack()->db()->messages()->findAll(50);
