<?php

namespace App\Services\Order;

use App\Events\OrderPlaced;
use App\Exceptions\OutOfStockIngredient;
use App\Models\Order;
use App\Pipelines\CheckIngredientAvailability;
use App\Repositories\Order\OrderRepositoryInterface;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;

class PlaceOrderService implements PlaceOrderServiceInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    protected OrderRepositoryInterface $orderRepository;

    /**
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param array<mixed> $data
     * @return Order
     * @throws OutOfStockIngredient
     */
    public function placeOrder(array $data): Order
    {
        return app(Pipeline::class)
            ->send($data)
            ->through([
                CheckIngredientAvailability::class,
            ])
            ->then(function (array $data) {
                return DB::transaction(function () use ($data) {
                    $order = $this->orderRepository->saveOrder($data);
                    OrderPlaced::dispatch($order);

                    return $order;
                });
            });
    }
}
