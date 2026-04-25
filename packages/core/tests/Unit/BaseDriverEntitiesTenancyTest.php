<?php

namespace Sanvex\Core\Tests\Unit;

use Illuminate\Support\Facades\DB;
use Sanvex\Core\SanvexManager;
use Sanvex\Core\Tenancy\Owner;
use Sanvex\Core\Tests\CoreTestCase;
use Sanvex\Core\Tests\Stubs\FakeDriver;

class BaseDriverEntitiesTenancyTest extends CoreTestCase
{
    public function test_entities_are_scoped_per_owner(): void
    {
        $manager = SanvexManager::make([
            'kek' => str_repeat('k', 32),
            'drivers' => [FakeDriver::class],
        ]);

        $ownerA = new Owner('App\\Models\\Team', '7');
        $ownerB = new Owner('App\\Models\\Team', '8');

        /** @var FakeDriver $driverA */
        $driverA = $manager->for($ownerA)->resolveDriver('fake');
        /** @var FakeDriver $driverB */
        $driverB = $manager->for($ownerB)->resolveDriver('fake');

        $driverA->putEntity('repo', '123', ['name' => 'A']);
        $driverB->putEntity('repo', '123', ['name' => 'B']);

        $rowsA = $driverA->listEntities('repo');
        $rowsB = $driverB->listEntities('repo');

        $this->assertCount(1, $rowsA);
        $this->assertCount(1, $rowsB);
        $this->assertStringContainsString('"A"', (string) ($rowsA[0]['data'] ?? ''));
        $this->assertStringContainsString('"B"', (string) ($rowsB[0]['data'] ?? ''));

        $this->assertSame(2, DB::table('sv_entities')->count());
    }
}
