<p align="center">
  <a href="https://laravel.com" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
  </a>
</p>

<p align="center">
  <a href="https://github.com/laravel/framework/actions">
    <img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status">
  </a>
  <a href="https://packagist.org/packages/laravel/framework">
    <img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads">
  </a>
  <a href="https://packagist.org/packages/laravel/framework">
    <img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version">
  </a>
  <a href="https://packagist.org/packages/laravel/framework">
    <img src="https://img.shields.io/packagist/l/laravel/framework" alt="License">
  </a>
</p>

---

# 📦 Sales Dashboard System

Tujuan utama sistem ini adalah mempermudah proses perhitungan nilai persediaan dan harga pokok secara real-time berbasis transaksi yang terjadi.

Sistem ini dibangun menggunakan **Laravel 12** sebagai backend dan Vue.js untuk frontend. Tujuan sistem adalah menampilkan **Dashboard Penjualan dengan role-based access (RBAC), realtime polling update, CRUD transaksi, dan import CSV untuk update data**.

---

## 🚀 Fitur Utama

-   Dashboard Utama
    -   KPI Cards: Total Product Sales & Unique Customer
    -   Charts:
        -   Revenue vs Sales (Combo Chart)
        -   Total Sales by Channel (Donut Chart)
    -   Top Selling Products (Table: Product Name, Qty Sold, Talent/Endorser)
-   Dashboard Produk
    -   Top Selling Products Table (Qty Sold, Talent/Endorser, Channel, Harga)
    -   Channel Overview (Radar Chart)
    -   Sessions Overview (Line Chart)
-   CRUD Admin (Admin Only)
    -   Tambah, Edit, Hapus transaksi produk
-   Realtime Update
    -   Polling setiap 10 detik untuk update Top Selling Products otomatis
-   Upload CSV
    -   Admin dapat meng-upload CSV untuk update transaksi
    -   Validasi CSV otomatis (qty > 0, harga valid, dll.)

---

## ⚙️ Instalasi & Setup Project

### 1. Clone Repository

```bash
git clone https://github.com/BayMx19/Sales-Dashboard_BackEnd_Isa-Iman-Muhammad.git
cd Sales-Dashboard_BackEnd_Isa-Iman-Muhammad
```

### 2. Install Dependency

```bash
composer install
```

### 3. Copy File Environment

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Konfigurasi Database

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sales_dashboard
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Jalankan Migrasi dan Seeder

```bash
php artisan migrate --seed
```

### 6. Jalankan Server Lokal (Server default: http://127.0.0.1:8000)

```bash
php artisan serve
```

---

## 🧮 Endpoint API

| Method   | Endpoint        | Deskripsi                                    |
| -------- | --------------- | -------------------------------------------- |
| **POST** | `/api/register` | Registrasi user baru                         |
| **POST** | `/api/login`    | Login user                                   |
| **POST** | `/api/logout`   | Logout user (autentikasi Sanctum dibutuhkan) |

### Dashboard Utama

| Method  | Endpoint                       | Deskripsi                                 |
| ------- | ------------------------------ | ----------------------------------------- |
| **GET** | `/api/dashboard/kpi`           | Menampilkan KPI utama                     |
| **GET** | `/api/dashboard/revenue-sales` | Menampilkan revenue vs sales              |
| **GET** | `/api/dashboard/sales-channel` | Menampilkan penjualan berdasarkan channel |
| **GET** | `/api/dashboard/top-products`  | Menampilkan produk terlaris               |

### Dashboard Product

| Method  | Endpoint                                   | Deskripsi                                      |
| ------- | ------------------------------------------ | ---------------------------------------------- |
| **GET** | `/api/dashboard-product/top-products`      | Menampilkan produk terlaris spesifik dashboard |
| **GET** | `/api/dashboard-product/channel-overview`  | Menampilkan overview channel                   |
| **GET** | `/api/dashboard-product/sessions-overview` | Menampilkan overview sesi                      |

### Users

| Method     | Endpoint          | Deskripsi               |
| ---------- | ----------------- | ----------------------- |
| **GET**    | `/api/users`      | Menampilkan semua user  |
| **POST**   | `/api/users`      | Menambah user baru      |
| **GET**    | `/api/users/{id}` | Menampilkan detail user |
| **PUT**    | `/api/users/{id}` | Mengubah data user      |
| **DELETE** | `/api/users/{id}` | Menghapus user          |

### Products

| Method     | Endpoint             | Deskripsi                 |
| ---------- | -------------------- | ------------------------- |
| **GET**    | `/api/products`      | Menampilkan semua produk  |
| **POST**   | `/api/products`      | Menambah produk baru      |
| **GET**    | `/api/products/{id}` | Menampilkan detail produk |
| **PUT**    | `/api/products/{id}` | Mengubah data produk      |
| **DELETE** | `/api/products/{id}` | Menghapus produk          |

### Transactions

| Method     | Endpoint                   | Deskripsi                    |
| ---------- | -------------------------- | ---------------------------- |
| **GET**    | `/api/transactions`        | Menampilkan semua transaksi  |
| **POST**   | `/api/transactions`        | Menambah transaksi baru      |
| **GET**    | `/api/transactions/{id}`   | Menampilkan detail transaksi |
| **PUT**    | `/api/transactions/{id}`   | Mengubah data transaksi      |
| **DELETE** | `/api/transactions/{id}`   | Menghapus transaksi          |
| **POST**   | `/api/transactions/import` | Import transaksi via CSV     |

### Top Selling

| Method  | Endpoint           | Deskripsi                   |
| ------- | ------------------ | --------------------------- |
| **GET** | `/api/top-selling` | Menampilkan produk terlaris |

### Profile

| Method  | Endpoint       | Deskripsi                    |
| ------- | -------------- | ---------------------------- |
| **GET** | `/api/profile` | Menampilkan data profil user |
| **PUT** | `/api/profile` | Mengubah data profil user    |

> Semua endpoint di atas memerlukan autentikasi **Sanctum** kecuali `/register` dan `/login`.  
> Response dikembalikan dalam format JSON, dan pastikan header request:

```bash
Content-Type: application/json
Accept: application/json
```

---

## 🧑‍💻 Teknologi yang Digunakan

Laravel 12
PHP 8.2+
MySQL
Eloquent ORM
Postman (API Testing)
Composer

---

## ✉️ Kontak

Untuk pertanyaan atau pengembangan lebih lanjut, hubungi:<br>
Isa Iman Muhammad<br>
📩 Email: isaimanmuhammad19@gmail.com<br>
🌐 Website: https://isaimanmuhammad.netlify.app/<br>
