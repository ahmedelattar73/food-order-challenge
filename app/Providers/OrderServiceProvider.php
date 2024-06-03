<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Order\PlaceOrderServiceInterface;
use App\Services\Order\PlaceOrderService;
use App\Repositories\Order\OrderRepositoryInterface;
use App\Repositories\Order\OrderRepository;

class OrderServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            PlaceOrderServiceInterface::class,
            PlaceOrderService::class
        );

        $this->app->bind(
            OrderRepositoryInterface::class,
            OrderRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
