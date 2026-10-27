<?php

namespace App\Http\Controllers;

use App\Exceptions\EmptyCartException;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\ProductNotFoundException;
use App\Exceptions\ServiceUnavailableException;
use App\Http\Requests\StoreOrderRequest;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {
    }

    /**
     * Membuat pesanan baru dari keranjang user.
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $order = $this->orderService->createOrder(
                (int) $validated['user_id']
            );

            return response()->json([
                'message' => 'Pesanan berhasil dibuat',
                'data' => $order,
            ], 201);

        } catch (
            EmptyCartException |
            ProductNotFoundException |
            InsufficientStockException $e
        ) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 409);

        } catch (ServiceUnavailableException $e) {
            Log::error('Service lain tidak dapat dihubungi.', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => $e->getMessage(),
            ], 503);

        } catch (\Throwable $e) {
            Log::error('Terjadi error saat membuat pesanan.', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $response = [
                'message' => 'Terjadi kesalahan pada server.',
            ];

            if (config('app.debug')) {
                $response['error'] = $e->getMessage();
            }

            return response()->json($response, 500);
        }
    }
}