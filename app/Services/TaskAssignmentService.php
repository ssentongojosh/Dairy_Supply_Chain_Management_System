<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log; // For logging assignment decisions
use Illuminate\Support\Facades\DB; // For potential locking if concurrency is an issue
use App\Notifications\NewTaskAssignedNotification;

class TaskAssignmentService
{
    /**
     * Assigns a new task to an eligible user based on role and workload.
     *
     * @param string $type The type of task (e.g., 'product_delivery', 'premises_inspection')
     * @param string $description The detailed description of the task.
     * @param string $requiredRole The role required for this task (e.g., 'driver', 'inspector', 'worker').
     * @param Carbon|null $dueDate The desired due date for the task.
     * @param string $priority The priority of the task (e.g., 'low', 'medium', 'high', 'urgent').
     * @param Model|null $related The related Eloquent model (Order, Customer, Inventory).
     * @return Task|null The created and assigned Task model, or null if assignment failed.
     */
    public function assignTask(
        string $type,
        string $description,
        string $requiredRole,
        ?Carbon $dueDate = null,
        string $priority = Task::PRIORITY_MEDIUM,
        ?Model $related = null
    ): ?Task {
        try {
            // Find the least busy eligible user for the required role
            $assignee = $this->getLeastBusyUserByRole($requiredRole);

            if (!$assignee) {
                Log::warning("No eligible user found for role: {$requiredRole} to assign task type: {$type}. Task not created.");
                // Optionally, create a 'pending' task here without an assignee
                return null;
            }

            // Create the task
            $task = Task::create([
                'user_id' => $assignee->id,
                'type' => $type,
                'description' => $description,
                'due_date' => $dueDate,
                'priority' => $priority,
                'status' => Task::STATUS_ASSIGNED, // Task is immediately 'assigned'
                'related_id' => $related ? $related->id : null,
                'related_type' => $related ? $related::class : null,
                'assigned_at' => Carbon::now(),
            ]);

            $relatedClass = $related ? $related::class : 'N/A';
            $relatedId = $related && isset($related->id) ? $related->id : 'N/A';
            Log::info("Task '{$type}' assigned to user ID: {$assignee->id} ({$assignee->name}) for related {$relatedClass}:{$relatedId}. Task ID: {$task->id}");

            // TODO: Trigger in-app notification here
            // Example: $assignee->notify(new NewTaskAssignedNotification($task));
            // This assumes you have Laravel's Notification system set up and a Mailable/Notification class defined.
            // Send in-app notification to the assigned user
            $assignee->notify(new NewTaskAssignedNotification($task));
            Log::info("New task assigned notification dispatched for Task ID: {$task->id} to User ID: {$assignee->id}.");
            return $task;

        } catch (\Exception $e) {
            Log::error("Error assigning task of type '{$type}' to role '{$requiredRole}': " . $e->getMessage(), ['exception' => $e]);
            return null;
        }
    }

    /**
     * Finds the least busy user with the specified role.
     * This method implements the "least current tasks" and "next available agent" logic.
     * It will consider users with pending/assigned/in_progress tasks.
     *
     * @param string $role The role to search for (e.g., 'driver', 'inspector').
     * @return User|null The least busy user, or null if no eligible users found.
     */
    protected function getLeastBusyUserByRole(string $role): ?User
    {
        // Get all active and verified users for the given role
        $eligibleUsers = User::where('role', $role)
                             ->where('verified', true) // Assuming 'verified' means they are active/available
                            //  ->where('is_active', true) // Add an 'is_active' column to your users table if you have one
                             ->get();

        if ($eligibleUsers->isEmpty()) {
            return null;
        }

        $leastBusyUser = null;
        $minTasks = PHP_INT_MAX; // Initialize with a very large number
        $candidates = [];

        foreach ($eligibleUsers as $user) {
            // Count tasks that are not yet completed, failed, or cancelled
            $taskCount = $user->tasks()
                              ->whereIn('status', [
                                  Task::STATUS_PENDING,
                                  Task::STATUS_ASSIGNED,
                                  Task::STATUS_IN_PROGRESS,
                                  Task::STATUS_OVERDUE // Overdue tasks still count as active workload
                              ])
                              ->count();

            if ($taskCount < $minTasks) {
                $minTasks = $taskCount;
                $leastBusyUser = $user;
            } elseif ($taskCount == $minTasks) {
                // If we find another user with the same minimum task count, we can add them to candidates
                $candidates[] = $user;
            }
        }

        // If we have candidates, we can apply round-robin or other logic to select one
        if (!empty($candidates)) {
            // For simplicity, we'll just pick the first candidate
            return collect($candidates)->sortBy('id')->first(); // Sort by ID to ensure consistent selection
        }

        return $leastBusyUser;
    }


    /**
     * Get the count of active tasks for a given user.
     *
     * @param User $user
     * @return int
     */
    protected function getUserWorkload(User $user): int
    {
        return $user->tasks()
                    ->whereIn('status', [
                        Task::STATUS_PENDING,
                        Task::STATUS_ASSIGNED,
                        Task::STATUS_IN_PROGRESS,
                        Task::STATUS_OVERDUE
                    ])
                    ->count();
    }

    /**
     * Placeholder for more advanced conflict resolution or scheduling.
     * This is where "assign on a different date" or "assign to a different inspector" logic
     * would become more complex, potentially involving checking due date conflicts against
     * existing tasks' due dates, or capacity planning.
     *
     * For now, the `getLeastBusyUserByRole` handles the primary assignment.
     * If a user is "busy" as defined by their workload, they won't be picked.
     * More advanced scheduling would be for a later phase if needed.
     */
    protected function handleAssignmentConflict(Task $task, User $user, Carbon $dueDate): void
    {
        // Logic for complex scheduling/reassignment if primary fails
        // For now, this is covered by finding the least busy user.
    }
}
