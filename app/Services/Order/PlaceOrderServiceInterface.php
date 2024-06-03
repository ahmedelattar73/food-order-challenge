<?php

namespace App\Services\Order;

use App\Exceptions\OutOfStockIngredient;
use App\Models\Order;

interface PlaceOrderServiceInterface
{
    /**
     * @api
     *
     * Place a new order.
     *
     * This method performs the following tasks:
     * - Checks the availability of ingredients for the products in the order.
     * - Creates a new order with the specified products and quantities.
     * - Dispatches an event to signal that the order has been placed.
     *
     * @param array<mixed> $data
     * @return Order
     * @throws OutOfStockIngredient
     */
    public function placeOrder(array $data): Order;
}
