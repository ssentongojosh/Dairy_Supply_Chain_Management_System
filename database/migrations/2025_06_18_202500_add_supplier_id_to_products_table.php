<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Check if supplier_id doesn't exist, then add it
            if (!Schema::hasColumn('products', 'supplier_id')) {
                $table->unsignedBigInteger('supplier_id')->nullable()->after('price');
                $table->foreign('supplier_id')->references('id')->on('users');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'supplier_id')) {
                $table->dropForeign(['supplier_id']);
                $table->dropColumn('supplier_id');
            }
        });
    }
};
