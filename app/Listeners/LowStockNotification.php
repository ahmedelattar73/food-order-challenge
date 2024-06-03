<?php

namespace App\Listeners;

use App\Events\StockUpdated;
use App\Mail\LowStockAlert;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class LowStockNotification
{
    /**
     * Handle the event.
     *
     * @param StockUpdated $event
     * @return void
     */
    public function handle(StockUpdated $event): void
    {
        foreach ($event->ingredients as $ingredient) {

            if ($ingredient->available <= $ingredient->stock / 2 && !$ingredient->low_stock) {
                $ingredient->update(['low_stock' => true]);

                Mail::to(config('mail.merchant_email'))
                    ->send(new LowStockAlert($ingredient));
            }
        }
    }
}
