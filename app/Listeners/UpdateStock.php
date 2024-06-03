<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Events\StockUpdated;
use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateStock
{
    /**
     * Handle the event.
     *
     * @param OrderPlaced $event
     * @return void
     */
    public function handle(OrderPlaced $event): void
    {
        $orderItems = $event->order->items;

        foreach ($orderItems as $item) {
            $product = Product::findOrFail($item->product_id);
            $quantity = $item->quantity;

            foreach ($product->ingredients as $ingredient) {
                $consumedQuantity = $ingredient->pivot->amount * $quantity;
                $ingredient->available -= $consumedQuantity;
                $ingredient->save();
            }

            StockUpdated::dispatch($product->ingredients);
        }
    }
}
