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
        Schema::table('tasks', function (Blueprint $table) {
            // Add wholesaler_id, nullable as general forecasts won't have it
            $table->string('wholesaler_id')->nullable()->after('type');
            // Add forecast start and end dates, nullable if task not from forecast
            $table->date('forecast_start_date')->nullable()->after('wholesaler_id');
            $table->date('forecast_end_date')->nullable()->after('forecast_start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['wholesaler_id', 'forecast_start_date', 'forecast_end_date']);
        });
    }
};
