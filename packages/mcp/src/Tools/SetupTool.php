<?php

namespace Sanvex\Mcp\Tools;

use Sanvex\Core\ConnectorManager;

class SetupTool
{
    public string $name = 'sanvex_setup';
    public string $description = 'Check auth status and get credential setup instructions for a driver';

    public function __construct(private readonly ConnectorManager $connector) {}

    public function run(array $params): array
    {
        $driverId = $params['driver'] ?? null;

        if (!$driverId) {
            return [
                'error' => 'driver parameter is required',
                'registered_drivers' => $this->connector->getRegisteredDriverIds(),
            ];
        }

        try {
            $driver = $this->connector->resolveDriver($driverId);
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }

        return [
            'driver' => $driver->id,
            'name' => $driver->name,
            'auth_types' => $driver->authTypes,
            'default_auth_type' => $driver->defaultAuthType,
            'instructions' => $this->getInstructions($driver->id, $driver->authTypes),
        ];
    }

    private function getInstructions(string $driver, array $authTypes): array
    {
        $instructions = [];

        if (in_array('api_key', $authTypes)) {
            $instructions['api_key'] = "Call connector->{$driver}()->keys()->setApiKey('your-api-key')";
        }

        if (in_array('oauth2', $authTypes)) {
            $instructions['oauth2'] = "Call connector->{$driver}()->keys()->set('access_token', 'your-access-token')";
        }

        return $instructions;
    }
}
