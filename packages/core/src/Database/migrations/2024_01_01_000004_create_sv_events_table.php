<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sv_events', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->default('default')->index();
            $table->string('driver')->index();
            $table->string('event_type')->index();
            $table->json('payload')->nullable();
            $table->string('status')->default('pending');
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sv_events');
    }
};
