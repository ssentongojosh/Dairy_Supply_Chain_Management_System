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
        Schema::table('product_raw_material', function (Blueprint $table) {
            //
            $table->foreignId('raw_material_id')->constrained('raw_materials')->onDelete('cascade');
            $table->float('quantity_required');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_raw_material', function (Blueprint $table) {
            //
            $table->dropForeign(['raw_material_id']);
            $table->dropColumn(['raw_material_id', 'quantity_required']);
        });
    }
};
