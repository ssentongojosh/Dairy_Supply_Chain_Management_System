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
        Schema::create('inventories', function (Blueprint $table) 
        {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedInteger('quantity');
            $table->string('unit');
            $table->string('location',15)->nullable();
            $table->string('goods_type',10)->nullable();
            $table->integer('store_id')->nullable();
            $table->integer('batch_id')->nullable();
            $table->float('storage_condition')->nullable();
            $table->date('expiry_date')->nullable();
            //$table->enum('status',['available', 'reserved', 'expired', 'out_of_stock'])->default('available');
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('product')->onDelete('cascade');
            //$table->foreign('delivery_id')->references('id')->on('delivery')->onDelete('cascade');
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
