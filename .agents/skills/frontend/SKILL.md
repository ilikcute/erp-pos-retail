# FRONTEND.md

# Frontend Design System & UI/UX Guideline  
## Mini ERP POS, Inventory, Purchasing, Accounting & Reporting

**Versi Dokumen:** 1.0  
**Status:** Draft Final Frontend Guideline  
**Target UI:** Modern, premium, responsif, mudah dioperasikan, tidak terlihat generic/template AI  
**Inspirasi Visual:** Apple-style clarity, whitespace, focus, hierarchy, calm interaction  
**Stack Frontend:** Vue 3, Composition API, Inertia, PWA, Pinia, Vite, TailwindCSS, Headless UI, Dexie.js, Laravel Reverb, Laravel Echo, Capacitor

---

# 1. Tujuan Dokumen

Dokumen ini menjadi acuan desain dan implementasi frontend untuk sistem:

```text
POS
Inventory
Purchasing
Planogram
Loyalty
Accounting
Reporting
System
```

Target utama UI:

```text
modern
bersih
premium
responsif
cepat dipakai
mudah dipahami
tidak ramai
tidak terlihat seperti template AI
nyaman untuk kasir, admin, warehouse, accounting, dan owner
```

Frontend tidak hanya harus menarik, tetapi juga harus operasional.

Artinya:

```text
kasir harus bisa transaksi cepat
warehouse harus mudah cek stok
accounting harus mudah cek jurnal
owner harus mudah membaca dashboard
supervisor harus mudah approve
admin harus mudah setup master data
```

---

# 2. Design Direction

## 2.1 Prinsip Visual

UI mengambil inspirasi dari pendekatan visual Apple:

```text
minimal
clear
spacious
high contrast where needed
large typography for focus
soft surface
precise spacing
subtle animation
content-first
```

Namun sistem ini bukan website marketing. Sistem ini adalah aplikasi operasional POS/ERP.

Maka penerapannya harus seimbang:

```text
Apple-like visual clarity
+
ERP/POS operational speed
```

Final direction:

```text
Premium Operational Dashboard
```

Bukan:

```text
landing page terlalu artistik
dashboard terlalu ramai
UI seperti template admin murahan
UI terlalu gelap tanpa alasan
UI terlalu banyak gradient
UI terlalu banyak animasi
```

---

## 2.2 Karakter UI yang Diinginkan

| Aspek | Arah Desain |
|---|---|
| Visual | Clean, calm, premium |
| Layout | Spacious, structured, jelas |
| Typography | Besar untuk headline, readable untuk data |
| Warna | Netral, soft, aksen terkontrol |
| Komponen | Rounded, subtle shadow, high quality |
| Animasi | Halus, pendek, tidak mengganggu |
| Data Table | Rapi, mudah scan, tidak padat berlebihan |
| POS Screen | Cepat, touch-friendly, fokus transaksi |
| Mobile | Ringkas, bottom action, mudah tap |
| Offline | Status jelas, sync queue terlihat |

---

# 3. Anti-Pattern: Hal yang Harus Dihindari

Agar UI tidak terlihat seperti template AI/generic, hindari:

```text
gradient berlebihan
glassmorphism berlebihan
shadow terlalu tebal
card terlalu banyak
warna terlalu ramai
ikon tidak konsisten
font terlalu kecil
spacing tidak seragam
table terlalu padat
modal terlalu banyak layer
button terlalu banyak style
empty state terlalu generik
copywriting seperti template AI
```

Contoh copywriting yang dihindari:

```text
"Welcome to your amazing dashboard"
"Manage everything seamlessly"
"Unlock your business potential"
```

Gunakan copywriting operasional:

```text
"Penjualan Hari Ini"
"Stok Perlu Restock"
"3 transaksi menunggu approval"
"Shift kasir masih terbuka"
"Sinkronisasi offline tertunda"
```

---

# 4. Brand Personality UI

UI harus terasa:

```text
tenang
percaya diri
profesional
rapi
modern
premium
cepat
akurat
```

Bukan:

