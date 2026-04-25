<?php

namespace Sanvex\Core;

use Sanvex\Core\DTOs\WebhookResult;
use Sanvex\Core\Encryption\EncryptionService;
use Sanvex\Core\Encryption\KeyManager;
use Sanvex\Core\Exceptions\ConnectorException;
use Sanvex\Core\Tenancy\Owner;
use Sanvex\Core\Tenancy\TenantContext;
use Sanvex\Core\Webhooks\WebhookProcessor;

class SanvexManager
{
    /** @var array<string, class-string<BaseDriver>> */
    private array $drivers = [];

    /** @var array<string, BaseDriver> */
    private array $driverInstances = [];

    private ?EncryptionService $encryption = null;

    private ?KeyManager $keyManager = null;

    private ?TenantContext $globalContext = null;

    /** @var array<string, TenantContext> */
    private array $tenantContexts = [];
    
    public function __construct(private array $config = [])
    {
        if (! empty($config['kek'])) {
            $this->encryption = new EncryptionService($config['kek']);
            $this->keyManager = new KeyManager($this->encryption);
        }

        foreach ($config['drivers'] ?? [] as $driverClass) {
            $this->registerDriver($driverClass);
        }
    }

    public static function make(array $config = []): self
    {
        return new self($config);
    }

    public function config(): array
    {
        return $this->config;
    }

    public function registerDriver(string $driverClass): void
    {
        /** @var BaseDriver $driver */
        $driver = new $driverClass();
        $driver->setManager($this);
        $driver->setOwner(Owner::global());
        
        if ($this->keyManager) {
            $driver->setKeyManager($this->keyManager);
        }

        $this->drivers[$driver->id] = $driverClass;
        $this->driverInstances[$driver->id] = $driver;
    }

    public function resolveDriver(string $id): BaseDriver
    {
        return $this->for(null)->resolveDriver($id);
    }

    public function for(mixed $owner): TenantContext
    {
        $resolvedOwner = Owner::resolve($owner);

        if ($resolvedOwner->isGlobal()) {
            if (! $this->globalContext) {
                $this->globalContext = new TenantContext($this, $resolvedOwner);
            }

            return $this->globalContext;
        }

        $cacheKey = $resolvedOwner->cacheKey();

        if (! isset($this->tenantContexts[$cacheKey])) {
            $this->tenantContexts[$cacheKey] = new TenantContext($this, $resolvedOwner);
        }

        return $this->tenantContexts[$cacheKey];
    }

    public function resolveDriverForOwner(string $id, Owner $owner): BaseDriver
    {
        if (! isset($this->driverInstances[$id])) {
            throw new ConnectorException("Driver [{$id}] is not registered.");
        }

        if ($owner->isGlobal()) {
            return $this->driverInstances[$id];
        }

        return $this->driverInstances[$id]->cloneForTenant($owner);
    }

    public function getRegisteredDriverIds(): array
    {
        return array_keys($this->drivers);
    }

    public function getEncryption(): ?EncryptionService
    {
        return $this->encryption;
    }

    public function getKeyManager(): ?KeyManager
    {
        return $this->keyManager;
    }

    public function processWebhook(array $headers, array $payload): WebhookResult
    {
        $processor = new WebhookProcessor();
        return $processor->process($headers, $payload, $this->driverInstances);
    }

    public function __call(string $driver, array $args): BaseDriver
    {
        return $this->for(null)->resolveDriver($driver);
    }
}
