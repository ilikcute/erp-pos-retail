<script setup>
import { ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import DataTable from '@/Components/Table/DataTable.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    transactions: Object,
    filters: Object,
});

const transactions = ref(props.transactions?.data || []);

const columns = [
    { key: 'transaction_no', label: 'Order No' },
    { key: 'cashier', label: 'Cashier' },
    { key: 'customer', label: 'Customer' },
    { key: 'grand_total', label: 'Grand Total', align: 'right' },
    { key: 'status', label: 'Status', align: 'center' },
    { key: 'transaction_date', label: 'Date' },
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
        POSTED: 'bg-semantic-success-soft text-semantic-success',
        VOID: 'bg-semantic-danger-soft text-semantic-danger',
    };
    return classes[status] || 'bg-surface-subtle text-ink-muted';
};
</script>

<template>
    <Head title="Sales Orders" />

    <DashboardLayout>
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-ink-primary">
                    Orders / Sales Transactions
                </h1>
                <p class="text-ink-secondary">
                    Daftar semua transaksi penjualan kasir (POS)
                </p>
            </div>

            <div>
                <Link href="/pos">
                    <BaseButton>
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Buka POS Kasir
                    </BaseButton>
                </Link>
            </div>
        </div>

        <div class="bg-surface-card rounded-card border border-border-soft p-6 shadow-card">
            <DataTable :columns="columns" :rows="transactions">
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
                    <span class="font-mono text-ink-primary font-semibold">{{ formatCurrency(value) }}</span>
                </template>
                <template #cell-status="{ value }">
                    <span
                        :class="getStatusClass(value)"
                        class="px-2 py-1 rounded-full text-xs font-semibold border border-transparent"
                    >
                        {{ value }}
                    </span>
                </template>
                <template #cell-transaction_date="{ value }">
                    <span>{{ formatDate(value) }}</span>
                </template>
                <template #cell-actions="{ row }">
                    <Link :href="`/pos/sales/${row.id}`" class="text-brand hover:underline font-medium">
                        View Detail
                    </Link>
                </template>
            </DataTable>
        </div>
    </DashboardLayout>
</template>
