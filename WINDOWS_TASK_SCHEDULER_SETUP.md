# Windows Task Scheduler Setup for DSCMS Report Generation

## Overview

This guide explains how to set up automatic report generation using Windows Task Scheduler, which runs independently of the Laravel development server.

## Setup Instructions

### 1. Verify Paths

First, make sure the paths in `run_reports.bat` are correct for your system:

```batch
cd /d "C:\xampp\htdocs\DSCMS"  # Adjust if your project is elsewhere
php artisan reports:send-scheduled  # Uses system PHP (make sure PHP is in PATH)
```

If PHP is not in your system PATH, use the full path:

```batch
C:\xampp\php\php.exe artisan reports:send-scheduled
```

### 2. Test the Batch File

1. Open Command Prompt as Administrator
2. Navigate to your project directory: `cd C:\xampp\htdocs\DSCMS`
3. Run the batch file: `run_reports.bat`
4. Check that reports are generated and logged

### 3. Create Windows Task Scheduler Task

#### Step 1: Open Task Scheduler

- Press `Win + R`, type `taskschd.msc`, press Enter
- Or search "Task Scheduler" in the Start menu

#### Step 2: Create Basic Task

1. Click "Create Basic Task..." in the right panel
2. **Name**: "DSCMS Daily Reports"
3. **Description**: "Generate and send daily reports for DSCMS users"
4. Click "Next"

#### Step 3: Set Trigger

1. Select "Daily"
2. Click "Next"
3. Set **Start date** and **Start time** (e.g., 6:00 AM daily)
4. Set **Recur every**: 1 days
5. Click "Next"

#### Step 4: Set Action

1. Select "Start a program"
2. Click "Next"
3. **Program/script**: Browse to `C:\xampp\htdocs\DSCMS\run_reports.bat`
4. **Start in**: `C:\xampp\htdocs\DSCMS`
5. Click "Next"

#### Step 5: Finish

1. Review settings
2. Check "Open the Properties dialog for this task when I click Finish"
3. Click "Finish"

#### Step 6: Advanced Configuration

In the Properties dialog:

**General Tab:**

- Check "Run whether user is logged on or not"
- Check "Run with highest privileges"
- Select "Windows 10" in "Configure for" dropdown

**Conditions Tab:**

- Uncheck "Start the task only if the computer is on AC power"
- Check "Wake the computer to run this task"

**Settings Tab:**

- Check "Allow task to be run on demand"
- Check "Run task as soon as possible after a scheduled start is missed"
- Set "Stop the task if it runs longer than": 1 hour

### 4. Test the Scheduled Task

1. In Task Scheduler, find your task in "Task Scheduler Library"
2. Right-click and select "Run"
3. Check the "Last Run Result" column (should show "The operation completed successfully (0x0)")
4. Check the log file: `C:\xampp\htdocs\DSCMS\storage\logs\task_scheduler.log`

## Troubleshooting

### Common Issues

1. **PHP not found**

   - Add PHP to system PATH or use full path in batch file
   - Test: Open Command Prompt and type `php -v`

2. **Permission errors**

   - Run Task Scheduler as Administrator
   - Set task to "Run with highest privileges"

3. **Laravel errors**

   - Check Laravel logs: `storage\logs\laravel.log`
   - Ensure database connection works
   - Test command manually: `php artisan send:user-reports`

4. **Environment issues**
   - Ensure `.env` file exists and is configured
   - Check that all required services (MySQL) are running
   - Consider using XAMPP Control Panel to start services automatically

### Verification Commands

Test the report generation manually:

```bash
cd C:\xampp\htdocs\DSCMS
php artisan send:user-reports
```

Check logs:

```bash
# Task scheduler log
type storage\logs\task_scheduler.log

# Laravel application log
type storage\logs\laravel.log
```

## Notes

- The task runs independently of the Laravel development server (`php artisan serve`)
- Reports are generated even when no one is logged into the system
- Logs are created in `storage\logs\task_scheduler.log` for tracking
- The task uses the same database and configuration as your development environment
- For production deployment, consider using Laravel's built-in task scheduling with cron (Linux) or similar solutions
