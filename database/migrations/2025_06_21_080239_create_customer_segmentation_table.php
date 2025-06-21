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
        Schema::create('customer_segmentation', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->string('Customer_ID');
    $table->integer('Age');
    $table->string('Gender');
    $table->decimal('Annual_Income', 10, 2);
    $table->integer('Spending_Score');
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_segmentation');
    }
};
