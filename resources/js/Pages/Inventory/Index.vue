<script setup>
import { ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import KPICard from '@/Components/Dashboard/KPICard.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import DataTable from '@/Components/Table/DataTable.vue';
import BaseModal from '@/Components/Modal/BaseModal.vue';
import FormInput from '@/Components/Form/FormInput.vue';
import FormSelect from '@/Components/Form/FormSelect.vue';
import FormTextarea from '@/Components/Form/FormTextarea.vue';
import StatusBadge from '@/Components/DataDisplay/StatusBadge.vue';
import { Head, router } from '@inertiajs/vue3';
import { formatCurrency, formatDate } from '@/Utils/formatters';

const props = defineProps({
    inventoryBalance: { type: Array, default: () => [] },
    movements: { type: Array, default: () => [] },
    locations: { type: Array, default: () => [] },
    variants: { type: Array, default: () => [] },
    planograms: { type: Array, default: () => [] },
    transfers: { type: Array, default: () => [] },
    activeTab: { type: String, default: 'balance' },
});

const activeTab = ref(props.activeTab || 'balance');

// Planogram Modal State
const showPlanogramModal = ref(false);
const planogramForm = ref({
    product_variant_id: '',
    location_id: '',
    position_code: '',
    notes: '',
    is_active: true,
});

function openCreatePlanogram() {
    planogramForm.value = {
        product_variant_id: '',
        location_id: '',
        position_code: '',
        notes: '',
        is_active: true,
    };
    showPlanogramModal.value = true;
}

function submitPlanogram() {
    router.post('/inventory/planograms', planogramForm.value, {
        onSuccess: () => showPlanogramModal.value = false
    });
}

function deletePlanogram(row) {
    if (confirm(`Hapus planogram "${row.position_code}"?`)) {
        router.delete(`/inventory/planograms/${row.id}`);
    }
}

// KPI aggregations
const sumValue = () => props.inventoryBalance.reduce((s, r) => s + parseFloat(r.balance_value || 0), 0);
const lowStockCount = () => props.inventoryBalance.filter(r => r.quantity_on_hand <= r.reorder_level).length;
const totalSku = () => props.inventoryBalance.length;

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

const planogramColumns = [
    { key: 'no', label: 'No' },
    { key: 'position_code', label: 'Position Code' },
    { key: 'location', label: 'Location' },
    { key: 'product', label: 'Product / Variant' },
    { key: 'notes', label: 'Notes' },
    { key: 'actions', label: 'Actions', align: 'center' },
];

const transferColumns = [
    { key: 'no', label: 'No' },
    { key: 'transfer_no', label: 'Transfer No' },
    { key: 'source', label: 'Source Location' },
    { key: 'destination', label: 'Destination Location' },
    { key: 'transfer_date', label: 'Date' },
    { key: 'status', label: 'Status', align: 'center' },
];

// Formatters imported from @/Utils/formatters
</script>

<template>
    <Head title="Inventory Management" />
    <DashboardLayout>
        <!-- Header banner -->
        <div class="rounded-card bg-mint-gradient text-white p-xl mb-xl shadow-mint-glow flex items-center justify-between flex-wrap gap-base">
            <div>
                <h1 class="text-page-title font-extrabold">Inventory 📦</h1>
                <p class="text-white/85 mt-xs text-base">Kelola stok barang, mutasi, transfer antar lokasi, dan planogram rak.</p>
            </div>
            <div class="flex gap-sm">
                <BaseButton v-if="activeTab === 'planograms'" @click="openCreatePlanogram" variant="soft" size="sm">➕ New Planogram</BaseButton>
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
            <button @click="activeTab = 'planograms'" :class="['chip whitespace-nowrap px-lg py-sm', activeTab==='planograms' ? 'bg-brand text-white shadow-brand-glow' : 'bg-surface-card text-ink-secondary border border-border-soft hover:border-brand-border']">📐 Planograms</button>
            <button @click="activeTab = 'transfers'" :class="['chip whitespace-nowrap px-lg py-sm', activeTab==='transfers' ? 'bg-brand text-white shadow-brand-glow' : 'bg-surface-card text-ink-secondary border border-border-soft hover:border-brand-border']">📦 Transfers</button>
        </div>

        <!-- Stock Balance Tab -->
        <div v-if="activeTab === 'balance'" class="card-friendly p-lg">
            <DataTable :columns="balanceColumns" :rows="inventoryBalance">
                <template #cell-product="{ row }"><span class="font-semibold text-ink-primary">{{ row.product_variant?.product?.product_name || '-' }}</span></template>
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
                <template #cell-product="{ row }"><span class="font-semibold text-ink-primary">{{ row.product_variant?.product?.product_name || '-' }}</span></template>
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

        <!-- Planograms Tab -->
        <div v-if="activeTab === 'planograms'" class="card-friendly p-lg">
            <DataTable :columns="planogramColumns" :rows="planograms">
                <template #cell-position_code="{ value }">
                    <span class="font-mono font-bold text-brand">{{ value }}</span>
                </template>
                <template #cell-location="{ row }">
                    <span>{{ row.location?.name || '-' }}</span>
                </template>
                <template #cell-product="{ row }">
                    <span class="font-semibold text-ink-primary">{{ row.variant?.product?.product_name }} - {{ row.variant?.variant_name || 'Default' }}</span>
                </template>
                <template #cell-notes="{ value }">
                    <span class="text-xs text-ink-secondary">{{ value || '-' }}</span>
                </template>
                <template #cell-actions="{ row }">
                    <button @click="deletePlanogram(row)" class="text-semantic-danger hover:underline text-sm font-semibold">Delete</button>
                </template>
            </DataTable>
            <div v-if="planograms.length === 0" class="text-center text-ink-secondary py-xl">
                Belum ada planogram rak terdaftar.
            </div>
        </div>

        <!-- Stock Transfers Tab -->
        <div v-if="activeTab === 'transfers'" class="card-friendly p-lg">
            <DataTable :columns="transferColumns" :rows="transfers">
                <template #cell-transfer_no="{ value }">
                    <span class="font-mono font-bold text-ink-primary">{{ value }}</span>
                </template>
                <template #cell-source="{ row }">
                    <span>{{ row.source?.name || '-' }}</span>
                </template>
                <template #cell-destination="{ row }">
                    <span>{{ row.destination?.name || '-' }}</span>
                </template>
                <template #cell-transfer_date="{ value }">
                    <span>{{ formatDate(value) }}</span>
                </template>
                <template #cell-status="{ value }">
                    <StatusBadge :status="value" variant="soft" size="sm" />
                </template>
            </DataTable>
            <div v-if="transfers.length === 0" class="text-center text-ink-secondary py-xl">
                Belum ada transaksi transfer stok.
            </div>
        </div>

        <!-- New Planogram Modal -->
        <BaseModal :show="showPlanogramModal" @close="showPlanogramModal = false" title="New Shelf Planogram">
            <form @submit.prevent="submitPlanogram" class="space-y-md">
                <div class="grid grid-cols-2 gap-md">
                    <FormSelect label="Warehouse Location" v-model="planogramForm.location_id" required>
                        <option value="">Pilih Gudang</option>
                        <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name }}</option>
                    </FormSelect>
                    <FormInput label="Position Code (Rack/Shelf)" v-model="planogramForm.position_code" required placeholder="A-01-C" />
                </div>
                <FormSelect label="Product Variant" v-model="planogramForm.product_variant_id" required>
                    <option value="">Pilih Produk/Varian</option>
                    <option v-for="v in variants" :key="v.id" :value="v.id">{{ v.product?.product_name }} - {{ v.variant_name || 'Default' }} ({{ v.sku }})</option>
                </FormSelect>
                <FormTextarea label="Notes" v-model="planogramForm.notes" placeholder="Catatan rak..." rows="2" />
                
                <div class="flex justify-end gap-md pt-md border-t border-border-soft">
                    <BaseButton type="button" variant="secondary" @click="showPlanogramModal = false">Cancel</BaseButton>
                    <BaseButton type="submit" variant="primary">Save Planogram</BaseButton>
                </div>
            </form>
        </BaseModal>
    </DashboardLayout>
</template>