<?php

namespace App\Listeners;

use App\Events\CustomerDocumentValidated;
use App\Services\TaskAssignmentService; // Import your service
use Carbon\Carbon;
use App\Enums\Role;
use Illuminate\Support\Facades\Log;
// use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\User;
use Illuminate\Queue\InteractsWithQueue;

class AssignPremisesInspectionTask
{
    /**
     * Create the event listener.
     */
    protected $taskAssignmentService;
    public function __construct(TaskAssignmentService $taskAssignmentService)
    {
        $this->taskAssignmentService = $taskAssignmentService;
    }

    /**
     * Handle the event.
     */
    public function handle(CustomerDocumentValidated $event): void
    {
      Log::info("DEBUG: AssignPremisesInspectionTask LISTENER HANDLE method ENTERED for user ID: {$event->verifiedUser->id}.");

        $user = $event->verifiedUser;
        // Always get the string value from the enum
        $roleString = ($user->role instanceof \BackedEnum) ? $user->role->value : (string)$user->role;
        Log::info("Handling CustomerDocumentValidated event for User ID: {$user->id} (Role: {$roleString}). Attempting to assign premises inspection task.");

        // Define task details
        $taskType = 'premises_inspection';
        $taskDescription = "Inspect premises for new {$roleString} '{$user->name}' (ID: {$user->id}). Contact: {$user->email}.";
        $requiredRole = 'inspector';

        // Example: Schedule for next week on the same day if possible, or Monday
        $dueDate = Carbon::now()->addWeek();
        // Compare using the string value, not the enum object
        if ($roleString === 'wholesaler' || $roleString === 'retailer') {
             $dueDate = $dueDate->startOfWeek(Carbon::MONDAY)->addDays(rand(0, 4));
        } else {
             $dueDate = $dueDate->startOfWeek(Carbon::MONDAY);
        }

        $priority = 'medium';

        // Call the TaskAssignmentService to assign the task
        $assignedTask = $this->taskAssignmentService->assignTask(
            $taskType,
            $taskDescription,
            $requiredRole,
            $dueDate,
            $priority,
            $user // Pass the related User model
        );

        if ($assignedTask) {
            Log::info("Premises inspection task assigned successfully. Task ID: {$assignedTask->id}");
        } else {
            Log::warning("Failed to assign premises inspection task for User ID: {$user->id}. Check TaskAssignmentService logs.");
        }
    }
}
