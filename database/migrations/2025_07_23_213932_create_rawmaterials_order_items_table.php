<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::create('rawmaterials_order_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('order_id')->constrained('rawmaterials_orders')->onDelete('cascade');
        $table->foreignId('raw_material_id')->constrained('raw_materials');
        $table->integer('quantity');
        $table->decimal('unit_price', 10, 2);
        $table->timestamps();
    });}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rawmaterials_order_items');
    }
};