```text
ramai
main-main
terlalu futuristik
terlalu gelap
terlalu banyak efek
terlalu dekoratif
```

Tone visual:

```text
Retail operation meets premium product experience.
```

---

# 5. Design Token

## 5.1 Color Palette

Gunakan warna netral sebagai dasar.

### Neutral

```text
background-main       #F5F5F7
background-surface    #FFFFFF
background-subtle     #FAFAFA
border-soft           #E5E5EA
border-strong         #D1D1D6
text-primary          #1D1D1F
text-secondary        #6E6E73
text-muted            #8E8E93
```

### Primary Accent

```text
primary               #0071E3
primary-hover         #0066CC
primary-soft          #E8F2FF
primary-border        #B9DCFF
```

### Semantic

```text
success               #34C759
success-soft          #EAF8EF

warning               #FF9500
warning-soft          #FFF4E5

danger                #FF3B30
danger-soft           #FFECEC

info                  #5AC8FA
info-soft             #EAF8FF
```

### Accounting Colors

Untuk debit/credit, jangan terlalu mencolok.

```text
debit                 #1D1D1F
credit                #1D1D1F
balance-ok            #34C759
balance-warning       #FF9500
balance-error         #FF3B30
```

Aturan:

```text
Gunakan warna hanya untuk status, prioritas, dan action.
Jangan membuat setiap card berbeda warna.
Primary blue hanya untuk aksi utama.
Danger hanya untuk destructive action.
```

---

## 5.2 Dark Mode

Dark mode boleh disediakan, tetapi bukan prioritas awal.

Dark mode harus:

```text
tidak full black
tetap readable
tidak mengorbankan table dan POS speed
```

Token dark mode:

```text
background-main       #111113
background-surface    #1C1C1E
background-subtle     #2C2C2E
border-soft           #3A3A3C
text-primary          #F5F5F7
text-secondary        #AEAEB2
```

Untuk MVP, fokus light mode dulu.

---

## 5.3 Typography

Gunakan system font stack agar terasa native dan ringan.

```css
font-family:
  ui-sans-serif,
  system-ui,
  -apple-system,
  BlinkMacSystemFont,
  "Segoe UI",
  sans-serif;
```

Ukuran:

| Token | Size | Use Case |
|---|---:|---|
| Display | 40–56px | Landing/internal hero/dashboard greeting |
| Page Title | 28–36px | Judul halaman |
| Section Title | 20–24px | Judul section |
| Card Title | 16–18px | Judul card |
| Body | 14–16px | Text umum |
| Table | 13–14px | Data table |
| Caption | 12px | Metadata/status kecil |

Aturan:

```text
Jangan gunakan terlalu banyak font weight.
Gunakan 600/700 untuk heading.
Gunakan 400/500 untuk body.
Table boleh 13px tetapi line-height harus nyaman.
```

---

## 5.4 Radius

Gunakan rounded yang modern tapi tidak terlalu cartoon.

```text
radius-xs     6px
radius-sm     8px
radius-md     12px
radius-lg     16px
radius-xl     20px
radius-2xl    28px
```

Standar:

| Component | Radius |
|---|---:|
| Button | 999px atau 12px |
| Input | 12px |
| Card | 20px |
| Modal | 24px |
| Product Card POS | 18px |
| KPI Card | 24px |
| Bottom Sheet Mobile | 28px top-left/top-right |

---

## 5.5 Shadow

Gunakan shadow halus.

```text
shadow-soft      0 1px 2px rgba(0,0,0,0.04)
shadow-card      0 8px 24px rgba(0,0,0,0.06)
shadow-floating  0 16px 48px rgba(0,0,0,0.12)
```

Aturan:

```text
Jangan gunakan shadow tebal di semua card.
Gunakan border + subtle shadow untuk surface.
Floating panel seperti POS cart boleh shadow lebih kuat.
```

---

## 5.6 Spacing

Gunakan sistem spacing konsisten:

```text
4px
8px
12px
16px
20px
24px
32px
40px
48px
64px
```

Aturan:

