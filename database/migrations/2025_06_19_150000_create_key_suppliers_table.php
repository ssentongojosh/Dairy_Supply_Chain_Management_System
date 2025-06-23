<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('key_suppliers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('retailer_id');
            $table->unsignedBigInteger('wholesaler_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('category')->nullable();
            $table->timestamps();

            $table->foreign('retailer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('wholesaler_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('key_suppliers');
    }
};
