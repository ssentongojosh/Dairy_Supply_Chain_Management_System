<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   protected $guarded = [];

    public function up()
{
    Schema::create('business_customers', function (Blueprint $table) {
        $table->bigInteger('customer_id'); // or $table->integer('customer_id');
        $table->id();
        $table->string('business_type');
        $table->string('location');
        $table->bigInteger('annual_revenue');
        $table->integer('order_frequency');
        $table->integer('total_quantity_purchased');
        $table->string('product');
        $table->integer('quantity');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_customers');
    }
};
