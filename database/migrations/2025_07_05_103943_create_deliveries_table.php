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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();

            //item details
            $table->string('item_name');
            $table->integer('quantity');

            //delivery process
            $table->unsignedBigInteger('sender_id');   // user_id of who sent -supplier/farmer/plant
            $table->unsignedBigInteger('receiver_id'); //user_id of who received -wholesaler/retailer/plant
            $table->string('from'); //supplier premises location or plant
            $table->string('to');  // receiever premises location or plant

            //status in movement
            $table->enum('status', ['pending', 'transit', 'delivered', 'rejected'])->default('pending');
            $table->boolean('confirmed')->default(false);
            $table->text('notes')->nullable();

            //delivery confirmation date$table->timestamp('delivery_date')->nullable(); // filled by supplier/farmer when dispatching
            $table->timestamp('delivery_date')->nullable(); // filled by supplier/farmer when dispatching
            $table->timestamps();

            //relationships from user table
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade'); 
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
