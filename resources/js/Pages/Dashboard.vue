<script setup>
import { ref, onMounted, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useChart, chartPresets } from '@/Composables/useChart.js';

import axios from 'axios';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';



const loading = ref(true);
const data = ref({
    sales_kpi: { total_sales: 18450000, total_transactions: 312 },
    financial_kpi: { gross_margin_percent: 42.5 },
    inventory_kpi: { low_stock_count: 7 },
    customer_kpi: { total_customers: 1280 },
    top_products: [
        { product_id: 1, product_name: 'Kopi Susu Gula Aren', total_sales: 4200000, emoji: '☕' },
        { product_id: 2, product_name: 'Nasi Goreng Spesial', total_sales: 3550000, emoji: '🍛' },
        { product_id: 3, product_name: 'Burger Daging',       total_sales: 2980000, emoji: '🍔' },
        { product_id: 4, product_name: 'Pizza Slice',         total_sales: 2410000, emoji: '🍕' },
        { product_id: 5, product_name: 'Jus Jeruk Segar',     total_sales: 1870000, emoji: '🧃' },
    ],
    sales_trend: { labels: ['Sen','Sel','Rab','Kam','Jum','Sab','Min'], values: [2.1, 2.8, 2.4, 3.2, 3.8, 4.5, 4.1] },
});

const rupiah = (n) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n || 0);
const compact = (n) => new Intl.NumberFormat('id-ID', { notation: 'compact', maximumFractionDigits: 1 }).format(n || 0);

const kpis = computed(() => [
    { label: 'Total Penjualan', value: rupiah(data.value.sales_kpi.total_sales), icon: '💰', grad: 'bg-brand-gradient',  trend: '+12.5%', up: true },
    { label: 'Transaksi',       value: data.value.sales_kpi.total_transactions, icon: '🧾', grad: 'bg-mint-gradient',   trend: '+8.2%',  up: true },
    { label: 'Margin Kotor',    value: data.value.financial_kpi.gross_margin_percent + '%', icon: '📈', grad: 'bg-grape-gradient', trend: '+2.1%', up: true },
    { label: 'Pelanggan',       value: compact(data.value.customer_kpi.total_customers), icon: '👥', grad: 'bg-sunset-gradient', trend: '+5.0%', up: true },
]);

const topMax = computed(() => Math.max(...data.value.top_products.map(p => p.total_sales), 1));

const quickActions = [
    { name: 'Buka Kasir',    href: '/pos',        icon: '🛒', tint: 'bg-brand-soft text-brand' },
    { name: 'Tambah Produk', href: '/products',   icon: '📦', tint: 'bg-accent-mint-soft text-accent-mint' },
    { name: 'Pembelian',     href: '/purchasing', icon: '🚚', tint: 'bg-accent-sunny-soft text-accent-sunny' },
    { name: 'Laporan',       href: '/reporting',  icon: '📊', tint: 'bg-accent-grape-soft text-accent-grape' },
];

const chartRef = ref(null);
const { createChart } = useChart(chartRef);

const renderChart = () => {
    const t = data.value.sales_trend;
    createChart(chartPresets.areaChart(t.labels, [{
        label: 'Penjualan (juta Rp)',
        data: t.values,
        borderColor: '#6C5CE7',
        backgroundColor: 'rgba(108, 92, 231, 0.12)',
        borderWidth: 3,
        tension: 0.45,
        fill: true,
        pointBackgroundColor: '#6C5CE7',
        pointRadius: 4,
    }]));
};

onMounted(async () => {
    try {
        const res = await axios.get('/api/v1/dashboard');
        if (res.data) { data.value = { ...data.value, ...res.data }; }
    } catch (e) {
        console.warn('Dashboard API tidak tersedia, memakai data contoh.');
    } finally {
        loading.value = false;
        renderChart();
    }
});
</script>

