<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

// create_rawmaterials_orders_table migration
public function up()
{
    Schema::create('rawmaterials_orders', function (Blueprint $table) {
        $table->id();
        $table->foreignId('buyer_id')->constrained('users');
        $table->foreignId('seller_id')->constrained('users');
        $table->string('status')->default('pending');
        $table->string('payment_status')->default('unpaid');
        $table->decimal('total_amount', 10, 2);
        $table->timestamps();
    });
}


    public function down()
    {
        Schema::dropIfExists('rawmaterials_orders');
    }
};
