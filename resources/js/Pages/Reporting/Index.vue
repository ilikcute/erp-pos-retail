<script setup>
import { ref, onMounted } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import DataTable from '@/Components/Table/DataTable.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import KPICard from '@/Components/Dashboard/KPICard.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';

const activeTab = ref('sales');

// Date range filters
const dateFrom = ref(new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0]);
const dateTo = ref(new Date().toISOString().split('T')[0]);

// Report state data

// --- Data contoh (fallback bila API belum tersedia) ---
const MOCK_SALES = {
    total_sales: 18450000, total_transactions: 312, average_sale: 59134, total_items_sold: 874,
    payment_summary: { Tunai: 9200000, QRIS: 5100000, Kartu: 3150000, Transfer: 1000000 },
    transactions: [
        { no: 1, transaction_no: 'TRX-1001', cashier: 'Kasir 01', customer: 'Umum',      grand_total: 'Rp 125.000', transaction_date: '2026-06-25 09:12' },
        { no: 2, transaction_no: 'TRX-1002', cashier: 'Kasir 01', customer: 'Budi S.',    grand_total: 'Rp 78.000',  transaction_date: '2026-06-25 09:31' },
        { no: 3, transaction_no: 'TRX-1003', cashier: 'Kasir 02', customer: 'Umum',      grand_total: 'Rp 240.000', transaction_date: '2026-06-25 10:05' },
        { no: 4, transaction_no: 'TRX-1004', cashier: 'Kasir 02', customer: 'Sari D.',    grand_total: 'Rp 56.000',  transaction_date: '2026-06-25 10:48' },
    ],
};
const MOCK_INVENTORY = {
    total_items: 1245, total_value: 312500000,
    low_stock_items: [
        { no: 1, product: 'Kopi Susu Gula Aren', location: 'Gudang A', quantity_on_hand: 5,  reorder_level: 20, balance_value: 'Rp 110.000' },
        { no: 2, product: 'Air Mineral 600ml',   location: 'Gudang A', quantity_on_hand: 8,  reorder_level: 50, balance_value: 'Rp 48.000' },
        { no: 3, product: 'Donat Gula',          location: 'Gudang B', quantity_on_hand: 3,  reorder_level: 15, balance_value: 'Rp 36.000' },
    ],
};
const MOCK_FINANCIAL = {
    summary: { total_assets: 312500000, total_revenue: 184500000, net_income: 42300000 },
    income_statement: {
        revenue:  [{ account_code: '4-100', account_name: 'Pendapatan Penjualan', balance: 184500000 }],
        expenses: [{ account_code: '5-100', account_name: 'Harga Pokok Penjualan', balance: 106000000 }, { account_code: '6-100', account_name: 'Beban Operasional', balance: 36200000 }],
        total_revenue: 184500000, total_expenses: 142200000, net_income: 42300000,
    },
    balance_sheet: {
        assets:      [{ account_code: '1-100', account_name: 'Kas & Bank', balance: 95000000 }, { account_code: '1-200', account_name: 'Persediaan', balance: 217500000 }],
        liabilities: [{ account_code: '2-100', account_name: 'Hutang Usaha', balance: 78000000 }],
        equity:      [{ account_code: '3-100', account_name: 'Modal Pemilik', balance: 234500000 }],
        total_assets: 312500000, total_liabilities: 78000000, total_equity: 234500000,
    },
};
const salesReport = ref(MOCK_SALES);
const inventoryReport = ref(MOCK_INVENTORY);
const financialReport = ref(MOCK_FINANCIAL);

const loadingSales = ref(false);
const loadingInventory = ref(false);
const loadingFinancial = ref(false);

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value || 0);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID');
};

// ── API Fetchers ───────────────────────────────────────────────

const fetchSalesReport = async () => {
    loadingSales.value = true;
    try {
        const response = await axios.get('/api/v1/reports/sales', {
            params: {
                date_from: dateFrom.value,
                date_to: dateTo.value,
            }
        });
        if (response.data.success) {
            salesReport.value = response.data.data;
        }
    } catch (error) {
        console.error('Error loading sales report:', error);
    } finally {
        loadingSales.value = false;
    }
};

const fetchInventoryReport = async () => {
    loadingInventory.value = true;
    try {
        const response = await axios.get('/api/v1/reports/inventory-valuation', {
            params: {
                date_from: dateFrom.value,
                date_to: dateTo.value,
            }
        });
        if (response.data.success) {
            inventoryReport.value = response.data.data;
        }
    } catch (error) {
        console.error('Error loading inventory report:', error);
    } finally {
        loadingInventory.value = false;
    }
};

const fetchFinancialReport = async () => {
    loadingFinancial.value = true;
    try {
        const response = await axios.get('/api/v1/reports/financial', {
            params: {
                date_from: dateFrom.value,
                date_to: dateTo.value,
            }
        });
        if (response.data.success) {
            financialReport.value = response.data.data;
        }
    } catch (error) {
        console.error('Error loading financial report:', error);
    } finally {
        loadingFinancial.value = false;
    }
};

