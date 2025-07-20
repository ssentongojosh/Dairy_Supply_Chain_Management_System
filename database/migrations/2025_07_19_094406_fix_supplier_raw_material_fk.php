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
        Schema::table('supplier_raw_material', function (Blueprint $table) {
            //error correction
            $table->dropForeign(['supplier_id']);

            // Add new FK constraint to users table
            $table->foreign('supplier_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplier_raw_material', function (Blueprint $table) {
            //
            $table->dropForeign(['supplier_id']);

            // Restore the old FK to supplier table (if you want rollback)
            $table->foreign('supplier_id')
                  ->references('id')
                  ->on('supplier')
                  ->onDelete('cascade');
        });
    }
};
