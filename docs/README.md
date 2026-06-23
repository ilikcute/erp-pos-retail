# Business Requirement Document (BRD)

## Sistem POS, Inventory & Accounting Terintegrasi

**Versi Dokumen:** 1.0  
**Status:** Draft Final
**Target Sistem:** POS, Inventory, Purchasing, Planogram, Loyalty Program, Accounting, dan Reporting dalam satu platform terintegrasi.  
**Arsitektur:** Laravel Modular Layered Monolith
**Backend:** Laravel 13
**Database:** MySQL 8
**Realtime:** Laravel reverb + Laravel Echo
**Queue management:** Laravel Horizon
**API authentication:** Laravel Sanctum
**Frontend:** Vue 3 + PWA + Composition API + Offline-First Strategy
**State Management:** Pinia
**Build Tool:** Vite
**Offline Support:** Vite PWA Plugin
**Index Db Wrapper:** Dexie.js
**Styling:** TailwindCSS + Headless UI
**Hardware Integration:** Printer ESC/POS Encoder + Web USB for Hardware + Web Serial API
**Mobile:** capacitor.js (Vue 3 + native wrapper)

---

# Daftar Isi

1. [BAB 1 — Arsitektur Sistem & Modul](#bab-1--arsitektur-sistem--modul)
    - [1.1 Tujuan BAB 1](#11-tujuan-bab-1)
    - [1.2 Standar 12 Modul Utama](#12-standar-12-modul-utama)
    - [1.3 Struktur Modular Layered Monolith](#13-struktur-modular-layered-monolith)
    - [1.4 Standar Routes](#14-standar-routes)
    - [1.5 Standar Layer Aplikasi](#15-standar-layer-aplikasi)
    - [1.6 Isi Standar Modul](#16-isi-standar-modul)
    - [1.7 Standar Nama Model Domain](#17-standar-nama-model-domain)
    - [1.8 Repository Pattern](#18-repository-pattern)
    - [1.9 Event, Listener, dan Job](#19-event-listener-dan-job)
    - [1.10 Standar Posting Transaksi](#110-standar-posting-transaksi)
    - [1.11 Kesimpulan BAB 1](#111-kesimpulan-bab-1)
2. [BAB 2 — Business Flow / Alur Bisnis Utama](#bab-2--business-flow--alur-bisnis-utama)
    - [2.1 Tujuan BAB 2](#21-tujuan-bab-2)
    - [2.2 Prinsip Umum Transaksi](#22-prinsip-umum-transaksi)
    - [2.3 Status Dokumen](#23-status-dokumen)
    - [2.4 POS Sales Flow](#24-pos-sales-flow)
    - [2.5 Purchasing Flow](#25-purchasing-flow)
    - [2.6 Inventory Flow](#26-inventory-flow)
    - [2.7 Accounting Flow](#27-accounting-flow)
    - [2.8 Closing & Reporting Flow](#28-closing--reporting-flow)
    - [2.9 Event, Notification & Queue Flow](#29-event-notification--queue-flow)
    - [2.10 Risiko dan Validasi Penting](#210-risiko-dan-validasi-penting)
    - [2.11 Kesimpulan BAB 2](#211-kesimpulan-bab-2)
3. [BAB 3 — Struktur Database & Domain Mapping](#bab-3--struktur-database--domain-mapping)
    - [3.1 Tujuan BAB 3](#31-tujuan-bab-3)
    - [3.2 Prinsip Desain Database](#32-prinsip-desain-database)
    - [3.3 Database sebagai Modular Monolith](#33-database-sebagai-modular-monolith)
    - [3.4 Sumber Kebenaran Data](#34-sumber-kebenaran-data)
    - [3.5 Mapping Tabel ke 12 Modul](#35-mapping-tabel-ke-12-modul)
    - [3.6 Standar Nama Model dan Table Mapping](#36-standar-nama-model-dan-table-mapping)
    - [3.7 Master Table vs Transaction Table vs Ledger Table](#37-master-table-vs-transaction-table-vs-ledger-table)
    - [3.8 Tabel yang Immutable Setelah POSTED](#38-tabel-yang-immutable-setelah-posted)
    - [3.9 Relasi Penting Antar Modul](#39-relasi-penting-antar-modul)
    - [3.10 Catatan Sinkronisasi dengan README dan API](#310-catatan-sinkronisasi-dengan-readme-dan-api)
    - [3.11 Rekomendasi Perubahan DATABASE.sql](#311-rekomendasi-perubahan-databasesql)
    - [3.12 Kesimpulan BAB 3](#312-kesimpulan-bab-3)
4. [BAB 4 — API Contract, Endpoint Standard & Response Format](#bab-4--api-contract-endpoint-standard--response-format)
    - [4.1 Tujuan BAB 4](#41-tujuan-bab-4)
    - [4.2 Prinsip Desain API](#42-prinsip-desain-api)
    - [4.3 API Versioning](#43-api-versioning)
    - [4.4 Authentication & Authorization Standard](#44-authentication--authorization-standard)
    - [4.5 Module Endpoint Ownership](#45-module-endpoint-ownership)
    - [4.6 HTTP Method Standard](#46-http-method-standard)
    - [4.7 Naming Convention Endpoint](#47-naming-convention-endpoint)
    - [4.8 Request Format Standard](#48-request-format-standard)
    - [4.9 Response Format Standard](#49-response-format-standard)
    - [4.10 Error Response Standard](#410-error-response-standard)
    - [4.11 Pagination, Filter, Search, Sort Standard](#411-pagination-filter-search-sort-standard)
    - [4.12 Status Code Standard](#412-status-code-standard)
    - [4.13 Endpoint Pattern per Modul](#413-endpoint-pattern-per-modul)
    - [4.14 Transaction Posting Endpoint Standard](#414-transaction-posting-endpoint-standard)
    - [4.15 Approval Endpoint Standard](#415-approval-endpoint-standard)
    - [4.16 Reporting Endpoint Standard](#416-reporting-endpoint-standard)
    - [4.17 Upload File Endpoint Standard](#417-upload-file-endpoint-standard)
    - [4.18 Realtime Event & Webhook Standard](#418-realtime-event--webhook-standard)
    - [4.19 API Security Rules](#419-api-security-rules)
    - [4.20 Kesimpulan BAB 4](#420-kesimpulan-bab-4)
5. [BAB 5 — Standar Implementasi Backend Laravel](#bab-5--standar-implementasi-backend-laravel)
    - [5.1 Tujuan BAB 5](#51-tujuan-bab-5)
    - [5.2 Prinsip Implementasi Backend](#52-prinsip-implementasi-backend)
    - [5.3 Struktur Backend per Modul](#53-struktur-backend-per-modul)
    - [5.4 Standar Controller](#54-standar-controller)
    - [5.5 Standar Form Request](#55-standar-form-request)
    - [5.6 Standar Action](#56-standar-action)
    - [5.7 Standar Service](#57-standar-service)
    - [5.8 Standar Repository](#58-standar-repository)
    - [5.9 Standar Repository Binding](#59-standar-repository-binding)
    - [5.10 Standar Model](#510-standar-model)
    - [5.11 Standar API Resource](#511-standar-api-resource)
    - [5.12 Standar Database Transaction](#512-standar-database-transaction)
    - [5.13 Standar Business Exception](#513-standar-business-exception)
    - [5.14 Standar Enum](#514-standar-enum)
    - [5.15 Standar Trait](#515-standar-trait)
    - [5.16 Standar Policy dan Permission](#516-standar-policy-dan-permission)
    - [5.17 Standar Number Generator](#517-standar-number-generator)
    - [5.18 Standar Event dan Listener](#519-standar-event-dan-listener)
    - [5.19 Standar Job](#520-standar-job)
    - [5.20 Standar Testing Backend](#521-standar-testing-backend)
    - [5.21 Contoh Implementasi Flow POS Lengkap](#522-contoh-implementasi-flow-pos-lengkap)
    - [5.22 Standar Penanganan File Upload](#523-standar-penanganan-file-upload)
    - [5.23 Standar Logging](#524-standar-logging)
    - [5.24 Standar Implementasi Modul Baru](#525-standar-implementasi-modul-baru)
    - [5.25 Kesimpulan BAB 5](#526-kesimpulan-bab-5)
6. [BAB 6 — Standar Implementasi Frontend Vue 3 + Inertia](#bab-6--standar-implementasi-frontend-vue-3--inertia)
    - [6.1 Tujuan BAB 6](#61-tujuan-bab-6)
    - [6.2 Prinsip Implementasi Frontend](#62-prinsip-implementasi-frontend)
    - [6.3 Struktur Folder Frontend](#63-struktur-folder-frontend)
    - [6.4 Standar Layout](#64-standar-layout)
    - [6.5 Standar Page per Modul](#65-standar-page-per-modul)
    - [6.6 Standar Component](#66-standar-component)
    - [6.7 Standar Vue 3 Composition API](#67-standar-vue-3-composition-api)
    - [6.8 Standar Inertia Form](#68-standar-inertia-form)
    - [6.9 Standar Error Handling Frontend](#69-standar-error-handling-frontend)
    - [6.10 Standar Permission-based UI](#610-standar-permission-based-ui)
    - [6.11 Standar Data Table](#611-standar-data-table)
    - [6.12 Standar Filter dan Search](#612-standar-filter-dan-search)
    - [6.13 Standar Modal dan Dialog](#613-standar-modal-dan-dialog)
    - [6.14 Standar Halaman POS](#614-standar-halaman-pos)
    - [6.15 Standar Halaman Inventory](#615-standar-halaman-inventory)
    - [6.16 Standar Halaman Purchasing](#616-standar-halaman-purchasing)
    - [6.17 Standar Halaman Accounting](#617-standar-halaman-accounting)
    - [6.18 Standar Halaman Reporting](#618-standar-halaman-reporting)
    - [6.19 Standar Realtime UI](#619-standar-realtime-ui)
    - [6.20 Standar Loading State](#620-standar-loading-state)
    - [6.21 Standar Empty State](#621-standar-empty-state)
    - [6.22 Standar Notification dan Toast](#622-standar-notification-dan-toast)
    - [6.23 Standar State Management](#623-standar-state-management)
    - [6.24 Standar Format Angka, Uang, dan Tanggal](#624-standar-format-angka-uang-dan-tanggal)
    - [6.25 Standar Frontend Testing](#625-standar-frontend-testing)
    - [6.26 Checklist Implementasi Halaman Baru](#626-checklist-implementasi-halaman-baru)
    - [6.27 Kesimpulan BAB 6](#627-kesimpulan-bab-6)
7. [BAB 7 — Security, RBAC, Approval & Audit Trail Standard](#bab-7--security-rbac-approval--audit-trail-standard)
    - [7.1 Tujuan BAB 7](#71-tujuan-bab-7)
    - [7.2 Prinsip Keamanan Sistem](#72-prinsip-keamanan-sistem)
    - [7.3 Authentication Standard](#73-authentication-standard)
    - [7.4 Password Security](#74-password-security)
    - [7.5 Session Device Standard](#75-session-device-standard)
    - [7.6 Role Based Access Control / RBAC](#76-role-based-access-control--rbac)
    - [7.7 Standar Role](#77-standar-role)
    - [7.8 Standar Permission](#78-standar-permission)
    - [7.9 Authorization Layer](#79-authorization-layer)
    - [7.10 Permission-based UI](#710-permission-based-ui)
    - [7.11 Approval Standard](#711-approval-standard)
    - [7.12 Approval Flow](#712-approval-flow)
    - [7.13 Approval Level](#713-approval-level)
    - [7.14 Separation of Duties](#714-separation-of-duties)
    - [7.15 Audit Trail Standard](#715-audit-trail-standard)
    - [7.16 Audit Trail untuk Transaksi](#716-audit-trail-untuk-transaksi)
    - [7.17 Activity Log Standard](#717-activity-log-standard)
    - [7.18 Data Sensitif](#718-data-sensitif)
    - [7.19 Security untuk POS](#719-security-untuk-pos)
    - [7.20 Security untuk Inventory](#720-security-untuk-inventory)
    - [7.21 Security untuk Purchasing](#721-security-untuk-purchasing)
    - [7.22 Security untuk Accounting](#722-security-untuk-accounting)
    - [7.23 Security untuk Reporting](#723-security-untuk-reporting)
    - [7.24 Rate Limiting](#724-rate-limiting)
    - [7.25 Idempotency untuk Transaksi Kritikal](#725-idempotency-untuk-transaksi-kritikal)
    - [7.26 Security Logging](#726-security-logging)
    - [7.27 Checklist Security per Fitur Baru](#727-checklist-security-per-fitur-baru)
    - [7.28 Kesimpulan BAB 7](#728-kesimpulan-bab-7)
8. [BAB 8 — Testing, QA & Acceptance Criteria](#bab-8--testing-qa--acceptance-criteria)
    - [8.1 Tujuan BAB 8](#81-tujuan-bab-8)
    - [8.2 Prinsip Testing](#82-prinsip-testing)
    - [8.3 Jenis Testing](#83-jenis-testing)
    - [8.4 Unit Test](#84-unit-test)
    - [8.5 Feature Test Backend](#85-feature-test-backend)
    - [8.6 API Test](#86-api-test)
    - [8.7 Frontend Test](#87-frontend-test)
    - [8.8 End-to-End Test](#88-end-to-end-test)
    - [8.9 Regression Test](#89-regression-test)
    - [8.10 Scenario Testing per Modul](#810-scenario-testing-per-modul)
    - [8.11 Acceptance Criteria](#811-acceptance-criteria)
    - [8.12 QA Checklist Sebelum Release](#812-qa-checklist-sebelum-release)
    - [8.13 Kesimpulan BAB 8](#813-kesimpulan-bab-8)
9. [BAB 9 — Deployment, Environment & DevOps Standard](#bab-9--deployment-environment--devops-standard)
    - [9.1 Tujuan BAB 9](#91-tujuan-bab-9)
    - [9.2 Environment Standard](#92-environment-standard)
    - [9.3 .env Standard](#93-env-standard)
    - [9.4 Deployment Backend Laravel](#94-deployment-backend-laravel)
    - [9.5 Deployment Frontend Vue + Inertia](#95-deployment-frontend-vue--inertia)
    - [9.6 Queue Standard](#96-queue-standard)
    - [9.7 Scheduler Standard](#97-scheduler-standard)
    - [9.8 Laravel Reverb Standard](#98-laravel-reverb-standard)
    - [9.9 Database Backup Standard](#99-database-backup-standard)
    - [9.10 Storage Standard](#910-storage-standard)
    - [9.11 Log Standard](#911-log-standard)
    - [9.12 Rollback Standard](#912-rollback-standard)
    - [9.13 Production Checklist](#913-production-checklist)
    - [9.14 Kesimpulan BAB 9](#914-kesimpulan-bab-9)
10. [BAB 10 — Migration, Refactor Roadmap & Implementation Phase](#bab-10--migration-refactor-roadmap--implementation-phase)
    - [10.1 Tujuan BAB 10](#101-tujuan-bab-10)
    - [10.2 Prinsip Refactor](#102-prinsip-refactor)
    - [10.3 Urutan Implementasi Modul](#103-urutan-implementasi-modul)
    - [10.4 Phase 1 — Auth, System, MasterData](#104-phase-1--auth-system-masterdata)
    - [10.5 Phase 2 — Product & Pricing](#105-phase-2--product--pricing)
    - [10.6 Phase 3 — POS Basic](#106-phase-3--pos-basic)
    - [10.7 Phase 4 — Inventory Ledger](#107-phase-4--inventory-ledger)
    - [10.8 Phase 5 — Purchasing](#108-phase-5--purchasing)
    - [10.9 Phase 6 — Accounting Posting](#109-phase-6--accounting-posting)
    - [10.10 Phase 7 — Closing & Reporting](#1010-phase-7--closing--reporting)
    - [10.11 Phase 8 — Promotion & Loyalty](#1011-phase-8--promotion--loyalty)
    - [10.12 Phase 9 — Optimization & Realtime](#1012-phase-9--optimization--realtime)
    - [10.13 Risiko Refactor](#1013-risiko-refactor)
    - [10.14 Kesimpulan BAB 10](#1014-kesimpulan-bab-10)
11. [BAB 11 — Seeder, Initial Data & Configuration Standard](#bab-11--seeder-initial-data--configuration-standard)
    - [11.1 Tujuan BAB 11](#111-tujuan-bab-11)
    - [11.2 Prinsip Seeder](#112-prinsip-seeder)
    - [11.3 Default Roles](#113-default-roles)
    - [11.4 Default Permissions](#114-default-permissions)
    - [11.5 Default Admin User](#115-default-admin-user)
    - [11.6 Default Chart of Accounts](#116-default-chart-of-accounts)
    - [11.7 Default Payment Methods](#117-default-payment-methods)
    - [11.8 Default Tax](#118-default-tax)
    - [11.9 Default Document Types & Sequences](#119-default-document-types--sequences)
    - [11.10 Default Warehouse Location](#1110-default-warehouse-location)
    - [11.11 Default System Settings](#1111-default-system-settings)
    - [11.12 Default Approval Rules](#1112-default-approval-rules)
    - [11.13 Seeder Checklist](#1113-seeder-checklist)
    - [11.14 Kesimpulan BAB 11](#1114-kesimpulan-bab-11)
12. [BAB 12 — Operational SOP](#bab-12--operational-sop)
    - [12.1 Tujuan BAB 12](#121-tujuan-bab-12)
    - [12.2 SOP Buka Shift](#122-sop-buka-shift)
    - [12.3 SOP Transaksi POS](#123-sop-transaksi-pos)
    - [12.4 SOP Hold Bill](#124-sop-hold-bill)
    - [12.5 SOP Void Transaksi](#125-sop-void-transaksi)
    - [12.6 SOP Sales Return](#126-sop-sales-return)
    - [12.7 SOP Tutup Sales Session](#127-sop-tutup-sales-session)
    - [12.8 SOP Day Closing](#128-sop-day-closing)
    - [12.9 SOP Month Closing](#129-sop-month-closing)
    - [12.10 SOP Purchase Order](#1210-sop-purchase-order)
    - [12.11 SOP Goods Receipt](#1211-sop-goods-receipt)
    - [12.12 SOP Supplier Invoice](#1212-sop-supplier-invoice)
    - [12.13 SOP Supplier Payment](#1213-sop-supplier-payment)
    - [12.14 SOP Stock Adjustment](#1214-sop-stock-adjustment)
    - [12.15 SOP Stock Opname](#1215-sop-stock-opname)
    - [12.16 SOP Manual Journal](#1216-sop-manual-journal)
    - [12.17 SOP Reporting](#1217-sop-reporting)
    - [12.18 SOP Backup Operasional](#1218-sop-backup-operasional)
    - [12.19 SOP Penanganan Error Operasional](#1219-sop-penanganan-error-operasional)
    - [12.20 Kesimpulan BAB 12](#1220-kesimpulan-bab-12)

---

# BAB 1 — Arsitektur Sistem & Modul

## 1.1 Tujuan BAB 1

BAB 1 menetapkan standar arsitektur teknis untuk sistem ERP POS agar project lebih konsisten, scalable, mudah dipelihara, dan siap dikembangkan ke tahap berikutnya.

Sistem tidak diposisikan sebagai aplikasi POS sederhana, tetapi sebagai **mini ERP retail** yang terintegrasi dengan inventory dan accounting:

Target pembuatan aplikasi:

1. Menstandarkan struktur folder Laravel.
2. Menentukan 12 modul utama.
3. Menetapkan alur layer aplikasi.
4. Menentukan penggunaan Repository Pattern.
5. Memisahkan tanggung jawab Controller, Request, Action, Service, Repository, Model, dan Resource.
6. Menyiapkan standar Event, Listener, Job, dan Posting Transaction.
7. Menjadi acuan perubahan `README.md`, `API_DOCUMENTATION.md`, dan struktur project Laravel.

---

## 1.2 Standar 12 Modul Utama

Sistem menggunakan **12 modul utama** sebagai standar final.

|  No | Modul      | Tanggung Jawab Utama                                                                  |
| --: | ---------- | ------------------------------------------------------------------------------------- |
|   1 | Auth       | Autentikasi, token, password, profile, session device                                 |
|   2 | System     | User, role, permission, approval, audit, notification, setting                        |
|   3 | MasterData | Supplier, customer, unit, tax, currency, master umum                                  |
|   4 | Product    | Brand, kategori, produk, varian, barcode, gambar, atribut, cost profile, mapping akun |
|   5 | Pricing    | Price list, price item, mapping harga customer, request perubahan harga               |
|   6 | Promotion  | Promo, kondisi, reward, target, usage, schedule, limit, simulation                    |
|   7 | POS        | Shift, sesi kasir, transaksi penjualan, pembayaran, hold bill, void, retur, closing   |
|   8 | Inventory  | Lokasi, batch, balance, ledger, transfer, adjustment, opname, reservation, planogram  |
|   9 | Purchasing | PR, PO, goods receipt, supplier invoice, payment, AP, retur beli, landed cost         |
|  10 | Loyalty    | Loyalty account, transaksi poin, konfigurasi, tier, reward, redemption, adjustment    |
|  11 | Accounting | COA, payment method, journal, GL, fiscal period, trial balance, financial snapshot    |
|  12 | Reporting  | Sales report, inventory report, purchase report, financial report, dashboard          |

Keputusan:

```text
Jumlah modul final = 12 modul
```

Tidak boleh membuat modul baru di luar 12 modul ini kecuali ada alasan teknis yang kuat dan disetujui sebagai perubahan arsitektur.

---

## 1.3 Struktur Modular Layered Monolith

Struktur project Laravel menggunakan pendekatan **Modular Layered Monolith**.

```text
app/
├── Actions/
│   ├── Auth/
│   ├── System/
│   ├── MasterData/
│   ├── Product/
│   ├── Pricing/
│   ├── Promotion/
│   ├── POS/
│   ├── Inventory/
│   ├── Purchasing/
│   ├── Loyalty/
│   ├── Accounting/
│   └── Reporting/
│
├── Services/
│   ├── Auth/
│   ├── System/
│   ├── MasterData/
│   ├── Product/
│   ├── Pricing/
│   ├── Promotion/
│   ├── POS/
│   ├── Inventory/
│   ├── Purchasing/
│   ├── Loyalty/
│   ├── Accounting/
│   └── Reporting/
│
├── Models/
│   ├── Auth/
│   ├── System/
│   ├── MasterData/
│   ├── Product/
│   ├── Pricing/
│   ├── Promotion/
│   ├── POS/
│   ├── Inventory/
│   ├── Purchasing/
│   ├── Loyalty/
│   ├── Accounting/
│   └── Reporting/
│
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       ├── Auth/
│   │       ├── System/
│   │       ├── MasterData/
│   │       ├── Product/
│   │       ├── Pricing/
│   │       ├── Promotion/
│   │       ├── POS/
│   │       ├── Inventory/
│   │       ├── Purchasing/
│   │       ├── Loyalty/
│   │       ├── Accounting/
│   │       └── Reporting/
│   │
│   ├── Requests/
│   │   ├── Auth/
│   │   ├── System/
│   │   ├── MasterData/
│   │   ├── Product/
│   │   ├── Pricing/
│   │   ├── Promotion/
│   │   ├── POS/
│   │   ├── Inventory/
│   │   ├── Purchasing/
│   │   ├── Loyalty/
│   │   ├── Accounting/
│   │   └── Reporting/
│   │
│   └── Resources/
│       ├── Auth/
│       ├── System/
│       ├── MasterData/
│       ├── Product/
│       ├── Pricing/
│       ├── Promotion/
│       ├── POS/
│       ├── Inventory/
│       ├── Purchasing/
│       ├── Loyalty/
│       ├── Accounting/
│       └── Reporting/
│
├── Repositories/
│   ├── Contracts/
│   │   ├── Auth/
│   │   ├── System/
│   │   ├── MasterData/
│   │   ├── Product/
│   │   ├── Pricing/
│   │   ├── Promotion/
│   │   ├── POS/
│   │   ├── Inventory/
│   │   ├── Purchasing/
│   │   ├── Loyalty/
│   │   ├── Accounting/
│   │   └── Reporting/
│   │
│   └── Eloquent/
│       ├── Auth/
│       ├── System/
│       ├── MasterData/
│       ├── Product/
│       ├── Pricing/
│       ├── Promotion/
│       ├── POS/
│       ├── Inventory/
│       ├── Purchasing/
│       ├── Loyalty/
│       ├── Accounting/
│       └── Reporting/
│
├── Events/
├── Listeners/
├── Jobs/
├── Enums/
├── Policies/
├── Support/
└── Traits/
```

Penjelasan folder pendukung:

| Folder      | Fungsi                                                                  |
| ----------- | ----------------------------------------------------------------------- |
| `Events`    | Menandai kejadian penting seperti transaksi posted, void, closing       |
| `Listeners` | Menangani notifikasi, audit, broadcast, side effect ringan              |
| `Jobs`      | Proses async non-kritikal seperti export report dan notifikasi          |
| `Enums`     | Status dokumen, jenis transaksi, payment type, journal status           |
| `Policies`  | Authorization per model/domain                                          |
| `Support`   | Helper class non-global seperti number generator, formatter, calculator |
| `Traits`    | Reusable behavior seperti `HasCreatedBy`, `HasPostedBy`, `HasUuid`      |

---

## 1.4 Standar Routes

File `routes/api.php` tidak boleh terlalu gemuk. Route harus dipisah berdasarkan modul.

```text
routes/
├── api.php
├── auth.php
├── system.php
├── master-data.php
├── product.php
├── pricing.php
├── promotion.php
├── pos.php
├── inventory.php
├── purchasing.php
├── loyalty.php
├── accounting.php
└── reporting.php
```

Contoh `routes/api.php`:

```php
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    require __DIR__.'/auth.php';

    Route::middleware(['auth:sanctum'])->group(function () {
        require __DIR__.'/system.php';
        require __DIR__.'/master-data.php';
        require __DIR__.'/product.php';
        require __DIR__.'/pricing.php';
        require __DIR__.'/promotion.php';
        require __DIR__.'/pos.php';
        require __DIR__.'/inventory.php';
        require __DIR__.'/purchasing.php';
        require __DIR__.'/loyalty.php';
        require __DIR__.'/accounting.php';
        require __DIR__.'/reporting.php';
    });
});
```

Catatan:

1. `auth.php` tidak seluruhnya boleh dibungkus `auth:sanctum`.
2. Endpoint login, forgot password, dan reset password harus public.
3. Endpoint logout, profile/me, change password, dan session device harus protected.
4. Route module lain harus berada di dalam middleware auth.
5. Permission middleware dapat ditambahkan pada route masing-masing modul.

---

## 1.5 Standar Layer Aplikasi

Setiap fitur API harus mengikuti alur layer berikut:

```text
Controller
-> Form Request
-> Action
-> Service
-> Repository Contract
-> Eloquent Repository
-> Model
-> Resource
```

Tanggung jawab tiap layer:

| Layer               | Tanggung Jawab                                |
| ------------------- | --------------------------------------------- |
| Controller          | Menerima request dan mengembalikan response   |
| Form Request        | Validasi input dan authorization awal         |
| Action              | Orkestrasi proses bisnis utama                |
| Service             | Logic domain yang bisa digunakan ulang        |
| Repository Contract | Interface query/data access                   |
| Eloquent Repository | Implementasi query menggunakan Eloquent       |
| Model               | Relasi tabel dan representasi entity database |
| Resource            | Format response API                           |

Aturan penting:

1. Controller tidak boleh berisi business logic.
2. Controller tidak boleh query langsung ke model.
3. Form Request tidak boleh memproses transaksi bisnis.
4. Action tidak boleh membuat format response API.
5. Service tidak boleh mengembalikan HTTP response.
6. Repository tidak boleh berisi business rule kompleks.
7. Model tidak boleh melakukan posting transaksi besar sendiri.
8. Resource tidak boleh menjalankan query berat.
9. Semua proses posting utama harus masuk Action.
10. Query kompleks dan reporting harus lewat Repository.

---

## 1.6 Isi Standar Modul

### 1.6.1 Auth

- Login
- Logout
- Refresh Token / Sanctum Token jika dibutuhkan
- Forgot Password
- Reset Password
- Change Password
- Profile / Me
- Session Device jika dibutuhkan

### 1.6.2 System

- User
- Role
- Permission
- Approval
- Audit Log
- Activity Log
- Notification
- System Setting
- Business Profile
- License

### 1.6.3 MasterData

- Supplier
- Customer
- Customer Category
- Unit
- Unit Conversion
- Tax
- Currency

Catatan:

- Tax dapat masuk MVP jika sistem membutuhkan pengaturan pajak fleksibel.
- Currency dapat ditandai sebagai future enhancement jika target awal hanya IDR.

### 1.6.4 Product

- Product Brand
- Product Category
- Product
- Product Variant
- Product Barcode
- Product Image
- Product Attribute
- Product Cost Profile
- Product Account Mapping

### 1.6.5 Pricing

- Price List
- Price List Item
- Customer Category Price Mapping
- Price Change Request
- Price History

### 1.6.6 Promotion

- Promotion
- Promotion Condition
- Promotion Reward
- Promotion Target
- Promotion Usage
- Promotion Schedule
- Promotion Simulation
- Promotion Limit
- Promotion Setting

### 1.6.7 POS

- Shift
- Sales Session
- Sales Transaction
- Sales Transaction Item
- Sales Payment
- Hold Bill
- Void Transaction
- Sales Return
- Day Closing
- Month Closing
- Receipt

### 1.6.8 Inventory

- Inventory Location
- Inventory Batch
- Inventory Balance
- Inventory Ledger
- Stock Transfer
- Stock Adjustment
- Stock Opname
- Stock Reservation
- Planogram

Catatan:

- Inventory Ledger adalah sumber kebenaran stok.
- Inventory Balance adalah saldo cepat hasil agregasi transaksi.
- Planogram hanya mencatat posisi fisik barang dan tidak memengaruhi saldo stok.

### 1.6.9 Purchasing

- Purchase Request
- Purchase Order
- Goods Receipt
- Supplier Invoice
- Supplier Payment
- Accounts Payable
- Purchase Return
- Landed Cost
- Supplier Performance

### 1.6.10 Loyalty

- Loyalty Account
- Loyalty Transaction
- Loyalty Configuration
- Membership Tier
- Reward Catalog
- Redemption
- Point Adjustment

### 1.6.11 Accounting

- Chart of Account
- Payment Method
- Payment Method COA Mapping
- Journal Entry
- Journal Line
- Journal Template
- Journal Template Line
- Accounting Rule
- Accounting Rule Line
- General Ledger
- Fiscal Period
- Trial Balance
- Financial Report Snapshot
- Financial Report Line

Catatan:

- Payment Method berada dalam domain Accounting karena wajib dihubungkan ke COA.
- Payment Method COA Mapping tidak perlu menjadi tabel terpisah jika mapping sudah disimpan di `payment_methods.account_id`.
- Journal yang sudah POSTED tidak boleh diedit.
- Koreksi jurnal harus menggunakan reversal journal.

### 1.6.12 Reporting

Reporting bersifat read-only.

Aturan Reporting:

- Reporting tidak boleh posting transaksi.
- Reporting tidak boleh mengubah jurnal.
- Reporting tidak boleh mengubah stok.
- Reporting tidak boleh mengubah data operasional.

Isi modul:

- Sales Report
- Inventory Report
- Stock Card
- Inventory Movement
- Inventory Valuation
- Purchase Report
- Accounts Payable Report
- Supplier Performance Report
- Financial Report
- Dashboard Report

---

## 1.7 Standar Nama Model Domain

Nama model harus konsisten dengan domain bisnis.

### POS

```text
Shift
SalesSession
SalesTransaction
SalesTransactionItem
SalesPayment
SalesPaymentAllocation
SalesVoid
SalesHold
SalesHoldItem
SalesReturn
SalesReturnItem
DayClosing
MonthClosing
```

Catatan:

- UI/API boleh memakai istilah “Hold Bill”.
- Model Laravel menggunakan `SalesHold`.
- Table menggunakan `sales_holds`.

### Inventory

```text
InventoryLocation
InventoryBatch
InventoryBalance
InventoryLedger
InventoryCostLayer
InventoryLedgerSnapshot
StockTransfer
StockTransferItem
StockAdjustment
StockAdjustmentItem
StockOpname
StockOpnameItem
StockReservation
Planogram
```

Catatan:

- Jika tabel fisik lokasi bernama `warehouse_locations`, model tetap boleh bernama `InventoryLocation`.
- Pada model gunakan `protected $table = 'warehouse_locations';`.

### Purchasing

```text
PurchaseRequest
PurchaseRequestItem
PurchaseOrder
PurchaseOrderItem
GoodsReceipt
GoodsReceiptItem
SupplierInvoice
SupplierInvoiceItem
SupplierPayment
SupplierPaymentAllocation
AccountsPayable
PurchaseReturn
PurchaseReturnItem
LandedCost
LandedCostAllocation
SupplierPerformance
```

### Accounting

```text
ChartOfAccount
PaymentMethod
JournalEntry
JournalLine
JournalTemplate
JournalTemplateLine
AccountingRule
AccountingRuleLine
GeneralLedger
FiscalPeriod
TrialBalance
FinancialReportSnapshot
FinancialReportLine
```

---

## 1.8 Repository Pattern

Repository pattern digunakan menyeluruh di semua modul.

Struktur repository:

```text
app/Repositories/
├── Contracts/
│   └── {Module}/
└── Eloquent/
    └── {Module}/
```

Binding repository dilakukan melalui `RepositoryServiceProvider`.

Contoh contract:

```php
namespace App\Repositories\Contracts\POS;

use App\Models\POS\SalesTransaction;

interface SalesTransactionRepositoryInterface
{
    public function create(array $data): SalesTransaction;

    public function findById(int $id): ?SalesTransaction;

    public function paginateBySession(int $sessionId, int $perPage = 15): mixed;
}
```

Contoh Eloquent repository:

```php
namespace App\Repositories\Eloquent\POS;

use App\Models\POS\SalesTransaction;
use App\Repositories\Contracts\POS\SalesTransactionRepositoryInterface;

class EloquentSalesTransactionRepository implements SalesTransactionRepositoryInterface
{
    public function create(array $data): SalesTransaction
    {
        return SalesTransaction::create($data);
    }

    public function findById(int $id): ?SalesTransaction
    {
        return SalesTransaction::query()->find($id);
    }

    public function paginateBySession(int $sessionId, int $perPage = 15): mixed
    {
        return SalesTransaction::query()
            ->where('sales_session_id', $sessionId)
            ->latest()
            ->paginate($perPage);
    }
}
```

---

## 1.9 Event, Listener, dan Job

Event digunakan untuk menandai kejadian penting dalam sistem.

Contoh event:

```text
SalesTransactionPosted
SalesTransactionVoided
SalesReturnPosted
GoodsReceiptPosted
SupplierInvoicePosted
SupplierPaymentPosted
StockTransferPosted
StockAdjustmentPosted
StockOpnamePosted
JournalEntryPosted
LoyaltyPointEarned
LoyaltyPointRedeemed
DayClosingCompleted
MonthClosingCompleted
```

Listener digunakan untuk:

- membuat notification,
- mencatat activity log,
- broadcast realtime,
- menjalankan side effect non-kritikal.

Job digunakan untuk:

- export report,
- generate report snapshot,
- kirim notifikasi,
- sinkronisasi data eksternal,
- proses ulang posting event yang gagal,
- backup atau maintenance task.

Prinsip:

```text
Proses kritikal seperti posting stok dan posting jurnal utama dilakukan dalam database transaction.
Proses non-kritikal seperti notifikasi dan export report boleh menggunakan Job.
```

---

## 1.10 Standar Posting Transaksi

Semua proses posting transaksi utama harus melalui Action.

Contoh Action:

```text
CreateSalesTransactionAction
VoidSalesTransactionAction
PostSalesReturnAction
PostGoodsReceiptAction
PostSupplierInvoiceAction
PostSupplierPaymentAction
PostStockTransferAction
PostStockAdjustmentAction
PostStockOpnameAction
PostJournalEntryAction
CloseDayAction
CloseMonthAction
```

Aturan posting:

1. Semua posting wajib menggunakan database transaction.
2. Semua posting wajib memiliki nomor dokumen unik.
3. Semua posting wajib mencatat `created_by` atau `posted_by`.
4. Dokumen POSTED tidak boleh diedit langsung.
5. Pembatalan harus menggunakan VOID, CANCEL, atau reversal.
6. Transaksi stok harus membentuk inventory ledger.
7. Transaksi finansial harus membentuk journal entry.
8. Journal entry harus balance antara debit dan credit.
9. Transaksi tidak boleh diposting ke periode yang sudah closed.
10. Proses gagal harus rollback seluruh transaksi.

---

## 1.11 Kesimpulan BAB 1

Keputusan final BAB 1:

```text
Arsitektur:
Laravel Modular Layered Monolith

Jumlah Modul:
12 Modul

Modul:
Auth
System
MasterData
Product
Pricing
Promotion
POS
Inventory
Purchasing
Loyalty
Accounting
Reporting

Layer:
Controller
-> Form Request
-> Action
-> Service
-> Repository Contract
-> Eloquent Repository
-> Model
-> Resource
```

Target dari BAB 1 adalah memastikan seluruh developer memiliki standar yang sama dalam menempatkan file, menulis business logic, membuat endpoint API, dan memisahkan tanggung jawab antar layer.

---

# BAB 2 — Business Flow / Alur Bisnis Utama

## 2.1 Tujuan BAB 2

BAB 2 menetapkan alur bisnis utama sistem agar setiap transaksi memiliki efek yang jelas terhadap modul lain.

BAB ini menjawab pertanyaan:

```text
Bagaimana transaksi berjalan?
Modul mana yang terlibat?
Data apa yang berubah?
Kapan stok bergerak?
Kapan jurnal terbentuk?
Kapan loyalty point masuk atau keluar?
Kapan transaksi dikunci?
Dari mana laporan membaca data?
```

Flow utama yang disepakati:

```text
1. POS Sales Flow
2. Purchasing Flow
3. Inventory Flow
4. Accounting Flow
5. Closing & Reporting Flow
```

BAB 2 tidak membahas struktur folder Laravel lagi. BAB ini fokus pada proses bisnis dan efek transaksi.

---

## 2.2 Prinsip Umum Transaksi

### 2.2.1 Semua Transaksi Harus Traceable

Setiap transaksi penting harus memiliki:

```text
document_no
status
created_by
approved_by jika perlu
posted_by jika perlu
posted_at
reference_type
reference_id
remarks / reason jika perlu
```

Tujuan:

1. Semua transaksi dapat diaudit.
2. Setiap efek stok dan jurnal dapat dilacak ke dokumen sumber.
3. Tidak ada perubahan data penting tanpa jejak user dan waktu.
4. Laporan dapat ditelusuri ke transaksi asli.

### 2.2.2 Dokumen POSTED Tidak Boleh Diedit

Dokumen yang sudah `POSTED` tidak boleh diubah langsung.

Jika terjadi kesalahan, gunakan:

```text
VOID
CANCEL
RETURN
REVERSAL JOURNAL
STOCK ADJUSTMENT
STOCK OPNAME
```

Bukan update atau delete transaksi lama.

### 2.2.3 Inventory Ledger adalah Sumber Kebenaran Stok

```text
Inventory Ledger = sumber kebenaran stok
Inventory Balance = saldo cepat / hasil agregasi
```

Setiap stok bertambah atau berkurang wajib membentuk:

```text
inventory_ledgers
inventory_balances update
```

### 2.2.4 Journal Entry adalah Sumber Kebenaran Accounting

```text
Journal Entry = dokumen akuntansi
Journal Line = detail debit/kredit
General Ledger = buku besar
Trial Balance = neraca saldo
Financial Report = hasil dari data accounting
```

Journal wajib balance:

```text
total_debit = total_credit
```

### 2.2.5 POS Harus Atomic

Transaksi POS tidak boleh setengah berhasil.

Contoh kondisi yang tidak boleh terjadi:

```text
Sales transaction berhasil
Stok gagal berkurang
Journal gagal dibuat
Loyalty gagal diproses
```

Maka proses POS utama wajib menggunakan database transaction.

---

## 2.3 Status Dokumen

Status dokumen standar:

| Status    | Keterangan                                                    |
| --------- | ------------------------------------------------------------- |
| DRAFT     | Dokumen masih dapat diedit dan belum berdampak ke stok/jurnal |
| PENDING   | Dokumen menunggu approval                                     |
| APPROVED  | Dokumen sudah disetujui dan siap diproses/posting             |
| POSTED    | Dokumen sudah berdampak ke stok/jurnal/ledger                 |
| CANCELLED | Dokumen dibatalkan sebelum atau sesuai aturan posting         |
| REJECTED  | Dokumen ditolak                                               |
| VOID      | Dokumen dibatalkan setelah posting dengan jejak audit         |
| CLOSED    | Dokumen/periode sudah selesai dan dikunci                     |

Aturan umum:

| Status    | Boleh Edit? |   Berdampak ke Stok/Jurnal? |
| --------- | ----------: | --------------------------: |
| DRAFT     |          Ya |                       Tidak |
| PENDING   |    Terbatas |                       Tidak |
| APPROVED  |    Terbatas |        Tidak / siap posting |
| POSTED    |       Tidak |                          Ya |
| CANCELLED |       Tidak | Tidak / reversal jika perlu |
| REJECTED  |       Tidak |                       Tidak |
| VOID      |       Tidak |        Ya, melalui reversal |
| CLOSED    |       Tidak |                Ya, terkunci |

---

## 2.4 POS Sales Flow

### 2.4.1 Tujuan

POS Sales Flow mengatur alur operasional kasir dari awal shift sampai transaksi penjualan selesai.

POS Flow fokus pada:

```text
session kasir
pemilihan produk
perhitungan harga
promosi
customer
payment
receipt
```

Efek lanjutan seperti inventory ledger, journal entry, loyalty transaction, audit log, dan reporting source dijelaskan lebih detail pada Inventory Flow, Accounting Flow, dan Closing & Reporting Flow.

### 2.4.2 Modul Terlibat

```text
POS
Product
Pricing
Promotion
Inventory
Loyalty
Accounting
System
Reporting
```

| Modul      | Peran                                                 |
| ---------- | ----------------------------------------------------- |
| POS        | Mengelola shift, session, transaksi, payment, receipt |
| Product    | Menyediakan product variant, barcode, status sellable |
| Pricing    | Menentukan harga jual                                 |
| Promotion  | Menghitung diskon dan promo                           |
| Inventory  | Validasi stok dan pengurangan stok                    |
| Loyalty    | Earn/redeem point                                     |
| Accounting | Auto journal                                          |
| System     | Permission, audit, cashier user                       |
| Reporting  | Membaca hasil transaksi                               |

### 2.4.3 Alur Utama POS

```text
Cashier Login
-> Open Shift
-> Open Sales Session
-> Scan / pilih produk
-> Ambil harga dari Pricing
-> Simulasi Promotion
-> Hitung subtotal, discount, tax, grand total
-> Pilih customer jika ada
-> Input payment
-> Validasi payment
-> Create Sales Transaction
-> Create Sales Transaction Items
-> Create Sales Payments
-> Trigger Inventory Flow
-> Trigger Accounting Flow
-> Trigger Loyalty Flow
-> Print / Send Receipt
-> Available for Reporting
```

### 2.4.4 Detail Tahap POS

#### A. Cashier Login

Kasir login menggunakan modul Auth.

Validasi:

```text
user aktif
role cashier / supervisor / admin
permission POS tersedia
```

#### B. Open Shift dan Sales Session

Kasir membuka sesi kasir berdasarkan shift aktif.

Data yang terbentuk:

```text
sales_sessions
```

Validasi:

```text
shift aktif
kasir belum punya session OPEN
opening_cash wajib diisi jika cash drawer digunakan
tanggal belum day closing
```

#### C. Scan / Pilih Produk

Kasir memilih produk melalui:

```text
barcode
search SKU
search product name
category
quick menu
```

Validasi produk:

```text
product aktif
variant aktif
is_sellable = true
barcode valid jika scan
```

#### D. Ambil Harga

Harga diambil dari Pricing module.

Prioritas harga dapat mengikuti:

```text
customer category price list
default price list
special price
manual price override jika permission tersedia
```

#### E. Simulasi Promotion

Promotion module menghitung diskon.

Contoh promo:

```text
discount percentage
discount amount
minimum purchase
customer category promo
product/category promo
```

#### F. Input Customer

Customer optional untuk transaksi retail umum.

Customer wajib jika:

```text
menggunakan loyalty point
earn point
redeem point
transaksi piutang jika nanti didukung
```

#### G. Input Payment

Payment dapat terdiri dari satu atau banyak metode bayar.

Contoh:

```text
Cash
QRIS
Debit
Credit Card
Transfer
Loyalty Point
Other
```

Aturan:

```text
payment_method_id wajib valid
payment total harus sama dengan grand_total
reference_no wajib untuk non-cash jika diperlukan
points_used wajib jika payment method bertipe LOYALTY_POINT
```

#### H. Create Sales Transaction

Data utama:

```text
sales_transactions
sales_transaction_items
sales_payments
sales_discounts jika ada
```

Pada POS normal, transaksi biasanya langsung `POSTED` setelah payment valid.

### 2.4.5 Validasi POS Wajib

```text
session kasir harus OPEN
shift harus aktif
produk harus aktif
produk harus sellable
harga harus ditemukan
stok harus cukup
payment total harus sama dengan grand_total
payment method harus aktif
loyalty point cukup jika dipakai
periode belum closing
user punya permission
```

### 2.4.6 Output POS Flow

| Output                    | Modul Tujuan     |
| ------------------------- | ---------------- |
| Sales Transaction         | POS              |
| Sales Items               | POS              |
| Sales Payments            | POS / Accounting |
| Inventory Ledger SALE_OUT | Inventory        |
| Journal Entry             | Accounting       |
| Loyalty Transaction       | Loyalty          |
| Receipt                   | POS              |
| Sales Report Source       | Reporting        |
| Audit Log                 | System           |

---

## 2.5 Purchasing Flow

### 2.5.1 Tujuan

Purchasing Flow mengatur proses pembelian barang dari kebutuhan internal sampai supplier dibayar.

Flow ini menjawab:

```text
Barang dibutuhkan dari mana?
Siapa yang approve?
Supplier mana yang dipilih?
Barang diterima kapan?
Invoice supplier dicatat kapan?
Hutang supplier terbentuk kapan?
Pembayaran supplier dilakukan kapan?
Jurnal accounting terbentuk kapan?
```

### 2.5.2 Modul Terlibat

```text
Purchasing
Inventory
Accounting
MasterData
System
Reporting
```

| Modul      | Peran                                                      |
| ---------- | ---------------------------------------------------------- |
| Purchasing | Mengelola PR, PO, Goods Receipt, Supplier Invoice, Payment |
| Inventory  | Menerima stok dari Goods Receipt                           |
| Accounting | Membentuk hutang, jurnal, dan general ledger               |
| MasterData | Menyediakan supplier, produk, unit                         |
| System     | Approval, audit log, permission                            |
| Reporting  | Membaca data pembelian, AP, dan supplier performance       |

### 2.5.3 Alur Utama Purchasing

```text
Purchase Request
-> Approval Purchase Request
-> Purchase Order
-> Approval Purchase Order
-> Goods Receipt
-> Post Goods Receipt
-> Supplier Invoice
-> Post Supplier Invoice
-> Accounts Payable
-> Supplier Payment
-> Post Supplier Payment
-> Accounting Posting
-> Reporting
```

### 2.5.4 Purchase Request

Purchase Request adalah permintaan internal untuk membeli barang.

```text
Warehouse / Admin membuat Purchase Request
-> input item dan qty yang dibutuhkan
-> submit approval
-> supervisor / owner approve atau reject
```

Data utama:

```text
purchase_requests
purchase_request_items
approval_requests
approval_histories
```

Status:

```text
DRAFT
-> PENDING
-> APPROVED
-> REJECTED
-> CLOSED
```

Catatan:

- Purchase Request belum menambah stok.
- Purchase Request belum membuat jurnal.
- Purchase Request hanya dokumen kebutuhan pembelian.

### 2.5.5 Purchase Order

Purchase Order adalah pesanan resmi ke supplier.

```text
Purchase Request approved
-> buat Purchase Order
-> pilih supplier
-> tentukan harga beli
-> tentukan expected date
-> submit approval
-> approve PO
```

Data utama:

```text
purchase_orders
purchase_order_items
suppliers
```

Status:

```text
DRAFT
-> PENDING
-> APPROVED
-> PARTIAL_RECEIVED
-> RECEIVED
-> CANCELLED
```

Catatan:

- PO belum menambah stok.
- PO belum membuat hutang.
- PO adalah komitmen pembelian ke supplier.

### 2.5.6 Goods Receipt

Goods Receipt adalah proses penerimaan barang dari supplier.

```text
Barang datang dari supplier
-> staff warehouse cek PO
-> input barang diterima
-> input batch / expiry jika ada
-> post Goods Receipt
```

Saat Goods Receipt diposting:

```text
create inventory batch
create inventory ledger PURCHASE_IN
update inventory balance
update PO received qty
```

Data utama:

```text
goods_receipts
goods_receipt_items
inventory_batches
inventory_ledgers
inventory_balances
```

Status:

```text
DRAFT
-> POSTED
-> CANCELLED
```

Catatan:

- Goods Receipt adalah titik stok bertambah.
- Goods Receipt harus masuk Inventory Flow.
- Goods Receipt boleh belum membentuk hutang jika supplier invoice belum diterima.

### 2.5.7 Supplier Invoice

Supplier Invoice adalah pencatatan tagihan supplier.

```text
Supplier mengirim invoice
-> accounting input invoice
-> cocokkan dengan Goods Receipt
-> validasi nilai tagihan
-> post Supplier Invoice
```

Saat Supplier Invoice diposting:

```text
create accounts payable
create journal entry
create journal lines
```

Data utama:

```text
supplier_invoices
supplier_invoice_items
accounts_payable
journal_entries
journal_lines
```

Contoh jurnal Supplier Invoice:

```text
Dr Inventory / Purchase
Dr Input Tax jika ada
Cr Accounts Payable
```

### 2.5.8 Supplier Payment

Supplier Payment adalah pembayaran hutang ke supplier.

```text
Pilih supplier
-> pilih invoice / AP yang akan dibayar
-> input metode pembayaran
-> input reference number
-> post payment
```

Saat Supplier Payment diposting:

```text
create supplier payment
create payment allocation
update accounts payable
create journal entry
```

Data utama:

```text
supplier_payments
supplier_payment_allocations
accounts_payable
journal_entries
journal_lines
```

Contoh jurnal Supplier Payment:

```text
Dr Accounts Payable
Cr Cash / Bank
```

### 2.5.9 Output Purchasing Flow

| Output                         | Modul Tujuan           |
| ------------------------------ | ---------------------- |
| Barang diterima                | Inventory              |
| Stok bertambah                 | Inventory              |
| Hutang supplier terbentuk      | Accounting             |
| Pembayaran tercatat            | Accounting             |
| Supplier performance terbentuk | Purchasing / Reporting |
| Laporan pembelian tersedia     | Reporting              |

### 2.5.10 Validasi Purchasing

```text
Supplier harus aktif
Product variant harus aktif
PO harus approved sebelum Goods Receipt
Qty Goods Receipt tidak boleh melebihi PO tanpa permission
Supplier Invoice harus cocok dengan Goods Receipt
Payment tidak boleh melebihi outstanding AP
Periode accounting belum closed
```

---

## 2.6 Inventory Flow

### 2.6.1 Tujuan

Inventory Flow mengatur seluruh pergerakan stok agar stok fisik, stok sistem, dan nilai persediaan tetap bisa diaudit.

Inventory Flow menjawab:

```text
Stok bertambah dari mana?
Stok berkurang karena apa?
Stok pindah lokasi ke mana?
Stok rusak dicatat bagaimana?
Selisih opname diproses bagaimana?
Saldo stok dihitung dari mana?
```

### 2.6.2 Modul Terlibat

```text
Inventory
Purchasing
POS
Accounting
Product
System
Reporting
```

| Modul      | Peran                                                          |
| ---------- | -------------------------------------------------------------- |
| Inventory  | Mengelola ledger, balance, batch, transfer, adjustment, opname |
| Purchasing | Sumber stok masuk dari Goods Receipt                           |
| POS        | Sumber stok keluar dari penjualan dan retur masuk              |
| Accounting | Mencatat nilai inventory dan COGS                              |
| Product    | Menyediakan product variant dan cost profile                   |
| System     | Approval, audit, permission                                    |
| Reporting  | Stock card, movement, valuation                                |

### 2.6.3 Prinsip Utama Inventory

```text
Semua pergerakan stok wajib masuk Inventory Ledger.
Inventory Balance hanya saldo cepat.
Inventory Ledger tidak boleh dihapus.
Koreksi stok harus lewat adjustment atau opname.
```

### 2.6.4 Jenis Pergerakan Stok

Movement type aktif untuk MVP:

```text
PURCHASE_IN
SALE_OUT
SALES_RETURN_IN
PURCHASE_RETURN_OUT
TRANSFER_OUT
TRANSFER_IN
ADJUSTMENT_IN
ADJUSTMENT_OUT
OPNAME_IN
OPNAME_OUT
```

Catatan konsistensi:

```text
RESERVATION dan RELEASE_RESERVATION tidak aktif pada MVP.
Hold Bill tidak membuat inventory ledger dan tidak melakukan stock reservation.
Validasi stok final dilakukan saat transaksi benar-benar diproses menjadi sales transaction.
```

Future enhancement:

```text
RESERVATION
RELEASE_RESERVATION
```

### 2.6.5 Alur Umum Inventory Movement

```text
Business Event
-> Validate Product Variant
-> Validate Inventory Location
-> Validate Stock Bearing Location
-> Validate Batch / Cost Layer
-> Create Inventory Ledger
-> Update Inventory Balance
-> Update Batch Qty
-> Create Accounting Posting jika berdampak nilai
-> Available for Inventory Report
```

### 2.6.6 Goods Receipt Stock In

Sumber: Purchasing.

```text
Goods Receipt Posted
-> create inventory batch
-> create inventory ledger PURCHASE_IN
-> increase inventory balance
```

### 2.6.7 POS Sale Stock Out

Sumber: POS.

```text
Sales Transaction Posted
-> validate stock available
-> select batch / cost layer
-> create inventory ledger SALE_OUT
-> decrease inventory balance
-> calculate COGS
```

Catatan:

- Untuk FIFO, sistem mengambil batch paling awal.
- Untuk average, sistem menggunakan average cost.
- Penjualan stok minus sebaiknya default `BLOCK`, kecuali ada override supervisor.

### 2.6.8 Sales Return Stock In

Sumber: POS Return.

```text
Sales Return Posted
-> validate original sales transaction
-> create inventory ledger SALES_RETURN_IN
-> increase inventory balance
-> reverse COGS jika diperlukan
```

### 2.6.9 Purchase Return Stock Out

Sumber: Purchasing.

```text
Purchase Return Posted
-> validate received batch
-> create inventory ledger PURCHASE_RETURN_OUT
-> decrease inventory balance
-> adjust AP / supplier credit jika diperlukan
```

### 2.6.10 Stock Transfer

Stock Transfer digunakan untuk memindahkan stok antar lokasi.

```text
Create Stock Transfer
-> validate source location
-> validate destination location
-> validate stock available
-> post transfer
-> create ledger TRANSFER_OUT
-> create ledger TRANSFER_IN
-> update source balance
-> update destination balance
```

Aturan:

```text
source location harus stock-bearing
destination location harus stock-bearing
RACK / DISPLAY non-stock-bearing tidak boleh menjadi lokasi transfer stok
```

### 2.6.11 Stock Adjustment

Stock Adjustment digunakan untuk koreksi stok manual.

```text
Create Adjustment
-> input reason
-> submit approval jika diperlukan
-> approve adjustment
-> post adjustment
-> create ledger ADJUSTMENT_IN / ADJUSTMENT_OUT
-> update inventory balance
-> create journal jika berdampak nilai
```

Aturan:

```text
Adjustment wajib memiliki reason
Adjustment minus harus validasi stok cukup
Adjustment besar wajib approval
```

### 2.6.12 Stock Opname

Stock Opname digunakan untuk mencocokkan stok sistem dengan stok fisik.

```text
Create Stock Opname
-> freeze expected qty
-> input physical qty
-> calculate variance
-> approve opname
-> post opname
-> create ledger OPNAME_IN / OPNAME_OUT
-> update inventory balance
-> create journal adjustment jika ada selisih nilai
```

### 2.6.13 Output Inventory Flow

| Output               | Fungsi                  |
| -------------------- | ----------------------- |
| Inventory Ledger     | Riwayat pergerakan stok |
| Inventory Balance    | Saldo stok cepat        |
| Inventory Batch      | Trace batch/expiry/cost |
| Inventory Cost Layer | Dasar COGS              |
| Stock Card           | Laporan kartu stok      |
| Inventory Valuation  | Nilai persediaan        |

---

## 2.7 Accounting Flow

### 2.7.1 Tujuan

Accounting Flow mengatur bagaimana setiap transaksi bisnis membentuk jurnal double-entry, general ledger, trial balance, dan laporan keuangan.

### 2.7.2 Modul Terlibat

```text
Accounting
POS
Purchasing
Inventory
Loyalty
Reporting
System
```

| Modul      | Peran                                              |
| ---------- | -------------------------------------------------- |
| Accounting | COA, journal, ledger, fiscal period, trial balance |
| POS        | Sumber event penjualan, return, void               |
| Purchasing | Sumber event invoice supplier dan payment          |
| Inventory  | Sumber event stock adjustment dan valuation        |
| Loyalty    | Sumber event poin dan redemption                   |
| Reporting  | Membaca GL dan snapshot                            |
| System     | Audit, approval, activity log                      |

### 2.7.3 Alur Utama Accounting

```text
Business Event
-> Resolve Accounting Rule
-> Resolve Journal Template
-> Create Journal Entry
-> Create Journal Lines
-> Validate Debit = Credit
-> Post Journal Entry
-> Update General Ledger
-> Update Trial Balance Source
-> Available for Financial Report
```

### 2.7.4 Business Event yang Membentuk Jurnal

```text
SALE_POSTED
SALE_VOIDED
SALES_RETURN_POSTED
SUPPLIER_INVOICE_POSTED
SUPPLIER_PAYMENT_POSTED
PURCHASE_RETURN_POSTED
STOCK_ADJUSTMENT_POSTED
STOCK_OPNAME_POSTED
LOYALTY_REDEEMED
MANUAL_JOURNAL_POSTED
DAY_CLOSING_COMPLETED
MONTH_CLOSING_COMPLETED
```

### 2.7.5 Jurnal Penjualan POS

Saat transaksi POS diposting:

```text
Dr Cash / Bank / Payment Method Account
Cr Sales Revenue
Cr Output Tax jika ada
```

Jika sistem memakai inventory perpetual dan COGS otomatis:

```text
Dr Cost of Goods Sold
Cr Inventory
```

Catatan:

- Akun debit mengikuti `payment_methods.account_id`.
- Akun revenue mengikuti product/category account mapping.
- Akun inventory dan COGS mengikuti product account mapping.

### 2.7.6 Jurnal Sales Return

Saat retur penjualan diposting:

```text
Dr Sales Return / Sales Revenue
Dr Output Tax Payable jika ada
Cr Cash / Refund Payable / Store Credit
```

Jika stok kembali:

```text
Dr Inventory
Cr Cost of Goods Sold
```

### 2.7.7 Jurnal Supplier Invoice

Saat supplier invoice diposting:

```text
Dr Inventory / Purchase / Expense
Dr Input Tax jika ada
Cr Accounts Payable
```

Catatan:

- Jika memakai GRNI, jurnal Goods Receipt dan Supplier Invoice bisa dipisah.
- Untuk MVP, bisa lebih sederhana: jurnal inventory dibentuk saat supplier invoice posted.

### 2.7.8 Jurnal Supplier Payment

Saat pembayaran supplier diposting:

```text
Dr Accounts Payable
Cr Cash / Bank
```

Akun kredit mengikuti payment method atau akun bank/kas yang dipilih.

### 2.7.9 Jurnal Stock Adjustment

Adjustment minus:

```text
Dr Inventory Loss / Adjustment Expense
Cr Inventory
```

Adjustment plus:

```text
Dr Inventory
Cr Inventory Gain / Adjustment Income
```

### 2.7.10 Jurnal Stock Opname

Jika stok fisik lebih kecil dari sistem:

```text
Dr Inventory Loss
Cr Inventory
```

Jika stok fisik lebih besar dari sistem:

```text
Dr Inventory
Cr Inventory Gain
```

### 2.7.11 Jurnal Loyalty

Saat earn point, terdapat dua pendekatan.

Opsi sederhana:

```text
Tidak membuat jurnal saat earn point
Jurnal dibuat saat point diredeem
```

Saat redeem point sebagai payment:

```text
Dr Loyalty Liability
Cr Sales Settlement / Revenue Adjustment
```

Opsi lebih akurat:

```text
Saat earn:
Dr Loyalty Expense
Cr Loyalty Liability

Saat redeem:
Dr Loyalty Liability
Cr Sales Discount / Payment Settlement
```

Rekomendasi MVP:

```text
Jurnal loyalty dibuat saat redeem dulu.
Earn point cukup dicatat di loyalty ledger.
```

### 2.7.12 Aturan Journal

```text
Journal wajib balance
Journal posted tidak boleh diedit
Journal void harus membuat reversal
Journal harus memiliki reference_type dan reference_id
Journal manual wajib approval jika nominal besar
Journal tidak boleh diposting ke fiscal period yang sudah closed
```

### 2.7.13 Output Accounting Flow

| Output                    | Fungsi                    |
| ------------------------- | ------------------------- |
| Journal Entry             | Bukti akuntansi           |
| Journal Line              | Debit/kredit detail       |
| General Ledger            | Buku besar                |
| Trial Balance             | Neraca saldo              |
| Financial Report Snapshot | Snapshot laporan keuangan |
| Financial Report Line     | Detail laporan            |

---

## 2.8 Closing & Reporting Flow

### 2.8.1 Tujuan

Closing & Reporting Flow mengatur proses penutupan harian/bulanan serta pembuatan laporan operasional dan keuangan.

Reporting bersifat read-only dan tidak boleh mengubah stok, jurnal, atau data operasional.

### 2.8.2 Modul Terlibat

```text
POS
Inventory
Purchasing
Accounting
Reporting
System
```

| Modul      | Peran                                                |
| ---------- | ---------------------------------------------------- |
| POS        | Day closing dan month closing operasional            |
| Inventory  | Stock report dan valuation                           |
| Purchasing | Purchase report dan AP report                        |
| Accounting | Fiscal period, GL, trial balance, financial snapshot |
| Reporting  | Query, generate, export laporan                      |
| System     | Audit, notification, permission                      |

### 2.8.3 Day Closing Flow

Day Closing adalah proses tutup transaksi harian POS.

```text
Pilih tanggal closing
-> validasi semua sales session sudah CLOSED
-> validasi tidak ada transaksi PENDING
-> hitung total sales
-> hitung total cash
-> hitung total non-cash
-> hitung refund / return / void
-> hitung expected cash
-> bandingkan dengan actual cash
-> create day closing record
-> lock transaksi POS pada tanggal tersebut
-> publish DayClosingCompleted event
```

Validasi:

```text
Semua session kasir harus CLOSED
Tidak boleh ada transaction DRAFT/PENDING
Tidak boleh ada payment pending
Tidak boleh ada return belum posted
Tidak boleh ada void pending approval
Tanggal belum pernah closed
```

Output:

```text
day_closings
cash difference
sales summary
payment summary
cashier/session summary
audit log
```

### 2.8.4 Month Closing Flow

Month Closing adalah proses penutupan periode fiskal bulanan dan dimiliki oleh modul **Accounting**.

Owner:

```text
Accounting
```

Month Closing boleh membaca data lintas modul, tetapi tidak boleh bypass service domain masing-masing.

```text
Pilih bulan dan tahun
-> pastikan semua day closing bulan tersebut selesai
-> pastikan semua transaksi POS posted
-> pastikan semua supplier invoice/payment posted
-> pastikan stock adjustment/opname penting posted
-> generate inventory valuation
-> generate trial balance
-> generate financial report snapshot
-> create month closing record
-> close fiscal period
-> lock periode
-> publish MonthClosingCompleted event
```

Validasi:

```text
Semua day closing harus CLOSED
Tidak boleh ada transaksi posted mundur ke periode closed
Tidak boleh ada journal unposted
Tidak boleh ada stock opname penting yang belum posted
Trial balance harus balance
Inventory valuation harus valid
Fiscal period belum closed
```

Output:

```text
month_closings
fiscal_periods closed
trial_balances
financial_report_snapshots
financial_report_lines
inventory valuation snapshot jika digunakan
audit log
```

Aturan konsistensi:

```text
Day Closing owner = POS.
Month Closing owner = Accounting.
Fiscal Period tidak boleh ditutup langsung dari endpoint public.
Fiscal Period hanya boleh CLOSED melalui proses Month Closing yang tervalidasi.
```

### 2.8.5 Reporting Flow

Reporting hanya membaca data.

Alur umum:

```text
User pilih jenis report
-> input filter tanggal/periode
-> validate permission
-> query source data
-> transform data
-> return JSON / export CSV / export PDF
```

Jenis laporan:

```text
Sales Report
Inventory Report
Stock Card
Inventory Movement
Inventory Valuation
Purchase Report
Accounts Payable Report
Supplier Performance Report
Financial Report
Dashboard Report
```

### 2.8.6 Source Data Report

| Report               | Source Data                                                                         |
| -------------------- | ----------------------------------------------------------------------------------- |
| Sales Report         | `sales_transactions`, `sales_transaction_items`, `sales_payments`                   |
| Inventory Report     | `inventory_balances`, `inventory_ledgers`, `inventory_batches`                      |
| Stock Card           | `inventory_ledgers`                                                                 |
| Inventory Movement   | `inventory_ledgers`                                                                 |
| Inventory Valuation  | `inventory_batches`, `inventory_cost_layers`, `inventory_balances`                  |
| Purchase Report      | `purchase_orders`, `goods_receipts`, `supplier_invoices`                            |
| AP Report            | `accounts_payable`, `supplier_payments`                                             |
| Supplier Performance | `supplier_performances`, purchasing history                                         |
| Financial Report     | `journal_entries`, `journal_lines`, `general_ledgers`, `financial_report_snapshots` |
| Dashboard            | Aggregasi sales, inventory, purchase, accounting                                    |

### 2.8.7 Aturan Reporting

```text
Reporting tidak boleh insert transaksi
Reporting tidak boleh update transaksi
Reporting tidak boleh posting journal
Reporting tidak boleh update stock
Reporting boleh membuat snapshot atau export log
Reporting boleh jalan async via Job
```

---

## 2.9 Event, Notification & Queue Flow

### 2.9.1 Tujuan

Event, Notification & Queue Flow mengatur proses non-kritikal yang berjalan setelah transaksi utama berhasil.

Tujuan:

1. Mengurangi beban transaksi utama.
2. Memisahkan proses notifikasi dari posting transaksi.
3. Mendukung realtime dashboard.
4. Mendukung retry untuk proses async.
5. Menjaga transaksi utama tetap cepat dan aman.

### 2.9.2 Prinsip Event

Event dipublish setelah transaksi utama berhasil.

Contoh:

```text
SalesTransactionPosted
GoodsReceiptPosted
SupplierInvoicePosted
SupplierPaymentPosted
StockAdjustmentPosted
JournalEntryPosted
DayClosingCompleted
MonthClosingCompleted
```

Event dapat ditangani oleh Listener untuk:

```text
create notification
create activity log
broadcast realtime
dispatch export/report job
send email/WhatsApp jika nanti didukung
```

### 2.9.3 Queue dan Job

Job digunakan untuk proses yang tidak harus selesai saat transaksi utama berlangsung.

Contoh Job:

```text
GenerateSalesReportJob
GenerateFinancialSnapshotJob
ExportInventoryValuationJob
SendNotificationJob
BroadcastDashboardUpdateJob
RetryPostingEventJob
```

Aturan:

```text
Job tidak boleh merusak konsistensi transaksi utama
Job harus idempotent jika memungkinkan
Job gagal harus bisa retry
Job penting harus menyimpan error log
```

### 2.9.4 Outbox Pattern Optional

Untuk sistem yang lebih aman, dapat digunakan `system_outbox`.

Alur:

```text
Business Transaction Posted
-> create system_outbox record
-> commit transaction
-> worker membaca outbox
-> publish event / dispatch job
-> mark outbox as processed
```

Kelebihan:

```text
event tidak hilang saat transaksi sukses
proses async dapat diulang
lebih aman untuk integrasi eksternal
```

Untuk MVP, outbox pattern dapat disiapkan tetapi tidak harus dipakai pada semua proses.

---

## 2.10 Risiko dan Validasi Penting

### 2.10.1 Risiko POS

| Risiko                  | Mitigasi                                            |
| ----------------------- | --------------------------------------------------- |
| Transaksi double submit | Gunakan idempotency key / unique transaction number |
| Stok minus              | Lock inventory balance dan validasi stok            |
| Payment tidak cocok     | Validasi total payment = grand total                |
| Session tidak valid     | Wajib session OPEN                                  |
| Periode sudah closed    | Block transaksi mundur                              |

### 2.10.2 Risiko Purchasing

| Risiko                 | Mitigasi                              |
| ---------------------- | ------------------------------------- |
| GR melebihi PO         | Validasi qty dan permission override  |
| Invoice tidak cocok GR | Matching invoice dengan goods receipt |
| Payment melebihi AP    | Validasi outstanding payable          |
| Supplier tidak aktif   | Validasi master supplier              |
| PO belum approved      | Block goods receipt                   |

### 2.10.3 Risiko Inventory

| Risiko                                 | Mitigasi                             |
| -------------------------------------- | ------------------------------------ |
| Stok tidak sinkron                     | Ledger sebagai sumber kebenaran      |
| Race condition                         | Gunakan `lockForUpdate()`            |
| Adjustment tanpa alasan                | Reason wajib                         |
| Transfer ke non-stock-bearing location | Validasi `is_stock_bearing`          |
| Opname menghapus history               | Opname harus membuat ledger variance |

### 2.10.4 Risiko Accounting

| Risiko                        | Mitigasi                            |
| ----------------------------- | ----------------------------------- |
| Journal tidak balance         | Validasi total debit = total credit |
| Mapping COA kosong            | Validasi sebelum posting            |
| Journal diedit setelah posted | Block edit posted journal           |
| Posting ke periode closed     | Validasi fiscal period              |
| Void tanpa reversal           | Wajib reversal journal              |

### 2.10.5 Risiko Reporting

| Risiko                       | Mitigasi                                        |
| ---------------------------- | ----------------------------------------------- |
| Report lambat                | Gunakan summary table / snapshot / async export |
| Report beda dengan GL        | Financial report harus berdasarkan journal/GL   |
| Report mengubah data         | Reporting read-only                             |
| Periode sudah closed berubah | Gunakan financial snapshot                      |

---

## 2.11 Kesimpulan BAB 2

Keputusan final BAB 2:

```text
Flow utama:
1. POS Sales Flow
2. Purchasing Flow
3. Inventory Flow
4. Accounting Flow
5. Closing & Reporting Flow
```

Batas tanggung jawab:

| Flow                     | Fokus                                             |
| ------------------------ | ------------------------------------------------- |
| POS Sales Flow           | Operasional kasir dan transaksi penjualan         |
| Purchasing Flow          | Pembelian barang sampai pembayaran supplier       |
| Inventory Flow           | Semua pergerakan stok dan saldo persediaan        |
| Accounting Flow          | Semua transaksi menjadi jurnal dan general ledger |
| Closing & Reporting Flow | Tutup periode, snapshot, dan laporan              |

Prinsip utama:

```text
POS menghasilkan transaksi.
Purchasing menghasilkan pembelian dan hutang.
Inventory mencatat semua pergerakan stok.
Accounting mencatat semua efek keuangan.
Reporting membaca data, tidak mengubah transaksi.
Closing mengunci periode agar data historis stabil.
```

Dengan BAB 1 dan BAB 2, sistem memiliki standar arsitektur dan standar alur bisnis yang jelas untuk refactor project ERP POS secara bertahap.

# BAB 3 — Struktur Database & Domain Mapping

## 3.1 Tujuan BAB 3

BAB 3 menjelaskan standar struktur database dan pemetaan domain agar desain database fisik selaras dengan:

```text
README.md
API_DOCUMENTATION.md
DATABASE.sql
12 Modul Utama
Model Laravel
Business Flow
Repository Pattern
```

Tujuan utama BAB 3:

1. Menentukan ownership tabel berdasarkan 12 modul utama.
2. Menstandarkan mapping antara tabel database dan model Laravel.
3. Menentukan tabel sumber kebenaran untuk stok, accounting, loyalty, dan reporting.
4. Membedakan master table, transaction table, ledger table, snapshot table, dan system table.
5. Menetapkan tabel yang immutable setelah status `POSTED`.
6. Menjelaskan relasi penting antar modul.
7. Menentukan catatan perubahan atau penyesuaian yang perlu dilakukan pada `DATABASE.sql`.

Database tetap menggunakan pendekatan **single database**, tetapi ownership-nya harus modular berdasarkan domain.

Artinya, semua tabel tetap berada dalam satu database MySQL, tetapi secara arsitektur setiap tabel harus dimiliki oleh salah satu dari 12 modul utama.

---

## 3.2 Prinsip Desain Database

### 3.2.1 Single Database, Modular Ownership

Sistem menggunakan satu database utama, tetapi tabel dikelompokkan berdasarkan module owner.

Contoh:

```text
sales_transactions      -> POS
inventory_ledgers       -> Inventory
journal_entries         -> Accounting
purchase_orders         -> Purchasing
loyalty_transactions    -> Loyalty
products                -> Product
```

Tujuan modular ownership:

1. Developer tahu model harus ditempatkan di folder mana.
2. Repository dan service lebih mudah distandarkan.
3. Relasi antar modul lebih mudah dilacak.
4. Dokumentasi API dan database menjadi konsisten.
5. Refactor dari struktur lama ke struktur baru lebih aman.

---

### 3.2.2 Ledger sebagai Sumber Kebenaran

Sistem memiliki beberapa ledger utama.

| Area       | Sumber Kebenaran                    | Saldo Cepat / Summary               |
| ---------- | ----------------------------------- | ----------------------------------- |
| Inventory  | `inventory_ledgers`                 | `inventory_balances`                |
| Accounting | `journal_entries` + `journal_lines` | `general_ledgers`, `trial_balances` |
| Loyalty    | `loyalty_transactions`              | `loyalty_accounts.current_balance`  |
| Reporting  | Ledger dan snapshot                 | Report query / snapshot             |

Prinsip utama:

```text
Ledger = sumber kebenaran
Balance/Summary = hasil agregasi untuk performa
```

Artinya:

- `inventory_balances` tidak boleh dianggap sumber utama stok.
- `loyalty_accounts.current_balance` tidak boleh dianggap sumber utama histori poin.
- `general_ledgers` adalah hasil posting dari journal, bukan pengganti journal.
- Laporan keuangan harus dapat ditelusuri ke journal entry dan journal line.

---

### 3.2.3 Dokumen POSTED Tidak Boleh Diedit Langsung

Setiap dokumen transaksi yang sudah `POSTED` tidak boleh diedit langsung.

Contoh dokumen immutable:

```text
sales_transactions
sales_returns
goods_receipts
supplier_invoices
supplier_payments
purchase_returns
stock_transfers
stock_adjustments
stock_opnames
journal_entries
day_closings
month_closings
```

Koreksi harus dilakukan dengan mekanisme:

```text
VOID
CANCEL
RETURN
REVERSAL JOURNAL
STOCK ADJUSTMENT
STOCK OPNAME
```

Bukan dengan update atau delete data lama.

---

### 3.2.4 Semua Transaksi Harus Memiliki Jejak Audit

Tabel transaksi penting sebaiknya memiliki field:

```text
created_by
updated_by
approved_by
posted_by
voided_by
cancelled_by
created_at
updated_at
approved_at
posted_at
voided_at
cancelled_at
remarks
reason
```

Tidak semua tabel harus memiliki semua field tersebut, tetapi tabel transaksi utama wajib memiliki jejak user dan waktu yang cukup untuk audit.

---

### 3.2.5 Semua Transaksi Harus Memiliki Nomor Dokumen

Transaksi utama harus memiliki nomor dokumen unik.

Contoh:

```text
transaction_no
session_no
return_no
purchase_request_no
purchase_order_no
goods_receipt_no
supplier_invoice_no
payment_no
transfer_no
adjustment_no
opname_no
journal_no
closing_no
```

Penomoran dokumen sebaiknya dikendalikan oleh:

```text
document_types
document_sequences
```

Agar format nomor dokumen konsisten dan tidak hardcode di service/action.

---

## 3.3 Database sebagai Modular Monolith

Walaupun aplikasi menggunakan satu database, struktur ownership harus mengikuti 12 modul utama:

```text
1. Auth
2. System
3. MasterData
4. Product
5. Pricing
6. Promotion
7. POS
8. Inventory
9. Purchasing
10. Loyalty
11. Accounting
12. Reporting
```

Setiap tabel harus memiliki owner utama.

Contoh:

| Tabel                        | Owner Utama | Dipakai Oleh                              |
| ---------------------------- | ----------- | ----------------------------------------- |
| `users`                      | System      | Auth, POS, Purchasing, Accounting         |
| `products`                   | Product     | POS, Inventory, Purchasing, Pricing       |
| `price_lists`                | Pricing     | POS                                       |
| `promotions`                 | Promotion   | POS                                       |
| `sales_transactions`         | POS         | Inventory, Accounting, Loyalty, Reporting |
| `inventory_ledgers`          | Inventory   | POS, Purchasing, Accounting, Reporting    |
| `journal_entries`            | Accounting  | POS, Purchasing, Inventory, Reporting     |
| `loyalty_transactions`       | Loyalty     | POS, Reporting                            |
| `financial_report_snapshots` | Accounting  | Reporting                                 |

Aturan:

1. Satu tabel hanya memiliki satu owner utama.
2. Modul lain boleh membaca tabel tersebut melalui service/repository yang sesuai.
3. Modul lain tidak boleh sembarangan mengubah tabel milik modul lain.
4. Proses lintas modul harus melalui Action atau Service domain.

---

## 3.4 Sumber Kebenaran Data

### 3.4.1 Sumber Kebenaran Stok

Sumber kebenaran stok adalah:

```text
inventory_ledgers
```

Saldo cepat stok ada di:

```text
inventory_balances
```

Aturan:

```text
inventory_ledgers tidak boleh dihapus
inventory_balances boleh dihitung ulang dari inventory_ledgers
setiap pergerakan stok aktif wajib membuat inventory ledger
```

Movement type aktif untuk MVP:

```text
PURCHASE_IN
SALE_OUT
SALES_RETURN_IN
PURCHASE_RETURN_OUT
TRANSFER_OUT
TRANSFER_IN
ADJUSTMENT_IN
ADJUSTMENT_OUT
OPNAME_IN
OPNAME_OUT
```

Catatan future enhancement:

```text
stock_reservations, RESERVATION, dan RELEASE_RESERVATION disiapkan sebagai future enhancement.
Pada MVP, Hold Bill tidak melakukan stock reservation dan tidak membuat inventory ledger.
```

---

### 3.4.2 Sumber Kebenaran Accounting

Sumber kebenaran accounting adalah:

```text
journal_entries
journal_lines
```

Buku besar disimpan pada:

```text
general_ledgers
```

Neraca saldo dapat disimpan pada:

```text
trial_balances
```

Snapshot laporan keuangan disimpan pada:

```text
financial_report_snapshots
financial_report_lines
```

Aturan:

```text
Journal wajib balance.
Journal posted tidak boleh diedit.
Journal void harus membuat reversal.
Journal tidak boleh diposting ke fiscal period yang closed.
Financial report untuk periode closed sebaiknya menggunakan snapshot.
```

---

### 3.4.3 Sumber Kebenaran Loyalty

Sumber kebenaran mutasi poin adalah:

```text
loyalty_transactions
```

Saldo cepat loyalty ada di:

```text
loyalty_accounts.current_balance
```

Aturan:

```text
loyalty_transactions tidak boleh dihapus
current_balance dapat dihitung ulang dari loyalty_transactions
redeem point wajib membuat transaksi poin
adjustment point wajib memiliki reason
```

---

### 3.4.4 Sumber Kebenaran Reporting

Reporting tidak memiliki banyak tabel transaksi sendiri.

Reporting membaca dari:

```text
sales_transactions
sales_transaction_items
sales_payments
inventory_ledgers
inventory_balances
inventory_batches
purchase_orders
goods_receipts
supplier_invoices
accounts_payable
journal_entries
journal_lines
general_ledgers
financial_report_snapshots
financial_report_lines
```

Prinsip:

```text
Reporting bersifat read-only.
Reporting tidak boleh mengubah transaksi.
Reporting tidak boleh posting journal.
Reporting tidak boleh mengubah stok.
```

---

## 3.5 Mapping Tabel ke 12 Modul

## 3.5.1 Auth

### Tabel

```text
user_sessions
password_histories
```

Tabel yang digunakan bersama dengan System:

```text
users
```

### Model Laravel

```text
App\Models\Auth\UserSession
App\Models\Auth\PasswordHistory
```

Untuk `users`, ada dua pendekatan:

| Opsi                     | Keterangan                                      |
| ------------------------ | ----------------------------------------------- |
| `App\Models\Auth\User`   | Cocok jika user lebih dominan untuk autentikasi |
| `App\Models\System\User` | Cocok jika user dikelola dari System Management |

Keputusan standar:

```text
users dimiliki oleh System.
Auth menggunakan users untuk proses login/logout/session.
```

Jadi:

```text
Model utama: App\Models\System\User
Auth service menggunakan User model tersebut.
```

### Tanggung Jawab Auth

```text
Login
Logout
Forgot Password
Reset Password
Change Password
Profile / Me
Session Device
Token Management
```

---

## 3.5.2 System

### Tabel

```text
users
roles
permissions
user_roles
role_permissions
approval_types
approval_levels
approval_requests
approval_histories
audit_logs
activity_logs
document_types
document_sequences
system_settings
business_profiles
system_events
system_commands
system_outbox
notifications
job_queue
```

### Model Laravel

```text
App\Models\System\User
App\Models\System\Role
App\Models\System\Permission
App\Models\System\UserRole
App\Models\System\RolePermission
App\Models\System\ApprovalType
App\Models\System\ApprovalLevel
App\Models\System\ApprovalRequest
App\Models\System\ApprovalHistory
App\Models\System\AuditLog
App\Models\System\ActivityLog
App\Models\System\DocumentType
App\Models\System\DocumentSequence
App\Models\System\SystemSetting
App\Models\System\BusinessProfile
App\Models\System\SystemEvent
App\Models\System\SystemCommand
App\Models\System\SystemOutbox
App\Models\System\Notification
App\Models\System\JobQueue
```

### Catatan

`document_sequences` sangat penting untuk nomor dokumen.

Contoh dokumen yang membutuhkan sequence:

```text
sales_transactions
sales_sessions
sales_returns
purchase_requests
purchase_orders
goods_receipts
supplier_invoices
supplier_payments
stock_transfers
stock_adjustments
stock_opnames
journal_entries
day_closings
month_closings
```

`system_outbox` digunakan untuk outbox pattern.

Contoh:

```text
Transaction berhasil
-> insert system_outbox
-> commit database transaction
-> worker memproses outbox
-> event dikirim / job dijalankan
```

---

## 3.5.3 MasterData

### Tabel

```text
suppliers
customer_categories
customers
units
unit_conversions
taxes
```

### Future Enhancement

```text
currencies
exchange_rates
```

### Model Laravel

```text
App\Models\MasterData\Supplier
App\Models\MasterData\CustomerCategory
App\Models\MasterData\Customer
App\Models\MasterData\Unit
App\Models\MasterData\UnitConversion
App\Models\MasterData\Tax
```

Future enhancement:

```text
App\Models\MasterData\Currency
App\Models\MasterData\ExchangeRate
```

### Keputusan Tax dan Currency

#### Tax

Tax masuk MVP.

Alasan:

1. POS membutuhkan tax calculation.
2. Supplier invoice membutuhkan input tax.
3. Laporan keuangan membutuhkan pemisahan tax.
4. Product atau transaction dapat memiliki konfigurasi pajak.
5. Lebih baik disiapkan sejak awal daripada hardcode tax rate.

Rekomendasi tabel:

```text
taxes
```

Field minimal:

```text
id
tax_code
tax_name
tax_rate
is_inclusive
account_id
is_active
created_by
updated_by
created_at
updated_at
deleted_at
```

Catatan:

```text
account_id mengarah ke chart_of_accounts jika tax ingin diposting ke akun tertentu.
```

#### Currency

Currency tidak wajib masuk MVP.

Keputusan:

```text
Currency menjadi future enhancement.
Base currency awal adalah IDR.
```

Currency baru diperlukan jika sistem mendukung:

```text
multi-currency purchase
foreign supplier
exchange gain/loss
multi-currency bank account
multi-currency financial report
```

---

## 3.5.4 Product

### Tabel

```text
product_brands
product_categories
products
product_attributes
product_attribute_values
product_variants
product_variant_attributes
product_barcodes
product_images
product_tags
product_tag_maps
product_supplier_links
product_cost_profiles
product_account_mappings
```

### Model Laravel

```text
App\Models\Product\ProductBrand
App\Models\Product\ProductCategory
App\Models\Product\Product
App\Models\Product\ProductAttribute
App\Models\Product\ProductAttributeValue
App\Models\Product\ProductVariant
App\Models\Product\ProductVariantAttribute
App\Models\Product\ProductBarcode
App\Models\Product\ProductImage
App\Models\Product\ProductTag
App\Models\Product\ProductTagMap
App\Models\Product\ProductSupplierLink
App\Models\Product\ProductCostProfile
App\Models\Product\ProductAccountMapping
```

### Catatan

Product module hanya mengatur definisi barang.

Product tidak mengatur stok langsung.

Pemisahan tanggung jawab:

| Kebutuhan                 | Modul                            |
| ------------------------- | -------------------------------- |
| Nama produk               | Product                          |
| SKU / Variant             | Product                          |
| Barcode                   | Product                          |
| Harga jual                | Pricing                          |
| Stok                      | Inventory                        |
| HPP / Cost flow           | Inventory + Product Cost Profile |
| Akun inventory/COGS/sales | Product + Accounting             |

---

## 3.5.5 Pricing

### Tabel

```text
price_lists
price_list_items
customer_category_price_lists
price_change_requests
price_change_request_items
price_histories
price_rules
price_rule_items
```

### Model Laravel

```text
App\Models\Pricing\PriceList
App\Models\Pricing\PriceListItem
App\Models\Pricing\CustomerCategoryPriceList
App\Models\Pricing\PriceChangeRequest
App\Models\Pricing\PriceChangeRequestItem
App\Models\Pricing\PriceHistory
App\Models\Pricing\PriceRule
App\Models\Pricing\PriceRuleItem
```

### Catatan

Pricing module bertanggung jawab atas harga jual.

Batas antara Pricing dan Promotion:

| Area                                | Modul     |
| ----------------------------------- | --------- |
| Harga dasar                         | Pricing   |
| Harga berdasarkan customer category | Pricing   |
| Riwayat harga                       | Pricing   |
| Request perubahan harga             | Pricing   |
| Diskon event / promo                | Promotion |
| Buy X Get Y                         | Promotion |
| Minimum purchase discount           | Promotion |
| Promo schedule                      | Promotion |

`price_rules` dapat digunakan untuk aturan harga khusus, tetapi tidak boleh tumpang tindih dengan promotion.

Rekomendasi:

```text
price_lists  = harga resmi
price_rules  = aturan harga khusus
promotions   = diskon/promosi
```

---

## 3.5.6 Promotion

### Tabel

```text
promotions
promotion_conditions
promotion_rewards
promotion_targets
promotion_customer_categories
promotion_applications
promotion_settings
promotion_schedules
promotion_limits
```

### Model Laravel

```text
App\Models\Promotion\Promotion
App\Models\Promotion\PromotionCondition
App\Models\Promotion\PromotionReward
App\Models\Promotion\PromotionTarget
App\Models\Promotion\PromotionCustomerCategory
App\Models\Promotion\PromotionApplication
App\Models\Promotion\PromotionSetting
App\Models\Promotion\PromotionSchedule
App\Models\Promotion\PromotionLimit
```

### Catatan Konsistensi Promotion vs Sales Discount

`promotion_applications` adalah log hasil engine promosi.

Digunakan untuk:

1. Audit rule promo yang match.
2. Tracking promo usage.
3. Validasi limit pemakaian promo.
4. Analisis efektivitas promosi.
5. Laporan efektivitas promo per customer / periode / produk.

`promotion_applications` bukan sumber utama laporan finansial diskon penjualan.

Jika promo menghasilkan diskon nominal pada transaksi, maka sistem wajib membuat baris final di:

```text
sales_discounts
```

Relasi yang disarankan:

```text
promotion_applications.sales_transaction_id
promotion_applications.sales_transaction_item_id nullable
promotion_applications.promotion_id
promotion_applications.sales_discount_id nullable
```

Rule final:

```text
Promotion effectiveness report source = promotion_applications.
Sales discount financial/reporting source = sales_discounts.
Manual discount hanya masuk sales_discounts, tidak masuk promotion_applications.
Promotion tidak boleh langsung membuat jurnal.
Efek promo ke accounting terjadi melalui POS transaction dan journal posting.
```

---

## 3.5.7 POS

### Tabel

```text
shifts
sales_sessions
sales_transactions
sales_transaction_items
sales_payments
sales_payment_allocations
sales_discounts
sales_voids
sales_holds
sales_hold_items
sales_returns
sales_return_items
day_closings
```

### Model Laravel

```text
App\Models\POS\Shift
App\Models\POS\SalesSession
App\Models\POS\SalesTransaction
App\Models\POS\SalesTransactionItem
App\Models\POS\SalesPayment
App\Models\POS\SalesPaymentAllocation
App\Models\POS\SalesDiscount
App\Models\POS\SalesVoid
App\Models\POS\SalesHold
App\Models\POS\SalesHoldItem
App\Models\POS\SalesReturn
App\Models\POS\SalesReturnItem
App\Models\POS\DayClosing
```

### Keputusan Nama Model Payment

Keputusan final:

```text
Model: SalesPayment
Table: sales_payments
```

Bukan:

```text
SalesPayment
```

Alasan:

1. Lebih pendek.
2. Sesuai nama tabel.
3. Tetap jelas berada dalam konteks POS.
4. Konsisten dengan `SalesPaymentAllocation`.

### Catatan Hold Bill

Istilah UI/API boleh menggunakan:

```text
Hold Bill
```

Tetapi model Laravel menggunakan:

```text
SalesHold
SalesHoldItem
```

Tabel:

```text
sales_holds
sales_hold_items
```

### Catatan Konsistensi Sales Discount

`sales_discounts` adalah snapshot diskon final yang memengaruhi transaksi POS.

Digunakan untuk:

```text
laporan sales discount
audit diskon transaksi POS
perhitungan net sales
perhitungan grand total
rekap diskon manual/promo/voucher/member
```

Sumber data laporan diskon penjualan:

```text
sales_discounts
```

Batas dengan promotion:

```text
promotion_applications = log hasil engine promo
sales_discounts = nilai diskon final yang masuk transaksi penjualan
```

Jika promo menghasilkan diskon nominal, maka harus ada baris terkait di `sales_discounts`.

### Catatan Month Closing

`month_closings` tidak dimiliki modul POS.

Owner canonical:

```text
Accounting
```

POS hanya memiliki `day_closings`.

---

## 3.5.8 Inventory

### Tabel

```text
warehouse_locations
inventory_batches
planograms
inventory_balances
inventory_ledgers
inventory_cost_layers
inventory_ledger_snapshots
stock_transfers
stock_transfer_items
stock_adjustments
stock_adjustment_items
stock_opnames
stock_opname_items
stock_reservations
inventory_transaction_types
```

### Model Laravel

```text
App\Models\Inventory\InventoryLocation
App\Models\Inventory\InventoryBatch
App\Models\Inventory\Planogram
App\Models\Inventory\InventoryBalance
App\Models\Inventory\InventoryLedger
App\Models\Inventory\InventoryCostLayer
App\Models\Inventory\InventoryLedgerSnapshot
App\Models\Inventory\StockTransfer
App\Models\Inventory\StockTransferItem
App\Models\Inventory\StockAdjustment
App\Models\Inventory\StockAdjustmentItem
App\Models\Inventory\StockOpname
App\Models\Inventory\StockOpnameItem
App\Models\Inventory\StockReservation
App\Models\Inventory\InventoryTransactionType
```

### Keputusan InventoryLocation

Database menggunakan tabel fisik:

```text
warehouse_locations
```

Tetapi model domain menggunakan:

```text
InventoryLocation
```

Implementasi model:

```php
namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

class InventoryLocation extends Model
{
    protected $table = 'warehouse_locations';
}
```

Alasan:

1. API menggunakan konsep `/inventory/locations`.
2. Domain flow menyebut Inventory Location.
3. Lebih konsisten dengan modul Inventory.
4. `warehouse_locations` tetap boleh dipakai sebagai nama tabel fisik.

---

## 3.5.9 Purchasing

### Tabel

```text
purchase_plans
purchase_plan_items
purchase_requests
purchase_request_items
purchase_orders
purchase_order_items
goods_receipts
goods_receipt_items
supplier_invoices
supplier_invoice_items
accounts_payable
supplier_payments
supplier_payment_allocations
purchase_returns
purchase_return_items
landed_costs
landed_cost_allocations
supplier_performances
supplier_price_lists
```

### Model Laravel

```text
App\Models\Purchasing\PurchasePlan
App\Models\Purchasing\PurchasePlanItem
App\Models\Purchasing\PurchaseRequest
App\Models\Purchasing\PurchaseRequestItem
App\Models\Purchasing\PurchaseOrder
App\Models\Purchasing\PurchaseOrderItem
App\Models\Purchasing\GoodsReceipt
App\Models\Purchasing\GoodsReceiptItem
App\Models\Purchasing\SupplierInvoice
App\Models\Purchasing\SupplierInvoiceItem
App\Models\Purchasing\AccountsPayable
App\Models\Purchasing\SupplierPayment
App\Models\Purchasing\SupplierPaymentAllocation
App\Models\Purchasing\PurchaseReturn
App\Models\Purchasing\PurchaseReturnItem
App\Models\Purchasing\LandedCost
App\Models\Purchasing\LandedCostAllocation
App\Models\Purchasing\SupplierPerformance
App\Models\Purchasing\SupplierPriceList
```

### Catatan Purchase Plan

`purchase_plans` dan `purchase_plan_items` dapat digunakan untuk perencanaan pembelian berdasarkan:

```text
reorder point
sales forecast
minimum stock
seasonal demand
supplier lead time
```

Keputusan:

```text
Purchase Plan boleh menjadi phase lanjutan.
Purchasing MVP tetap dimulai dari Purchase Request dan Purchase Order.
```

---

## 3.5.10 Loyalty

### Tabel

```text
membership_tiers
customer_memberships
loyalty_accounts
loyalty_transactions
loyalty_expirations
loyalty_configurations
reward_catalogs
reward_redemptions
loyalty_vouchers
loyalty_adjustments
```

### Model Laravel

```text
App\Models\Loyalty\MembershipTier
App\Models\Loyalty\CustomerMembership
App\Models\Loyalty\LoyaltyAccount
App\Models\Loyalty\LoyaltyTransaction
App\Models\Loyalty\LoyaltyExpiration
App\Models\Loyalty\LoyaltyConfiguration
App\Models\Loyalty\RewardCatalog
App\Models\Loyalty\RewardRedemption
App\Models\Loyalty\LoyaltyVoucher
App\Models\Loyalty\LoyaltyAdjustment
```

### Catatan

Sumber kebenaran loyalty adalah:

```text
loyalty_transactions
```

Saldo cepat ada di:

```text
loyalty_accounts.current_balance
```

Aturan:

```text
loyalty_accounts.current_balance dapat dihitung ulang dari loyalty_transactions
redeem point wajib membuat loyalty transaction
manual adjustment wajib membuat loyalty adjustment dan loyalty transaction
```

---

## 3.5.11 Accounting

### Tabel

```text
chart_of_accounts
payment_methods
journal_entries
journal_lines
fiscal_periods
accounting_rules
accounting_rule_lines
posting_events
trial_balances
journal_templates
journal_template_lines
general_ledgers
financial_report_snapshots
financial_report_lines
```

### Model Laravel

```text
App\Models\Accounting\ChartOfAccount
App\Models\Accounting\PaymentMethod
App\Models\Accounting\JournalEntry
App\Models\Accounting\JournalLine
App\Models\Accounting\FiscalPeriod
App\Models\Accounting\AccountingRule
App\Models\Accounting\AccountingRuleLine
App\Models\Accounting\PostingEvent
App\Models\Accounting\TrialBalance
App\Models\Accounting\JournalTemplate
App\Models\Accounting\JournalTemplateLine
App\Models\Accounting\GeneralLedger
App\Models\Accounting\FinancialReportSnapshot
App\Models\Accounting\FinancialReportLine
```

### Catatan Payment Method

`payment_methods` dimiliki oleh Accounting.

Alasan:

```text
payment_methods.account_id mengarah ke chart_of_accounts
payment method menentukan akun debit/kredit saat transaksi pembayaran
payment method dipakai POS, supplier payment, refund, dan settlement
```

Tidak perlu membuat tabel `payment_method_account_mappings` selama mapping masih sederhana:

```text
payment_methods.account_id
```

Tabel mapping baru baru dibutuhkan jika payment method memiliki beberapa akun:

```text
cash_account_id
clearing_account_id
fee_account_id
receivable_account_id
settlement_account_id
```

---

## 3.5.12 Reporting

Reporting module bersifat read-only.

### Source Data Reporting

```text
sales_transactions
sales_transaction_items
sales_payments
inventory_ledgers
inventory_balances
inventory_batches
inventory_cost_layers
purchase_orders
goods_receipts
supplier_invoices
accounts_payable
supplier_payments
journal_entries
journal_lines
general_ledgers
trial_balances
financial_report_snapshots
financial_report_lines
```

### Model Khusus Reporting

Reporting tidak wajib memiliki model transaksi sendiri.

Reporting dapat menggunakan:

```text
ReportService
ReportRepository
DTO / Data Object
Export Job
```

Contoh class:

```text
App\Services\Reporting\SalesReportService
App\Services\Reporting\InventoryValuationService
App\Services\Reporting\FinancialReportService

App\Repositories\Contracts\Reporting\SalesReportRepositoryInterface
App\Repositories\Eloquent\Reporting\EloquentSalesReportRepository
```

### Aturan Reporting

```text
Reporting tidak boleh insert transaksi
Reporting tidak boleh update transaksi
Reporting tidak boleh posting journal
Reporting tidak boleh update stock
Reporting boleh membuat export log
Reporting boleh membaca snapshot
Reporting boleh berjalan async melalui Job
```

---

## 3.6 Standar Nama Model dan Table Mapping

Standar umum:

| Table                        | Model                     | Module     |
| ---------------------------- | ------------------------- | ---------- |
| `users`                      | `User`                    | System     |
| `user_sessions`              | `UserSession`             | Auth       |
| `suppliers`                  | `Supplier`                | MasterData |
| `customers`                  | `Customer`                | MasterData |
| `product_brands`             | `ProductBrand`            | Product    |
| `products`                   | `Product`                 | Product    |
| `product_variants`           | `ProductVariant`          | Product    |
| `price_lists`                | `PriceList`               | Pricing    |
| `promotions`                 | `Promotion`               | Promotion  |
| `sales_transactions`         | `SalesTransaction`        | POS        |
| `sales_transaction_items`    | `SalesTransactionItem`    | POS        |
| `sales_payments`             | `SalesPayment`            | POS        |
| `sales_holds`                | `SalesHold`               | POS        |
| `warehouse_locations`        | `InventoryLocation`       | Inventory  |
| `inventory_ledgers`          | `InventoryLedger`         | Inventory  |
| `inventory_balances`         | `InventoryBalance`        | Inventory  |
| `purchase_orders`            | `PurchaseOrder`           | Purchasing |
| `goods_receipts`             | `GoodsReceipt`            | Purchasing |
| `supplier_invoices`          | `SupplierInvoice`         | Purchasing |
| `accounts_payable`           | `AccountsPayable`         | Purchasing |
| `loyalty_transactions`       | `LoyaltyTransaction`      | Loyalty    |
| `chart_of_accounts`          | `ChartOfAccount`          | Accounting |
| `payment_methods`            | `PaymentMethod`           | Accounting |
| `journal_entries`            | `JournalEntry`            | Accounting |
| `journal_lines`              | `JournalLine`             | Accounting |
| `general_ledgers`            | `GeneralLedger`           | Accounting |
| `financial_report_snapshots` | `FinancialReportSnapshot` | Accounting |

Aturan:

1. Model menggunakan PascalCase.
2. Tabel menggunakan snake_case plural.
3. Namespace model mengikuti module owner.
4. Nama model boleh berbeda dari nama tabel jika domain lebih jelas.
5. Perbedaan nama model dan tabel wajib ditulis eksplisit di model menggunakan `protected $table`.

---

## 3.7 Master Table vs Transaction Table vs Ledger Table

### 3.7.1 Master Table

Master table adalah tabel referensi yang digunakan oleh transaksi.

Contoh:

```text
users
roles
permissions
suppliers
customers
customer_categories
units
taxes
product_brands
product_categories
products
product_variants
chart_of_accounts
payment_methods
warehouse_locations
membership_tiers
```

Ciri-ciri:

```text
relatif stabil
dipakai banyak transaksi
biasanya menggunakan is_active
boleh soft delete
tidak langsung berdampak ke stok/jurnal
```

---

### 3.7.2 Transaction Table

Transaction table adalah tabel dokumen bisnis.

Contoh:

```text
sales_transactions
sales_returns
purchase_requests
purchase_orders
goods_receipts
supplier_invoices
supplier_payments
purchase_returns
stock_transfers
stock_adjustments
stock_opnames
journal_entries
day_closings
month_closings
```

Ciri-ciri:

```text
punya document_no
punya status
punya tanggal transaksi
punya created_by / posted_by
dapat berdampak ke ledger
tidak boleh diedit setelah posted
```

---

### 3.7.3 Transaction Detail Table

Detail table menyimpan baris item dari transaction table.

Contoh:

```text
sales_transaction_items
sales_payments
sales_return_items
purchase_order_items
goods_receipt_items
supplier_invoice_items
supplier_payment_allocations
stock_transfer_items
stock_adjustment_items
stock_opname_items
journal_lines
```

Ciri-ciri:

```text
bergantung pada parent transaction
tidak boleh berdiri sendiri
mengandung qty, price, amount, debit, credit, atau detail transaksi
```

---

### 3.7.4 Ledger Table

Ledger table menyimpan histori mutasi.

Contoh:

```text
inventory_ledgers
journal_entries
journal_lines
loyalty_transactions
general_ledgers
```

Ciri-ciri:

```text
menjadi sumber kebenaran
tidak boleh dihapus
tidak boleh diedit sembarangan
koreksi melalui reversal atau adjustment
```

---

### 3.7.5 Balance / Summary Table

Balance table menyimpan saldo cepat.

Contoh:

```text
inventory_balances
loyalty_accounts
trial_balances
```

Ciri-ciri:

```text
hasil agregasi ledger
digunakan untuk performa
harus bisa dihitung ulang dari ledger
bukan sumber kebenaran utama
```

---

### 3.7.6 Snapshot Table

Snapshot table menyimpan hasil laporan pada waktu tertentu.

Contoh:

```text
financial_report_snapshots
financial_report_lines
inventory_ledger_snapshots
```

Ciri-ciri:

```text
digunakan untuk periode closed
tidak boleh berubah sembarangan
menjaga laporan historis tetap stabil
```

---

### 3.7.7 System Table

System table mendukung operasional aplikasi.

Contoh:

```text
audit_logs
activity_logs
notifications
system_settings
system_events
system_commands
system_outbox
job_queue
document_sequences
approval_requests
approval_histories
```

Ciri-ciri:

```text
mendukung keamanan, audit, approval, notification, queue, dan konfigurasi
```

---

## 3.8 Tabel yang Immutable Setelah POSTED

Tabel berikut tidak boleh diedit langsung setelah status `POSTED` atau `CLOSED`.

### POS

```text
sales_transactions
sales_transaction_items
sales_payments
sales_voids
sales_returns
sales_return_items
day_closings
month_closings
```

### Purchasing

```text
goods_receipts
goods_receipt_items
supplier_invoices
supplier_invoice_items
supplier_payments
supplier_payment_allocations
purchase_returns
purchase_return_items
```

### Inventory

```text
inventory_ledgers
stock_transfers
stock_transfer_items
stock_adjustments
stock_adjustment_items
stock_opnames
stock_opname_items
```

### Accounting

```text
journal_entries
journal_lines
general_ledgers
financial_report_snapshots
financial_report_lines
fiscal_periods jika closed
```

### Loyalty

```text
loyalty_transactions
reward_redemptions jika approved/posted
loyalty_adjustments jika posted
```

Aturan:

```text
Tidak boleh hard delete.
Tidak boleh update nominal/qty setelah posted.
Perubahan harus menggunakan reversal, return, void, adjustment, atau transaksi pembalik.
```

---

## 3.9 Relasi Penting Antar Modul

### 3.9.1 POS ke Inventory

```text
sales_transactions
-> sales_transaction_items
-> product_variants
-> inventory_ledgers
-> inventory_balances
```

Makna:

```text
Transaksi POS mengurangi stok melalui inventory ledger.
```

---

### 3.9.2 POS ke Accounting

```text
sales_transactions
-> sales_payments
-> payment_methods
-> journal_entries
-> journal_lines
```

Makna:

```text
Transaksi POS membentuk jurnal penjualan dan jurnal COGS.
```

---

### 3.9.3 POS ke Loyalty

```text
sales_transactions
-> customers
-> loyalty_accounts
-> loyalty_transactions
```

Makna:

```text
Transaksi POS dapat menghasilkan earn point atau redeem point.
```

---

### 3.9.4 Purchasing ke Inventory

```text
purchase_orders
-> goods_receipts
-> goods_receipt_items
-> inventory_batches
-> inventory_ledgers
-> inventory_balances
```

Makna:

```text
Goods Receipt menambah stok.
```

---

### 3.9.5 Purchasing ke Accounting

```text
supplier_invoices
-> accounts_payable
-> journal_entries
-> journal_lines

supplier_payments
-> supplier_payment_allocations
-> accounts_payable
-> journal_entries
-> journal_lines
```

Makna:

```text
Supplier invoice membentuk hutang.
Supplier payment mengurangi hutang.
```

---

### 3.9.6 Product ke Accounting

```text
product_categories / products
-> product_account_mappings
-> chart_of_accounts
```

Makna:

```text
Mapping akun menentukan akun inventory, COGS, dan sales revenue.
```

---

### 3.9.7 Payment Method ke Accounting

```text
payment_methods
-> chart_of_accounts
```

Makna:

```text
Payment method menentukan akun kas/bank/liability yang digunakan saat jurnal.
```

---

### 3.9.8 Reporting ke Semua Modul

```text
Reporting
-> sales_transactions
-> inventory_ledgers
-> purchase_orders
-> accounts_payable
-> journal_entries
-> general_ledgers
-> financial_report_snapshots
```

Makna:

```text
Reporting membaca data lintas modul, tetapi tidak mengubah data.
```

---

## 3.10 Catatan Sinkronisasi dengan README dan API

### 3.10.1 README.md

README harus menjelaskan:

```text
12 modul utama
struktur Modular Layered Monolith
Repository Pattern
Inventory Ledger sebagai sumber kebenaran stok
Journal Entry sebagai sumber kebenaran accounting
Reporting read-only
Tax masuk MVP
Currency future enhancement
```

---

### 3.10.2 API_DOCUMENTATION.md

API documentation harus memiliki mapping endpoint ke module owner.

Contoh:

| Endpoint           | Module Owner |
| ------------------ | ------------ |
| `/auth/*`          | Auth         |
| `/users`           | System       |
| `/roles`           | System       |
| `/suppliers`       | MasterData   |
| `/customers`       | MasterData   |
| `/taxes`           | MasterData   |
| `/products`        | Product      |
| `/product-brands`  | Product      |
| `/price-lists`     | Pricing      |
| `/promotions`      | Promotion    |
| `/pos/*`           | POS          |
| `/inventory/*`     | Inventory    |
| `/purchasing/*`    | Purchasing   |
| `/loyalty/*`       | Loyalty      |
| `/payment-methods` | Accounting   |
| `/accounting/*`    | Accounting   |
| `/reports/*`       | Reporting    |

---

### 3.10.3 DATABASE.sql

`DATABASE.sql` harus memiliki catatan module ownership.

Contoh:

```sql
-- MODULE: POS
-- Tables:
-- shifts
-- sales_sessions
-- sales_transactions
-- sales_transaction_items
-- sales_payments
-- sales_returns
-- day_closings
-- month_closings
```

Dan:

```sql
-- MODULE: Accounting
-- Tables:
-- chart_of_accounts
-- payment_methods
-- journal_entries
-- journal_lines
-- general_ledgers
-- fiscal_periods
-- financial_report_snapshots
```

---

## 3.11 Rekomendasi Perubahan DATABASE.sql

### 3.11.1 Tambahkan Tabel Tax

Karena Tax disepakati masuk MVP, tambahkan tabel:

```text
taxes
```

Rekomendasi struktur:

```sql
CREATE TABLE taxes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tax_code VARCHAR(50) NOT NULL UNIQUE,
    tax_name VARCHAR(150) NOT NULL,
    tax_rate DECIMAL(8,4) NOT NULL DEFAULT 0,
    is_inclusive BOOLEAN NOT NULL DEFAULT FALSE,
    account_id BIGINT UNSIGNED NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_by BIGINT UNSIGNED NULL,
    updated_by BIGINT UNSIGNED NULL,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    CONSTRAINT fk_taxes_account
        FOREIGN KEY (account_id)
        REFERENCES chart_of_accounts(id)
        ON DELETE SET NULL
);
```

Catatan:

```text
account_id optional.
Digunakan jika tax ingin langsung dipetakan ke COA.
```

---

### 3.11.2 Currency Masuk Future Enhancement

Tidak wajib membuat tabel currency pada MVP.

Namun jika ingin disiapkan sejak awal, gunakan:

```text
currencies
exchange_rates
```

Rekomendasi:

```text
Jangan aktifkan multi-currency di MVP.
Gunakan IDR sebagai base currency.
Tambahkan currency nanti saat ada kebutuhan multi-currency.
```

---

### 3.11.3 Konsistenkan Nama Payment POS

Gunakan standar:

```text
Model: SalesPayment
Table: sales_payments
```

Dan:

```text
Model: SalesPaymentAllocation
Table: sales_payment_allocations
```

Dokumentasi BAB 1 perlu disesuaikan jika sebelumnya masih menulis `SalesPayment`.

---

### 3.11.4 Pastikan Payment Methods Memiliki Account ID

`payment_methods` harus memiliki relasi ke COA.

Minimal field:

```text
account_id
```

Tidak perlu tabel baru untuk `payment_method_account_mappings` selama satu payment method hanya memiliki satu akun utama.

---

### 3.11.5 Tambahkan Index untuk Tabel Besar

Tabel transaksi dan ledger perlu index yang baik.

Contoh index penting:

```text
sales_transactions.transaction_date
sales_transactions.sales_session_id
sales_transactions.customer_id
sales_transaction_items.product_variant_id

inventory_ledgers.product_variant_id
inventory_ledgers.location_id
inventory_ledgers.transaction_date
inventory_ledgers.reference_type
inventory_ledgers.reference_id

journal_entries.entry_date
journal_entries.reference_type
journal_entries.reference_id
journal_entries.status
journal_lines.account_id

goods_receipts.purchase_order_id
supplier_invoices.supplier_id
accounts_payable.supplier_id
accounts_payable.status
```

Tujuan:

```text
mempercepat report
mempercepat stock card
mempercepat general ledger
mempercepat pencarian transaksi
```

---

### 3.11.6 Gunakan Soft Delete untuk Master Data

Master data sebaiknya menggunakan soft delete.

Contoh:

```text
suppliers
customers
product_brands
product_categories
products
product_variants
units
payment_methods
taxes
```

Transaksi posted tidak boleh soft delete sembarangan.

---

### 3.11.7 Tambahkan Field Audit pada Transaksi Penting

Tabel transaksi utama sebaiknya memiliki:

```text
created_by
updated_by
approved_by
posted_by
cancelled_by
voided_by
created_at
updated_at
approved_at
posted_at
cancelled_at
voided_at
```

Tidak semua wajib, tetapi minimal:

```text
created_by
posted_by
posted_at
status
```

untuk dokumen yang memiliki proses posting.

---

## 3.12 Kesimpulan BAB 3

Keputusan final BAB 3:

```text
Database tetap single database.
Ownership tabel mengikuti 12 modul utama.
Inventory Ledger adalah sumber kebenaran stok.
Journal Entry dan Journal Line adalah sumber kebenaran accounting.
Loyalty Transaction adalah sumber kebenaran poin.
Reporting bersifat read-only.
Tax masuk MVP.
Currency menjadi future enhancement.
Model payment POS menggunakan SalesPayment.
InventoryLocation tetap menggunakan table warehouse_locations.
PaymentMethod dimiliki Accounting.
```

Standar penting:

```text
Master table boleh soft delete.
Transaction table tidak boleh diubah setelah posted.
Ledger table tidak boleh dihapus.
Balance table harus bisa dihitung ulang dari ledger.
Snapshot table digunakan untuk periode closed.
Reporting tidak boleh mengubah transaksi.
```

Dengan BAB 3 ini, struktur database, model Laravel, API endpoint, dan business flow sudah memiliki mapping domain yang jelas dan konsisten.

# BAB 4 — API Contract, Endpoint Standard & Response Format

## 4.1 Tujuan BAB 4

BAB 4 menetapkan standar kontrak API agar seluruh endpoint backend konsisten, mudah digunakan frontend, mudah diuji, mudah dikembangkan, dan mudah didokumentasikan.

BAB ini menyelaraskan API dengan:

```text
12 Modul Utama
Business Flow
Database Domain Mapping
Repository Pattern
Form Request
Resource Response
Sanctum Authentication
RBAC Permission
```

Tujuan utama BAB 4:

1. Menentukan standar URL endpoint.
2. Menentukan format request.
3. Menentukan format response sukses.
4. Menentukan format response error.
5. Menentukan standar pagination, filter, search, dan sort.
6. Menentukan standar endpoint transaksi.
7. Menentukan standar endpoint approval.
8. Menentukan standar endpoint reporting.
9. Menentukan standar authorization dan permission.
10. Menyelaraskan API dengan 12 modul utama.

---

## 4.2 Prinsip Desain API

API harus mengikuti prinsip berikut:

```text
Consistent
Predictable
Versioned
Secure
Validated
Auditable
Frontend-friendly
Domain-oriented
```

| Prinsip           | Keterangan                                                |
| ----------------- | --------------------------------------------------------- |
| Consistent        | Semua endpoint memakai pola response dan error yang sama  |
| Predictable       | Nama endpoint mudah ditebak berdasarkan modul             |
| Versioned         | API memakai versi seperti `/api/v1`                       |
| Secure            | Semua endpoint protected kecuali auth public              |
| Validated         | Semua input divalidasi Form Request                       |
| Auditable         | Semua perubahan penting memiliki audit trail              |
| Frontend-friendly | Response mudah dipakai Vue/Inertia                        |
| Domain-oriented   | Endpoint mengikuti domain, bukan struktur database mentah |

Aturan utama:

```text
API tidak boleh mencerminkan struktur tabel mentah secara membabi buta.
API harus mengikuti domain bisnis.
API harus selaras dengan module owner.
API harus menjaga konsistensi response.
```

---

## 4.3 API Versioning

Semua endpoint menggunakan versi:

```text
/api/v1
```

Contoh:

```text
GET    /api/v1/products
POST   /api/v1/products
GET    /api/v1/pos/transactions
POST   /api/v1/pos/transactions
GET    /api/v1/reports/sales
```

Aturan versioning:

```text
v1 digunakan untuk versi stabil pertama.
Breaking change harus naik versi menjadi v2.
Perubahan minor yang backward-compatible tetap di v1.
```

Contoh breaking change:

```text
menghapus field response
mengubah tipe data field
mengubah struktur response utama
mengubah nama endpoint
mengubah behavior endpoint secara besar
```

Contoh non-breaking change:

```text
menambahkan field baru
menambahkan endpoint baru
menambahkan filter optional
menambahkan metadata optional
```

---

## 4.4 Authentication & Authorization Standard

### 4.4.1 Public Endpoint

Endpoint public hanya boleh untuk kebutuhan auth awal.

```text
POST /auth/login
POST /auth/forgot-password
POST /auth/reset-password
```

Endpoint ini tidak menggunakan middleware `auth:sanctum`.

---

### 4.4.2 Protected Endpoint

Endpoint selain public auth wajib menggunakan:

```text
auth:sanctum
```

Contoh endpoint protected:

```text
GET  /auth/me
POST /auth/logout
GET  /products
POST /pos/transactions
GET  /reports/sales
```

---

### 4.4.3 Permission Middleware

Endpoint penting harus menggunakan permission.

Contoh route:

```php
Route::middleware(['auth:sanctum', 'permission:pos.transaction.create'])
    ->post('/pos/transactions', [SalesTransactionController::class, 'store']);
```

Format permission:

```text
{module}.{resource}.{action}
```

Contoh permission:

```text
pos.transaction.create
pos.transaction.void
inventory.stock-adjustment.post
purchasing.purchase-order.approve
accounting.journal-entry.post
reporting.sales.view
system.user.manage
```

Aturan:

```text
Authentication menjawab: siapa user ini?
Authorization menjawab: user ini boleh melakukan apa?
Permission wajib dicek untuk action penting.
```

---

## 4.5 Module Endpoint Ownership

Endpoint harus mengikuti ownership 12 modul.

| Module     | Endpoint Prefix                                                                    |
| ---------- | ---------------------------------------------------------------------------------- |
| Auth       | `/auth/*`                                                                          |
| System     | `/users`, `/roles`, `/permissions`, `/approvals`, `/settings`                      |
| MasterData | `/suppliers`, `/customers`, `/customer-categories`, `/units`, `/taxes`             |
| Product    | `/products`, `/product-brands`, `/product-categories`                              |
| Pricing    | `/price-lists`, `/price-change-requests`, `/price-histories`                       |
| Promotion  | `/promotions`, `/promotions/simulate`                                              |
| POS        | `/pos/shifts`, `/pos/sessions`, `/pos/transactions`, `/pos/returns`                |
| Inventory  | `/inventory/locations`, `/inventory/ledgers`, `/inventory/transfers`               |
| Purchasing | `/purchasing/purchase-orders`, `/purchasing/goods-receipts`                        |
| Loyalty    | `/loyalty/accounts`, `/loyalty/transactions`, `/loyalty/rewards`                   |
| Accounting | `/accounting/journal-entries`, `/accounting/chart-of-accounts`, `/payment-methods` |
| Reporting  | `/reports/sales`, `/reports/inventory`, `/reports/financial`                       |

Catatan khusus:

```text
/payment-methods dimiliki oleh Accounting walaupun secara tampilan bisa terasa seperti master data.
```

Alasannya:

```text
payment_methods.account_id mengarah ke chart_of_accounts
payment method menentukan akun saat posting jurnal
```

---

## 4.6 HTTP Method Standard

Gunakan standar HTTP method berikut:

| Method | Fungsi                                           |
| ------ | ------------------------------------------------ |
| GET    | Ambil data                                       |
| POST   | Buat data baru atau menjalankan action           |
| PUT    | Update penuh                                     |
| PATCH  | Update sebagian / perubahan status               |
| DELETE | Soft delete atau hapus data yang belum digunakan |

Contoh CRUD:

```text
GET    /products
POST   /products
GET    /products/{id}
PUT    /products/{id}
DELETE /products/{id}
```

Untuk action transaksi:

```text
POST /pos/transactions/{id}/void
POST /purchasing/purchase-orders/{id}/approve
POST /inventory/stock-adjustments/{id}/post
POST /accounting/journal-entries/{id}/post
```

Action seperti `post`, `void`, `approve`, `reject`, `close`, dan `reverse` menggunakan `POST` karena mengubah state dan memiliki efek bisnis.

---

## 4.7 Naming Convention Endpoint

### 4.7.1 Gunakan kebab-case

Benar:

```text
/product-brands
/price-lists
/sales-transactions
/stock-adjustments
/journal-entries
```

Salah:

```text
/product_brands
/productBrands
/ProductBrands
```

---

### 4.7.2 Gunakan Plural Noun untuk Resource

Benar:

```text
/products
/customers
/suppliers
/journal-entries
```

Salah:

```text
/product
/customer
/supplier
/journal-entry
```

---

### 4.7.3 Gunakan Prefix Module untuk Domain Besar

Contoh:

```text
/pos/transactions
/inventory/ledgers
/purchasing/purchase-orders
/accounting/journal-entries
/reports/sales
```

Master sederhana boleh langsung tanpa prefix modul:

```text
/products
/customers
/suppliers
/units
/taxes
```

Namun ownership tetap mengikuti modul.

---

## 4.8 Request Format Standard

Request menggunakan JSON.

Header standar:

```text
Accept: application/json
Content-Type: application/json
Authorization: Bearer {token}
```

Contoh request JSON:

```json
{
    "customer_id": 1,
    "items": [
        {
            "product_variant_id": 10,
            "qty": 2,
            "unit_price": 25000
        }
    ],
    "payments": [
        {
            "payment_method_id": 1,
            "amount": 50000
        }
    ]
}
```

Untuk upload file gunakan:

```text
Content-Type: multipart/form-data
```

Contoh form-data:

```text
image: file
name: Product Image
```

---

## 4.9 Response Format Standard

### 4.9.1 Response Sukses Single Object

```json
{
    "success": true,
    "message": "Data retrieved successfully",
    "data": {
        "id": 1,
        "name": "Example"
    }
}
```

---

### 4.9.2 Response Sukses Collection

```json
{
    "success": true,
    "message": "Data retrieved successfully",
    "data": [
        {
            "id": 1,
            "name": "Example"
        }
    ]
}
```

---

### 4.9.3 Response Sukses dengan Pagination

```json
{
    "success": true,
    "message": "Data retrieved successfully",
    "data": [
        {
            "id": 1,
            "name": "Example"
        }
    ],
    "meta": {
        "current_page": 1,
        "per_page": 15,
        "total": 100,
        "last_page": 7
    },
    "links": {
        "first": "https://example.com/api/v1/products?page=1",
        "last": "https://example.com/api/v1/products?page=7",
        "prev": null,
        "next": "https://example.com/api/v1/products?page=2"
    }
}
```

---

### 4.9.4 Response Setelah Create

HTTP status:

```text
201 Created
```

Format:

```json
{
    "success": true,
    "message": "Data created successfully",
    "data": {
        "id": 1
    }
}
```

---

### 4.9.5 Response Setelah Update

```json
{
    "success": true,
    "message": "Data updated successfully",
    "data": {
        "id": 1
    }
}
```

---

### 4.9.6 Response Setelah Delete

```json
{
    "success": true,
    "message": "Data deleted successfully",
    "data": null
}
```

---

### 4.9.7 Response Action Berhasil

Contoh untuk posting transaksi:

```json
{
    "success": true,
    "message": "Transaction posted successfully",
    "data": {
        "id": 1001,
        "document_no": "POS-20260621-0001",
        "status": "POSTED",
        "posted_at": "2026-06-21 10:30:00"
    }
}
```

---

## 4.10 Error Response Standard

### 4.10.1 Validation Error

HTTP status:

```text
422 Unprocessable Entity
```

Format:

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password field is required."]
    }
}
```

---

### 4.10.2 Unauthorized

HTTP status:

```text
401 Unauthorized
```

Format:

```json
{
    "success": false,
    "message": "Unauthenticated",
    "errors": null
}
```

---

### 4.10.3 Forbidden

HTTP status:

```text
403 Forbidden
```

Format:

```json
{
    "success": false,
    "message": "You do not have permission to perform this action",
    "errors": null
}
```

---

### 4.10.4 Not Found

HTTP status:

```text
404 Not Found
```

Format:

```json
{
    "success": false,
    "message": "Resource not found",
    "errors": null
}
```

---

### 4.10.5 Business Rule Error

HTTP status:

```text
409 Conflict
```

Contoh:

```json
{
    "success": false,
    "message": "Stock is not enough for this transaction",
    "errors": {
        "product_variant_id": 10,
        "available_qty": 3,
        "requested_qty": 5
    }
}
```

Business rule error digunakan untuk kasus:

```text
stok tidak cukup
periode sudah closing
journal tidak balance
payment tidak sesuai total
PO belum approved
invoice sudah dibayar
session kasir belum open
dokumen sudah posted
```

---

### 4.10.6 Server Error

HTTP status:

```text
500 Internal Server Error
```

Format production:

```json
{
    "success": false,
    "message": "Internal server error",
    "errors": null
}
```

Detail error teknis tidak boleh ditampilkan di production.

---

## 4.11 Pagination, Filter, Search, Sort Standard

### 4.11.1 Pagination

Parameter standar:

```text
page
per_page
```

Contoh:

```text
GET /products?page=1&per_page=15
```

Default:

```text
page = 1
per_page = 15
max_per_page = 100
```

---

### 4.11.2 Search

Gunakan parameter:

```text
search
```

Contoh:

```text
GET /products?search=kopi
GET /customers?search=budi
```

---

### 4.11.3 Filter

Gunakan nama field langsung.

Contoh:

```text
GET /products?category_id=1&is_active=true
GET /pos/transactions?status=POSTED
GET /inventory/ledgers?product_variant_id=10&location_id=2
```

Untuk date range:

```text
date_from
date_to
```

Contoh:

```text
GET /reports/sales?date_from=2026-01-01&date_to=2026-01-31
```

---

### 4.11.4 Sort

Gunakan parameter:

```text
sort
direction
```

Contoh:

```text
GET /products?sort=name&direction=asc
GET /pos/transactions?sort=transaction_date&direction=desc
```

Nilai direction:

```text
asc
desc
```

---

## 4.12 Status Code Standard

| Status Code | Keterangan                      |
| ----------: | ------------------------------- |
|         200 | Request sukses                  |
|         201 | Data berhasil dibuat            |
|         204 | Sukses tanpa response body      |
|         400 | Request tidak valid secara umum |
|         401 | Belum login / token invalid     |
|         403 | Tidak punya permission          |
|         404 | Data tidak ditemukan            |
|         409 | Konflik business rule           |
|         422 | Validasi gagal                  |
|         429 | Terlalu banyak request          |
|         500 | Error server                    |

---

## 4.13 Endpoint Pattern per Modul

### 4.13.1 Auth

```text
POST   /auth/login
POST   /auth/logout
GET    /auth/me
PUT    /auth/password
POST   /auth/forgot-password
POST   /auth/reset-password
GET    /auth/sessions
DELETE /auth/sessions/{id}
```

---

### 4.13.2 System

```text
GET    /users
POST   /users
GET    /users/{id}
PUT    /users/{id}
DELETE /users/{id}

GET    /roles
POST   /roles
GET    /permissions

GET    /approvals
POST   /approvals/{id}/approve
POST   /approvals/{id}/reject

GET    /audit-logs
GET    /activity-logs
GET    /notifications
PUT    /notifications/{id}/read
GET    /settings
PUT    /settings/{key}
```

---

### 4.13.3 MasterData

```text
GET    /suppliers
POST   /suppliers
GET    /suppliers/{id}
PUT    /suppliers/{id}
DELETE /suppliers/{id}

GET    /customers
POST   /customers
GET    /customers/{id}
PUT    /customers/{id}
DELETE /customers/{id}

GET    /customer-categories
POST   /customer-categories

GET    /units
POST   /units

GET    /unit-conversions
POST   /unit-conversions

GET    /taxes
POST   /taxes
GET    /taxes/{id}
PUT    /taxes/{id}
DELETE /taxes/{id}
```

---

### 4.13.4 Product

```text
GET    /products
POST   /products
GET    /products/{id}
PUT    /products/{id}
DELETE /products/{id}

GET    /product-brands
POST   /product-brands
GET    /product-brands/{id}
PUT    /product-brands/{id}
DELETE /product-brands/{id}

GET    /product-categories
POST   /product-categories
GET    /product-categories/{id}
PUT    /product-categories/{id}
DELETE /product-categories/{id}

GET    /products/barcode/{barcode}
POST   /products/{id}/images
POST   /products/{id}/account-mapping
GET    /products/variants/{id}/cost-profile
PUT    /products/variants/{id}/cost-profile
```

---

### 4.13.5 Pricing

```text
GET    /price-lists
POST   /price-lists
GET    /price-lists/{id}
PUT    /price-lists/{id}
DELETE /price-lists/{id}

GET    /price-lists/{id}/items
POST   /price-lists/{id}/items
PUT    /price-list-items/{id}
DELETE /price-list-items/{id}

GET    /price-change-requests
POST   /price-change-requests
POST   /price-change-requests/{id}/approve
POST   /price-change-requests/{id}/reject

GET    /products/variants/{id}/price-history
```

---

### 4.13.6 Promotion

```text
GET    /promotions
POST   /promotions
GET    /promotions/{id}
PUT    /promotions/{id}
DELETE /promotions/{id}

POST   /promotions/{id}/activate
POST   /promotions/{id}/deactivate
POST   /promotions/simulate

GET    /promotions/settings
PUT    /promotions/settings
```

---

### 4.13.7 POS

```text
GET    /pos/shifts
POST   /pos/shifts/open
POST   /pos/shifts/{id}/close

GET    /pos/sessions
POST   /pos/sessions/open
POST   /pos/sessions/{id}/close

GET    /pos/transactions
POST   /pos/transactions
GET    /pos/transactions/{id}
POST   /pos/transactions/{id}/void

POST   /pos/holds
GET    /pos/holds
POST   /pos/holds/{id}/resume
DELETE /pos/holds/{id}

POST   /pos/returns
GET    /pos/returns
GET    /pos/returns/{id}
POST   /pos/returns/{id}/post

POST   /pos/day-closings
GET    /pos/day-closings
POST   /pos/month-closings
GET    /pos/month-closings
```

---

### 4.13.8 Inventory

```text
GET    /inventory/locations
POST   /inventory/locations
GET    /inventory/locations/{id}
PUT    /inventory/locations/{id}
DELETE /inventory/locations/{id}

GET    /inventory/balances
GET    /inventory/ledgers
GET    /inventory/stock-card

# Stock Card canonical endpoint.
# Tidak ada endpoint /reports/stock-card.

POST   /inventory/transfers
GET    /inventory/transfers
GET    /inventory/transfers/{id}
POST   /inventory/transfers/{id}/post
POST   /inventory/transfers/{id}/cancel

POST   /inventory/adjustments
GET    /inventory/adjustments
GET    /inventory/adjustments/{id}
POST   /inventory/adjustments/{id}/post

POST   /inventory/opnames
GET    /inventory/opnames
GET    /inventory/opnames/{id}
POST   /inventory/opnames/{id}/post

GET    /inventory/planograms
POST   /inventory/planograms
```

---

### 4.13.9 Purchasing

```text
GET    /purchasing/purchase-requests
POST   /purchasing/purchase-requests
GET    /purchasing/purchase-requests/{id}
PUT    /purchasing/purchase-requests/{id}
POST   /purchasing/purchase-requests/{id}/approve
POST   /purchasing/purchase-requests/{id}/reject

GET    /purchasing/purchase-orders
POST   /purchasing/purchase-orders
GET    /purchasing/purchase-orders/{id}
PUT    /purchasing/purchase-orders/{id}
POST   /purchasing/purchase-orders/{id}/approve
POST   /purchasing/purchase-orders/{id}/cancel

GET    /purchasing/goods-receipts
POST   /purchasing/goods-receipts
GET    /purchasing/goods-receipts/{id}
POST   /purchasing/goods-receipts/{id}/post

GET    /purchasing/supplier-invoices
POST   /purchasing/supplier-invoices
GET    /purchasing/supplier-invoices/{id}
POST   /purchasing/supplier-invoices/{id}/post

GET    /purchasing/accounts-payable
GET    /purchasing/supplier-payments
POST   /purchasing/supplier-payments
POST   /purchasing/supplier-payments/{id}/post
```

---

### 4.13.10 Loyalty

```text
GET    /loyalty/accounts
GET    /loyalty/accounts/{id}
GET    /loyalty/transactions
POST   /loyalty/adjustments

GET    /loyalty/membership-tiers
POST   /loyalty/membership-tiers

GET    /loyalty/rewards
POST   /loyalty/rewards
POST   /loyalty/rewards/{id}/redeem

GET    /loyalty/configurations
PUT    /loyalty/configurations
```

---

### 4.13.11 Accounting

```text
GET    /accounting/chart-of-accounts
POST   /accounting/chart-of-accounts
GET    /accounting/chart-of-accounts/{id}
PUT    /accounting/chart-of-accounts/{id}
DELETE /accounting/chart-of-accounts/{id}

GET    /payment-methods
POST   /payment-methods
GET    /payment-methods/{id}
PUT    /payment-methods/{id}
DELETE /payment-methods/{id}

GET    /accounting/journal-entries
POST   /accounting/journal-entries
GET    /accounting/journal-entries/{id}
POST   /accounting/journal-entries/{id}/post
POST   /accounting/journal-entries/{id}/reverse

GET    /accounting/general-ledgers
GET    /accounting/trial-balances
GET    /accounting/fiscal-periods

GET    /accounting/month-closings
POST   /accounting/month-closings
GET    /accounting/month-closings/{id}

GET    /accounting/journal-templates
POST   /accounting/journal-templates

GET    /accounting/accounting-rules
POST   /accounting/accounting-rules
```

---

### 4.13.12 Reporting

```text
GET /reports/sales
GET /reports/inventory
GET /reports/inventory-movement
GET /reports/inventory-valuation
GET /reports/purchases
GET /reports/accounts-payable
GET /reports/supplier-performance
GET /reports/financial
GET /reports/dashboard
```

---

## 4.14 Transaction Posting Endpoint Standard

Endpoint posting transaksi menggunakan pattern:

```text
POST /{module}/{resource}/{id}/post
```

Contoh:

```text
POST /inventory/stock-adjustments/{id}/post
POST /purchasing/goods-receipts/{id}/post
POST /purchasing/supplier-invoices/{id}/post
POST /purchasing/supplier-payments/{id}/post
POST /accounting/journal-entries/{id}/post
```

Aturan posting:

```text
hanya dokumen valid yang boleh diposting
dokumen POSTED tidak boleh diposting ulang
posting harus menggunakan database transaction
posting harus mencatat posted_by dan posted_at
posting harus memicu audit/event
posting harus gagal total jika ada step kritikal gagal
```

Business rule error saat dokumen sudah posted:

```json
{
    "success": false,
    "message": "Document has already been posted",
    "errors": {
        "status": "POSTED"
    }
}
```

---

## 4.15 Approval Endpoint Standard

Approval memiliki dua jenis endpoint:

```text
approval inbox endpoint
per-resource approval write action endpoint
```

### Approval Inbox Endpoint

Endpoint generic `/approvals` hanya digunakan untuk membaca daftar approval yang perlu diproses user.

```text
GET /approvals
GET /approvals/{id}
```

Endpoint ini tidak boleh menjadi write action untuk mengubah status dokumen.

### Per-Resource Approval Write Action

Approve dan reject dokumen harus dilakukan melalui endpoint resource masing-masing.

Pattern:

```text
POST /{module}/{resource}/{id}/approve
POST /{module}/{resource}/{id}/reject
```

Contoh:

```text
POST /purchasing/purchase-orders/{id}/approve
POST /purchasing/purchase-orders/{id}/reject
POST /price-change-requests/{id}/approve
POST /price-change-requests/{id}/reject
POST /inventory/stock-adjustments/{id}/approve
POST /inventory/stock-adjustments/{id}/reject
```

Request reject:

```json
{
    "reason": "Harga tidak sesuai dengan ketentuan"
}
```

Aturan approval:

```text
approval wajib cek permission
approval wajib cek status dokumen
approval wajib mencatat approved_by / rejected_by
approval wajib mencatat approval history
approval tidak boleh dilakukan oleh user yang tidak berwenang
approve/reject harus update document status, approval_requests, approval_histories, dan audit_logs dalam satu database transaction
```

Final rule:

```text
Approval inbox = /approvals.
Approval write action = endpoint resource masing-masing.
Tidak boleh ada dua write endpoint untuk approval dokumen yang sama.
```

---

## 4.16 Reporting Endpoint Standard

Reporting endpoint hanya menggunakan `GET`.

Contoh:

```text
GET /reports/sales?date_from=2026-01-01&date_to=2026-01-31
GET /reports/inventory-valuation?as_of_date=2026-01-31
GET /reports/financial?period=2026-01
```

Aturan reporting:

```text
Reporting endpoint tidak boleh POST transaksi.
Reporting endpoint tidak boleh mengubah stok.
Reporting endpoint tidak boleh membuat journal.
Reporting endpoint boleh membuat export job.
Reporting endpoint boleh membaca snapshot.
```

Untuk export report:

```text
POST /reports/sales/export
POST /reports/inventory/export
POST /reports/financial/export
```

Catatan:

```text
Export boleh memakai POST karena membuat job/export file, bukan mengubah transaksi bisnis.
```

---

## 4.17 Upload File Endpoint Standard

Upload file menggunakan `multipart/form-data`.

Contoh:

```text
POST /products/{id}/images
POST /product-brands/{id}/logo
```

Header:

```text
Accept: application/json
Content-Type: multipart/form-data
Authorization: Bearer {token}
```

Response:

```json
{
    "success": true,
    "message": "File uploaded successfully",
    "data": {
        "file_url": "https://example.com/storage/products/image.png"
    }
}
```

Aturan upload:

```text
validasi mime type
validasi ukuran file
gunakan storage Laravel
simpan metadata file jika diperlukan
jangan expose path internal server
```

---

## 4.18 Realtime Event & Webhook Standard

Realtime event digunakan untuk update dashboard, notifikasi, dan monitoring operasional.

Contoh event realtime:

```text
SalesTransactionPosted
StockAdjustmentPosted
GoodsReceiptPosted
SupplierInvoicePosted
JournalEntryPosted
DayClosingCompleted
MonthClosingCompleted
```

Channel contoh:

```text
private-user.{user_id}
private-role.{role_id}
private-store.{store_id}
dashboard
pos
inventory
accounting
```

Payload standar:

```json
{
    "event": "SalesTransactionPosted",
    "message": "Sales transaction has been posted",
    "data": {
        "transaction_id": 1001,
        "transaction_no": "POS-20260621-0001",
        "status": "POSTED"
    },
    "timestamp": "2026-06-21 10:30:00"
}
```

Aturan:

```text
jangan broadcast data sensitif
gunakan private channel untuk data user-specific
gunakan queue untuk broadcast non-kritikal
event realtime tidak boleh menjadi sumber kebenaran data
```

Webhook optional untuk integrasi eksternal.

Pattern:

```text
POST /webhooks/{provider}
```

Webhook harus memiliki:

```text
signature validation
timestamp validation
idempotency key
request log
retry handling
```

---

## 4.19 API Security Rules

Aturan keamanan API:

```text
semua endpoint protected wajib auth:sanctum
permission dicek untuk action penting
validasi input wajib melalui Form Request
gunakan rate limit untuk auth endpoint
jangan expose stack trace di production
jangan expose field sensitif seperti password/token
gunakan HTTPS di production
gunakan audit log untuk perubahan penting
gunakan idempotency key untuk transaksi kritikal jika dibutuhkan
```

Field sensitif yang tidak boleh muncul di response:

```text
password
remember_token
token plain text setelah login selesai
secret_key
api_key
internal_error_trace
```

Rate limit disarankan:

| Endpoint                | Rate Limit      |
| ----------------------- | --------------- |
| `/auth/login`           | Ketat           |
| `/auth/forgot-password` | Ketat           |
| `/pos/transactions`     | Sedang          |
| `/reports/export`       | Sedang / rendah |
| Endpoint read biasa     | Normal          |

---

## 4.20 Kesimpulan BAB 4

Keputusan final BAB 4:

```text
API prefix menggunakan /api/v1.
Endpoint menggunakan kebab-case.
Resource menggunakan plural noun.
Action transaksi menggunakan POST.
Response selalu memakai success, message, data.
Error response distandarkan.
Pagination memakai page dan per_page.
Filter memakai nama field langsung.
Business rule error memakai HTTP 409.
Validation error memakai HTTP 422.
Reporting endpoint bersifat read-only.
Payment methods dimiliki Accounting.
Upload file memakai multipart/form-data.
Realtime event digunakan untuk notifikasi dan dashboard, bukan sumber kebenaran data.
```

Dengan BAB 4 ini, standar endpoint API sudah konsisten dengan:

```text
BAB 1 — Arsitektur Sistem & Modul
BAB 2 — Business Flow
BAB 3 — Struktur Database & Domain Mapping
```

Dokumentasi ini dapat digunakan sebagai dasar untuk merapikan `API_DOCUMENTATION.md`, route Laravel, Form Request, Resource, dan integration contract dengan frontend Vue/Inertia.

# BAB 5 — Standar Implementasi Backend Laravel

## 5.1 Tujuan BAB 5

BAB 5 menetapkan standar implementasi backend Laravel agar seluruh modul dikembangkan dengan pola yang konsisten, mudah diuji, mudah dipelihara, dan selaras dengan arsitektur **Modular Layered Monolith**.

BAB ini menjadi acuan teknis untuk penulisan:

```text
Controller
Form Request
Action
Service
Repository Contract
Eloquent Repository
Model
Resource
Event
Listener
Job
Exception
Trait
Policy
Enum
```

Tujuan utama BAB 5:

1. Menstandarkan cara membuat endpoint backend.
2. Menjaga Controller tetap tipis.
3. Memastikan validasi berada di Form Request.
4. Memastikan proses bisnis utama berada di Action.
5. Memastikan logic domain reusable berada di Service.
6. Memastikan query database berada di Repository.
7. Memastikan response API menggunakan Resource.
8. Memastikan transaksi kritikal menggunakan database transaction.
9. Memastikan error bisnis menggunakan exception yang konsisten.
10. Memastikan setiap modul mengikuti struktur 12 modul utama.

---

## 5.2 Prinsip Implementasi Backend

Backend harus mengikuti prinsip berikut:

```text
Thin Controller
Validated Request
Action-based Business Process
Reusable Domain Service
Repository Pattern
Eloquent Model for Relationship
API Resource for Response
Database Transaction for Critical Process
Event-driven for Side Effect
Queue for Async Non-Critical Process
```

Penjelasan:

| Prinsip              | Keterangan                                                   |
| -------------------- | ------------------------------------------------------------ |
| Thin Controller      | Controller hanya menerima request dan mengembalikan response |
| Validated Request    | Semua validasi input dilakukan di Form Request               |
| Action-based Process | Proses bisnis utama dijalankan melalui Action                |
| Domain Service       | Logic domain yang reusable ditempatkan di Service            |
| Repository Pattern   | Query database tidak ditulis langsung di Controller/Action   |
| Eloquent Model       | Model fokus pada relasi, accessor, mutator, dan casting      |
| API Resource         | Format response API selalu melalui Resource                  |
| DB Transaction       | Posting transaksi kritikal harus atomic                      |
| Event-driven         | Notifikasi, audit, dan realtime dipicu oleh event            |
| Queue                | Proses berat/non-kritikal dijalankan async melalui Job       |

---

## 5.3 Struktur Backend per Modul

Setiap modul harus mengikuti struktur berikut.

```text
app/
├── Actions/{Module}/
├── Services/{Module}/
├── Models/{Module}/
├── Repositories/
│   ├── Contracts/{Module}/
│   └── Eloquent/{Module}/
├── Http/
│   ├── Controllers/Api/{Module}/
│   ├── Requests/{Module}/
│   └── Resources/{Module}/
├── Events/
├── Listeners/
├── Jobs/
├── Enums/
├── Policies/
├── Support/
└── Traits/
```

Contoh untuk modul POS:

```text
app/
├── Actions/POS/
│   ├── CreateSalesTransactionAction.php
│   ├── VoidSalesTransactionAction.php
│   ├── PostSalesReturnAction.php
│   ├── OpenSalesSessionAction.php
│   └── CloseSalesSessionAction.php
│
├── Services/POS/
│   ├── SalesTransactionService.php
│   ├── SalesPaymentService.php
│   ├── SalesSessionService.php
│   └── ReceiptService.php
│
├── Models/POS/
│   ├── SalesTransaction.php
│   ├── SalesTransactionItem.php
│   ├── SalesPayment.php
│   ├── SalesPaymentAllocation.php
│   ├── SalesSession.php
│   ├── SalesHold.php
│   ├── SalesReturn.php
│   └── DayClosing.php
│
├── Repositories/
│   ├── Contracts/POS/
│   │   ├── SalesTransactionRepositoryInterface.php
│   │   └── SalesSessionRepositoryInterface.php
│   └── Eloquent/POS/
│       ├── EloquentSalesTransactionRepository.php
│       └── EloquentSalesSessionRepository.php
│
├── Http/
│   ├── Controllers/Api/POS/
│   │   ├── SalesTransactionController.php
│   │   └── SalesSessionController.php
│   ├── Requests/POS/
│   │   ├── StoreSalesTransactionRequest.php
│   │   └── OpenSalesSessionRequest.php
│   └── Resources/POS/
│       ├── SalesTransactionResource.php
│       └── SalesSessionResource.php
```

---

## 5.4 Standar Controller

Controller harus tipis.

Tanggung jawab Controller:

```text
menerima request
memanggil Action atau Service
mengembalikan Resource / JSON response
```

Controller tidak boleh:

```text
menulis query Eloquent langsung
menulis business logic
menghitung stok
membuat jurnal
mengatur posting transaksi
mengakses banyak model sekaligus
melakukan validasi manual yang seharusnya di Form Request
```

Contoh Controller yang benar:

```php
namespace App\Http\Controllers\Api\POS;

use App\Http\Controllers\Controller;
use App\Http\Requests\POS\StoreSalesTransactionRequest;
use App\Actions\POS\CreateSalesTransactionAction;
use App\Http\Resources\POS\SalesTransactionResource;

class SalesTransactionController extends Controller
{
    public function store(
        StoreSalesTransactionRequest $request,
        CreateSalesTransactionAction $action
    ) {
        $transaction = $action->execute($request->validated());

        return new SalesTransactionResource($transaction);
    }
}
```

Contoh Controller yang salah:

```php
public function store(Request $request)
{
    $transaction = SalesTransaction::create($request->all());

    foreach ($request->items as $item) {
        SalesTransactionItem::create($item);
        InventoryBalance::where('product_variant_id', $item['product_variant_id'])
            ->decrement('qty_on_hand', $item['qty']);
    }

    return response()->json($transaction);
}
```

Masalah pada contoh salah:

```text
validasi tidak jelas
query langsung di Controller
stok diubah langsung
tidak ada Action
tidak ada Service
tidak ada Repository
tidak ada Resource
tidak aman untuk transaksi kritikal
```

---

## 5.5 Standar Form Request

Form Request digunakan untuk validasi input dan authorization awal.

Lokasi:

```text
app/Http/Requests/{Module}/
```

Contoh:

```text
app/Http/Requests/POS/StoreSalesTransactionRequest.php
```

Contoh Form Request:

```php
namespace App\Http\Requests\POS;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalesTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('pos.transaction.create');
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'payments' => ['required', 'array', 'min:1'],
            'payments.*.payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
            'payments.*.amount' => ['required', 'numeric', 'min:0'],
        ];
    }
}
```

Form Request boleh melakukan:

```text
validasi required
validasi tipe data
validasi exists
validasi format tanggal
validasi ukuran file
validasi permission awal
```

Form Request tidak boleh melakukan:

```text
posting transaksi
membuat journal
mengurangi stok
membuat inventory ledger
menghitung business rule kompleks
```

Business rule kompleks tetap berada di Action atau Service.

---

## 5.6 Standar Action

Action adalah pusat orkestrasi proses bisnis utama.

Lokasi:

```text
app/Actions/{Module}/
```

Contoh:

```text
app/Actions/POS/CreateSalesTransactionAction.php
app/Actions/Purchasing/PostGoodsReceiptAction.php
app/Actions/Inventory/PostStockAdjustmentAction.php
app/Actions/Accounting/PostJournalEntryAction.php
```

Action digunakan untuk proses:

```text
create transaction
post transaction
void transaction
approve transaction
close day
close month
generate financial snapshot
```

Action boleh:

```text
membuka database transaction
memanggil beberapa Service
memanggil Event
mengatur urutan proses bisnis
mengembalikan Model hasil transaksi
```

Action tidak boleh:

```text
membuat response API
mengembalikan JSON langsung
berisi query kompleks
berisi logic formatting response
```

Contoh Action:

```php
namespace App\Actions\POS;

use App\Models\POS\SalesTransaction;
use App\Services\POS\SalesTransactionService;
use App\Services\Inventory\InventoryPostingService;
use App\Services\Accounting\JournalPostingService;
use App\Services\Loyalty\LoyaltyPostingService;
use Illuminate\Support\Facades\DB;

class CreateSalesTransactionAction
{
    public function __construct(
        private SalesTransactionService $salesTransactionService,
        private InventoryPostingService $inventoryPostingService,
        private JournalPostingService $journalPostingService,
        private LoyaltyPostingService $loyaltyPostingService,
    ) {}

    public function execute(array $data): SalesTransaction
    {
        return DB::transaction(function () use ($data) {
            $transaction = $this->salesTransactionService->create($data);

            $this->inventoryPostingService->postSale($transaction);
            $this->journalPostingService->postSale($transaction);
            $this->loyaltyPostingService->postSale($transaction);

            event(new \App\Events\SalesTransactionPosted($transaction));

            return $transaction;
        });
    }
}
```

---

### 5.6.1 Contoh Cross-Module Orchestration: CloseMonthAction

`CloseMonthAction` adalah contoh Action yang memang boleh mengorkestrasi beberapa service lintas modul, karena proses Month Closing membutuhkan validasi dan output dari beberapa domain sekaligus.

Owner utama:

```text
Accounting
```

Alasan owner berada di Accounting:

```text
Month Closing menutup fiscal period.
Month Closing memvalidasi journal, trial balance, dan financial snapshot.
Month Closing menghasilkan status periode accounting.
```

Namun prosesnya tetap membutuhkan validasi dari modul lain.

Pola orchestration:

```text
CloseMonthAction
-> POSClosingValidationService
-> InventoryValuationValidationService
-> PurchasingAPValidationService
-> AccountingPeriodClosingService
-> FinancialSnapshotService
```

Tanggung jawab masing-masing service:

| Service                               | Modul Owner            | Tanggung Jawab                                                                                         |
| ------------------------------------- | ---------------------- | ------------------------------------------------------------------------------------------------------ |
| `POSClosingValidationService`         | POS                    | Memastikan semua Day Closing dalam periode sudah selesai dan tidak ada sales session/transaksi pending |
| `InventoryValuationValidationService` | Inventory              | Memastikan stock opname/adjustment penting sudah posted dan inventory valuation valid                  |
| `PurchasingAPValidationService`       | Purchasing             | Memastikan supplier invoice/payment penting sudah posted dan tidak ada AP flow yang menggantung        |
| `AccountingPeriodClosingService`      | Accounting             | Memvalidasi fiscal period, journal posted, trial balance balance, lalu menutup fiscal period           |
| `FinancialSnapshotService`            | Accounting / Reporting | Membuat financial report snapshot dan lines untuk periode yang ditutup                                 |

Contoh struktur Action:

```php
namespace App\Actions\Accounting;

use App\Services\POS\POSClosingValidationService;
use App\Services\Inventory\InventoryValuationValidationService;
use App\Services\Purchasing\PurchasingAPValidationService;
use App\Services\Accounting\AccountingPeriodClosingService;
use App\Services\Accounting\FinancialSnapshotService;
use Illuminate\Support\Facades\DB;

class CloseMonthAction
{
    public function __construct(
        private POSClosingValidationService $posClosingValidationService,
        private InventoryValuationValidationService $inventoryValuationValidationService,
        private PurchasingAPValidationService $purchasingAPValidationService,
        private AccountingPeriodClosingService $accountingPeriodClosingService,
        private FinancialSnapshotService $financialSnapshotService,
    ) {}

    public function execute(array $data): mixed
    {
        return DB::transaction(function () use ($data) {
            $period = $this->accountingPeriodClosingService->findOpenPeriod(
                month: $data['month'],
                year: $data['year'],
            );

            $this->posClosingValidationService->validateMonthReady($period);
            $this->inventoryValuationValidationService->validateMonthReady($period);
            $this->purchasingAPValidationService->validateMonthReady($period);

            $this->accountingPeriodClosingService->validateTrialBalance($period);
            $snapshot = $this->financialSnapshotService->createForPeriod($period);

            $monthClosing = $this->accountingPeriodClosingService->closePeriod(
                period: $period,
                snapshot: $snapshot,
                closedBy: $data['closed_by'],
            );

            event(new \App\Events\MonthClosingCompleted($monthClosing));

            return $monthClosing;
        });
    }
}
```

Aturan implementasi:

```text
CloseMonthAction boleh membaca dan memvalidasi lintas modul.
CloseMonthAction tidak boleh mengambil alih logic internal modul lain.
Validasi POS tetap berada di service POS.
Validasi Inventory tetap berada di service Inventory.
Validasi Purchasing/AP tetap berada di service Purchasing.
Penutupan fiscal period tetap berada di service Accounting.
Financial snapshot dibuat melalui service khusus.
Seluruh proses harus atomic menggunakan database transaction.
Semua hasil closing wajib diaudit.
```

Anti-pattern:

```text
CloseMonthAction langsung query semua tabel lintas modul tanpa service.
CloseMonthAction menutup fiscal period sebelum validasi POS/Inventory/Purchasing selesai.
CloseMonthAction membuat financial report snapshot tanpa trial balance balance.
CloseMonthAction mengubah data transaksi POS/Inventory/Purchasing secara langsung.
```

Final rule:

```text
Action boleh menjadi orchestrator lintas modul.
Service tetap menjadi pemilik logic domain masing-masing.
Month Closing owner = Accounting.
Day Closing owner = POS.
Fiscal Period hanya boleh closed melalui Month Closing.
```

---

## 5.7 Standar Service

Service berisi logic domain yang reusable.

Lokasi:

```text
app/Services/{Module}/
```

Contoh:

```text
app/Services/POS/SalesTransactionService.php
app/Services/Inventory/InventoryPostingService.php
app/Services/Accounting/JournalPostingService.php
app/Services/Pricing/PriceResolverService.php
app/Services/Promotion/PromotionSimulationService.php
```

Service digunakan untuk:

```text
menghitung harga
menghitung promo
validasi stok
posting inventory ledger
posting journal
menghitung loyalty point
menggenerate nomor dokumen
membuat receipt
```

Service boleh memanggil:

```text
Repository
Model melalui Repository
Service lain jika memang lintas domain
Support class
Enum
```

Service tidak boleh:

```text
mengembalikan HTTP response
membaca Request langsung
menggunakan response()->json()
mengatur route
mengakses input mentah dari controller
```

Contoh Service:

```php
namespace App\Services\POS;

use App\Models\POS\SalesTransaction;
use App\Repositories\Contracts\POS\SalesTransactionRepositoryInterface;

class SalesTransactionService
{
    public function __construct(
        private SalesTransactionRepositoryInterface $salesTransactionRepository
    ) {}

    public function create(array $data): SalesTransaction
    {
        return $this->salesTransactionRepository->createWithItemsAndPayments($data);
    }
}
```

---

## 5.8 Standar Repository

Repository digunakan untuk query database.

Struktur:

```text
app/Repositories/
├── Contracts/{Module}/
└── Eloquent/{Module}/
```

Contoh Contract:

```php
namespace App\Repositories\Contracts\POS;

use App\Models\POS\SalesTransaction;

interface SalesTransactionRepositoryInterface
{
    public function createWithItemsAndPayments(array $data): SalesTransaction;

    public function findById(int $id): ?SalesTransaction;

    public function paginateBySession(int $sessionId, int $perPage = 15): mixed;
}
```

Contoh Eloquent Repository:

```php
namespace App\Repositories\Eloquent\POS;

use App\Models\POS\SalesTransaction;
use App\Repositories\Contracts\POS\SalesTransactionRepositoryInterface;

class EloquentSalesTransactionRepository implements SalesTransactionRepositoryInterface
{
    public function createWithItemsAndPayments(array $data): SalesTransaction
    {
        $transaction = SalesTransaction::create([
            'customer_id' => $data['customer_id'] ?? null,
            'transaction_date' => now(),
            'status' => 'POSTED',
        ]);

        foreach ($data['items'] as $item) {
            $transaction->items()->create($item);
        }

        foreach ($data['payments'] as $payment) {
            $transaction->payments()->create($payment);
        }

        return $transaction->load(['items', 'payments']);
    }

    public function findById(int $id): ?SalesTransaction
    {
        return SalesTransaction::query()->find($id);
    }

    public function paginateBySession(int $sessionId, int $perPage = 15): mixed
    {
        return SalesTransaction::query()
            ->where('sales_session_id', $sessionId)
            ->latest()
            ->paginate($perPage);
    }
}
```

Repository boleh:

```text
menulis query Eloquent
menulis join/query report
mengatur eager loading
mengatur pagination
menggunakan lockForUpdate jika dibutuhkan
```

Repository tidak boleh:

```text
membuat response API
membuat journal
memutuskan business rule kompleks
mengirim notifikasi
memanggil event utama
```

---

## 5.9 Standar Repository Binding

Setiap Repository Contract harus di-bind ke implementasi Eloquent.

Lokasi provider:

```text
app/Providers/RepositoryServiceProvider.php
```

Contoh:

```php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\POS\SalesTransactionRepositoryInterface;
use App\Repositories\Eloquent\POS\EloquentSalesTransactionRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            SalesTransactionRepositoryInterface::class,
            EloquentSalesTransactionRepository::class
        );
    }
}
```

Untuk banyak repository, gunakan mapping array:

```php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    protected array $repositories = [
        \App\Repositories\Contracts\POS\SalesTransactionRepositoryInterface::class
            => \App\Repositories\Eloquent\POS\EloquentSalesTransactionRepository::class,

        \App\Repositories\Contracts\Inventory\InventoryLedgerRepositoryInterface::class
            => \App\Repositories\Eloquent\Inventory\EloquentInventoryLedgerRepository::class,

        \App\Repositories\Contracts\Accounting\JournalEntryRepositoryInterface::class
            => \App\Repositories\Eloquent\Accounting\EloquentJournalEntryRepository::class,
    ];

    public function register(): void
    {
        foreach ($this->repositories as $contract => $implementation) {
            $this->app->bind($contract, $implementation);
        }
    }
}
```

Aturan:

```text
semua Repository Contract wajib memiliki binding
jangan inject Eloquent Repository langsung
inject selalu menggunakan Interface
```

---

## 5.10 Standar Model

Model berada di:

```text
app/Models/{Module}/
```

Contoh:

```text
app/Models/POS/SalesTransaction.php
app/Models/POS/SalesPayment.php
app/Models/Inventory/InventoryLedger.php
app/Models/Accounting/JournalEntry.php
```

Model bertanggung jawab untuk:

```text
table mapping
fillable / guarded
casts
relationship
accessor / mutator ringan
scope query sederhana
```

Model tidak boleh:

```text
posting transaksi
mengurangi stok sendiri
membuat jurnal sendiri
mengirim notifikasi
menjalankan business flow kompleks
```

Contoh Model:

```php
namespace App\Models\POS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesTransaction extends Model
{
    protected $fillable = [
        'transaction_no',
        'sales_session_id',
        'customer_id',
        'transaction_date',
        'subtotal',
        'discount_total',
        'tax_total',
        'grand_total',
        'status',
        'created_by',
        'posted_by',
        'posted_at',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'posted_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(SalesTransactionItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SalesPayment::class);
    }
}
```

---

## 5.11 Standar API Resource

Resource digunakan untuk format response API.

Lokasi:

```text
app/Http/Resources/{Module}/
```

Contoh:

```text
app/Http/Resources/POS/SalesTransactionResource.php
```

Contoh Resource:

```php
namespace App\Http\Resources\POS;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'transaction_no' => $this->transaction_no,
            'transaction_date' => $this->transaction_date?->format('Y-m-d H:i:s'),
            'status' => $this->status,
            'subtotal' => $this->subtotal,
            'discount_total' => $this->discount_total,
            'tax_total' => $this->tax_total,
            'grand_total' => $this->grand_total,
            'items' => SalesTransactionItemResource::collection(
                $this->whenLoaded('items')
            ),
            'payments' => SalesPaymentResource::collection(
                $this->whenLoaded('payments')
            ),
        ];
    }
}
```

Resource boleh:

```text
mengatur nama field response
mengatur format tanggal
mengatur nested resource
menyembunyikan field internal
```

Resource tidak boleh:

```text
query database berat
menghitung business rule kompleks
memanggil service
mengubah data
```

---

## 5.12 Standar Database Transaction

Semua proses kritikal harus menggunakan database transaction.

Contoh proses kritikal:

```text
POS transaction
sales return
void transaction
goods receipt posting
supplier invoice posting
supplier payment posting
stock transfer posting
stock adjustment posting
stock opname posting
journal entry posting
day closing
month closing
```

Pattern:

```php
use Illuminate\Support\Facades\DB;

return DB::transaction(function () use ($data) {
    // create transaction header
    // create transaction detail
    // create ledger
    // update balance
    // create journal
    // create audit/event
});
```

Aturan:

```text
jika satu step kritikal gagal, semua harus rollback
jangan dispatch job kritikal sebelum commit
jangan mengirim notifikasi eksternal di dalam transaction jika tidak wajib
gunakan lockForUpdate untuk saldo stok/kas yang rawan race condition
```

Contoh lock stok:

```php
$balance = InventoryBalance::query()
    ->where('product_variant_id', $productVariantId)
    ->where('location_id', $locationId)
    ->lockForUpdate()
    ->first();
```

---

## 5.13 Standar Business Exception

Business rule error harus menggunakan exception khusus, bukan `abort()` sembarangan.

Contoh:

```text
InsufficientStockException
ClosedPeriodException
InvalidPaymentAmountException
JournalNotBalanceException
DocumentAlreadyPostedException
ApprovalRequiredException
```

Contoh Exception:

```php
namespace App\Exceptions;

use Exception;

class BusinessException extends Exception
{
    public function __construct(
        string $message,
        protected array $errors = [],
        protected int $statusCode = 409
    ) {
        parent::__construct($message);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }
}
```

Contoh penggunaan:

```php
throw new BusinessException(
    message: 'Stock is not enough for this transaction',
    errors: [
        'product_variant_id' => $productVariantId,
        'available_qty' => $availableQty,
        'requested_qty' => $requestedQty,
    ]
);
```

Format response:

```json
{
    "success": false,
    "message": "Stock is not enough for this transaction",
    "errors": {
        "product_variant_id": 10,
        "available_qty": 3,
        "requested_qty": 5
    }
}
```

---

## 5.14 Standar Enum

Enum digunakan untuk status dan tipe yang berulang.

Lokasi:

```text
app/Enums/
```

Contoh enum:

```text
DocumentStatus.php
SalesTransactionStatus.php
PaymentType.php
InventoryMovementType.php
JournalStatus.php
ApprovalStatus.php
```

Contoh:

```php
namespace App\Enums;

enum DocumentStatus: string
{
    case DRAFT = 'DRAFT';
    case PENDING = 'PENDING';
    case APPROVED = 'APPROVED';
    case POSTED = 'POSTED';
    case CANCELLED = 'CANCELLED';
    case VOID = 'VOID';
    case CLOSED = 'CLOSED';
}
```

Aturan:

```text
gunakan Enum untuk status dokumen
hindari string status hardcode berulang
gunakan enum value di database
validasi status harus merujuk enum
```

---

## 5.15 Standar Trait

Trait digunakan untuk behavior yang berulang.

Lokasi:

```text
app/Traits/
```

Contoh trait:

```text
HasCreatedBy
HasUpdatedBy
HasPostedBy
HasApprovedBy
HasUuid
HasDocumentNumber
HasStatus
```

Contoh:

```php
namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\System\User;

trait HasPostedBy
{
    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function markAsPosted(int $userId): void
    {
        $this->forceFill([
            'status' => 'POSTED',
            'posted_by' => $userId,
            'posted_at' => now(),
        ])->save();
    }
}
```

Aturan:

```text
Trait hanya untuk behavior umum
Trait tidak boleh berisi proses bisnis besar
Trait tidak boleh menggantikan Service
```

---

## 5.16 Standar Policy dan Permission

Policy digunakan untuk authorization berbasis model.

Lokasi:

```text
app/Policies/
```

Contoh:

```text
SalesTransactionPolicy
PurchaseOrderPolicy
JournalEntryPolicy
StockAdjustmentPolicy
```

Contoh Policy:

```php
namespace App\Policies;

use App\Models\System\User;
use App\Models\POS\SalesTransaction;

class SalesTransactionPolicy
{
    public function view(User $user, SalesTransaction $transaction): bool
    {
        return $user->hasPermission('pos.transaction.view');
    }

    public function void(User $user, SalesTransaction $transaction): bool
    {
        return $user->hasPermission('pos.transaction.void')
            && $transaction->status === 'POSTED';
    }
}
```

Format permission:

```text
{module}.{resource}.{action}
```

Contoh:

```text
pos.transaction.create
pos.transaction.void
purchasing.purchase-order.approve
inventory.stock-adjustment.post
accounting.journal-entry.post
reporting.sales.view
```

---

## 5.17 Standar Number Generator

Nomor dokumen tidak boleh dibuat manual di banyak tempat.

Gunakan service khusus:

```text
app/Services/System/DocumentNumberService.php
```

Contoh penggunaan:

```php
$documentNo = $this->documentNumberService->generate('SALES_TRANSACTION');
```

Sumber konfigurasi:

```text
document_types
document_sequences
```

Contoh format:

```text
POS-20260621-0001
PO-20260621-0001
GR-20260621-0001
SI-20260621-0001
JE-20260621-0001
```

Aturan:

```text
nomor dokumen harus unik
nomor dokumen harus atomic
gunakan lockForUpdate pada sequence
jangan generate nomor dokumen dari frontend
```

---

## 5.18 Standar Event dan Listener

Audit Trail secara domain didefinisikan penuh pada BAB 7.

Pada BAB 5, audit trail hanya dibahas dari sisi implementasi backend.

Aturan implementasi:

```text
Untuk transaksi kritikal, audit dibuat eksplisit di Action/Service.
Untuk master data sederhana, audit boleh dibuat melalui Observer.
Untuk activity/security event, audit dapat dibuat melalui Listener.
Daftar aktivitas wajib audit mengikuti BAB 7.15 - 7.17.
```

Audit dapat dibuat melalui:

```text
Action
Service
Observer
Event Listener
```

Rekomendasi:

```text
Jangan menduplikasi daftar aktivitas wajib audit di BAB 5.
Jika ada perbedaan, BAB 7 menjadi source-of-truth.
```

---

## 5.19 Standar Job

Job digunakan untuk proses async non-kritikal.

Contoh job:

```text
GenerateSalesReportJob
GenerateFinancialSnapshotJob
ExportInventoryValuationJob
SendNotificationJob
BroadcastDashboardUpdateJob
RetryPostingEventJob
```

Job boleh digunakan untuk:

```text
export report
generate snapshot besar
send notification
broadcast dashboard
sync external service
retry outbox
```

Job tidak boleh digunakan untuk proses kritikal yang harus langsung konsisten, seperti:

```text
mengurangi stok utama POS
membuat journal utama POS
membentuk AP saat supplier invoice posted
```

Kecuali sistem sudah menerapkan outbox pattern dan eventual consistency dengan desain matang.

---

## 5.20 Standar Testing Backend

Setiap modul minimal memiliki test untuk:

```text
Form Request validation
Repository query
Service business rule
Action transaction flow
API endpoint
Authorization / permission
```

Lokasi:

```text
tests/Feature/{Module}/
tests/Unit/{Module}/
```

Contoh:

```text
tests/Feature/POS/CreateSalesTransactionTest.php
tests/Feature/Inventory/PostStockAdjustmentTest.php
tests/Feature/Accounting/PostJournalEntryTest.php
tests/Unit/Pricing/PriceResolverServiceTest.php
```

Test penting POS:

```text
cashier can create transaction
cannot create transaction without open session
cannot sell inactive product
cannot sell when stock insufficient
payment total must match grand total
transaction creates inventory ledger
transaction creates journal entry
transaction creates loyalty transaction if customer exists
```

Test penting Accounting:

```text
journal debit credit must balance
posted journal cannot be edited
closed fiscal period cannot accept new journal
reverse journal creates opposite lines
```

Test penting Inventory:

```text
goods receipt increases stock
sales transaction decreases stock
adjustment requires reason
stock transfer creates transfer out and transfer in
stock opname creates variance ledger
```

---

## 5.21 Contoh Implementasi Flow POS Lengkap

Contoh alur implementasi backend untuk create POS transaction:

```text
SalesTransactionController@store
-> StoreSalesTransactionRequest
-> CreateSalesTransactionAction
-> SalesTransactionService
-> SalesTransactionRepository
-> InventoryPostingService
-> JournalPostingService
-> LoyaltyPostingService
-> SalesTransactionResource
```

Detail:

```text
1. Controller menerima request.
2. Form Request melakukan validasi input dan permission.
3. Action membuka database transaction.
4. Service membuat sales transaction.
5. Repository menyimpan header, item, dan payment.
6. Inventory service membuat inventory ledger SALE_OUT.
7. Inventory service update inventory balance.
8. Accounting service membuat journal entry dan journal lines.
9. Loyalty service mencatat earn/redeem point jika ada.
10. Event SalesTransactionPosted dipublish.
11. Resource mengembalikan response API.
```

Flow ini harus atomic.

Jika langkah 6, 7, atau 8 gagal, maka seluruh transaksi POS harus rollback.

---

## 5.22 Standar Penanganan File Upload

File upload dilakukan melalui endpoint khusus.

Contoh:

```text
POST /products/{id}/images
POST /product-brands/{id}/logo
```

Aturan upload:

```text
validasi file wajib di Form Request
validasi mime type
validasi max size
gunakan Laravel Storage
simpan path relatif, bukan absolute path server
response menggunakan URL publik atau temporary URL
```

Contoh validasi:

```php
public function rules(): array
{
    return [
        'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
    ];
}
```

---

## 5.23 Standar Logging

Gunakan logging untuk error teknis, bukan untuk menggantikan audit.

Perbedaan:

| Jenis        | Fungsi                      |
| ------------ | --------------------------- |
| Log          | Debugging teknis            |
| Audit Log    | Jejak perubahan data bisnis |
| Activity Log | Jejak aktivitas user        |
| System Event | Jejak event sistem          |

Log teknis boleh mencatat:

```text
exception
failed job
external service error
database timeout
unexpected error
```

Log teknis tidak boleh menampilkan:

```text
password
token
secret key
payment sensitive data
data pribadi berlebihan
```

---

## 5.24 Standar Implementasi Modul Baru

Saat membuat fitur baru, developer wajib membuat minimal:

```text
Controller
Form Request
Action jika proses bisnis utama
Service jika ada logic domain
Repository Contract
Eloquent Repository
Model
Resource
Route
Test
```

Checklist fitur baru:

```text
[ ] Route sudah sesuai module owner
[ ] Controller tipis
[ ] Form Request tersedia
[ ] Action tersedia untuk proses bisnis
[ ] Service tersedia untuk logic reusable
[ ] Repository Contract tersedia
[ ] Eloquent Repository tersedia
[ ] Binding repository sudah didaftarkan
[ ] Model berada di namespace module
[ ] Resource tersedia
[ ] Permission dicek
[ ] Audit/event dibuat jika perlu
[ ] Test dibuat
```

---

## 5.25 Kesimpulan BAB 5

Keputusan final BAB 5:

```text
Backend Laravel menggunakan pola Modular Layered Monolith.
Controller harus tipis.
Form Request menjadi pusat validasi input.
Action menjadi pusat orkestrasi proses bisnis utama.
Service menjadi pusat logic domain reusable.
Repository menjadi pusat query database.
Model fokus pada relasi dan representasi tabel.
Resource menjadi standar response API.
Database transaction wajib untuk proses kritikal.
Business exception digunakan untuk error aturan bisnis.
Event, Listener, dan Job digunakan untuk proses non-kritikal.
```

Standar alur backend:

```text
Controller
-> Form Request
-> Action
-> Service
-> Repository Contract
-> Eloquent Repository
-> Model
-> Resource
```

Dengan BAB 5 ini, seluruh implementasi backend memiliki standar teknis yang jelas dan konsisten dengan:

```text
BAB 1 — Arsitektur Sistem & Modul
BAB 2 — Business Flow
BAB 3 — Struktur Database & Domain Mapping
BAB 4 — API Contract
```

# BAB 6 — Standar Implementasi Frontend Vue 3 + Inertia

## 6.1 Tujuan BAB 6

BAB 6 menetapkan standar implementasi frontend menggunakan **Vue 3 + Inertia** agar seluruh halaman aplikasi memiliki struktur kode, pola komponen, alur data, dan pengalaman pengguna yang konsisten.

BAB ini menjadi acuan teknis untuk penulisan:

```text
Page
Layout
Component
Form
Table
Modal
Dialog
Filter
Pagination
Notification
Permission Guard
Realtime UI
Frontend Validation
Error Handling
```

Tujuan utama BAB 6:

1. Menstandarkan struktur folder frontend.
2. Menstandarkan penggunaan Vue 3 Composition API.
3. Menstandarkan penggunaan Inertia Page dan Layout.
4. Menstandarkan komponen reusable.
5. Menstandarkan form dan error handling.
6. Menstandarkan table, filter, search, sort, dan pagination.
7. Menstandarkan permission-based UI.
8. Menstandarkan halaman POS agar cepat dan user friendly.
9. Menstandarkan realtime update menggunakan Laravel Reverb.
10. Menjaga frontend tetap konsisten dengan API Contract pada BAB 4.

---

## 6.2 Prinsip Implementasi Frontend

Frontend harus mengikuti prinsip berikut:

```text
Consistent UI
Reusable Component
Page-based Structure
Inertia-driven Navigation
Permission-aware Interface
Form State Management
Clear Loading State
Clear Error State
Responsive Layout
Realtime-ready Interface
```

Penjelasan:

| Prinsip                    | Keterangan                                                |
| -------------------------- | --------------------------------------------------------- |
| Consistent UI              | Tampilan antar modul harus seragam                        |
| Reusable Component         | Komponen umum tidak ditulis berulang                      |
| Page-based Structure       | Setiap route Inertia memiliki page yang jelas             |
| Inertia-driven Navigation  | Navigasi utama menggunakan Inertia                        |
| Permission-aware Interface | Tombol/action mengikuti permission user                   |
| Form State Management      | Form memiliki state, loading, error, dan reset yang jelas |
| Clear Loading State        | User tahu saat data sedang diproses                       |
| Clear Error State          | Error validasi dan business rule tampil jelas             |
| Responsive Layout          | UI nyaman untuk desktop, tablet, dan kasir                |
| Realtime-ready Interface   | Dashboard/POS/inventory siap menerima update realtime     |

---

## 6.3 Struktur Folder Frontend

Struktur frontend disarankan berada di:

```text
resources/js/
```

Struktur standar:

```text
resources/js/
├── App.vue
├── app.js
├── bootstrap.js
│
├── Layouts/
│   ├── AuthLayout.vue
│   ├── DashboardLayout.vue
│   ├── POSLayout.vue
│   └── GuestLayout.vue
│
├── Pages/
│   ├── Auth/
│   ├── Dashboard/
│   ├── System/
│   ├── MasterData/
│   ├── Product/
│   ├── Pricing/
│   ├── Promotion/
│   ├── POS/
│   ├── Inventory/
│   ├── Purchasing/
│   ├── Loyalty/
│   ├── Accounting/
│   └── Reporting/
│
├── Components/
│   ├── Base/
│   ├── Form/
│   ├── Table/
│   ├── Modal/
│   ├── Navigation/
│   ├── Feedback/
│   ├── DataDisplay/
│   └── POS/
│
├── Composables/
│   ├── useAuth.js
│   ├── usePermission.js
│   ├── useFormError.js
│   ├── useConfirmDialog.js
│   ├── useToast.js
│   ├── usePagination.js
│   ├── useFilter.js
│   └── useRealtime.js
│
├── Stores/
│   ├── authStore.js
│   ├── posStore.js
│   ├── cartStore.js
│   ├── notificationStore.js
│   └── uiStore.js
│
├── Services/
│   ├── api.js
│   ├── route.js
│   ├── currency.js
│   ├── date.js
│   └── permission.js
│
├── Constants/
│   ├── modules.js
│   ├── permissions.js
│   ├── statuses.js
│   └── routes.js
│
└── Utils/
    ├── formatter.js
    ├── number.js
    ├── debounce.js
    └── object.js
```

Aturan:

```text
Pages hanya untuk halaman utama.
Components untuk reusable UI.
Composables untuk reusable logic.
Stores untuk shared state.
Services untuk helper komunikasi/API/format.
Constants untuk daftar status, permission, dan konfigurasi statis.
Utils untuk fungsi kecil non-domain.
```

---

## 6.4 Standar Layout

Frontend minimal memiliki layout berikut:

```text
AuthLayout
DashboardLayout
POSLayout
GuestLayout
```

### 6.4.1 AuthLayout

Digunakan untuk:

```text
Login
Register jika ada
Forgot Password
Reset Password
```

Ciri:

```text
tanpa sidebar
tanpa topbar dashboard
form berada di tengah
tampilan sederhana dan fokus
```

---

### 6.4.2 DashboardLayout

Digunakan untuk halaman backoffice:

```text
Dashboard
Master Data
Product
Pricing
Promotion
Inventory
Purchasing
Loyalty
Accounting
Reporting
System Setting
```

Ciri:

```text
sidebar navigation
topbar
breadcrumb
user menu
notification
content area
```

---

### 6.4.3 POSLayout

Digunakan untuk halaman kasir.

Ciri:

```text
full screen friendly
minim distraksi
akses cepat produk
cart selalu terlihat
payment panel jelas
support keyboard shortcut
support touch screen
```

POSLayout tidak harus sama dengan DashboardLayout karena kebutuhan kasir berbeda.

---

### 6.4.4 GuestLayout

Digunakan untuk halaman publik jika ada.

Contoh:

```text
landing page
public receipt
customer reward page
```

---

## 6.5 Standar Page per Modul

Setiap modul memiliki folder sendiri di `Pages`.

Contoh POS:

```text
resources/js/Pages/POS/
├── Dashboard.vue
├── Sales/
│   ├── Index.vue
│   ├── Show.vue
│   └── POSScreen.vue
├── Sessions/
│   ├── Index.vue
│   └── Show.vue
├── Returns/
│   ├── Index.vue
│   └── Create.vue
└── Closings/
    ├── DayClosing.vue
    └── MonthClosing.vue
```

Contoh Product:

```text
resources/js/Pages/Product/
├── Products/
│   ├── Index.vue
│   ├── Create.vue
│   ├── Edit.vue
│   └── Show.vue
├── Brands/
│   ├── Index.vue
│   └── Form.vue
└── Categories/
    ├── Index.vue
    └── Form.vue
```

Aturan:

```text
Index.vue untuk list data.
Create.vue untuk form tambah jika halaman penuh.
Edit.vue untuk form edit jika halaman penuh.
Show.vue untuk detail.
Form.vue boleh dipakai sebagai reusable partial.
Modal form boleh dipakai jika proses sederhana.
```

---

## 6.6 Standar Component

Komponen reusable disimpan di:

```text
resources/js/Components/
```

### 6.6.1 Base Components

```text
BaseButton.vue
BaseInput.vue
BaseSelect.vue
BaseTextarea.vue
BaseCheckbox.vue
BaseRadio.vue
BaseBadge.vue
BaseCard.vue
BaseDropdown.vue
BaseIcon.vue
```

Aturan:

```text
Base component tidak boleh mengandung logic domain.
Base component hanya mengatur UI umum.
Base component harus reusable di semua modul.
```

---

### 6.6.2 Form Components

```text
FormInput.vue
FormSelect.vue
FormDatePicker.vue
FormCurrencyInput.vue
FormNumberInput.vue
FormError.vue
FormLabel.vue
FormActions.vue
```

Aturan:

```text
Form component harus bisa menerima error dari Laravel validation.
Form component harus support disabled/loading state.
Form component harus konsisten untuk semua modul.
```

---

### 6.6.3 Table Components

```text
DataTable.vue
TableHeader.vue
TablePagination.vue
TableSearch.vue
TableFilter.vue
TableAction.vue
TableEmptyState.vue
```

Aturan:

```text
Table harus support pagination.
Table harus support search.
Table harus support filter.
Table harus support sort jika diperlukan.
Table harus punya empty state.
Table harus punya loading state.
```

---

### 6.6.4 Modal Components

```text
BaseModal.vue
ConfirmDialog.vue
DeleteConfirmDialog.vue
PostConfirmDialog.vue
ApprovalDialog.vue
RejectDialog.vue
```

Aturan:

```text
Action berisiko wajib memakai confirm dialog.
Void/reject/cancel wajib meminta reason.
Delete master data wajib confirm.
Posting transaksi wajib confirm jika dampaknya besar.
```

---

### 6.6.5 Feedback Components

```text
Toast.vue
Alert.vue
LoadingOverlay.vue
SkeletonLoader.vue
EmptyState.vue
ErrorState.vue
```

Aturan:

```text
Sukses tampil sebagai toast.
Validasi tampil dekat input.
Business error tampil sebagai alert/dialog.
Loading proses panjang wajib jelas.
```

---

## 6.7 Standar Vue 3 Composition API

Frontend menggunakan Composition API.

Contoh struktur component:

```vue
<script setup>
import { ref, computed } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    products: {
        type: Object,
        required: true,
    },
});

const search = ref("");

const hasData = computed(() => {
    return props.products?.data?.length > 0;
});

function submitSearch() {
    router.get(
        "/products",
        {
            search: search.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
}
</script>

<template>
    <div>
        <!-- template -->
    </div>
</template>
```

Aturan:

```text
Gunakan <script setup>.
Gunakan ref untuk state sederhana.
Gunakan computed untuk derived state.
Gunakan composable untuk logic reusable.
Hindari Options API untuk fitur baru.
```

---

## 6.8 Standar Inertia Form

Gunakan `useForm` dari Inertia untuk form.

Contoh:

```vue
<script setup>
import { useForm } from "@inertiajs/vue3";

const form = useForm({
    name: "",
    sku: "",
    category_id: null,
    is_active: true,
});

function submit() {
    form.post("/products", {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
}
</script>
```

Template:

```vue
<template>
    <form @submit.prevent="submit">
        <FormInput
            v-model="form.name"
            label="Nama Produk"
            :error="form.errors.name"
        />

        <FormInput v-model="form.sku" label="SKU" :error="form.errors.sku" />

        <BaseButton type="submit" :loading="form.processing">
            Simpan
        </BaseButton>
    </form>
</template>
```

Aturan:

```text
Gunakan useForm untuk create/update sederhana.
Tampilkan error langsung di field.
Gunakan form.processing untuk loading.
Gunakan preserveScroll saat validasi gagal.
Reset form hanya jika proses sukses.
```

---

## 6.9 Standar Error Handling Frontend

Frontend harus mampu menangani:

```text
Validation Error 422
Business Rule Error 409
Unauthorized 401
Forbidden 403
Not Found 404
Server Error 500
```

### 6.9.1 Validation Error

Validation error tampil pada field terkait.

Contoh:

```text
Nama produk wajib diisi.
Harga tidak boleh kurang dari 0.
Payment method wajib dipilih.
```

---

### 6.9.2 Business Rule Error

Business rule error tampil sebagai alert/dialog.

Contoh:

```text
Stok tidak cukup.
Periode sudah closed.
Journal tidak balance.
Payment tidak sesuai grand total.
Session kasir belum open.
```

---

### 6.9.3 Forbidden Error

Jika user tidak punya permission:

```text
tombol disembunyikan jika memungkinkan
jika tetap diakses langsung, tampilkan halaman 403
```

---

### 6.9.4 Server Error

Server error tampil sebagai pesan umum:

```text
Terjadi kesalahan pada server. Silakan coba lagi atau hubungi administrator.
```

Jangan tampilkan stack trace di frontend production.

---

## 6.10 Standar Permission-based UI

Frontend harus membaca permission user dari shared props Inertia.

Contoh shared props:

```json
{
    "auth": {
        "user": {
            "id": 1,
            "name": "Admin",
            "roles": ["Admin"],
            "permissions": [
                "product.create",
                "product.update",
                "pos.transaction.create"
            ]
        }
    }
}
```

Composable:

```js
import { usePage } from "@inertiajs/vue3";

export function usePermission() {
    const page = usePage();

    function can(permission) {
        const permissions = page.props.auth?.user?.permissions || [];
        return permissions.includes(permission);
    }

    function canAny(permissionList) {
        return permissionList.some((permission) => can(permission));
    }

    return {
        can,
        canAny,
    };
}
```

Contoh penggunaan:

```vue
<script setup>
import { usePermission } from "@/Composables/usePermission";

const { can } = usePermission();
</script>

<template>
    <BaseButton v-if="can('product.create')"> Tambah Produk </BaseButton>
</template>
```

Aturan:

```text
UI boleh menyembunyikan tombol berdasarkan permission.
Backend tetap wajib melakukan authorization.
Frontend permission hanya untuk UX, bukan keamanan utama.
```

---

## 6.11 Standar Data Table

Setiap halaman list menggunakan pola table yang konsisten.

Fitur minimal:

```text
search
filter
sort
pagination
row action
empty state
loading state
```

Contoh query:

```text
/products?search=kopi&category_id=1&sort=name&direction=asc&page=1&per_page=15
```

Komponen:

```vue
<DataTable
    :columns="columns"
    :rows="products.data"
    :pagination="products.meta"
    :loading="loading"
/>
```

Aturan:

```text
Search menggunakan debounce.
Filter tidak boleh reload penuh jika bisa preserve state.
Pagination mengikuti response API.
Action table mengikuti permission.
```

---

## 6.12 Standar Filter dan Search

Filter standar:

```text
search
status
date_from
date_to
category_id
supplier_id
customer_id
location_id
payment_method_id
```

Aturan:

```text
Filter harus terlihat jelas.
Filter aktif harus bisa di-reset.
Date range harus valid.
Search sebaiknya debounce 300-500ms.
```

Contoh:

```js
import { reactive } from "vue";
import { router } from "@inertiajs/vue3";

const filters = reactive({
    search: "",
    status: "",
    date_from: "",
    date_to: "",
});

function applyFilter() {
    router.get("/pos/transactions", filters, {
        preserveState: true,
        preserveScroll: true,
    });
}
```

---

## 6.13 Standar Modal dan Dialog

Modal digunakan untuk proses ringan.

Cocok untuk:

```text
create/edit master sederhana
confirm delete
confirm post
confirm approve/reject
input reason void/cancel/reject
```

Tidak cocok untuk:

```text
form transaksi besar
stock opname besar
purchase order kompleks
supplier invoice kompleks
laporan kompleks
```

Aturan:

```text
Modal harus bisa close dengan aman.
Jika form dirty, tampilkan confirm sebelum close.
Action besar wajib confirm.
Reason wajib untuk void, cancel, reject, dan adjustment tertentu.
```

---

## 6.14 Standar Halaman POS

Halaman POS adalah halaman khusus dan tidak harus mengikuti layout backoffice biasa.

### 6.14.1 Struktur Halaman POS

Komponen utama:

```text
Product Search
Category Filter
Product Grid
Cart Panel
Customer Selector
Promotion Summary
Payment Panel
Hold Bill
Receipt Preview
Session Info
```

Contoh layout:

```text
┌────────────────────────────────────────────┐
│ Top Bar: Cashier, Session, Shift, Time     │
├───────────────────────┬────────────────────┤
│ Product Area          │ Cart Area          │
│ - Search              │ - Items            │
│ - Category            │ - Product Grid     │
│                       │ - Qty              │
│                       │ - Discount         │
│                       │ - Tax              │
│                       │ - Grand Total      │
│                       │ - Payment          │
└───────────────────────┴────────────────────┘
```

---

### 6.14.2 Prinsip UX POS

```text
cepat
minim klik
mudah dipakai kasir
support barcode scanner
support keyboard shortcut
support touch screen
cart selalu terlihat
grand total jelas
payment jelas
error stok/payment langsung terlihat
```

---

### 6.14.3 Shortcut POS

Rekomendasi shortcut:

| Shortcut | Fungsi               |
| -------- | -------------------- |
| F2       | Fokus search product |
| F4       | Pilih customer       |
| F6       | Hold bill            |
| F8       | Payment              |
| F9       | Print receipt        |
| Esc      | Close modal          |
| Enter    | Confirm action       |

---

### 6.14.4 Validasi POS Frontend

Frontend boleh melakukan validasi awal:

```text
cart tidak boleh kosong
qty harus lebih dari 0
payment total harus sama dengan grand total
customer wajib jika pakai loyalty point
session harus open
```

Namun validasi final tetap wajib dilakukan backend.

---

## 6.15 Standar Halaman Inventory

Halaman inventory harus memudahkan tracking stok.

Halaman utama:

```text
Inventory Balance
Inventory Ledger
Stock Card
Stock Transfer
Stock Adjustment
Stock Opname
Planogram
```

Standar tampilan:

```text
filter product
filter location
filter date range
filter movement type
saldo awal
mutasi masuk
mutasi keluar
saldo akhir
```

Stock Card harus memperlihatkan:

```text
tanggal
document_no
movement_type
qty_in
qty_out
balance
reference
created_by
```

Endpoint canonical:

```text
GET /inventory/stock-card
```

Aturan:

```text
Stock Card adalah fitur Inventory.
Reporting boleh menampilkan shortcut, tetapi tidak membuat endpoint /reports/stock-card.
```

---

## 6.16 Standar Halaman Purchasing

Halaman purchasing harus memperlihatkan status dokumen dengan jelas.

Dokumen utama:

```text
Purchase Request
Purchase Order
Goods Receipt
Supplier Invoice
Supplier Payment
Accounts Payable
Purchase Return
```

Status harus terlihat sebagai badge:

```text
DRAFT
PENDING
APPROVED
POSTED
CANCELLED
REJECTED
CLOSED
```

Action mengikuti status:

| Status    | Action               |
| --------- | -------------------- |
| DRAFT     | Edit, Submit, Delete |
| PENDING   | Approve, Reject      |
| APPROVED  | Create next document |
| POSTED    | View only            |
| CANCELLED | View only            |

---

## 6.17 Standar Halaman Accounting

Halaman accounting harus fokus pada akurasi dan audit.

Halaman utama:

```text
Chart of Accounts
Payment Methods
Journal Entries
General Ledger
Trial Balance
Fiscal Period
Financial Report Snapshot
Accounting Rules
Journal Templates
```

Aturan UI accounting:

```text
Journal debit credit harus jelas.
Journal tidak balance harus diberi warning.
Posted journal view only.
Reverse journal wajib confirm.
Fiscal period closed harus diberi badge jelas.
```

Contoh tampilan journal line:

| Account       | Description |   Debit | Credit |
| ------------- | ----------- | ------: | -----: |
| Cash          | Payment POS | 100.000 |      0 |
| Sales Revenue | Sales       |       0 | 90.000 |
| Output Tax    | Tax         |       0 | 10.000 |

---

## 6.18 Standar Halaman Reporting

Reporting bersifat read-only.

Halaman report:

```text
Sales Report
Inventory Report
Inventory Movement
Inventory Valuation
Purchase Report
Accounts Payable Report
Supplier Performance Report
Financial Report
Dashboard Report
```

Fitur standar:

```text
filter tanggal/periode
filter modul terkait
summary card
table detail
export CSV/PDF jika tersedia
loading state
empty state
```

Aturan:

```text
Reporting tidak boleh punya tombol post/edit/delete transaksi.
Export boleh async jika data besar.
Financial report periode closed sebaiknya membaca snapshot.
```

---

## 6.19 Standar Realtime UI

Realtime menggunakan Laravel Reverb.

Realtime cocok untuk:

```text
dashboard sales hari ini
notifikasi approval
stok kritis
transaksi POS baru
closing completed
job export selesai
```

Composable:

```text
useRealtime.js
```

Contoh event:

```text
SalesTransactionPosted
StockAdjustmentPosted
GoodsReceiptPosted
DayClosingCompleted
MonthClosingCompleted
NotificationCreated
```

Aturan:

```text
Realtime hanya untuk update UI.
Realtime bukan sumber kebenaran data.
Jika menerima event penting, frontend boleh refetch data.
Jangan tampilkan data sensitif di event payload.
```

---

## 6.20 Standar Loading State

Setiap proses yang memerlukan waktu harus memiliki loading state.

Contoh:

```text
submit form
load table
generate report
export file
post transaction
approve document
```

Jenis loading:

```text
button loading
table skeleton
page loading
modal loading
overlay loading untuk proses kritikal
```

Aturan:

```text
Button submit disabled saat processing.
Jangan izinkan double submit.
Untuk transaksi POS, cegah klik bayar berkali-kali.
```

---

## 6.21 Standar Empty State

Setiap halaman list harus memiliki empty state.

Contoh:

```text
Belum ada produk.
Belum ada transaksi penjualan.
Belum ada data stok.
Belum ada jurnal pada periode ini.
```

Empty state boleh memiliki action jika user punya permission.

Contoh:

```text
Belum ada produk.
[Tambah Produk]
```

---

## 6.22 Standar Notification dan Toast

Toast digunakan untuk feedback ringan.

Contoh sukses:

```text
Data berhasil disimpan.
Transaksi berhasil diposting.
Jurnal berhasil direversal.
```

Alert/dialog digunakan untuk error penting.

Contoh:

```text
Stok tidak cukup.
Periode sudah closed.
Anda tidak memiliki permission.
```

Aturan:

```text
Toast jangan dipakai untuk error kritikal yang butuh perhatian.
Error kritikal gunakan alert/dialog.
```

---

## 6.23 Standar State Management

State global hanya digunakan untuk data yang benar-benar dipakai lintas halaman.

Contoh state global:

```text
auth user
permissions
sidebar state
notification count
POS cart
current sales session
```

State lokal halaman tetap di component/page.

Rekomendasi:

```text
Gunakan Pinia jika state mulai kompleks.
Gunakan composable jika state sederhana dan reusable.
Jangan simpan semua data API ke global store.
```

---

## 6.24 Standar Format Angka, Uang, dan Tanggal

Format uang menggunakan Rupiah.

Contoh:

```text
Rp 10.000
Rp 1.250.000
```

Format tanggal:

```text
21/06/2026
21/06/2026 14:30
```

Service formatter:

```text
resources/js/Services/currency.js
resources/js/Services/date.js
```

Contoh:

```js
export function formatRupiah(value) {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(value);
}
```

Aturan:

```text
Jangan format currency manual di banyak component.
Gunakan helper terpusat.
```

---

## 6.25 Standar Frontend Testing

Frontend test dapat dibuat untuk komponen penting.

Prioritas test:

```text
POS cart calculation
payment validation
permission button visibility
form validation display
table filter behavior
modal confirm action
```

Tools yang bisa digunakan:

```text
Vitest
Vue Test Utils
Playwright untuk E2E jika diperlukan
```

Contoh test penting POS:

```text
cart subtotal dihitung benar
discount mengurangi grand total
payment kurang tidak bisa submit
payment pas bisa submit
loyalty point wajib customer
```

---

## 6.26 Checklist Implementasi Halaman Baru

Saat membuat halaman baru, developer wajib memastikan:

```text
[ ] Page berada di folder module yang benar
[ ] Menggunakan layout yang sesuai
[ ] Menggunakan komponen reusable
[ ] Form memakai useForm atau pattern standar
[ ] Error validasi tampil di field
[ ] Loading state tersedia
[ ] Empty state tersedia untuk list
[ ] Action button mengikuti permission
[ ] Table mendukung pagination jika data banyak
[ ] Filter/search mengikuti standar query
[ ] Modal confirm tersedia untuk action berisiko
[ ] Format uang/tanggal memakai helper
[ ] Tidak ada hardcode permission sembarangan
[ ] Tidak ada duplicate component tanpa alasan
```

---

## 6.27 Kesimpulan BAB 6

Keputusan final BAB 6:

```text
Frontend menggunakan Vue 3 + Inertia.
Struktur halaman mengikuti 12 modul utama.
Layout dipisah menjadi AuthLayout, DashboardLayout, POSLayout, dan GuestLayout.
Komponen reusable ditempatkan di Components.
Logic reusable ditempatkan di Composables.
State global hanya untuk data lintas halaman.
Form menggunakan useForm atau pattern standar Inertia.
Error validation tampil pada field.
Business error tampil sebagai alert/dialog.
Permission-based UI digunakan untuk menyembunyikan action yang tidak boleh diakses.
POS memiliki layout khusus yang cepat dan minim distraksi.
Reporting bersifat read-only.
Realtime digunakan untuk update UI, bukan sumber kebenaran data.
```

Standar frontend harus selaras dengan:

```text
BAB 1 — Arsitektur Sistem & Modul
BAB 2 — Business Flow
BAB 3 — Struktur Database & Domain Mapping
BAB 4 — API Contract
BAB 5 — Standar Implementasi Backend Laravel
```

Dengan BAB 6 ini, implementasi frontend memiliki standar teknis yang jelas, konsisten, dan siap mendukung pengembangan modul POS, Inventory, Purchasing, Loyalty, Accounting, dan Reporting secara bertahap.

# BAB 7 — Security, RBAC, Approval & Audit Trail Standard

## 7.1 Tujuan BAB 7

BAB 7 menetapkan standar keamanan sistem, role-based access control, approval, audit trail, dan activity logging untuk seluruh modul ERP POS.

BAB ini penting karena sistem memproses data sensitif seperti:

```text
user dan permission
transaksi POS
cash drawer
payment method
inventory stock
purchase order
supplier invoice
accounts payable
journal entry
financial report
day closing
month closing
```

Tujuan utama BAB 7:

1. Menentukan standar autentikasi user.
2. Menentukan standar role dan permission.
3. Menentukan standar authorization di backend.
4. Menentukan standar permission-based UI di frontend.
5. Menentukan standar approval transaksi.
6. Menentukan standar audit trail.
7. Menentukan standar activity log.
8. Menentukan standar keamanan data sensitif.
9. Menentukan standar proteksi transaksi kritikal.
10. Menentukan standar monitoring aktivitas penting.

---

## 7.2 Prinsip Keamanan Sistem

Sistem harus mengikuti prinsip keamanan berikut:

```text
Least Privilege
Defense in Depth
Secure by Default
Audit Everything Important
No Direct Edit After Posted
Separation of Duties
Traceable Transaction
Protected Financial Data
```

Penjelasan:

| Prinsip                     | Keterangan                                                     |
| --------------------------- | -------------------------------------------------------------- |
| Least Privilege             | User hanya mendapat akses sesuai tugasnya                      |
| Defense in Depth            | Keamanan diterapkan di route, request, policy, service, dan UI |
| Secure by Default           | Default akses adalah tidak boleh, kecuali diberi permission    |
| Audit Everything Important  | Perubahan penting harus tercatat                               |
| No Direct Edit After Posted | Dokumen posted tidak boleh diedit langsung                     |
| Separation of Duties        | Pembuat, approver, dan poster dapat dipisah                    |
| Traceable Transaction       | Semua transaksi dapat dilacak ke user dan waktu                |
| Protected Financial Data    | Data jurnal, closing, dan report keuangan harus dibatasi       |

---

## 7.3 Authentication Standard

Authentication digunakan untuk memastikan user yang mengakses sistem adalah user yang valid.

Sistem menggunakan:

```text
Laravel Sanctum
Session / Token Authentication
Password Hashing
Optional Session Device Tracking
```

Endpoint auth utama:

```text
POST /auth/login
POST /auth/logout
GET  /auth/me
PUT  /auth/password
POST /auth/forgot-password
POST /auth/reset-password
GET  /auth/sessions
DELETE /auth/sessions/{id}
```

Aturan authentication:

```text
login wajib menggunakan email/username dan password
password wajib di-hash
token tidak boleh disimpan dalam plain text
logout harus mencabut token/session aktif
user inactive tidak boleh login
user locked tidak boleh login
failed login dapat dibatasi dengan rate limit
```

---

## 7.4 Password Security

Password harus memenuhi standar minimum.

Rekomendasi aturan password:

```text
minimal 8 karakter
mengandung huruf
mengandung angka
tidak sama dengan password lama
tidak boleh sama dengan username/email
```

Jika sistem membutuhkan keamanan lebih tinggi, gunakan:

```text
uppercase
lowercase
number
symbol
password expiration
password history
```

Tabel yang dapat digunakan:

```text
password_histories
```

Aturan:

```text
password tidak boleh disimpan plain text
reset password token harus memiliki expiry
reset password token hanya bisa dipakai satu kali
change password wajib meminta password lama
```

---

## 7.5 Session Device Standard

Session device digunakan untuk melacak perangkat login user.

Data yang dicatat:

```text
user_id
device_name
ip_address
user_agent
last_active_at
logged_out_at
is_current
```

Manfaat:

```text
user dapat melihat perangkat aktif
admin dapat memutus session mencurigakan
audit login lebih jelas
keamanan user meningkat
```

Aturan:

```text
user bisa logout dari session sendiri
admin/supervisor tertentu bisa revoke session user lain jika diberi permission
session expired harus otomatis tidak valid
```

---

## 7.6 Role Based Access Control / RBAC

Sistem menggunakan RBAC custom.

Struktur utama:

```text
users
roles
permissions
user_roles
role_permissions
```

Konsep:

```text
User memiliki satu atau lebih Role.
Role memiliki banyak Permission.
Permission menentukan aksi yang boleh dilakukan user.
```

Contoh role utama:

```text
Owner
Admin
Supervisor
Cashier
Warehouse
Accounting
```

---

## 7.7 Standar Role

### 7.7.1 Owner

Hak akses umum:

```text
semua laporan
semua konfigurasi
semua approval penting
financial report
user management
closing override jika diperlukan
```

Owner biasanya memiliki akses tertinggi.

---

### 7.7.2 Admin

Hak akses umum:

```text
master data
product
pricing
promotion
system operational
user tertentu jika diberi hak
```

Admin tidak harus otomatis bisa melihat semua financial report.

---

### 7.7.3 Supervisor

Hak akses umum:

```text
approval transaksi tertentu
void POS
return POS
monitoring shift
monitoring sales session
approval adjustment kecil
```

Supervisor berperan sebagai kontrol operasional.

---

### 7.7.4 Cashier

Hak akses umum:

```text
open sales session
create POS transaction
hold bill
resume bill
print receipt
view own session
close own session
```

Cashier tidak boleh:

```text
mengubah harga tanpa permission
void transaksi tanpa permission
melihat financial report
mengakses journal
mengubah stock adjustment
mengubah master product penting
```

---

### 7.7.5 Warehouse

Hak akses umum:

```text
inventory balance
inventory ledger
goods receipt
stock transfer
stock adjustment sesuai permission
stock opname
planogram
```

Warehouse tidak boleh mengakses financial report kecuali diberi permission khusus.

---

### 7.7.6 Accounting

Hak akses umum:

```text
chart of accounts
payment methods
supplier invoice
accounts payable
supplier payment
journal entries
general ledger
trial balance
financial report
fiscal period
```

Accounting tidak otomatis boleh melakukan void POS kecuali diberi permission.

---

## 7.8 Standar Permission

Format permission:

```text
{module}.{resource}.{action}
```

Contoh:

```text
pos.transaction.create
pos.transaction.view
pos.transaction.void
pos.return.create
pos.session.open
pos.session.close

inventory.balance.view
inventory.ledger.view
inventory.stock-transfer.create
inventory.stock-transfer.post
inventory.stock-adjustment.create
inventory.stock-adjustment.post
inventory.stock-opname.post

purchasing.purchase-order.create
purchasing.purchase-order.approve
purchasing.goods-receipt.post
purchasing.supplier-invoice.post
purchasing.supplier-payment.post

accounting.journal-entry.create
accounting.journal-entry.post
accounting.journal-entry.reverse
accounting.general-ledger.view
accounting.financial-report.view

reporting.sales.view
reporting.inventory.view
reporting.financial.view

system.user.manage
system.role.manage
system.permission.manage
system.setting.manage
```

Aturan permission:

```text
permission harus spesifik
hindari permission terlalu umum seperti manage-all
permission action harus jelas: view, create, update, delete, approve, reject, post, void, reverse, close
permission wajib dicek di backend
frontend hanya membantu menyembunyikan UI
```

---

## 7.9 Authorization Layer

Authorization harus diterapkan di beberapa lapisan.

```text
Route Middleware
Form Request authorize()
Policy
Action / Service business rule
Frontend Permission UI
```

Prioritas keamanan:

| Layer            | Fungsi                       |
| ---------------- | ---------------------------- |
| Route Middleware | Proteksi awal endpoint       |
| Form Request     | Validasi permission awal     |
| Policy           | Authorization berbasis model |
| Action/Service   | Business rule authorization  |
| Frontend UI      | Menyembunyikan tombol/action |

Contoh route:

```php
Route::middleware(['auth:sanctum', 'permission:pos.transaction.create'])
    ->post('/pos/transactions', [SalesTransactionController::class, 'store']);
```

Contoh Form Request:

```php
public function authorize(): bool
{
    return $this->user()->can('pos.transaction.create');
}
```

Contoh Policy:

```php
public function void(User $user, SalesTransaction $transaction): bool
{
    return $user->hasPermission('pos.transaction.void')
        && $transaction->status === 'POSTED';
}
```

---

## 7.10 Permission-based UI

Frontend wajib menyembunyikan action yang tidak dimiliki user.

Contoh:

```vue
<BaseButton v-if="can('product.create')">
  Tambah Produk
</BaseButton>

<BaseButton v-if="can('pos.transaction.void')">
  Void Transaksi
</BaseButton>
```

Aturan:

```text
jika user tidak punya permission, tombol disembunyikan
jika user akses URL langsung, backend tetap harus menolak
frontend permission bukan pengganti backend authorization
```

UI harus memperhatikan permission untuk:

```text
menu sidebar
button create
button edit
button delete
button approve
button post
button void
button reverse
button close period
export report
```

---

## 7.11 Approval Standard

Approval digunakan untuk transaksi yang membutuhkan persetujuan sebelum diposting atau dijalankan.

Tabel utama:

```text
approval_types
approval_levels
approval_requests
approval_histories
```

Contoh transaksi yang membutuhkan approval:

```text
price change request
purchase order
stock adjustment besar
stock opname variance besar
void POS transaksi besar
sales return besar
manual journal besar
supplier payment besar
discount override
cash difference closing
```

---

## 7.12 Approval Flow

Alur approval standar:

```text
Create Document
-> Submit for Approval
-> Create Approval Request
-> Approver Review
-> Approve / Reject
-> Create Approval History
-> Update Document Status
-> Allow Next Process
```

Contoh:

```text
Purchase Order DRAFT
-> Submit
-> PENDING
-> Approved by Supervisor
-> APPROVED
-> Goods Receipt allowed
```

Jika reject:

```text
Purchase Order DRAFT/PENDING
-> Rejected
-> REJECTED
-> Creator revises or cancels
```

---

## 7.13 Approval Level

Approval dapat dibuat berdasarkan level.

Contoh:

```text
Level 1: Supervisor
Level 2: Owner
```

Contoh rule:

| Kondisi                        | Approval                   |
| ------------------------------ | -------------------------- |
| Stock adjustment < Rp 500.000  | Supervisor                 |
| Stock adjustment >= Rp 500.000 | Owner                      |
| Purchase order < Rp 5.000.000  | Supervisor                 |
| Purchase order >= Rp 5.000.000 | Owner                      |
| Manual journal besar           | Accounting Manager / Owner |

Aturan:

```text
approval level harus configurable
approval tidak boleh dilakukan oleh user yang tidak memiliki permission
approval history tidak boleh dihapus
reject wajib memiliki reason
```

---

## 7.14 Separation of Duties

Untuk transaksi kritikal, pembuat dan approver sebaiknya tidak sama.

Contoh:

```text
Cashier membuat void request
Supervisor approve void

Warehouse membuat stock adjustment
Supervisor approve adjustment

Accounting membuat manual journal
Owner approve journal besar
```

Aturan:

```text
creator tidak boleh approve dokumen sendiri jika rule separation aktif
poster dapat berbeda dari creator
approver harus tercatat
posted_by harus tercatat
```

---

## 7.15 Audit Trail Standard

Audit trail mencatat perubahan data penting.

Tabel:

```text
audit_logs
```

Data minimal:

```text
id
user_id
module
action
table_name
record_id
old_values
new_values
ip_address
user_agent
created_at
```

Perubahan yang wajib diaudit:

```text
create/update/delete user
change role
change permission
change system setting
create/update/delete product penting
price change
promotion change
posting transaction
void transaction
sales return
stock adjustment
stock opname
supplier invoice post
supplier payment post
journal post
journal reverse
day closing
month closing
```

---

## 7.16 Audit Trail untuk Transaksi

Untuk transaksi, audit harus mencatat:

```text
document_no
document_type
status_before
status_after
user_id
action
reason jika ada
timestamp
```

Contoh audit void POS:

```json
{
    "module": "POS",
    "action": "VOID_TRANSACTION",
    "table_name": "sales_transactions",
    "record_id": 1001,
    "old_values": {
        "status": "POSTED"
    },
    "new_values": {
        "status": "VOID"
    },
    "reason": "Double input transaksi"
}
```

---

## 7.17 Activity Log Standard

Activity log mencatat aktivitas user yang penting tetapi tidak selalu mengubah data.

Tabel:

```text
activity_logs
```

Contoh aktivitas:

```text
login
logout
view financial report
export sales report
download inventory valuation
open POS session
close POS session
view customer detail
view supplier invoice
```

Perbedaan audit log dan activity log:

| Jenis         | Fokus                 |
| ------------- | --------------------- |
| Audit Log     | Perubahan data        |
| Activity Log  | Aktivitas user        |
| System Event  | Event internal sistem |
| Technical Log | Error/debug teknis    |

---

## 7.18 Data Sensitif

Data sensitif harus dilindungi.

Contoh data sensitif:

```text
password
token
reset password token
financial report
journal entry
cash drawer amount
supplier payment
customer phone/email
user permission
system setting
api key
secret key
```

Aturan:

```text
jangan tampilkan password/token di response
jangan simpan secret di frontend
jangan tampilkan stack trace di production
batasi akses financial report
batasi akses jurnal
batasi export data sensitif
```

---

## 7.19 Security untuk POS

POS memiliki risiko operasional tinggi.

Proteksi POS:

```text
cashier wajib login
cashier wajib memiliki session OPEN
cashier hanya boleh transaksi pada session sendiri
void butuh permission
return butuh permission
discount override butuh permission
price override butuh permission
cash difference closing harus tercatat
```

Aturan:

```text
cashier tidak boleh menghapus transaksi posted
cashier tidak boleh mengubah transaksi posted
void harus memiliki reason
return harus refer ke transaksi asli
receipt reprint harus tercatat jika diperlukan
```

---

## 7.20 Security untuk Inventory

Inventory memengaruhi nilai persediaan dan HPP.

Proteksi inventory:

```text
stock adjustment wajib permission
stock adjustment minus wajib reason
stock adjustment besar wajib approval
stock opname variance wajib approval jika besar
stock transfer harus valid source/destination
inventory ledger tidak boleh diedit manual
```

Aturan:

```text
Inventory Balance tidak boleh diubah langsung dari UI.
Semua koreksi stok harus lewat Adjustment atau Opname.
Inventory Ledger tidak boleh dihapus.
```

---

## 7.21 Security untuk Purchasing

Purchasing memengaruhi pembelian, hutang, dan stok.

Proteksi purchasing:

```text
PO approval wajib untuk nominal tertentu
goods receipt hanya boleh dari PO approved
supplier invoice harus cocok dengan goods receipt
supplier payment tidak boleh melebihi outstanding AP
supplier payment besar wajib approval
purchase return wajib reason
```

Aturan:

```text
PO posted/approved tidak boleh diedit sembarangan
GR posted tidak boleh diedit
supplier invoice posted tidak boleh diedit
supplier payment posted tidak boleh diedit
```

---

## 7.22 Security untuk Accounting

Accounting memengaruhi laporan keuangan.

Proteksi accounting:

```text
journal entry manual wajib permission
journal post wajib permission
journal reverse wajib permission
posted journal tidak boleh diedit
closed fiscal period tidak boleh menerima journal baru
financial report hanya untuk role tertentu
trial balance harus balance
```

Aturan:

```text
Journal posted harus immutable.
Correction menggunakan reversal journal.
Fiscal period closed harus terkunci.
Fiscal Period hanya boleh ditutup melalui proses Month Closing di modul Accounting.
Endpoint langsung untuk close fiscal period tidak boleh diekspos sebagai public API.
```

---

## 7.23 Security untuk Reporting

Reporting bersifat read-only tetapi tetap sensitif.

Proteksi reporting:

```text
sales report dibatasi permission
financial report dibatasi permission
export report dibatasi permission
view customer data dibatasi permission
view supplier performance dibatasi permission
```

Aturan:

```text
Reporting tidak boleh update data.
Reporting tidak boleh posting transaksi.
Export data sensitif harus tercatat di activity log.
```

---

## 7.24 Rate Limiting

Rate limit digunakan untuk mencegah abuse.

Endpoint yang wajib diberi rate limit:

```text
/auth/login
/auth/forgot-password
/auth/reset-password
reports export
webhook endpoint
```

Contoh:

| Endpoint        | Rate Limit             |
| --------------- | ---------------------- |
| Login           | Ketat                  |
| Forgot Password | Ketat                  |
| Reset Password  | Ketat                  |
| Export Report   | Sedang                 |
| Webhook         | Sedang sesuai provider |

---

## 7.25 Idempotency untuk Transaksi Kritikal

Idempotency digunakan untuk mencegah double submit.

Cocok untuk:

```text
POS transaction
supplier payment
journal posting
stock adjustment posting
day closing
month closing
```

Contoh header:

```text
Idempotency-Key: uuid-random
```

Aturan:

```text
jika request dengan idempotency key yang sama dikirim ulang, jangan membuat transaksi ganda
simpan key, endpoint, user_id, request_hash, response_reference
gunakan expiry untuk idempotency key
```

Untuk MVP, idempotency dapat diterapkan terlebih dahulu pada POS transaction dan payment.

---

## 7.26 Security Logging

Security logging mencatat kejadian mencurigakan.

Contoh:

```text
login gagal berulang
akses endpoint forbidden
percobaan void tanpa permission
percobaan akses financial report tanpa permission
perubahan role/permission
reset password
revoke session
```

Log ini dapat masuk ke:

```text
activity_logs
audit_logs
security_logs jika dibuat
```

Untuk MVP, `activity_logs` dan `audit_logs` sudah cukup.

---

## 7.27 Checklist Security per Fitur Baru

Setiap fitur baru wajib dicek:

```text
[ ] Endpoint memakai auth:sanctum jika protected
[ ] Permission sudah dibuat
[ ] Permission dicek di backend
[ ] Tombol/action dicek di frontend
[ ] Form Request memiliki authorize()
[ ] Policy dibuat jika berbasis model
[ ] Action kritikal mencatat created_by/posted_by
[ ] Audit log dibuat untuk perubahan penting
[ ] Activity log dibuat untuk aktivitas penting
[ ] Data sensitif tidak tampil di response
[ ] Dokumen POSTED tidak bisa diedit
[ ] Void/cancel/reject wajib reason
[ ] Approval dibuat jika transaksi berisiko
[ ] Export data sensitif dicatat
```

---

## 7.28 Kesimpulan BAB 7

Keputusan final BAB 7:

```text
Sistem menggunakan RBAC custom.
Permission menggunakan format {module}.{resource}.{action}.
Backend wajib menjadi sumber utama authorization.
Frontend permission hanya untuk UX.
Approval digunakan untuk transaksi berisiko.
Audit log wajib untuk perubahan data penting.
Activity log digunakan untuk aktivitas user penting.
Dokumen POSTED tidak boleh diedit langsung.
Void, cancel, reject, dan reversal wajib traceable.
Data sensitif harus dibatasi.
Financial report dan journal harus dilindungi permission ketat.
```

Dengan BAB 7 ini, sistem memiliki standar keamanan yang selaras dengan:

```text
BAB 1 — Arsitektur Sistem & Modul
BAB 2 — Business Flow
BAB 3 — Database & Domain Mapping
BAB 4 — API Contract
BAB 5 — Backend Laravel
BAB 6 — Frontend Vue 3 + Inertia
```

BAB ini menjadi dasar untuk implementasi RBAC, approval, audit trail, activity log, dan proteksi transaksi kritikal pada seluruh modul ERP POS.

# BAB 8 — Testing, QA & Acceptance Criteria

## 8.1 Tujuan BAB 8

BAB 8 menetapkan standar pengujian sistem ERP POS agar setiap modul dapat diuji secara konsisten sebelum masuk ke production.

Sistem ini menangani proses kritikal seperti:

```text
POS transaction
inventory movement
purchasing
supplier invoice
accounts payable
journal posting
day closing
month closing
financial report
```

Karena itu, testing tidak boleh hanya fokus pada apakah halaman bisa dibuka, tetapi harus memastikan proses bisnis berjalan benar.

Tujuan utama BAB 8:

1. Menentukan standar pengujian backend.
2. Menentukan standar pengujian frontend.
3. Menentukan standar pengujian API.
4. Menentukan standar scenario testing per modul.
5. Menentukan acceptance criteria setiap fitur.
6. Menentukan regression test untuk flow kritikal.
7. Menentukan QA checklist sebelum release.
8. Memastikan transaksi stok dan accounting selalu konsisten.

---

## 8.2 Prinsip Testing

Testing harus mengikuti prinsip berikut:

```text
Business Flow First
Critical Transaction Priority
Automated Where Possible
Manual QA for UX Critical Flow
Regression Safe
Traceable Test Case
Permission-aware Testing
Data Consistency Testing
```

Penjelasan:

| Prinsip                       | Keterangan                                               |
| ----------------------------- | -------------------------------------------------------- |
| Business Flow First           | Test harus mengikuti alur bisnis utama                   |
| Critical Transaction Priority | POS, inventory, accounting, closing wajib diprioritaskan |
| Automated Where Possible      | Logic penting sebaiknya diuji otomatis                   |
| Manual QA for UX              | Alur kasir dan dashboard tetap perlu QA manual           |
| Regression Safe               | Perubahan baru tidak boleh merusak flow lama             |
| Traceable Test Case           | Test case harus bisa ditelusuri ke modul/fitur           |
| Permission-aware Testing      | Role dan permission wajib diuji                          |
| Data Consistency Testing      | Stok, jurnal, dan saldo harus konsisten                  |

---

## 8.3 Jenis Testing

Jenis testing yang digunakan:

```text
Unit Test
Feature Test
API Test
Integration Test
Frontend Component Test
End-to-End Test
Regression Test
Manual QA
User Acceptance Test
```

---

## 8.4 Unit Test

Unit test digunakan untuk menguji logic kecil secara terisolasi.

Contoh target unit test:

```text
PriceResolverService
PromotionSimulationService
InventoryCostingService
JournalPostingService
DocumentNumberService
LoyaltyPointService
TaxCalculationService
```

Contoh test:

```text
harga produk sesuai price list
promo buy x get y dihitung benar
tax inclusive dihitung benar
journal debit dan credit balance
nomor dokumen unik
loyalty point dihitung sesuai konfigurasi
```

Aturan:

```text
Unit test tidak perlu akses UI.
Unit test fokus ke service/helper/domain logic.
Unit test harus cepat dijalankan.
```

---

## 8.5 Feature Test Backend

Feature test digunakan untuk menguji flow backend dari request sampai database.

Contoh:

```text
Create POS Transaction
Post Goods Receipt
Post Supplier Invoice
Post Stock Adjustment
Post Journal Entry
Day Closing
Month Closing
```

Contoh skenario POS:

```text
Given cashier memiliki session OPEN
And produk aktif memiliki stok cukup
When cashier membuat transaksi POS
Then sales transaction dibuat
And sales transaction item dibuat
And sales payment dibuat
And inventory ledger SALE_OUT dibuat
And inventory balance berkurang
And journal entry penjualan dibuat
And journal debit credit balance
```

---

## 8.6 API Test

API test memastikan endpoint sesuai kontrak BAB 4.

Yang diuji:

```text
HTTP status code
response format
validation error
business rule error
permission error
pagination
filter
sort
payload structure
```

Contoh API test:

```text
POST /api/v1/pos/transactions
GET  /api/v1/products
GET  /api/v1/reports/sales
POST /api/v1/accounting/journal-entries/{id}/post
```

Acceptance response sukses:

```json
{
    "success": true,
    "message": "Data retrieved successfully",
    "data": {}
}
```

Acceptance response error:

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {}
}
```

---

## 8.7 Frontend Test

Frontend test digunakan untuk memastikan komponen dan interaksi UI berjalan benar.

Prioritas frontend test:

```text
POS cart calculation
payment validation
permission-based button visibility
form validation display
table filter
modal confirmation
loading state
empty state
```

Contoh test POS frontend:

```text
cart subtotal dihitung benar
grand total berubah setelah diskon
payment kurang tidak bisa submit
payment pas bisa submit
tombol void disembunyikan jika user tidak punya permission
```

---

## 8.8 End-to-End Test

E2E test digunakan untuk menguji flow dari UI sampai backend.

Prioritas E2E:

```text
login
open shift
open sales session
create POS transaction
print receipt
close sales session
day closing
stock adjustment
purchase order to goods receipt
supplier invoice to payment
journal posting
financial report
```

Contoh E2E POS:

```text
Login sebagai cashier
Open shift
Open sales session
Scan produk
Tambah item ke cart
Pilih payment method
Submit payment
Receipt muncul
Stok berkurang
Sales report bertambah
```

---

## 8.9 Regression Test

Regression test wajib dilakukan sebelum release.

Flow yang wajib regression:

```text
Auth login/logout
POS sales
POS return
Void transaction
Inventory ledger
Stock balance
Purchasing flow
Supplier invoice
Supplier payment
Journal posting
Day closing
Month closing
Reporting
Permission check
```

Aturan:

```text
Setiap bug yang sudah diperbaiki harus dibuat test case.
Flow kritikal harus diuji ulang sebelum deploy production.
```

---

## 8.10 Scenario Testing per Modul

### Auth

```text
user aktif bisa login
user inactive tidak bisa login
password salah ditolak
logout mencabut session/token
forgot password mengirim token
reset password berhasil jika token valid
```

### System

```text
admin bisa membuat user
role bisa diberi permission
user tanpa permission tidak bisa akses endpoint
audit log tercatat saat role berubah
```

### Product

```text
produk bisa dibuat
variant bisa dibuat
barcode unik
produk inactive tidak bisa dijual
product account mapping valid
```

### Pricing

```text
price list aktif digunakan POS
customer category mendapat harga sesuai mapping
price change request butuh approval
price history tercatat
```

### Promotion

```text
promo aktif diterapkan
promo expired tidak diterapkan
promo limit tidak boleh terlampaui
promo simulation menghasilkan perhitungan benar
```

### POS

```text
cashier wajib punya session open
cart tidak boleh kosong
payment harus sama dengan grand total
stok berkurang setelah transaksi
journal terbentuk
void butuh permission
return harus refer ke transaksi asli
```

### Inventory

```text
goods receipt menambah stok
sales mengurangi stok
stock adjustment membuat ledger
stock transfer membuat transfer out dan transfer in
stock opname membuat variance
inventory balance cocok dengan ledger
```

### Purchasing

```text
PR bisa dibuat
PO harus approved sebelum GR
GR tidak boleh melebihi PO tanpa permission
supplier invoice membentuk AP
supplier payment mengurangi AP
payment tidak boleh melebihi outstanding
```

### Loyalty

```text
customer mendapat point dari transaksi
redeem mengurangi point
point tidak boleh minus
manual adjustment butuh reason
```

### Accounting

```text
journal debit credit harus balance
posted journal tidak bisa diedit
reverse journal membuat jurnal pembalik
closed fiscal period tidak bisa menerima journal
```

### Reporting

```text
report hanya read-only
filter tanggal bekerja
export report bisa dibuat
financial report membaca snapshot jika periode closed
```

---

## 8.11 Acceptance Criteria

Setiap fitur harus memiliki acceptance criteria.

Format:

```text
Given kondisi awal
When user melakukan aksi
Then hasil yang diharapkan terjadi
```

Contoh POS:

```text
Given cashier memiliki sales session OPEN
And produk memiliki stok 10
When cashier menjual produk qty 2
Then transaksi berhasil dibuat
And stok menjadi 8
And inventory ledger SALE_OUT dibuat
And jurnal penjualan dibuat
```

Contoh accounting:

```text
Given journal entry memiliki debit 100.000 dan credit 100.000
When user melakukan post journal
Then journal status menjadi POSTED
And general ledger terbentuk
```

---

## 8.12 QA Checklist Sebelum Release

Checklist:

```text
[ ] Semua migration berhasil dijalankan
[ ] Seeder awal berhasil dijalankan
[ ] Semua endpoint utama bisa diakses sesuai permission
[ ] POS transaction berhasil
[ ] Inventory ledger terbentuk
[ ] Journal entry terbentuk dan balance
[ ] Day closing berhasil
[ ] Month closing berhasil
[ ] Report utama tampil
[ ] Role cashier tidak bisa akses accounting
[ ] Role warehouse tidak bisa akses financial report
[ ] Error validation tampil benar
[ ] Business rule error tampil benar
[ ] Tidak ada stack trace di production
[ ] Backup database tersedia
```

---

## 8.13 Kesimpulan BAB 8

Keputusan final BAB 8:

```text
Testing harus mengikuti business flow.
Flow kritikal wajib diuji otomatis.
POS, inventory, purchasing, accounting, dan closing menjadi prioritas utama.
Acceptance criteria wajib dibuat untuk fitur penting.
Regression test wajib sebelum release.
QA tidak hanya menguji UI, tetapi juga konsistensi database.
```

---

# BAB 9 — Deployment, Environment & DevOps Standard

## 9.1 Tujuan BAB 9

BAB 9 menetapkan standar deployment, environment, server, queue, scheduler, backup, dan monitoring agar aplikasi aman dijalankan di production.

Tujuan utama:

1. Menentukan standar environment.
2. Menentukan standar file `.env`.
3. Menentukan standar deployment backend Laravel.
4. Menentukan standar deployment frontend Vue/Inertia.
5. Menentukan standar queue dan scheduler.
6. Menentukan standar Redis dan Reverb.
7. Menentukan standar backup database.
8. Menentukan standar rollback.
9. Menentukan standar monitoring production.

---

## 9.2 Environment Standard

Minimal environment:

```text
local
staging
production
```

Penjelasan:

| Environment | Fungsi                     |
| ----------- | -------------------------- |
| local       | Development developer      |
| staging     | Testing sebelum production |
| production  | Sistem live                |

Aturan:

```text
Production tidak boleh digunakan untuk testing sembarangan.
Staging harus semirip mungkin dengan production.
Data production tidak boleh dicopy ke local tanpa masking jika berisi data sensitif.
```

---

## 9.3 .env Standard

Konfigurasi penting:

```text
APP_NAME
APP_ENV
APP_KEY
APP_DEBUG
APP_URL

DB_CONNECTION
DB_HOST
DB_PORT
DB_DATABASE
DB_USERNAME
DB_PASSWORD

CACHE_STORE
QUEUE_CONNECTION
SESSION_DRIVER

REDIS_HOST
REDIS_PASSWORD
REDIS_PORT

BROADCAST_CONNECTION
REVERB_APP_ID
REVERB_APP_KEY
REVERB_APP_SECRET
REVERB_HOST
REVERB_PORT
REVERB_SCHEME

MAIL_MAILER
MAIL_HOST
MAIL_PORT
MAIL_USERNAME
MAIL_PASSWORD
MAIL_FROM_ADDRESS
```

Aturan production:

```text
APP_ENV=production
APP_DEBUG=false
APP_KEY wajib valid
database credential tidak boleh dibagikan
.env tidak boleh masuk git
```

---

## 9.4 Deployment Backend Laravel

Langkah deployment backend:

```text
pull latest code
install composer dependencies
set environment
run migration
run seeder jika diperlukan
optimize config
optimize route
optimize view
restart queue
restart scheduler jika diperlukan
restart reverb jika diperlukan
```

Command umum:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan queue:restart
```

Aturan:

```text
Migration production harus direview.
Jangan menjalankan fresh migration di production.
Seeder production hanya untuk data konfigurasi awal.
```

---

## 9.5 Deployment Frontend Vue + Inertia

Langkah build frontend:

```bash
npm install
npm run build
```

Aturan:

```text
Pastikan asset production sudah dibuild.
Jangan gunakan npm run dev di production.
Pastikan APP_URL benar.
Pastikan Vite env sesuai production.
```

Jika frontend dan backend satu repository Laravel, hasil build berada di:

```text
public/build
```

---

## 9.6 Queue Standard

Queue digunakan untuk proses async.

Contoh job:

```text
export report
generate snapshot
send notification
broadcast dashboard update
retry outbox
```

Queue driver production direkomendasikan:

```text
redis
```

Command worker:

```bash
php artisan queue:work --tries=3 --timeout=120
```

Aturan:

```text
queue worker harus dimonitor
failed job harus dicek
queue restart setelah deployment
job kritikal harus idempotent
```

---

## 9.7 Scheduler Standard

Laravel Scheduler digunakan untuk proses berkala.

Contoh schedule:

```text
expire loyalty point
generate daily summary
cleanup old sessions
send low stock notification
backup database
retry failed outbox
```

Cron server:

```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

Aturan:

```text
scheduler hanya aktif di satu server utama
hindari duplicate scheduler jika multi-server
schedule kritikal harus memiliki log
```

---

## 9.8 Laravel Reverb Standard

Reverb digunakan untuk realtime.

Digunakan untuk:

```text
notification
dashboard update
POS update
stock update
job completion notification
```

Aturan:

```text
gunakan private channel untuk data sensitif
jangan broadcast data rahasia
reverb process harus dimonitor
frontend harus fallback jika realtime tidak aktif
```

---

## 9.9 Database Backup Standard

Database wajib dibackup secara rutin.

Rekomendasi:

```text
daily backup
weekly backup
monthly archive
backup before major deployment
```

Minimal backup:

```text
database dump
storage penting
.env production secara aman
```

Aturan:

```text
backup harus diuji restore
backup harus disimpan di lokasi aman
backup tidak boleh hanya berada di server yang sama
akses backup harus dibatasi
```

Command contoh:

```bash
mysqldump -u user -p database_name > backup.sql
```

---

## 9.10 Storage Standard

File upload disimpan menggunakan Laravel Storage.

Folder contoh:

```text
products/
receipts/
reports/
exports/
attachments/
```

Aturan:

```text
jangan simpan file upload langsung tanpa validasi
gunakan symbolic link jika memakai public disk
file sensitif jangan diletakkan di public storage
```

Command:

```bash
php artisan storage:link
```

---

## 9.11 Log Standard

Log production harus dimonitor.

Jenis log:

```text
laravel.log
queue failed job
web server log
database error log
scheduler log
reverb log
```

Aturan:

```text
APP_DEBUG=false
jangan tampilkan stack trace ke user
log tidak boleh menyimpan password/token
log harus dirotate
```

---

## 9.12 Rollback Standard

Rollback diperlukan jika deployment gagal.

Strategi rollback:

```text
rollback code
rollback config
rollback migration jika aman
restore backup jika data rusak
disable fitur bermasalah
```

Aturan:

```text
sebelum deployment besar wajib backup
migration destructive harus dihindari
gunakan feature flag jika fitur berisiko
```

---

## 9.13 Production Checklist

Checklist sebelum go live:

```text
[ ] APP_ENV production
[ ] APP_DEBUG false
[ ] APP_KEY valid
[ ] Database production siap
[ ] Migration berhasil
[ ] Seeder konfigurasi awal berhasil
[ ] Storage link aktif
[ ] Queue worker aktif
[ ] Scheduler aktif
[ ] Reverb aktif jika digunakan
[ ] Backup berjalan
[ ] Log rotation aktif
[ ] HTTPS aktif
[ ] Role dan permission awal tersedia
[ ] Admin owner tersedia
[ ] Chart of accounts tersedia
[ ] Payment methods tersedia
[ ] Document sequences tersedia
```

---

## 9.14 Kesimpulan BAB 9

Keputusan final BAB 9:

```text
Production harus menggunakan APP_DEBUG=false.
Deployment harus melalui staging jika memungkinkan.
Queue, scheduler, Redis, dan Reverb harus dimonitor.
Backup database wajib berjalan rutin.
Rollback plan wajib tersedia sebelum deployment besar.
```

---

# BAB 10 — Migration, Refactor Roadmap & Implementation Phase

## 10.1 Tujuan BAB 10

BAB 10 menetapkan roadmap implementasi dan refactor agar pengembangan aplikasi dilakukan bertahap, terkontrol, dan tidak merusak flow yang sudah berjalan.

Tujuan utama:

1. Menentukan prioritas implementasi modul.
2. Menentukan fase MVP.
3. Menentukan urutan refactor backend.
4. Menentukan urutan refactor frontend.
5. Menentukan urutan migrasi database.
6. Menentukan risiko refactor.
7. Menentukan rollback plan.
8. Menentukan checklist setiap phase.

---

## 10.2 Prinsip Refactor

Prinsip refactor:

```text
Incremental
Backward Safe
Tested
Documented
Database-aware
Business Flow First
No Big Bang if Avoidable
```

Aturan:

```text
Jangan refactor semua modul sekaligus tanpa test.
Prioritaskan flow utama.
Setiap phase harus bisa diuji.
Setiap perubahan database harus jelas dampaknya.
```

---

## 10.3 Urutan Implementasi Modul

Rekomendasi urutan:

```text
Phase 1: Auth, System, MasterData
Phase 2: Product, Pricing
Phase 3: POS Basic
Phase 4: Inventory Ledger
Phase 5: Purchasing
Phase 6: Accounting Posting
Phase 7: Closing & Reporting
Phase 8: Promotion & Loyalty
Phase 9: Optimization & Realtime
```

---

## 10.4 Phase 1 — Auth, System, MasterData

Scope:

```text
login/logout
user
role
permission
system setting
business profile
supplier
customer
unit
tax
```

Target:

```text
user bisa login
role dan permission aktif
master data dasar tersedia
tax tersedia untuk POS dan purchasing
```

Checklist:

```text
[ ] Auth berjalan
[ ] RBAC berjalan
[ ] User management berjalan
[ ] Supplier CRUD berjalan
[ ] Customer CRUD berjalan
[ ] Unit CRUD berjalan
[ ] Tax CRUD berjalan
```

---

## 10.5 Phase 2 — Product & Pricing

Scope:

```text
product brand
product category
product
product variant
barcode
product image
price list
price history
price change request
```

Target:

```text
produk bisa dijual
harga bisa ditentukan
barcode bisa digunakan
```

Checklist:

```text
[ ] Product CRUD
[ ] Variant CRUD
[ ] Barcode unique
[ ] Price list aktif
[ ] Price resolver berjalan
[ ] Product inactive tidak bisa dijual
```

---

## 10.6 Phase 3 — POS Basic

Scope:

```text
shift
sales session
sales transaction
sales items
sales payments
receipt
hold bill
void basic
sales return basic
```

Target:

```text
kasir bisa transaksi
payment tercatat
receipt tersedia
session bisa dibuka dan ditutup
```

Checklist:

```text
[ ] Open shift
[ ] Open session
[ ] Create transaction
[ ] Payment method bekerja
[ ] Hold bill
[ ] Print receipt
[ ] Close session
```

---

## 10.7 Phase 4 — Inventory Ledger

Scope:

```text
inventory location
inventory batch
inventory ledger
inventory balance
stock transfer
stock adjustment
stock opname
stock card
```

Target:

```text
stok berubah melalui ledger
inventory balance bisa dihitung
stock card tampil
```

Checklist:

```text
[ ] Goods in menambah stok
[ ] Sale out mengurangi stok
[ ] Adjustment membuat ledger
[ ] Transfer membuat ledger keluar dan masuk
[ ] Stock card sesuai ledger
```

---

## 10.8 Phase 5 — Purchasing

Scope:

```text
purchase request
purchase order
goods receipt
supplier invoice
accounts payable
supplier payment
purchase return
```

Target:

```text
pembelian masuk ke stok
supplier invoice membentuk hutang
payment mengurangi hutang
```

Checklist:

```text
[ ] PR dibuat
[ ] PO approved
[ ] GR posted menambah stok
[ ] Supplier invoice posted membentuk AP
[ ] Supplier payment posted mengurangi AP
```

---

## 10.9 Phase 6 — Accounting Posting

Scope:

```text
chart of accounts
payment methods
journal entry
journal lines
accounting rules
journal templates
posting events
general ledger
trial balance
```

Target:

```text
setiap transaksi penting membentuk journal
journal balance
general ledger tersedia
```

Checklist:

```text
[ ] COA tersedia
[ ] Payment method punya account_id
[ ] POS membuat journal
[ ] Supplier invoice membuat journal
[ ] Supplier payment membuat journal
[ ] Stock adjustment membuat journal jika berdampak nilai
[ ] Trial balance balance
```

---

## 10.10 Phase 7 — Closing & Reporting

Scope:

```text
day closing
month closing
sales report
inventory report
purchase report
AP report
financial report
dashboard
```

Target:

```text
periode bisa ditutup
laporan bisa dibaca
periode closed terkunci
```

Checklist:

```text
[ ] Day closing validasi session
[ ] Month closing validasi journal dan inventory
[ ] Report sales tampil
[ ] Report inventory tampil
[ ] Financial report tampil
[ ] Closed period tidak bisa diubah
```

---

## 10.11 Phase 8 — Promotion & Loyalty

Scope:

```text
promotion
promotion condition
promotion reward
promotion usage
loyalty account
loyalty transaction
membership tier
reward redemption
```

Target:

```text
promo bisa diterapkan
loyalty point bisa earned dan redeemed
```

Checklist:

```text
[ ] Promo simulation
[ ] Promo applied ke POS
[ ] Point earned
[ ] Point redeemed
[ ] Loyalty balance sesuai transaction
```

---

## 10.12 Phase 9 — Optimization & Realtime

Scope:

```text
index optimization
query optimization
Redis cache
queue
realtime notification
dashboard realtime
export async
```

Target:

```text
sistem lebih cepat
dashboard realtime
report besar tidak membebani request utama
```

Checklist:

```text
[ ] Slow query dicek
[ ] Index ditambahkan
[ ] Queue worker stabil
[ ] Realtime notification berjalan
[ ] Export report async
```

---

## 10.13 Risiko Refactor

Risiko utama:

```text
database tidak sinkron dengan model
business flow berubah tanpa test
permission tidak lengkap
journal tidak balance
stok tidak sesuai ledger
report berbeda dari transaksi asli
frontend masih memakai endpoint lama
```

Mitigasi:

```text
gunakan test
buat migration bertahap
gunakan feature flag jika perlu
review database sebelum deploy
backup sebelum perubahan besar
```

---

## 10.14 Kesimpulan BAB 10

Keputusan final BAB 10:

```text
Implementasi dilakukan bertahap.
Auth, System, MasterData menjadi fondasi awal.
POS dan Inventory menjadi prioritas operasional.
Accounting dan Closing menjadi prioritas integritas data.
Promotion dan Loyalty dapat masuk setelah flow utama stabil.
Setiap phase harus memiliki checklist dan test.
```

---

# BAB 11 — Seeder, Initial Data & Configuration Standard

## 11.1 Tujuan BAB 11

BAB 11 menetapkan standar data awal yang harus tersedia setelah aplikasi diinstall agar sistem bisa langsung digunakan.

Data awal mencakup:

```text
roles
permissions
users
chart of accounts
payment methods
taxes
document types
document sequences
system settings
warehouse locations
approval rules
```

---

## 11.2 Prinsip Seeder

Seeder harus mengikuti prinsip:

```text
Repeatable
Safe
Configurable
Environment-aware
No Duplicate
Production Safe
```

Aturan:

```text
Seeder tidak boleh membuat data duplikat.
Seeder production harus aman dijalankan ulang.
Seeder tidak boleh menghapus data production sembarangan.
```

Gunakan pattern:

```text
updateOrCreate
firstOrCreate
```

---

## 11.3 Default Roles

Role awal:

```text
Owner
Admin
Supervisor
Cashier
Warehouse
Accounting
```

Penjelasan:

| Role       | Fungsi                        |
| ---------- | ----------------------------- |
| Owner      | Akses tertinggi dan laporan   |
| Admin      | Konfigurasi operasional       |
| Supervisor | Approval operasional          |
| Cashier    | Transaksi POS                 |
| Warehouse  | Inventory dan goods receipt   |
| Accounting | Journal, AP, financial report |

---

## 11.4 Default Permissions

Permission dibuat berdasarkan format:

```text
{module}.{resource}.{action}
```

Contoh:

```text
system.user.manage
system.role.manage
master-data.customer.view
master-data.customer.create
product.product.view
product.product.create
pricing.price-list.manage
promotion.promotion.manage
pos.transaction.create
pos.transaction.void
inventory.ledger.view
inventory.stock-adjustment.post
purchasing.purchase-order.approve
accounting.journal-entry.post
reporting.financial.view
```

Aturan:

```text
permission harus lengkap sebelum role digunakan
role tidak boleh diberi akses berlebihan tanpa alasan
Owner boleh memiliki semua permission
```

---

## 11.5 Default Admin User

Seeder dapat membuat user awal:

```text
Owner / Super Admin
```

Data minimal:

```text
name
email
password
role
is_active
```

Aturan:

```text
password default wajib diganti setelah login pertama
jangan gunakan password default lemah di production
email admin production harus valid
```

---

## 11.6 Default Chart of Accounts

COA awal minimal:

```text
Cash
Bank
Accounts Receivable
Inventory
Input Tax
Output Tax
Accounts Payable
Sales Revenue
Sales Return
Discount Given
Cost of Goods Sold
Inventory Adjustment Gain
Inventory Adjustment Loss
Operating Expense
Retained Earnings
```

Aturan:

```text
COA harus memiliki kode akun
COA harus memiliki tipe akun
COA tidak boleh dihapus jika sudah dipakai journal
```

Contoh tipe akun:

```text
ASSET
LIABILITY
EQUITY
REVENUE
EXPENSE
```

---

## 11.7 Default Payment Methods

Payment method awal:

```text
Cash
Bank Transfer
Debit Card
Credit Card
QRIS
E-Wallet
Loyalty Point
Store Credit
```

Field penting:

```text
name
code
type
account_id
is_active
```

Aturan:

```text
setiap payment method wajib memiliki account_id jika berdampak accounting
LOYALTY_POINT digunakan untuk redeem point
payment method inactive tidak boleh dipakai transaksi baru
```

---

## 11.8 Default Tax

Karena Tax masuk MVP, seed awal:

```text
PPN 11%
Non Tax
```

Field:

```text
tax_code
tax_name
tax_rate
is_inclusive
account_id
is_active
```

Aturan:

```text
tax rate harus jelas
tax inclusive/exclusive harus konsisten
tax account mengarah ke COA terkait
```

---

## 11.9 Default Document Types & Sequences

Document type awal:

```text
SALES_TRANSACTION
SALES_RETURN
PURCHASE_REQUEST
PURCHASE_ORDER
GOODS_RECEIPT
SUPPLIER_INVOICE
SUPPLIER_PAYMENT
STOCK_TRANSFER
STOCK_ADJUSTMENT
STOCK_OPNAME
JOURNAL_ENTRY
DAY_CLOSING
MONTH_CLOSING
```

Contoh prefix:

```text
POS
SR
PR
PO
GR
SI
SP
ST
SA
SO
JE
DC
MC
```

Aturan:

```text
nomor dokumen harus unik
sequence harus atomic
format nomor dokumen harus konsisten
```

---

## 11.10 Default Warehouse Location

Lokasi awal:

```text
Main Warehouse
Display Area
Return Area
Damage Area
```

Field penting:

```text
location_code
location_name
location_type
is_stock_bearing
is_active
```

Aturan:

```text
hanya stock-bearing location yang memengaruhi stok
display area boleh stock-bearing jika stok display ingin dihitung
damage area digunakan untuk barang rusak
```

---

## 11.11 Default System Settings

Setting awal:

```text
base_currency = IDR
timezone = Asia/Jakarta
tax_enabled = true
loyalty_enabled = false/true sesuai kebutuhan
negative_stock_allowed = false
approval_enabled = true
auto_post_journal = true
```

Aturan:

```text
setting penting harus terdokumentasi
perubahan setting penting harus diaudit
```

---

## 11.12 Default Approval Rules

Approval awal:

```text
void transaksi besar
stock adjustment besar
purchase order besar
manual journal besar
supplier payment besar
cash difference closing
```

Contoh:

| Rule                          | Approver   |
| ----------------------------- | ---------- |
| Void POS > Rp 500.000         | Supervisor |
| Stock Adjustment > Rp 500.000 | Owner      |
| PO > Rp 5.000.000             | Owner      |
| Manual Journal > Rp 5.000.000 | Owner      |
| Cash Difference               | Supervisor |

---

## 11.13 Seeder Checklist

Checklist:

```text
[ ] Roles tersedia
[ ] Permissions tersedia
[ ] Role permission mapping tersedia
[ ] Owner/Admin user tersedia
[ ] COA tersedia
[ ] Payment methods tersedia
[ ] Taxes tersedia
[ ] Document types tersedia
[ ] Document sequences tersedia
[ ] Warehouse locations tersedia
[ ] System settings tersedia
[ ] Approval rules tersedia
```

---

## 11.14 Kesimpulan BAB 11

Keputusan final BAB 11:

```text
Seeder digunakan untuk menyiapkan data awal sistem.
Seeder harus aman dijalankan ulang.
Role, permission, COA, payment method, tax, sequence, dan setting wajib tersedia.
Data awal harus mendukung POS, Inventory, Purchasing, Accounting, dan Reporting.
```

---

# BAB 12 — Operational SOP

## 12.1 Tujuan BAB 12

BAB 12 menjelaskan standar operasional penggunaan sistem oleh user, supervisor, warehouse, accounting, dan owner.

BAB ini bukan fokus coding, tetapi menjadi acuan operasional harian.

Tujuan utama:

1. Menentukan SOP buka shift.
2. Menentukan SOP transaksi kasir.
3. Menentukan SOP tutup shift.
4. Menentukan SOP day closing.
5. Menentukan SOP pembelian.
6. Menentukan SOP inventory.
7. Menentukan SOP accounting.
8. Menentukan SOP reporting.
9. Menentukan SOP backup.
10. Menentukan SOP penanganan error operasional.

---

## 12.2 SOP Buka Shift

Aktor:

```text
Cashier
Supervisor jika diperlukan
```

Langkah:

```text
1. Cashier login.
2. Cashier memilih menu POS.
3. Cashier membuka shift.
4. Cashier mengisi opening cash jika diperlukan.
5. Sistem membuat shift/session.
6. POS siap digunakan.
```

Validasi:

```text
cashier hanya boleh memiliki satu session open
cashier inactive tidak boleh buka session
opening cash harus angka valid
```

---

## 12.3 SOP Transaksi POS

Aktor:

```text
Cashier
```

Langkah:

```text
1. Cashier scan/pilih produk.
2. Sistem menambahkan produk ke cart.
3. Cashier mengatur qty jika perlu.
4. Sistem menghitung subtotal, diskon, tax, dan grand total.
5. Cashier memilih customer jika diperlukan.
6. Cashier memilih payment method.
7. Cashier submit payment.
8. Sistem membuat transaksi.
9. Sistem mengurangi stok.
10. Sistem membuat journal.
11. Sistem mencetak receipt.
```

Validasi:

```text
cart tidak boleh kosong
stok harus cukup
payment harus sama dengan grand total
session harus open
produk inactive tidak boleh dijual
```

---

## 12.4 SOP Hold Bill

Aktor:

```text
Cashier
```

Langkah:

```text
1. Cashier membuat cart.
2. Cashier memilih Hold Bill.
3. Sistem menyimpan cart sementara.
4. Cashier dapat membuat transaksi lain.
5. Cashier dapat resume bill saat customer siap bayar.
```

Aturan:

```text
hold bill belum mengurangi stok
hold bill belum membuat journal
hold bill belum melakukan stock reservation pada MVP
hold bill dapat expired sesuai setting
stok final dicek ulang saat transaksi dibayar/posting
```

---

## 12.5 SOP Void Transaksi

Aktor:

```text
Cashier
Supervisor
```

Langkah:

```text
1. Cashier memilih transaksi.
2. Cashier mengajukan void.
3. Sistem meminta reason.
4. Jika butuh approval, supervisor approve.
5. Sistem membuat reversal sesuai aturan.
6. Status transaksi menjadi VOID.
```

Aturan:

```text
void wajib reason
void wajib permission
void transaksi besar wajib approval
void harus tercatat audit
```

---

## 12.6 SOP Sales Return

Aktor:

```text
Cashier
Supervisor jika diperlukan
```

Langkah:

```text
1. User mencari transaksi asli.
2. User memilih item yang direturn.
3. User mengisi qty return dan reason.
4. Sistem validasi qty return.
5. Sistem membuat sales return.
6. Sistem menambah stok jika barang kembali layak jual.
7. Sistem membuat journal reversal/return.
8. Refund/store credit dicatat sesuai payment method.
```

Aturan:

```text
return harus refer transaksi asli
qty return tidak boleh melebihi qty jual
return wajib reason
return besar dapat membutuhkan approval
```

---

## 12.7 SOP Tutup Sales Session

Aktor:

```text
Cashier
Supervisor
```

Langkah:

```text
1. Cashier memilih close session.
2. Sistem menghitung total sales.
3. Sistem menghitung expected cash.
4. Cashier menginput actual cash.
5. Sistem menghitung selisih.
6. Jika selisih melewati batas, butuh approval supervisor.
7. Session ditutup.
```

Aturan:

```text
session tidak bisa ditutup jika ada transaksi pending
cash difference harus tercatat
closed session tidak bisa membuat transaksi baru
```

---

## 12.8 SOP Day Closing

Aktor:

```text
Supervisor
Owner jika diperlukan
```

Langkah:

```text
1. Supervisor memilih tanggal closing.
2. Sistem memvalidasi semua sales session sudah closed.
3. Sistem memastikan tidak ada transaksi pending.
4. Sistem menghitung total sales, refund, void, cash, non-cash.
5. Supervisor review summary.
6. Supervisor submit day closing.
7. Sistem mengunci transaksi pada tanggal tersebut.
```

Aturan:

```text
semua session harus closed
tidak boleh ada transaksi pending
tanggal yang sudah closed tidak boleh diubah sembarangan
```

---

## 12.9 SOP Month Closing

Aktor:

```text
Accounting
Owner
```

Langkah:

```text
1. Accounting memilih periode.
2. Sistem validasi semua day closing selesai.
3. Sistem validasi journal posted.
4. Sistem validasi trial balance balance.
5. Sistem validasi inventory valuation.
6. Sistem membuat financial snapshot.
7. Owner/Accounting melakukan closing.
8. Fiscal period menjadi closed.
```

Aturan:

```text
periode closed tidak boleh menerima transaksi baru
journal baru tidak boleh diposting ke periode closed
laporan periode closed menggunakan snapshot
```

---

## 12.10 SOP Purchase Order

Aktor:

```text
Purchasing/Admin
Supervisor/Owner
```

Langkah:

```text
1. User membuat purchase request jika diperlukan.
2. User membuat purchase order.
3. Sistem menghitung total PO.
4. Jika butuh approval, approver review.
5. PO approved.
6. PO siap diproses goods receipt.
```

Aturan:

```text
PO pending tidak bisa digunakan GR
PO rejected harus direvisi atau dibatalkan
PO approved tidak boleh diedit sembarangan
```

---

## 12.11 SOP Goods Receipt

Aktor:

```text
Warehouse
Purchasing
```

Langkah:

```text
1. Warehouse memilih PO approved.
2. Warehouse menginput barang diterima.
3. Sistem validasi qty.
4. Warehouse post goods receipt.
5. Sistem menambah stok.
6. Sistem membuat inventory ledger PURCHASE_IN.
7. PO status diperbarui menjadi partial received/received.
```

Aturan:

```text
GR harus berdasarkan PO approved
qty received tidak boleh melebihi PO tanpa permission
GR posted tidak boleh diedit
```

---

## 12.12 SOP Supplier Invoice

Aktor:

```text
Accounting
Purchasing
```

Langkah:

```text
1. Accounting menerima invoice supplier.
2. Accounting memilih goods receipt terkait.
3. Accounting input invoice number, tanggal, tax, total.
4. Sistem validasi invoice dengan GR.
5. Accounting post supplier invoice.
6. Sistem membuat accounts payable.
7. Sistem membuat journal.
```

Aturan:

```text
supplier invoice harus refer GR
invoice posted tidak boleh diedit
invoice membentuk AP
```

---

## 12.13 SOP Supplier Payment

Aktor:

```text
Accounting
Owner jika approval diperlukan
```

Langkah:

```text
1. Accounting memilih AP outstanding.
2. Accounting menginput nominal payment.
3. Accounting memilih payment method.
4. Jika nominal besar, butuh approval.
5. Accounting post payment.
6. Sistem mengurangi AP.
7. Sistem membuat journal payment.
```

Aturan:

```text
payment tidak boleh melebihi outstanding AP
payment posted tidak boleh diedit
payment besar dapat butuh approval
```

---

## 12.14 SOP Stock Adjustment

Aktor:

```text
Warehouse
Supervisor
```

Langkah:

```text
1. Warehouse membuat stock adjustment.
2. Warehouse memilih produk, lokasi, qty adjustment, reason.
3. Jika adjustment besar, butuh approval.
4. Adjustment diposting.
5. Sistem membuat inventory ledger.
6. Sistem update inventory balance.
7. Jika berdampak nilai, sistem membuat journal.
```

Aturan:

```text
adjustment wajib reason
adjustment besar wajib approval
inventory balance tidak boleh diedit langsung
```

---

## 12.15 SOP Stock Opname

Aktor:

```text
Warehouse
Supervisor
```

Langkah:

```text
1. Warehouse membuat stock opname.
2. Sistem mengambil snapshot stok sistem.
3. Warehouse menginput hasil hitung fisik.
4. Sistem menghitung variance.
5. Supervisor review variance.
6. Opname diposting.
7. Sistem membuat ledger variance.
```

Aturan:

```text
opname posted tidak boleh diedit
variance besar wajib approval
opname harus memiliki tanggal dan lokasi jelas
```

---

## 12.16 SOP Manual Journal

Aktor:

```text
Accounting
Owner jika approval diperlukan
```

Langkah:

```text
1. Accounting membuat journal entry.
2. Accounting mengisi journal lines.
3. Sistem validasi debit = credit.
4. Jika nominal besar, butuh approval.
5. Journal diposting.
6. General ledger diperbarui.
```

Aturan:

```text
journal tidak balance tidak boleh posted
posted journal tidak boleh diedit
koreksi menggunakan reversal
```

---

## 12.17 SOP Reporting

Aktor:

```text
Owner
Admin
Supervisor
Accounting
Warehouse sesuai permission
```

Langkah:

```text
1. User memilih report.
2. User memilih filter.
3. Sistem menampilkan summary.
4. User dapat export jika punya permission.
5. Export data sensitif dicatat activity log.
```

Aturan:

```text
reporting read-only
financial report hanya untuk role tertentu
export harus dibatasi permission
```

---

## 12.18 SOP Backup Operasional

Aktor:

```text
Admin Server
Owner
```

Langkah:

```text
1. Backup database dilakukan rutin.
2. Backup disimpan di lokasi aman.
3. Backup diuji restore berkala.
4. Backup dilakukan sebelum deployment besar.
```

Aturan:

```text
backup tidak boleh hanya di satu server
akses backup harus dibatasi
restore test wajib dilakukan berkala
```

---

## 12.19 SOP Penanganan Error Operasional

Jika terjadi error:

```text
1. Catat waktu kejadian.
2. Catat user yang mengalami error.
3. Catat transaksi/dokumen terkait.
4. Screenshot jika perlu.
5. Cek activity log dan audit log.
6. Cek laravel log.
7. Jangan edit database manual tanpa prosedur.
8. Jika data transaksi salah, gunakan void/reversal/adjustment.
```

Aturan:

```text
hindari update manual langsung ke database production
semua koreksi harus traceable
backup sebelum koreksi data besar
```

---

## 12.20 Kesimpulan BAB 12

Keputusan final BAB 12:

```text
Operasional sistem harus mengikuti SOP.
POS, inventory, purchasing, accounting, closing, dan reporting memiliki prosedur jelas.
Dokumen posted tidak boleh diedit langsung.
Koreksi menggunakan void, return, reversal, adjustment, atau opname.
Backup dan restore menjadi bagian dari operasional wajib.
```

BAB 12 menjadi acuan penggunaan sistem sehari-hari oleh cashier, warehouse, accounting, supervisor, admin, dan owner.

---

# Lampiran — Consistency Decision Register

|  No | Area          | Keputusan Final                                                                                   |
| --: | ------------- | ------------------------------------------------------------------------------------------------- |
|   1 | Stock Card    | Endpoint canonical hanya `GET /inventory/stock-card`. Tidak ada `/reports/stock-card`.            |
|   2 | Audit Trail   | BAB 7 adalah source-of-truth audit/activity/security log. BAB 5 hanya implementasi teknis.        |
|   3 | Diskon POS    | `sales_discounts` adalah snapshot diskon final transaksi.                                         |
|   4 | Promotion     | `promotion_applications` adalah log hasil engine promo dan efektivitas promo.                     |
|   5 | Reservation   | `RESERVATION` dan `RELEASE_RESERVATION` tidak aktif pada MVP.                                     |
|   6 | Hold Bill     | Hold Bill tidak mengurangi stok, tidak membuat journal, dan tidak reserve stok pada MVP.          |
|   7 | Approval      | `/approvals` hanya inbox/read-only. Approve/reject dilakukan via endpoint resource masing-masing. |
|   8 | Day Closing   | Owner = POS. Endpoint = `/pos/day-closings`.                                                      |
|   9 | Month Closing | Owner = Accounting. Endpoint = `/accounting/month-closings`.                                      |
|  10 | Fiscal Period | Fiscal Period hanya boleh closed melalui Month Closing, bukan endpoint close langsung.            |
|  11 | Permission    | Policy permission source-of-truth = BAB 7. Implementasi Policy = BAB 5.                           |
