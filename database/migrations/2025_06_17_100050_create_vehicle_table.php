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
        Schema::create('vehicle', function (Blueprint $table) {
            $table->string('vehicle_number',8)->primary();
            $table->string('owner_name',20);
            $table->string('category',15);
            $table->enum('vehicle_status',['roadworthy','minor_fault','serviced','under_repair'])->default('roadworthy');
            $table->enum('police_tatus',['clear','ticket_issued','eps_defaulter','red_flagged'])->default('clear');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle');
    }
};
