<?php

namespace App\Http\Controllers;

use App\Exceptions\EmptyCartException;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\OrderAlreadyPaidException;
use App\Exceptions\ProductNotFoundException;
use App\Exceptions\ServiceUnavailableException;
use App\Http\Requests\StoreOrderRequest;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

    /**
     * Mengambil detail pesanan berdasarkan ID.
     */
    public function show(int $id): JsonResponse
    {
        $order = $this->orderService->getOrderById($id);

        if (!$order) {
            return response()->json([
                'message' => 'Pesanan tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'message' => 'Berhasil mengambil detail pesanan',
            'data' => $order,
        ], 200);
    }

    /**
     * Mengambil daftar pesanan berdasarkan user.
     */
    public function getByUser(
        Request $request,
        int $userId
    ): JsonResponse {
        $status = $request->query('status');

        if (
            $status !== null &&
            !in_array($status, ['PENDING', 'PAID'], true)
        ) {
            return response()->json([
                'message' => 'Status tidak valid',
                'errors' => [
                    'status' => [
                        'Status harus PENDING atau PAID',
                    ],
                ],
            ], 422);
        }

        $orders = $this->orderService->getOrdersByUser(
            $userId,
            $status
        );

        return response()->json([
            'message' => 'Berhasil mengambil daftar pesanan',
            'data' => $orders,
        ], 200);
    }

    /**
     * Melakukan simulasi pembayaran pesanan.
     */
    public function pay(int $id): JsonResponse
    {
        $order = $this->orderService->getOrderById($id);

        if (!$order) {
            return response()->json([
                'message' => 'Pesanan tidak ditemukan',
            ], 404);
        }

        try {
            $order = $this->orderService->payOrder($order);

            return response()->json([
                'message' => 'Pembayaran berhasil',
                'data' => $order,
            ], 200);

        } catch (OrderAlreadyPaidException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 409);

        } catch (\Throwable $e) {
            Log::error('Terjadi error saat melakukan pembayaran.', [
                'order_id' => $id,
                'error' => $e->getMessage(),
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