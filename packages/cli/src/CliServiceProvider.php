<?php

namespace Sanvex\Cli;

use Illuminate\Support\ServiceProvider;
use Sanvex\Cli\Commands\BackfillCommand;
use Sanvex\Cli\Commands\KeygenCommand;
use Sanvex\Cli\Commands\ListCommand;
use Sanvex\Cli\Commands\MakeDriverCommand;
use Sanvex\Cli\Commands\MigrateCommand;
use Sanvex\Cli\Commands\SetupCommand;

class CliServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                KeygenCommand::class,
                MigrateCommand::class,
                SetupCommand::class,
                BackfillCommand::class,
                ListCommand::class,
                MakeDriverCommand::class,
            ]);
        }
    }
}
