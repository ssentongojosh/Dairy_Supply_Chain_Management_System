<?php

namespace App\Listeners;

use App\Events\OrderApproved;
use App\Services\TaskAssignmentService;
// use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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
