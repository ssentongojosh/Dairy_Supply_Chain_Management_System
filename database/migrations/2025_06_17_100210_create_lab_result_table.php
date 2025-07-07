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
        Schema::create('lab_result', function (Blueprint $table) {
            $table->id();
            $table->integer('sample_id');
            $table->unsignedBigInteger('lab_tech_id');
            $table->unsignedBigInteger('lab_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->unsignedBigInteger('inventoriess_id');
            $table->unsignedBigInteger('delivery_id');
            $table->date('test_date');
            $table->decimal('fat_content', 5, 2);
            $table->decimal('protein_level', 5, 2);
            $table->decimal('php_level', 2, 1);
            $table->enum('final_result',['approved', 'rejected', 'needs_review']);
            $table->enum('recommenation',['set_for_processing','set_for_packaging','rejected'])->default('set_for_processing');
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('product')->onDelete('cascade');
            $table->foreign('lab_tech_id')->references('id')->on('lab_tech')->onDelete('cascade');
            $table->foreign('lab_id')->references('id')->on('lab')->onDelete('cascade');
            $table->foreign('batch_id')->references('id')->on('batch')->onDelete('cascade');
            $table->foreign('inventoriess_id')->references('id')->on('inventoriess')->onDelete('cascade');
            $table->foreign('delivery_id')->references('id')->on('delivery')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_result');
    }
};
