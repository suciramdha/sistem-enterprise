# Dockerfile
# Image untuk menjalankan order-service (Laravel) di dalam container.
FROM php:8.4-cli

# Ekstensi pdo_mysql dibutuhkan Laravel untuk terhubung ke MySQL.
RUN apt-get update \
    && apt-get install -y --no-install-recommends unzip git \
    && docker-php-ext-install pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Composer diambil dari image resminya untuk memasang dependency Laravel.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /app

COPY . .

RUN composer install --no-interaction --prefer-dist --optimize-autoloader

EXPOSE 3003

# Jalankan migration terlebih dahulu, lalu Order Service pada port 3003.
CMD ["sh", "-c", "export PRODUCT_SERVICE_URL=http://product-service:3000 && export CART_SERVICE_URL=http://cart-service:3001 && php artisan optimize:clear && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=3003"]