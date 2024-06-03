<?php

namespace App\Mail;

use App\Models\Ingredient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class LowStockAlert extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @var Ingredient
     */
    public Ingredient $ingredient;

    /**
     * Create a new message instance.
     */
    public function __construct(Ingredient $ingredient)
    {
        $this->ingredient = $ingredient;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('Low Stock Alert')
            ->view('mail.stock.alert')
            ->with(['ingredient' => $this->ingredient]);
    }
}
