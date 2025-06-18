<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customer_segmentation', function (Blueprint $table) {
            $table->bigIncrements('customer_id');
    $table->string('gender');
    $table->integer('age');
    $table->decimal('annual_income', 10, 2);
    $table->integer('spending_score');
    $table->timestamps();
        });

        // Add CHECK constraint for spending_score between 1 and 100
        DB::statement('ALTER TABLE customer_segmentation ADD CONSTRAINT chk_spending_score CHECK (spending_score >= 1 AND spending_score <= 100)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_segmentation');
    }
};
