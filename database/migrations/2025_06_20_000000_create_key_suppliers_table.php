<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('key_suppliers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('retailer_id');
            $table->unsignedBigInteger('wholesaler_id');
            $table->timestamps();

            $table->unique(['retailer_id', 'wholesaler_id']);

            $table->foreign('retailer_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('wholesaler_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('key_suppliers');
    }
};
