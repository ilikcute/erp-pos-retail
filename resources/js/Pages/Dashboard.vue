<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Chart from 'chart.js/auto';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Icon from '@/Components/Base/Icon.vue';
import StatCard from '@/Components/Dashboard/StatCard.vue';
import TargetCard from '@/Components/Dashboard/TargetCard.vue';
import InfoCard from '@/Components/Dashboard/InfoCard.vue';
import ListCard from '@/Components/Dashboard/ListCard.vue';

const props = defineProps({
    totalCategories: { type: Number, default: 24 },
    totalProducts: { type: Number, default: 318 },
    totalTransactions: { type: Number, default: 1240 },
    totalCustomers: { type: Number, default: 486 },
    revenueTrend: { type: Array, default: () => [] },
    totalRevenue: { type: Number, default: 0 },
    totalProfit: { type: Number, default: 0 },
    averageOrder: { type: Number, default: 0 },
    todayTransactions: { type: Number, default: 37 },
    todaySales: { type: Number, default: 4250000 },
    todayProfit: { type: Number, default: 1180000 },
    monthlyTarget: { type: Number, default: 150000000 },
    currentMonthSales: { type: Number, default: 98500000 },
    topProducts: { type: Array, default: () => [] },
    slowMovingProducts: { type: Array, default: () => [] },
    recentTransactions: { type: Array, default: () => [] },
    topCustomers: { type: Array, default: () => [] },
    topLocations: { type: Array, default: () => [] },
    lowStockProducts: { type: Array, default: () => [] },
    activeShifts: { type: Array, default: () => [] },
});

const fmt = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v || 0);

// ---- mock fallbacks (render immediately before API is wired) ----
const mockTrend = [
    { label: 'Sen', value: 12 }, { label: 'Sel', value: 18 }, { label: 'Rab', value: 15 },
    { label: 'Kam', value: 22 }, { label: 'Jum', value: 28 }, { label: 'Sab', value: 35 }, { label: 'Min', value: 30 },
].map((d) => ({ label: d.label, value: d.value * 1000000 }));

const trend = computed(() => (props.revenueTrend && props.revenueTrend.length ? props.revenueTrend : mockTrend));
const trendLabel = (d) => d.label ?? d.date ?? d.day ?? d.name ?? '';
const trendValue = (d) => Number(d.value ?? d.total ?? d.revenue ?? d.amount ?? 0);

const products = computed(() => props.topProducts.length ? props.topProducts : [
    { name: 'Kopi Susu Gula Aren', sold: 320, revenue: 9600000 },
    { name: 'Roti Bakar Coklat', sold: 210, revenue: 4200000 },
    { name: 'Teh Tarik', sold: 180, revenue: 2700000 },
]);
const transactions = computed(() => props.recentTransactions.length ? props.recentTransactions : [
    { invoice: 'INV-1042', customer: 'Andi', total: 125000, created_at: '10:24' },
    { invoice: 'INV-1041', customer: 'Sari', total: 86000, created_at: '10:11' },
    { invoice: 'INV-1040', customer: 'Budi', total: 240000, created_at: '09:58' },
]);
const customers = computed(() => props.topCustomers.length ? props.topCustomers : [
    { name: 'Andi Pratama', total_spent: 4200000 }, { name: 'Sari Dewi', total_spent: 3850000 }, { name: 'Budi Santoso', total_spent: 3100000 },
]);
const locations = computed(() => props.topLocations.length ? props.topLocations : [
    { name: 'Menteng', total: 18500000 }, { name: 'Kebayoran', total: 14200000 }, { name: 'Cilandak', total: 9800000 },
]);
const lowStock = computed(() => props.lowStockProducts.length ? props.lowStockProducts : [
    { name: 'Gula Aren 1kg', stock: 3 }, { name: 'Susu UHT 1L', stock: 5 }, { name: 'Kopi Robusta 500g', stock: 2 },
]);
const slowMoving = computed(() => props.slowMovingProducts.length ? props.slowMovingProducts : [
    { name: 'Sirup Vanilla', stock: 24 }, { name: 'Cup 16oz', stock: 540 }, { name: 'Teh Hijau Premium', stock: 18 },
]);
const shifts = computed(() => props.activeShifts.length ? props.activeShifts : [
    { cashier_name: 'Rina', opened_at: '08:00', expected_cash: 2350000 },
]);

