<script setup>
import { Head, Link } from "@inertiajs/vue3";

defineProps({
    canLogin: { type: Boolean, default: true },
    canRegister: { type: Boolean, default: true },
});
</script>

<template>
    <Head title="Selamat Datang - ERP POS & Retail" />

    <div
        class="bg-surface-main text-ink-primary font-sans selection:bg-brand selection:text-white"
    >
        <!-- ========================================== -->
        <!-- 1. HERO SECTION (Full Screen & Centered)   -->
        <!-- ========================================== -->
        <section class="relative h-screen flex flex-col overflow-hidden">
            <!-- Decorative Mesh Gradient Background -->
            <div
                class="absolute top-1/4 left-1/3 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-brand/10 rounded-full blur-[120px] pointer-events-none z-0"
            ></div>
            <div
                class="absolute bottom-1/4 right-1/4 translate-x-1/2 translate-y-1/2 w-[400px] h-[400px] bg-brand/5 rounded-full blur-[100px] pointer-events-none z-0"
            ></div>

            <!-- Top Navbar (Absolute agar tidak memakan space vertikal) -->
            <header class="absolute top-0 left-0 w-full z-50">
                <div
                    class="max-w-7xl mx-auto px-xl py-base flex justify-between items-center"
                >
                    <div class="flex items-center gap-sm">
                        <div
                            class="bg-brand text-white p-sm rounded-lg shadow-soft font-extrabold text-lg tracking-wider"
                        >
                            E
                        </div>
                        <span
                            class="text-lg font-bold tracking-tight text-ink-primary"
                            >ERP POS & Retail</span
                        >
                    </div>

                    <nav class="flex items-center gap-md">
                        <Link
                            v-if="$page.props.auth.user"
                            href="/dashboard"
                            class="px-base py-md text-sm font-semibold bg-brand text-white rounded-xl shadow-soft hover:bg-brand/90 transition-all duration-200"
                        >
                            Masuk ke Dashboard
                        </Link>

                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="px-base py-md text-sm font-medium text-ink-secondary hover:text-brand transition-colors duration-200"
                            >
                                Log In
                            </Link>

                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="px-base py-md text-sm font-semibold bg-ink-primary text-white hover:bg-ink-primary/95 rounded-xl shadow-soft transition-all duration-200"
                            >
                                Daftar Akun
                            </Link>
                        </template>
                    </nav>
                </div>
            </header>

            <!-- Centered Hero Content -->
            <div class="flex-1 flex items-center justify-center px-xl z-10">
                <div class="max-w-3xl text-center space-y-xl">
                    <span
                        class="inline-block px-base py-sm bg-brand-soft text-brand rounded-full text-xs font-bold tracking-wide uppercase"
                    >
                        Integrated Enterprise Solution
                    </span>

                    <h1
                        class="text-4xl md:text-6xl font-black tracking-tight leading-tight"
                    >
                        Sistem ERP POS &
                        <span class="text-brand">Retail Modern</span>
                    </h1>

                    <p
                        class="text-base md:text-xl text-ink-secondary leading-relaxed max-w-2xl mx-auto"
                    >
                        Kelola seluruh siklus bisnis retail Anda dalam satu
                        platform realtime. Mulai dari kasir toko (POS), kontrol
                        inventaris multi-gudang, manajemen pembelian (PO/GRN),
                        program loyalitas pelanggan, hingga akuntansi otomatis.
                    </p>

                    <div
                        class="flex flex-col sm:flex-row gap-base justify-center"
                    >
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="px-xl py-xl font-bold bg-brand text-white text-center rounded-2xl shadow-soft hover:shadow-medium hover:bg-brand/90 transition-all duration-200"
                        >
                            Buka Dashboard Utama
                        </Link>
                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="px-xl py-xl font-bold bg-brand text-white text-center rounded-2xl shadow-soft hover:shadow-medium hover:bg-brand/90 transition-all duration-200"
                            >
                                Log In Sekarang
                            </Link>
                            <Link
                                :href="route('register')"
                                class="px-xl py-xl font-bold bg-surface-card border border-border-soft text-center text-ink-primary rounded-2xl shadow-soft hover:bg-surface-subtle transition-all duration-200"
                            >
                                Hubungi Penjualan
                            </Link>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Scroll Indicator (Panah ke bawah untuk UX) -->
            <div
                class="absolute bottom-8 left-1/2 -translate-x-1/2 z-10 animate-bounce text-ink-secondary"
            >
                <svg
                    class="w-6 h-6"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M19 14l-7 7m0 0l-7-7m7 7V3"
                    ></path>
                </svg>
            </div>
        </section>

        <!-- ========================================== -->
        <!-- 2. FEATURES SECTION (Muncul saat scroll)   -->
        <!-- ========================================== -->
        <section class="py-24 px-xl bg-surface-main">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-16 space-y-4">
                    <h2 class="text-3xl md:text-4xl font-bold">
                        Fitur Unggulan
                    </h2>
                    <p class="text-ink-secondary max-w-2xl mx-auto">
                        Solusi lengkap untuk mengelola seluruh operasional
                        bisnis retail Anda.
                    </p>
                </div>

                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-xl"
                >
                    <!-- Feature 1 -->
                    <div
                        class="bg-surface-card border border-border-soft p-xl rounded-2xl shadow-soft hover:shadow-medium transition-all duration-200 space-y-md"
                    >
                        <div
                            class="w-10 h-10 bg-brand-soft text-brand rounded-xl flex items-center justify-center font-bold text-xl"
                        >
                            🛒
                        </div>
                        <h3 class="font-bold text-lg text-ink-primary">
                            Kasir Toko (POS)
                        </h3>
                        <p class="text-sm text-ink-secondary leading-relaxed">
                            Transaksi cepat dengan barcode scan, penahanan bon
                            belanja (hold bill), retur barang, cetak struk, dan
                            integrasi laci kasir.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div
                        class="bg-surface-card border border-border-soft p-xl rounded-2xl shadow-soft hover:shadow-medium transition-all duration-200 space-y-md"
                    >
                        <div
                            class="w-10 h-10 bg-brand-soft text-brand rounded-xl flex items-center justify-center font-bold text-xl"
                        >
                            📦
                        </div>
                        <h3 class="font-bold text-lg text-ink-primary">
                            Kontrol Inventaris
                        </h3>
                        <p class="text-sm text-ink-secondary leading-relaxed">
                            Pantau sisa stok barang, catat mutasi stok
                            masuk-keluar, serta lakukan transfer antar gudang
                            dengan alur persetujuan.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div
                        class="bg-surface-card border border-border-soft p-xl rounded-2xl shadow-soft hover:shadow-medium transition-all duration-200 space-y-md"
                    >
                        <div
                            class="w-10 h-10 bg-brand-soft text-brand rounded-xl flex items-center justify-center font-bold text-xl"
                        >
                            🧾
                        </div>
                        <h3 class="font-bold text-lg text-ink-primary">
                            Manajemen Pembelian
                        </h3>
                        <p class="text-sm text-ink-secondary leading-relaxed">
                            Kelola Purchase Orders (PO) ke Supplier, penerimaan
                            barang (Goods Receipt), dan penagihan invoice secara
                            terpusat.
                        </p>
                    </div>

                    <!-- Feature 4 -->
                    <div
                        class="bg-surface-card border border-border-soft p-xl rounded-2xl shadow-soft hover:shadow-medium transition-all duration-200 space-y-md"
                    >
                        <div
                            class="w-10 h-10 bg-brand-soft text-brand rounded-xl flex items-center justify-center font-bold text-xl"
                        >
                            📊
                        </div>
                        <h3 class="font-bold text-lg text-ink-primary">
                            Akuntansi Otomatis
                        </h3>
                        <p class="text-sm text-ink-secondary leading-relaxed">
                            Setiap transaksi penjualan dan pembelian secara
                            otomatis diposting ke Jurnal Akuntansi dan Buku
                            Besar (General Ledger).
                        </p>
                    </div>

                    <!-- Feature 5 -->
                    <div
                        class="bg-surface-card border border-border-soft p-xl rounded-2xl shadow-soft hover:shadow-medium transition-all duration-200 space-y-md"
                    >
                        <div
                            class="w-10 h-10 bg-brand-soft text-brand rounded-xl flex items-center justify-center font-bold text-xl"
                        >
                            🦁
                        </div>
                        <h3 class="font-bold text-lg text-ink-primary">
                            Loyalitas Pelanggan
                        </h3>
                        <p class="text-sm text-ink-secondary leading-relaxed">
                            Tingkatkan retensi dengan kartu poin member dinamis,
                            tingkat keanggotaan (membership tiers), dan sistem
                            diskon otomatis.
                        </p>
                    </div>

                    <!-- Feature 6 -->
                    <div
                        class="bg-surface-card border border-border-soft p-xl rounded-2xl shadow-soft hover:shadow-medium transition-all duration-200 space-y-md"
                    >
                        <div
                            class="w-10 h-10 bg-brand-soft text-brand rounded-xl flex items-center justify-center font-bold text-xl"
                        >
                            📈
                        </div>
                        <h3 class="font-bold text-lg text-ink-primary">
                            Laporan Finansial
                        </h3>
                        <p class="text-sm text-ink-secondary leading-relaxed">
                            Dashboard omset penjualan harian, analisis laba
                            kotor produk, mutasi persediaan, hingga neraca
                            keuangan secara langsung.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========================================== -->
        <!-- 3. FOOTER                                  -->
        <!-- ========================================== -->
        <footer
            class="w-full max-w-7xl mx-auto px-xl py-xl border-t border-border-soft flex flex-col md:flex-row justify-between items-center gap-base text-sm text-ink-secondary"
        >
            <p>ERP POS & Retail © 2026. Hak Cipta Dilindungi.</p>
            <p>Made with Laravel & Vue 3</p>
        </footer>
    </div>
</template>
