<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sv_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->default('default')->index();
            $table->string('driver')->index();
            $table->string('key_name');
            $table->text('encrypted_value');
            $table->text('encrypted_dek');
            $table->timestamps();

            $table->unique(['tenant_id', 'driver', 'key_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sv_accounts');
    }
};
