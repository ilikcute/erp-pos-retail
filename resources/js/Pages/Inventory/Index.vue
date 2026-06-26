<script setup>
import { ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import KPICard from '@/Components/Dashboard/KPICard.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import DataTable from '@/Components/Table/DataTable.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    inventoryBalance: Array,
    movements: Array,
    activeTab: { type: String, default: 'balance' },
});

const activeTab = ref(props.activeTab || 'balance');

// --- Data contoh (fallback bila props kosong) ---
const MOCK_BALANCE = [
    { no: 1, product: { name: 'Kopi Susu Gula Aren' }, location: { name: 'Gudang A' }, quantity_on_hand: 5,  reorder_level: 20, balance_value: 110000 },
    { no: 2, product: { name: 'Nasi Goreng Spesial' }, location: { name: 'Gudang A' }, quantity_on_hand: 32, reorder_level: 10, balance_value: 1120000 },
    { no: 3, product: { name: 'Air Mineral 600ml' },   location: { name: 'Gudang A' }, quantity_on_hand: 8,  reorder_level: 50, balance_value: 48000 },
    { no: 4, product: { name: 'Donat Gula' },          location: { name: 'Gudang B' }, quantity_on_hand: 3,  reorder_level: 15, balance_value: 36000 },
    { no: 5, product: { name: 'Burger Daging' },       location: { name: 'Gudang B' }, quantity_on_hand: 24, reorder_level: 8,  balance_value: 912000 },
];
const MOCK_MOVEMENTS = [
    { no: 1, product: { name: 'Kopi Susu Gula Aren' }, document_no: 'GR-2001', reference_date: '2026-06-24', movement_type: 'IN',  quantity: 50 },
    { no: 2, product: { name: 'Donat Gula' },          document_no: 'SO-1003', reference_date: '2026-06-24', movement_type: 'OUT', quantity: 12 },
    { no: 3, product: { name: 'Burger Daging' },       document_no: 'GR-2002', reference_date: '2026-06-25', movement_type: 'IN',  quantity: 30 },
    { no: 4, product: { name: 'Air Mineral 600ml' },   document_no: 'SO-1004', reference_date: '2026-06-25', movement_type: 'OUT', quantity: 18 },
];

