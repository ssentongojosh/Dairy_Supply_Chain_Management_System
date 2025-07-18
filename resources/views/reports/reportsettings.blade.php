@extends('layouts.contentNavbarLayout')

@section('title', 'Report Settings')

@section('vendor-style')
<!-- No external vendor styles needed - using Materio Bootstrap theme -->
<style>
/* Enhanced Progress Feedback Styles */
#downloadProgressContainer {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid #28a745;
}

#downloadProgressContainer .progress {
    height: 8px;
    border-radius: 4px;
    background-color: #e9ecef;
}

#downloadProgressContainer .progress-bar {
    border-radius: 4px;
    transition: width 0.3s ease;
}

#progressText {
    font-weight: 600;
    color: #495057;
}

#progressStatus {
    font-style: italic;
    color: #6c757d;
}

#downloadResult .alert {
    border: none;
    border-radius: 8px;
    padding: 12px 15px;
}

#downloadResult .alert-success {
    background-color: #d1e7dd;
    color: #0a3622;
    border-left: 4px solid #28a745;
}

#downloadResult .alert-danger {
    background-color: #f8d7da;
    color: #58151c;
    border-left: 4px solid #dc3545;
}

/* Enhanced Alert Styles */
.alert {
    border: none;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.alert-success {
    background: linear-gradient(135deg, #d1e7dd 0%, #a3d9a5 100%);
    border-left: 5px solid #28a745;
    color: #0a3622;
}

.alert-danger {
    background: linear-gradient(135deg, #f8d7da 0%, #f5b2b8 100%);
    border-left: 5px solid #dc3545;
    color: #721c24;
}

.alert-icon {
    flex-shrink: 0;
}

.alert-heading {
    font-weight: 600;
    color: inherit;
    margin-bottom: 8px;
}

/* Success Animation */
@keyframes slideInFromTop {
    0% {
        transform: translateY(-100%);
        opacity: 0;
    }
    100% {
        transform: translateY(0);
        opacity: 1;
    }
}

@keyframes bounceIn {
    0% {
        transform: scale(0.3);
        opacity: 0;
    }
    50% {
        transform: scale(1.05);
    }
    70% {
        transform: scale(0.9);
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

.alert.show {
    animation: slideInFromTop 0.5s ease-out;
}

#successAlert {
    animation: slideInFromTop 0.6s ease-out, bounceIn 0.8s ease-out 0.3s;
}

.alert-success .alert-icon i {
    animation: bounceIn 0.6s ease-out 0.8s both;
}

.btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.btn.generating {
    animation: pulse 2s infinite;
}

/* Validation Highlighting Styles */
.validation-error {
    border: 2px solid #dc3545 !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
}

.validation-error-text {
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.required-indicator {
    color: #dc3545;
    font-weight: bold;
}

/* Shake animation for validation errors */
@keyframes shake {
    0%, 20%, 40%, 60%, 80% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
}

.shake {
    animation: shake 0.5s ease-in-out;
}
</style>
@endsection

@section('vendor-script')
<!-- No external vendor scripts needed - using Materio theme components -->
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
                                <i class="bx bx-cog me-2"></i>Report Settings
                            </h4>
                            <p class="text-muted mb-0">Configure automated report generation and delivery preferences</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary" id="previewBtn">
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
            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" id="successAlert">
                    <div class="d-flex align-items-center">
                        <div class="alert-icon">
                            <i class="bx bx-check-circle fs-4 me-3"></i>
                        </div>
                        <div>
                            <h6 class="alert-heading mb-1">Settings Saved Successfully!</h6>
                            <div>{{ session('success') }}</div>
                            <small class="text-muted">Your report configuration is now active and ready to use.</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <div class="d-flex align-items-center">
                        <div class="alert-icon">
                            <i class="bx bx-x-circle fs-4 me-3"></i>
                        </div>
                        <div>
                            <h6 class="alert-heading mb-1">Save Failed</h6>
                            <div>{{ session('error') }}</div>
                            <small class="text-muted">Please check your settings and try again.</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <div class="d-flex align-items-start">
                        <div class="alert-icon">
                            <i class="bx bx-error fs-4 me-3 mt-1"></i>
                        </div>
                        <div>
                            <h6 class="alert-heading mb-2">Validation Errors</h6>
                            <ul class="mb-0 list-unstyled">
                                @foreach($errors->all() as $error)
                                    <li class="mb-1">
                                        <i class="bx bx-chevron-right me-1"></i>{{ $error }}
                                    </li>
                                @endforeach
                            </ul>
                            <small class="text-muted">Please fix the above issues and try again.</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('report-settings.store') }}" method="POST" id="reportSettingsForm">
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
                                    <option value="daily" {{ (old('frequency', $configuration->frequency ?? '') == 'daily') ? 'selected' : '' }}>Daily</option>
                                    <option value="weekly" {{ (old('frequency', $configuration->frequency ?? '') == 'weekly') ? 'selected' : '' }}>Weekly</option>
                                    <option value="biweekly" {{ (old('frequency', $configuration->frequency ?? '') == 'biweekly') ? 'selected' : '' }}>Bi-weekly</option>
                                    <option value="monthly" {{ (old('frequency', $configuration->frequency ?? '') == 'monthly') ? 'selected' : '' }}>Monthly</option>
                                </select>
                                <div class="form-text">How often should reports be generated?</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="send_time" class="form-label fw-semibold">Send Time</label>
                                <input type="time" name="send_time" id="send_time" class="form-control" value="{{ old('send_time', $configuration->send_time ?? '08:00') }}">
                                <div class="form-text">What time should reports be sent?</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3" id="day-of-week-div" style="display:none;">
                                <label for="day_of_week" class="form-label fw-semibold">Day of Week</label>
                                <select name="day_of_week" id="day_of_week" class="form-select">
                                    <option value="">Select day...</option>
                                    <option value="1" {{ (old('day_of_week', $configuration->day_of_week ?? '') == '1') ? 'selected' : '' }}>Monday</option>
                                    <option value="2" {{ (old('day_of_week', $configuration->day_of_week ?? '') == '2') ? 'selected' : '' }}>Tuesday</option>
                                    <option value="3" {{ (old('day_of_week', $configuration->day_of_week ?? '') == '3') ? 'selected' : '' }}>Wednesday</option>
                                    <option value="4" {{ (old('day_of_week', $configuration->day_of_week ?? '') == '4') ? 'selected' : '' }}>Thursday</option>
                                    <option value="5" {{ (old('day_of_week', $configuration->day_of_week ?? '') == '5') ? 'selected' : '' }}>Friday</option>
                                    <option value="6" {{ (old('day_of_week', $configuration->day_of_week ?? '') == '6') ? 'selected' : '' }}>Saturday</option>
                                    <option value="7" {{ (old('day_of_week', $configuration->day_of_week ?? '') == '7') ? 'selected' : '' }}>Sunday</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3" id="day-of-month-div" style="display:none;">
                                <label for="day_of_month" class="form-label fw-semibold">Day of Month</label>
                                <input type="number" name="day_of_month" id="day_of_month" class="form-control" min="1" max="31" placeholder="e.g., 15" value="{{ old('day_of_month', $configuration->day_of_month ?? '') }}">
                                <div class="form-text">Day of the month (1-31)</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Report Types Section -->
                <div class="card mb-4" id="reportTypesCard">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-list-check me-2"></i>Report Types
                            <span class="text-danger ms-1" title="Required">*</span>
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
                                    <input type="checkbox" name="report_types[]" value="sales" id="sales" class="form-check-input report-type-checkbox"
                                           {{ (in_array('sales', old('report_types', $configuration->report_types ?? []))) ? 'checked' : '' }}>
                                    <label for="sales" class="form-check-label">
                                        <i class="bx bx-dollar text-success me-1"></i>
                                        <strong>Sales Report</strong>
                                        <small class="d-block text-muted">Revenue, orders, and sales metrics</small>
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="report_types[]" value="inventory" id="inventory" class="form-check-input report-type-checkbox"
                                           {{ (in_array('inventory', old('report_types', $configuration->report_types ?? []))) ? 'checked' : '' }}>
                                    <label for="inventory" class="form-check-label">
                                        <i class="bx bx-package text-warning me-1"></i>
                                        <strong>Inventory Report</strong>
                                        <small class="d-block text-muted">Stock levels, low stock alerts</small>
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="report_types[]" value="suppliers" id="suppliers" class="form-check-input report-type-checkbox"
                                           {{ (in_array('suppliers', old('report_types', $configuration->report_types ?? []))) ? 'checked' : '' }}>
                                    <label for="suppliers" class="form-check-label">
                                        <i class="bx bx-group text-info me-1"></i>
                                        <strong>Key Suppliers Report</strong>
                                        <small class="d-block text-muted">Top suppliers and performance</small>
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="report_types[]" value="customers" id="customers" class="form-check-input report-type-checkbox"
                                           {{ (in_array('customers', old('report_types', $configuration->report_types ?? []))) ? 'checked' : '' }}>
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
                                <label for="format" class="form-label fw-semibold">
                                    Report Format
                                    <span class="text-danger ms-1" title="Required">*</span>
                                </label>
                                <select name="format" id="format" class="form-select" required>
                                    <option value="">Choose format...</option>
                                    <option value="excel" {{ (old('format', $configuration->format ?? '') == 'excel') ? 'selected' : '' }}>📊 Excel (.xlsx )</option>
                                    <option value="pdf" {{ (old('format', $configuration->format ?? '') == 'pdf') ? 'selected' : '' }}>📄 PDF (.pdf )</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Notification Channels</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input type="checkbox" name="notification_channels[]" value="email" id="channel_email" class="form-check-input"
                                               {{ (in_array('email', old('notification_channels', $configuration->notification_channels ?? []))) ? 'checked' : '' }}>
                                        <label for="channel_email" class="form-check-label">
                                            <i class="bx bx-envelope me-1"></i>Email
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" name="notification_channels[]" value="database" id="channel_database" class="form-check-input"
                                               {{ (in_array('database', old('notification_channels', $configuration->notification_channels ?? []))) ? 'checked' : '' }}>
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
                                <input type="checkbox" name="is_active" id="is_active" value="1" class="form-check-input"
                                       {{ old('is_active', $configuration->is_active ?? true) ? 'checked' : '' }}>
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

                    <!-- Enhanced Progress Section -->
                    <div class="mt-3" id="downloadProgressContainer" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted" id="progressText">Preparing...</small>
                            <small class="text-muted" id="progressPercentage">0%</small>
                        </div>
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                                 role="progressbar"
                                 style="width: 0%"
                                 id="progressBar">
                            </div>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted" id="progressStatus">Starting report generation...</small>
                        </div>
                    </div>

                    <!-- Success/Error Messages -->
                    <div class="mt-3" id="downloadResult" style="display: none;">
                        <div class="alert alert-success" id="successMessage" style="display: none;">
                            <i class="bx bx-check-circle me-2"></i>
                            <span id="successText">Report generated successfully!</span>
                        </div>
                        <div class="alert alert-danger" id="errorMessage" style="display: none;">
                            <i class="bx bx-error-circle me-2"></i>
                            <span id="errorText">Failed to generate report. Please try again.</span>
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

<!-- Bootstrap Confirmation Modals -->
<!-- Save Settings Confirmation Modal -->
<div class="modal fade" id="saveSettingsModal" tabindex="-1" aria-labelledby="saveSettingsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="saveSettingsModalLabel">
                    <i class="bx bx-check-circle me-2 text-primary"></i>Save Settings?
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>This will update your report configuration and save all current settings.</p>
                <div class="alert alert-info">
                    <small><i class="bx bx-info-circle me-1"></i>Your settings will be applied immediately for automatic report generation.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmSaveSettings">
                    <i class="bx bx-check me-1"></i>Yes, Save Settings
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Save as Draft Confirmation Modal -->
<div class="modal fade" id="saveDraftModal" tabindex="-1" aria-labelledby="saveDraftModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="saveDraftModalLabel">
                    <i class="bx bx-save me-2 text-secondary"></i>Save as Draft?
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>This will save your current settings as a draft configuration.</p>
                <div class="alert alert-warning">
                    <small><i class="bx bx-info-circle me-1"></i>Note: Draft settings will be saved but not activated for automatic reports.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-outline-primary" id="confirmSaveDraft">
                    <i class="bx bx-save me-1"></i>Yes, Save Draft
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Reset Settings Confirmation Modal -->
<div class="modal fade" id="resetSettingsModal" tabindex="-1" aria-labelledby="resetSettingsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resetSettingsModalLabel">
                    <i class="bx bx-reset me-2 text-danger"></i>Reset Settings?
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>This will clear all current settings. Are you sure?</p>
                <div class="alert alert-danger">
                    <small><i class="bx bx-warning me-1"></i>This action cannot be undone. All your current configuration will be lost.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmResetSettings">
                    <i class="bx bx-reset me-1"></i>Yes, Reset All
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h6 id="loadingTitle">Saving Settings...</h6>
                <p class="text-muted mb-0" id="loadingMessage">Please wait while we save your configuration.</p>
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

    // Updated progress elements
    const downloadProgressContainer = document.getElementById('downloadProgressContainer');

    // Success alert handling
    const successAlert = document.getElementById('successAlert');
    if (successAlert) {
        // Auto-scroll to success message
        successAlert.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        });

        // Auto-hide after 8 seconds
        setTimeout(() => {
            successAlert.style.transition = 'opacity 0.5s ease-out';
            successAlert.style.opacity = '0';
            setTimeout(() => {
                if (successAlert.parentNode) {
                    successAlert.remove();
                }
            }, 500);
        }, 8000);
    }

    // Utility function to show Bootstrap alerts
    function showAlert(type, title, message) {
        const alertContainer = document.createElement('div');
        alertContainer.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertContainer.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 500px;';

        alertContainer.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="bx ${type === 'warning' ? 'bx-warning' : type === 'danger' ? 'bx-error' : 'bx-check-circle'} fs-4 me-3"></i>
                <div>
                    <h6 class="alert-heading mb-1">${title}</h6>
                    <div>${message}</div>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

        document.body.appendChild(alertContainer);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (alertContainer.parentNode) {
                alertContainer.remove();
            }
        }, 5000);
    }

    // Show loading modal
    function showLoadingModal(title, message) {
        document.getElementById('loadingTitle').textContent = title;
        document.getElementById('loadingMessage').textContent = message;
        const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
        loadingModal.show();
        return loadingModal;
    }

    // Initialize Select2 if available - not using external select2, using native selects
    // Bootstrap and Materio theme provide good native select styling

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
        const format = document.getElementById('format').value;
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
        const selectedFormat = document.getElementById('format').value;
        const checkedReportTypes = Array.from(document.querySelectorAll('input[name="report_types[]"]:checked'))
                                         .map(cb => cb.value);

        // Get UI elements
        const progressContainer = document.getElementById('downloadProgressContainer');
        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');
        const progressPercentage = document.getElementById('progressPercentage');
        const progressStatus = document.getElementById('progressStatus');
        const downloadResult = document.getElementById('downloadResult');
        const successMessage = document.getElementById('successMessage');
        const errorMessage = document.getElementById('errorMessage');
        const successText = document.getElementById('successText');
        const errorText = document.getElementById('errorText');

        // Enhanced validation with better messaging and visual feedback
        if (checkedReportTypes.length === 0) {
            // Add visual feedback to the report types section
            const reportTypesCard = document.getElementById('reportTypesCard');
            reportTypesCard.classList.add('shake');
            reportTypesCard.style.borderColor = '#dc3545';

            setTimeout(() => {
                reportTypesCard.classList.remove('shake');
                reportTypesCard.style.borderColor = '';
            }, 2000);

            Swal.fire({
                icon: 'warning',
                title: 'No Report Types Selected',
                html: `
                    <p>Please select at least one report type before generating:</p>
                    <ul style="text-align: left; margin: 10px 0;">
                        <li>📊 <strong>Sales Report</strong> - Revenue and transaction data</li>
                        <li>📦 <strong>Inventory Report</strong> - Stock levels and product data</li>
                        <li>🏢 <strong>Suppliers Report</strong> - Vendor and supply chain data</li>
                        <li>👥 <strong>Customers Report</strong> - Customer insights and analytics</li>
                    </ul>
                `,
                confirmButtonText: 'OK, Let me select',
                confirmButtonColor: '#28a745',
                customClass: {
                    htmlContainer: 'text-start'
                }
            });
            return;
        }

        if (!selectedFormat) {
            // Add visual feedback to the format selection
            const formatSelect = document.getElementById('format');
            formatSelect.classList.add('validation-error');

            setTimeout(() => {
                formatSelect.classList.remove('validation-error');
            }, 3000);

            Swal.fire({
                icon: 'warning',
                title: 'No Report Format Selected',
                html: `
                    <p>Please choose a format for your report:</p>
                    <div style="margin: 15px 0;">
                        <div style="padding: 8px; margin: 5px 0; border-left: 4px solid #28a745;">
                            📊 <strong>Excel (.xlsx)</strong> - Best for data analysis and calculations
                        </div>
                        <div style="padding: 8px; margin: 5px 0; border-left: 4px solid #dc3545;">
                            📄 <strong>PDF (.pdf)</strong> - Best for printing and formal reports
                        </div>
                    </div>
                `,
                confirmButtonText: 'OK, Let me choose',
                confirmButtonColor: '#28a745',
                customClass: {
                    htmlContainer: 'text-start'
                }
            });
            return;
        }

        // Reset UI
        downloadResult.style.display = 'none';
        successMessage.style.display = 'none';
        errorMessage.style.display = 'none';

        // Show progress container
        progressContainer.style.display = 'block';

        // Disable button
        downloadNowButton.disabled = true;
        downloadNowButton.classList.add('generating');
        downloadNowButton.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i>Generating...';

        // Build URL parameters
        const params = new URLSearchParams();
        params.append('format', selectedFormat);
        checkedReportTypes.forEach(type => {
            params.append('report_types[]', type);
        });

        // Progress simulation with realistic steps
        const progressSteps = [
            { percent: 15, text: 'Validating parameters...', status: 'Checking report configuration...' },
            { percent: 30, text: 'Collecting data...', status: `Gathering ${checkedReportTypes.join(', ')} data...` },
            { percent: 50, text: 'Processing data...', status: 'Organizing and formatting data...' },
            { percent: 70, text: 'Generating file...', status: `Creating ${selectedFormat.toUpperCase()} file...` },
            { percent: 85, text: 'Finalizing...', status: 'Preparing file for download...' },
            { percent: 95, text: 'Almost ready...', status: 'Validating generated file...' }
        ];

        let currentStep = 0;
        const progressInterval = setInterval(() => {
            if (currentStep < progressSteps.length) {
                const step = progressSteps[currentStep];
                progressBar.style.width = step.percent + '%';
                progressPercentage.textContent = step.percent + '%';
                progressText.textContent = step.text;
                progressStatus.textContent = step.status;
                currentStep++;
            }
        }, 400);

        // Create a hidden iframe for download to avoid page navigation issues
        const downloadFrame = document.createElement('iframe');
        downloadFrame.style.display = 'none';
        document.body.appendChild(downloadFrame);

        // Set download URL
        const downloadUrl = `/reports/download-on-demand?${params.toString()}`;

        // Complete progress after delay
        setTimeout(() => {
            clearInterval(progressInterval);
            progressBar.style.width = '100%';
            progressPercentage.textContent = '100%';
            progressText.textContent = 'Complete!';
            progressStatus.textContent = 'Starting download...';

            // Attempt download
            try {
                downloadFrame.src = downloadUrl;

                // Show success after brief delay
                setTimeout(() => {
                    progressContainer.style.display = 'none';
                    downloadResult.style.display = 'block';
                    successMessage.style.display = 'block';
                    successText.textContent = `${selectedFormat.toUpperCase()} report generated and downloaded successfully!`;

                    // Reset button
                    downloadNowButton.disabled = false;
                    downloadNowButton.classList.remove('generating');
                    downloadNowButton.innerHTML = '<i class="bx bx-download me-1"></i>Generate & Download Now';

                    // Reset progress
                    progressBar.style.width = '0%';
                    progressPercentage.textContent = '0%';

                    // Auto-hide success message after 5 seconds
                    setTimeout(() => {
                        downloadResult.style.display = 'none';
                    }, 5000);

                }, 800);

            } catch (error) {
                // Show error
                progressContainer.style.display = 'none';
                downloadResult.style.display = 'block';
                errorMessage.style.display = 'block';
                errorText.textContent = 'Download failed. Please check your settings and try again.';

                // Reset button
                downloadNowButton.disabled = false;
                downloadNowButton.classList.remove('generating');
                downloadNowButton.innerHTML = '<i class="bx bx-download me-1"></i>Generate & Download Now';
            }

            // Clean up iframe after delay
            setTimeout(() => {
                if (downloadFrame.parentNode) {
                    downloadFrame.parentNode.removeChild(downloadFrame);
                }
            }, 3000);

        }, 2500); // Total progress duration
    });

    // Preview button functionality
    previewBtn.addEventListener('click', function() {
        updatePreview();
        // Just focus on the preview section instead of modal
        const previewCard = document.getElementById('settingsPreview').closest('.card');
        previewCard.scrollIntoView({ behavior: 'smooth', block: 'center' });

        
        // Add temporary highlight effect
        previewCard.style.border = '2px solid #007bff';
        setTimeout(() => {
            previewCard.style.border = '';
        }, 2000);
    });

    // Reset button functionality
    resetBtn.addEventListener('click', function() {
        // Show confirmation modal
        const resetModal = new bootstrap.Modal(document.getElementById('resetSettingsModal'));
        resetModal.show();

        // Handle confirmation
        document.getElementById('confirmResetSettings').onclick = () => {
            document.getElementById('reportSettingsForm').reset();
            reportTypeCheckboxes.forEach(cb => cb.checked = false);
            selectAllReports.checked = false;
            selectAllReports.indeterminate = false;
            toggleFrequencyFields();
            updatePreview();

            resetModal.hide();
            showAlert('success', 'Settings Reset', 'All settings have been cleared successfully.');
        };
    });

    // Save as draft functionality
    saveAsDraftBtn.addEventListener('click', function() {
        // Validate required fields
        const frequency = document.getElementById('frequency').value;
        const reportTypes = Array.from(document.querySelectorAll('input[name="report_types[]"]:checked'));
        const format = document.getElementById('format').value;

        if (!frequency || reportTypes.length === 0 || !format) {
            showAlert('warning', 'Cannot Save Draft', 'Please fill in the required fields: frequency, at least one report type, and format.');
            return;
        }

        // Show confirmation modal
        const draftModal = new bootstrap.Modal(document.getElementById('saveDraftModal'));
        draftModal.show();

        // Handle confirmation
        document.getElementById('confirmSaveDraft').onclick = () => {
            // Set is_active to false for draft
            const isActiveCheckbox = document.getElementById('is_active');
            isActiveCheckbox.checked = false;

            draftModal.hide();
            const loadingModal = showLoadingModal('Saving Draft...', 'Please wait while we save your draft configuration.');

            // Submit the form
            document.getElementById('reportSettingsForm').submit();
        };
    });

    // Save settings confirmation
    document.getElementById('confirmSaveSettings').addEventListener('click', function() {
        // Show loading modal
        const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
        loadingModal.show();

        // Submit the form
        document.getElementById('reportSettingsForm').submit();
    });

    // Save draft confirmation
    document.getElementById('confirmSaveDraft').addEventListener('click', function() {
        // Set is_active to false for draft
        const isActiveCheckbox = document.getElementById('is_active');
        const originalState = isActiveCheckbox.checked;
        isActiveCheckbox.checked = false;

        // Show loading state
        const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
        loadingModal.show();

        // Submit the form
        document.getElementById('reportSettingsForm').submit();
    });

    // Reset settings confirmation
    document.getElementById('confirmResetSettings').addEventListener('click', function() {
        // Reset the form
        document.getElementById('reportSettingsForm').reset();
        reportTypeCheckboxes.forEach(cb => cb.checked = false);
        selectAllReports.checked = false;
        selectAllReports.indeterminate = false;
        toggleFrequencyFields();
        updatePreview();

        // Show success message
        Swal.fire({
            icon: 'success',
            title: 'Settings Reset',
            text: 'All settings have been cleared.',
            timer: 1500,
            showConfirmButton: false
        });
    });

    // Form submission
    document.getElementById('reportSettingsForm').addEventListener('submit', function(e) {
        e.preventDefault();

        // Validate required fields before showing confirmation
        const frequency = document.getElementById('frequency').value;
        const reportTypes = Array.from(document.querySelectorAll('input[name="report_types[]"]:checked'));
        const format = document.getElementById('format').value;

        if (!frequency) {
            showAlert('warning', 'Validation Error', 'Please select a report frequency.');
            return;
        }

        if (reportTypes.length === 0) {
            showAlert('warning', 'Validation Error', 'Please select at least one report type.');
            return;
        }

        if (!format) {
            showAlert('warning', 'Validation Error', 'Please select a report format.');
            return;
        }

        // Show confirmation modal
        const saveModal = new bootstrap.Modal(document.getElementById('saveSettingsModal'));
        saveModal.show();

        // Handle confirmation
        document.getElementById('confirmSaveSettings').onclick = () => {
            saveModal.hide();
            const loadingModal = showLoadingModal('Saving Settings...', 'Please wait while we save your configuration.');

            // Add visual feedback to form
            const form = document.getElementById('reportSettingsForm');
            form.style.opacity = '0.7';
            form.style.pointerEvents = 'none';

            // Actually submit the form
            this.submit();
        };
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
