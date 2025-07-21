Phase 1: Foundation Setup (Database & Models)
Task 1.1: Create Report Notifications Table
Create migration for report_notifications table
Fields: user_id, report_name, report_types (JSON), format, file_path, file_name, file_size, status, error_message, generated_at, is_read
Add proper indexes for performance
Task 1.2: Create ReportNotification Model
Model with proper relationships to User
Accessors for formatted file size and report types
Methods for marking as read
JSON casting for report_types array
Phase 2: Notification System Integration
Task 2.1: Create Laravel Notification Class
ReportGeneratedSystemNotification class extending Laravel's Notification
Database channel integration (works with your existing notifications table)
Proper data structure for navbar display
Success/failure notification variants
Task 2.2: Create Notification Service
ReportNotificationService to manage notification logic
Check user's notification preferences (system vs email)
Create report notifications and Laravel notifications
Mark notifications as read functionality
Phase 3: Report Generation Integration
Task 3.1: Update ReportGeneratorService
Add notification service dependency injection
Create generateAndStoreReportWithNotification() method
Integrate notification sending after successful/failed report generation
Maintain backward compatibility with existing methods
Task 3.2: Update ReportConfigurationController
Inject ReportNotificationService
Update downloadOnDemand() method to send notifications
Add new route for downloading files from notifications
Add downloadNotificationFile() method
Phase 4: Scheduled Report Generation
Task 4.1: Create Report Generation Command
Artisan command to process scheduled reports
Query ReportConfiguration for active scheduled reports
Generate reports based on frequency (daily, weekly, monthly)
Send notifications to users with "system" selected
Task 4.2: Setup Task Scheduling
Add command to Laravel's task scheduler
Configure appropriate frequency (e.g., daily at specific times)
Handle timezone considerations for user preferences
Phase 5: Frontend Integration
Task 5.1: Update Navbar Notification System
Enhance existing notification dropdown to handle report notifications
Add specific styling for report notification types
Include download buttons for successful reports
Add "mark as read" functionality
Task 5.2: Update Report Settings UI
Ensure "System" option in notification channels works properly

Show notification preferences clearly
Phase 6: Notification Management
Task 6.1: Create Notification Management Routes
Route for marking single notification as read
Route for marking all notifications as read
Route for downloading report files from notifications
API endpoints for AJAX functionality
Task 6.2: Create Notification Management Pages
Page to view all report notifications
Filter by read/unread status
Bulk actions for notifications
Download history for generated reports
Phase 7: User Experience Enhancements
Task 7.1: Real-time Notifications (Optional)
WebSocket/Pusher integration for instant notifications
Browser notifications for important alerts
Update notification counts in real-time
Task 7.2: Email Integration
Ensure email notifications still work alongside system notifications
Allow users to choose both email AND system notifications
Update existing mail templates if needed
