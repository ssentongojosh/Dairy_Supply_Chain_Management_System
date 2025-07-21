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
      if (Schema::hasColumn('product_raw_material', 'product_id')) {
            return; // Column already exists, skip migration
        }
        Schema::table('product_raw_material', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_raw_material', function (Blueprint $table) {
            //
            $table->dropColumn('product_id');
        });
    }
};
