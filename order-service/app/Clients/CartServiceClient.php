<?php

namespace App\Clients;

use App\Exceptions\ServiceUnavailableException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class CartServiceClient
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.cart_service.url'), '/');
    }

    public function getCart(int $userId): array
    {
        try {
            $response = Http::timeout(5)
                ->get("{$this->baseUrl}/carts/{$userId}");
        } catch (ConnectionException $e) {
            throw new ServiceUnavailableException(
                'Cart Service tidak dapat dihubungi.'
            );
        }

        if (!$response->successful()) {
            throw new ServiceUnavailableException(
                'Gagal mengambil data dari Cart Service.'
            );
        }

        return $response->json('data');
    }

    public function clearCart(int $userId): array
    {
        try {
            $response = Http::timeout(5)
                ->delete("{$this->baseUrl}/carts/{$userId}");
        } catch (ConnectionException $e) {
            throw new ServiceUnavailableException(
                'Cart Service tidak dapat dihubungi.'
            );
        }

        if (!$response->successful()) {
            throw new ServiceUnavailableException(
                'Gagal mengosongkan Cart.'
            );
        }

        return $response->json('data');
    }
}