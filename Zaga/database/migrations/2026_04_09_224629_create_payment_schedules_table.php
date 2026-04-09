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
        Schema::create('payment_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->date('due_date');
            $table->unsignedBigInteger('amount');
            $table->unsignedBigInteger('principal');
            $table->unsignedBigInteger('interest');
            $table->unsignedBigInteger('remaining_balance');
            $table->boolean('paid')->default(false);
            $table->date('paid_date')->nullable();
            $table->unsignedBigInteger('paid_amount')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_schedules');
    }
};