// Export Handlers
const handleExport = async (type) => {
    try {
        const response = await axios.post(`/api/v1/reports/${type}/export`, {
            date_from: dateFrom.value,
            date_to: dateTo.value,
        });
        alert(`📥 ${response.data.message || 'Export berhasil dimulai.'}`);
    } catch (error) {
        alert('❌ Gagal melakukan export data.');
    }
};

// Load initial tab data
onMounted(() => {
    fetchSalesReport();
    fetchInventoryReport();
    fetchFinancialReport();
});

// Table columns setup
const txColumns = [
    { key: 'no', label: 'No' },
    { key: 'transaction_no', label: 'Order No' },
    { key: 'cashier', label: 'Kasir' },
    { key: 'customer', label: 'Pelanggan' },
    { key: 'grand_total', label: 'Total', align: 'right' },
    { key: 'transaction_date', label: 'Tanggal' },
];

const lowStockColumns = [
    { key: 'no', label: 'No' },
    { key: 'product', label: 'Product' },
    { key: 'location', label: 'Location' },
    { key: 'quantity_on_hand', label: 'Stok Saat Ini', align: 'right' },
    { key: 'reorder_level', label: 'Min Reorder', align: 'right' },
    { key: 'balance_value', label: 'Nilai Aset', align: 'right' },
];
</script>

