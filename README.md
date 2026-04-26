# Sanvex

AI agents hit GitHub, Gmail, Linear, Notion, Slack, or your own drivers through one Laravel surface: `SanvexManager` → `resolveDriver($id)` → `$driver->resourceName()->action($args)`.

[Latest Version on Packagist](https://packagist.org/packages/sanvex/core)
[Total Downloads](https://packagist.org/packages/sanvex/core)

---

## Packages at a glance


| Package       | Role                                                         |
| ------------- | ------------------------------------------------------------ |
| `sanvex/core` | `SanvexManager`, encryption, DB tables, webhooks, tenancy    |
| `sanvex/cli`  | Artisan: setup, migrate, list, scaffolding                   |
| `sanvex/mcp`  | MCP server (stdio + optional HTTP SSE) exposing Sanvex tools |


Require `sanvex/cli` only if you want those commands; require `sanvex/mcp` only if an agent or IDE will speak MCP.

---

## Install and setup

1. **Composer** – require `sanvex/core` and `sanvex/cli`; add `sanvex/mcp` only if you use MCP; add each driver package you need.
  ```bash
   composer require sanvex/core sanvex/cli
   # composer require sanvex/mcp
   composer require sanvex/github
   # composer require sanvex/gmail sanvex/linear sanvex/notion sanvex/slack
  ```
2. **Database** – run migrations (core loads its migrations with the app).
  ```bash
   php artisan migrate
  ```
3. **Config (optional)** – publish app `config/sanvex.php`, or rely on package defaults (`packages/core/config/sanvex.php`).
  ```bash
   php artisan vendor:publish --tag=sanvex-config
  ```
4. **Credentials** – list drivers, then store keys (global or per-owner).
  ```bash
   php artisan sanvex:list
   php artisan sanvex:setup github --api-key="ghp_..."
   php artisan sanvex:setup notion --api-key="secret_..." --owner-type=App\\Models\\Team --owner-id=1
  ```
5. **Custom drivers (optional)** – register classes in `config/sanvex.php`:
  ```php
   'drivers' => [
       \App\Sanvex\AcmeDriver::class,
   ],
  ```

---

## Drivers


| Composer package | Driver id | Typical surface                     |
| ---------------- | --------- | ----------------------------------- |
| `sanvex/github`  | `github`  | Repositories, issues, pull requests |
| `sanvex/gmail`   | `gmail`   | Messages, threads                   |
| `sanvex/linear`  | `linear`  | Issues, projects                    |
| `sanvex/notion`  | `notion`  | Pages, databases, blocks, search    |
| `sanvex/slack`   | `slack`   | Channels, messages, users           |


Each package’s `composer.json` declares its Laravel service provider for discovery.

---

## Core PHP example

```php
use Sanvex\Core\SanvexManager;

public function repos(SanvexManager $manager)
{
    return $manager->resolveDriver('github')
        ->repositories()
        ->list(['per_page' => 10]);
}
```

---

## AI example

```json
{
  "name": "sanvex_action",
  "parameters": {
    "type": "object",
    "properties": {
      "driver": { "type": "string" },
      "resource": { "type": "string" },
      "action": { "type": "string" },
      "args": { "type": "object" }
    },
    "required": ["driver", "resource", "action"]
  }
}
```

```php
$d = $manager->for($tenant)->resolveDriver($instruction['driver']);
$result = $d->{$instruction['resource']}()->{$instruction['action']}($instruction['args'] ?? []);
```

---

## MCP (optional)

Package: `sanvex/mcp`. Entry command:

```bash
php artisan sanvex:mcp-stdio
```


| Topic        | Detail                                                                 |
| ------------ | ---------------------------------------------------------------------- |
| Transport    | JSON-RPC one message per line on stdin, replies on stdout              |
| Server class | `Sanvex\Mcp\Server\JsonRpcServer`                                      |
| Tools        | `sanvex_action`, `sanvex_list_operations`                              |
| Tenancy      | Uses global `resolveDriver()` (no `for($owner)` in the bundled server) |


SSE (optional): when `SANVEX_MCP_ENABLE_SERVER` / `config('sanvex.mcp.enable_server')` is true, routes `GET /sanvex/mcp/sse` and `POST /sanvex/mcp/message` use the same server for payloads.

---

## CLI

Package: `sanvex/cli`. Commands register only when the app runs in console.


| Command                     | Purpose                                                                      |
| --------------------------- | ---------------------------------------------------------------------------- |
| `sanvex:list`               | Registered drivers and auth metadata                                         |
| `sanvex:setup {driver}`     | Store credentials (`--api-key`, `--bot-token`, `--owner-type`, `--owner-id`) |
| `sanvex:migrate`            | Run migrations from `vendor/sanvex/core/src/Database/migrations`             |
| `sanvex:keygen`             | Print a random `SANVEX_KEK=...` line for `.env`                              |
| `sanvex:make-driver {name}` | Scaffold under `packages/drivers/{name}` in the consuming app                |
| `sanvex:mcp-stdio`          | Start MCP stdio server (requires `sanvex/mcp`)                               |


---

## Multi-tenancy

Use `SanvexManager::for($owner)` before `resolveDriver()` so keys and driver clones are scoped to that owner (`Owner` from an Eloquent model or `Sanvex\Core\Contracts\SanvexOwner`).

```php
$notion = $manager->for(auth()->user())->resolveDriver('notion');
$pages = $notion->pages()->list(['page_size' => 10]);
```

Global scope stays available: `resolveDriver('github')` is the same as `for(null)->resolveDriver('github')`.

CLI setup with owner: `php artisan sanvex:setup slack --bot-token=... --owner-type=App\\Models\\Workspace --owner-id=42`.

---

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.