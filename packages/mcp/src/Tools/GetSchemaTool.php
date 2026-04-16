<?php

namespace Sanvex\Mcp\Tools;

use Sanvex\Core\ConnectorManager;

class GetSchemaTool
{
    public string $name = 'sanvex_get_schema';
    public string $description = 'Get the parameter schema for a specific driver resource operation';

    public function __construct(private readonly ConnectorManager $connector) {}

    public function run(array $params): array
    {
        $driverId = $params['driver'] ?? null;
        $resource = $params['resource'] ?? null;
        $operation = $params['operation'] ?? null;

        if (!$driverId || !$resource || !$operation) {
            return ['error' => 'driver, resource, and operation parameters are required'];
        }

        try {
            $driver = $this->connector->resolveDriver($driverId);
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }

        if (!method_exists($driver, $resource)) {
            return ['error' => "Resource [{$resource}] not found on driver [{$driverId}]"];
        }

        $resourceInstance = $driver->$resource();

        if (!method_exists($resourceInstance, $operation)) {
            return ['error' => "Operation [{$operation}] not found on resource [{$resource}]"];
        }

        $reflection = new \ReflectionMethod($resourceInstance, $operation);
        $params = [];

        foreach ($reflection->getParameters() as $param) {
            $params[] = [
                'name' => $param->getName(),
                'type' => $param->getType()?->getName() ?? 'mixed',
                'required' => !$param->isOptional(),
                'default' => $param->isOptional() ? $param->getDefaultValue() : null,
            ];
        }

        return [
            'driver' => $driverId,
            'resource' => $resource,
            'operation' => $operation,
            'parameters' => $params,
        ];
    }
}