<template>
    <Head title="Reporting & Analytics" />
    <DashboardLayout>
        <!-- Header banner -->
        <div class="rounded-card bg-grape-gradient text-white p-xl mb-xl shadow-card flex items-center justify-between flex-wrap gap-base">
            <div>
                <h1 class="text-page-title font-extrabold">Laporan & Analitik 📊</h1>
                <p class="text-white/80 mt-xs text-base">Pantau penjualan, persediaan, dan keuangan toko Anda.</p>
            </div>
            <div class="flex items-center gap-sm flex-wrap">
                <div class="flex items-center gap-xs bg-white/15 rounded-pill px-md py-xs backdrop-blur">
                    <input v-model="dateFrom" type="date" class="bg-transparent text-sm text-white placeholder-white/70 border-0 focus:ring-0 outline-none" />
                    <span class="text-white/70">—</span>
                    <input v-model="dateTo" type="date" class="bg-transparent text-sm text-white border-0 focus:ring-0 outline-none" />
                </div>
                <BaseButton variant="soft" size="sm" @click="handleExport(activeTab)">⬇️ Export</BaseButton>
            </div>
        </div>

        <!-- Tabs -->
        <div class="flex gap-sm mb-xl overflow-x-auto scroll-soft pb-xs">
            <button @click="activeTab = 'sales'; fetchSalesReport()" :class="['chip whitespace-nowrap px-lg py-sm', activeTab==='sales' ? 'bg-brand text-white shadow-brand-glow' : 'bg-surface-card text-ink-secondary border border-border-soft hover:border-brand-border']">🛒 Penjualan</button>
            <button @click="activeTab = 'inventory'; fetchInventoryReport()" :class="['chip whitespace-nowrap px-lg py-sm', activeTab==='inventory' ? 'bg-brand text-white shadow-brand-glow' : 'bg-surface-card text-ink-secondary border border-border-soft hover:border-brand-border']">📦 Persediaan</button>
            <button @click="activeTab = 'financial'; fetchFinancialReport()" :class="['chip whitespace-nowrap px-lg py-sm', activeTab==='financial' ? 'bg-brand text-white shadow-brand-glow' : 'bg-surface-card text-ink-secondary border border-border-soft hover:border-brand-border']">💰 Keuangan</button>
        </div>

        <!-- ===== SALES ===== -->
        <template v-if="activeTab === 'sales' && salesReport">
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-base mb-xl">
                <KPICard label="Total Penjualan" :value="formatCurrency(salesReport.total_sales)" icon="dollar-sign" color="brand" />
                <KPICard label="Transaksi" :value="salesReport.total_transactions" icon="file-text" color="mint" />
                <KPICard label="Rata-rata / Transaksi" :value="formatCurrency(salesReport.average_sale)" icon="trending-up" color="sunny" />
                <KPICard label="Item Terjual" :value="salesReport.total_items_sold" icon="package" color="grape" />
            </div>
            <div class="card-friendly p-lg mb-xl">
                <h2 class="text-section-title font-bold text-ink-primary mb-base">Metode Pembayaran 💳</h2>
                <div class="flex flex-wrap gap-sm">
                    <div v-for="(amount, method) in salesReport.payment_summary" :key="method" class="chip bg-accent-sky-soft text-accent-sky px-lg py-sm">
                        <span class="font-semibold">{{ method }}</span><span class="ml-sm font-bold">{{ formatCurrency(amount) }}</span>
                    </div>
                </div>
            </div>
            <div class="card-friendly p-lg">
                <h2 class="text-section-title font-bold text-ink-primary mb-base">Daftar Transaksi 🧾</h2>
                <DataTable :columns="txColumns" :rows="salesReport.transactions" :loading="loadingSales" />
            </div>
        </template>

        <!-- ===== INVENTORY ===== -->
        <template v-if="activeTab === 'inventory' && inventoryReport">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-base mb-xl">
                <KPICard label="Total Item" :value="inventoryReport.total_items" icon="package" color="mint" />
                <KPICard label="Total Nilai Aset" :value="formatCurrency(inventoryReport.total_value)" icon="dollar-sign" color="brand" />
                <KPICard label="Stok Menipis" :value="inventoryReport.low_stock_items.length" icon="alert-triangle" color="sunny" />
            </div>
            <div class="card-friendly p-lg">
                <h2 class="text-section-title font-bold text-ink-primary mb-base">Produk Perlu Reorder 🔻</h2>
                <DataTable :columns="lowStockColumns" :rows="inventoryReport.low_stock_items" :loading="loadingInventory" />
            </div>
        </template>

        <!-- ===== FINANCIAL ===== -->
        <template v-if="activeTab === 'financial' && financialReport">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-base mb-xl">
                <KPICard label="Total Aset" :value="formatCurrency(financialReport.summary.total_assets)" icon="dollar-sign" color="brand" />
                <KPICard label="Pendapatan" :value="formatCurrency(financialReport.summary.total_revenue)" icon="trending-up" color="mint" />
                <KPICard label="Laba Bersih" :value="formatCurrency(financialReport.summary.net_income)" icon="trending-up" color="grape" />
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-base">
                <!-- Income statement -->
                <div class="card-friendly p-lg">
                    <h2 class="text-section-title font-bold text-ink-primary mb-base">Laporan Laba Rugi 📑</h2>
                    <p class="text-xs font-bold text-accent-mint uppercase tracking-wide mb-sm">Pendapatan</p>
                    <div v-for="r in financialReport.income_statement.revenue" :key="r.account_code" class="flex justify-between text-sm py-xs"><span class="text-ink-secondary">{{ r.account_name }}</span><span class="font-medium">{{ formatCurrency(r.balance) }}</span></div>
                    <div class="flex justify-between text-sm font-bold py-xs border-t border-border-soft mt-xs"><span>Total Pendapatan</span><span>{{ formatCurrency(financialReport.income_statement.total_revenue) }}</span></div>
                    <p class="text-xs font-bold text-semantic-danger uppercase tracking-wide mb-sm mt-base">Beban</p>
                    <div v-for="e in financialReport.income_statement.expenses" :key="e.account_code" class="flex justify-between text-sm py-xs"><span class="text-ink-secondary">{{ e.account_name }}</span><span class="font-medium">{{ formatCurrency(e.balance) }}</span></div>
                    <div class="flex justify-between text-sm font-bold py-xs border-t border-border-soft mt-xs"><span>Total Beban</span><span>{{ formatCurrency(financialReport.income_statement.total_expenses) }}</span></div>
                    <div class="flex justify-between text-card-title font-extrabold py-md mt-sm rounded-lg bg-accent-mint-soft px-md text-accent-mint"><span>Laba Bersih</span><span>{{ formatCurrency(financialReport.income_statement.net_income) }}</span></div>
                </div>
                <!-- Balance sheet -->
                <div class="card-friendly p-lg">
                    <h2 class="text-section-title font-bold text-ink-primary mb-base">Neraca ⚖️</h2>
                    <p class="text-xs font-bold text-accent-sky uppercase tracking-wide mb-sm">Aset</p>
                    <div v-for="a in financialReport.balance_sheet.assets" :key="a.account_code" class="flex justify-between text-sm py-xs"><span class="text-ink-secondary">{{ a.account_name }}</span><span class="font-medium">{{ formatCurrency(a.balance) }}</span></div>
                    <div class="flex justify-between text-sm font-bold py-xs border-t border-border-soft mt-xs"><span>Total Aset</span><span>{{ formatCurrency(financialReport.balance_sheet.total_assets) }}</span></div>
                    <p class="text-xs font-bold text-accent-coral uppercase tracking-wide mb-sm mt-base">Kewajiban</p>
                    <div v-for="l in financialReport.balance_sheet.liabilities" :key="l.account_code" class="flex justify-between text-sm py-xs"><span class="text-ink-secondary">{{ l.account_name }}</span><span class="font-medium">{{ formatCurrency(l.balance) }}</span></div>
                    <div class="flex justify-between text-sm font-bold py-xs border-t border-border-soft mt-xs"><span>Total Kewajiban</span><span>{{ formatCurrency(financialReport.balance_sheet.total_liabilities) }}</span></div>
                    <p class="text-xs font-bold text-accent-grape uppercase tracking-wide mb-sm mt-base">Ekuitas</p>
                    <div v-for="q in financialReport.balance_sheet.equity" :key="q.account_code" class="flex justify-between text-sm py-xs"><span class="text-ink-secondary">{{ q.account_name }}</span><span class="font-medium">{{ formatCurrency(q.balance) }}</span></div>
                    <div class="flex justify-between text-card-title font-extrabold py-md mt-sm rounded-lg bg-brand-soft px-md text-brand"><span>Total Ekuitas</span><span>{{ formatCurrency(financialReport.balance_sheet.total_equity) }}</span></div>
                </div>
            </div>
        </template>
    </DashboardLayout>
</template>