<?php


namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Message;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a list of tasks for the authenticated user.
     */
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $query = $user->tasks();

        // --- Apply Filters ---
        if ($request->has('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        } else {
            $query->whereIn('status', [
                Task::STATUS_ASSIGNED,
                Task::STATUS_IN_PROGRESS,
                Task::STATUS_OVERDUE
            ]);
        }

        if ($request->has('priority') && $request->input('priority') !== 'all') {
            $query->where('priority', $request->input('priority'));
        }

        if ($request->has('type') && $request->input('type') !== 'all') {
            $query->where('type', $request->input('type'));
        }

        // --- Apply Sorting ---
        $sortBy = $request->input('sort_by', 'due_date');
        $sortOrder = $request->input('sort_order', 'asc');
        $allowedSortColumns = ['due_date', 'priority', 'created_at'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'due_date';
        }
        if (!in_array(strtolower($sortOrder), ['asc', 'desc'])) {
            $sortOrder = 'asc';
        }

        if ($sortBy === 'priority') {
            $query->orderByRaw("FIELD(priority, ?, ?, ?, ?) " . ($sortOrder === 'asc' ? 'ASC' : 'DESC'), [
                Task::PRIORITY_URGENT,
                Task::PRIORITY_HIGH,
                Task::PRIORITY_MEDIUM,
                Task::PRIORITY_LOW
            ]);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $query->orderBy('created_at', 'desc');

        // --- Add Pagination ---
        $tasksPerPage = $request->input('per_page', 10);
        $tasks = $query->paginate($tasksPerPage)->withQueryString();

        // Fetch unique task types for the filter dropdown
        $taskTypes = Task::select('type')->distinct()->pluck('type')->map(function($type) {
            return str_replace('_', ' ', $type);
        })->toArray();

        $allStatuses = [
            'all' => 'All Statuses',
            Task::STATUS_ASSIGNED => 'Assigned',
            Task::STATUS_IN_PROGRESS => 'In Progress',
            Task::STATUS_OVERDUE => 'Overdue',
            Task::STATUS_COMPLETED => 'Completed',
        ];
        $allPriorities = [
            'all' => 'All Priorities',
            Task::PRIORITY_URGENT => 'Urgent',
            Task::PRIORITY_HIGH => 'High',
            Task::PRIORITY_MEDIUM => 'Medium',
            Task::PRIORITY_LOW => 'Low',
        ];

        return view('tasks.index', compact('tasks', 'taskTypes', 'allStatuses', 'allPriorities'));
    }

    public function show(Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('tasks.show', compact('task'));
    }

    /**
     * Mark a task as completed.
     */
    public function complete(Request $request, Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        if (!in_array($task->status, [Task::STATUS_ASSIGNED, Task::STATUS_IN_PROGRESS, Task::STATUS_OVERDUE])) {
            return response()->json(['message' => 'Task cannot be completed in its current status.'], 400);
        }

        $task->status = Task::STATUS_COMPLETED;
        $task->completed_at = Carbon::now();
        $task->save();

        Log::info("Task ID {$task->id} marked as COMPLETED by User ID: " . Auth::id());

        if ($request->ajax()) {
            return response()->json(['message' => 'Task marked as completed.', 'task' => $task]);
        }

        return redirect()->route('tasks.index')->with('success', 'Task marked as completed!');
    }

    /**
     * Mark a task as in progress.
     */
    public function inProgress(Request $request, Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        if ($task->status === Task::STATUS_COMPLETED) {
            return response()->json(['message' => 'Completed tasks cannot be set to in progress.'], 400);
        }

        $task->status = Task::STATUS_IN_PROGRESS;
        $task->save();

        Log::info("Task ID {$task->id} marked as IN_PROGRESS by User ID: " . Auth::id());

        if ($request->ajax()) {
            return response()->json(['message' => 'Task marked as in progress.', 'task' => $task]);
        }

        return redirect()->route('tasks.index')->with('success', 'Task marked as in progress!');
    }

    /**
     * Inspector sends an inspection message to the related user for a premises inspection task.
     */
    public function sendInspectionMessage(Request $request, Task $task)
    {
        $inspector = Auth::user();

        // Safely get the string value of the inspector's role
        $inspectorRoleValue = '';
        if ($inspector->role instanceof Role) {
            $inspectorRoleValue = $inspector->role->value;
        } elseif (is_string($inspector->role)) {
            $inspectorRoleValue = $inspector->role;
        } else {
            Log::warning("Unexpected role type for authenticated user ID: {$inspector->id}. Type: " . gettype($inspector->role));
            return response()->json(['message' => 'Internal error: Cannot determine sender role.'], 500);
        }

        // --- Corrected Authorization Check (Using $inspectorRoleValue) ---
        // 1. Authorization Check: Is the authenticated user an inspector assigned to this task?
        if ($task->user_id !== $inspector->id || $inspectorRoleValue !== Role::INSPECTOR->value) {
            return response()->json(['message' => 'Unauthorized to send message for this task.'], 403);
        }
        // --- END Corrected Authorization Check ---


        // 2. Task Type Check: Is this a premises inspection task?
        if ($task->type !== 'premises_inspection') {
            return response()->json(['message' => 'This action is only for premises inspection tasks.'], 400);
        }

        // 3. Get the recipient (the verified user related to the task)
        $recipient = $task->related;

        if (!$recipient || !($recipient instanceof User)) {
            return response()->json(['message' => 'Related user for this task not found or invalid.'], 404);
        }

        // --- Corrected Recipient Role Extraction (Using the same robust logic) ---
        // Safely get the string value of the recipient's role
        $recipientRoleValue = '';
        if ($recipient->role instanceof Role) {
            $recipientRoleValue = $recipient->role->value;
        } elseif (is_string($recipient->role)) {
            $recipientRoleValue = $recipient->role;
        } else {
            Log::warning("Unexpected role type for recipient user ID: {$recipient->id}. Type: " . gettype($recipient->role));
            return response()->json(['message' => 'Internal error: Cannot determine recipient role.'], 500);
        }
        // --- END Corrected Recipient Role Extraction ---


        // 4. Role Authorization Check (mimic ChatController's allowedRoles logic)
        $allowedRecipientRolesForInspector = [
            Role::FARMER->value,
            Role::ADMIN->value, // Based on your ChatController
            Role::WHOLESALER->value,
            Role::RETAILER->value
        ];

        // --- Corrected in_array check (using $recipientRoleValue and no direct cast) ---
        if (!in_array($recipientRoleValue, $allowedRecipientRolesForInspector)) {
            return response()->json(['message' => "Cannot send message to a user with role '{$recipientRoleValue}'."], 403);
        }
        // --- END Corrected in_array check ---

        // 5. (Optional) Check if a message was already sent for this task

        // 6. Compose the message
        $inspectionTime = $task->due_date ? $task->due_date->format('M d, Y \a\t H:i') : 'soon';
        $messageContent = "Hello {$recipient->name}, I am your assigned inspector, {$inspector->name}. "
            . "I am preparing for your premises inspection scheduled for {$inspectionTime}. "
            . "Please confirm your business location (full address/GPS coordinates) and suggest a convenient time for my visit. "
            . "Thank you!";

        try {
            Message::create([
                'sender_id' => $inspector->id,
                'recipient_id' => $recipient->id,
                'message' => $messageContent,
                'is_read' => false,
            ]);

            Log::info("Inspector (ID: {$inspector->id}) sent inspection message to User (ID: {$recipient->id}) for Task ID: {$task->id}.");

            return response()->json(['message' => 'Inspection message sent successfully!', 'status' => 'success']);

        } catch (\Exception $e) {
            Log::error("Failed to send inspection message from inspector (ID: {$inspector->id}) to user (ID: {$recipient->id}) for Task ID: {$task->id}: " . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Failed to send message: ' . $e->getMessage()], 500);
        }
    }
}