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
    Schema::create('milk_production', function (Blueprint $table) {
       $table->year('year')->primary();
    $table->decimal('milk_production', 10, 2);
    $table->decimal('human_population', 10, 2);
    $table->decimal('per_capita_availability', 10, 2);
    $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('milk_production');
    }
};
