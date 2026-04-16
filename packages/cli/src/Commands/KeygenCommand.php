<?php

namespace Sanvex\Cli\Commands;

use Illuminate\Console\Command;

class KeygenCommand extends Command
{
    protected $signature = 'sanvex:keygen';
    protected $description = 'Generate a new SANVEX_KEK encryption key';

    public function handle(): int
    {
        $key = base64_encode(random_bytes(32));
        $this->line("SANVEX_KEK={$key}");
        $this->info('Add this to your .env file.');
        return self::SUCCESS;
    }
}
