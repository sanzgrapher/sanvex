<?php

namespace Sanvex\Cli\Commands;

use Illuminate\Console\Command;
use Sanvex\Core\SanvexManager;

class ListCommand extends Command
{
    protected $signature = 'sanvex:list';
    protected $description = 'List all configured sanvex drivers and their status';

    public function handle(SanvexManager $connector): int
    {
        $driverIds = $connector->getRegisteredDriverIds();

        if (empty($driverIds)) {
            $this->warn('No drivers registered. Add driver classes to config/sanvex.php under "drivers".');
            return self::SUCCESS;
        }

        $rows = [];
        foreach ($driverIds as $id) {
            $driver = $connector->resolveDriver($id);
            $rows[] = [
                $driver->id,
                $driver->name,
                implode(', ', $driver->authTypes),
                $driver->defaultAuthType,
            ];
        }

        $this->table(['ID', 'Name', 'Auth Types', 'Default Auth'], $rows);
        return self::SUCCESS;
    }
}