```text
Page padding desktop: 24–32px
Page padding tablet: 20–24px
Page padding mobile: 16px
Section gap: 24–32px
Card padding: 20–24px
Table cell padding: 12–16px
POS touch target minimal: 44px
```

---

# 6. Layout System

## 6.1 App Shell

Aplikasi menggunakan beberapa layout:

```text
AuthLayout
DashboardLayout
POSLayout
MobileLayout
PublicReceiptLayout
```

---

## 6.2 DashboardLayout

Untuk halaman:

```text
Dashboard
MasterData
Product
Pricing
Promotion
Inventory
Purchasing
Loyalty
Accounting
Reporting
System
```

Struktur:

```text
┌───────────────────────────────────────────────┐
│ Topbar: search, branch, notification, user    │
├───────────────┬───────────────────────────────┤
│ Sidebar       │ Content Area                  │
│ Navigation    │ Page Header                   │
│ Module Menu   │ Page Body                     │
└───────────────┴───────────────────────────────┘
```

Standar:

```text
sidebar collapsible
topbar sticky
content scrollable
breadcrumb optional
page action di kanan atas
global command search tersedia
```

---

## 6.3 Sidebar

Sidebar harus bersih dan tidak terlalu ramai.

Group menu:

```text
Overview
Sales / POS
Inventory
Purchasing
Product
Customer & Loyalty
Accounting
Reports
System
```

Aturan:

```text
ikon konsisten
label pendek
menu aktif jelas
submenu maksimal 2 level
hide menu jika tidak punya permission
```

Desktop:

```text
sidebar width expanded: 260px
sidebar width collapsed: 76px
```

Mobile:

```text
sidebar berubah menjadi drawer
bottom navigation hanya untuk role mobile/POS tertentu
```

---

## 6.4 Topbar

Topbar berisi:

```text
global search / command palette
current date
store/location
sync status
notification
user profile
```

Topbar tidak boleh terlalu tinggi.

```text
height: 64px
```

---

## 6.5 Page Header

Setiap halaman utama harus punya:

```text
title
short description
primary action
secondary action jika perlu
```

Contoh:

```text
Produk
Kelola produk, varian, barcode, dan status penjualan.
[Tambah Produk]
```

Untuk dashboard:

```text
Selamat pagi, Admin
Ringkasan operasional toko hari ini.
```

---

# 7. Responsive Standard

## 7.1 Breakpoints

Gunakan breakpoint:

```text
mobile       < 640px
tablet       640px - 1023px
desktop      1024px - 1439px
wide         >= 1440px
pos-kiosk    >= 1280px touch-friendly
```

---

## 7.2 Desktop

Desktop digunakan untuk:

```text
admin
owner
accounting
warehouse
reporting
```

Layout:

```text
sidebar + topbar + content grid
```

---

## 7.3 Tablet

Tablet digunakan untuk:

```text
warehouse
supervisor
mobile POS
stock opname
approval
```

Layout:

```text
sidebar drawer
card grid 2 column
table bisa berubah menjadi list
bottom sheet untuk action
```

---

## 7.4 Mobile

Mobile digunakan untuk:

```text
approval cepat
cek dashboard ringkas
cek stok
scan barcode ringan
lihat laporan ringkas
```

Aturan:

```text
hindari table horizontal panjang
gunakan list card
primary action sticky bottom
modal berubah menjadi bottom sheet
filter masuk drawer
```

---

## 7.5 POS Kiosk

POS Kiosk digunakan untuk layar kasir.

Prioritas:

```text
kecepatan
touch target besar
cart selalu terlihat
payment mudah
search cepat
barcode scanner friendly
```

Minimal touch target:

```text
44px
```

Ideal POS touch target:

```text
56px
```

---

# 8. Component Design System

## 8.1 Base Button

Variant:

```text
primary
secondary
ghost
soft
danger
success
icon
```

Button rules:

```text
hanya satu primary action per section
danger untuk destructive
loading state wajib
disabled state jelas
focus ring wajib
```

Contoh label:

```text
Simpan
Posting
Bayar
Approve
Reject
Void
Cetak Receipt
Export
```

