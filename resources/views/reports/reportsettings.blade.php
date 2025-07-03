@extends('layouts.contentNavbarLayout')
@section('title', 'Report Settings')
@section('content')
<div class="container">
        <h1>Report Settings</h1>

        <form action="#" method="POST">
            <div class="mb-5">
                <label for="frequency" class="form-label">Report Frequency:</label>
                <select name="frequency" class="form-select" id="frequency" required>
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="biweekly">Biweekly</option>
                    <option value="monthly">Monthly</option>
                </select>
            </div>

            <div class=" mb-5" id="day-of-week-div" style="display:none;">
                <label for="day_of_week" class="form-label">Day of Week (1=Mon, 7=Sun):</label>
                <input type="number" name="day_of_week" id="day_of_week" class="" min="1" max="7" placeholder="e.g., 1 for Monday">
            </div>

            <div class="" id="day-of-month-div" style="display:none;">
                <label for="day_of_month">Day of Month (1-31):</label>
                <input type="number" name="day_of_month" id="day_of_month" min="1" max="31" placeholder="e.g., 15 for 15th">
            </div>

            <div class=" mb-5">
                <label for="send_time">Send Time (HH:MM):</label>
                <input type="time" name="send_time" id="send_time" value="08:00">
            </div>

            <div class=" mb-5 card card-body">
                <label>Select Report Types:</label>
                <div class="">
                    <div class="checkbox-item">
                        <input type="checkbox" name="report_types[]" value="sales" id="sales">
                        <label for="sales">Sales Report</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" name="report_types[]" value="inventory" id="inventory">
                        <label for="inventory">Inventory Report</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" name="report_types[]" value="suppliers" id="suppliers">
                        <label for="suppliers">Key Suppliers Report</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" name="report_types[]" value="customers" id="customers">
                        <label for="customers">Key Customers Report</label>
                    </div>
                </div>
            </div>

            <div class="form mb-5">
                <label for="report_format" class="form-label">Choose Report Format:</label>
                <select name="report_format" id="report_format" class="form-select" required>
                    <option value="email">Email (Content in email body)</option>
                    <option value="excel">Excel (.xlsx attachment)</option>
                    <option value="pdf">PDF (.pdf attachment)</option>
                </select>
            </div>

            <div class="mb-5">
                <label>Choose Notification Channels:</label>
                <div class="card card-body">
                    <div class="checkbox-item">
                        <input type="checkbox" name="notification_channels[]" value="email" id="channel_email">
                        <label for="channel_email">Email</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" name="notification_channels[]" value="database" id="channel_database">
                        <label for="channel_database">System Notification</label>
                    </div>
                </div>
            </div>

            <div class="form-group checkbox-item">
                <input type="checkbox" name="is_active" id="is_active" value="1" checked>
                <label for="is_active">Enable Automatic Reports</label>
            </div>

            <div class="mb-5">
                <button type="submit" class="btn btn-primary">Save Report Settings</button>
            </div>
            <div class="">
    <label>Download Report Now:</label>
    <div class="card card-body">
        <p>Generate and download a report immediately based on your current settings.</p>
        <button type="button" id="downloadNowButton" class="btn btn-danger">Generate & Download Now</button>
    </div>
</div>

        </form>
    </div>
@endsection
@section('scripts')
<script>
        document.addEventListener('DOMContentLoaded', function () {
            const frequencySelect = document.getElementById('frequency');
            // ... (keep your existing toggleFrequencyFields and toggleNotificationChannels if you use them) ...

            const downloadNowButton = document.getElementById('downloadNowButton');

            if (downloadNowButton) {
                downloadNowButton.addEventListener('click', function() {
                    const selectedFormat = document.getElementById('report_format').value;

                    // Get all checked report types
                    const checkedReportTypes = Array.from(document.querySelectorAll('input[name="report_types[]"]:checked'))
                                                     .map(cb => cb.value);

                    // Basic validation: Ensure at least one report type is selected
                    if (checkedReportTypes.length === 0) {
                        alert('Please select at least one Report Type to generate.');
                        return; // Stop the function if no types are selected
                    }

                    const params = new URLSearchParams();
                    params.append('report_format', selectedFormat);

                    // Add each selected report type to the parameters
                    checkedReportTypes.forEach(type => {
                        params.append('report_types[]', type);
                    });

                    // Direct to the download URL with the parameters
                    window.location.href = `/reports/download-on-demand?${params.toString()}`;

                    // Optional: Provide user feedback
                    alert('Your report is being generated. It will download shortly!');
                });
            }

            // ... (your existing initial calls to toggle functions) ...
        });
    </script>
@endsection
