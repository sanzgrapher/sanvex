<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sv_accounts', function (Blueprint $table) {
            $table->string('owner_type')->default('global')->after('id');
            $table->string('owner_id')->default('default')->after('owner_type');
            $table->dropUnique(['driver', 'key_name']);
            $table->unique(['owner_type', 'owner_id', 'driver', 'key_name'], 'sv_accounts_owner_driver_key_unique');
            $table->index(['owner_type', 'owner_id'], 'sv_accounts_owner_index');
        });

        Schema::table('sv_entities', function (Blueprint $table) {
            $table->string('owner_type')->default('global')->after('id');
            $table->string('owner_id')->default('default')->after('owner_type');
            $table->dropUnique(['driver', 'entity_type', 'entity_id']);
            $table->unique(['owner_type', 'owner_id', 'driver', 'entity_type', 'entity_id'], 'sv_entities_owner_driver_entity_unique');
            $table->index(['owner_type', 'owner_id'], 'sv_entities_owner_index');
        });

        Schema::table('sv_events', function (Blueprint $table) {
            $table->string('owner_type')->default('global')->after('id');
            $table->string('owner_id')->default('default')->after('owner_type');
            $table->index(['owner_type', 'owner_id'], 'sv_events_owner_index');
        });

        Schema::table('sv_permissions', function (Blueprint $table) {
            $table->string('owner_type')->default('global')->after('id');
            $table->string('owner_id')->default('default')->after('owner_type');
            $table->index(['owner_type', 'owner_id'], 'sv_permissions_owner_index');
        });
    }

    public function down(): void
    {
        Schema::table('sv_accounts', function (Blueprint $table) {
            $table->dropIndex('sv_accounts_owner_index');
            $table->dropUnique('sv_accounts_owner_driver_key_unique');
            $table->unique(['driver', 'key_name']);
            $table->dropColumn(['owner_type', 'owner_id']);
        });

        Schema::table('sv_entities', function (Blueprint $table) {
            $table->dropIndex('sv_entities_owner_index');
            $table->dropUnique('sv_entities_owner_driver_entity_unique');
            $table->unique(['driver', 'entity_type', 'entity_id']);
            $table->dropColumn(['owner_type', 'owner_id']);
        });

        Schema::table('sv_events', function (Blueprint $table) {
            $table->dropIndex('sv_events_owner_index');
            $table->dropColumn(['owner_type', 'owner_id']);
        });

        Schema::table('sv_permissions', function (Blueprint $table) {
            $table->dropIndex('sv_permissions_owner_index');
            $table->dropColumn(['owner_type', 'owner_id']);
        });
    }
};
