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
        Schema::create('product_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // wholesaler, retailer, plant manager
            $table->integer('quantity')->default(0);
            $table->decimal('cost_price', 10, 2)->nullable(); // What they paid for it
            $table->decimal('selling_price', 10, 2)->nullable(); // What they sell it for
            $table->integer('minimum_stock')->default(0); // Reorder level
            $table->integer('maximum_stock')->nullable(); // Max capacity
            $table->date('expiry_date')->nullable(); // For perishable products
            $table->date('manufacture_date')->nullable();
            $table->string('batch_number')->nullable();
            $table->enum('status', ['active', 'expired', 'damaged', 'sold'])->default('active');
            $table->timestamps();
            
            // Prevent duplicate product-user combinations for same batch
            $table->unique(['product_id', 'user_id', 'batch_number']);
            
            // Indexes for better performance
            $table->index(['user_id', 'status']);
            $table->index(['product_id', 'status']);
            $table->index('expiry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_items');
    }
};
