<?php

namespace App\Services;

use App\Clients\CartServiceClient;
use App\Clients\ProductServiceClient;
use App\Exceptions\EmptyCartException;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\OrderAlreadyPaidException;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    public function __construct(
        private CartServiceClient $cartService,
        private ProductServiceClient $productService
    ) {
    }

    public function createOrder(int $userId): Order
    {
        // 1. Ambil keranjang user dari Cart Service
        $cart = $this->cartService->getCart($userId);

        // 2. Pastikan keranjang tidak kosong
        if (empty($cart['items'])) {
            throw new EmptyCartException(
                'Keranjang kosong, pesanan tidak dapat dibuat.'
            );
        }

        $orderItems = [];
        $totalAmount = 0;

        // 3. Periksa setiap produk yang ada di keranjang
        foreach ($cart['items'] as $cartItem) {
            $product = $this->productService->getProduct(
                (int) $cartItem['product_id']
            );

            $quantity = (int) $cartItem['quantity'];
            $stock = (int) $product['stock'];
            $unitPrice = (float) $product['price'];

            // 4. Pastikan stok produk mencukupi
            if ($quantity > $stock) {
                throw new InsufficientStockException(
                    "Stok {$product['name']} tidak mencukupi: " .
                    "diminta {$quantity}, tersedia {$stock}"
                );
            }

            // 5. Hitung subtotal setiap item
            $subtotal = $unitPrice * $quantity;

            $orderItems[] = [
                'product_id' => (int) $product['id'],
                'product_name' => $product['name'],
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'subtotal' => $subtotal,
            ];

            $totalAmount += $subtotal;
        }

        // 6. Simpan order dan seluruh item dalam satu transaksi database
        $order = DB::transaction(function () use (
            $userId,
            $orderItems,
            $totalAmount
        ) {
            $order = Order::create([
                'user_id' => $userId,
                'status' => 'PENDING',
                'total_amount' => $totalAmount,
            ]);

            $order->items()->createMany($orderItems);

            return $order;
        });

        // 7. Setelah order tersimpan, kosongkan keranjang
        try {
            $this->cartService->clearCart($userId);
        } catch (\Throwable $e) {
            Log::warning(
                'Pesanan berhasil dibuat tetapi keranjang gagal dikosongkan.',
                [
                    'order_id' => $order->id,
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                ]
            );
        }

        // 8. Kembalikan order beserta item
        return $order->load('items');
    }

    public function getOrderById(int $id): ?Order
    {
        return Order::with('items')->find($id);
    }

    public function getOrdersByUser(
        int $userId,
        ?string $status = null
    ) {
        $query = Order::with('items')
            ->where('user_id', $userId);

        if ($status !== null) {
            $query->where('status', $status);
        }

        return $query->latest()->get();
    }

    public function payOrder(Order $order): Order
    {
        // Hanya order PENDING yang boleh dibayar
        if ($order->status !== 'PENDING') {
            throw new OrderAlreadyPaidException(
                'Pesanan sudah dibayar.'
            );
        }

        $order->update([
            'status' => 'PAID',
        ]);

        return $order->load('items');
    }
}