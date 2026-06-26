<script setup>
import { ref, onMounted } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import DataTable from '@/Components/Table/DataTable.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';

const activeTab = ref('sales');

// Date range filters
const dateFrom = ref(new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0]);
const dateTo = ref(new Date().toISOString().split('T')[0]);

// Report state data
const salesReport = ref(null);
const inventoryReport = ref(null);
const financialReport = ref(null);

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
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-ink-primary">
                    Reporting & Analytics
                </h1>
                <p class="text-ink-secondary">
                    Laporan analisa penjualan, persediaan stok barang, serta laporan keuangan (GL & Balance Sheet)
                </p>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="bg-surface-card rounded-card border border-border-soft p-5 shadow-card mb-6">
            <h3 class="text-xs font-bold uppercase tracking-wider text-ink-secondary mb-3">Filter Jangkauan Laporan</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block text-[11px] font-semibold text-ink-secondary mb-1.5 uppercase">Dari Tanggal</label>
                    <input
                        v-model="dateFrom"
                        type="date"
                        class="w-full px-base py-md rounded-md border border-border-strong bg-white text-ink-primary focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent text-sm"
                    />
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-ink-secondary mb-1.5 uppercase">Sampai Tanggal</label>
                    <input
                        v-model="dateTo"
                        type="date"
                        class="w-full px-base py-md rounded-md border border-border-strong bg-white text-ink-primary focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent text-sm"
                    />
                </div>
                <div>
                    <BaseButton
                        @click="activeTab === 'sales' ? fetchSalesReport() : activeTab === 'inventory' ? fetchInventoryReport() : fetchFinancialReport()"
                        class="w-full"
                    >
                        Filter Data
                    </BaseButton>
                </div>
                <div>
                    <BaseButton
                        @click="handleExport(activeTab)"
                        variant="secondary"
                        class="w-full border border-border-soft bg-surface-main hover:bg-border-soft text-ink-primary"
                    >
                        Export Excel
                    </BaseButton>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="flex space-x-4 mb-6 border-b border-border-soft">
            <button
                @click="activeTab = 'sales'"
                :class="activeTab === 'sales' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-2 px-4 font-medium transition-colors cursor-pointer"
            >
                Sales Report
            </button>
            <button
                @click="activeTab = 'inventory'"
                :class="activeTab === 'inventory' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-2 px-4 font-medium transition-colors cursor-pointer"
            >
                Inventory Valuation
            </button>
            <button
                @click="activeTab = 'financial'"
                :class="activeTab === 'financial' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-2 px-4 font-medium transition-colors cursor-pointer"
            >
                Financial Report
            </button>
        </div>

        <!-- ═════════════════ TAB 1: SALES REPORT ═════════════════ -->
        <div v-if="activeTab === 'sales'" class="space-y-6">
            <!-- Loading Indicator -->
            <div v-if="loadingSales" class="py-10 text-center text-ink-secondary">
                Memproses Laporan Penjualan...
            </div>

            <template v-else-if="salesReport">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-surface-card rounded-card border border-border-soft p-5 shadow-card">
                        <p class="text-xs font-semibold text-ink-secondary uppercase tracking-wider">Total Omset Penjualan</p>
                        <p class="text-2xl font-bold font-mono text-brand mt-1">{{ formatCurrency(salesReport.total_sales) }}</p>
                    </div>
                    <div class="bg-surface-card rounded-card border border-border-soft p-5 shadow-card">
                        <p class="text-xs font-semibold text-ink-secondary uppercase tracking-wider">Total Transaksi</p>
                        <p class="text-2xl font-bold font-mono text-ink-primary mt-1">{{ salesReport.total_transactions }}</p>
                    </div>
                    <div class="bg-surface-card rounded-card border border-border-soft p-5 shadow-card">
                        <p class="text-xs font-semibold text-ink-secondary uppercase tracking-wider">Rata-rata per Struk</p>
                        <p class="text-2xl font-bold font-mono text-ink-primary mt-1">{{ formatCurrency(salesReport.average_sale) }}</p>
                    </div>
                    <div class="bg-surface-card rounded-card border border-border-soft p-5 shadow-card">
                        <p class="text-xs font-semibold text-ink-secondary uppercase tracking-wider">Item Terjual</p>
                        <p class="text-2xl font-bold font-mono text-ink-primary mt-1">{{ salesReport.total_items_sold || 0 }} pcs</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Transactions List Table -->
                    <div class="lg:col-span-2 bg-surface-card rounded-card border border-border-soft p-5 shadow-card">
                        <h3 class="text-sm font-bold text-ink-primary mb-4">Rincian Nota Penjualan</h3>
                        <DataTable :columns="txColumns" :rows="salesReport.transactions">
                            <template #cell-transaction_no="{ value }">
                                <span class="font-mono text-ink-primary font-semibold">{{ value }}</span>
                            </template>
                            <template #cell-cashier="{ row }">
                                <span>{{ row.cashier?.name || '-' }}</span>
                            </template>
                            <template #cell-customer="{ row }">
                                <span>{{ row.customer?.name || 'Walk-in Customer' }}</span>
                            </template>
                            <template #cell-grand_total="{ value }">
                                <span class="font-mono text-ink-primary font-medium">{{ formatCurrency(value) }}</span>
                            </template>
                            <template #cell-transaction_date="{ value }">
                                <span>{{ formatDate(value) }}</span>
                            </template>
                        </DataTable>
                    </div>

                    <!-- Payment Summary Box -->
                    <div class="lg:col-span-1 bg-surface-card rounded-card border border-border-soft p-5 shadow-card">
                        <h3 class="text-sm font-bold text-ink-primary mb-4">Ringkasan Metode Pembayaran</h3>
                        <div class="divide-y divide-border-soft">
                            <div v-for="(amount, method) in salesReport.payment_summary" :key="method" class="py-3 flex justify-between items-center">
                                <span class="text-sm text-ink-secondary font-medium">{{ method }}</span>
                                <span class="font-mono font-bold text-ink-primary">{{ formatCurrency(amount) }}</span>
                            </div>
                            <div v-if="!salesReport.payment_summary || Object.keys(salesReport.payment_summary).length === 0" class="py-4 text-center text-ink-secondary">
                                Tidak ada data pembayaran.
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- ═════════════════ TAB 2: INVENTORY REPORT ═════════════════ -->
        <div v-if="activeTab === 'inventory'" class="space-y-6">
            <div v-if="loadingInventory" class="py-10 text-center text-ink-secondary">
                Memuat Laporan Inventaris...
            </div>

            <template v-else-if="inventoryReport">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-surface-card rounded-card border border-border-soft p-5 shadow-card">
                        <p class="text-xs font-semibold text-ink-secondary uppercase tracking-wider">Total Ragam Produk</p>
                        <p class="text-2xl font-bold font-mono text-brand mt-1">{{ inventoryReport.total_items }} SKU</p>
                    </div>
                    <div class="bg-surface-card rounded-card border border-border-soft p-5 shadow-card">
                        <p class="text-xs font-semibold text-ink-secondary uppercase tracking-wider">Total Nilai Persediaan Stok</p>
                        <p class="text-2xl font-bold font-mono text-brand mt-1">{{ formatCurrency(inventoryReport.total_value) }}</p>
                    </div>
                </div>

                <!-- Low Stock Items Table -->
                <div class="bg-surface-card rounded-card border border-border-soft p-5 shadow-card">
                    <h3 class="text-sm font-bold text-semantic-danger mb-4 flex items-center gap-xs">
                        ⚠️ Produk Dengan Stok Kritis (Mendekati Batas Reorder)
                    </h3>
                    <DataTable :columns="lowStockColumns" :rows="inventoryReport.low_stock_items">
                        <template #cell-product="{ row }">
                            <span class="font-medium text-ink-primary">{{ row.product?.name || '-' }}</span>
                        </template>
                        <template #cell-location="{ row }">
                            <span>{{ row.location?.name || '-' }}</span>
                        </template>
                        <template #cell-quantity_on_hand="{ value }">
                            <span class="font-mono text-semantic-danger font-bold">{{ value }}</span>
                        </template>
                        <template #cell-reorder_level="{ value }">
                            <span class="font-mono text-ink-secondary">{{ value }}</span>
                        </template>
                        <template #cell-balance_value="{ value }">
                            <span class="font-mono text-ink-primary">{{ formatCurrency(value) }}</span>
                        </template>
                    </DataTable>
                </div>
            </template>
        </div>

        <!-- ═════════════════ TAB 3: FINANCIAL REPORT ═════════════════ -->
        <div v-if="activeTab === 'financial'" class="space-y-6">
            <div v-if="loadingFinancial" class="py-10 text-center text-ink-secondary">
                Memuat Laporan Keuangan...
            </div>

            <template v-else-if="financialReport">
                <!-- Summary Card -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-surface-card rounded-card border border-border-soft p-5 shadow-card">
                        <p class="text-xs font-semibold text-ink-secondary uppercase tracking-wider">Total Nilai Aset</p>
                        <p class="text-2xl font-bold font-mono text-brand mt-1">{{ formatCurrency(financialReport.summary.total_assets) }}</p>
                    </div>
                    <div class="bg-surface-card rounded-card border border-border-soft p-5 shadow-card">
                        <p class="text-xs font-semibold text-ink-secondary uppercase tracking-wider">Pendapatan Bersih</p>
                        <p class="text-2xl font-bold font-mono text-brand mt-1">{{ formatCurrency(financialReport.summary.total_revenue) }}</p>
                    </div>
                    <div class="bg-surface-card rounded-card border border-border-soft p-5 shadow-card">
                        <p class="text-xs font-semibold text-ink-secondary uppercase tracking-wider">Laba Bersih (Net Income)</p>
                        <p :class="financialReport.summary.net_income >= 0 ? 'text-semantic-success' : 'text-semantic-danger'" class="text-2xl font-bold font-mono mt-1">
                            {{ formatCurrency(financialReport.summary.net_income) }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Balance Sheet Card -->
                    <div class="bg-surface-card rounded-card border border-border-soft p-6 shadow-card space-y-6">
                        <div class="border-b border-border-soft pb-3">
                            <h3 class="text-lg font-bold text-ink-primary">Neraca Keuangan (Balance Sheet)</h3>
                            <p class="text-xs text-ink-secondary mt-0.5">Asset, Kewajiban & Ekuitas</p>
                        </div>
                        
                        <div class="space-y-4 text-sm">
                            <!-- Assets -->
                            <div>
                                <h4 class="font-bold text-brand text-xs uppercase tracking-wider mb-2">Aset (Assets)</h4>
                                <div class="divide-y divide-border-soft bg-surface-main p-3 rounded-md">
                                    <div v-for="item in financialReport.balance_sheet.assets" :key="item.account_code" class="py-2 flex justify-between font-mono">
                                        <span class="text-ink-secondary text-xs">{{ item.account_code }} - {{ item.account_name }}</span>
                                        <span class="font-semibold text-xs">{{ formatCurrency(item.balance) }}</span>
                                    </div>
                                    <div class="py-2 border-t border-border-soft flex justify-between font-bold text-ink-primary">
                                        <span>TOTAL ASET:</span>
                                        <span>{{ formatCurrency(financialReport.balance_sheet.total_assets) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Liabilities -->
                            <div>
                                <h4 class="font-bold text-brand text-xs uppercase tracking-wider mb-2">Kewajiban (Liabilities)</h4>
                                <div class="divide-y divide-border-soft bg-surface-main p-3 rounded-md">
                                    <div v-for="item in financialReport.balance_sheet.liabilities" :key="item.account_code" class="py-2 flex justify-between font-mono">
                                        <span class="text-ink-secondary text-xs">{{ item.account_code }} - {{ item.account_name }}</span>
                                        <span class="font-semibold text-xs">{{ formatCurrency(item.balance) }}</span>
                                    </div>
                                    <div class="py-2 border-t border-border-soft flex justify-between font-bold text-ink-primary">
                                        <span>TOTAL KEWAJIBAN:</span>
                                        <span>{{ formatCurrency(financialReport.balance_sheet.total_liabilities) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Equity -->
                            <div>
                                <h4 class="font-bold text-brand text-xs uppercase tracking-wider mb-2">Ekuitas Modal (Equity)</h4>
                                <div class="divide-y divide-border-soft bg-surface-main p-3 rounded-md">
                                    <div v-for="item in financialReport.balance_sheet.equity" :key="item.account_code" class="py-2 flex justify-between font-mono">
                                        <span class="text-ink-secondary text-xs">{{ item.account_code }} - {{ item.account_name }}</span>
                                        <span class="font-semibold text-xs">{{ formatCurrency(item.balance) }}</span>
                                    </div>
                                    <div class="py-2 border-t border-border-soft flex justify-between font-bold text-ink-primary">
                                        <span>TOTAL EKUITAS:</span>
                                        <span>{{ formatCurrency(financialReport.balance_sheet.total_equity) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Income Statement Card -->
                    <div class="bg-surface-card rounded-card border border-border-soft p-6 shadow-card space-y-6">
                        <div class="border-b border-border-soft pb-3">
                            <h3 class="text-lg font-bold text-ink-primary">Laporan Laba Rugi (Income Statement)</h3>
                            <p class="text-xs text-ink-secondary mt-0.5">Pendapatan vs Pengeluaran Operasional</p>
                        </div>
                        
                        <div class="space-y-4 text-sm">
                            <!-- Revenue -->
                            <div>
                                <h4 class="font-bold text-brand text-xs uppercase tracking-wider mb-2">Pendapatan Usaha (Revenue)</h4>
                                <div class="divide-y divide-border-soft bg-surface-main p-3 rounded-md font-mono">
                                    <div v-for="item in financialReport.income_statement.revenue" :key="item.account_code" class="py-2 flex justify-between">
                                        <span class="text-ink-secondary text-xs">{{ item.account_code }} - {{ item.account_name }}</span>
                                        <span class="font-semibold text-xs">{{ formatCurrency(item.balance) }}</span>
                                    </div>
                                    <div class="py-2 border-t border-border-soft flex justify-between font-bold text-ink-primary">
                                        <span>TOTAL REVENUE:</span>
                                        <span>{{ formatCurrency(financialReport.income_statement.total_revenue) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Expenses -->
                            <div>
                                <h4 class="font-bold text-brand text-xs uppercase tracking-wider mb-2">Beban Pengeluaran (Expenses)</h4>
                                <div class="divide-y divide-border-soft bg-surface-main p-3 rounded-md font-mono">
                                    <div v-for="item in financialReport.income_statement.expenses" :key="item.account_code" class="py-2 flex justify-between">
                                        <span class="text-ink-secondary text-xs">{{ item.account_code }} - {{ item.account_name }}</span>
                                        <span class="font-semibold text-xs">{{ formatCurrency(item.balance) }}</span>
                                    </div>
                                    <div class="py-2 border-t border-border-soft flex justify-between font-bold text-ink-primary">
                                        <span>TOTAL BEBAN:</span>
                                        <span>{{ formatCurrency(financialReport.income_statement.total_expenses) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Net Income -->
                            <div class="p-4 rounded-md border border-border-soft flex justify-between items-center" :class="financialReport.income_statement.net_income >= 0 ? 'bg-semantic-success-soft/20 text-semantic-success' : 'bg-semantic-danger-soft/20 text-semantic-danger'">
                                <span class="font-extrabold uppercase text-xs tracking-wider">LABA / RUGI BERSIH:</span>
                                <span class="text-lg font-extrabold font-mono">{{ formatCurrency(financialReport.income_statement.net_income) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </DashboardLayout>
</template>
