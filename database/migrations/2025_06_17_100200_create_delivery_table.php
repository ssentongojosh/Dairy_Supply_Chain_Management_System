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
        Schema::create('delivery', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transport_id');
            $table->unsignedBigInteger('supplier_id');
            $table->integer('reciever_id');
            $table->unsignedBigInteger('store_id');
            $table->unsignedBigInteger('batch_id');
            $table->string('goods_type',10);
            $table->integer('quantity');
            $table->date('delivery_date');
            $table->time('delivery_time');
            $table->enum('delivery_status',['approved','rejected','delayed'])->default('approved');
            $table->timestamps();


            $table->foreign('transport_id')->references('id')->on('transport')->onDelete('cascade');
            $table->foreign('supplier_id')->references('id')->on('supplier')->onDelete('cascade');
            $table->foreign('store_id')->references('id')->on('store')->onDelete('cascade');
            $table->foreign('batch_id')->references('id')->on('batch')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery');
    }
};
