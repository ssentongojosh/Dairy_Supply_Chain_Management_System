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
        Schema::create('vendor', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inventoriess_id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('store_id');
            $table->unsignedBigInteger('product_id');
            $table->string('name',20);
            $table->integer('telephone');
            $table->string('email',25);
            $table->string('address',15);
            $table->integer('quantity');
            $table->decimal('total_price', 10, 2);
            $table->string('delivery_location',15);
            $table->timestamps();


            $table->foreign('inventoriess_id')->references('id')->on('inventoriess')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('order')->onDelete('cascade');
            $table->foreign('store_id')->references('id')->on('store')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('product')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor');
    }
};
