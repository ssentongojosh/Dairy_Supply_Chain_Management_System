<?php

namespace App\Http\Controllers;

use App\Models\Task;
use illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

class TaskController extends Controller
{
    //
    /**
     * Display a list of tasks for the authenticated user.
     * This will be the main task dashboard.
     */
    public function index(Request $request)
    {
        // Ensure the user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // If the user is not authenticated, redirect to login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // If the user is authenticated, proceed to fetch tasks
    {
        // // Get the authenticated user
        // $user = Auth::user();

        // // Fetch tasks assigned to the current user
        // // Order by priority (Urgent > High > Medium > Low) and then by due date
        // $tasks = $user->tasks()
        //               ->whereIn('status', [
        //                   Task::STATUS_ASSIGNED,
        //                   Task::STATUS_IN_PROGRESS,
        //                   Task::STATUS_OVERDUE
        //               ])
        //               ->orderByRaw("FIELD(priority, ?, ?, ?, ?) DESC", [
        //                   Task::PRIORITY_URGENT,
        //                   Task::PRIORITY_HIGH,
        //                   Task::PRIORITY_MEDIUM,
        //                   Task::PRIORITY_LOW
        //               ])
        //               ->orderBy('due_date')
        //               ->orderBy('created_at', 'desc')
        //               ->get();

        // // Optionally, fetch completed tasks if you want a separate section
        // $completedTasks = $user->tasks()
        //                        ->where('status', Task::STATUS_COMPLETED)
        //                        ->orderBy('completed_at', 'desc')
        //                        ->limit(10) // Show last 10 completed tasks
        //                        ->get();

        // return view('tasks.index', compact('tasks', 'completedTasks')); // temporary comment
        $user = Auth::user();

        // Start with the base query for the user's tasks
        $query = $user->tasks();

        // --- Apply Filters ---
        // Filter by Status
        if ($request->has('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        } else {
            // Default: Show only active tasks if no specific status is requested
            $query->whereIn('status', [
                Task::STATUS_ASSIGNED,
                Task::STATUS_IN_PROGRESS,
                Task::STATUS_OVERDUE
            ]);
        }

        // Filter by Priority
        if ($request->has('priority') && $request->input('priority') !== 'all') {
            $query->where('priority', $request->input('priority'));
        }

        // Filter by Task Type
        if ($request->has('type') && $request->input('type') !== 'all') {
            $query->where('type', $request->input('type'));
        }

        // --- Apply Sorting ---
        $sortBy = $request->input('sort_by', 'due_date'); // Default sort by due_date
        $sortOrder = $request->input('sort_order', 'asc'); // Default sort order ascending

        // Ensure valid sort columns to prevent SQL injection
        $allowedSortColumns = ['due_date', 'priority', 'created_at'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'due_date'; // Fallback to default
        }
        if (!in_array(strtolower($sortOrder), ['asc', 'desc'])) {
            $sortOrder = 'asc'; // Fallback to default
        }

        // Special handling for priority sorting to maintain logical order (Urgent > High > Medium > Low)
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

        $query->orderBy('created_at', 'desc'); // Secondary sort for consistency

        // --- Add Pagination ---
        $tasksPerPage = $request->input('per_page', 10); // Default to 10 tasks per page
        $tasks = $query->paginate($tasksPerPage)->withQueryString(); // paginate() instead of get()


        // Fetch unique task types for the filter dropdown (optional, but helpful)
        $taskTypes = Task::select('type')->distinct()->pluck('type')->map(function($type) {
            return str_replace('_', ' ', $type); // Make it human-readable
        })->toArray();


        // Get all possible statuses and priorities for dropdowns
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
                      }}

    public function show(Task $task)
    {
        // Ensure the authenticated user is authorized to view this task
        if ($task->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.'); // Or redirect with an error message
        }

        return view('tasks.show', compact('task'));
    }

    /**
     * Mark a task as completed.
     */
    public function complete(Request $request, Task $task)
    {
        // Ensure the authenticated user is authorized to complete this task
        if ($task->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        // Only allow completion of tasks that are assigned, in progress, or overdue
        if (!in_array($task->status, [Task::STATUS_ASSIGNED, Task::STATUS_IN_PROGRESS, Task::STATUS_OVERDUE])) {
             return response()->json(['message' => 'Task cannot be completed in its current status.'], 400);
        }

        $task->status = Task::STATUS_COMPLETED;
        $task->completed_at = Carbon::now();
        $task->save();

        // You might want to log this action or dispatch an event (e.g., TaskCompleted)
        // Event::dispatch(new TaskCompleted($task));
        Log::info("Task ID {$task->id} marked as COMPLETED by User ID: " . Auth::id());

        // Return a JSON response for AJAX requests
        if ($request->ajax()) {
            return response()->json(['message' => 'Task marked as completed.', 'task' => $task]);
        }

        // For non-AJAX requests, redirect back or to a task list
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

    // add more methods here for:
    // - Marking as failed (with a reason)

    // - Filtering tasks

}
