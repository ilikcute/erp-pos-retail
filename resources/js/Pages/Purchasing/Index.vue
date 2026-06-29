<script setup>
import { ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import DataTable from '@/Components/Table/DataTable.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import StatusBadge from '@/Components/DataDisplay/StatusBadge.vue';
import { Head, Link } from '@inertiajs/vue3';
import { formatCurrency, formatDate } from '@/Utils/formatters';

const props = defineProps({
    purchaseOrders: Array,
    activeTab: { type: String, default: 'orders' },
});

const activeTab = ref(props.activeTab || 'orders');
const purchaseOrders = ref(props.purchaseOrders || []);

const poColumns = [
    { key: 'no', label: 'No' },
    { key: 'po_number', label: 'PO No' },
    { key: 'supplier', label: 'Supplier' },
    { key: 'total_amount', label: 'Total', align: 'right' },
    { key: 'status', label: 'Status', align: 'center' },
    { key: 'order_date', label: 'Date' },
    { key: 'actions', label: 'Actions', align: 'center' },
];

// Formatters imported from @/Utils/formatters
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
                <template #cell-po_number="{ value }">
                    <span class="font-mono text-ink-primary font-semibold">{{ value }}</span>
                </template>
                <template #cell-supplier="{ row }">
                    <span>{{ row.supplier?.supplier_name || '-' }}</span>
                </template>
                <template #cell-total_amount="{ value }">
                    <span class="font-mono text-ink-primary font-medium">{{ formatCurrency(value) }}</span>
                </template>
                <template #cell-status="{ value }">
                    <StatusBadge :status="value" variant="soft" size="sm" />
                </template>
                <template #cell-order_date="{ value }">
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