// Ringkasan (fungsi biasa agar tidak perlu import computed)
const sumValue = () => inventoryBalance.value.reduce((s, r) => s + (r.balance_value || 0), 0);
const lowStockCount = () => inventoryBalance.value.filter(r => r.quantity_on_hand <= r.reorder_level).length;
const totalSku = () => inventoryBalance.value.length;
const inventoryBalance = ref(props.inventoryBalance && props.inventoryBalance.length ? props.inventoryBalance : MOCK_BALANCE);
const movements = ref(props.movements && props.movements.length ? props.movements : MOCK_MOVEMENTS);

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
        <!-- Header banner -->
        <div class="rounded-card bg-mint-gradient text-white p-xl mb-xl shadow-mint-glow flex items-center justify-between flex-wrap gap-base">
            <div>
                <h1 class="text-page-title font-extrabold">Inventory 📦</h1>
                <p class="text-white/85 mt-xs text-base">Kelola stok barang, mutasi, dan transfer antar lokasi.</p>
            </div>
            <div class="flex gap-sm">
                <BaseButton v-if="activeTab === 'balance'" variant="soft" size="sm">➕ Adjustment</BaseButton>
                <BaseButton v-if="activeTab === 'transfers'" variant="soft" size="sm">🔁 New Transfer</BaseButton>
            </div>
        </div>

        <!-- KPI cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-base mb-xl">
            <KPICard label="Total SKU" :value="totalSku()" icon="layers" color="brand" />
            <KPICard label="Total Nilai Aset" :value="formatCurrency(sumValue())" icon="dollar-sign" color="mint" />
            <KPICard label="Stok Menipis" :value="lowStockCount()" icon="alert-triangle" color="sunny" />
        </div>

        <!-- Tabs -->
        <div class="flex gap-sm mb-xl overflow-x-auto scroll-soft pb-xs">
            <button @click="activeTab = 'balance'" :class="['chip whitespace-nowrap px-lg py-sm', activeTab==='balance' ? 'bg-brand text-white shadow-brand-glow' : 'bg-surface-card text-ink-secondary border border-border-soft hover:border-brand-border']">📊 Stock Balance</button>
            <button @click="activeTab = 'movements'" :class="['chip whitespace-nowrap px-lg py-sm', activeTab==='movements' ? 'bg-brand text-white shadow-brand-glow' : 'bg-surface-card text-ink-secondary border border-border-soft hover:border-brand-border']">🔄 Movements</button>
            <button @click="activeTab = 'transfers'" :class="['chip whitespace-nowrap px-lg py-sm', activeTab==='transfers' ? 'bg-brand text-white shadow-brand-glow' : 'bg-surface-card text-ink-secondary border border-border-soft hover:border-brand-border']">🚚 Transfers</button>
        </div>

        <!-- Stock Balance Tab -->
        <div v-if="activeTab === 'balance'" class="card-friendly p-lg">
            <DataTable :columns="balanceColumns" :rows="inventoryBalance">
                <template #cell-product="{ row }"><span class="font-semibold text-ink-primary">{{ row.product?.name || '-' }}</span></template>
                <template #cell-location="{ row }"><span class="chip bg-accent-sky-soft text-accent-sky px-md py-0.5 text-xs">{{ row.location?.name || '-' }}</span></template>
                <template #cell-quantity_on_hand="{ value }"><span class="font-mono font-semibold">{{ value }}</span></template>
                <template #cell-reorder_level="{ value }"><span class="font-mono text-ink-muted">{{ value }}</span></template>
                <template #cell-balance_value="{ value }"><span class="font-mono font-semibold text-ink-primary">{{ formatCurrency(value) }}</span></template>
                <template #cell-status="{ row }">
                    <span v-if="row.quantity_on_hand <= row.reorder_level" class="chip bg-semantic-danger-soft text-semantic-danger px-md py-0.5 text-xs font-semibold">⚠️ Low Stock</span>
                    <span v-else class="chip bg-accent-mint-soft text-accent-mint px-md py-0.5 text-xs font-semibold">✓ OK</span>
                </template>
            </DataTable>
        </div>

        <!-- Movements Tab -->
        <div v-if="activeTab === 'movements'" class="card-friendly p-lg">
            <DataTable :columns="movementColumns" :rows="movements">
                <template #cell-product="{ row }"><span class="font-semibold text-ink-primary">{{ row.product?.name || '-' }}</span></template>
                <template #cell-document_no="{ value }"><span class="font-mono text-ink-muted">{{ value }}</span></template>
                <template #cell-reference_date="{ value }"><span>{{ formatDate(value) }}</span></template>
                <template #cell-movement_type="{ value }">
                    <span :class="value === 'IN' ? 'bg-accent-mint-soft text-accent-mint' : 'bg-semantic-danger-soft text-semantic-danger'" class="chip px-md py-0.5 text-xs font-semibold">{{ value === 'IN' ? '⬇️ IN' : '⬆️ OUT' }}</span>
                </template>
                <template #cell-quantity="{ row }">
                    <span :class="row.movement_type === 'IN' ? 'text-accent-mint' : 'text-semantic-danger'" class="font-mono font-bold">{{ row.movement_type === 'IN' ? '+' : '-' }}{{ row.quantity }}</span>
                </template>
            </DataTable>
        </div>

        <!-- Transfers Tab -->
        <div v-if="activeTab === 'transfers'" class="card-friendly p-2xl text-center">
            <p class="text-5xl mb-base">🚚</p>
            <p class="text-card-title font-semibold text-ink-primary">Belum ada transfer stok</p>
            <p class="text-sm text-ink-muted mt-xs">Buat transfer baru untuk memindahkan stok antar lokasi.</p>
        </div>
    </DashboardLayout>
</template>