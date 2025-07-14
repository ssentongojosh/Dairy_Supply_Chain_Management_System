@extends('layouts.contentNavbarLayout')
@section('title', 'My Tasks Dashboard')
@section('content')

    <div class="container mt-5">
        <h3>Tasks Dashboard</h3>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Filters and Sort Form --}}
        <form method="GET" action="{{ route('tasks.index') }}" class="mb-4" id="filterForm">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="statusFilter" class="form-label">Status:</label>
                    <select class="form-select filter-input" id="statusFilter" name="status">
                        @foreach($allStatuses as $value => $label)
                            <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="priorityFilter" class="form-label">Priority:</label>
                    <select class="form-select filter-input" id="priorityFilter" name="priority">
                        @foreach($allPriorities as $value => $label)
                            <option value="{{ $value }}" {{ request('priority') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="typeFilter" class="form-label">Task Type:</label>
                    <select class="form-select filter-input" id="typeFilter" name="type">
                        <option value="all" {{ request('type') == 'all' ? 'selected' : '' }}>All Types</option>
                        @foreach($taskTypes as $type)
                            <option value="{{ str_replace(' ', '_', $type) }}" {{ request('type') == str_replace(' ', '_', $type) ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="sortBy" class="form-label">Sort By:</label>
                    <select class="form-select filter-input" id="sortBy" name="sort_by">
                        <option value="due_date" {{ request('sort_by') == 'due_date' ? 'selected' : '' }}>Due Date</option>
                        <option value="priority" {{ request('sort_by') == 'priority' ? 'selected' : '' }}>Priority</option>
                        <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Assigned Date</option>
                    </select>
                </div>
                <div class="col-md-auto">
                    <label for="sortOrder" class="form-label">Order:</label>
                    <select class="form-select filter-input" id="sortOrder" name="sort_order">
                        <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Ascending</option>
                        <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>Descending</option>
                    </select>
                </div>
                <div class="col-md-auto">
                    <label for="perPage" class="form-label">Per Page:</label>
                    <select class="form-select filter-input" id="perPage" name="per_page">
                        <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                    </select>
                </div>
                <div class="col-md-auto">
                    <a href="{{ route('tasks.index') }}" class="btn btn-secondary">Clear Filters</a>
                </div>
            </div>
        </form>

        @if($tasks->isEmpty())
         <div class="row">
        <div class="col-12">
            <div class="card text-center">
                <div class="card-body p-5">
                    <div class="mb-4">
                        <i class="ri-file-search-line" style="font-size: 4rem; color: #8592a3;"></i>
                    </div>
                    <h5 class="card-title mb-3">No Tasks Found</h5>
                    <p class="card-text text-muted mb-4">
                        No tasks found matching your current criteria. Try adjusting your filters or check back later for new assignments.
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="{{ route('tasks.index') }}" class="btn btn-outline-primary">
                            <i class="ri-refresh-line me-1"></i>Clear Filters
                        </a>
                        <button type="button" class="btn btn-primary" onclick="location.reload()">
                            <i class="ri-refresh-line me-1"></i>Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@else

            <div class="row mt-4">
                @foreach($tasks as $task)
                    <div class="col-md-6 mb-4">
                        <div class="card task-card priority-{{ $task->priority }}">
                            <div class="card-body">
                                <h5 class="card-title">
                                    {{ ucfirst(str_replace('_', ' ', $task->type)) }}
                                    <span class="float-end status-badge status-{{ $task->status }}">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                                </h5>
                                <h6 class="card-subtitle mb-2 text-muted">
                                    Priority: <span class="badge bg-{{ $task->priority == 'urgent' ? 'danger' : ($task->priority == 'high' ? 'warning' : 'info') }}">{{ ucfirst($task->priority) }}</span>
                                    @if($task->due_date)
                                        Due: {{ $task->due_date->format('M d, Y') }}
                                        @if($task->due_date->isPast() && $task->status !== 'completed')
                                            <span class="badge bg-danger">OVERDUE</span>
                                        @endif
                                    @endif
                                </h6>
                                <p class="card-text">{{ $task->description }}</p>

                                <p class="card-text small text-muted">
                                    Assigned: {{ $task->assigned_at->format('M d, Y H:i') }}
                                </p>

                                <div class="task-actions mt-3">
                                    @if($task->status !== 'completed')
                                        @if($task->status !== 'in_progress')
                                            <button class="btn btn-sm btn-info me-2 btn-in-progress" data-task-id="{{ $task->id }}">Mark In Progress</button>
                                        @endif
                                        <button class="btn btn-sm btn-success btn-complete-task" data-task-id="{{ $task->id }}">Mark Completed</button>
                                        @if($task->type === 'premises_inspection' && Auth::user()->role === \App\Enums\Role::INSPECTOR)
    {{-- Check task type and current user role --}}
    <button class="btn btn-sm btn-primary ms-2 btn-send-inspection-message mt-3 mb-3" data-task-id="{{ $task->id }}">Send Inspection Message</button>
@endif
                                    @else
                                        <span class="text-success small">Task Completed!</span>
                                    @endif
                                    <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-sm btn-outline-primary float-end mt-3">View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination Links --}}
            <div class="mt-4">
                {{ $tasks->links() }}
            </div>
        @endif
    </div>

    {{-- Confirmation Modal --}}
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmationModalLabel">Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="confirmationMessage">Are you sure you want to perform this action?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmButton">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Toast Container --}}
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="taskToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="ri-check-circle-fill text-success me-2" id="toastIcon"></i>
                <strong class="me-auto" id="toastTitle">Task Update</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="toastMessage">
                Task updated successfully!
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Auto-submit form when filter values change
            document.querySelectorAll('.filter-input').forEach(input => {
                input.addEventListener('change', function() {
                    console.log('Filter changed:', this.name, '=', this.value);
                    document.getElementById('filterForm').submit();
                });
            });

            // Modal and Toast elements
            const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
            const taskToast = new bootstrap.Toast(document.getElementById('taskToast'));
            const confirmButton = document.getElementById('confirmButton');
            const confirmationMessage = document.getElementById('confirmationMessage');
            const toastIcon = document.getElementById('toastIcon');
            const toastTitle = document.getElementById('toastTitle');
            const toastMessage = document.getElementById('toastMessage');

            // Show toast notification
            function showToast(title, message, isSuccess = true) {
                toastTitle.textContent = title;
                toastMessage.textContent = message;
                toastIcon.className = isSuccess ? 'ri-check-circle-fill text-success me-2' : 'ri-error-warning-fill text-danger me-2';
                taskToast.show();
            }

            // Show confirmation modal
            function showConfirmation(message, onConfirm) {
                confirmationMessage.textContent = message;
                confirmButton.onclick = () => {
                    confirmationModal.hide();
                    onConfirm();
                };
                confirmationModal.show();
            }

            // Handle task completion using native fetch
            document.querySelectorAll('.btn-complete-task').forEach(button => {
                button.addEventListener('click', function () {
                    const taskId = this.dataset.taskId;
                    showConfirmation('Are you sure you want to mark this task as completed?', () => {
                        fetch(`/tasks/${taskId}/complete`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            showToast('Success', data.message, true);
                            setTimeout(() => location.reload(), 1500);
                        })
                        .catch(error => {
                            console.error('Error marking task as complete:', error);
                            showToast('Error', 'Failed to mark task as completed.', false);
                        });
                    });
                });
            });

            // Handle task in progress using native fetch
            document.querySelectorAll('.btn-in-progress').forEach(button => {
                button.addEventListener('click', function () {
                    const taskId = this.dataset.taskId;
                    showConfirmation('Are you sure you want to mark this task as "In Progress"?', () => {
                        fetch(`/tasks/${taskId}/in-progress`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            showToast('Success', data.message, true);
                            setTimeout(() => location.reload(), 1500);
                        })
                        .catch(error => {
                            console.error('Error marking task as in progress:', error);
                            showToast('Error', 'Failed to mark task as in progress.', false);
                        });
                    });
                });
            });

            // NEW: Send Inspection Message button logic
            document.querySelectorAll('.btn-send-inspection-message').forEach(button => {
                button.addEventListener('click', function () {
                    const taskId = this.dataset.taskId;
                    showConfirmation('Are you sure you want to send an initial inspection message to the user?', () => {
                        fetch(`/tasks/${taskId}/send-inspection-message`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            showToast('Success', data.message, true);
                            button.disabled = true;
                            button.textContent = 'Message Sent!';
                        })
                        .catch(error => {
                            console.error('Error sending inspection message:', error);
                            showToast('Error', 'Failed to send inspection message.', false);
                        });
                    });
                });
            });
        });
    </script>

@endsection

