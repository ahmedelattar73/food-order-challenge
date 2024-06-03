<?php

namespace App\Repositories\Order;

use App\Models\Order;

interface OrderRepositoryInterface
{
    /**
     * Save the order data.
     *
     * @param array<mixed> $data
     * @return Order
     */
    public function saveOrder(array $data): Order;
}
