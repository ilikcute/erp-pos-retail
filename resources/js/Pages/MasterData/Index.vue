<script setup>
import { ref, computed, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import DataTable from '@/Components/Table/DataTable.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';

const props = defineProps({
    suppliers: { type: Array, default: () => [] },
    customers: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] }, // Customer Categories
    currencies: { type: Array, default: () => [] },
    taxes: { type: Array, default: () => [] },
    units: { type: Array, default: () => [] },
    conversions: { type: Array, default: () => [] },
    priceLists: { type: Array, default: () => [] },
    activeTab: { type: String, default: 'suppliers' },
});

const activeTab = ref(props.activeTab);
const searchQuery = ref('');

// Watch activeTab changes to navigate with Inertia for proper data loading
watch(activeTab, (newTab) => {
    searchQuery.value = ''; // Reset search on tab change
    router.visit(`/master-data/${newTab}`, { preserveState: true, replace: true });
});

// Sync tab if props change
watch(() => props.activeTab, (newVal) => {
    activeTab.value = newVal;
});

// local filtered rows
const filteredRows = computed(() => {
    const query = searchQuery.value.toLowerCase();
    if (!query) {
        if (activeTab.value === 'suppliers') return props.suppliers;
        if (activeTab.value === 'customers') return props.customers;
        if (activeTab.value === 'customer-categories') return props.categories;
        if (activeTab.value === 'currencies') return props.currencies;
        if (activeTab.value === 'taxes') return props.taxes;
        if (activeTab.value === 'units') return props.units;
        if (activeTab.value === 'unit-conversions') return props.conversions;
        if (activeTab.value === 'price-lists') return props.priceLists;
        return [];
    }

    if (activeTab.value === 'suppliers') {
        return props.suppliers.filter(s => 
            s.supplier_name?.toLowerCase().includes(query) ||
            s.supplier_code?.toLowerCase().includes(query) ||
            s.contact_person?.toLowerCase().includes(query)
        );
    }
    if (activeTab.value === 'customers') {
        return props.customers.filter(c => 
            c.customer_name?.toLowerCase().includes(query) ||
            c.customer_code?.toLowerCase().includes(query) ||
            c.phone?.toLowerCase().includes(query)
        );
    }
    if (activeTab.value === 'customer-categories') {
        return props.categories.filter(c => 
            c.category_name?.toLowerCase().includes(query) ||
            c.category_code?.toLowerCase().includes(query)
        );
    }
    if (activeTab.value === 'currencies') {
        return props.currencies.filter(c => 
            c.name?.toLowerCase().includes(query) ||
            c.code?.toLowerCase().includes(query)
        );
    }
    if (activeTab.value === 'taxes') {
        return props.taxes.filter(t => 
            t.tax_name?.toLowerCase().includes(query) ||
            t.tax_code?.toLowerCase().includes(query)
        );
    }
    if (activeTab.value === 'units') {
        return props.units.filter(u => 
            u.unit_name?.toLowerCase().includes(query) ||
            u.unit_code?.toLowerCase().includes(query)
        );
    }
    if (activeTab.value === 'unit-conversions') {
        return props.conversions.filter(c => 
            c.from_unit?.unit_name?.toLowerCase().includes(query) ||
            c.to_unit?.unit_name?.toLowerCase().includes(query)
        );
    }
    if (activeTab.value === 'price-lists') {
        return props.priceLists.filter(p => 
            p.price_list_name?.toLowerCase().includes(query) ||
            p.price_list_code?.toLowerCase().includes(query)
        );
    }
    return [];
});

// Column Configurations
const supplierColumns = [
    { key: 'no', label: 'No' },
    { key: 'supplier_code', label: 'Code' },
    { key: 'supplier_name', label: 'Supplier Name' },
    { key: 'contact_person', label: 'Contact Person' },
    { key: 'phone', label: 'Phone' },
    { key: 'email', label: 'Email' },
    { key: 'status', label: 'Status', align: 'center' },
];

