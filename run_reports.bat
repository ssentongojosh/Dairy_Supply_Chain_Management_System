@echo off
REM Windows batch file to run Laravel report generation
REM This can be used with Windows Task Scheduler

REM Change to the Laravel project directory
cd /d "C:\xampp\htdocs\DSCMS"

REM Run the Laravel command using PHP directly (adjust path as needed)
php artisan reports:send-scheduled

REM Log the execution with timestamp
echo %date% %time% - Report generation completed >> storage\logs\task_scheduler.log
