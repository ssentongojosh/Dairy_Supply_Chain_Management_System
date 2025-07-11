<?php

namespace App\Events;

use App\Models\Product;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InventoryThresholdReached
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public Product $product; // The product/item whose inventory fell
    public float $currentQuantity; // The quantity that triggered the threshold
    public float $threshold; // The

    /**
     * Create a new event instance.
     */
    public function __construct(Product $product, float $currentQuantity, float $threshold)
    {
        //
        $this->product = $product;
        $this->currentQuantity = $currentQuantity;
        $this->threshold = $threshold;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
