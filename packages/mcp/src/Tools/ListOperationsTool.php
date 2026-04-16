<?php

namespace Sanvex\Mcp\Tools;

use Sanvex\Core\ConnectorManager;

class ListOperationsTool
{
    public string $name = 'sanvex_list_operations';
    public string $description = 'List all available operations across all registered drivers';

    public function __construct(private readonly ConnectorManager $connector) {}

    public function run(array $params = []): array
    {
        $driverIds = $this->connector->getRegisteredDriverIds();
        $operations = [];

        foreach ($driverIds as $id) {
            $driver = $this->connector->resolveDriver($id);
            $methods = get_class_methods($driver);
            $resourceMethods = array_filter($methods, fn($m) => !str_starts_with($m, '__')
                && !in_array($m, ['handleWebhook', 'verifySignature', 'setManager', 'withTenant',
                    'configure', 'setKeyManager', 'keys', 'db', 'getToken', 'httpClient'])
            );

            $operations[$id] = [
                'driver' => $driver->name,
                'resources' => array_values($resourceMethods),
            ];
        }

        return $operations;
    }
}
