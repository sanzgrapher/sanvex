<?php

namespace Sanvex\Core\Tests\Unit;

use Sanvex\Core\Encryption\EncryptionService;
use Sanvex\Core\Encryption\KeyManager;
use Sanvex\Core\Tenancy\Owner;
use Sanvex\Core\Tests\CoreTestCase;

class KeyManagerTenancyTest extends CoreTestCase
{
    public function test_credentials_are_isolated_per_owner(): void
    {
        $manager = new KeyManager(new EncryptionService(str_repeat('z', 32)));

        $ownerA = new Owner('App\\Models\\User', '42');
        $ownerB = new Owner('App\\Models\\User', '99');

        $manager->storeCredential('github', 'api_key', 'token-a', $ownerA);
        $manager->storeCredential('github', 'api_key', 'token-b', $ownerB);

        $this->assertSame('token-a', $manager->getCredential('github', 'api_key', $ownerA));
        $this->assertSame('token-b', $manager->getCredential('github', 'api_key', $ownerB));
        $this->assertNull($manager->getCredential('github', 'missing', $ownerA));
    }
}