// ---- chart ----
const chartRef = ref(null);
let chartInstance = null;
onMounted(() => {
    if (!chartRef.value) return;
    const ctx = chartRef.value.getContext('2d');
    const grad = ctx.createLinearGradient(0, 0, 0, 280);
    grad.addColorStop(0, 'rgba(99, 102, 241, 0.35)');
    grad.addColorStop(1, 'rgba(99, 102, 241, 0)');
    chartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: trend.value.map(trendLabel),
            datasets: [{
                label: 'Pendapatan',
                data: trend.value.map(trendValue),
                borderColor: '#6366f1',
                backgroundColor: grad,
                fill: true,
                tension: 0.4,
                borderWidth: 3,
                pointRadius: 0,
                pointHoverRadius: 5,
                pointHoverBackgroundColor: '#6366f1',
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { backgroundColor: '#1e293b', titleColor: '#f1f5f9', bodyColor: '#f1f5f9', padding: 12, displayColors: false, callbacks: { label: (c) => fmt(c.parsed.y) } },
            },
            scales: {
                y: { beginAtZero: true, ticks: { callback: (v) => 'Rp' + (v / 1000000) + 'jt', color: '#94a3b8' }, grid: { color: 'rgba(148,163,184,0.15)' } },
                x: { ticks: { color: '#94a3b8' }, grid: { display: false } },
            },
        },
    });
});
</script>

