<?php


namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\IssueApiTokenCommand::class, // Add your command here
        \App\Console\Commands\CheckProductThreshold::class,
        \App\Console\Commands\CheckRawMaterialThresholds::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
         // Schedule the command to run daily at a specific time
        // We've set send_time in config as HH:MM, so let's run this AFTER that time
        // For example, if users set reports for 08:00 AM, run the command at 08:05 AM.
        $schedule->command('reports:send-scheduled')
                 ->dailyAt('08:05') // Runs every day at 08:05 AM
                 ->timezone('Africa/Kampala') // Set this to your application's timezone
                 ->onSuccess(function () {
                    // This callback is executed if the command runs successfully
                    // Log::info('reports:send-scheduled command ran successfully.');
                 })
                 ->onFailure(function () {
                    // This callback is executed if the command fails
                    // Log::error('reports:send-scheduled command failed!');
                 });

        // You can add more schedules if needed, e.g., to clear old logs
        // $schedule->command('log:clear')->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
