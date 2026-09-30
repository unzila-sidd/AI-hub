<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_events', function (Blueprint $table) {
            $table->id();
            $table->string('entity')->index();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('action');
            $table->json('payload');
            $table->boolean('synced')->default(false)->index();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->string('last_error')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_events');
    }
};