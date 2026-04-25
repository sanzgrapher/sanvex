<?php

namespace Sanvex\Core\Tenancy;

use Sanvex\Core\BaseDriver;
use Sanvex\Core\SanvexManager;

final class TenantContext
{
    /** @var array<string, BaseDriver> */
    private array $driverInstances = [];

    public function __construct(
        private readonly SanvexManager $manager,
        private readonly Owner $owner,
    ) {}

    public function owner(): Owner
    {
        return $this->owner;
    }

    public function resolveDriver(string $id): BaseDriver
    {
        if (! isset($this->driverInstances[$id])) {
            $this->driverInstances[$id] = $this->manager->resolveDriverForOwner($id, $this->owner);
        }

        return $this->driverInstances[$id];
    }

    public function __call(string $driver, array $args): BaseDriver
    {
        return $this->resolveDriver($driver);
    }
}
