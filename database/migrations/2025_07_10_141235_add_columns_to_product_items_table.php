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
      if (!Schema::hasTable("product_items  ")) {
          return; // Table does not exist, no need to add columns
      }
        Schema::table('product_items', function (Blueprint $table) {
            // Check if columns don't exist before adding them
            if (!Schema::hasColumn('product_items', 'cost_price')) {
                $table->decimal('cost_price', 10, 2)->nullable()->after('quantity');
            }
            if (!Schema::hasColumn('product_items', 'selling_price')) {
                $table->decimal('selling_price', 10, 2)->nullable()->after('cost_price');
            }
            if (!Schema::hasColumn('product_items', 'minimum_stock')) {
                $table->integer('minimum_stock')->default(0)->after('selling_price');
            }
            if (!Schema::hasColumn('product_items', 'maximum_stock')) {
                $table->integer('maximum_stock')->nullable()->after('minimum_stock');
            }
            if (!Schema::hasColumn('product_items', 'expiry_date')) {
                $table->date('expiry_date')->nullable()->after('maximum_stock');
            }
            if (!Schema::hasColumn('product_items', 'manufacture_date')) {
                $table->date('manufacture_date')->nullable()->after('expiry_date');
            }
            if (!Schema::hasColumn('product_items', 'batch_number')) {
                $table->string('batch_number')->nullable()->after('manufacture_date');
            }
            if (!Schema::hasColumn('product_items', 'status')) {
                $table->enum('status', ['active', 'expired', 'damaged', 'sold'])->default('active')->after('batch_number');
            }

            // Add indexes
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
        Schema::table('product_items', function (Blueprint $table) {
            $table->dropColumn([
                'cost_price',
                'selling_price',
                'minimum_stock',
                'maximum_stock',
                'expiry_date',
                'manufacture_date',
                'batch_number',
                'status'
            ]);
        });
    }
};
