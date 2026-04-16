<?php

namespace Sanvex\Cli\Commands;

use Illuminate\Console\Command;

class MigrateCommand extends Command
{
    protected $signature = 'sanvex:migrate {--fresh : Drop all tables and re-run migrations}';
    protected $description = 'Run sanvex database migrations (sv_ tables)';

    public function handle(): int
    {
        $this->info('Running sanvex migrations...');

        if ($this->option('fresh')) {
            $this->call('migrate:fresh', ['--path' => 'vendor/sanvex/core/src/Database/migrations']);
        } else {
            $this->call('migrate', ['--path' => 'vendor/sanvex/core/src/Database/migrations']);
        }

        $this->info('Sanvex migrations complete.');
        return self::SUCCESS;
    }
}
