<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import DataTable from '@/Components/Table/DataTable.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';

const props = defineProps({
    suppliers: { type: Array, default: () => [] },
    customers: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    units: { type: Array, default: () => [] },
    activeTab: { type: String, default: 'suppliers' },
});

const activeTab = ref(props.activeTab);
const customerSearch = ref('');
const supplierSearch = ref('');

// Watch activeTab changes to navigate with Inertia for proper data loading
watch(activeTab, (newTab) => {
    if (newTab === 'suppliers') {
        router.visit('/master-data/suppliers', { preserveState: true, replace: true });
    } else if (newTab === 'customers') {
        router.visit('/master-data/customers', { preserveState: true, replace: true });
    }
});

// Sync tab if props change
watch(() => props.activeTab, (newVal) => {
    activeTab.value = newVal;
});

// Local Search Filters
const filteredSuppliers = computed(() => {
    if (!supplierSearch.value) return props.suppliers;
    const query = supplierSearch.value.toLowerCase();
    return props.suppliers.filter(s => 
        s.supplier_name?.toLowerCase().includes(query) ||
        s.contact_person?.toLowerCase().includes(query) ||
        s.email?.toLowerCase().includes(query)
    );
});

const filteredCustomers = computed(() => {
    if (!customerSearch.value) return props.customers;
    const query = customerSearch.value.toLowerCase();
    return props.customers.filter(c => 
        c.customer_name?.toLowerCase().includes(query) ||
        c.phone?.toLowerCase().includes(query) ||
        c.email?.toLowerCase().includes(query)
    );
});

// Column Configurations
const supplierColumns = [
    { key: 'supplier_name', label: 'Supplier Name' },
    { key: 'contact_person', label: 'Contact Person' },
    { key: 'phone', label: 'Phone' },
    { key: 'email', label: 'Email' },
    { key: 'status', label: 'Status', align: 'center' },
    { key: 'actions', label: 'Actions', align: 'right' },
];

const customerColumns = [
    { key: 'customer_name', label: 'Customer Name' },
    { key: 'category', label: 'Category' },
    { key: 'phone', label: 'Phone' },
    { key: 'email', label: 'Email' },
    { key: 'points', label: 'Loyalty Points', align: 'right' },
    { key: 'actions', label: 'Actions', align: 'right' },
];

const unitColumns = [
    { key: 'code', label: 'Unit Code' },
    { key: 'name', label: 'Unit Name' },
    { key: 'is_base', label: 'Type', align: 'center' },
];
</script>

<template>
    <Head title="Master Data Management" />

    <DashboardLayout>
        <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-md">
            <div>
                <h1 class="text-2xl font-bold text-ink-primary">
                    Master Data
                </h1>
                <p class="text-ink-secondary text-sm">
                    Kelola data partner bisnis Anda, termasuk penyuplai, pelanggan, dan unit satuan.
                </p>
            </div>

            <div>
                <BaseButton v-if="activeTab === 'suppliers'">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Supplier
                </BaseButton>
                <BaseButton v-if="activeTab === 'customers'">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Customer
                </BaseButton>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex space-x-1 mb-6 border-b border-border-soft">
            <button
                @click="activeTab = 'suppliers'"
                :class="activeTab === 'suppliers' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-3 px-6 font-medium transition-colors duration-200 cursor-pointer text-sm"
            >
                Suppliers
            </button>
            <button
                @click="activeTab = 'customers'"
                :class="activeTab === 'customers' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-3 px-6 font-medium transition-colors duration-200 cursor-pointer text-sm"
            >
                Customers
            </button>
        </div>

        <!-- Suppliers Tab Content -->
        <div v-if="activeTab === 'suppliers'" class="space-y-base">
            <div class="flex items-center gap-md max-w-md bg-surface-card rounded-lg border border-border-soft p-sm shadow-soft">
                <svg class="w-5 h-5 text-ink-muted ml-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input
                    v-model="supplierSearch"
                    type="text"
                    placeholder="Search suppliers..."
                    class="w-full bg-transparent border-none text-ink-primary text-sm focus:outline-none focus:ring-0 p-0"
                />
            </div>

            <DataTable :columns="supplierColumns" :rows="filteredSuppliers">
                <template #cell-supplier_name="{ value }">
                    <span class="font-semibold text-ink-primary">{{ value }}</span>
                </template>
                
                <template #cell-status="{ row }">
                    <span
                        :class="row.is_active ? 'bg-semantic-success-soft text-semantic-success' : 'bg-surface-subtle text-ink-muted'"
                        class="px-2.5 py-0.5 rounded-full text-xs font-semibold border border-transparent"
                    >
                        {{ row.is_active ? 'Active' : 'Inactive' }}
                    </span>
                </template>

                <template #cell-actions>
                    <button class="text-brand hover:underline font-medium text-sm">Edit</button>
                </template>
            </DataTable>
        </div>

        <!-- Customers Tab Content -->
        <div v-if="activeTab === 'customers'" class="space-y-base">
            <div class="flex items-center gap-md max-w-md bg-surface-card rounded-lg border border-border-soft p-sm shadow-soft">
                <svg class="w-5 h-5 text-ink-muted ml-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input
                    v-model="customerSearch"
                    type="text"
                    placeholder="Search customers..."
                    class="w-full bg-transparent border-none text-ink-primary text-sm focus:outline-none focus:ring-0 p-0"
                />
            </div>

            <DataTable :columns="customerColumns" :rows="filteredCustomers">
                <template #cell-customer_name="{ value }">
                    <span class="font-semibold text-ink-primary">{{ value }}</span>
                </template>

                <template #cell-category="{ row }">
                    <span class="px-2 py-0.5 bg-brand-soft text-brand rounded text-xs font-semibold">
                        {{ row.category?.name || 'Regular' }}
                    </span>
                </template>

                <template #cell-points="{ row }">
                    <span class="font-mono text-brand font-bold">{{ row.loyalty_account?.current_points || 0 }} pts</span>
                </template>

                <template #cell-actions>
                    <button class="text-brand hover:underline font-medium text-sm">View Details</button>
                </template>
            </DataTable>
        </div>
    </DashboardLayout>
</template>
