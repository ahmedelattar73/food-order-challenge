<?php

namespace App\Http\Controllers;

use App\Exceptions\OutOfStockIngredient;
use App\Http\Requests\PlaceOrderRequest;
use App\Http\Resources\OrderResource;
use App\Services\Order\PlaceOrderServiceInterface;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    /**
     * @var PlaceOrderServiceInterface
     */
    protected PlaceOrderServiceInterface $placeOrderService;

    /**
     * OrderController constructor.
     *
     * @param PlaceOrderServiceInterface $placeOrderService
     */
    public function __construct(PlaceOrderServiceInterface $placeOrderService)
    {
        $this->placeOrderService = $placeOrderService;
    }

    /**
     * Handle the incoming request to place an order.
     *
     * @param PlaceOrderRequest $request
     * @return JsonResponse
     * @throws OutOfStockIngredient
     */
    public function placeOrder(PlaceOrderRequest $request): JsonResponse
    {
        $order = $this->placeOrderService->placeOrder($request->input('products'));

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
