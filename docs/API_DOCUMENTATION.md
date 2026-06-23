# API Documentation — ERP POS, Inventory & Accounting

**Versi:** 2.0
**Tanggal Update:** 2025-06
**Base URL:** `https://api.yourdomain.com/api/v1`
**Stack:** Laravel 12 · Vue 3 + Inertia · MySQL 8 · Redis · Laravel Reverb
**Autentikasi:** Bearer Token (Sanctum)

> **Changelog v2.0**
> - `payment_method` string ENUM diganti `payment_method_id` (FK ke tabel master `payment_methods`)
> - Endpoint master `Payment Methods` ditambahkan (Section 3.7)
> - `LOYALTY_POINT` sebagai metode bayar split payment di POS
> - `shift_id` ditambahkan ke buka/tutup sesi kasir
> - Endpoint `Shift` ditambahkan (Section 6.1)
> - Endpoint `Day Closing` dan `Month Closing` ditambahkan (Section 6.6 & 6.7)
> - `location_type` diperbarui sesuai schema terbaru (`STORE_WAREHOUSE`, `RENTED_WAREHOUSE`, `RACK`, `DISPLAY`, dll)
> - Field `is_stock_bearing`, `is_external`, `valid_from`, `valid_to` ditambahkan ke Lokasi
> - Endpoint `Planogram` ditambahkan (Section 7.6)
> - Role diperluas menjadi 6 role (Owner, Admin, Supervisor, Cashier, Warehouse, Accounting)

---

## Daftar Isi

