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