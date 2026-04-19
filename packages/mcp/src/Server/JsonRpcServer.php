<?php

namespace Sanvex\Mcp\Server;

use Sanvex\Core\SanvexManager;
use Throwable;

class JsonRpcServer
{
    public function __construct(private readonly SanvexManager $manager) {}

    public function handle(string $payload): ?string
    {
        $request = json_decode($payload, true);
        if (!$request) {
            return null;
        }

        $id = $request['id'] ?? null;
        $method = $request['method'] ?? '';
        $params = $request['params'] ?? [];
        
        $error = null;
        $responseData = null;

        try {
            switch ($method) {
                case 'initialize':
                    $responseData = [
                        'protocolVersion' => '2024-11-05',
                        'capabilities' => ['tools' => []],
                        'serverInfo' => ['name' => 'Sanvex Native MCP', 'version' => '1.0.0'],
                    ];
                    break;
                case 'notifications/initialized':
                    return null; // Acknowledgment, no response needed
                case 'tools/list':
                    $responseData = ['tools' => $this->getTools()];
                    break;
                case 'tools/call':
                    $responseData = ['content' => $this->callTool($params['name'], $params['arguments'] ?? [])];
                    break;
                default:
                    if ($id) {
                        $error = ['code' => -32601, 'message' => "Method '{$method}' not found"];
                    }
                    break;
            }
        } catch (Throwable $e) {
            $error = ['code' => -32000, 'message' => "Sanvex MCP Error: " . $e->getMessage()];
        }

        if ($id !== null) {
            $response = [
                'jsonrpc' => '2.0',
                'id' => $id,
            ];
            
            if ($error) {
                $response['error'] = $error;
            } else {
                $response['result'] = $responseData;
            }
            
            return json_encode($response);
        }

        return null;
    }

    private function getTools(): array
    {
        return [
            [
                'name' => 'sanvex_action',
                'description' => 'Perform an action on a Sanvex integrated driver (github, slack, linear, notion, gmail).',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'driver' => [
                            'type' => 'string',
                            'description' => 'The integration driver. e.g. github, gmail, slack, linear, notion'
                        ],
                        'resource' => [
                            'type' => 'string',
                            'description' => 'The resource to interact with. e.g. repositories, issues'
                        ],
                        'action' => [
                            'type' => 'string',
                            'description' => 'The action to perform. e.g. list, get, create'
                        ],
                        'args' => [
                            'type' => 'object',
                            'description' => 'Key-value map of arguments for the action.'
                        ]
                    ],
                    'required' => ['driver', 'resource', 'action']
                ]
            ],
            [
                'name' => 'sanvex_list_operations',
                'description' => 'List all available drivers, resources, and methods in the system.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => (object)[],
                ]
            ]
        ];
    }

    private function callTool(string $name, array $args): array
    {
        if ($name === 'sanvex_list_operations') {
            $driverIds = $this->manager->getRegisteredDriverIds();
            $operations = [];

            foreach ($driverIds as $id) {
                $driver = $this->manager->resolveDriver($id);
                $methods = get_class_methods($driver);
                $resourceMethods = array_filter($methods, fn($m) => !str_starts_with($m, '__') && !in_array($m, ['handleWebhook', 'verifySignature', 'setManager', 'configure', 'setKeyManager', 'keys', 'db', 'getToken', 'httpClient']));
                $operations[$id] = ['driver' => $driver->name, 'resources' => array_values($resourceMethods)];
            }
            
            return [['type' => 'text', 'text' => json_encode($operations, JSON_PRETTY_PRINT)]];
        }

        if ($name === 'sanvex_action') {
            $driverId = $args['driver'] ?? null;
            $resource = $args['resource'] ?? null;
            $action = $args['action'] ?? null;
            $actionArgs = $args['args'] ?? [];

            $driver = $this->manager->resolveDriver($driverId);
            $module = $driver->{$resource}();
            $result = $module->{$action}($actionArgs);

            // Token limit protection for AI
            if (is_array($result) && count($result) > 10) {
                if (isset($result[0]) && is_array($result[0])) {
                    $result = array_slice($result, 0, 8);
                    $result[] = ['note' => 'Results truncated for token limits. Showing top 8 items.'];
                }
            }

            $output = is_string($result) ? $result : json_encode($result, JSON_PRETTY_PRINT);
            return [['type' => 'text', 'text' => $output]];
        }

        throw new \Exception("Unknown MCP tool: {$name}");
    }
}
