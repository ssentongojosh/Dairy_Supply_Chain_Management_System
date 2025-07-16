<?php

namespace App\Listeners;

use App\Events\OrderApproved;
use App\Services\TaskAssignmentService;
// use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\Task;
use App\Models\Order;

class AssignProductDeliveryTask
{
    /**
     * Create the event listener.
     */
    use InteractsWithQueue;

    protected $taskAssignmentService;

    public function __construct(TaskAssignmentService $taskAssignmentService)
    {
        //
        $this->taskAssignmentService = $taskAssignmentService;
    }

    /**
     * Handle the event.
     */
    public function handle(OrderApproved $event): void
    {
        //
        $order = $event->order;
        Log::info("Handling OrderApproved event for Order ID: {$order->id}. Attempting to assign delivery task.");

         $existingActiveTask = Task::where('related_type', Order::class) // Link to your Order model
                                  ->where('related_id', $order->id)
                                  ->where('type', 'product_delivery')
                                  ->whereIn('status', [
                                      Task::STATUS_ASSIGNED,
                                      Task::STATUS_IN_PROGRESS,
                                      Task::STATUS_OVERDUE // Consider overdue as still "active" for this purpose
                                  ])
                                  ->first();

        if ($existingActiveTask) {
            Log::info("Skipping product delivery task assignment for Order ID: {$order->id}. An active task (ID: {$existingActiveTask->id}, Status: {$existingActiveTask->status}) already exists.");
            return; // Stop execution, don't create a new task
        }

        // Define task details
        $taskType = 'product_delivery';
        $taskDescription = "Deliver products for Order #{$order->id} to customer {$order->customer_name}. Total: {$order->total_amount}. Location: {$order->delivery_address}.";
        $requiredRole = 'driver'; // As per your requirements
        $dueDate = Carbon::tomorrow()->endOfDay(); // Example: Deliver by tomorrow
        $priority = 'high';


        // Call the TaskAssignmentService to assign the task
        $assignedTask = $this->taskAssignmentService->assignTask(
            $taskType,
            $taskDescription,
            $requiredRole,
            $dueDate,
            $priority,
            $order // Pass the related Order model
        );

        if ($assignedTask) {
            Log::info("Product delivery task assigned successfully. Task ID: {$assignedTask->id}");
        } else {
            Log::warning("Failed to assign product delivery task for Order ID: {$order->id}. Check TaskAssignmentService logs.");
        }
    }
}
