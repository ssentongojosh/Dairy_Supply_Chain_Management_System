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
        Schema::create('report_configurations', function (Blueprint $table) {
           
           
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Links to the users table
            $table->string('frequency'); // 'daily', 'weekly', 'biweekly', 'monthly'
            $table->time('send_time'); // e.g., '08:00:00'
            $table->tinyInteger('day_of_week')->nullable(); // 1=Mon, 7=Sun (for weekly/biweekly)
            $table->tinyInteger('day_of_month')->nullable(); // 1-31 (for monthly)
            $table->json('report_types'); // JSON array of types, e.g., ['sales', 'inventory']
            $table->string('format'); // 'excel', 'pdf'
            $table->json('notification_channels'); // JSON array, e.g., ['email', 'database']
            $table->boolean('is_active')->default(true); // Is this configuration currently active?
            $table->timestamp('last_generated_at')->nullable(); // To track when this specific config last ran successfully

            $table->timestamps();

            // unique constraint if a user can only have ONE report configuration
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_configurations');
    }
};
