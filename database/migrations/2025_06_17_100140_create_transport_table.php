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
        Schema::create('transport', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_id');
            //$table->unsignedBigString('vehicle_number');
            $table->unsignedBigInteger('batch_id');
            $table->string('goods_type',10);
            $table->integer('quantity');
            $table->decimal('travel_condition', 5, 2); //temperature , humidity
            $table->time('departure_time');
            $table->time('arrival_time');
            $table->string('location',15); //origin of goods
            $table->string('route',20);
            $table->enum('travel_status',['arrived','in_transit','failed']);
            $table->timestamps();


            $table->foreign('driver_id')->references('id')->on('driver')->onDelete('cascade');
            //$table->foreign('vehicle_number')->references('number_plate')->on('vehicle')->onDelete('cascade');
            $table->foreign('batch_id')->references('id')->on('batch')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transport');
    }
};
