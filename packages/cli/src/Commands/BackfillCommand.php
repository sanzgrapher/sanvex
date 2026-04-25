<?php

namespace Sanvex\Cli\Commands;

use Illuminate\Console\Command;
use Sanvex\Core\SanvexManager;
use Sanvex\Core\Tenancy\Owner;

class BackfillCommand extends Command
{
    protected $signature = 'sanvex:backfill
                            {driver : The driver to backfill}
                            {--owner-type= : Owner type for tenant-scoped backfill}
                            {--owner-id= : Owner id for tenant-scoped backfill}';
    protected $description = 'Backfill existing data from an external API into sv_entities';

    public function handle(SanvexManager $connector): int
    {
        $driverId = $this->argument('driver');
        $ownerType = $this->option('owner-type');
        $ownerId = $this->option('owner-id');

        $ownerType = is_string($ownerType) ? trim($ownerType) : $ownerType;
        $ownerId = is_string($ownerId) ? trim($ownerId) : $ownerId;

        if (($ownerType === '' || $ownerType === null) && ($ownerId === '' || $ownerId === null)) {
            $ownerType = null;
            $ownerId = null;
        } elseif ($ownerType === '' || $ownerType === null || $ownerId === '' || $ownerId === null) {
            $this->error('Both --owner-type and --owner-id must be provided together and must not be empty.');
            return self::FAILURE;
        }

        try {
            $owner = Owner::fromTypeAndId($ownerType, $ownerId);
        } catch (\InvalidArgumentException $e) {
            $this->error('Invalid owner options: '.$e->getMessage());
            return self::FAILURE;
        }

        try {
            $connector->for($owner)->resolveDriver($driverId);
        } catch (\Throwable $e) {
            $this->error("Driver [{$driverId}] is not registered.");
            return self::FAILURE;
        }

        $scope = $owner->isGlobal()
            ? 'global/default'
            : $owner->type().'/'.$owner->id();

        $this->info("Starting backfill for [{$driverId}] owner [{$scope}]...");
        $this->warn("Note: Implement driver-specific backfill logic in the driver's backfill handler.");
        $this->info("Backfill complete.");
        return self::SUCCESS;
    }
}
