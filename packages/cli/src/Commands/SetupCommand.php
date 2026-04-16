<?php

namespace Sanvex\Cli\Commands;

use Illuminate\Console\Command;
use Sanvex\Core\ConnectorManager;

class SetupCommand extends Command
{
    protected $signature = 'sanvex:setup
                            {driver : The driver to set up (e.g. slack, github)}
                            {--api-key= : API key or token}
                            {--bot-token= : Bot token (for Slack)}
                            {--backfill : Run backfill after setup}';

    protected $description = 'Set up a driver integration with credentials';

    public function handle(ConnectorManager $connector): int
    {
        $driverId = $this->argument('driver');

        try {
            $driver = $connector->resolveDriver($driverId);
        } catch (\Throwable $e) {
            $this->error("Driver [{$driverId}] is not registered. Make sure the driver package is installed.");
            return self::FAILURE;
        }

        $keys = $driver->keys();

        if ($apiKey = $this->option('api-key')) {
            $keys->setApiKey($apiKey);
            $this->info("API key stored for [{$driverId}].");
        }

        if ($botToken = $this->option('bot-token')) {
            if (method_exists($keys, 'setBotToken')) {
                $keys->setBotToken($botToken);
                $this->info("Bot token stored for [{$driverId}].");
            }
        }

        if ($this->option('backfill')) {
            $this->call('sanvex:backfill', ['driver' => $driverId]);
        }

        $this->info("Driver [{$driverId}] setup complete.");
        return self::SUCCESS;
    }
}
