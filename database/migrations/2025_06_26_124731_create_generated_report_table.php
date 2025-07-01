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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('report_name'); // e.g., "Weekly Sales Report"
            $table->string('frequency'); // 'daily', 'weekly', 'monthly', 'on-demand'
            $table->json('report_types')->nullable(); // e.g., ['sales', 'inventory']
            $table->string('format'); // 'excel', 'pdf'
            $table->string('file_path'); // Path relative to storage disk, e.g., 'reports/user_id/report_name.xlsx'
            $table->string('file_name'); // Display name for the file
            $table->unsignedBigInteger('file_size')->nullable(); // Size in bytes
            $table->date('report_start_date');
            $table->date('report_end_date');
            $table->timestamp('generated_at')->useCurrent(); // When the file was created
            $table->string('status')->default('success'); // 'success', 'failed'
            $table->text('error_message')->nullable(); // Store error details if status is 'failed'
            $table->unsignedInteger('download_count')->default(0);
            $table->timestamp('expires_at')->nullable(); // For automatic deletion of old reports

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
