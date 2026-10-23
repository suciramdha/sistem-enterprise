<?php

namespace App\Clients;

use Illuminate\Support\Facades\Http;

class ProductServiceClient
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.product_service.url'), '/');
    }

    public function getProduct(int $productId): array
    {
        $response = Http::timeout(5)
            ->get("{$this->baseUrl}/products/{$productId}");

        $response->throw();

        return $response->json('data');
    }
}