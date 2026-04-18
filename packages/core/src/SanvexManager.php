<?php

namespace Sanvex\Core;

use Sanvex\Core\Encryption\EncryptionService;
use Sanvex\Core\Encryption\KeyManager;
use Sanvex\Core\Exceptions\ConnectorException;

class SanvexManager
{
    private array $drivers = [];
    private array $driverInstances = [];
    private ?EncryptionService $encryption = null;
    private ?KeyManager $keyManager = null;
    
    public function __construct(private array $config = [])
    {
        if (!empty($config['kek'])) {
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

    public function getRegisteredDriverIds(): array
    {
        return array_keys($this->driverInstances);
    }

    public function getEncryption(): ?EncryptionService
    {
        return $this->encryption;
    }

    public function getKeyManager(): ?KeyManager
    {
        return $this->keyManager;
    }

    public function __call(string $driver, array $args): BaseDriver
    {
        return $this->resolveDriver($driver);
    }
}
