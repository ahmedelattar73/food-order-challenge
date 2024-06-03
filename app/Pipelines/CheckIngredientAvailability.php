<?php

namespace App\Pipelines;

use App\Exceptions\OutOfStockIngredient;
use App\Models\Product;
use Closure;

class CheckIngredientAvailability
{
    /**
     * Handle the pipeline payload.
     *
     * @param array<mixed> $data
     * @param Closure $next
     * @return mixed
     * @throws OutOfStockIngredient
     */
    public function handle(array $data, Closure $next): mixed
    {
        foreach ($data as $item) {
            $product = Product::findOrFail($item['product_id']);
            $quantity = $item['quantity'];

            foreach ($product->ingredients as $ingredient) {
                $consumedQuantity = $ingredient->pivot->amount * $quantity;

                if ($ingredient->available < $consumedQuantity) {
                    throw new OutOfStockIngredient(__('Ingredient :ingredient is out of stock.', ['ingredient' => $ingredient->name]));
                }
            }
        }

        return $next($data);
    }
}
