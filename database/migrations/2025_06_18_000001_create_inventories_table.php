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
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // The retailer/user who owns this inventory
            $table->foreignId('product_id')->constrained()->onDelete('cascade'); // The product being tracked
            $table->integer('quantity')->default(0); // Current stock quantity
            $table->integer('reorder_point')->default(10); // Minimum stock before reorder
            $table->decimal('unit_cost', 10, 2)->nullable(); // Cost per unit for this retailer
            $table->decimal('selling_price', 10, 2)->nullable(); // Selling price for this retailer
            $table->string('location')->nullable(); // Storage location (shelf, warehouse, etc.)
            $table->date('last_restocked_at')->nullable(); // When was this last restocked
            $table->timestamps();

            // Ensure one inventory record per user-product combination
            $table->unique(['user_id', 'product_id']);

            // Add indexes for better performance
            $table->index(['user_id', 'quantity']);
            $table->index(['quantity', 'reorder_point']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
