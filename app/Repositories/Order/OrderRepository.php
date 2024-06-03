<?php

namespace App\Repositories\Order;

use App\Models\Order;

class OrderRepository implements OrderRepositoryInterface
{
    /**
     * {@inheritdoc}
     *
     * @param array<mixed> $data
     * @return Order
     */
    public function saveOrder(array $data): Order
    {
        $order = new Order();
        $order->save();
        $order->items()->createMany($data);

        return $order->load('items.product');
    }
}
