@extends('layouts.contentNavbarLayout')

@section('title', 'Report History')

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
                                <i class="bx bx-history me-2"></i>Report History
                            </h4>
                            <p class="text-muted mb-0">View and download your previously generated reports</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('report-settings') }}" class="btn btn-primary">
                                <i class="bx bx-cog me-1"></i>Report Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    @if(!$reports->isEmpty())
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="avatar mx-auto mb-3 avatar-lg">
                            <div class="avatar-initial bg-primary rounded">
                                <i class="icon-base ri ri-file-text-line icon-26px"></i>
                            </div>
                        </div>
                        <h4 class="card-title mb-1">{{ $reports->count() }}</h4>
                        <span class="fw-medium text-muted">Total Reports</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="avatar mx-auto mb-3 avatar-lg">
                            <div class="avatar-initial bg-success rounded">
                                <i class="icon-base ri ri-checkbox-circle-line icon-26px"></i>
                            </div>
                        </div>
                        <h4 class="card-title mb-1">{{ $reports->where('status', 'success')->count() }}</h4>
                        <span class="fw-medium text-muted">Successful</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="avatar mx-auto mb-3 avatar-lg">
                            <div class="avatar-initial bg-info rounded">
                                <i class="icon-base ri ri-file-excel-2-line icon-26px"></i>
                            </div>
                        </div>
                        <h4 class="card-title mb-1">{{ $reports->where('format', 'excel')->count() }}</h4>
                        <span class="fw-medium text-muted">Excel Reports</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="avatar mx-auto mb-3 avatar-lg">
                            <div class="avatar-initial bg-secondary rounded">
                                <i class="icon-base ri ri-file-pdf-2-line icon-26px"></i>
                            </div>
                        </div>
                        <h4 class="card-title mb-1">{{ $reports->where('format', 'pdf')->count() }}</h4>
                        <span class="fw-medium text-muted">PDF Reports</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <i class="bx bx-check-circle fs-4 me-3"></i>
                <div>
                    <h6 class="alert-heading mb-1">Success!</h6>
                    <div>{{ session('success') }}</div>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <i class="bx bx-x-circle fs-4 me-3"></i>
                <div>
                    <h6 class="alert-heading mb-1">Error!</h6>
                    <div>{{ session('error') }}</div>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Reports Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-file me-2"></i>Generated Reports
                    </h5>
                    <div class="d-flex gap-3 align-items-center">
                        @if(!$reports->isEmpty())
                            <!-- Search Bar -->
                            <div class="position-relative">
                                <input type="text" class="form-control form-control-sm"
                                       id="reportSearch"
                                       placeholder="Search reports..."
                                       style="width: 250px;">
                                <i class="bx bx-search position-absolute top-50 end-0 translate-middle-y me-2 text-muted"></i>
                            </div>
                            <!-- Report Count Badge -->
                            <span class="badge bg-primary">
                                <span id="reportCount">{{ $reports->count() }}</span> Reports
                            </span>
                        @endif
                    </div>
                </div>

                @if($reports->isEmpty())
                    <!-- Empty State -->
                    <div class="card-body text-center py-5">
                        <div class="empty-state">
                            <i class="bx bx-file-blank display-1 text-muted mb-3"></i>
                            <h5 class="text-muted mb-3">No Reports Generated Yet</h5>
                            <p class="text-muted mb-4">
                                You haven't generated any reports yet. Start by configuring your report settings.
                            </p>
                            <a href="{{ route('report-settings') }}" class="btn btn-primary">
                                <i class="bx bx-cog me-1"></i>Go to Report Settings
                            </a>
                        </div>
                    </div>
                @else
                    <!-- Reports Table -->
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width: 25%;"><i class="bx bx-file me-1"></i>Report Name</th>
                                    <th style="width: 15%;"><i class="bx bx-category me-1"></i>Type(s)</th>
                                    <th style="width: 10%;"><i class="bx bx-file-blank me-1"></i>Format</th>
                                    <th style="width: 15%;"><i class="bx bx-calendar me-1"></i>Period</th>
                                    <th style="width: 15%;"><i class="bx bx-time me-1"></i>Generated</th>
                                    <th style="width: 10%;"><i class="bx bx-check-circle me-1"></i>Status</th>
                                    <th style="width: 10%;" class="text-center"><i class="bx bx-download me-1"></i>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @foreach($reports as $report)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($report->format === 'excel')
                                                    <div class="avatar avatar-xs me-2">
                                                        <span class="avatar-initial bg-label-success rounded">
                                                            <i class="bx bx-table bx-xs"></i>
                                                        </span>
                                                    </div>
                                                @else
                                                    <div class="avatar avatar-xs me-2">
                                                        <span class="avatar-initial bg-label-secondary rounded">
                                                            <i class="bx bx-file bx-xs"></i>
                                                        </span>
                                                    </div>
                                                @endif
                                                <div>
                                                    <span class="fw-medium text-truncate d-block" style="max-width: 200px;" title="{{ $report->report_name }}">{{ $report->report_name }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if(is_array($report->report_types))
                                                @if(count($report->report_types) > 2)
                                                    <span class="badge bg-label-primary rounded-pill" title="{{ implode(', ', array_map('ucfirst', $report->report_types)) }}">
                                                        {{ count($report->report_types) }} Types
                                                    </span>
                                                @else
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @foreach($report->report_types as $type)
                                                            <span class="badge bg-label-primary rounded-pill small">{{ ucfirst($type) }}</span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            @else
                                                <span class="badge bg-label-primary rounded-pill small">{{ ucfirst($report->report_types) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($report->format === 'excel')
                                                <span class="badge bg-success rounded-pill small">
                                                    <i class="bx bx-table me-1"></i>XLS
                                                </span>
                                            @else
                                                <span class="badge bg-secondary rounded-pill small">
                                                    <i class="bx bx-file me-1"></i>PDF
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="text-muted small">
                                                <div>{{ \Carbon\Carbon::parse($report->report_start_date)->format('M d') }}</div>
                                                <div>{{ \Carbon\Carbon::parse($report->report_end_date)->format('M d, Y') }}</div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-muted small">
                                                <div>{{ $report->generated_at->format('M d, Y') }}</div>
                                                <div class="text-muted">{{ $report->generated_at->format('H:i') }}</div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($report->status === 'success')
                                                <span class="badge bg-success rounded-pill small">
                                                    <i class="bx bx-check me-1"></i>OK
                                                </span>
                                            @else
                                                <span class="badge bg-danger rounded-pill small">
                                                    <i class="bx bx-x me-1"></i>Fail
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($report->status === 'success')
                                                <div class="dropdown">
                                                    <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="icon-base ri ri-more-2-line icon-22px"></i>
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item" href="{{ route('reports.history.download', $report->id) }}">
                                                            <i class="bx bx-download me-2"></i>Download Report
                                                        </a>
                                                        @if($report->format === 'pdf')
                                                            <a class="dropdown-item" href="{{ route('reports.history.preview', $report->id) }}" target="_blank">
                                                                <i class="bx bx-show me-2"></i>Preview Report
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">
                                                    <i class="bx bx-block me-1"></i>N/A
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination if needed --}}
                    {{-- @if($reports->hasPages())
                        <div class="card-footer">
                            {{ $reports->links() }}
                        </div>
                    @endif --}}
                @endif
            </div>
        </div>
    </div>
</div>

@if(!$reports->isEmpty())
<style>
/* Custom styles for responsive table */
.table td {
    white-space: nowrap;
    vertical-align: middle;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }

    .table th,
    .table td {
        padding: 0.5rem 0.25rem;
    }

    .badge {
        font-size: 0.7rem;
    }

    .avatar.avatar-xs {
        width: 1.5rem;
        height: 1.5rem;
    }
}

