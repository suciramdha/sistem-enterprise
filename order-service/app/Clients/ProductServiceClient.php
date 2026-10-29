<?php

namespace App\Clients;

use App\Exceptions\ProductNotFoundException;
use App\Exceptions\ServiceUnavailableException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class ProductServiceClient
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(
            config('services.product_service.url'),
            '/'
        );
    }

    public function getProduct(int $productId): array
    {
        try {
            $response = Http::timeout(5)
                ->get("{$this->baseUrl}/products/{$productId}");
        } catch (ConnectionException $e) {
            throw new ServiceUnavailableException(
                'Product Service tidak dapat dihubungi.'
            );
        }

        if ($response->status() === 404) {
            throw new ProductNotFoundException(
                "Produk dengan ID {$productId} tidak ditemukan."
            );
        }

        if ($response->serverError()) {
            throw new ServiceUnavailableException(
                'Product Service sedang bermasalah.'
            );
        }

        if (!$response->successful()) {
            throw new ServiceUnavailableException(
                'Gagal mengambil data dari Product Service.'
            );
        }

        return $response->json('data');
    }
}