<?php
/**
 * Multi-tenant sanvex usage example.
 * This is documentation — not a standalone executable script.
 */

use Sanvex\Core\ConnectorManager;
use Sanvex\Drivers\GitHub\GitHubDriver;
use Sanvex\Drivers\Slack\SlackDriver;

// Initialize with multi_tenancy enabled
$connector = ConnectorManager::make([
    'multi_tenancy' => true,
    'drivers' => [GitHubDriver::class, SlackDriver::class],
    'kek' => env('SANVEX_KEK'),
]);

// Tenant A: GitHub PAT
$tenantA = $connector->forTenant('user_abc123');
$tenantA->github()->keys()->setApiKey('ghp_token_for_user_abc123');
$repos = $tenantA->github()->repositories()->list(['type' => 'owner']);
echo "Tenant A repos: " . count($repos) . PHP_EOL;

// Tenant B: Different GitHub PAT
$tenantB = $connector->forTenant('org_xyz789');
$tenantB->github()->keys()->setApiKey('ghp_token_for_org_xyz789');
$issues = $tenantB->github()->issues()->list([
    'owner' => 'org_xyz789',
    'repo'  => 'main-project',
]);
echo "Tenant B open issues: " . count($issues) . PHP_EOL;

// Tenants are fully isolated — credentials stored separately in sv_accounts
// with tenant_id scoping all queries.
