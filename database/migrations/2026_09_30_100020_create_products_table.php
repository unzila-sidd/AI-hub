<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique();
            $table->string('category')->nullable()->index();
            $table->decimal('price', 12, 2);
            $table->decimal('cost', 12, 2)->default(0);
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('alert_stock')->default(5);
            $table->text('description')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};