<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

// my custom events
use App\Events\OrderApproved;
use App\Events\CustomerDocumentValidated;
use App\Events\InventoryThresholdReached;

// my custom listeners
use App\Listeners\AssignProductDeliveryTask;
use App\Listeners\AssignPremisesInspectionTask;
use App\Listeners\AssignMilkPickupTask;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [

        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        //Add your other event listeners here
        // Example:
        // \App\Events\OrderShipped::class => [
        //     \App\Listeners\SendShipmentNotification::class,
        // ],
         // Your custom event-listener mappings for task assignment
        OrderApproved::class => [
            AssignProductDeliveryTask::class,
        ],
        CustomerDocumentValidated::class => [
            AssignPremisesInspectionTask::class,
        ],
        InventoryThresholdReached::class => [
            AssignMilkPickupTask::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents(): bool
    {
        return false; // Set to true if you want to use event discovery
    }
}
