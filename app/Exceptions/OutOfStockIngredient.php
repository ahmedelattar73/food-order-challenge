<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OutOfStockIngredient extends Exception
{
    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'error' => __('Out of stock ingredient.'),
            'message' => $this->getMessage(),
        ], Response::HTTP_BAD_REQUEST);
    }
}
