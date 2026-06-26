<script setup>
import { ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import DataTable from '@/Components/Table/DataTable.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    inventoryBalance: Array,
    movements: Array,
    activeTab: { type: String, default: 'balance' },
});

const activeTab = ref(props.activeTab || 'balance');
const inventoryBalance = ref(props.inventoryBalance || []);
const movements = ref(props.movements || []);

const balanceColumns = [
    { key: 'no', label: 'No' },
    { key: 'product', label: 'Product' },
    { key: 'location', label: 'Location' },
    { key: 'quantity_on_hand', label: 'Quantity', align: 'right' },
    { key: 'reorder_level', label: 'Reorder Level', align: 'right' },
    { key: 'balance_value', label: 'Value', align: 'right' },
    { key: 'status', label: 'Status', align: 'center' },
];

const movementColumns = [
    { key: 'no', label: 'No' },
    { key: 'product', label: 'Product' },
    { key: 'document_no', label: 'Ref No' },
    { key: 'reference_date', label: 'Date' },
    { key: 'movement_type', label: 'Type', align: 'center' },
    { key: 'quantity', label: 'Quantity', align: 'right' },
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
</script>

<template>
    <Head title="Inventory Management" />

    <DashboardLayout>
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-ink-primary">
                    Inventory
                </h1>
                <p class="text-ink-secondary">
                    Kelola stok barang, mutasi, dan transfer antar lokasi
                </p>
            </div>

            <div class="flex gap-md">
                <BaseButton v-if="activeTab === 'balance'">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Adjustment
                </BaseButton>
                <BaseButton v-if="activeTab === 'transfers'">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Transfer
                </BaseButton>
            </div>
        </div>

        <!-- Tabs -->
        <div class="flex space-x-4 mb-6 border-b border-border-soft">
            <button
                @click="activeTab = 'balance'"
                :class="activeTab === 'balance' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-2 px-4 font-medium transition-colors cursor-pointer"
            >
                Stock Balance
            </button>
            <button
                @click="activeTab = 'movements'"
                :class="activeTab === 'movements' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-2 px-4 font-medium transition-colors cursor-pointer"
            >
                Movements
            </button>
            <button
                @click="activeTab = 'transfers'"
                :class="activeTab === 'transfers' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-2 px-4 font-medium transition-colors cursor-pointer"
            >
                Transfers
            </button>
        </div>

        <!-- Stock Balance Tab -->
        <div v-if="activeTab === 'balance'">
            <DataTable :columns="balanceColumns" :rows="inventoryBalance">
                <template #cell-product="{ row }">
                    <span class="font-medium text-ink-primary">{{ row.product?.name || '-' }}</span>
                </template>
                <template #cell-location="{ row }">
                    <span>{{ row.location?.name || '-' }}</span>
                </template>
                <template #cell-quantity_on_hand="{ value }">
                    <span class="font-mono font-medium">{{ value }}</span>
                </template>
                <template #cell-reorder_level="{ value }">
                    <span class="font-mono text-ink-secondary">{{ value }}</span>
                </template>
                <template #cell-balance_value="{ value }">
                    <span class="font-mono text-ink-primary">{{ formatCurrency(value) }}</span>
                </template>
                <template #cell-status="{ row }">
                    <span
                        v-if="row.quantity_on_hand <= row.reorder_level"
                        class="px-2 py-1 rounded-full text-xs font-semibold bg-semantic-danger-soft text-semantic-danger border border-transparent"
                    >
                        Low Stock
                    </span>
                    <span
                        v-else
                        class="px-2 py-1 rounded-full text-xs font-semibold bg-semantic-success-soft text-semantic-success border border-transparent"
                    >
                        OK
                    </span>
                </template>
            </DataTable>
        </div>

        <!-- Movements Tab -->
        <div v-if="activeTab === 'movements'">
            <DataTable :columns="movementColumns" :rows="movements">
                <template #cell-product="{ row }">
                    <span class="font-medium text-ink-primary">{{ row.product?.name || '-' }}</span>
                </template>
                <template #cell-document_no="{ value }">
                    <span class="font-mono text-ink-secondary">{{ value }}</span>
                </template>
                <template #cell-reference_date="{ value }">
                    <span>{{ formatDate(value) }}</span>
                </template>
                <template #cell-movement_type="{ value }">
                    <span
                        :class="value === 'IN' ? 'bg-semantic-success-soft text-semantic-success' : 'bg-semantic-danger-soft text-semantic-danger'"
                        class="px-2 py-1 rounded-full text-xs font-semibold border border-transparent"
                    >
                        {{ value }}
                    </span>
                </template>
                <template #cell-quantity="{ row }">
                    <span
                        :class="row.movement_type === 'IN' ? 'text-semantic-success' : 'text-semantic-danger'"
                        class="font-mono font-bold"
                    >
                        {{ row.movement_type === 'IN' ? '+' : '-' }}{{ row.quantity }}
                    </span>
                </template>
            </DataTable>
        </div>

        <!-- Transfers Tab -->
        <div v-if="activeTab === 'transfers'" class="bg-surface-card border border-border-soft rounded-md p-6 text-center text-ink-secondary">
            No stock transfers recorded yet.
        </div>
    </DashboardLayout>
</template>
