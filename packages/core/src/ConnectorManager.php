<?php

namespace Sanvex\Core;

use Sanvex\Core\DTOs\WebhookResult;
use Sanvex\Core\Encryption\EncryptionService;
use Sanvex\Core\Encryption\KeyManager;
use Sanvex\Core\Exceptions\ConnectorException;
use Sanvex\Core\Webhooks\WebhookProcessor;

class ConnectorManager
{
    private array $drivers = [];
    private array $driverInstances = [];
    private bool $multiTenancy = false;
    private ?EncryptionService $encryption = null;
    private ?KeyManager $keyManager = null;
    private array $config = [];

    private function __construct(array $config)
    {
        $this->config = $config;
        $this->multiTenancy = $config['multi_tenancy'] ?? false;

        if (!empty($config['kek'])) {
            $this->encryption = new EncryptionService($config['kek']);
            $this->keyManager = new KeyManager($this->encryption);
        }

        foreach ($config['drivers'] ?? [] as $driverClass) {
            $this->registerDriver($driverClass);
        }
    }

    public static function make(array $config): self
    {
        return new self($config);
    }

    public function registerDriver(string $driverClass): void
    {
        /** @var BaseDriver $driver */
        $driver = new $driverClass();
        $driver->setManager($this);
        if ($this->keyManager) {
            $driver->setKeyManager($this->keyManager);
        }
        $this->drivers[$driver->id] = $driverClass;
        $this->driverInstances[$driver->id] = $driver;
    }

    public function resolveDriver(string $id): BaseDriver
    {
        if (!isset($this->driverInstances[$id])) {
            throw new ConnectorException("Driver [{$id}] is not registered.");
        }

        return $this->driverInstances[$id];
    }

    public function forTenant(string $tenantId): TenantConnector
    {
        return new TenantConnector($this, $tenantId);
    }

    public function processWebhook(array $headers, array $payload, ?string $tenantId = null): WebhookResult
    {
        $processor = new WebhookProcessor();
        return $processor->process($headers, $payload, $tenantId, $this->driverInstances);
    }

    public function getEncryption(): ?EncryptionService
    {
        return $this->encryption;
    }

    public function getKeyManager(): ?KeyManager
    {
        return $this->keyManager;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function isMultiTenant(): bool
    {
        return $this->multiTenancy;
    }

    public function getRegisteredDriverIds(): array
    {
        return array_keys($this->driverInstances);
    }

    public function __call(string $driver, array $args): BaseDriver
    {
        return $this->resolveDriver($driver);
    }
}
