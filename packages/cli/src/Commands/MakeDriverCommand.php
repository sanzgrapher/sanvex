<?php

namespace Sanvex\Cli\Commands;

use Illuminate\Console\Command;

class MakeDriverCommand extends Command
{
    protected $signature = 'sanvex:make-driver {name : The driver name (e.g. MyService)}';
    protected $description = 'Scaffold a new sanvex driver package';

    public function handle(): int
    {
        $name = $this->argument('name');
        $lower = strtolower($name);
        $namespace = 'Sanvex\\Drivers\\' . $name . '\\';
        $packageDir = base_path("packages/drivers/{$lower}");

        if (is_dir($packageDir)) {
            $this->error("Directory [{$packageDir}] already exists.");
            return self::FAILURE;
        }

        mkdir($packageDir . '/src/Resources/Db', 0755, true);
        mkdir($packageDir . '/src/Auth', 0755, true);
        mkdir($packageDir . '/tests/Unit', 0755, true);

        // composer.json
        file_put_contents($packageDir . '/composer.json', json_encode([
            'name' => "sanvex/{$lower}",
            'description' => "{$name} driver for sanvex",
            'type' => 'library',
            'license' => 'MIT',
            'require' => ['php' => '^8.2', 'sanvex/core' => '*'],
            'autoload' => ['psr-4' => ["{$namespace}" => 'src/']],
            'extra' => ['laravel' => ['providers' => ["{$namespace}{$name}ServiceProvider"]]],
            'minimum-stability' => 'dev',
            'prefer-stable' => true,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

        // Driver stub
        file_put_contents($packageDir . "/src/{$name}Driver.php", <<<PHP
<?php

namespace {$namespace};

use Sanvex\\Core\\BaseDriver;
use Sanvex\\Core\\DTOs\\WebhookResult;

class {$name}Driver extends BaseDriver
{
    public string \$id = '{$lower}';
    public string \$name = '{$name}';
    public array \$authTypes = ['api_key'];
    public string \$defaultAuthType = 'api_key';

    public function handleWebhook(array \$headers, array|string \$payload): WebhookResult
    {
        return WebhookResult::ok(['status' => 'ok'], '{$lower}', 'notification');
    }

    public function verifySignature(array \$headers, string \$rawBody, string \$secret): bool
    {
        return true;
    }
}
PHP);

        // ServiceProvider stub
        file_put_contents($packageDir . "/src/{$name}ServiceProvider.php", <<<PHP
<?php

namespace {$namespace};

use Illuminate\\Support\\ServiceProvider;
use Sanvex\\Core\\ConnectorManager;

class {$name}ServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (\$this->app->bound(ConnectorManager::class)) {
            \$this->app->make(ConnectorManager::class)->registerDriver({$name}Driver::class);
        }
    }
}
PHP);

        $this->info("Driver [{$name}] scaffolded at [{$packageDir}].");
        $this->line("Next steps:");
        $this->line("  1. Add {\"type\": \"path\", \"url\": \"packages/drivers/{$lower}\"} to root composer.json repositories");
        $this->line("  2. Run: composer require sanvex/{$lower}");
        return self::SUCCESS;
    }
}
