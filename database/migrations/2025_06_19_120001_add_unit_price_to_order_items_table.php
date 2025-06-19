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
    if (Schema::hasColumn('order_items', 'unit_price')) {
        return; // Skip if unit_price already exists
    }
    Schema::table('order_items', function (Blueprint $table) {
        $table->decimal('unit_price', 10, 2)->default(0)->after('quantity');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('unit_price');
        });
    }
};
