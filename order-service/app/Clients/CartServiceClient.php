<?php

namespace App\Clients;

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
        $response = Http::timeout(5)
            ->get("{$this->baseUrl}/carts/{$userId}");

        $response->throw();

        return $response->json('data');
    }

    public function clearCart(int $userId): array
    {
        $response = Http::timeout(5)
            ->delete("{$this->baseUrl}/carts/{$userId}");

        $response->throw();

        return $response->json('data');
    }
}