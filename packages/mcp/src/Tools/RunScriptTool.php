<?php

namespace Sanvex\Mcp\Tools;

use Sanvex\Core\ConnectorManager;

class RunScriptTool
{
    public string $name = 'sanvex_run_script';
    public string $description = 'Execute a PHP expression with $connector in scope';

    public function __construct(private readonly ConnectorManager $connector) {}

    public function run(array $params): array
    {
        $script = $params['script'] ?? null;

        if (!$script) {
            return ['error' => 'script parameter is required'];
        }

        $connector = $this->connector;

        try {
            $result = eval("return {$script};");
            return ['result' => $result];
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
