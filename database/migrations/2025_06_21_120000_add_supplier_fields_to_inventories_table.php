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
        Schema::table('inventories', function (Blueprint $table) {
            // Add threshold column (alias for reorder_point for supplier functionality)
            $table->integer('threshold')->default(10)->after('reorder_point');
            
            // Add unit_price column (for supplier cost price)
            $table->decimal('unit_price', 10, 2)->nullable()->after('selling_price');
            
            // Add index for better performance on threshold queries
            $table->index(['user_id', 'threshold']);
            $table->index(['quantity', 'threshold']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'threshold']);
            $table->dropIndex(['quantity', 'threshold']);
            $table->dropColumn(['threshold', 'unit_price']);
        });
    }
};
