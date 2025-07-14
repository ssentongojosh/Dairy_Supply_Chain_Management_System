<?php

namespace App\Listeners;

use App\Services\TaskAssignmentService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Events\InventoryThresholdReached;
// use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AssignMilkPickupTask
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
    public function handle(InventoryThresholdReached $event): void
    {
        //
        $product = $event->product;
        Log::info("Handling InventoryThresholdReached event for Product: {$product->name} (Current: {$event->currentQuantity}, Threshold: {$event->threshold}). Attempting to assign milk pickup task.");

        // Define task details
        $taskType = 'milk_pickup';
        $taskDescription = "Pick up milk batch for '{$product->name}' (Current Stock: {$event->currentQuantity} - below threshold {$event->threshold}). Farmer: [Farmer Details]";
        // You might need to fetch farmer details here if your Product model links to a Farmer
        // For example: if ($product->farmer) { $taskDescription .= " from Farmer " . $product->farmer->name; }

        $requiredRole = 'driver'; // As per your requirements
        $dueDate = Carbon::now()->addDays(2); // Example: Pickup within 2 days
        $priority = 'high';

        // Call the TaskAssignmentService to assign the task
        $assignedTask = $this->taskAssignmentService->assignTask(
            $taskType,
            $taskDescription,
            $requiredRole,
            $dueDate,
            $priority,
            $product // Pass the related Product model
        );

        if ($assignedTask) {
            Log::info("Milk pickup task assigned successfully. Task ID: {$assignedTask->id}");
        } else {
            Log::warning("Failed to assign milk pickup task for Product: {$product->name}. Check TaskAssignmentService logs.");
        }
    }
}
