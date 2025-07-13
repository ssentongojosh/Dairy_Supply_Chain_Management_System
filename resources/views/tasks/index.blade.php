@extends('layouts.contentNavbarLayout')
@section('content')
    {{-- <style>
        .task-card {
            border-left: 5px solid;
            margin-bottom: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .task-card.priority-urgent { border-color: #dc3545; } /* Red */
        .task-card.priority-high { border-color: #ffc107; }   /* Yellow */
        .task-card.priority-medium { border-color: #0d6efd; } /* Blue */
        .task-card.priority-low { border-color: #6c757d; }    /* Gray */

        .status-badge {
            font-size: 0.75em;
            padding: 0.3em 0.6em;
            border-radius: 0.25rem;
            text-transform: uppercase;
        }
        .status-assigned { background-color: #0d6efd; color: white; }
        .status-in_progress { background-color: #20c997; color: white; }
        .status-overdue { background-color: #dc3545; color: white; }
        .status-completed { background-color: #6c757d; color: white; }
    </style>

    <div class="container mt-5">
        <h1>My Tasks Dashboard</h1>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <ul class="nav nav-tabs mb-4" id="taskTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="active-tab" data-bs-toggle="tab" data-bs-target="#activeTasks" type="button" role="tab" aria-controls="activeTasks" aria-selected="true">
                    Active Tasks ({{ $tasks->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completedTasks" type="button" role="tab" aria-controls="completedTasks" aria-selected="false">
                    Completed Tasks ({{ $completedTasks->count() }})
                </button>
            </li>
        </ul>

        <div class="tab-content" id="taskTabContent">
            <div class="tab-pane fade show active" id="activeTasks" role="tabpanel" aria-labelledby="active-tab">
                @if($tasks->isEmpty())
                    <div class="alert alert-info">You have no active tasks assigned at the moment. Good job!</div>
                @else
                    <div class="row">
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
                                            @if($task->status !== 'in_progress')
                                                <button class="btn btn-sm btn-info me-2 btn-in-progress" data-task-id="{{ $task->id }}">Mark In Progress</button>
                                            @endif
                                            <button class="btn btn-sm btn-success btn-complete-task" data-task-id="{{ $task->id }}">Mark Completed</button>
                                            <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-sm btn-outline-primary float-end">View Details</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="tab-pane fade" id="completedTasks" role="tabpanel" aria-labelledby="completed-tab">
                @if($completedTasks->isEmpty())
                    <div class="alert alert-info">No tasks completed yet.</div>
                @else
                    <div class="list-group">
                        @foreach($completedTasks as $task)
                            <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">{{ ucfirst(str_replace('_', ' ', $task->type)) }} - {{ $task->description }}</h6>
                                    <small class="text-muted">Completed on: {{ $task->completed_at->format('M d, Y H:i') }}</small>
                                </div>
                                <span class="badge status-badge status-completed">Completed</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // CSRF Token setup for Axios
            axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
            axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Handle Mark Completed button click
            document.querySelectorAll('.btn-complete-task').forEach(button => {
                button.addEventListener('click', function () {
                    const taskId = this.dataset.taskId;
                    if (confirm('Are you sure you want to mark this task as completed?')) {
                        axios.post(`/tasks/${taskId}/complete`)
                            .then(response => {
                                alert(response.data.message);
                                // Reload the page or remove the card from the active tasks list
                                location.reload();
                            })
                            .catch(error => {
                                console.error('Error marking task as complete:', error);
                                alert(error.response.data.message || 'Failed to mark task as completed.');
                            });
                    }
                });
            });

            // Handle Mark In Progress button click
            document.querySelectorAll('.btn-in-progress').forEach(button => {
                button.addEventListener('click', function () {
                    const taskId = this.dataset.taskId;
                    if (confirm('Are you sure you want to mark this task as "In Progress"?')) {
                        axios.post(`/tasks/${taskId}/in-progress`)
                            .then(response => {
                                alert(response.data.message);
                                location.reload(); // Reload to update status
                            })
                            .catch(error => {
                                console.error('Error marking task as in progress:', error);
                                alert(error.response.data.message || 'Failed to mark task as in progress.');
                            });
                    }
                });
            });
        });
    </script> --}}


    <div class="container mt-5">
        <h1>My Tasks Dashboard</h1>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Filters and Sort Form --}}
        <form method="GET" action="{{ route('tasks.index') }}" class="mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="statusFilter" class="form-label">Status:</label>
                    <select class="form-select" id="statusFilter" name="status">
                        @foreach($allStatuses as $value => $label)
                            <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="priorityFilter" class="form-label">Priority:</label>
                    <select class="form-select" id="priorityFilter" name="priority">
                        @foreach($allPriorities as $value => $label)
                            <option value="{{ $value }}" {{ request('priority') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="typeFilter" class="form-label">Task Type:</label>
                    <select class="form-select" id="typeFilter" name="type">
                        <option value="all" {{ request('type') == 'all' ? 'selected' : '' }}>All Types</option>
                        @foreach($taskTypes as $type)
                            <option value="{{ str_replace(' ', '_', $type) }}" {{ request('type') == str_replace(' ', '_', $type) ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="sortBy" class="form-label">Sort By:</label>
                    <select class="form-select" id="sortBy" name="sort_by">
                        <option value="due_date" {{ request('sort_by') == 'due_date' ? 'selected' : '' }}>Due Date</option>
                        <option value="priority" {{ request('sort_by') == 'priority' ? 'selected' : '' }}>Priority</option>
                        <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Assigned Date</option>
                    </select>
                </div>
                <div class="col-md-auto">
                    <label for="sortOrder" class="form-label">Order:</label>
                    <select class="form-select" id="sortOrder" name="sort_order">
                        <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Ascending</option>
                        <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>Descending</option>
                    </select>
                </div>
                <div class="col-md-auto">
                    <label for="perPage" class="form-label">Per Page:</label>
                    <select class="form-select" id="perPage" name="per_page">
                        <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                    </select>
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="{{ route('tasks.index') }}" class="btn btn-secondary">Clear Filters</a>
                </div>
            </div>
        </form>

        @if($tasks->isEmpty())
            <div class="alert alert-info">No tasks found matching your criteria.</div>
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
                                    @else
                                        <span class="text-success small">Task Completed!</span>
                                    @endif
                                    <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-sm btn-outline-primary float-end">View Details</a>
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
            axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            document.querySelectorAll('.btn-complete-task').forEach(button => {
                button.addEventListener('click', function () {
                    const taskId = this.dataset.taskId;
                    if (confirm('Are you sure you want to mark this task as completed?')) {
                        axios.post(`/tasks/${taskId}/complete`)
                            .then(response => {
                                alert(response.data.message);
                                location.reload();
                            })
                            .catch(error => {
                                console.error('Error marking task as complete:', error);
                                alert(error.response.data.message || 'Failed to mark task as completed.');
                            });
                    }
                });
            });

            document.querySelectorAll('.btn-in-progress').forEach(button => {
                button.addEventListener('click', function () {
                    const taskId = this.dataset.taskId;
                    if (confirm('Are you sure you want to mark this task as "In Progress"?')) {
                        axios.post(`/tasks/${taskId}/in-progress`)
                            .then(response => {
                                alert(response.data.message);
                                location.reload();
                            })
                            .catch(error => {
                                console.error('Error marking task as in progress:', error);
                                alert(error.response.data.message || 'Failed to mark task as in progress.');
                            });
                    }
                });
            });
        });
    </script>

@endsection