const customerColumns = [
    { key: 'no', label: 'No' },
    { key: 'customer_code', label: 'Code' },
    { key: 'customer_name', label: 'Customer Name' },
    { key: 'phone', label: 'Phone' },
    { key: 'email', label: 'Email' },
    { key: 'credit_limit', label: 'Credit Limit', align: 'right' },
    { key: 'status', label: 'Status', align: 'center' },
];

const categoryColumns = [
    { key: 'no', label: 'No' },
    { key: 'category_code', label: 'Category Code' },
    { key: 'category_name', label: 'Category Name' },
    { key: 'description', label: 'Description' },
    { key: 'status', label: 'Status', align: 'center' },
];

const currencyColumns = [
    { key: 'no', label: 'No' },
    { key: 'code', label: 'Currency Code' },
    { key: 'name', label: 'Currency Name' },
    { key: 'symbol', label: 'Symbol', align: 'center' },
    { key: 'exchange_rate', label: 'Exchange Rate', align: 'right' },
    { key: 'is_base', label: 'Base Currency', align: 'center' },
    { key: 'status', label: 'Status', align: 'center' },
];

const taxColumns = [
    { key: 'no', label: 'No' },
    { key: 'tax_code', label: 'Tax Code' },
    { key: 'tax_name', label: 'Tax Name' },
    { key: 'tax_rate', label: 'Tax Rate (%)', align: 'right' },
    { key: 'is_inclusive', label: 'Inclusive', align: 'center' },
    { key: 'status', label: 'Status', align: 'center' },
];

const unitColumns = [
    { key: 'no', label: 'No' },
    { key: 'unit_code', label: 'Unit Code' },
    { key: 'unit_name', label: 'Unit Name' },
    { key: 'description', label: 'Description' },
    { key: 'status', label: 'Status', align: 'center' },
];

const conversionColumns = [
    { key: 'no', label: 'No' },
    { key: 'from_unit', label: 'From Unit' },
    { key: 'conversion_factor', label: 'Conversion Factor', align: 'right' },
    { key: 'to_unit', label: 'To Unit' },
    { key: 'status', label: 'Status', align: 'center' },
];

const priceListColumns = [
    { key: 'no', label: 'No' },
    { key: 'price_list_code', label: 'Code' },
    { key: 'price_list_name', label: 'Price List Name' },
    { key: 'price_list_type', label: 'Type', align: 'center' },
    { key: 'currency', label: 'Currency', align: 'center' },
    { key: 'is_default', label: 'Default', align: 'center' },
    { key: 'status', label: 'Status', align: 'center' },
];

const formatCurrency = (val) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
};
</script>

