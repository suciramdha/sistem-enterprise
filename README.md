# Sistem Enterprise - Microservices E-Commerce

Project ini merupakan implementasi arsitektur microservices untuk sistem e-commerce. Pada UTS ini dikembangkan Order Service menggunakan Laravel dan MySQL yang terintegrasi dengan Product Service dan Cart Service melalui REST API.

## Arsitektur Sistem

Sistem terdiri dari beberapa service:

- Product Service: mengelola data produk, port 3000, menggunakan MySQL.
- Cart Service: mengelola keranjang pengguna, port 3001, menggunakan PostgreSQL.
- Review Service: mengelola review produk, port 3002.
- Order Service: mengelola checkout dan pesanan, port 3003, menggunakan Laravel dan MySQL.

Alur checkout:

User → Order Service → Cart Service → Product Service

Order Service tidak mengakses database service lain secara langsung. Komunikasi antar-service dilakukan melalui HTTP/REST API.

## Struktur Project

- product-service/
- cart-service/
- review-service/
- order-service/
- docker-compose.yml
- README.md

## Menjalankan Project

Dari folder utama project jalankan:

docker compose up --build

Service dapat diakses melalui:

- Product Service: http://localhost:3000
- Cart Service: http://localhost:3001
- Order Service: http://localhost:3003

Order Service akan menjalankan migration Laravel secara otomatis saat container dijalankan.

## Endpoint Order Service

### Health Check
GET /health

### Membuat Pesanan
POST /orders

Contoh request:

{
  "user_id": 1
}

Response berhasil: 201 Created.

Kemungkinan error:
- 422 jika request tidak valid.
- 409 jika cart kosong, produk tidak ditemukan, atau stok tidak mencukupi.
- 503 jika Cart Service atau Product Service tidak dapat dihubungi.
- 500 jika terjadi kesalahan server.

### Detail Pesanan
GET /orders/{id}

Response 200 jika ditemukan dan 404 jika pesanan tidak ditemukan.

### Daftar Pesanan User
GET /orders/user/{userId}

Filter status:

GET /orders/user/{userId}?status=PENDING

atau:

GET /orders/user/{userId}?status=PAID

### Pembayaran Pesanan
PATCH /orders/{id}/pay

Pesanan dengan status PENDING akan berubah menjadi PAID.

## Environment Variable

Konfigurasi utama Order Service:

- APP_DEBUG
- APP_URL
- DB_CONNECTION
- DB_HOST
- DB_PORT
- DB_DATABASE
- DB_USERNAME
- DB_PASSWORD
- PRODUCT_SERVICE_URL
- CART_SERVICE_URL

Contoh konfigurasi tersedia pada file order-service/.env.example.

## Keputusan Desain

Order Service menggunakan arsitektur berlapis yang memisahkan routes, Form Request, controller, service, model Eloquent, dan client.

Logika bisnis ditempatkan pada OrderService. Komunikasi HTTP ke service lain dilakukan melalui CartServiceClient dan ProductServiceClient dengan timeout maksimal 5 detik.

Order dan order item disimpan menggunakan database transaction. Nama dan harga produk disalin ke order item sebagai snapshot sehingga perubahan data produk setelah checkout tidak mengubah pesanan yang sudah dibuat.

Jika order berhasil disimpan tetapi cart gagal dikosongkan, order tetap dianggap berhasil dan kegagalan tersebut dicatat pada log.

## Keterbatasan

Sesuai ruang lingkup tugas, sistem belum menangani:

- autentikasi dan otorisasi;
- pengurangan stok setelah checkout;
- pembayaran nyata;
- pembatalan pesanan;
- frontend;
- API Gateway;
- message broker.

Status pesanan saat ini hanya PENDING dan PAID.