---

## 8.2 Card

Card digunakan untuk:

```text
KPI
summary
form section
product tile
report block
approval item
```

Card style:

```text
background: white
border: 1px solid soft border
radius: 20px
padding: 20–24px
shadow: subtle
```

Jangan menggunakan card terlalu banyak jika data lebih cocok table.

---

## 8.3 KPI Card

KPI card harus mudah dibaca.

Isi:

```text
label
value
trend
context
optional icon
```

Contoh:

```text
Penjualan Hari Ini
Rp 12.450.000
+8.2% dibanding kemarin
```

Aturan:

```text
angka besar
label kecil
trend jelas
maksimal 4 KPI utama di dashboard
```

---

## 8.4 Data Table

Data table harus clean dan readable.

Fitur:

```text
search
filter
sort
pagination
column visibility jika perlu
bulk action jika perlu
empty state
loading skeleton
row action
```

Style:

```text
header sticky jika table panjang
zebra row optional sangat subtle
hover row subtle
status badge jelas
angka align kanan
tanggal konsisten
```

Aturan:

```text
jangan memasukkan terlalu banyak action button di row
gunakan dropdown action untuk action sekunder
gunakan badge untuk status
gunakan monospace untuk document_no jika perlu
```

---

## 8.5 Form

Form harus nyaman dan tidak padat.

Struktur:

```text
section title
field group
helper text
validation error
action bar
```

Aturan:

```text
label selalu terlihat
placeholder bukan pengganti label
error dekat field
field wajib diberi tanda
gunakan 2 column di desktop
gunakan 1 column di mobile
```

Form transaksi besar seperti PO, Supplier Invoice, Stock Opname harus page-based, bukan modal kecil.

---

## 8.6 Modal

Modal digunakan untuk proses ringan.

Cocok:

```text
confirm delete
confirm post
approval
reject reason
void reason
quick create simple master
```

Tidak cocok:

```text
purchase order besar
stock opname besar
supplier invoice kompleks
report kompleks
```

Mobile modal:

```text
gunakan bottom sheet
```

---

## 8.7 Badge

Badge digunakan untuk status.

Status umum:

```text
DRAFT
PENDING
APPROVED
POSTED
CANCELLED
REJECTED
VOID
CLOSED
OPEN
SYNCED
OFFLINE
FAILED
```

Badge style:

```text
soft background
medium text
rounded full
small padding
```

---

## 8.8 Toast & Alert

Toast untuk feedback ringan:

```text
Data berhasil disimpan.
Transaksi berhasil diposting.
Receipt berhasil dicetak.
```

Alert untuk kondisi penting:

```text
Stok tidak cukup.
Periode sudah ditutup.
Transaksi belum tersinkron.
Printer tidak terhubung.
```

Error kritikal jangan hanya toast.

---

## 8.9 Empty State

Empty state tidak boleh generic.

Contoh baik:

```text
Belum ada produk
Tambahkan produk pertama agar dapat digunakan di POS.
[Tambah Produk]
```

Contoh untuk report:

```text
Tidak ada transaksi pada periode ini
Coba ubah filter tanggal atau pilih shift lain.
```

---

## 8.10 Loading State

Gunakan:

```text
skeleton table
button spinner
page loader subtle
sync indicator
progress export
```

Aturan:

```text
jangan blank screen
jangan membuat user bingung apakah action berhasil
disable double submit
```

---

# 9. Navigation & Information Architecture

## 9.1 Main Navigation

Menu utama:

```text
Dashboard
POS
Orders / Sales
Inventory
Purchasing
Products
Pricing
Promotion
Customers
Loyalty
Accounting
Reports
System
```

Aturan:

```text
Menu mengikuti permission.
Role Cashier hanya melihat POS dan session terkait.
Role Warehouse fokus Inventory dan Purchasing.
Role Accounting fokus Accounting, AP, dan Reports.
Owner melihat dashboard dan reports lengkap.
```

---

## 9.2 Command Palette

Tambahkan command palette untuk desktop.

Shortcut:

```text
Cmd/Ctrl + K
```

