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
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity');
            $table->string('location',15);
            $table->string('goods_type',10);
            $table->integer('store_id');
            $table->integer('batch_id');
            $table->float('storage_condition');
            $table->date('expiry_date');
            $table->enum('status',['available', 'reserved', 'expired', 'out_of_stock'])->default('available');
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
