<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sv_entities', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->default('default')->index();
            $table->string('driver')->index();
            $table->string('entity_type')->index();
            $table->string('entity_id')->index();
            $table->json('data')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'driver', 'entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sv_entities');
    }
};
