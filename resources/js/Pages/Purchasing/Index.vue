<script setup>
import { ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import DataTable from '@/Components/Table/DataTable.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    purchaseOrders: Array,
    activeTab: { type: String, default: 'orders' },
});

const activeTab = ref(props.activeTab || 'orders');
const purchaseOrders = ref(props.purchaseOrders || []);

const poColumns = [
    { key: 'no', label: 'No' },
    { key: 'purchase_order_no', label: 'PO No' },
    { key: 'supplier', label: 'Supplier' },
    { key: 'grand_total', label: 'Total', align: 'right' },
    { key: 'status', label: 'Status', align: 'center' },
    { key: 'po_date', label: 'Date' },
    { key: 'actions', label: 'Actions', align: 'center' },
];

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID');
};

const getStatusClass = (status) => {
    const classes = {
        DRAFT: 'bg-surface-subtle text-ink-muted border border-border-soft',
        PENDING: 'bg-semantic-warning-soft text-semantic-warning',
        APPROVED: 'bg-brand-soft text-brand',
        POSTED: 'bg-semantic-success-soft text-semantic-success',
        CANCELLED: 'bg-semantic-danger-soft text-semantic-danger',
    };
    return classes[status] || 'bg-surface-subtle text-ink-muted';
};
</script>

<template>
    <Head title="Purchasing Management" />

    <DashboardLayout>
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-ink-primary">
                    Purchasing
                </h1>
                <p class="text-ink-secondary">
                    Kelola Purchase Orders, Goods Receipts, dan Supplier Invoices
                </p>
            </div>

            <div class="flex gap-md">
                <Link v-if="activeTab === 'orders'" href="/purchasing/create">
                    <BaseButton>
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        New Order
                    </BaseButton>
                </Link>
                <BaseButton v-if="activeTab === 'receipts'">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Receipt
                </BaseButton>
            </div>
        </div>

        <!-- Tabs -->
        <div class="flex space-x-4 mb-6 border-b border-border-soft">
            <button
                @click="activeTab = 'orders'"
                :class="activeTab === 'orders' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-2 px-4 font-medium transition-colors cursor-pointer"
            >
                Purchase Orders
            </button>
            <button
                @click="activeTab = 'receipts'"
                :class="activeTab === 'receipts' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-2 px-4 font-medium transition-colors cursor-pointer"
            >
                Goods Receipts
            </button>
            <button
                @click="activeTab = 'invoices'"
                :class="activeTab === 'invoices' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-2 px-4 font-medium transition-colors cursor-pointer"
            >
                Invoices
            </button>
        </div>

        <!-- Purchase Orders -->
        <div v-if="activeTab === 'orders'">
            <DataTable :columns="poColumns" :rows="purchaseOrders">
                <template #cell-purchase_order_no="{ value }">
                    <span class="font-mono text-ink-primary font-semibold">{{ value }}</span>
                </template>
                <template #cell-supplier="{ row }">
                    <span>{{ row.supplier?.supplier_name || '-' }}</span>
                </template>
                <template #cell-grand_total="{ value }">
                    <span class="font-mono text-ink-primary font-medium">{{ formatCurrency(value) }}</span>
                </template>
                <template #cell-status="{ value }">
                    <span
                        :class="getStatusClass(value)"
                        class="px-2 py-1 rounded-full text-xs font-semibold border border-transparent"
                    >
                        {{ value }}
                    </span>
                </template>
                <template #cell-po_date="{ value }">
                    <span>{{ formatDate(value) }}</span>
                </template>
                <template #cell-actions="{ row }">
                    <Link :href="`/purchasing/${row.id}`" class="text-brand hover:underline font-medium">
                        View
                    </Link>
                </template>
            </DataTable>
        </div>

        <!-- Goods Receipts -->
        <div v-if="activeTab === 'receipts'" class="bg-surface-card border border-border-soft rounded-md p-6 text-center text-ink-secondary">
            No Goods Receipts yet.
        </div>

        <!-- Invoices -->
        <div v-if="activeTab === 'invoices'" class="bg-surface-card border border-border-soft rounded-md p-6 text-center text-ink-secondary">
            No Supplier Invoices yet.
        </div>
    </DashboardLayout>
</template>
