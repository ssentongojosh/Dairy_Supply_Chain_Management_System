@extends('layouts/contentNavbarLayout')

@section('title', 'Report Notifications')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Reports /</span> Notifications
    </h4>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Your Report Notifications</h5>
            <div class="btn-group">
                <button class="btn btn-outline-primary btn-sm" onclick="markAllAsRead()">
                    <i class="fas fa-check-double me-1"></i> Mark All Read
                </button>
                <button class="btn btn-outline-secondary btn-sm" onclick="refreshNotifications()">
                    <i class="fas fa-sync-alt me-1"></i> Refresh
                </button>
            </div>
        </div>

        <div class="card-body">
            @if($notifications->count() > 0)
                <div class="row">
                    @foreach($notifications as $notification)
                        <div class="col-12 mb-3">
                            <div class="card border-left-{{ $notification->status === 'success' ? 'success' : 'danger' }} {{ !$notification->is_read ? 'shadow-sm' : '' }}">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-1">
                                            <div class="avatar avatar-sm">
                                                <span class="avatar-initial rounded bg-label-{{ $notification->status === 'success' ? 'success' : 'danger' }}">
                                                    <i class="fas fa-file-{{ $notification->format === 'pdf' ? 'pdf' : 'excel' }}"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-8">
                                            <h6 class="mb-1 {{ !$notification->is_read ? 'fw-bold' : '' }}">
                                                {{ $notification->report_name }}
                                                @if(!$notification->is_read)
                                                    <span class="badge bg-primary ms-2">New</span>
                                                @endif
                                            </h6>
                                            <p class="text-muted mb-1">
                                                <small>
                                                    <i class="fas fa-chart-bar me-1"></i>
                                                    {{ $notification->formatted_report_types }}
                                                    <span class="mx-2">•</span>
                                                    <i class="fas fa-file me-1"></i>
                                                    {{ strtoupper($notification->format) }}
                                                    @if($notification->file_size)
                                                        <span class="mx-2">•</span>
                                                        <i class="fas fa-weight me-1"></i>
                                                        {{ $notification->formatted_file_size }}
                                                    @endif
                                                </small>
                                            </p>
                                            <p class="text-muted mb-0">
                                                <small>
                                                    <i class="fas fa-clock me-1"></i>
                                                    Generated {{ $notification->generated_at->diffForHumans() }}
                                                </small>
                                            </p>
                                        </div>
                                        <div class="col-3 text-end">
                                            @if($notification->status === 'success')
                                                <div class="btn-group-vertical">
                                                    <a href="{{ route('reports.download', $notification->id) }}"
                                                       class="btn btn-success btn-sm mb-1"
                                                       onclick="markAsRead({{ $notification->id }})">
                                                        <i class="fas fa-download me-1"></i> Download
                                                    </a>
                                                    @if(!$notification->is_read)
                                                        <button class="btn btn-outline-secondary btn-sm"
                                                                onclick="markAsRead({{ $notification->id }})">
                                                            <i class="fas fa-check me-1"></i> Mark Read
                                                        </button>
                                                    @endif
                                                </div>
                                            @else
                                                <div class="alert alert-danger alert-sm mb-0">
                                                    <small>
                                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                                        Generation Failed
                                                        @if($notification->error_message)
                                                            <br>{{ Str::limit($notification->error_message, 50) }}
                                                        @endif
                                                    </small>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $notifications->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <div class="avatar avatar-xl mx-auto mb-3">
                        <span class="avatar-initial rounded bg-label-secondary">
                            <i class="fas fa-bell-slash" style="font-size: 2rem;"></i>
                        </span>
                    </div>
                    <h5 class="mb-2">No Notifications Yet</h5>
                    <p class="text-muted">
                        Your report notifications will appear here when reports are automatically generated.
                        <br>
                        <a href="{{ route('reports.download-on-demand') }}" class="text-primary">Configure your report settings</a> to get started.
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.border-left-success {
    border-left: 4px solid #28a745 !important;
}
.border-left-danger {
    border-left: 4px solid #dc3545 !important;
}
.alert-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}
</style>

<script>
function markAsRead(notificationId) {
    fetch(`/reports/notifications/${notificationId}/mark-read`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI to show as read
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
    });
}

function markAllAsRead() {
    // Implementation for marking all notifications as read
    if (confirm('Mark all notifications as read?')) {
        // You can implement this endpoint later if needed
        location.reload();
    }
}

function refreshNotifications() {
    location.reload();
}
</script>
@endsection
