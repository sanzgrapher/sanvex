<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sv_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('driver')->index();
            $table->string('tenant_id')->default('default')->index();
            $table->string('resource');
            $table->string('action');
            $table->json('args')->nullable();
            $table->string('approval_token')->unique();
            $table->string('status')->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sv_permissions');
    }
};