<template>
    <Head title="Dashboard" />
    <DashboardLayout>
        <!-- Greeting banner -->
        <div class="rounded-card bg-brand-gradient text-white p-xl mb-xl shadow-brand-glow flex items-center justify-between flex-wrap gap-base">
            <div>
                <h1 class="text-page-title font-extrabold">Halo, selamat datang! 👋</h1>
                <p class="text-white/80 mt-xs text-base">Ringkasan performa toko Anda hari ini.</p>
            </div>
            <Link href="/pos" class="btn-pill bg-white text-brand px-xl py-md text-base font-bold hover:opacity-95">
                🛒 Buka Kasir
            </Link>
        </div>

        <!-- KPI cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-base mb-xl">
            <div v-for="k in kpis" :key="k.label" class="card-friendly p-lg hover:shadow-floating transition-shadow">
                <div class="flex items-start justify-between">
                    <div :class="['w-12 h-12 rounded-xl flex items-center justify-center text-2xl text-white shadow-soft', k.grad]">{{ k.icon }}</div>
                    <span :class="['chip text-xs px-sm py-0.5', k.up ? 'bg-accent-mint-soft text-accent-mint' : 'bg-semantic-danger-soft text-semantic-danger']">
                        {{ k.up ? '▲' : '▼' }} {{ k.trend }}
                    </span>
                </div>
                <p class="text-sm font-medium text-ink-secondary mt-base">{{ k.label }}</p>
                <p class="text-page-title-sm font-extrabold text-ink-primary mt-xs">{{ k.value }}</p>
            </div>
        </div>

        <!-- Chart + Low stock -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-base mb-xl">
            <div class="card-friendly p-lg lg:col-span-2">
                <div class="flex items-center justify-between mb-base">
                    <h2 class="text-section-title font-bold text-ink-primary">Tren Penjualan</h2>
                    <span class="chip bg-brand-soft text-brand text-xs">7 hari terakhir</span>
                </div>
                <div class="h-64"><canvas ref="chartRef"></canvas></div>
            </div>

            <div class="card-friendly p-lg flex flex-col">
                <h2 class="text-section-title font-bold text-ink-primary mb-base">Perlu Perhatian</h2>
                <div class="rounded-lg bg-semantic-warning-soft p-base flex items-center gap-md mb-md">
                    <div class="w-11 h-11 rounded-xl bg-accent-sunny flex items-center justify-center text-xl text-white">⚠️</div>
                    <div>
                        <p class="text-page-title-sm font-extrabold text-ink-primary leading-none">{{ data.inventory_kpi.low_stock_count }}</p>
                        <p class="text-sm text-ink-secondary">Produk stok menipis</p>
                    </div>
                </div>
                <Link href="/inventory" class="btn-pill bg-accent-sunny-soft text-accent-sunny py-sm text-sm mb-base hover:opacity-90">Lihat Inventory →</Link>
                <div class="rounded-lg bg-accent-mint-soft p-base flex items-center gap-md mt-auto">
                    <div class="w-11 h-11 rounded-xl bg-accent-mint flex items-center justify-center text-xl text-white">✅</div>
                    <div>
                        <p class="text-sm font-semibold text-ink-primary">Semua sistem normal</p>
                        <p class="text-sm text-ink-secondary">Sinkronisasi terakhir baru saja</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top products + Quick actions -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-base">
            <div class="card-friendly p-lg lg:col-span-2">
                <h2 class="text-section-title font-bold text-ink-primary mb-base">Produk Terlaris 🔥</h2>
                <div class="space-y-md">
                    <div v-for="(p, i) in data.top_products" :key="p.product_id" class="flex items-center gap-md">
                        <div class="w-10 h-10 rounded-lg bg-surface-muted flex items-center justify-center text-xl flex-shrink-0">{{ p.emoji || '📦' }}</div>
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-center mb-xs">
                                <span class="text-sm font-semibold text-ink-primary truncate">{{ i + 1 }}. {{ p.product_name }}</span>
                                <span class="text-sm font-bold text-brand ml-md">{{ rupiah(p.total_sales) }}</span>
                            </div>
                            <div class="h-2 rounded-pill bg-surface-muted overflow-hidden">
                                <div class="h-full rounded-pill bg-brand-gradient" :style="{ width: (p.total_sales / topMax * 100) + '%' }"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-friendly p-lg">
                <h2 class="text-section-title font-bold text-ink-primary mb-base">Aksi Cepat ⚡</h2>
                <div class="grid grid-cols-2 gap-md">
                    <Link v-for="a in quickActions" :key="a.name" :href="a.href"
                        class="rounded-xl p-base flex flex-col items-center justify-center gap-sm text-center hover:-translate-y-0.5 transition-transform active:scale-95"
                        :class="a.tint">
                        <span class="text-3xl">{{ a.icon }}</span>
                        <span class="text-sm font-semibold">{{ a.name }}</span>
                    </Link>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>