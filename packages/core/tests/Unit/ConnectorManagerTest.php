<?php

use Sanvex\Core\ConnectorManager;
use Sanvex\Core\TenantConnector;
use Sanvex\Core\Encryption\EncryptionService;

it('creates a connector manager', function () {
    $connector = ConnectorManager::make(['kek' => base64_encode(random_bytes(32))]);
    expect($connector)->toBeInstanceOf(ConnectorManager::class);
});

it('returns tenant connector for multi-tenant mode', function () {
    $connector = ConnectorManager::make([
        'multi_tenancy' => true,
        'kek' => base64_encode(random_bytes(32)),
    ]);
    $tenant = $connector->forTenant('user_123');
    expect($tenant)->toBeInstanceOf(TenantConnector::class);
    expect($tenant->getTenantId())->toBe('user_123');
});

it('encrypts and decrypts data correctly', function () {
    $service = new EncryptionService(base64_encode(random_bytes(32)));
    $dek = $service->generateDek();
    $data = 'secret-api-key-value';
    $encrypted = $service->encrypt($data, $dek);
    expect($encrypted)->not->toBe($data);
    expect($service->decrypt($encrypted, $dek))->toBe($data);
});

it('creates a connector manager without kek', function () {
    $connector = ConnectorManager::make([]);
    expect($connector)->toBeInstanceOf(ConnectorManager::class);
    expect($connector->getEncryption())->toBeNull();
});

it('throws when resolving unregistered driver', function () {
    $connector = ConnectorManager::make([]);
    expect(fn() => $connector->resolveDriver('nonexistent'))
        ->toThrow(\Sanvex\Core\Exceptions\ConnectorException::class);
});

it('generates unique dek each time', function () {
    $service = new EncryptionService(base64_encode(random_bytes(32)));
    $dek1 = $service->generateDek();
    $dek2 = $service->generateDek();
    expect($dek1)->not->toBe($dek2);
});

it('encrypts dek and decrypts back', function () {
    $service = new EncryptionService(base64_encode(random_bytes(32)));
    $dek = $service->generateDek();
    $encrypted = $service->encryptDek($dek);
    expect($encrypted)->not->toBe($dek);
    expect($service->decryptDek($encrypted))->toBe($dek);
});
