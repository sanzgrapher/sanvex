<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sv_integrations', function (Blueprint $table) {
            $table->id();
            $table->string('driver')->index();
            $table->string('tenant_id')->default('default')->index();
            $table->boolean('enabled')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->unique(['driver', 'tenant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sv_integrations');
    }
};