Fungsi:

```text
cari halaman
cari transaksi
cari produk
cari customer
quick action
```

Contoh command:

```text
Buka POS
Tambah Produk
Cari Receipt
Buka Stock Card
Buka Trial Balance
```

---

## 9.3 Breadcrumb

Gunakan breadcrumb untuk halaman detail.

Contoh:

```text
Inventory / Stock Transfer / ST-2026060001
```

Untuk POS screen tidak perlu breadcrumb.

---

# 10. Page Design Standard

## 10.1 Auth Pages

Halaman:

```text
Login
Forgot Password
Reset Password
Register jika diperlukan
```

Design:

```text
centered card
brand logo kecil
background soft gradient minimal
headline singkat
form clean
```

Login harus cepat:

```text
username/email
password
remember device optional
button login
forgot password link
```

---

## 10.2 Dashboard

Dashboard harus memberikan overview cepat.

Section utama:

```text
Greeting
KPI Cards
Sales Trend
Payment Summary
Low Stock Alert
Pending Approval
Open Sessions
Today Activity
```

KPI:

```text
Penjualan Hari Ini
Transaksi Hari Ini
Gross Margin
Stok Kritis
Outstanding AP
Cash Difference
```

Layout desktop:

```text
4 KPI cards
2 chart cards
2 operational cards
```

Mobile:

```text
horizontal KPI scroll
cards stacked
```

---

## 10.3 POS Screen

POS adalah halaman paling penting.

Layout desktop/kiosk:

```text
┌─────────────────────────────────────────────────────────────┐
│ POS Topbar: cashier, session, sync, printer, shortcut       │
├─────────────────────────────┬───────────────────────────────┤
│ Product Area                │ Cart & Payment Area           │
│ Search + category           │ Customer                      │
│ Product grid                │ Cart items                    │
│                             │ Discount/Tax/Total            │
│                             │ Payment actions               │
└─────────────────────────────┴───────────────────────────────┘
```

Product Area:

```text
search besar
category chips
product grid
barcode focus mode
stock badge optional
favorite products
```

Cart Area:

```text
customer selector
item list
qty stepper
discount summary
tax summary
grand total besar
payment button sticky
hold bill
clear cart
```

POS action priority:

```text
Bayar
Hold Bill
Customer
Discount
Void
Print
```

Keyboard shortcut:

| Shortcut | Action |
|---|---|
| F2 | Focus product search |
| F4 | Select customer |
| F6 | Hold bill |
| F8 | Payment |
| F9 | Print receipt |
| Esc | Close modal |
| Enter | Confirm |

Offline POS indicator:

```text
Online
Offline — transaksi akan disinkronkan
Syncing 3 transaksi
Sync failed
```

---

## 10.4 Payment Modal

Payment modal harus sangat jelas.

Isi:

```text
grand total besar
payment method tabs
amount input
split payment list
change amount
reference number
submit payment
```

Aturan:

```text
payment kurang tidak boleh submit
payment lebih untuk cash hitung change
non-cash wajib reference jika setting mewajibkan
LOYALTY_POINT wajib customer
```

---

## 10.5 Inventory Pages

Halaman:

```text
Inventory Balance
Inventory Ledger
Stock Card
Stock Transfer
Stock Adjustment
Stock Opname
Planogram
```

Design:

```text
filter jelas
status stock visual
table readable
movement timeline untuk detail
```

Stock Card:

```text
product selector
location selector
date range
opening balance
movement list
ending balance
```

Endpoint canonical:

```text
GET /inventory/stock-card
```

---

## 10.6 Purchasing Pages

Halaman:

```text
Purchase Request
Purchase Order
Goods Receipt
Supplier Invoice
Supplier Payment
Accounts Payable
Purchase Return
```

Design:

```text
document flow timeline
status badge besar
supplier info card
item table
approval panel
posting panel
```

Document detail layout:

```text
Header document
Status and action
Supplier/metadata
Items
Totals
Approval history
Audit history
```

---

## 10.7 Accounting Pages

Halaman:

