<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

Route::post('/placeorder', [OrderController::class, 'placeOrder']);
