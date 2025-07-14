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
                    @if($task->type === 'premises_inspection' && Auth::user()->role === \App\Enums\Role::INSPECTOR &&
    $task->user_id === Auth::user()->id )
    {{-- Check task type and current user role --}}
    <button class="btn btn-sm btn-primary ms-2 btn-send-inspection-message mt-3 mb-3" data-task-id="{{ $task->id }}">Send Inspection Message</button>
@endif
                </div>
            </div>
        </div>
    </div>


<!-- Confirmation Modal -->
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

<!-- Toast Container -->
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
    // Modal and Toast elements
    const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
    const taskToast = new bootstrap.Toast(document.getElementById('taskToast'));
    const confirmButton = document.getElementById('confirmButton');
    const confirmationMessage = document.getElementById('confirmationMessage');
    const toastIcon = document.getElementById('toastIcon');
    const toastTitle = document.getElementById('toastTitle');
    const toastMessage = document.getElementById('toastMessage');

    function showToast(title, message, isSuccess = true) {
        toastTitle.textContent = title;
        toastMessage.textContent = message;
        toastIcon.className = isSuccess ? 'ri-check-circle-fill text-success me-2' : 'ri-error-warning-fill text-danger me-2';
        taskToast.show();
    }

    function showConfirmation(message, onConfirm) {
        confirmationMessage.textContent = message;
        confirmButton.onclick = () => {
            confirmationModal.hide();
            onConfirm();
        };
        confirmationModal.show();
    }

    // Send Inspection Message button logic
    document.querySelectorAll('.btn-send-inspection-message').forEach(button => {
        button.addEventListener('click', function () {
            const taskId = this.dataset.taskId;
            showConfirmation('Are you sure you want to send the initial inspection message to the user?', () => {
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
