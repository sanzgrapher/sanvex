<?php

namespace Sanvex\Core;

class TenantConnector
{
    public function __construct(
        private readonly ConnectorManager $manager,
        private readonly string $tenantId,
    ) {}

    public function getTenantId(): string
    {
        return $this->tenantId;
    }

    public function __call(string $driver, array $args): BaseDriver
    {
        return $this->manager->resolveDriver($driver)->withTenant($this->tenantId);
    }
}
