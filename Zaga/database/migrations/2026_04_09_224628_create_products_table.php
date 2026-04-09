<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category');
            $table->unsignedBigInteger('price');
            $table->unsignedBigInteger('original_price')->nullable();
            $table->unsignedTinyInteger('discount')->nullable();
            $table->decimal('rating', 3, 1)->default(4.0);
            $table->unsignedInteger('reviews')->default(0);
            $table->text('description');
            $table->json('features')->nullable();
            $table->string('sku')->nullable();
            $table->string('warranty')->nullable();
            $table->boolean('in_stock')->default(true);
            $table->unsignedInteger('stock')->default(10);
            $table->string('image')->nullable();
            $table->json('additional_images')->nullable();
            $table->boolean('credit_available')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
