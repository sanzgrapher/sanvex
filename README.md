# 🚀 Sanvex

**An AI-agentic ecosystem and unified API integration layer for Laravel.**

Sanvex is a powerful Laravel package designed to provide a unified, developer-friendly interface for interacting with various third-party services (GitHub, Gmail, Linear, Notion, Slack). It is built specifically to be consumed by AI Agents via dynamic tool calling, but it can also be used directly within your application code.

## ✨ Features

- **Unified Interface (\`SanvexManager`)**: Interact with any supported service using a consistent \`Driver -> Resource -> Action\` pattern.
- **AI Tool Calling Ready**: Designed from the ground up to be easily mapped to LLM function calls (e.g., OpenAI, Anthropic, Groq).
- **Interactive CLI**: Easily configure API keys and check driver status via built-in Artisan commands.
- **Secure Credential Storage**: Built-in encryption (\`EncryptionService\`) for storing API keys securely within your application database.
- **Extensible Architecture**: Easily add new custom drivers and resources.

## 🧩 Monorepo Package Layout

Sanvex is developed in a single monorepo:

- Main packages: `packages/core`, `packages/cli`, `packages/mcp`
- Driver packages: `packages/drivers/*` (e.g. `github`, `gmail`, `linear`, `notion`, `slack`)

Each package is split automatically into its own read-only repository via `.github/workflows/split-packages.yml`.
To enable pushing split updates, set `GH_PAT` in repository secrets with permission to push to the split repositories.
Contributions and pull requests should be opened against this monorepo (the split repositories are mirrors).

**Releases:** Stable versions on Packagist come from **SemVer git tags** on this monorepo (for example `v0.1.0`). Pushing a tag runs the split workflow and tags the matching read-only repos (for example `sanvexdev/core`), which Packagist then indexes as stable.

## 📦 Supported Drivers

Currently, Sanvex supports the following out-of-the-box integrations:

- **GitHub** (Repositories, Issues, Pull Requests)
- **Gmail** (Emails, Threads)
- **Linear** (Issues, Projects)
- **Notion** (Pages, Databases)
- **Slack** (Channels, Messages, Users)

## 🛠️ Installation

### From Packagist (recommended)

Install the core package in a Laravel app (works with default Composer `minimum-stability: stable` once a tagged release exists):

```bash
composer require sanvex/core:^0.1.0
```

To track the default branch instead of a tagged release (dev stability), require it explicitly:

```bash
composer require sanvex/core:dev-main
```

Alternatively, set `"minimum-stability": "dev"` and `"prefer-stable": true` in your app’s root `composer.json`, then run `composer require sanvex/core`.

Other split packages (CLI, MCP, drivers) are published under the `sanvex` vendor on Packagist the same way; pin each with a semver range when stable tags are available.

### Local path (this monorepo)

For development against a checkout of this repository, add a path repository in your app’s `composer.json` pointing at this monorepo root, then require the package you need (see each package’s `composer.json` under `packages/`).

Make sure the Service Providers are successfully registered in your Laravel application (e.g., inside \`bootstrap/providers.php\`):

```php
\Sanvex\Core\SanvexServiceProvider::class,
\Sanvex\Mcp\McpServiceProvider::class,
\Sanvex\Drivers\GitHub\GitHubServiceProvider::class,
// ... other drivers
```

## ⚙️ Setup & Configuration

Sanvex comes with a helpful CLI module to manage your driver connections.

**1. List all available drivers:**

```bash
php artisan sanvex:list
```

**2. Setup a specific driver:**
This command prompts you to enter required credentials (e.g., API Key, OAuth tokens) or allows them inline.

```bash
php artisan sanvex:setup github --api-key="your_api_key_here"
```

## 💻 Usage

### Direct PHP Implementation

You can interact with the drivers simply using the \`SanvexManager\` via Dependency Injection.

```php
use Sanvex\Core\SanvexManager;

class GithubController extends Controller
{
    public function getRepos(SanvexManager $manager)
    {
        // 1. Resolve the driver
        $github = $manager->resolveDriver("github");

        // 2. Access a resource module and perform an action
        $repos = $github->repositories()->list([
            "per_page" => 10
        ]);

        return response()->json($repos);
    }
}
```

### 🤖 Using with AI Agents (Tool Calling)

Sanvex really shines when bridging your application to AI models. Instead of manually writing logic for every endpoint, you can expose a generic JSON \`sanvex_action\` tool to your LLM.

**Exposed JSON Tool Definition:**

```json
{
  "name": "sanvex_action",
  "description": "Perform an action on an integrated driver resource to fetch or manipulate data.",
  "parameters": {
    "type": "object",
    "properties": {
      "driver": {
        "type": "string",
        "description": "e.g., github, linear, notion"
      },
      "resource": {
        "type": "string",
        "description": "e.g., repositories, issues"
      },
      "action": { "type": "string", "description": "e.g., list, get, create" },
      "args": {
        "type": "object",
        "description": "Key-value arguments for the action."
      }
    },
    "required": ["driver", "resource", "action"]
  }
}
```

**Dynamic Execution Pipeline:**

```php
$driverId = $instruction["driver"];      // e.g., "github"
$resource = $instruction["resource"];    // e.g., "repositories"
$action   = $instruction["action"];      // e.g., "list"
$args     = $instruction["args"] ?? [];

$driver = $manager->resolveDriver($driverId);

// Effortlessly map LLM actions directly to Sanvex features
$result = $driver->{$resource}()->{$action}($args);

return $result;
```

### Multi-tenant usage

Single-tenant usage remains unchanged:

```php
$driver = $manager->resolveDriver('notion');
```

For multi-tenant apps, use a tenant-scoped context with `for($owner)`.
The owner can be an Eloquent model (for example `User`, `Team`, `Workspace`) or an object implementing `Sanvex\Core\Contracts\SanvexOwner`.

```php
use Sanvex\Core\SanvexManager;

class CrmController extends Controller
{
  public function index(SanvexManager $manager)
  {
    $notion = $manager->for(auth()->user())->resolveDriver('notion');

    return $notion->pages()->list([
      'page_size' => 10,
    ]);
  }
}
```

Optional container sugar if your app centralizes owner resolution:

```php
app()->bind('sanvex.current_owner', fn () => auth()->user());

$owner = app('sanvex.current_owner');
$github = app(SanvexManager::class)->for($owner)->resolveDriver('github');
```
