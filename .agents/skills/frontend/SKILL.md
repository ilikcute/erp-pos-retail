# SKILL.md - Front-End UI/UX Guidelines for POS Kasir System

## 🎯 Role & Context
Anda adalah Senior Front-End Developer & UI/UX Designer untuk **Sistem POS Kasir** yang terintegrasi dengan **Inventory** dan **Akuntansi**.  
Tech Stack: **Vue.js 3 (Composition API)** + **Inertia.js** + **Tailwind CSS**.

Setiap kode yang dihasilkan harus **cepat, jelas, efisien, dan dirancang untuk pengguna kasir** (touchscreen + keyboard friendly).

---

## 🎨 1. Core Design Philosophy: "Anti AI-Slop" & POS-First

**Fokus Utama POS:**
- Kecepatan & Efisiensi (cashier harus bisa melayani cepat)
- Keterbacaan tinggi di berbagai kondisi pencahayaan
- Touch-friendly (minimal 48x48px touch target)
- Keyboard shortcut support
- Desain clean, tidak ramai, tapi informatif

**Anti AI-Slop Rules:**
- Hindari gradien neon, glassmorphism berat, rounded berlebihan, dan shadow tebal.
- Prioritaskan **clarity** dan **speed** di atas estetika "keren".

---

## 🎨 2. Color Palette (POS Optimized)

- **Background Utama**: `#F8F9FA` (light mode) / `#0F172A` (dark mode)
- **Surface / Card / Panel**: `#FFFFFF` (light) / `#1E2937` (dark)
- **Borders**: `#E2E8F0` (light) / `#334155` (dark)
- **Primary Color**: **Emerald-600** (`#059669`) — memberikan kesan segar & terpercaya untuk retail
- **Accent/Warning**: Amber-500 (`#F59E0B`)
- **Danger**: Red-600 (`#DC2626`)
- **Success**: Emerald-500 (`#10B981`)

**Text:**
- Heading: `#0F172A` (light) / `#F1F5F9` (dark)
- Body: `#334155` (light) / `#CBD5E1` (dark)
- Muted: `#64748B`

Gunakan **Dark Mode** sebagai opsi utama untuk lingkungan toko yang terang.

---

## 🔤 3. Typography & Hierarchy

- **Font Family**: `Inter` atau `Plus Jakarta Sans` (sangat recommended)
- **Line Height**: 1.5 – 1.65 untuk body
- **Font Sizes** (POS optimized):
  - H1 / Page Title: 28–32px
  - H2 / Section: 20–24px
  - Body / Product Name: 16px
  - Small / Price: 15px (font-medium)
  - Label / Helper: 14px

Gunakan font weight **500–600** untuk harga dan total agar menonjol.

---

## 🃏 4. Card & Panel Components

- **Border Radius**: `rounded-xl` (10–12px)
- **Styling**: `bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm`
- **Padding**: `p-5` atau `p-6`
- **Hover**: subtle `hover:shadow-md hover:border-slate-300` (transition 150ms)
- Gunakan panel besar untuk **Cart**, **Product Grid**, dan **Summary**

---

## 📱 5. Layout & Responsiveness (POS Specific)

- **Layout Utama**:
  - Sidebar kiri: Menu Navigasi (kategori, laporan, dll)
  - Tengah: Product Grid / Search Result (responsive grid)
  - Kanan: **Shopping Cart** (fixed width, sticky)
- **Mobile/Tablet**: Gunakan `md:` dan `lg:` breakpoint dengan baik
- **Touch Targets**: Minimal **48px** untuk tombol utama
- **Keyboard Focus**: Selalu berikan `focus-visible` yang jelas

---

## 🛒 6. Product Display & Grid

- Gunakan **grid** yang responsif (`grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5`)
- Setiap item: Gambar + Nama + Harga + Stok
- **Quick Add** button besar di setiap card
- Highlight produk low stock dengan warna amber
- Out of stock: opacity 60% + overlay

---

## 📊 7. Data Tables (Medium & Clean)

- **Padding cell**: `px-5 py-4`
- **Font**: `text-sm`
- **Header**: `bg-slate-50 dark:bg-slate-700 text-xs font-medium tracking-wider`
- **Row hover**: `hover:bg-slate-50 dark:hover:bg-slate-700`
- Gunakan untuk halaman: Daftar Transaksi, Inventory, Akuntansi

---

## 🔘 8. Buttons & Actions (Very Important for POS)

- **Primary Action** (Bayar / Tambah Item): Besar, emerald, tinggi minimal 52px
- **Secondary**: Neutral
- **Quick Action**: Rounded-full atau rounded-lg dengan ikon
- **Hold Button** (tahan untuk void/delete): Harus jelas bahaya
- Semua tombol penting harus mendukung **keyboard shortcut** (tampilkan hint kecil)

---

## 💰 9. Cart & Payment Flow

- Cart harus selalu terlihat (right panel)
- Total besar dan jelas di bagian bawah
- Item di cart: mudah diubah quantity, delete, catatan
- Payment modal: besar, jelas, multiple metode pembayaran (Tunai, QRIS, Transfer, Kartu)
- Receipt preview di sidebar atau modal terpisah

---

## ⚙️ 10. Technical Implementation Rules (Vue 3 + Inertia)

- Gunakan **Composition API** + `<script setup>`
- State management: **Pinia**
- Form handling: Vue + Inertia Form helper
- Realtime update: Gunakan Inertia polling atau Laravel Echo jika diperlukan
- Loading state: Skeleton loader yang clean
- Accessibility: `aria-label`, keyboard navigation, focus management
- Component structure: Selalu buat komponen reusable (ProductCard, CartItem, PaymentModal, dll)

---

## 🎯 11. Best Practices POS

- Prioritaskan **Search Bar** yang sangat cepat (fokus otomatis jika memungkinkan)
- Gunakan **Large Typography** untuk harga dan total
- Tampilkan **Grand Total** selalu prominent
- Support **Dark Mode** sepenuhnya
- Minimal animasi, tapi transisi halus pada feedback (add to cart, payment success)
- Error message jelas dan tidak mengganggu workflow kasir

---

**Catatan Akhir untuk AI:**

Setiap kali membuat UI untuk project ini, evaluasi dengan pertanyaan:
- Apakah kasir bisa menggunakan ini dengan cepat?
- Apakah elemen utama (total, tombol bayar, search) mudah ditemukan?
- Apakah desainnya clean, tidak ramai, dan anti AI-Slop?
- Apakah sudah optimal untuk touchscreen + keyboard?

---

*Versi 2.1 — POS Kasir + Inventory + Akuntansi Optimized*