<template>
    <Head title="Master Data Management" />

    <DashboardLayout>
        <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-md">
            <div>
                <h1 class="text-2xl font-bold text-ink-primary">
                    Master Data 📦
                </h1>
                <p class="text-ink-secondary text-sm">
                    Kelola data partner bisnis, mata uang, pajak, unit konversi, dan daftar harga retail/grosir.
                </p>
            </div>
        </div>

        <!-- Navigation Tabs (Horizontal Scrollable on Mobile) -->
        <div class="flex space-x-1 mb-6 border-b border-border-soft overflow-x-auto whitespace-nowrap scrollbar-none">
            <button
                @click="activeTab = 'suppliers'"
                :class="activeTab === 'suppliers' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-3 px-6 font-medium transition-colors duration-200 cursor-pointer text-sm"
            >
                Suppliers 🚚
            </button>
            <button
                @click="activeTab = 'customers'"
                :class="activeTab === 'customers' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-3 px-6 font-medium transition-colors duration-200 cursor-pointer text-sm"
            >
                Customers 👥
            </button>
            <button
                @click="activeTab = 'customer-categories'"
                :class="activeTab === 'customer-categories' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-3 px-6 font-medium transition-colors duration-200 cursor-pointer text-sm"
            >
                Customer Categories 🏷️
            </button>
            <button
                @click="activeTab = 'currencies'"
                :class="activeTab === 'currencies' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-3 px-6 font-medium transition-colors duration-200 cursor-pointer text-sm"
            >
                Currencies 💵
            </button>
            <button
                @click="activeTab = 'taxes'"
                :class="activeTab === 'taxes' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-3 px-6 font-medium transition-colors duration-200 cursor-pointer text-sm"
            >
                Taxes 📊
            </button>
            <button
                @click="activeTab = 'units'"
                :class="activeTab === 'units' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-3 px-6 font-medium transition-colors duration-200 cursor-pointer text-sm"
            >
                Units 📐
            </button>
            <button
                @click="activeTab = 'unit-conversions'"
                :class="activeTab === 'unit-conversions' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-3 px-6 font-medium transition-colors duration-200 cursor-pointer text-sm"
            >
                Unit Conversions ⇄
            </button>
            <button
                @click="activeTab = 'price-lists'"
                :class="activeTab === 'price-lists' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-3 px-6 font-medium transition-colors duration-200 cursor-pointer text-sm"
            >
                Price Lists 💳
            </button>
        </div>

        <!-- Search Bar -->
        <div class="mb-6 flex items-center gap-md max-w-md bg-surface-card rounded-lg border border-border-soft p-sm shadow-soft">
            <svg class="w-5 h-5 text-ink-muted ml-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input
                v-model="searchQuery"
                type="text"
                placeholder="Cari data..."
                class="w-full bg-transparent border-none text-ink-primary text-sm focus:outline-none focus:ring-0 p-0"
            />
        </div>

        <!-- SUPPLIERS TABLE -->
        <div v-if="activeTab === 'suppliers'">
            <DataTable :columns="supplierColumns" :rows="filteredRows">
                <template #cell-supplier_name="{ value }">
                    <span class="font-semibold text-ink-primary">{{ value }}</span>
                </template>
                <template #cell-status="{ row }">
                    <span
                        :class="row.is_active ? 'bg-semantic-success-soft text-semantic-success' : 'bg-surface-subtle text-ink-muted'"
                        class="px-2.5 py-0.5 rounded-full text-xs font-semibold"
                    >
                        {{ row.is_active ? 'Active' : 'Inactive' }}
                    </span>
                </template>
            </DataTable>
        </div>

        <!-- CUSTOMERS TABLE -->
        <div v-if="activeTab === 'customers'">
            <DataTable :columns="customerColumns" :rows="filteredRows">
                <template #cell-customer_name="{ value }">
                    <span class="font-semibold text-ink-primary">{{ value }}</span>
                </template>
                <template #cell-credit_limit="{ value }">
                    <span class="font-mono text-ink-primary">{{ formatCurrency(value) }}</span>
                </template>
                <template #cell-status="{ row }">
                    <span
                        :class="row.is_active ? 'bg-semantic-success-soft text-semantic-success' : 'bg-surface-subtle text-ink-muted'"
                        class="px-2.5 py-0.5 rounded-full text-xs font-semibold"
                    >
                        {{ row.is_active ? 'Active' : 'Inactive' }}
                    </span>
                </template>
            </DataTable>
        </div>

        <!-- CUSTOMER CATEGORIES TABLE -->
        <div v-if="activeTab === 'customer-categories'">
            <DataTable :columns="categoryColumns" :rows="filteredRows">
                <template #cell-category_name="{ value }">
                    <span class="font-semibold text-ink-primary">{{ value }}</span>
                </template>
                <template #cell-status="{ row }">
                    <span
                        :class="row.is_active ? 'bg-semantic-success-soft text-semantic-success' : 'bg-surface-subtle text-ink-muted'"
                        class="px-2.5 py-0.5 rounded-full text-xs font-semibold"
                    >
                        {{ row.is_active ? 'Active' : 'Inactive' }}
                    </span>
                </template>
            </DataTable>
        </div>

        <!-- CURRENCIES TABLE -->
        <div v-if="activeTab === 'currencies'">
            <DataTable :columns="currencyColumns" :rows="filteredRows">
                <template #cell-name="{ value }">
                    <span class="font-semibold text-ink-primary">{{ value }}</span>
                </template>
                <template #cell-is_base="{ value }">
                    <span
                        v-if="value"
                        class="px-2 py-0.5 bg-brand-soft text-brand text-xs font-bold rounded"
                    >
                        Base Currency
                    </span>
                    <span v-else class="text-ink-muted text-xs">-</span>
                </template>
                <template #cell-status="{ row }">
                    <span
                        :class="row.is_active ? 'bg-semantic-success-soft text-semantic-success' : 'bg-surface-subtle text-ink-muted'"
                        class="px-2.5 py-0.5 rounded-full text-xs font-semibold"
                    >
                        {{ row.is_active ? 'Active' : 'Inactive' }}
                    </span>
                </template>
            </DataTable>
        </div>

        <!-- TAXES TABLE -->
        <div v-if="activeTab === 'taxes'">
            <DataTable :columns="taxColumns" :rows="filteredRows">
                <template #cell-tax_name="{ value }">
                    <span class="font-semibold text-ink-primary">{{ value }}</span>
                </template>
                <template #cell-is_inclusive="{ value }">
                    <span
                        :class="value ? 'text-semantic-success' : 'text-ink-secondary'"
                        class="text-xs font-semibold"
                    >
                        {{ value ? 'Inclusive' : 'Exclusive' }}
                    </span>
                </template>
                <template #cell-status="{ row }">
                    <span
                        :class="row.is_active ? 'bg-semantic-success-soft text-semantic-success' : 'bg-surface-subtle text-ink-muted'"
                        class="px-2.5 py-0.5 rounded-full text-xs font-semibold"
                    >
                        {{ row.is_active ? 'Active' : 'Inactive' }}
                    </span>
                </template>
            </DataTable>
        </div>

        <!-- UNITS TABLE -->
        <div v-if="activeTab === 'units'">
            <DataTable :columns="unitColumns" :rows="filteredRows">
                <template #cell-unit_name="{ value }">
                    <span class="font-semibold text-ink-primary">{{ value }}</span>
                </template>
                <template #cell-status="{ row }">
                    <span
                        :class="row.is_active ? 'bg-semantic-success-soft text-semantic-success' : 'bg-surface-subtle text-ink-muted'"
                        class="px-2.5 py-0.5 rounded-full text-xs font-semibold"
                    >
                        {{ row.is_active ? 'Active' : 'Inactive' }}
                    </span>
                </template>
            </DataTable>
        </div>

        <!-- UNIT CONVERSIONS TABLE -->
        <div v-if="activeTab === 'unit-conversions'">
            <DataTable :columns="conversionColumns" :rows="filteredRows">
                <template #cell-from_unit="{ value }">
                    <span class="font-semibold text-ink-primary">{{ value?.unit_code }} ({{ value?.unit_name }})</span>
                </template>
                <template #cell-to_unit="{ value }">
                    <span class="font-semibold text-ink-primary">{{ value?.unit_code }} ({{ value?.unit_name }})</span>
                </template>
                <template #cell-conversion_factor="{ value }">
                    <span class="font-mono text-brand font-bold">{{ parseFloat(value) }}</span>
                </template>
                <template #cell-status="{ row }">
                    <span
                        :class="row.is_active ? 'bg-semantic-success-soft text-semantic-success' : 'bg-surface-subtle text-ink-muted'"
                        class="px-2.5 py-0.5 rounded-full text-xs font-semibold"
                    >
                        {{ row.is_active ? 'Active' : 'Inactive' }}
                    </span>
                </template>
            </DataTable>
        </div>

        <!-- PRICE LISTS TABLE -->
        <div v-if="activeTab === 'price-lists'">
            <DataTable :columns="priceListColumns" :rows="filteredRows">
                <template #cell-price_list_name="{ value }">
                    <span class="font-semibold text-ink-primary">{{ value }}</span>
                </template>
                <template #cell-is_default="{ value }">
                    <span
                        v-if="value"
                        class="px-2 py-0.5 bg-brand-soft text-brand text-xs font-bold rounded"
                    >
                        Default
                    </span>
                    <span v-else class="text-ink-muted text-xs">-</span>
                </template>
                <template #cell-status="{ row }">
                    <span
                        :class="row.is_active ? 'bg-semantic-success-soft text-semantic-success' : 'bg-surface-subtle text-ink-muted'"
                        class="px-2.5 py-0.5 rounded-full text-xs font-semibold"
                    >
                        {{ row.is_active ? 'Active' : 'Inactive' }}
                    </span>
                </template>
            </DataTable>
        </div>
    </DashboardLayout>
</template>