```text
Chart of Accounts
Payment Methods
Journal Entries
General Ledger
Trial Balance
Fiscal Period
Month Closing
Financial Snapshot
Accounting Rules
Journal Templates
```

Design:

```text
serius
rapi
minim warna
angka align kanan
debit credit jelas
balance indicator
```

Journal line:

```text
account
description
debit
credit
```

Rules:

```text
posted journal view-only
reverse action harus confirm
period closed badge jelas
```

---

## 10.8 Reporting Pages

Reporting bersifat read-only.

Halaman:

```text
Sales Report
Inventory Report
Inventory Movement
Inventory Valuation
Purchase Report
AP Report
Supplier Performance
Financial Report
Dashboard Report
```

Design:

```text
filter panel
summary cards
chart
detail table
export button
snapshot badge untuk closed period
```

Aturan:

```text
reporting tidak punya edit/post/delete action
export data sensitif harus permission
financial report closed period membaca snapshot
```

---

## 10.9 System Pages

Halaman:

```text
Users
Roles
Permissions
Approvals Inbox
Audit Logs
Activity Logs
Settings
Business Profile
Notifications
```

Design:

```text
akses jelas
risk action diberi confirm
audit log readable
role permission matrix mudah scan
```

---

# 11. POS Offline-First Standard

## 11.1 Tujuan

Karena POS perlu tetap operasional saat koneksi tidak stabil, frontend mendukung Offline-First Strategy.

Offline bukan berarti semua proses bebas risiko. Offline hanya boleh untuk transaksi yang bisa disimpan lokal dan disinkronkan ulang dengan aman.

---

## 11.2 Offline Data Lokal

Gunakan IndexedDB via Dexie.js.

Data lokal:

```text
products cache
product variants cache
barcodes cache
price list cache
payment methods cache
customers basic cache optional
current cashier session
cart draft
hold bills
offline transaction queue
sync logs
printer config
```

Jangan simpan:

```text
password
token plain text
financial report sensitif
full customer sensitive data
secret key
```

---

## 11.3 Offline Transaction Queue

Saat offline:

```text
cashier tetap bisa membuat transaksi
transaksi masuk local queue
receipt lokal bisa dicetak dengan status OFFLINE
stok final divalidasi ulang saat sync
journal dibuat setelah backend menerima transaksi
```

Local queue field:

```text
local_id
idempotency_key
transaction_payload
created_at
cashier_id
sales_session_id
sync_status
sync_attempt
last_error
```

Status:

```text
PENDING_SYNC
SYNCING
SYNCED
FAILED
CONFLICT
```

---

## 11.4 Idempotency

Setiap transaksi offline wajib memiliki:

```text
idempotency_key
```

Tujuan:

```text
mencegah double submit saat reconnect
mencegah transaksi ganda jika retry
```

Rule:

```text
idempotency_key dibuat di frontend
backend wajib menyimpan dan memvalidasi key
retry dengan key sama tidak membuat transaksi baru
```

---

## 11.5 Conflict Handling

Conflict yang mungkin terjadi:

```text
stok tidak cukup saat sync
produk sudah inactive
harga berubah
session sudah closed
periode sudah closed
payment method inactive
```

UI conflict harus jelas.

Contoh:

```text
Transaksi belum bisa disinkronkan
Produk "Kopi Susu" stok tidak cukup saat sinkronisasi.
Pilih tindakan: Recheck / Void Offline Transaction / Hubungi Supervisor
```

---

## 11.6 Offline Receipt

Receipt offline harus diberi label:

```text
OFFLINE RECEIPT
Belum tersinkron ke server
```

Setelah sync sukses:

```text
receipt server number tersedia
offline receipt linked to server transaction
```

---

# 12. Hardware Integration Standard

## 12.1 Printer

Integrasi printer:

```text
ESC/POS Encoder
WebUSB
WebSerial
Capacitor native bridge jika mobile
```

UI harus menyediakan:

```text
printer status
test print
reconnect printer
fallback download receipt PDF
```

Status:

```text
Printer connected
Printer disconnected
Printer permission required
Print failed
```

---

## 12.2 Barcode Scanner

Barcode scanner umumnya masuk sebagai keyboard input.

UI POS harus:

```text
selalu punya scan mode
focus search input otomatis
support fast input
debounce rendah
enter langsung add item
```

---

## 12.3 Cash Drawer

Jika cash drawer terhubung ke printer:

```text
open drawer command via ESC/POS
audit open drawer jika manual
permission untuk open drawer manual
```

---

# 13. Motion & Interaction

## 13.1 Motion Principle

Animasi harus:

```text
cepat
halus
membantu orientasi
tidak dekoratif berlebihan
```

Duration:

```text
micro: 120ms
normal: 180ms
modal: 240ms
page transition: 200ms
```

Gunakan easing:

```text
ease-out
ease-in-out
```

---

## 13.2 Hover & Press

Desktop:

```text
hover subtle background
hover slight translate optional
```

Touch:

```text
press state jelas
no hover dependency
```

---

## 13.3 Page Transition

Gunakan transition halus.

```text
opacity
small translate-y
```

Jangan animasikan table besar secara berat.

---

# 14. Accessibility

Minimal:

```text
keyboard navigable
focus visible
color contrast cukup
aria label untuk icon button
modal trap focus
form error readable
touch target minimal 44px
```

POS harus tetap bisa dipakai dengan keyboard.

---

# 15. TailwindCSS Standard

## 15.1 Class Style

Gunakan utility class, tetapi untuk component besar gunakan wrapper component.

Hindari:

```text
copy paste class panjang di banyak page
```

Gunakan:

```text
BaseButton
BaseInput
BaseCard
DataTable
StatusBadge
```

---

## 15.2 Tailwind Token Example

```js
theme: {
  extend: {
    colors: {
      surface: {
        main: '#F5F5F7',
        card: '#FFFFFF',
        subtle: '#FAFAFA',
      },
      ink: {
        primary: '#1D1D1F',
        secondary: '#6E6E73',
        muted: '#8E8E93',
      },
      brand: {
        DEFAULT: '#0071E3',
        hover: '#0066CC',
        soft: '#E8F2FF',
      },
    },
    borderRadius: {
      card: '20px',
      modal: '24px',
      pill: '999px',
    },
    boxShadow: {
      card: '0 8px 24px rgba(0,0,0,0.06)',
      floating: '0 16px 48px rgba(0,0,0,0.12)',
    },
  },
}
```

---

# 16. Folder Structure

```text
resources/js/
├── Layouts/
│   ├── AuthLayout.vue
│   ├── DashboardLayout.vue
│   ├── POSLayout.vue
│   └── PublicReceiptLayout.vue
│
├── Pages/
│   ├── Auth/
│   ├── Dashboard/
│   ├── POS/
│   ├── Inventory/
│   ├── Purchasing/
│   ├── Product/
│   ├── Pricing/
│   ├── Promotion/
│   ├── Loyalty/
│   ├── Accounting/
│   ├── Reporting/
│   └── System/
│
├── Components/
│   ├── Base/
│   ├── Form/
│   ├── Table/
│   ├── Modal/
│   ├── Navigation/
│   ├── Feedback/
│   ├── POS/
│   ├── Dashboard/
│   ├── Report/
│   └── Offline/
│
├── Composables/
│   ├── useAuth.js
│   ├── usePermission.js
│   ├── useToast.js
│   ├── useConfirmDialog.js
│   ├── useRealtime.js
│   ├── useOfflineQueue.js
│   ├── usePrinter.js
│   ├── useBarcodeScanner.js
│   └── useResponsive.js
│
├── Stores/
│   ├── authStore.js
│   ├── uiStore.js
│   ├── posStore.js
│   ├── cartStore.js
│   ├── offlineQueueStore.js
│   └── notificationStore.js
│
├── Services/
│   ├── api.js
│   ├── offlineDb.js
│   ├── syncService.js
│   ├── printerService.js
│   ├── currency.js
│   ├── date.js
│   └── permission.js
│
├── Constants/
│   ├── routes.js
│   ├── permissions.js
│   ├── statuses.js
│   ├── colors.js
│   └── breakpoints.js
│
└── Utils/
    ├── formatter.js
    ├── debounce.js
    ├── idempotency.js
    └── object.js
```

---

# 17. State Management

Gunakan Pinia untuk state lintas page.

Global state:

```text
auth user
permissions
sidebar state
notification count
POS cart
current sales session
offline queue
sync status
printer status
```

Jangan simpan semua data API di global store.

Data table/list cukup di page props atau local state.

---

# 18. Realtime UI Standard

Realtime menggunakan:

```text
Laravel Reverb
Laravel Echo
```

Digunakan untuk:

```text
notification
approval update
dashboard refresh
stock alert
sync completed
export completed
session status update
```

Rule:

```text
realtime hanya update UI
data penting tetap refetch dari API
jangan broadcast data sensitif
gunakan private channel
```

---

# 19. Iconography

Gunakan satu icon family.

Rekomendasi:

```text
Heroicons
Lucide
```

Pilih salah satu, jangan campur.

Aturan:

```text
stroke width konsisten
ukuran default 20px
sidebar 20px
button icon 18px
empty state 48px
```

---

# 20. Chart & Data Visualization

Chart digunakan untuk:

```text
sales trend
payment composition
inventory movement
gross margin
purchase trend
AP aging
financial summary
```

Design:

```text
clean
minimal grid
label jelas
tooltip readable
warna tidak terlalu banyak
```

Jangan gunakan chart jika table lebih jelas.

---

# 21. Copywriting UI

Gunakan bahasa Indonesia yang singkat dan operasional.

Contoh:

```text
Simpan
Batal
Posting
Approve
Reject
Cetak
Export
Sinkronkan
Tambah Produk
Buka Shift
Tutup Shift
```

Error:

```text
Stok tidak cukup.
Payment belum sesuai total.
Session kasir belum terbuka.
Periode sudah ditutup.
Printer belum terhubung.
Transaksi offline belum tersinkron.
```

Success:

```text
Transaksi berhasil diposting.
Data berhasil disimpan.
Receipt berhasil dicetak.
Sinkronisasi selesai.
```

---

# 22. UI Acceptance Criteria

UI dianggap sesuai jika:

```text
[ ] Tampilan bersih, modern, tidak generic
[ ] Semua halaman responsive
[ ] POS bisa dipakai cepat dengan keyboard dan touch
[ ] Table mudah dibaca
[ ] Form error jelas
[ ] Loading state tersedia
[ ] Empty state tidak generic
[ ] Permission memengaruhi menu dan action
[ ] Offline status jelas
[ ] Sync queue terlihat
[ ] Printer status terlihat
[ ] Mobile layout nyaman
[ ] Tidak ada action penting tanpa confirmation
[ ] Design token konsisten
[ ] Component reusable digunakan
```

---

# 23. Prioritas Implementasi Frontend

Urutan implementasi:

```text
Phase 1: Design Token + Base Components
Phase 2: Auth Layout + Dashboard Layout
Phase 3: Product + Master Data UI
Phase 4: POS Layout + Cart + Payment
Phase 5: Offline Queue + Printer Integration
Phase 6: Inventory UI
Phase 7: Purchasing UI
Phase 8: Accounting UI
Phase 9: Reporting UI
Phase 10: Mobile/PWA polish
```

---

# 24. Final Design Rule

Keputusan final:

```text
UI harus terasa premium, clean, dan modern.
Inspirasi visual boleh dari Apple, tetapi tidak meniru brand Apple.
ERP/POS tetap harus operasional, cepat, dan jelas.
POS screen mengutamakan speed.
Backoffice mengutamakan clarity.
Accounting mengutamakan akurasi.
Reporting mengutamakan readability.
Mobile mengutamakan action cepat.
Offline-first mengutamakan trust dan sync visibility.
```

Final target:

```text
Aplikasi terlihat seperti produk serius yang dibuat manusia,
bukan dashboard template,
bukan UI hasil generate AI,
dan bukan admin panel generik.
```
