@extends('layouts.contentNavbarLayout')
@section('content')

    <div class="container mt-5">
        <a href="{{ route('tasks.index') }}" class="btn btn-secondary mb-3">← Back to My Tasks</a>

        <h1>Task Details: {{ ucfirst(str_replace('_', ' ', $task->type)) }}</h1>

        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">{{ $task->description }}</h5>
                <p class="card-text"><strong>Priority:</strong> {{ ucfirst($task->priority) }}</p>
                <p class="card-text"><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $task->status)) }}</p>
                <p class="card-text"><strong>Assigned To:</strong> {{ $task->user->name ?? 'Unassigned' }}</p>
                @if($task->due_date)
                    <p class="card-text"><strong>Due Date:</strong> {{ $task->due_date->format('M d, Y') }}</p>
                @endif
                <p class="card-text"><strong>Assigned At:</strong> {{ $task->assigned_at->format('M d, Y H:i') }}</p>
                @if($task->completed_at)
                    <p class="card-text"><strong>Completed At:</strong> {{ $task->completed_at->format('M d, Y H:i') }}</p>
                @endif
                <hr>
                <h6 class="card-subtitle mb-2 text-muted">Related Entity</h6>
                @if($task->related)
                    <p class="card-text"><strong>Type:</strong> {{ class_basename($task->related_type) }}</p>
                    <p class="card-text"><strong>ID:</strong> {{ $task->related_id }}</p>
                    {{-- You can add links to the related entity's detail page here if they exist --}}
                    {{-- Example: @if($task->related_type === 'App\Models\Order') <a href="/orders/{{ $task->related_id }}">View Order</a> @endif --}}
                @else
                    <p class="card-text">No related entity.</p>
                @endif

                <div class="mt-4">
                    @if($task->status === 'assigned' || $task->status === 'overdue')
                        <form action="{{ route('tasks.in-progress', $task->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-info me-2">Mark In Progress</button>
                        </form>
                    @endif
                    @if($task->status !== 'completed')
                        <form action="{{ route('tasks.complete', $task->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success">Mark Completed</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection
