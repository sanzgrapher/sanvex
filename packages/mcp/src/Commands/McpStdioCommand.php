<?php

namespace Sanvex\Mcp\Commands;

use Illuminate\Console\Command;
use Sanvex\Mcp\Server\JsonRpcServer;
use Sanvex\Core\SanvexManager;

class McpStdioCommand extends Command
{
    protected $signature = 'sanvex:mcp-stdio';
    protected $description = 'Run the Sanvex native MCP server over standard I/O for local AI agents (Cursor, Claude Desktop).';

    public function handle(SanvexManager $manager)
    {
        $server = new JsonRpcServer($manager);
        
        $stdin = fopen('php://stdin', 'r');
        $stdout = fopen('php://stdout', 'w');

        while ($line = fgets($stdin)) {
            $line = trim($line);
            if (!$line) {
                continue;
            }

            $response = $server->handle($line);
            
            if ($response) {
                fwrite($stdout, $response . "\n");
                fflush($stdout);
            }
        }

        fclose($stdin);
        fclose($stdout);
        
        return 0;
    }
}
