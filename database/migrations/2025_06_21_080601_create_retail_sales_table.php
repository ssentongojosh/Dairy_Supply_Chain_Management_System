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
        Schema::create('retail_sales', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->string('invoice_no');
    $table->string('customer_id');
    $table->string('gender');
    $table->integer('age');
    $table->string('category');
    $table->integer('quantity');
    $table->decimal('price', 10, 2);
    $table->string('payment_method');
    $table->date('invoice_date');
    $table->string('shopping_mall');
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retail_sales');
    }
};
