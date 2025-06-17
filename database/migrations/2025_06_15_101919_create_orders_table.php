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
      // database/migrations/[timestamp]_create_orders_table.php
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->foreignId('buyer_id')->constrained('users');
    $table->foreignId('seller_id')->constrained('users');
    $table->string('status')->default('pending');
    $table->decimal('total_amount', 10, 2);
    $table->text('notes')->nullable();
    $table->foreignId('parent_order_id')->nullable()->constrained('orders');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