@media (max-width: 576px) {
    .table th:nth-child(4),
    .table td:nth-child(4) {
        display: none; /* Hide Period column on very small screens */
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('reportSearch');
    const reportTable = document.querySelector('table');
    const reportCount = document.getElementById('reportCount');
    const rows = reportTable.querySelectorAll('tbody tr');

    if (searchInput && reportTable) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            let visibleRows = 0;

            rows.forEach(function(row) {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                    visibleRows++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Update the count
            if (reportCount) {
                reportCount.textContent = visibleRows;
            }

            // Show no results message if needed
            const noResultsRow = reportTable.querySelector('.no-results-row');
            if (visibleRows === 0 && searchTerm !== '') {
                if (!noResultsRow) {
                    const tbody = reportTable.querySelector('tbody');
                    const newRow = document.createElement('tr');
                    newRow.className = 'no-results-row';
                    newRow.innerHTML = `
                        <td colspan="7" class="text-center py-4">
                            <div class="text-muted">
                                <i class="bx bx-search-alt fs-1 mb-2 d-block"></i>
                                <p class="mb-0">No reports found matching your search.</p>
                            </div>
                        </td>
                    `;
                    tbody.appendChild(newRow);
                }
            } else if (noResultsRow) {
                noResultsRow.remove();
            }
        });
    }
});
</script>
@endif

@endsection
