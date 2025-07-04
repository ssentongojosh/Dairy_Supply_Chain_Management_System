@extends('layouts.contentNavbarLayout')

@section('title', 'Report Settings')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="fw-bold mb-1">
                                <i class="bx bx-chart me-2 text-primary"></i>Report Configuration
                            </h4>
                            <p class="text-muted mb-0">Customize your automated reports and notifications</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary" id="previewBtn">
                                <i class="bx bx-show me-1"></i>Preview
                            </button>
                            <button type="button" class="btn btn-label-danger" id="resetBtn">
                                <i class="bx bx-reset me-1"></i>Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Settings Form -->
        <div class="col-lg-8 col-12">
            <form action="#" method="POST" id="reportSettingsForm">
                @csrf
                
                <!-- Report Frequency Section -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-time me-2"></i>Schedule Configuration
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="frequency" class="form-label fw-semibold">Report Frequency</label>
                                <select name="frequency" class="form-select" id="frequency" required>
                                    <option value="">Select frequency...</option>
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="biweekly">Bi-weekly</option>
                                    <option value="monthly">Monthly</option>
                                </select>
                                <div class="form-text">How often should reports be generated?</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="send_time" class="form-label fw-semibold">Send Time</label>
                                <input type="time" name="send_time" id="send_time" class="form-control" value="08:00">
                                <div class="form-text">What time should reports be sent?</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3" id="day-of-week-div" style="display:none;">
                                <label for="day_of_week" class="form-label fw-semibold">Day of Week</label>
                                <select name="day_of_week" id="day_of_week" class="form-select">
                                    <option value="">Select day...</option>
                                    <option value="1">Monday</option>
                                    <option value="2">Tuesday</option>
                                    <option value="3">Wednesday</option>
                                    <option value="4">Thursday</option>
                                    <option value="5">Friday</option>
                                    <option value="6">Saturday</option>
                                    <option value="7">Sunday</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3" id="day-of-month-div" style="display:none;">
                                <label for="day_of_month" class="form-label fw-semibold">Day of Month</label>
                                <input type="number" name="day_of_month" id="day_of_month" class="form-control" min="1" max="31" placeholder="e.g., 15">
                                <div class="form-text">Day of the month (1-31)</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Report Types Section -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-list-check me-2"></i>Report Types
                        </h5>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="selectAllReports">
                            <label class="form-check-label" for="selectAllReports">Select All</label>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="report_types[]" value="sales" id="sales" class="form-check-input report-type-checkbox">
                                    <label for="sales" class="form-check-label">
                                        <i class="bx bx-dollar text-success me-1"></i>
                                        <strong>Sales Report</strong>
                                        <small class="d-block text-muted">Revenue, orders, and sales metrics</small>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="report_types[]" value="inventory" id="inventory" class="form-check-input report-type-checkbox">
                                    <label for="inventory" class="form-check-label">
                                        <i class="bx bx-package text-warning me-1"></i>
                                        <strong>Inventory Report</strong>
                                        <small class="d-block text-muted">Stock levels, low stock alerts</small>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="report_types[]" value="suppliers" id="suppliers" class="form-check-input report-type-checkbox">
                                    <label for="suppliers" class="form-check-label">
                                        <i class="bx bx-group text-info me-1"></i>
                                        <strong>Key Suppliers Report</strong>
                                        <small class="d-block text-muted">Top suppliers and performance</small>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="report_types[]" value="customers" id="customers" class="form-check-input report-type-checkbox">
                                    <label for="customers" class="form-check-label">
                                        <i class="bx bx-user text-primary me-1"></i>
                                        <strong>Key Customers Report</strong>
                                        <small class="d-block text-muted">Top customers and insights</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Format & Delivery Section -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-send me-2"></i>Format & Delivery
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="report_format" class="form-label fw-semibold">Report Format</label>
                                <select name="report_format" id="report_format" class="form-select" required>
                                    <option value="">Choose format...</option>
                                    <option value="excel">📊 Excel (.xlsx )</option>
                                    <option value="pdf">📄 PDF (.pdf )</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Notification Channels</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input type="checkbox" name="notification_channels[]" value="email" id="channel_email" class="form-check-input">
                                        <label for="channel_email" class="form-check-label">
                                            <i class="bx bx-envelope me-1"></i>Email
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" name="notification_channels[]" value="database" id="channel_database" class="form-check-input">
                                        <label for="channel_database" class="form-check-label">
                                            <i class="bx bx-bell me-1"></i>System
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status & Actions -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="is_active" id="is_active" value="1" class="form-check-input" checked>
                                <label for="is_active" class="form-check-label fw-semibold">
                                    <i class="bx bx-check-circle text-success me-1"></i>
                                    Enable Automatic Reports
                                </label>
                                <div class="form-text">Toggle to enable/disable scheduled report generation</div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-primary" id="saveAsDraftBtn">
                                    <i class="bx bx-save me-1"></i>Save as Draft
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-check me-1"></i>Save Settings
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Preview & Quick Actions Sidebar -->
        <div class="col-lg-4 col-12">
            <!-- Quick Download -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-download me-2"></i>Quick Download
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Generate and download a report immediately with current settings.</p>
                    <div class="d-grid">
                        <button type="button" id="downloadNowButton" class="btn btn-success">
                            <i class="bx bx-download me-1"></i>Generate & Download Now
                        </button>
                    </div>
                    <div class="mt-3">
                        <div class="progress" id="downloadProgress" style="display: none;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Preview -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-show me-2"></i>Settings Preview
                    </h5>
                </div>
                <div class="card-body">
                    <div id="settingsPreview">
                        <div class="text-center text-muted py-3">
                            <i class="bx bx-info-circle"></i>
                            <p class="mb-0">Configure settings to see preview</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Help & Tips -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-help-circle me-2"></i>Tips & Help
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item px-0 py-2">
                            <i class="bx bx-check-circle text-success me-2"></i>
                            <small>Select multiple report types for comprehensive insights</small>
                        </div>
                        <div class="list-group-item px-0 py-2">
                            <i class="bx bx-check-circle text-success me-2"></i>
                            <small>PDF format is best for formal reports</small>
                        </div>
                        <div class="list-group-item px-0 py-2">
                            <i class="bx bx-check-circle text-success me-2"></i>
                            <small>Enable both email and system notifications</small>
                        </div>
                        <div class="list-group-item px-0 py-2">
                            <i class="bx bx-check-circle text-success me-2"></i>
                            <small>Test with "Generate Now" before scheduling</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Initialize elements
    const frequencySelect = document.getElementById('frequency');
    const dayOfWeekDiv = document.getElementById('day-of-week-div');
    const dayOfMonthDiv = document.getElementById('day-of-month-div');
    const downloadNowButton = document.getElementById('downloadNowButton');
    const selectAllReports = document.getElementById('selectAllReports');
    const reportTypeCheckboxes = document.querySelectorAll('.report-type-checkbox');
    const previewBtn = document.getElementById('previewBtn');
    const resetBtn = document.getElementById('resetBtn');
    const saveAsDraftBtn = document.getElementById('saveAsDraftBtn');
    const settingsPreview = document.getElementById('settingsPreview');
    const downloadProgress = document.getElementById('downloadProgress');

    // Initialize Select2 if available
    if (typeof window.select2 !== 'undefined') {
        $('#frequency, #report_format').select2({
            minimumResultsForSearch: Infinity
        });
    }

    // Frequency change handler
    function toggleFrequencyFields() {
        const frequency = frequencySelect.value;
        
        // Hide all frequency-specific fields
        dayOfWeekDiv.style.display = 'none';
        dayOfMonthDiv.style.display = 'none';
        
        // Show relevant fields based on frequency
        if (frequency === 'weekly' || frequency === 'biweekly') {
            dayOfWeekDiv.style.display = 'block';
        } else if (frequency === 'monthly') {
            dayOfMonthDiv.style.display = 'block';
        }
        
        updatePreview();
    }

    // Select all reports functionality
    selectAllReports.addEventListener('change', function() {
        reportTypeCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updatePreview();
    });

    // Individual checkbox change handler
    reportTypeCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const allChecked = Array.from(reportTypeCheckboxes).every(cb => cb.checked);
            const someChecked = Array.from(reportTypeCheckboxes).some(cb => cb.checked);
            
            selectAllReports.checked = allChecked;
            selectAllReports.indeterminate = someChecked && !allChecked;
            
            updatePreview();
        });
    });

    // Update settings preview
    function updatePreview() {
        const frequency = frequencySelect.value;
        const sendTime = document.getElementById('send_time').value;
        const selectedReports = Array.from(reportTypeCheckboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.nextElementSibling.querySelector('strong').textContent);
        const format = document.getElementById('report_format').value;
        const isActive = document.getElementById('is_active').checked;

        let previewHtml = '';
        
        if (frequency || selectedReports.length > 0 || format) {
            previewHtml = `
                <div class="mb-3">
                    <h6 class="fw-bold text-primary">
                        <i class="bx bx-cog me-1"></i>Current Configuration
                    </h6>
                </div>
                
                ${frequency ? `
                    <div class="d-flex align-items-center mb-2">
                        <i class="bx bx-time-five text-muted me-2"></i>
                        <small><strong>Frequency:</strong> ${frequency.charAt(0).toUpperCase() + frequency.slice(1)}</small>
                    </div>
                ` : ''}
                
                ${sendTime ? `
                    <div class="d-flex align-items-center mb-2">
                        <i class="bx bx-clock text-muted me-2"></i>
                        <small><strong>Send Time:</strong> ${sendTime}</small>
                    </div>
                ` : ''}
                
                ${selectedReports.length > 0 ? `
                    <div class="d-flex align-items-start mb-2">
                        <i class="bx bx-list-ul text-muted me-2 mt-1"></i>
                        <div>
                            <small><strong>Reports:</strong></small>
                            <div class="ms-2">
                                ${selectedReports.map(report => `<div class="badge bg-label-primary me-1 mb-1">${report}</div>`).join('')}
                            </div>
                        </div>
                    </div>
                ` : ''}
                
                ${format ? `
                    <div class="d-flex align-items-center mb-2">
                        <i class="bx bx-file text-muted me-2"></i>
                        <small><strong>Format:</strong> ${format.toUpperCase()}</small>
                    </div>
                ` : ''}
                
                <div class="d-flex align-items-center">
                    <i class="bx ${isActive ? 'bx-check-circle text-success' : 'bx-x-circle text-danger'} me-2"></i>
                    <small><strong>Status:</strong> ${isActive ? 'Active' : 'Inactive'}</small>
                </div>
            `;
        } else {
            previewHtml = `
                <div class="text-center text-muted py-3">
                    <i class="bx bx-info-circle"></i>
                    <p class="mb-0">Configure settings to see preview</p>
                </div>
            `;
        }
        
        settingsPreview.innerHTML = previewHtml;
    }

    // Download now functionality
    downloadNowButton.addEventListener('click', function() {
        const selectedFormat = document.getElementById('report_format').value;
        const checkedReportTypes = Array.from(document.querySelectorAll('input[name="report_types[]"]:checked'))
                                         .map(cb => cb.value);

        if (checkedReportTypes.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Report Types Selected',
                text: 'Please select at least one report type to generate.',
                confirmButtonText: 'OK'
            });
            return;
        }

        if (!selectedFormat) {
            Swal.fire({
                icon: 'warning',
                title: 'No Format Selected',
                text: 'Please select a report format.',
                confirmButtonText: 'OK'
            });
            return;
        }

        // Show progress
        downloadProgress.style.display = 'block';
        const progressBar = downloadProgress.querySelector('.progress-bar');
        
        // Simulate progress
        let progress = 0;
        const progressInterval = setInterval(() => {
            progress += Math.random() * 30;
            if (progress > 90) progress = 90;
            progressBar.style.width = progress + '%';
        }, 200);

        const params = new URLSearchParams();
        params.append('report_format', selectedFormat);
        checkedReportTypes.forEach(type => {
            params.append('report_types[]', type);
        });

        // Simulate download process
        setTimeout(() => {
            clearInterval(progressInterval);
            progressBar.style.width = '100%';
            
            setTimeout(() => {
                downloadProgress.style.display = 'none';
                progressBar.style.width = '0%';
                
                // Actual download
                window.location.href = `/reports/download-on-demand?${params.toString()}`;
                
                Swal.fire({
                    icon: 'success',
                    title: 'Report Generated!',
                    text: 'Your report is ready for download.',
                    timer: 2000,
                    showConfirmButton: false
                });
            }, 500);
        }, 2000);
    });

    // Preview button functionality
    previewBtn.addEventListener('click', function() {
        updatePreview();
        Swal.fire({
            title: 'Settings Preview',
            html: settingsPreview.innerHTML,
            icon: 'info',
            confirmButtonText: 'OK',
            width: '600px'
        });
    });

    // Reset button functionality
    resetBtn.addEventListener('click', function() {
        Swal.fire({
            title: 'Reset Settings?',
            text: 'This will clear all current settings. Are you sure?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, reset!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('reportSettingsForm').reset();
                reportTypeCheckboxes.forEach(cb => cb.checked = false);
                selectAllReports.checked = false;
                selectAllReports.indeterminate = false;
                toggleFrequencyFields();
                updatePreview();
                
                Swal.fire({
                    icon: 'success',
                    title: 'Settings Reset',
                    text: 'All settings have been cleared.',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        });
    });

    // Save as draft functionality
    saveAsDraftBtn.addEventListener('click', function() {
        Swal.fire({
            icon: 'success',
            title: 'Draft Saved',
            text: 'Your settings have been saved as draft.',
            timer: 1500,
            showConfirmButton: false
        });
    });

    // Form submission
    document.getElementById('reportSettingsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        Swal.fire({
            title: 'Save Settings?',
            text: 'This will update your report configuration.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, save!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Here you would normally submit the form
                // this.submit();
                
                Swal.fire({
                    icon: 'success',
                    title: 'Settings Saved!',
                    text: 'Your report configuration has been updated.',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        });
    });

    // Event listeners
    frequencySelect.addEventListener('change', toggleFrequencyFields);
    
    // Listen for changes on all form elements to update preview
    document.querySelectorAll('#reportSettingsForm input, #reportSettingsForm select').forEach(element => {
        element.addEventListener('change', updatePreview);
    });

    // Initial calls
    toggleFrequencyFields();
    updatePreview();
});
</script>
@endsection
