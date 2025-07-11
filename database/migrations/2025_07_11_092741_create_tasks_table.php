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
       if (Schema::hasTable('tasks')) {
                return; // Table already exists, no need to create it again
            }
        Schema::create('tasks', function (Blueprint $table) {


            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null')->comment('Assigned user ID');
            $table->string('type')->comment('e.g., product_delivery, premises_inspection, worker_assignment, milk_pickup');
            $table->string('description', 500); // A detailed description of the task
            $table->date('due_date')->nullable();
            $table->string('priority')->default('medium')->comment('e.g., low, medium, high, urgent'); // Added priority field
            $table->string('status')->default('pending')->comment('e.g., pending, assigned, in_progress, completed, failed, cancelled, overdue');

            // Polymorphic relation to link task to its source (Order, Customer, Inventory)
            $table->morphs('related'); // Adds related_id (BIGINT) and related_type (STRING) columns

            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps(); // created_at and updated_at

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
