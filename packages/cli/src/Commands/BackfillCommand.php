<?php

namespace Sanvex\Cli\Commands;

use Illuminate\Console\Command;
use Sanvex\Core\ConnectorManager;

class BackfillCommand extends Command
{
    protected $signature = 'sanvex:backfill {driver : The driver to backfill}';
    protected $description = 'Backfill existing data from an external API into sv_entities';

    public function handle(ConnectorManager $connector): int
    {
        $driverId = $this->argument('driver');

        try {
            $connector->resolveDriver($driverId);
        } catch (\Throwable $e) {
            $this->error("Driver [{$driverId}] is not registered.");
            return self::FAILURE;
        }

        $this->info("Starting backfill for [{$driverId}]...");
        $this->warn("Note: Implement driver-specific backfill logic in the driver's backfill handler.");
        $this->info("Backfill complete.");
        return self::SUCCESS;
    }
}
