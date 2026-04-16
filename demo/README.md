# sanvex Demo Examples

This directory contains example scripts showing how to use sanvex.

## Examples

- `examples/basic-usage.php` — Simple single-tenant usage with Slack and GitHub
- `examples/multi-tenant.php` — Multi-tenant usage with per-tenant credentials
- `examples/webhook-workflow.php` — Webhook processing workflow

## Running Examples

These are documentation examples, not executable standalone scripts.
In a real Laravel app, use the `ConnectorManager` via the service container:

```php
$connector = app(\Sanvex\Core\ConnectorManager::class);
$connector->slack()->messages()->post(['channel' => 'C123', 'text' => 'Hello!']);
```
