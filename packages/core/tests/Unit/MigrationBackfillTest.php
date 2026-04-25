<?php

namespace Sanvex\Core\Tests\Unit;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Sanvex\Core\Tests\CoreTestCase;

class MigrationBackfillTest extends CoreTestCase
{
    public function test_existing_rows_get_global_owner_defaults_when_owner_columns_are_added(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('sv_permissions');
        Schema::dropIfExists('sv_events');
        Schema::dropIfExists('sv_entities');
        Schema::dropIfExists('sv_accounts');
        Schema::dropIfExists('sv_integrations');
        Schema::enableForeignKeyConstraints();

        $basePath = __DIR__.'/../../src/Database/migrations/';

        $baseMigrations = [
            '2024_01_01_000001_create_sv_integrations_table.php',
            '2024_01_01_000002_create_sv_accounts_table.php',
            '2024_01_01_000003_create_sv_entities_table.php',
            '2024_01_01_000004_create_sv_events_table.php',
            '2024_01_01_000005_create_sv_permissions_table.php',
        ];

        foreach ($baseMigrations as $migrationFile) {
            $migration = require $basePath.$migrationFile;
            $migration->up();
        }

        DB::table('sv_accounts')->insert([
            'driver' => 'github',
            'key_name' => 'api_key',
            'encrypted_value' => 'enc',
            'encrypted_dek' => 'dek',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('sv_entities')->insert([
            'driver' => 'github',
            'entity_type' => 'repo',
            'entity_id' => '1',
            'data' => json_encode(['id' => 1]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('sv_events')->insert([
            'driver' => 'github',
            'event_type' => 'push',
            'payload' => json_encode(['ok' => true]),
            'status' => 'processed',
            'error' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('sv_permissions')->insert([
            'driver' => 'github',
            'resource' => 'repos',
            'action' => 'update',
            'args' => json_encode(['id' => 1]),
            'approval_token' => 'token-123',
            'status' => 'pending',
            'approved_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $ownerMigration = require $basePath.'2024_01_01_000006_add_owner_to_sv_tables.php';
        $ownerMigration->up();

        $account = DB::table('sv_accounts')->first();
        $entity = DB::table('sv_entities')->first();
        $event = DB::table('sv_events')->first();
        $permission = DB::table('sv_permissions')->first();

        $this->assertSame('global', $account->owner_type);
        $this->assertSame('default', $account->owner_id);
        $this->assertSame('global', $entity->owner_type);
        $this->assertSame('default', $entity->owner_id);
        $this->assertSame('global', $event->owner_type);
        $this->assertSame('default', $event->owner_id);
        $this->assertSame('global', $permission->owner_type);
        $this->assertSame('default', $permission->owner_id);
    }
}
