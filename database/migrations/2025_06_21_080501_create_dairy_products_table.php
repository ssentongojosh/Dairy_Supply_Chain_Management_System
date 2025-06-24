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
       Schema::create('dairy_products', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->year('Year');
    $table->string('Factory_Name');
    $table->string('Product');
    $table->string('Unity');
    $table->string('Month');
    $table->integer('Quantity');
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dairy_products');
    }
};
