<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The collection of ingredients that have been updated.
     *
     * @var Collection
     */
    public Collection $ingredients;

    /**
     * Create a new event instance.
     *
     * @param Collection $ingredients
     */
    public function __construct(Collection $ingredients)
    {
        $this->ingredients = $ingredients;
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
