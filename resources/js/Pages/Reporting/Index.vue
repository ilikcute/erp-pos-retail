<script setup>
import { ref, onMounted } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import DataTable from '@/Components/Table/DataTable.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import KPICard from '@/Components/Dashboard/KPICard.vue';
import { Head } from '@inertiajs/vue3';
import { formatCurrency, formatDate } from '@/Utils/formatters';
import axios from 'axios';

const activeTab = ref('sales');

// Date range filters
const dateFrom = ref(new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0]);
const dateTo = ref(new Date().toISOString().split('T')[0]);

const salesReport = ref(null);
const inventoryReport = ref(null);
const financialReport = ref(null);
const purchaseReport = ref(null);

const loadingSales = ref(false);
const loadingInventory = ref(false);
const loadingFinancial = ref(false);
const loadingPurchase = ref(false);

// Formatters imported from @/Utils/formatters

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

const fetchPurchaseReport = async () => {
    loadingPurchase.value = true;
    try {
        const response = await axios.get('/api/v1/reports/purchasing/orders', {
            params: {
                date_from: dateFrom.value,
                date_to: dateTo.value,
            }
        });
        if (response.data.success) {
            purchaseReport.value = response.data.data;
        }
    } catch (error) {
        console.error('Error loading purchase report:', error);
    } finally {
        loadingPurchase.value = false;
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
    fetchPurchaseReport();
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

const poColumns = [
    { key: 'no', label: 'No' },
    { key: 'po_number', label: 'Nomor PO' },
    { key: 'supplier', label: 'Pemasok' },
    { key: 'status', label: 'Status', align: 'center' },
    { key: 'total_amount', label: 'Total', align: 'right' },
    { key: 'date', label: 'Tanggal' },
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
            <button @click="activeTab = 'purchasing'; fetchPurchaseReport()" :class="['chip whitespace-nowrap px-lg py-sm', activeTab==='purchasing' ? 'bg-brand text-white shadow-brand-glow' : 'bg-surface-card text-ink-secondary border border-border-soft hover:border-brand-border']">🛍️ Pembelian</button>
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
                <DataTable :columns="txColumns" :rows="salesReport.transactions" :loading="loadingSales">
                    <template #cell-cashier="{ value }">
                        <span>{{ value?.name || 'Unknown' }}</span>
                    </template>
                    <template #cell-customer="{ value }">
                        <span>{{ value?.name || 'Umum' }}</span>
                    </template>
                </DataTable>
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

        <!-- ===== PURCHASING ===== -->
        <template v-if="activeTab === 'purchasing' && purchaseReport">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-base mb-xl">
                <KPICard label="Total Pembelian" :value="formatCurrency(purchaseReport.total_purchases)" icon="shopping-bag" color="brand" />
                <KPICard label="Total Pesanan (PO)" :value="purchaseReport.total_orders" icon="file-text" color="mint" />
                <KPICard label="Hutang Belum Dibayar" :value="formatCurrency(purchaseReport.pending_payables)" icon="alert-circle" color="coral" />
            </div>
            <div class="card-friendly p-lg">
                <h2 class="text-section-title font-bold text-ink-primary mb-base">Daftar Pesanan Pembelian 📦</h2>
                <DataTable :columns="poColumns" :rows="purchaseReport.orders" :loading="loadingPurchase">
                    <template #cell-total_amount="{ value }">
                        <span>{{ formatCurrency(value) }}</span>
                    </template>
                    <template #cell-status="{ value }">
                        <span :class="['px-2 py-0.5 rounded text-xs font-semibold', value === 'COMPLETED' ? 'bg-accent-mint-soft text-accent-mint' : (value === 'PENDING' ? 'bg-accent-sunny-soft text-accent-sunny' : 'bg-surface-subtle text-ink-secondary')]">{{ value }}</span>
                    </template>
                </DataTable>
            </div>
        </template>
    </DashboardLayout>
</template>