<template>
    <Head title="Dashboard" />
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-extrabold text-ink-primary">Dashboard</h1>
                    <p class="text-sm text-ink-secondary mt-1">Ringkasan performa toko Anda hari ini</p>
                </div>
                <Link href="/pos" class="inline-flex items-center gap-2 rounded-pill bg-brand-gradient text-white px-5 py-2.5 text-sm font-bold shadow-brand-glow hover:opacity-95 transition">
                    <Icon name="plus" size="4" /> <span>Transaksi Baru</span>
                </Link>
            </div>

            <!-- Main Stat Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <StatCard title="Penjualan Hari Ini" :value="fmt(todaySales)" subtitle="Total penjualan hari ini" icon="dollar-sign" gradient="from-brand to-brand-strong" />
                <StatCard title="Laba Hari Ini" :value="fmt(todayProfit)" subtitle="Estimasi margin" icon="trending-up" gradient="from-accent-mint to-accent-mint-strong" trend="Hari ini" />
                <TargetCard title="Target Bulan Ini" :current="currentMonthSales" :target="monthlyTarget" icon="target" />
                <StatCard title="Transaksi Hari Ini" :value="todayTransactions" subtitle="Jumlah struk" icon="receipt" gradient="from-accent-sunny to-accent-coral" />
            </div>

            <!-- Secondary Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <InfoCard title="Total Kategori" :value="totalCategories" icon="layers" />
                <InfoCard title="Total Produk" :value="totalProducts" icon="box" />
                <InfoCard title="Total Pelanggan" :value="totalCustomers" icon="users" />
                <InfoCard title="Total Transaksi" :value="totalTransactions" icon="file-text" />
            </div>

            <!-- Revenue Chart -->
            <ListCard title="Tren Pendapatan" subtitle="7 hari terakhir" icon="bar-chart">
                <div class="h-72"><canvas ref="chartRef"></canvas></div>
            </ListCard>

            <!-- Bottom Widgets -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <ListCard title="Shift Aktif" subtitle="Pemantauan kasir" icon="dollar-sign" empty-message="Tidak ada shift aktif" :is-empty="shifts.length === 0">
                    <div class="divide-y divide-border-soft">
                        <div v-for="(s, i) in shifts" :key="i" class="flex items-center justify-between py-3 first:pt-0 last:pb-0">
                            <div><p class="text-sm font-semibold text-ink-primary">{{ s.cashier_name }}</p><p class="text-xs text-ink-muted">Buka {{ s.opened_at }}</p></div>
                            <span class="text-sm font-bold text-accent-mint">{{ fmt(s.expected_cash) }}</span>
                        </div>
                    </div>
                </ListCard>
                <ListCard title="Produk Terlaris" subtitle="Best seller" icon="box" :is-empty="products.length === 0">
                    <div class="divide-y divide-border-soft">
                        <div v-for="(p, i) in products.slice(0,3)" :key="i" class="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0">
                            <div class="flex items-center gap-3 min-w-0"><span class="w-7 h-7 rounded-full bg-brand-soft text-brand text-sm font-semibold flex items-center justify-center shrink-0">{{ i + 1 }}</span><div class="min-w-0"><p class="text-sm font-semibold text-ink-primary truncate">{{ p.name }}</p><p class="text-xs text-ink-muted">{{ p.sold ?? p.qty ?? 0 }} terjual</p></div></div>
                            <span class="text-sm font-bold text-brand shrink-0">{{ fmt(p.revenue ?? 0) }}</span>
                        </div>
                    </div>
                </ListCard>
                <ListCard title="Transaksi Terbaru" subtitle="Aktivitas terkini" icon="receipt" :is-empty="transactions.length === 0">
                    <ul class="divide-y divide-border-soft">
                        <li v-for="(t, i) in transactions.slice(0,4)" :key="i" class="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0">
                            <div class="min-w-0"><p class="text-sm font-semibold text-ink-primary truncate">{{ t.invoice ?? t.code ?? "-" }}</p><p class="text-xs text-ink-muted truncate">{{ t.customer ?? "Umum" }} • {{ t.created_at ?? "" }}</p></div>
                            <p class="text-sm font-bold text-brand shrink-0">{{ fmt(t.total) }}</p>
                        </li>
                    </ul>
                </ListCard>
                <ListCard title="Pelanggan Terbaik" subtitle="Top spender" icon="users" :is-empty="customers.length === 0">
                    <ul class="divide-y divide-border-soft">
                        <li v-for="(cst, i) in customers.slice(0,4)" :key="i" class="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0">
                            <div class="flex items-center gap-3 min-w-0"><span class="w-7 h-7 rounded-full bg-accent-grape-soft text-accent-grape text-sm font-semibold flex items-center justify-center shrink-0">{{ i + 1 }}</span><span class="text-sm text-ink-primary truncate">{{ cst.name }}</span></div>
                            <span class="text-sm font-bold text-ink-primary shrink-0">{{ fmt(cst.total_spent ?? cst.total ?? 0) }}</span>
                        </li>
                    </ul>
                </ListCard>
            </div>

            <!-- Secondary Widgets -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <ListCard title="Lokasi Teratas" subtitle="Berdasar kelurahan transaksi" icon="map-pin" :is-empty="locations.length === 0">
                    <div class="divide-y divide-border-soft">
                        <div v-for="(loc, i) in locations.slice(0,5)" :key="i" class="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0">
                            <div class="flex items-center gap-2 min-w-0"><Icon name="map-pin" size="4" class="text-brand shrink-0" /><span class="text-sm text-ink-primary truncate">{{ loc.name }}</span></div>
                            <span class="text-sm font-bold text-ink-primary shrink-0">{{ fmt(loc.total ?? 0) }}</span>
                        </div>
                    </div>
                </ListCard>
                <ListCard title="Stok Menipis" subtitle="Perlu reorder" icon="alert-triangle" :is-empty="lowStock.length === 0">
                    <div class="divide-y divide-border-soft">
                        <div v-for="(p, i) in lowStock.slice(0,5)" :key="i" class="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0">
                            <div class="flex items-center gap-2 min-w-0"><Icon name="alert-triangle" size="4" class="text-accent-coral shrink-0" /><span class="text-sm text-ink-primary truncate">{{ p.name }}</span></div>
                            <span class="text-xs font-semibold text-accent-coral shrink-0">{{ p.stock }} pcs</span>
                        </div>
                    </div>
                </ListCard>
                <ListCard title="Slow Moving" subtitle="Tidak terjual 30 hari" icon="package" :is-empty="slowMoving.length === 0">
                    <div class="divide-y divide-border-soft">
                        <div v-for="(p, i) in slowMoving.slice(0,5)" :key="i" class="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0">
                            <div class="flex items-center gap-2 min-w-0"><Icon name="package" size="4" class="text-ink-muted shrink-0" /><span class="text-sm text-ink-primary truncate">{{ p.name }}</span></div>
                            <span class="text-xs font-semibold text-ink-muted shrink-0">{{ p.stock }} stok</span>
                        </div>
                    </div>
                </ListCard>
            </div>
        </div>
    </DashboardLayout>
</template>
