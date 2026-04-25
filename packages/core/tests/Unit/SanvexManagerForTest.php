<?php

namespace Sanvex\Core\Tests\Unit;

use Sanvex\Core\SanvexManager;
use Sanvex\Core\Tenancy\Owner;
use Sanvex\Core\Tests\CoreTestCase;
use Sanvex\Core\Tests\Stubs\FakeDriver;

class SanvexManagerForTest extends CoreTestCase
{
    public function test_for_context_isolates_driver_credentials(): void
    {
        $manager = SanvexManager::make([
            'kek' => str_repeat('k', 32),
            'drivers' => [FakeDriver::class],
        ]);

        $ownerA = new Owner('App\\Models\\User', '1');
        $ownerB = new Owner('App\\Models\\User', '2');

        $driverA = $manager->for($ownerA)->resolveDriver('fake');
        $driverB = $manager->for($ownerB)->resolveDriver('fake');
        $globalDriver = $manager->resolveDriver('fake');

        $driverA->keys()->set('api_key', 'owner-a-key');
        $driverB->keys()->set('api_key', 'owner-b-key');

        $this->assertSame('owner-a-key', $driverA->keys()->get('api_key'));
        $this->assertSame('owner-b-key', $driverB->keys()->get('api_key'));
        $this->assertNull($globalDriver->keys()->get('api_key'));
    }
}
