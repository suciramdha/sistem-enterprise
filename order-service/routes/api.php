<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'order-service',
    ], 200);
});

Route::post('/orders', [OrderController::class, 'store']);

Route::get('/orders/user/{userId}', [OrderController::class, 'getByUser']);

Route::get('/orders/{id}', [OrderController::class, 'show']);

Route::patch('/orders/{id}/pay', [OrderController::class, 'pay']);