1. [Konvensi Umum](#1-konvensi-umum)
   - [1.1 Format Request](#format-request)
   - [1.2 Format Response](#format-response)
   - [1.3 Status Dokumen](#status-dokumen)
   - [1.4 Module Ownership Mapping](#14-module-ownership-mapping)
2. [Autentikasi](#2-autentikasi)
3. [Master Data](#3-master-data)
   - [3.1 Produk & Varian](#31-produk--varian)
   - [3.2 Kategori Produk](#32-kategori-produk)
   - [3.3 Supplier](#33-supplier)
   - [3.4 Customer](#34-customer)
   - [3.5 Satuan (Unit)](#35-satuan-unit)
   - [3.6 Chart of Accounts (COA)](#36-chart-of-accounts-coa)
   - [3.7 Payment Methods](#37-payment-methods)
   - [3.8 Product Brands](#38-product-brands)
   - [3.9 Tax](#39-tax-optional)
   - [3.10 Currency](#310-currency-optional)
4. [Pricing](#4-pricing)
5. [Promotion](#5-promotion)
6. [POS (Point of Sale)](#6-pos-point-of-sale)
   - [6.1 Shift](#61-shift)
   - [6.2 Sesi Kasir](#62-sesi-kasir)
   - [6.3 Transaksi Penjualan](#63-transaksi-penjualan)
   - [6.4 Hold Bill](#64-hold-bill)
   - [6.5 Sales Return](#65-sales-return)
   - [6.6 Day Closing](#66-day-closing)
   - [6.7 Month Closing](#67-month-closing)
7. [Inventory](#7-inventory)
   - [7.1 Lokasi](#71-lokasi)
   - [7.2 Stok](#72-stok)
   - [7.3 Transfer Stok](#73-transfer-stok)
   - [7.4 Adjustment Stok](#74-adjustment-stok)
   - [7.5 Stock Opname](#75-stock-opname)
   - [7.6 Planogram](#76-planogram)
8. [Purchasing](#8-purchasing)
9. [Loyalty](#9-loyalty)
10. [Accounting](#10-accounting)
11. [Reporting](#11-reporting)
12. [System](#12-system)
13. [Error Reference](#13-error-reference)
14. [Webhook & Realtime Events](#14-webhook--realtime-events)

Catatan:

* Jika `Tax` dan `Currency` belum masuk MVP, tandai sebagai optional/future enhancement.
* Jika ingin lebih bersih secara domain, `Chart of Accounts` dan `Payment Methods` dapat tetap memakai endpoint saat ini, tetapi ownership teknisnya dicatat sebagai modul `Accounting`.


---

## 1. Konvensi Umum

### 1.1 Format Request

Semua request menggunakan `Content-Type: application/json` kecuali upload file.

```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### 1.2 Format Response

**Sukses:**
```json
{
  "success": true,
  "data": { },
  "message": "OK",
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 100
  }
}
```

**Gagal:**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

### Pagination

| Parameter | Default | Keterangan |
|-----------|---------|------------|
| `page` | 1 | Halaman aktif |
| `per_page` | 15 | Jumlah item per halaman |
| `sort` | `id` | Kolom untuk sorting |
| `order` | `desc` | `asc` atau `desc` |
| `search` | — | Pencarian teks bebas |

### 1.3 Status Dokumen

| Status | Keterangan |
|--------|------------|
| `DRAFT` | Dokumen belum diproses |
| `PENDING` | Menunggu approval |
| `APPROVED` | Sudah disetujui |
| `POSTED` | Sudah diposting ke sistem |
| `CANCELLED` | Dibatalkan |
| `REJECTED` | Ditolak |

### 1.4 Module Ownership Mapping

Section ini menjelaskan kepemilikan domain setiap endpoint agar struktur API selaras dengan struktur kode Modular Monolith.

| Endpoint Prefix                            | Module Owner         | Keterangan                                                        |
| ------------------------------------------ | -------------------- | ----------------------------------------------------------------- |
| `/auth/*`                                  | Auth                 | Login, logout, profile, password, token, session device           |
| `/users`                                   | System               | User management                                                   |
| `/roles`                                   | System               | Role management                                                   |
| `/permissions`                             | System               | Permission management                                             |
| `/approvals/*`                             | System               | Approval workflow                                                 |
| `/audit-logs`                              | System               | Audit trail                                                       |
| `/activity-logs`                           | System               | Activity log                                                      |
| `/notifications`                           | System               | Notification                                                      |
| `/settings`                                | System               | System setting                                                    |
| `/business-profile`                        | System               | Profil bisnis                                                     |
| `/suppliers`                               | MasterData           | Master supplier                                                   |
| `/customers`                               | MasterData           | Master customer                                                   |
| `/customer-categories`                     | MasterData           | Kategori customer                                                 |
| `/units`                                   | MasterData           | Master satuan                                                     |
| `/unit-conversions`                        | MasterData           | Konversi satuan                                                   |
| `/taxes`                                   | MasterData           | Master pajak jika masuk MVP                                       |
| `/currencies`                              | MasterData           | Master mata uang jika multi-currency masuk scope                  |
| `/products`                                | Product              | Produk dan varian                                                 |
| `/product-categories`                      | Product              | Kategori produk                                                   |
| `/product-brands`                          | Product              | Brand produk                                                      |
| `/products/barcode/*`                      | Product              | Pencarian produk via barcode                                      |
| `/products/{id}/images`                    | Product              | Upload gambar produk                                              |
| `/products/variants/{id}/cost-profile`     | Product              | Cost profile varian                                               |
| `/product-categories/{id}/account-mapping` | Product + Accounting | Mapping akun inventory, COGS, sales                               |
| `/price-lists`                             | Pricing              | Price list                                                        |
| `/price-change-requests`                   | Pricing              | Permohonan perubahan harga                                        |
| `/products/variants/{id}/price-history`    | Pricing              | Riwayat harga                                                     |
| `/promotions`                              | Promotion            | Promotion engine                                                  |
| `/promotions/simulate`                     | Promotion            | Simulasi promo                                                    |
| `/promotions/settings`                     | Promotion            | Setting promo                                                     |
| `/pos/*`                                   | POS                  | Shift, session, transaksi, hold bill, return, closing             |
| `/inventory/*`                             | Inventory            | Lokasi, stok, ledger, transfer, adjustment, opname, planogram     |
| `/purchasing/*`                            | Purchasing           | PR, PO, GR, invoice supplier, payment, AP, return, landed cost    |
| `/loyalty/*`                               | Loyalty              | Loyalty account, point, tier, reward, redemption                  |
| `/chart-of-accounts`                       | Accounting           | COA                                                               |
| `/payment-methods`                         | Accounting           | Metode pembayaran dan mapping COA                                 |
| `/accounting/*`                            | Accounting           | Journal, ledger, fiscal period, trial balance, financial snapshot |
| `/reports/*`                               | Reporting            | Report read-only                                                  |

Aturan ownership:

* Endpoint boleh berada pada prefix yang user-friendly, tetapi ownership teknis harus mengikuti module owner.
* `Payment Methods` dimiliki oleh `Accounting` karena setiap metode bayar wajib dihubungkan ke akun COA.
* `Product Account Mapping` adalah kolaborasi `Product` dan `Accounting`.
* `Reporting` bersifat read-only dan tidak boleh mem-posting transaksi.
* `Inventory Ledger` menjadi sumber kebenaran stok.
* `General Ledger` menjadi sumber utama laporan akuntansi.


---

## 2. Autentikasi

### Login

```
POST /auth/login
```

**Request Body:**
```json
{
  "email": "admin@toko.com",
  "password": "rahasia123"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "token": "1|abc123xyz...",
    "user": {
      "id": 1,
      "username": "admin",
      "full_name": "Admin Toko",
      "email": "admin@toko.com",
      "roles": ["admin"]
    }
  }
}
```

---

### Logout

```
POST /auth/logout
```

---

### Profil User

```
GET /auth/me
```

---

### Ganti Password

```
PUT /auth/password
```

**Request Body:**
```json
{
  "current_password": "lama123",
  "new_password": "baru456",
  "new_password_confirmation": "baru456"
}
```
### Forgot Password

```
POST /auth/forgot-password
```

Endpoint ini digunakan untuk mengirim instruksi reset password kepada user.

**Request Body:**

```json
{
  "email": "admin@toko.com"
}
```

**Response:**

```json
{
  "success": true,
  "message": "Reset password instruction has been sent"
}
```

---

### Reset Password

```
POST /auth/reset-password
```

Endpoint ini digunakan untuk mengganti password menggunakan token reset password.

**Request Body:**

```json
{
  "email": "admin@toko.com",
  "token": "reset-token",
  "password": "passwordbaru123",
  "password_confirmation": "passwordbaru123"
}
```

**Response:**

```json
{
  "success": true,
  "message": "Password has been reset"
}
```

---

### Refresh Token / Regenerate Token

```
POST /auth/token/refresh
```

Endpoint ini digunakan jika sistem membutuhkan regenerasi token Sanctum.

Catatan:

* Jika menggunakan Laravel Sanctum personal access token biasa, refresh token tidak wajib.
* Alternatif paling sederhana adalah user melakukan login ulang.
* Endpoint ini hanya digunakan jika sistem ingin mendukung rotasi token.

**Header:**

```text
Authorization: Bearer {token}
```

**Response:**

```json
{
  "success": true,
  "data": {
    "token": "2|newtoken..."
  },
  "message": "Token refreshed"
}
```

---

### Daftar Session Device

```
GET /auth/sessions
```

Endpoint ini digunakan untuk melihat daftar device/session aktif milik user.

**Response:**

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "device_name": "Chrome on Windows",
      "ip_address": "192.168.1.10",
      "last_used_at": "2025-06-01 10:00:00",
      "current_device": true
    }
  ]
}
```

---

### Revoke Session Device

```
DELETE /auth/sessions/{id}
```

Endpoint ini digunakan untuk menghapus session/token device tertentu.

**Response:**

```json
{
  "success": true,
  "message": "Session revoked"
}
```

---

## 3. Master Data

### 3.1 Produk & Varian

#### Daftar Produk

```
GET /products
```

| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `search` | string | Cari berdasarkan nama/kode produk |
| `category_id` | integer | Filter berdasarkan kategori |
| `brand_id` | integer | Filter berdasarkan brand |
| `is_active` | boolean | Filter status aktif |
| `is_sellable` | boolean | Filter produk yang bisa dijual |

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "product_code": "PRD-001",
      "product_name": "Baju Polo Pria",
      "product_category_id": 3,
      "brand_id": 1,
      "is_sellable": true,
      "is_purchasable": true,
      "is_active": true,
      "variants": [
        {
          "id": 10,
          "sku": "PRD-001-M-RED",
          "variant_name": "Size M - Merah",
          "base_unit_id": 1,
          "weight": 0.3,
          "is_active": true,
          "barcodes": [
            { "barcode": "8991234567890", "is_primary": true }
          ]
        }
      ]
    }
  ],
  "meta": { "total": 120, "current_page": 1, "per_page": 15 }
}
```

---

#### Buat Produk

```
POST /products
```

**Request Body:**
```json
{
  "product_category_id": 3,
  "brand_id": 1,
  "product_code": "PRD-002",
  "product_name": "Celana Chino",
  "description": "Celana bahan chino premium",
  "is_sellable": true,
  "is_purchasable": true,
  "variants": [
    {
      "sku": "PRD-002-32",
      "variant_name": "Size 32",
      "base_unit_id": 1,
      "weight": 0.5,
      "barcodes": ["8991234560001"],
      "attributes": [
        { "attribute_value_id": 5 }
      ]
    }
  ]
}
```

---

#### Detail, Update, Hapus Produk

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| `GET` | `/products/{id}` | Detail produk |
| `PUT` | `/products/{id}` | Update produk |
| `DELETE` | `/products/{id}` | Hapus produk (soft delete) |

---

#### Cari Produk via Barcode

```
GET /products/barcode/{barcode}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "product_variant_id": 10,
    "sku": "PRD-001-M-RED",
    "variant_name": "Size M - Merah",
    "product_name": "Baju Polo Pria",
    "selling_price": 150000,
    "stock_available": 25
  }
}
```

---

#### Upload Gambar Produk

```
POST /products/{id}/images
Content-Type: multipart/form-data
```

```
images[]: (file)
```

---

#### Cost Profile Varian

```
GET  /products/variants/{variant_id}/cost-profile
PUT  /products/variants/{variant_id}/cost-profile
```

**Request Body (PUT):**
```json
{
  "costing_method": "FIFO",
  "reorder_point": 10,
  "reorder_qty": 50
}
```

Nilai `costing_method`: `FIFO`, `AVERAGE`

---

#### Product Account Mapping

```
GET  /product-categories/{id}/account-mapping
PUT  /product-categories/{id}/account-mapping
```

**Request Body (PUT):**
```json
{
  "inventory_account_id": 5,
  "cogs_account_id": 12,
  "sales_account_id": 8
}
```

---

### 3.2 Kategori Produk

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| `GET` | `/product-categories` | Daftar kategori (tree) |
| `POST` | `/product-categories` | Buat kategori |
| `GET` | `/product-categories/{id}` | Detail kategori |
| `PUT` | `/product-categories/{id}` | Update kategori |
| `DELETE` | `/product-categories/{id}` | Hapus kategori |

**Request Body (POST/PUT):**
```json
{
  "parent_id": null,
  "code": "FASHION",
  "name": "Fashion",
  "description": "Kategori fashion dan pakaian",
  "is_active": true
}
```

---

### 3.3 Supplier

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| `GET` | `/suppliers` | Daftar supplier |
| `POST` | `/suppliers` | Buat supplier |
| `GET` | `/suppliers/{id}` | Detail supplier |
| `PUT` | `/suppliers/{id}` | Update supplier |
| `DELETE` | `/suppliers/{id}` | Hapus supplier (soft delete) |

**Request Body (POST/PUT):**
```json
{
  "supplier_code": "SUP-001",
  "supplier_name": "PT Maju Jaya Textile",
  "phone": "02112345678",
  "email": "sales@majujaya.com",
  "address": "Jl. Industri No. 1, Jakarta",
  "contact_person": "Budi Santoso",
  "payment_term_days": 30,
  "tax_number": "01.234.567.8-901.000",
  "is_active": true
}
```

---

### 3.4 Customer

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| `GET` | `/customers` | Daftar customer |
| `POST` | `/customers` | Buat customer |
| `GET` | `/customers/{id}` | Detail customer |
| `PUT` | `/customers/{id}` | Update customer |
| `DELETE` | `/customers/{id}` | Hapus customer (soft delete) |
| `GET` | `/customers/{id}/loyalty` | Info poin loyalty customer |
| `GET` | `/customers/{id}/transactions` | Riwayat transaksi customer |

**Request Body (POST/PUT):**
```json
{
  "customer_category_id": 1,
  "customer_code": "CUST-001",
  "customer_name": "Andi Wijaya",
  "phone": "08123456789",
  "email": "andi@email.com",
  "address": "Jl. Merdeka No. 5, Bandung",
  "birth_date": "1990-05-15",
  "join_date": "2024-01-01"
}
```

---

### 3.5 Satuan (Unit)

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| `GET` | `/units` | Daftar satuan |
| `POST` | `/units` | Buat satuan |
| `PUT` | `/units/{id}` | Update satuan |
| `DELETE` | `/units/{id}` | Hapus satuan |
| `POST` | `/unit-conversions` | Buat konversi satuan |
| `GET` | `/unit-conversions` | Daftar konversi satuan |

**Request Body Unit:**
```json
{
  "code": "PCS",
  "name": "Pieces",
  "is_base": true
}
```

**Request Body Konversi:**
```json
{
  "from_unit_id": 2,
  "to_unit_id": 1,
  "multiplier": 12
}
```

---

### 3.6 Chart of Accounts (COA)

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| `GET` | `/chart-of-accounts` | Daftar akun (tree) |
| `POST` | `/chart-of-accounts` | Buat akun |
| `GET` | `/chart-of-accounts/{id}` | Detail akun |
| `PUT` | `/chart-of-accounts/{id}` | Update akun |
| `DELETE` | `/chart-of-accounts/{id}` | Hapus akun |

**Request Body (POST/PUT):**
```json
{
  "parent_id": null,
  "account_code": "1-1001",
  "account_name": "Kas Toko",
  "account_type": "ASSET",
  "normal_balance": "DEBIT",
  "is_postable": true,
  "is_active": true
}
```

Nilai `account_type`: `ASSET`, `LIABILITY`, `EQUITY`, `REVENUE`, `EXPENSE`

Nilai `normal_balance`: `DEBIT`, `CREDIT`

---

### 3.7 Payment Methods

> **Baru di v2.0** — Metode bayar sekarang dikelola sebagai master data dan wajib di-mapping ke akun COA. `method_type` `LOYALTY_POINT` digunakan agar poin bisa menjadi baris pembayaran pada split bill POS.

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| `GET` | `/payment-methods` | Daftar metode bayar |
| `POST` | `/payment-methods` | Buat metode bayar |
| `GET` | `/payment-methods/{id}` | Detail metode bayar |
| `PUT` | `/payment-methods/{id}` | Update metode bayar |
| `DELETE` | `/payment-methods/{id}` | Nonaktifkan metode bayar |

**Request Body (POST/PUT):**
```json
{
  "method_code": "CASH-01",
  "method_name": "Tunai",
  "method_type": "CASH",
  "is_cash": true,
  "account_id": 5,
  "is_active": true
}
```

Nilai `method_type`: `CASH`, `QRIS`, `DEBIT`, `CREDIT_CARD`, `TRANSFER`, `LOYALTY_POINT`, `OTHER`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "method_code": "CASH-01",
      "method_name": "Tunai",
      "method_type": "CASH",
      "is_cash": true,
      "account_id": 5,
      "account_name": "Kas Toko",
      "is_active": true
    },
    {
      "id": 2,
      "method_code": "QRIS-01",
      "method_name": "QRIS",
      "method_type": "QRIS",
      "is_cash": false,
      "account_id": 6,
      "account_name": "Bank BCA",
      "is_active": true
    },
    {
      "id": 3,
      "method_code": "POINT-01",
      "method_name": "Loyalty Point",
      "method_type": "LOYALTY_POINT",
      "is_cash": false,
      "account_id": 15,
      "account_name": "Kewajiban Poin Loyalty",
      "is_active": true
    }
  ]
}

 **Domain Ownership:** Walaupun endpoint `Payment Methods` berada pada area master data, ownership teknis modul ini adalah `Accounting`, karena setiap metode bayar wajib dihubungkan ke akun COA melalui field `account_id`. Mapping ini digunakan saat sistem membuat auto journal dari transaksi POS, supplier payment, refund, dan transaksi pembayaran lainnya.

Catatan implementasi:

* `PaymentMethod` disimpan pada modul `Accounting`.
* Mapping COA menggunakan `payment_methods.account_id`.
* Tidak perlu membuat tabel `payment_method_account_mappings` jika satu metode bayar hanya membutuhkan satu akun utama.
* Jika di masa depan satu metode bayar membutuhkan banyak akun seperti clearing account, fee account, receivable account, atau settlement account, barulah dibuat tabel mapping tambahan.

```

### 3.8 Product Brands

```
GET /product-brands
```

| Parameter   | Tipe    | Keterangan                            |
| ----------- | ------- | ------------------------------------- |
| `search`    | string  | Cari berdasarkan kode atau nama brand |
| `is_active` | boolean | Filter status aktif                   |

**Response:**

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "brand_code": "NIKE",
      "brand_name": "Nike",
      "description": "Brand sepatu dan apparel",
      "logo_url": "https://example.com/storage/brands/logos/nike.png",
      "is_active": true
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 1
  }
}
```

---

### Buat Brand Produk

```
POST /product-brands
```

**Request Body:**

```json
{
  "brand_code": "ADIDAS",
  "brand_name": "Adidas",
  "description": "Brand sport apparel",
  "is_active": true
}
```

---

### Detail Brand Produk

```
GET /product-brands/{id}
```

---

### Update Brand Produk

```
PUT /product-brands/{id}
```

**Request Body:**

```json
{
  "brand_code": "ADIDAS",
  "brand_name": "Adidas Official",
  "description": "Brand sport apparel",
  "is_active": true
}
```

---

### Hapus Brand Produk

```
DELETE /product-brands/{id}
```

Catatan:

* Hapus brand menggunakan soft delete.
* Brand yang masih digunakan produk tidak boleh dihapus permanen.
* Jika brand tidak boleh digunakan lagi, ubah `is_active` menjadi `false`.

---

### Upload Logo Brand

```
POST /product-brands/{id}/logo
Content-Type: multipart/form-data
```

**Form Data:**

```text
logo: (file)
```

**Response:**

```json
{
  "success": true,
  "data": {
    "id": 1,
    "brand_code": "ADIDAS",
    "brand_name": "Adidas Official",
    "logo_url": "https://example.com/storage/brands/logos/adidas.png"
  },
  "message": "Brand logo uploaded"
}
```
## 3.9 Tax

> Section ini bersifat optional. Gunakan jika sistem membutuhkan master pajak yang fleksibel. Jika belum dibutuhkan, tax dapat tetap disimpan sebagai field transaksi seperti `tax_amount` atau `tax_total`.

### Daftar Pajak

```
GET /taxes
```

### Buat Pajak

```
POST /taxes
```

**Request Body:**

```json
{
  "tax_code": "PPN11",
  "tax_name": "PPN 11%",
  "tax_rate": 11,
  "is_inclusive": false,
  "account_id": 20,
  "is_active": true
}
```

### Update Pajak

```
PUT /taxes/{id}
```

### Hapus Pajak

```
DELETE /taxes/{id}
```

Catatan:

* Jika `account_id` diisi, pajak dapat dipetakan ke COA.
* Jika pajak belum ingin dipisah sebagai master, endpoint ini dapat ditunda.

---

## 3.10 Currency

> Section ini bersifat optional. Gunakan jika sistem mendukung multi-currency. Untuk fase awal single currency IDR, section ini dapat ditandai sebagai future enhancement.

### Daftar Currency

```
GET /currencies
```

### Buat Currency

```
POST /currencies
```

**Request Body:**

```json
{
  "currency_code": "IDR",
  "currency_name": "Indonesian Rupiah",
  "symbol": "Rp",
  "decimal_places": 0,
  "is_base": true,
  "is_active": true
}
```

### Update Currency

```
PUT /currencies/{id}
```

### Hapus Currency

```
DELETE /currencies/{id}
```

---

### Exchange Rate Optional

```
GET  /exchange-rates
POST /exchange-rates
```

**Request Body:**

```json
{
  "from_currency_id": 2,
  "to_currency_id": 1,
  "rate": 15750,
  "valid_date": "2025-06-01"
}
```

Catatan:

* Exchange rate hanya diperlukan jika transaksi multi-currency diaktifkan.
* Untuk MVP single currency, cukup gunakan IDR sebagai base currency.


---

## 4. Pricing

### Daftar Price List

```
GET /price-lists
```

---

### Buat Price List

```
POST /price-lists
```

**Request Body:**
```json
{
  "price_list_code": "RETAIL",
  "price_list_name": "Harga Retail",
  "description": "Harga standar untuk umum",
  "is_default": true,
  "is_active": true
}
```

---

### Tambah Item ke Price List

```
POST /price-lists/{id}/items
```

**Request Body:**
```json
{
  "product_variant_id": 10,
  "selling_price": 150000,
  "minimum_qty": 1,
  "valid_from": "2024-01-01 00:00:00",
  "valid_until": null
}
```

---

### Bulk Update Harga

```
PUT /price-lists/{id}/items/bulk
```

**Request Body:**
```json
{
  "items": [
    { "product_variant_id": 10, "selling_price": 145000 },
    { "product_variant_id": 11, "selling_price": 130000 }
  ]
}
```

---

### Mapping Price List ke Kategori Customer

```
POST /price-lists/{id}/customer-categories
```

**Request Body:**
```json
{
  "customer_category_id": 2
}
```

---

### Price Change Request (Permohonan Ubah Harga)

```
POST   /price-change-requests
GET    /price-change-requests
GET    /price-change-requests/{id}
POST   /price-change-requests/{id}/approve
POST   /price-change-requests/{id}/reject
```

**Request Body (POST):**
```json
{
  "notes": "Update harga akhir tahun",
  "items": [
    {
      "product_variant_id": 10,
      "old_price": 150000,
      "new_price": 155000,
      "reason": "Kenaikan bahan baku"
    }
  ]
}
```

---

### Riwayat Harga Varian

```
GET /products/variants/{variant_id}/price-history
```

---

## 5. Promotion

### Daftar Promosi

```
GET /promotions
```

| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `status` | string | `DRAFT`, `ACTIVE`, `INACTIVE`, `EXPIRED` |
| `valid_date` | date | Filter promosi aktif pada tanggal tertentu |

---

### Buat Promosi

```
POST /promotions
```

**Request Body:**
```json
{
  "promotion_code": "PROMO-AGUSTUS",
  "promotion_name": "Diskon Kemerdekaan 17%",
  "description": "Promo khusus bulan Agustus",
  "priority": 1,
  "stackable": false,
  "valid_from": "2024-08-01 00:00:00",
  "valid_until": "2024-08-31 23:59:59",
  "earn_point_allowed": true,
  "redeem_point_allowed": true,
  "conditions": [
    {
      "condition_type": "MIN_AMOUNT",
      "operator": ">=",
      "condition_value": "200000"
    }
  ],
  "rewards": [
    {
      "reward_type": "PERCENTAGE",
      "reward_value": 17
    }
  ],
  "targets": [
    {
      "target_type": "ALL_PRODUCT"
    }
  ],
  "limits": {
    "max_usage": 500,
    "max_usage_per_customer": 1
  },
  "schedules": [
    {
      "day_of_week": null,
      "start_time": null,
      "end_time": null
    }
  ]
}
```

Nilai `condition_type`: `MIN_AMOUNT`, `MIN_QTY`, `DAY_OF_WEEK`, `CUSTOMER_CATEGORY`, `PRODUCT`, `CATEGORY`

Nilai `reward_type`: `PERCENTAGE`, `FIXED_AMOUNT`, `FREE_PRODUCT`, `SPECIAL_PRICE`

Nilai `target_type`: `PRODUCT`, `CATEGORY`, `ALL_PRODUCT`

---

### Aktivasi / Nonaktifkan Promosi

```
POST /promotions/{id}/activate
POST /promotions/{id}/deactivate
```

---

### Simulasi Promosi

```
POST /promotions/simulate
```

**Request Body:**
```json
{
  "customer_id": 1,
  "items": [
    { "product_variant_id": 10, "qty": 2, "unit_price": 150000 }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "subtotal": 300000,
    "applied_promotions": [
      {
        "promotion_code": "PROMO-AGUSTUS",
        "promotion_name": "Diskon Kemerdekaan 17%",
        "discount_amount": 51000
      }
    ],
    "total_discount": 51000,
    "grand_total": 249000
  }
}
```

---

### Pengaturan Promosi (Margin Protection)

```
GET /promotions/settings
PUT /promotions/settings
```

**Request Body (PUT):**
```json
{
  "margin_protection_mode": "WARNING",
  "allow_negative_margin": false
}
```

Nilai `margin_protection_mode`: `BLOCK`, `WARNING`, `ALLOW`

---

## 6. POS (Point of Sale)

### 6.1 Shift

> **Baru di v2.0** — Shift wajib dikonfigurasi sebelum kasir bisa membuka sesi. `shift_id` menjadi referensi di `sales_sessions`.

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| `GET` | `/pos/shifts` | Daftar shift |
| `POST` | `/pos/shifts` | Buat shift |
| `GET` | `/pos/shifts/{id}` | Detail shift |
| `PUT` | `/pos/shifts/{id}` | Update shift |
| `DELETE` | `/pos/shifts/{id}` | Hapus shift |

**Request Body (POST/PUT):**
```json
{
  "shift_code": "PAGI",
  "shift_name": "Shift Pagi",
  "start_time": "08:00:00",
  "end_time": "14:00:00",
  "is_active": true
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "shift_code": "PAGI",
    "shift_name": "Shift Pagi",
    "start_time": "08:00:00",
    "end_time": "14:00:00",
    "is_active": true
  }
}
```

---

### 6.2 Sesi Kasir

#### Buka Sesi

```
POST /pos/sessions/open
```

> **Update v2.0:** Field `shift_id` ditambahkan. Kasir wajib memilih shift saat membuka sesi.

**Request Body:**
```json
{
  "shift_id": 1,
  "opening_cash": 500000
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 5,
    "session_no": "SES-2024080001",
    "shift_id": 1,
    "shift_name": "Shift Pagi",
    "opened_at": "2024-08-01 08:00:00",
    "opening_cash": 500000,
    "status": "OPEN"
  }
}
```

---

#### Tutup Sesi

```
POST /pos/sessions/{id}/close
```

**Request Body:**
```json
{
  "closing_cash": 1250000
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 5,
    "session_no": "SES-2024080001",
    "status": "CLOSED",
    "opening_cash": 500000,
    "closing_cash": 1250000,
    "cash_difference": 0,
    "closed_at": "2024-08-01 14:05:00"
  }
}
```

---

#### Sesi Aktif

```
GET /pos/sessions/active
```

---

#### Riwayat Sesi

```
GET /pos/sessions
```

| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `shift_id` | integer | Filter per shift |
| `date_from` | date | Tanggal awal |
| `date_to` | date | Tanggal akhir |
| `status` | string | `OPEN`, `CLOSED` |

---

### 6.3 Transaksi Penjualan

#### Buat Transaksi

```
POST /pos/transactions
```

> **Update v2.0:** `payment_method` string diganti `payment_method_id` (integer, FK ke tabel `payment_methods`). Untuk pembayaran dengan poin loyalty, gunakan `payment_method_id` yang bertipe `LOYALTY_POINT` dan isi `points_used`. Satu transaksi dapat memiliki beberapa baris `payments` (split payment).

**Request Body:**
```json
{
  "sales_session_id": 5,
  "customer_id": 1,
  "items": [
    {
      "product_variant_id": 10,
      "qty": 2,
      "unit_price": 150000,
      "discount_amount": 0
    }
  ],
  "promotion_ids": [3],
  "payments": [
    {
      "payment_method_id": 1,
      "amount": 200000,
      "reference_no": null,
      "points_used": null
    },
    {
      "payment_method_id": 3,
      "amount": 100000,
      "reference_no": null,
      "points_used": 1000
    }
  ]
}
```

> Catatan: Jika `payment_method_id` bertipe `LOYALTY_POINT`, isi `points_used` dengan jumlah poin aktual yang digunakan. Field `amount` diisi nilai Rupiah setara poin tersebut berdasarkan konfigurasi loyalty (`point_value`).

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 101,
    "transaction_no": "TRX-2024080001",
    "subtotal": 300000,
    "discount_total": 0,
    "tax_total": 0,
    "grand_total": 300000,
    "payment_status": "PAID",
    "loyalty_points_earned": 200,
    "loyalty_points_redeemed": 1000,
    "loyalty_points_value_redeemed": 100000,
    "payments": [
      {
        "payment_method_id": 1,
        "method_name": "Tunai",
        "method_type": "CASH",
        "amount": 200000,
        "points_used": null
      },
      {
        "payment_method_id": 3,
        "method_name": "Loyalty Point",
        "method_type": "LOYALTY_POINT",
        "amount": 100000,
        "points_used": 1000
      }
    ]
  }
}
```

---

#### Detail Transaksi

```
GET /pos/transactions/{id}
```

---

#### Daftar Transaksi

```
GET /pos/transactions
```

| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `session_id` | integer | Filter per sesi |
| `customer_id` | integer | Filter per customer |
| `date_from` | date | Tanggal awal |
| `date_to` | date | Tanggal akhir |
| `payment_status` | string | `UNPAID`, `PARTIAL`, `PAID`, `VOID` |

---

#### Void Transaksi

```
POST /pos/transactions/{id}/void
```

**Request Body:**
```json
{
  "void_reason": "Kesalahan input item"
}
```

> Void hanya bisa dilakukan dalam sesi yang sama dan oleh user dengan permission `pos.void`.

---

### 6.4 Hold Bill

#### Hold Transaksi

```
POST /pos/holds
```

**Request Body:**
```json
{
  "sales_session_id": 5,
  "customer_id": 1,
  "items": [
    {
      "product_variant_id": 10,
      "qty": 1,
      "unit_price": 150000,
      "discount_amount": 0
    }
  ],
  "notes": "Pelanggan akan kembali dalam 10 menit"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 8,
    "hold_no": "HOLD-2024080001",
    "status": "HELD",
    "grand_total": 150000,
    "created_at": "2024-08-01 10:15:00"
  }
}
```

---

#### Daftar Hold

```
GET /pos/holds
```

| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `session_id` | integer | Filter per sesi |
| `status` | string | `HELD`, `RECALLED`, `CANCELLED`, `CONVERTED` |

---

#### Recall Hold

```
POST /pos/holds/{id}/recall
```

---

#### Cancel Hold

```
POST /pos/holds/{id}/cancel
```

---

### 6.5 Sales Return

#### Buat Retur Penjualan

```
POST /pos/returns
```

**Request Body:**
```json
{
  "sales_transaction_id": 101,
  "reason": "Barang cacat, tidak sesuai pesanan",
  "items": [
    {
      "sales_transaction_item_id": 55,
      "product_variant_id": 10,
      "return_qty": 1,
      "unit_price": 150000
    }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 3,
    "return_no": "RET-2024080001",
    "status": "DRAFT",
    "refund_total": 150000,
    "created_at": "2024-08-01 11:00:00"
  }
}
```

---

#### Posting Retur

```
POST /pos/returns/{id}/post
```

> Posting retur akan otomatis menambah stok kembali ke inventory ledger dan membuat journal reversal.

---

#### Daftar Retur

```
GET /pos/returns
```

| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `date_from` | date | Tanggal awal |
| `date_to` | date | Tanggal akhir |
| `status` | string | `DRAFT`, `POSTED`, `CANCELLED` |

---

### 6.6 Day Closing

> **Baru di v2.0** — Tutup harian merekap seluruh sesi kasir dalam satu hari. Wajib dilakukan sebelum membuka sesi hari berikutnya jika konfigurasi sistem mengharuskan.

#### Status Tutup Harian

```
GET /pos/day-closings/{date}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "closing_date": "2024-08-01",
    "total_transactions": 85,
    "total_sales": 12500000,
    "total_cash": 8000000,
    "total_non_cash": 4500000,
    "status": "OPEN"
  }
}
```

---

#### Eksekusi Tutup Harian

```
POST /pos/day-closings/close
```

**Request Body:**
```json
{
  "closing_date": "2024-08-01"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "closing_date": "2024-08-01",
    "total_transactions": 85,
    "total_sales": 12500000,
    "total_cash": 8000000,
    "total_non_cash": 4500000,
    "status": "CLOSED",
    "closed_by": 1,
    "closed_at": "2024-08-01 23:00:00"
  }
}
```

---

#### Riwayat Tutup Harian

```
GET /pos/day-closings
```

| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `date_from` | date | Tanggal awal |
| `date_to` | date | Tanggal akhir |
| `status` | string | `OPEN`, `CLOSED` |

---

### 6.7 Month Closing

> **Baru di v2.0** — Tutup bulanan menutup periode operasional bulan berjalan. Setelah tutup bulanan, tidak ada transaksi POS baru yang dapat di-post ke periode tersebut. Berjalan paralel dengan `fiscal_periods` di modul accounting.

#### Status Tutup Bulanan

```
GET /pos/month-closings/{year}/{month}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "closing_year": 2024,
    "closing_month": 8,
    "status": "OPEN",
    "notes": null
  }
}
```

---

#### Eksekusi Tutup Bulanan

```
POST /pos/month-closings/close
```

**Request Body:**
```json
{
  "closing_year": 2024,
  "closing_month": 8,
  "notes": "Tutup operasional Agustus 2024"
}
```

---

#### Riwayat Tutup Bulanan

```
GET /pos/month-closings
```

---

## 7. Inventory

### 7.1 Lokasi

> **Update v2.0:** `location_type` diperluas. Ditambahkan field `is_stock_bearing`, `is_external`, `address`, `valid_from`, `valid_to` untuk mendukung gudang sewa musiman. Lokasi `RACK` dan `DISPLAY` dengan `is_stock_bearing = false` berfungsi sebagai data planogram saja, tidak memiliki saldo stok sendiri.

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| `GET` | `/inventory/locations` | Daftar lokasi |
| `POST` | `/inventory/locations` | Buat lokasi |
| `GET` | `/inventory/locations/{id}` | Detail lokasi |
| `PUT` | `/inventory/locations/{id}` | Update lokasi |

**Request Body (POST/PUT):**
```json
{
  "parent_id": null,
  "location_code": "WH-01",
  "location_name": "Gudang Utama",
  "location_type": "STORE_WAREHOUSE",
  "is_stock_bearing": true,
  "is_external": false,
  "address": null,
  "valid_from": null,
  "valid_to": null,
  "is_active": true
}
```

Nilai `location_type`:

| Nilai | Keterangan | `is_stock_bearing` Default |
|-------|------------|---------------------------|
| `STORE_WAREHOUSE` | Gudang utama di lokasi toko | `true` |
| `RENTED_WAREHOUSE` | Gudang sewa musiman (Lebaran, dll) | `true` |
| `RACK` | Rak penyimpanan di gudang (planogram) | `false` |
| `DISPLAY` | Rak display area jual (planogram) | `false` |
| `RECEIVING` | Area penerimaan barang | `true` |
| `RETURN_AREA` | Area barang retur | `true` |
| `DAMAGED_AREA` | Area barang rusak/cacat | `true` |

> Untuk gudang sewa musiman (`RENTED_WAREHOUSE`), wajib isi `is_external = true`, `address`, `valid_from`, dan `valid_to`.

**Contoh Gudang Sewa Lebaran:**
```json
{
  "location_code": "WH-LEBARAN-2025",
  "location_name": "Gudang Sewa Lebaran 2025",
  "location_type": "RENTED_WAREHOUSE",
  "is_stock_bearing": true,
  "is_external": true,
  "address": "Jl. Pergudangan Selatan No. 12, Bekasi",
  "valid_from": "2025-03-01",
  "valid_to": "2025-04-15",
  "is_active": true
}
```

---

### 7.2 Stok

#### Cek Saldo Stok

```
GET /inventory/balances
```

| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `product_variant_id` | integer | Filter per varian |
| `location_id` | integer | Filter per lokasi |
| `low_stock` | boolean | Tampilkan hanya yang di bawah reorder point |

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "product_variant_id": 10,
      "sku": "PRD-001-M-RED",
      "variant_name": "Size M - Merah",
      "location_id": 1,
      "location_name": "Gudang Utama",
      "location_type": "STORE_WAREHOUSE",
      "qty_on_hand": 50,
      "qty_reserved": 5,
      "qty_available": 45
    }
  ]
}
```

---

#### Ledger Stok

```
GET /inventory/ledgers
```

| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `product_variant_id` | integer | Filter per varian |
| `location_id` | integer | Filter per lokasi |
| `transaction_type` | string | `PURCHASE`, `SALE`, `TRANSFER_IN`, `TRANSFER_OUT`, `ADJUSTMENT_IN`, `ADJUSTMENT_OUT`, `RETURN_IN`, `RETURN_OUT`, `OPNAME` |
| `date_from` | date | Tanggal awal |
| `date_to` | date | Tanggal akhir |

---

### 7.3 Transfer Stok

#### Buat Transfer

```
POST /inventory/transfers
```

**Request Body:**
```json
{
  "source_location_id": 1,
  "destination_location_id": 2,
  "transfer_date": "2024-08-01",
  "remarks": "Transfer dari gudang utama ke gudang sewa Lebaran",
  "items": [
    {
      "inventory_batch_id": 15,
      "transfer_qty": 100
    }
  ]
}
```

> Transfer stok hanya dapat dilakukan antara lokasi dengan `is_stock_bearing = true`. Tidak bisa transfer dari/ke lokasi `RACK` atau `DISPLAY` (planogram).

---

#### Posting Transfer

```
POST /inventory/transfers/{id}/post
```

---

#### Daftar Transfer

```
GET /inventory/transfers
```

| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `source_location_id` | integer | Filter lokasi asal |
| `destination_location_id` | integer | Filter lokasi tujuan |
| `status` | string | `DRAFT`, `POSTED`, `CANCELLED` |
| `date_from` | date | Tanggal awal |
| `date_to` | date | Tanggal akhir |

---

### 7.4 Adjustment Stok

#### Buat Adjustment

```
POST /inventory/adjustments
```

**Request Body:**
```json
{
  "adjustment_date": "2024-08-01",
  "adjustment_type": "MINUS",
  "reason": "Barang rusak saat pengiriman",
  "items": [
    {
      "inventory_batch_id": 15,
      "adjustment_qty": 2,
      "notes": "Kotak penyok, barang tidak layak jual"
    }
  ]
}
```

Nilai `adjustment_type`: `PLUS`, `MINUS`

---

#### Approve / Reject Adjustment

```
POST /inventory/adjustments/{id}/approve
POST /inventory/adjustments/{id}/reject
```

---

#### Daftar Adjustment

```
GET /inventory/adjustments
```

---

### 7.5 Stock Opname

#### Buat Opname

```
POST /inventory/opnames
```

**Request Body:**
```json
{
  "inventory_location_id": 1,
  "opname_date": "2024-08-31"
}
```

---

#### Input Hasil Hitung Fisik

```
PUT /inventory/opnames/{id}/count
```

**Request Body:**
```json
{
  "items": [
    {
      "inventory_batch_id": 15,
      "physical_qty": 48
    }
  ]
}
```

---

#### Approve & Posting Opname

```
POST /inventory/opnames/{id}/approve
POST /inventory/opnames/{id}/post
```

---

#### Daftar Opname

```
GET /inventory/opnames
```

---

### 7.6 Planogram

> **Baru di v2.0** — Planogram mencatat posisi fisik barang di rak (RACK/DISPLAY) atau di area tertentu dalam gudang. Bersifat informasi penempatan saja, tidak memengaruhi saldo stok.

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| `GET` | `/inventory/planograms` | Daftar planogram |
| `POST` | `/inventory/planograms` | Buat entri planogram |
| `GET` | `/inventory/planograms/{id}` | Detail planogram |
| `PUT` | `/inventory/planograms/{id}` | Update posisi |
| `DELETE` | `/inventory/planograms/{id}` | Hapus entri planogram |

**Request Body (POST/PUT):**
```json
{
  "product_variant_id": 10,
  "location_id": 5,
  "position_code": "A-02-03",
  "notes": "Rak A, baris 2, kolom 3",
  "is_active": true
}
```

> `location_id` dapat mengarah ke lokasi `RACK`, `DISPLAY`, maupun lokasi stock-bearing seperti `STORE_WAREHOUSE` (untuk menandai posisi fisik di dalam gudang).

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 12,
    "product_variant_id": 10,
    "sku": "PRD-001-M-RED",
    "variant_name": "Size M - Merah",
    "location_id": 5,
    "location_name": "Rak Display A",
    "location_type": "DISPLAY",
    "position_code": "A-02-03",
    "notes": "Rak A, baris 2, kolom 3",
    "is_active": true
  }
}
```

---

#### Daftar Planogram per Lokasi

```
GET /inventory/planograms?location_id={id}
```

| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `location_id` | integer | Filter per lokasi |
| `product_variant_id` | integer | Filter per varian |
| `is_active` | boolean | Filter status aktif |

---

## 8. Purchasing

### Purchase Request (PR)

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| `POST` | `/purchasing/requests` | Buat PR |
| `GET` | `/purchasing/requests` | Daftar PR |
| `GET` | `/purchasing/requests/{id}` | Detail PR |
| `POST` | `/purchasing/requests/{id}/approve` | Approve PR |
| `POST` | `/purchasing/requests/{id}/reject` | Reject PR |

**Request Body (POST):**
```json
{
  "request_date": "2024-08-01",
  "remarks": "Permintaan restok bulan Agustus",
  "items": [
    {
      "product_variant_id": 10,
      "requested_qty": 50,
      "notes": "Prioritas tinggi"
    }
  ]
}
```

---

### Purchase Order (PO)

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| `POST` | `/purchasing/orders` | Buat PO |
| `GET` | `/purchasing/orders` | Daftar PO |
| `GET` | `/purchasing/orders/{id}` | Detail PO |
| `POST` | `/purchasing/orders/{id}/approve` | Approve PO |
| `POST` | `/purchasing/orders/{id}/cancel` | Cancel PO |

**Request Body (POST):**
```json
{
  "supplier_id": 1,
  "order_date": "2024-08-02",
  "expected_date": "2024-08-10",
  "items": [
    {
      "product_variant_id": 10,
      "ordered_qty": 50,
      "unit_cost": 80000,
      "discount_amount": 0,
      "tax_amount": 0
    }
  ]
}
```

---

### Goods Receipt (GR)

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| `POST` | `/purchasing/receipts` | Buat GR dari PO |
| `GET` | `/purchasing/receipts` | Daftar GR |
| `GET` | `/purchasing/receipts/{id}` | Detail GR |
| `POST` | `/purchasing/receipts/{id}/post` | Posting GR |

**Request Body (POST):**
```json
{
  "purchase_order_id": 20,
  "receipt_date": "2024-08-10",
  "remarks": "Penerimaan barang dari PT Maju Jaya",
  "items": [
    {
      "purchase_order_item_id": 35,
      "product_variant_id": 10,
      "received_qty": 50,
      "unit_cost": 80000,
      "batch_no": "BATCH-2024-08-001",
      "expiry_date": null
    }
  ]
}
```

> Posting GR akan otomatis menambah stok ke inventory ledger, membentuk `inventory_batches`, dan meng-trigger auto journal.

---

### Supplier Invoice

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| `POST` | `/purchasing/invoices` | Buat invoice supplier |
| `GET` | `/purchasing/invoices` | Daftar invoice |
| `GET` | `/purchasing/invoices/{id}` | Detail invoice |
| `POST` | `/purchasing/invoices/{id}/post` | Posting invoice |

**Request Body (POST):**
```json
{
  "supplier_id": 1,
  "goods_receipt_id": 10,
  "supplier_invoice_no": "INV/MJJ/2024/0801",
  "invoice_date": "2024-08-10",
  "due_date": "2024-09-10",
  "subtotal": 4000000,
  "tax_amount": 400000,
  "total_amount": 4400000,
  "items": [
    {
      "product_variant_id": 10,
      "qty": 50,
      "unit_cost": 80000,
      "tax_amount": 400000,
      "line_total": 4400000
    }
  ]
}
```

---

### Pembayaran Supplier

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| `POST` | `/purchasing/payments` | Buat pembayaran |
| `GET` | `/purchasing/payments` | Daftar pembayaran |
| `GET` | `/purchasing/payments/{id}` | Detail pembayaran |
| `POST` | `/purchasing/payments/{id}/post` | Posting pembayaran |

**Request Body (POST):**
```json
{
  "supplier_id": 1,
  "payment_date": "2024-09-05",
  "payment_method": "TRANSFER",
  "reference_no": "TRF-BCA-20240905",
  "total_amount": 4400000,
  "remarks": "Pembayaran invoice INV/MJJ/2024/0801",
  "allocations": [
    {
      "accounts_payable_id": 8,
      "allocated_amount": 4400000
    }
  ]
}
```

Nilai `payment_method` supplier: `CASH`, `TRANSFER`, `GIRO`, `CHEQUE`

---

### Accounts Payable

```
GET /purchasing/payables
```

| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `supplier_id` | integer | Filter per supplier |
| `status` | string | `OPEN`, `PARTIAL`, `PAID`, `OVERDUE`, `CANCELLED` |
| `overdue_only` | boolean | Tampilkan hanya yang jatuh tempo |

---

### Purchase Return

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| `POST` | `/purchasing/returns` | Buat retur pembelian |
| `GET` | `/purchasing/returns` | Daftar retur |
| `GET` | `/purchasing/returns/{id}` | Detail retur |
| `POST` | `/purchasing/returns/{id}/post` | Posting retur |

---

### Landed Cost

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| `POST` | `/purchasing/landed-costs` | Tambah biaya pengiriman ke GR |
| `GET` | `/purchasing/landed-costs` | Daftar landed cost |

**Request Body (POST):**
```json
{
  "goods_receipt_id": 10,
  "cost_type": "FREIGHT",
  "amount": 500000,
  "notes": "Ongkos kirim ekspedisi"
}
```

---

### Performa Supplier

```
GET  /purchasing/suppliers/{supplier_id}/performance
POST /purchasing/suppliers/{supplier_id}/performance
```

**Request Body (POST):**
```json
{
  "evaluation_period": "2024-08-01",
  "on_time_delivery_score": 90,
  "price_score": 85,
  "quality_score": 88,
  "service_score": 80,
  "notes": "Pengiriman tepat waktu, kualitas baik"
}
```

---

## 9. Loyalty

### Akun Loyalty Customer

```
GET /loyalty/accounts/{customer_id}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "account_no": "LYL-0000001",
    "customer_name": "Andi Wijaya",
    "current_balance": 1500,
    "membership_tier": "SILVER",
    "point_expiry_date": "2025-01-01"
  }
}
```

---

### Riwayat Transaksi Poin

```
GET /loyalty/accounts/{customer_id}/transactions
```

---

### Redeem Poin (via Reward Catalog)

```
POST /loyalty/redeem
```

**Request Body:**
```json
{
  "customer_id": 1,
  "reward_catalog_id": 2,
  "points_used": 500
}
```

> Ini adalah jalur redeem untuk hadiah dari reward catalog (voucher, produk gratis, dll). Untuk penggunaan poin sebagai alat bayar saat transaksi POS, gunakan `payment_method_id` bertipe `LOYALTY_POINT` di endpoint `POST /pos/transactions`.

---

### Approve / Reject Redemption

```
POST /loyalty/redemptions/{id}/approve
POST /loyalty/redemptions/{id}/reject
```

---

### Adjustment Poin Manual

```
POST /loyalty/adjustments
```

**Request Body:**
```json
{
  "customer_id": 1,
  "adjustment_type": "ADD",
  "points": 200,
  "reason": "Kompensasi poin tidak masuk"
}
```

Nilai `adjustment_type`: `ADD`, `DEDUCT`

---

### Konfigurasi Loyalty

```
GET  /loyalty/configuration
PUT  /loyalty/configuration
```

**Request Body (PUT):**
```json
{
  "point_expiry_months": 12,
  "minimum_redeem_points": 100,
  "point_value": 100,
  "allow_negative_point": false
}
```

> `point_value` = nilai Rupiah per 1 poin. Contoh: `100` artinya 1000 poin = Rp 100.000.

---

### Membership Tier

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| `GET` | `/loyalty/tiers` | Daftar tier |
| `POST` | `/loyalty/tiers` | Buat tier |
| `PUT` | `/loyalty/tiers/{id}` | Update tier |

**Request Body (POST/PUT):**
```json
{
  "tier_code": "SILVER",
  "tier_name": "Silver Member",
  "minimum_spending": 5000000,
  "minimum_points": 500,
  "point_multiplier": 1.5,
  "benefits": "Diskon 5% setiap transaksi"
}
```

---

### Reward Catalog

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| `GET` | `/loyalty/rewards` | Daftar reward |
| `POST` | `/loyalty/rewards` | Buat reward |
| `PUT` | `/loyalty/rewards/{id}` | Update reward |
| `DELETE` | `/loyalty/rewards/{id}` | Hapus reward |

**Request Body (POST/PUT):**
```json
{
  "reward_code": "RWD-001",
  "reward_name": "Voucher Belanja 50K",
  "reward_type": "VOUCHER",
  "point_required": 500,
  "voucher_amount": 50000,
  "stock_qty": 100,
  "is_active": true
}
```

Nilai `reward_type`: `VOUCHER`, `PRODUCT`, `LUCKY_DRAW`

---

## 10. Accounting

### Journal Entry

#### Daftar Journal

```
GET /accounting/journals
```

| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `status` | string | `DRAFT`, `POSTED`, `VOID` |
| `date_from` | date | Tanggal awal |
| `date_to` | date | Tanggal akhir |
| `reference_type` | string | Tipe referensi (misal `sales_transaction`) |

---

#### Buat Journal Manual

```
POST /accounting/journals
```

**Request Body:**
```json
{
  "entry_date": "2024-08-01",
  "reference_type": "manual",
  "reference_id": 0,
  "description": "Jurnal penyesuaian akhir bulan",
  "lines": [
    {
      "account_id": 10,
      "debit": 500000,
      "credit": 0,
      "description": "Beban perlengkapan"
    },
    {
      "account_id": 5,
      "debit": 0,
      "credit": 500000,
      "description": "Kas"
    }
  ]
}
```

---

#### Posting Journal

```
POST /accounting/journals/{id}/post
```

---

#### Void Journal

```
POST /accounting/journals/{id}/void
```

---

### General Ledger

```
GET /accounting/general-ledger
```

| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `account_id` | integer | Filter per akun COA |
| `date_from` | date | Tanggal awal |
| `date_to` | date | Tanggal akhir |
| `fiscal_period_id` | integer | Filter per periode fiskal |

---

### Fiscal Period

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| `GET` | `/accounting/fiscal-periods` | Daftar periode fiskal |
| `POST` | `/accounting/fiscal-periods` | Buat periode |
| `GET` | `/accounting/fiscal-periods/{id}` | Detail periode |
| `POST` | `/accounting/fiscal-periods/{id}/close` | Tutup periode |

**Request Body (POST):**
```json
{
  "period_name": "Agustus 2024",
  "start_date": "2024-08-01",
  "end_date": "2024-08-31"
}
```

---

### Trial Balance

```
GET /accounting/trial-balance?fiscal_period_id={id}
```

---

### Journal Template

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| `GET` | `/accounting/journal-templates` | Daftar template |
| `POST` | `/accounting/journal-templates` | Buat template |
| `GET` | `/accounting/journal-templates/{id}` | Detail template |
| `PUT` | `/accounting/journal-templates/{id}` | Update template |
| `DELETE` | `/accounting/journal-templates/{id}` | Hapus template |

**Request Body (POST):**
```json
{
  "template_code": "TMPL-SALE",
  "template_name": "Template Jurnal Penjualan",
  "event_type": "SALE",
  "description": "Auto-journal untuk setiap transaksi penjualan",
  "is_active": true,
  "lines": [
    {
      "account_id": 6,
      "direction": "DEBIT",
      "formula": "grand_total",
      "description": "Kas / Piutang",
      "sort_order": 1
    },
    {
      "account_id": 8,
      "direction": "CREDIT",
      "formula": "grand_total",
      "description": "Pendapatan Penjualan",
      "sort_order": 2
    }
  ]
}
```

---

### Accounting Rules

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| `GET` | `/accounting/rules` | Daftar aturan auto-journal |
| `POST` | `/accounting/rules` | Buat aturan |
| `PUT` | `/accounting/rules/{id}` | Update aturan |

---

## 11. Reporting

### Sales Report

```
GET /reports/sales
```

| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `date_from` | date | Tanggal awal |
| `date_to` | date | Tanggal akhir |
| `group_by` | string | `day`, `week`, `month`, `product`, `cashier` |
| `customer_id` | integer | Filter per customer |
| `format` | string | `json` (default), `csv`, `pdf` |

---

### Inventory Report

```
GET /reports/inventory/stock-card
GET /reports/inventory/movement
GET /reports/inventory/valuation
```

| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `product_variant_id` | integer | Wajib untuk stock card |
| `location_id` | integer | Opsional |
| `date_from` | date | Tanggal awal |
| `date_to` | date | Tanggal akhir |

---

### Financial Report (Laporan Keuangan)

```
POST /reports/financial/generate
```

**Request Body:**
```json
{
  "fiscal_period_id": 5,
  "report_type": "PROFIT_LOSS",
  "report_name": "Laba Rugi Agustus 2024"
}
```

Nilai `report_type`: `PROFIT_LOSS`, `BALANCE_SHEET`, `CASH_FLOW`, `CHANGES_IN_EQUITY`

---

```
GET /reports/financial/{snapshot_id}
```

---

### Purchase Report

```
GET /reports/purchasing/orders
GET /reports/purchasing/payables
GET /reports/purchasing/supplier-performance
```

---

## 12. System

### User & Role Management

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| `GET` | `/users` | Daftar user |
| `POST` | `/users` | Buat user |
| `GET` | `/users/{id}` | Detail user |
| `PUT` | `/users/{id}` | Update user |
| `DELETE` | `/users/{id}` | Nonaktifkan user |
| `GET` | `/roles` | Daftar role |
| `POST` | `/roles` | Buat role |
| `GET` | `/roles/{id}` | Detail role |
| `PUT` | `/roles/{id}` | Update role |
| `PUT` | `/roles/{id}/permissions` | Assign permission ke role |
| `GET` | `/permissions` | Daftar semua permission |
| `POST` | `/users/{id}/roles` | Assign role ke user |
| `DELETE` | `/users/{id}/roles/{role_id}` | Cabut role dari user |

**Request Body Buat User:**
```json
{
  "employee_code": "EMP-001",
  "username": "kasir.ahmad",
  "full_name": "Ahmad Kasir",
  "email": "ahmad@toko.com",
  "phone": "08123456789",
  "password": "rahasia123",
  "password_confirmation": "rahasia123",
  "is_active": true,
  "roles": ["cashier"]
}
```

> Role yang tersedia: `owner`, `admin`, `supervisor`, `cashier`, `warehouse`, `accounting`

**Request Body Assign Permission ke Role:**
```json
{
  "permission_ids": [1, 2, 5, 8]
}
```

---

### Approval

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| `GET` | `/approvals/pending` | Daftar approval menunggu aksi |
| `GET` | `/approvals/{id}` | Detail approval |
| `POST` | `/approvals/{id}/approve` | Approve |
| `POST` | `/approvals/{id}/reject` | Reject |

**Request Body Approve/Reject:**
```json
{
  "notes": "Disetujui, stok memang perlu ditambah"
}
```

---

### Audit Log

```
GET /audit-logs
```

| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `table_name` | string | Nama tabel |
| `record_id` | integer | ID record |
| `action` | string | `CREATE`, `UPDATE`, `DELETE` |
| `user_id` | integer | Filter per user |
| `date_from` | date | Tanggal awal |
| `date_to` | date | Tanggal akhir |

---

### Activity Log

```
GET /activity-logs
```

| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `module_name` | string | Nama modul |
| `user_id` | integer | Filter per user |
| `date_from` | date | Tanggal awal |

---

### Notifikasi

```
GET  /notifications
POST /notifications/{id}/read
POST /notifications/read-all
```

---

### System Settings

```
GET /settings
PUT /settings/{key}
```

**Request Body (PUT):**
```json
{
  "setting_value": "nilai_setting"
}
```

---

### Business Profile

```
GET /business-profile
PUT /business-profile
```

**Request Body (PUT):**
```json
{
  "business_name": "Toko Maju Jaya",
  "tax_number": "01.234.567.8-900.000",
  "phone": "02112345678",
  "email": "info@tokomajujaya.com",
  "address": "Jl. Pasar Baru No. 10, Jakarta"
}
```

---

### Document Sequences

```
GET  /document-sequences
POST /document-sequences
PUT  /document-sequences/{id}
```

**Request Body (POST):**
```json
{
  "document_code": "TRX",
  "document_name": "Sales Transaction",
  "prefix": "TRX",
  "reset_period": "MONTHLY"
}
```

Nilai `reset_period`: `NONE`, `YEARLY`, `MONTHLY`, `DAILY`

---

## 13. Error Reference

| HTTP Code | Error Code | Keterangan |
|-----------|------------|------------|
| `400` | `VALIDATION_ERROR` | Input tidak valid |
| `401` | `UNAUTHENTICATED` | Token tidak ada atau sudah kadaluarsa |
| `403` | `FORBIDDEN` | Tidak memiliki izin akses |
| `404` | `NOT_FOUND` | Data tidak ditemukan |
| `409` | `CONFLICT` | Konflik data (duplikat, status tidak valid) |
| `422` | `BUSINESS_RULE_VIOLATION` | Pelanggaran aturan bisnis |
| `429` | `TOO_MANY_REQUESTS` | Rate limit tercapai |
| `500` | `SERVER_ERROR` | Kesalahan server internal |

**Contoh Error Response:**
```json
{
  "success": false,
  "message": "Stok tidak mencukupi untuk transaksi ini.",
  "error_code": "BUSINESS_RULE_VIOLATION",
  "errors": {
    "product_variant_id_10": "Stok tersedia: 3, diminta: 5"
  }
}
```

**Contoh Error Margin Protection:**
```json
{
  "success": false,
  "message": "Diskon melebihi batas margin yang diizinkan.",
  "error_code": "BUSINESS_RULE_VIOLATION",
  "errors": {
    "promotion_id_3": "Margin akan menjadi negatif. Mode proteksi: BLOCK."
  }
}
```

---

## 14. Webhook & Realtime Events

Sistem menggunakan **Laravel Reverb** untuk event realtime via WebSocket.

### Koneksi WebSocket

```
ws://api.yourdomain.com:8080/app/{APP_KEY}
```

### Channel & Events

| Channel | Event | Pemicu |
|---------|-------|--------|
| `pos.session.{session_id}` | `TransactionCreated` | Transaksi baru berhasil |
| `pos.session.{session_id}` | `TransactionVoided` | Transaksi di-void |
| `pos.session.{session_id}` | `HoldCreated` | Hold bill dibuat |
| `pos.session.{session_id}` | `ReturnPosted` | Retur diposting |
| `pos.closing` | `DayClosingExecuted` | Tutup harian selesai |
| `pos.closing` | `MonthClosingExecuted` | Tutup bulanan selesai |
| `inventory.location.{location_id}` | `StockUpdated` | Perubahan stok |
| `notification.user.{user_id}` | `NotificationReceived` | Notifikasi baru |
| `accounting` | `JournalPosted` | Journal di-posting |
| `purchasing.order.{po_id}` | `GoodsReceived` | Barang diterima |
| `purchasing.order.{po_id}` | `PurchaseOrderApproved` | PO disetujui |
| `approval` | `ApprovalRequested` | Ada approval baru masuk |

### Contoh Payload Event `TransactionCreated`

```json
{
  "event": "TransactionCreated",
  "data": {
    "transaction_no": "TRX-2024080001",
    "grand_total": 300000,
    "payment_status": "PAID",
    "cashier": "Ahmad Kasir",
    "session_id": 5,
    "created_at": "2024-08-01T08:30:00Z"
  }
}
```

### Contoh Payload Event `StockUpdated`

```json
{
  "event": "StockUpdated",
  "data": {
    "product_variant_id": 10,
    "sku": "PRD-001-M-RED",
    "location_id": 1,
    "location_name": "Gudang Utama",
    "qty_on_hand": 45,
    "qty_available": 40,
    "transaction_type": "SALE",
    "updated_at": "2024-08-01T08:30:05Z"
  }
}
```

---

*Dokumen ini dibuat berdasarkan BRD versi 1.0 dan skema database `DATABASE.sql` versi terbaru. Versi API ini (v2.0) sudah sinkron dengan semua perubahan schema yang telah disepakati.*
