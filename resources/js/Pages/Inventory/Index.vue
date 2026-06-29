<script setup>
import { ref, computed, watch } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import KPICard from '@/Components/Dashboard/KPICard.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import DataTable from '@/Components/Table/DataTable.vue';
import BaseModal from '@/Components/Modal/BaseModal.vue';
import FormInput from '@/Components/Form/FormInput.vue';
import FormSelect from '@/Components/Form/FormSelect.vue';
import FormTextarea from '@/Components/Form/FormTextarea.vue';
import StatusBadge from '@/Components/DataDisplay/StatusBadge.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { formatCurrency, formatDate } from '@/Utils/formatters';

const props = defineProps({
    inventoryBalance: { type: Array, default: () => [] },
    movements: { type: Array, default: () => [] },
    locations: { type: Array, default: () => [] },
    variants: { type: Array, default: () => [] },
    planograms: { type: Array, default: () => [] },
    transfers: { type: Array, default: () => [] },
    adjustments: { type: Array, default: () => [] },
    opnames: { type: Array, default: () => [] },
    batches: { type: Array, default: () => [] },
    activeTab: { type: String, default: 'balance' },
});

const activeTab = ref(props.activeTab || 'balance');

// Modals State
const showLocationModal = ref(false);
const showTransferModal = ref(false);
const showAdjustmentModal = ref(false);
const showOpnameModal = ref(false);
const showCountModal = ref(false);
const showPlanogramModal = ref(false);
const showRejectAdjModal = ref(false);

const selectedLocation = ref(null);
const selectedTransfer = ref(null);
const selectedAdjustment = ref(null);
const selectedOpname = ref(null);
const rejectingAdjId = ref(null);

// Forms
const locationForm = useForm({
    id: null,
    code: '',
    name: '',
    type: 'STORE_WAREHOUSE',
    is_stock_bearing: true,
    is_external: false,
    address: '',
    valid_from: '',
    valid_to: '',
});

const transferForm = useForm({
    source_location_id: '',
    destination_location_id: '',
    transfer_date: new Date().toISOString().slice(0, 10),
    remarks: '',
    items: [{ inventory_batch_id: '', transfer_qty: 1 }],
});

const adjForm = useForm({
    adjustment_date: new Date().toISOString().slice(0, 10),
    adjustment_type: 'MINUS',
    reason: '',
    items: [{ inventory_batch_id: '', adjustment_qty: 1, notes: '' }],
});

const rejectAdjForm = useForm({
    rejection_notes: '',
});

const opnameForm = useForm({
    inventory_location_id: '',
    opname_date: new Date().toISOString().slice(0, 10),
});

const countForm = useForm({
    items: [],
});

const planForm = useForm({
    id: null,
    product_variant_id: '',
    location_id: '',
    position_code: '',
    notes: '',
    is_active: true,
});

// Dynamic form rows
function addTransferItem() {
    transferForm.items.push({ inventory_batch_id: '', transfer_qty: 1 });
}
function removeTransferItem(index) {
    transferForm.items.splice(index, 1);
}

function addAdjItem() {
    adjForm.items.push({ inventory_batch_id: '', adjustment_qty: 1, notes: '' });
}
function removeAdjItem(index) {
    adjForm.items.splice(index, 1);
}

// Watch location type in Location Form to set stock_bearing default and external fields
watch(() => locationForm.type, (newType) => {
    locationForm.is_stock_bearing = ['STORE_WAREHOUSE', 'RENTED_WAREHOUSE', 'RECEIVING', 'RETURN_AREA', 'DAMAGED_AREA'].includes(newType);
    locationForm.is_external = newType === 'RENTED_WAREHOUSE';
});

// Submissions
function openCreateLocation() {
    locationForm.reset();
    locationForm.id = null;
    showLocationModal.value = true;
}
function openEditLocation(loc) {
    locationForm.id = loc.id;
    locationForm.code = loc.code;
    locationForm.name = loc.name;
    locationForm.type = loc.type;
    locationForm.is_stock_bearing = loc.is_stock_bearing;
    locationForm.is_external = loc.is_external;
    locationForm.address = loc.address || '';
    locationForm.valid_from = loc.valid_from ? loc.valid_from.slice(0, 10) : '';
    locationForm.valid_to = loc.valid_to ? loc.valid_to.slice(0, 10) : '';
    showLocationModal.value = true;
}
function submitLocation() {
    if (locationForm.id) {
        locationForm.put(`/inventory/locations/${locationForm.id}`, {
            onSuccess: () => {
                showLocationModal.value = false;
                locationForm.reset();
            }
        });
    } else {
        locationForm.post('/inventory/locations', {
            onSuccess: () => {
                showLocationModal.value = false;
                locationForm.reset();
            }
        });
    }
}

function submitTransfer() {
    transferForm.post('/inventory/transfers', {
        onSuccess: () => {
            showTransferModal.value = false;
            transferForm.reset();
        }
    });
}
function postTransfer(id) {
    if (confirm('Posting transfer stok ini? Langkah ini akan memperbarui saldo stok di kedua lokasi.')) {
        router.post(`/inventory/transfers/${id}/post`);
    }
}
function cancelTransfer(id) {
    if (confirm('Batalkan transfer stok ini?')) {
        router.post(`/inventory/transfers/${id}/cancel`);
    }
}

function submitAdjustment() {
    adjForm.post('/inventory/adjustments', {
        onSuccess: () => {
            showAdjustmentModal.value = false;
            adjForm.reset();
        }
    });
}
function approveAdjustment(id) {
    if (confirm('Setujui dan Posting adjustment stok ini?')) {
        router.post(`/inventory/adjustments/${id}/approve`);
    }
}
function openRejectAdjustment(id) {
    rejectingAdjId.value = id;
    rejectAdjForm.reset();
    showRejectAdjModal.value = true;
}
function submitRejectAdjustment() {
    rejectAdjForm.post(`/inventory/adjustments/${rejectingAdjId.value}/reject`, {
        onSuccess: () => {
            showRejectAdjModal.value = false;
            rejectingAdjId.value = null;
        }
    });
}

function submitOpname() {
    opnameForm.post('/inventory/opnames', {
        onSuccess: () => {
            showOpnameModal.value = false;
            opnameForm.reset();
        }
    });
}
function openCountOpname(opname) {
    selectedOpname.value = opname;
    countForm.items = opname.items.map(item => ({
        id: item.id,
        product_name: item.product_variant?.product?.product_name || 'Product',
        batch_no: item.batch?.batch_no || '-',
        system_qty: item.system_qty,
        physical_qty: item.physical_qty !== null ? item.physical_qty : item.system_qty,
    }));
    showCountModal.value = true;
}
function submitCount() {
    countForm.put(`/inventory/opnames/${selectedOpname.value.id}/count`, {
        onSuccess: () => {
            showCountModal.value = false;
            selectedOpname.value = null;
        }
    });
}
function approveOpname(id) {
    if (confirm('Setujui hasil Stock Opname ini?')) {
        router.post(`/inventory/opnames/${id}/approve`);
    }
}
function postOpname(id) {
    if (confirm('Posting Stock Opname ini? Saldo stok fisik akan menggantikan stok sistem.')) {
        router.post(`/inventory/opnames/${id}/post`);
    }
}

function openCreatePlanogram() {
    planForm.reset();
    planForm.id = null;
    showPlanogramModal.value = true;
}
function openEditPlanogram(plan) {
    planForm.id = plan.id;
    planForm.product_variant_id = plan.product_variant_id;
    planForm.location_id = plan.location_id;
    planForm.position_code = plan.position_code;
    planForm.notes = plan.notes || '';
    planForm.is_active = plan.is_active;
    showPlanogramModal.value = true;
}
function submitPlanogram() {
    if (planForm.id) {
        planForm.put(`/inventory/planograms/${planForm.id}`, {
            onSuccess: () => {
                showPlanogramModal.value = false;
                planForm.reset();
            }
        });
    } else {
        planForm.post('/inventory/planograms', {
            onSuccess: () => {
                showPlanogramModal.value = false;
                planForm.reset();
            }
        });
    }
}
function deletePlanogram(row) {
    if (confirm(`Hapus planogram "${row.position_code}"?`)) {
        router.delete(`/inventory/planograms/${row.id}`);
    }
}

// KPI aggregations
const sumValue = () => props.inventoryBalance.reduce((s, r) => s + parseFloat(r.balance_value || 0), 0);
const lowStockCount = () => props.inventoryBalance.filter(r => r.qty_on_hand <= r.reorder_level).length;
const totalSku = () => props.inventoryBalance.length;

// Columns
const balanceColumns = [
    { key: 'no', label: 'No' },
    { key: 'product', label: 'Product' },
    { key: 'location', label: 'Location' },
    { key: 'qty_on_hand', label: 'Quantity', align: 'right' },
    { key: 'qty_reserved', label: 'Reserved', align: 'right' },
    { key: 'qty_available', label: 'Available', align: 'right' },
    { key: 'status', label: 'Status', align: 'center' },
];

const movementColumns = [
    { key: 'no', label: 'No' },
    { key: 'product', label: 'Product' },
    { key: 'location', label: 'Location' },
    { key: 'reference_number', label: 'Ref No' },
    { key: 'transaction_date', label: 'Date' },
    { key: 'transaction_type', label: 'Type', align: 'center' },
    { key: 'qty_change', label: 'Quantity', align: 'right' },
];

const locationColumns = [
    { key: 'no', label: 'No' },
    { key: 'code', label: 'Code' },
    { key: 'name', label: 'Name' },
    { key: 'type', label: 'Type' },
    { key: 'is_stock_bearing', label: 'Stock Bearing', align: 'center' },
    { key: 'is_external', label: 'External', align: 'center' },
    { key: 'actions', label: 'Actions', align: 'center' },
];

const transferColumns = [
    { key: 'no', label: 'No' },
    { key: 'transfer_number', label: 'Transfer No' },
    { key: 'source', label: 'Source' },
    { key: 'destination', label: 'Destination' },
    { key: 'transfer_date', label: 'Date' },
    { key: 'status', label: 'Status', align: 'center' },
    { key: 'actions', label: 'Actions', align: 'center' },
];

const adjColumns = [
    { key: 'no', label: 'No' },
    { key: 'adjustment_number', label: 'Adj No' },
    { key: 'adjustment_date', label: 'Date' },
    { key: 'adjustment_type', label: 'Type', align: 'center' },
    { key: 'reason', label: 'Reason' },
    { key: 'status', label: 'Status', align: 'center' },
    { key: 'actions', label: 'Actions', align: 'center' },
];

const opnameColumns = [
    { key: 'no', label: 'No' },
    { key: 'opname_number', label: 'Opname No' },
    { key: 'location', label: 'Location' },
    { key: 'opname_date', label: 'Date' },
    { key: 'status', label: 'Status', align: 'center' },
    { key: 'actions', label: 'Actions', align: 'center' },
];

const planogramColumns = [
    { key: 'no', label: 'No' },
    { key: 'position_code', label: 'Position Code' },
    { key: 'location', label: 'Location' },
    { key: 'product', label: 'Product / Variant' },
    { key: 'notes', label: 'Notes' },
    { key: 'actions', label: 'Actions', align: 'center' },
];
</script>

<template>
    <Head title="Inventory & Planogram Management" />

    <DashboardLayout>
        <div class="mb-6 flex justify-between items-center flex-wrap gap-md">
            <div>
                <h1 class="text-2xl font-bold text-ink-primary">Inventory Management 📦</h1>
                <p class="text-ink-secondary text-sm">
                    Kelola saldo stok, pergerakan kartu stok, lokasi gudang, transfer barang, adjustment, dan planogram display.
                </p>
            </div>

            <div class="flex gap-md">
                <BaseButton v-if="activeTab === 'locations'" @click="openCreateLocation">
                    ➕ New Location
                </BaseButton>
                <BaseButton v-if="activeTab === 'transfers'" @click="showTransferModal = true">
                    📦 New Transfer
                </BaseButton>
                <BaseButton v-if="activeTab === 'adjustments'" @click="showAdjustmentModal = true">
                    ⚖️ New Adjustment
                </BaseButton>
                <BaseButton v-if="activeTab === 'opnames'" @click="showOpnameModal = true">
                    📋 Start Opname
                </BaseButton>
                <BaseButton v-if="activeTab === 'planograms'" @click="openCreatePlanogram">
                    📐 New Planogram
                </BaseButton>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-base mb-xl">
            <KPICard label="Total SKU" :value="totalSku()" icon="layers" color="brand" />
            <KPICard label="Total Nilai Aset" :value="formatCurrency(sumValue())" icon="dollar-sign" color="mint" />
            <KPICard label="Stok Menipis" :value="lowStockCount()" icon="alert-triangle" color="sunny" />
        </div>

        <!-- Tabs -->
        <div class="flex space-x-1 mb-6 border-b border-border-soft overflow-x-auto">
            <button v-for="tab in [
                { key: 'balance', label: 'Stock Balances 📊' },
                { key: 'movements', label: 'Stock Movements 🔄' },
                { key: 'locations', label: 'Locations 📍' },
                { key: 'transfers', label: 'Transfers 📦' },
                { key: 'adjustments', label: 'Adjustments ⚖️' },
                { key: 'opnames', label: 'Stock Opnames 📋' },
                { key: 'planograms', label: 'Planograms 📐' }
            ]" :key="tab.key" @click="activeTab = tab.key"
                :class="activeTab === tab.key ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-3 px-6 font-medium transition-colors duration-200 cursor-pointer text-sm whitespace-nowrap"
            >
                {{ tab.label }}
            </button>
        </div>

        <!-- Tab 1: Stock Balances -->
        <div v-if="activeTab === 'balance'" class="card-friendly p-lg">
            <DataTable :columns="balanceColumns" :rows="inventoryBalance">
                <template #cell-product="{ row }">
                    <span class="font-semibold text-ink-primary">{{ row.variant?.product?.product_name || '-' }}</span>
                </template>
                <template #cell-location="{ row }">
                    <span class="chip bg-accent-sky-soft text-accent-sky px-md py-0.5 text-xs">{{ row.location?.name || '-' }}</span>
                </template>
                <template #cell-qty_on_hand="{ value }">
                    <span class="font-mono font-semibold">{{ value }}</span>
                </template>
                <template #cell-qty_reserved="{ value }">
                    <span class="font-mono text-ink-muted">{{ value }}</span>
                </template>
                <template #cell-qty_available="{ value }">
                    <span class="font-mono font-bold text-brand">{{ value }}</span>
                </template>
                <template #cell-status="{ row }">
                    <span v-if="row.qty_on_hand <= row.reorder_level" class="chip bg-semantic-danger-soft text-semantic-danger px-md py-0.5 text-xs font-semibold">⚠️ Low Stock</span>
                    <span v-else class="chip bg-accent-mint-soft text-accent-mint px-md py-0.5 text-xs font-semibold">✓ OK</span>
                </template>
            </DataTable>
        </div>

        <!-- Tab 2: Movements -->
        <div v-if="activeTab === 'movements'" class="card-friendly p-lg">
            <DataTable :columns="movementColumns" :rows="movements">
                <template #cell-product="{ row }">
                    <span class="font-semibold text-ink-primary">{{ row.variant?.product?.product_name || '-' }}</span>
                </template>
                <template #cell-location="{ row }">
                    <span>{{ row.location?.name || '-' }}</span>
                </template>
                <template #cell-reference_number="{ value }">
                    <span class="font-mono text-ink-muted">{{ value }}</span>
                </template>
                <template #cell-transaction_date="{ value }">
                    <span>{{ formatDate(value) }}</span>
                </template>
                <template #cell-transaction_type="{ value }">
                    <span class="chip px-md py-0.5 text-xs font-semibold bg-surface-subtle border border-border-soft">{{ value }}</span>
                </template>
                <template #cell-qty_change="{ value }">
                    <span :class="value >= 0 ? 'text-accent-mint' : 'text-semantic-danger'" class="font-mono font-bold">{{ value >= 0 ? '+' : '' }}{{ value }}</span>
                </template>
            </DataTable>
        </div>

        <!-- Tab 3: Locations -->
        <div v-if="activeTab === 'locations'" class="card-friendly p-lg">
            <DataTable :columns="locationColumns" :rows="locations">
                <template #cell-is_stock_bearing="{ value }">
                    <span :class="value ? 'text-accent-mint' : 'text-ink-muted'">{{ value ? '✓ Yes' : '✗ No' }}</span>
                </template>
                <template #cell-is_external="{ value }">
                    <span :class="value ? 'text-sunny' : 'text-ink-muted'">{{ value ? '✓ Yes' : '✗ No' }}</span>
                </template>
                <template #cell-actions="{ row }">
                    <button @click="openEditLocation(row)" class="text-brand hover:underline text-sm font-semibold">Edit</button>
                </template>
            </DataTable>
        </div>

        <!-- Tab 4: Transfers -->
        <div v-if="activeTab === 'transfers'" class="card-friendly p-lg">
            <DataTable :columns="transferColumns" :rows="transfers">
                <template #cell-transfer_number="{ row }">
                    <button @click="selectedTransfer = row" class="font-mono text-brand hover:underline font-bold text-left">{{ row.transfer_number }}</button>
                </template>
                <template #cell-source="{ row }">
                    <span>{{ row.source?.name }}</span>
                </template>
                <template #cell-destination="{ row }">
                    <span>{{ row.destination?.name }}</span>
                </template>
                <template #cell-transfer_date="{ value }">
                    <span>{{ formatDate(value) }}</span>
                </template>
                <template #cell-status="{ value }">
                    <StatusBadge :status="value" variant="soft" size="sm" />
                </template>
                <template #cell-actions="{ row }">
                    <div class="flex gap-md justify-center">
                        <button v-if="row.status === 'DRAFT'" @click="postTransfer(row.id)" class="text-brand hover:underline text-sm font-semibold">Post</button>
                        <button v-if="row.status === 'DRAFT'" @click="cancelTransfer(row.id)" class="text-semantic-danger hover:underline text-sm font-semibold">Cancel</button>
                        <button @click="selectedTransfer = row" class="text-ink-secondary hover:underline text-sm font-semibold">Detail</button>
                    </div>
                </template>
            </DataTable>
        </div>

        <!-- Tab 5: Adjustments -->
        <div v-if="activeTab === 'adjustments'" class="card-friendly p-lg">
            <DataTable :columns="adjColumns" :rows="adjustments">
                <template #cell-adjustment_number="{ row }">
                    <button @click="selectedAdjustment = row" class="font-mono text-brand hover:underline font-bold text-left">{{ row.adjustment_number }}</button>
                </template>
                <template #cell-adjustment_date="{ value }">
                    <span>{{ formatDate(value) }}</span>
                </template>
                <template #cell-adjustment_type="{ value }">
                    <span :class="value === 'PLUS' ? 'bg-accent-mint-soft text-accent-mint' : 'bg-semantic-danger-soft text-semantic-danger'" class="chip px-md py-0.5 text-xs font-semibold">{{ value }}</span>
                </template>
                <template #cell-status="{ value }">
                    <StatusBadge :status="value" variant="soft" size="sm" />
                </template>
                <template #cell-actions="{ row }">
                    <div class="flex gap-md justify-center">
                        <button v-if="row.status === 'PENDING'" @click="approveAdjustment(row.id)" class="text-brand hover:underline text-sm font-semibold">Approve</button>
                        <button v-if="row.status === 'PENDING'" @click="openRejectAdjustment(row.id)" class="text-semantic-danger hover:underline text-sm font-semibold">Reject</button>
                        <button @click="selectedAdjustment = row" class="text-ink-secondary hover:underline text-sm font-semibold">Detail</button>
                    </div>
                </template>
            </DataTable>
        </div>

        <!-- Tab 6: Opnames -->
        <div v-if="activeTab === 'opnames'" class="card-friendly p-lg">
            <DataTable :columns="opnameColumns" :rows="opnames">
                <template #cell-opname_number="{ row }">
                    <button @click="selectedOpname = row" class="font-mono text-brand hover:underline font-bold text-left">{{ row.opname_number }}</button>
                </template>
                <template #cell-location="{ row }">
                    <span>{{ row.location?.name }}</span>
                </template>
                <template #cell-opname_date="{ value }">
                    <span>{{ formatDate(value) }}</span>
                </template>
                <template #cell-status="{ value }">
                    <StatusBadge :status="value" variant="soft" size="sm" />
                </template>
                <template #cell-actions="{ row }">
                    <div class="flex gap-md justify-center">
                        <button v-if="row.status === 'DRAFT'" @click="openCountOpname(row)" class="text-brand hover:underline text-sm font-semibold">Input Count</button>
                        <button v-if="row.status === 'DRAFT'" @click="approveOpname(row.id)" class="text-brand hover:underline text-sm font-semibold">Approve</button>
                        <button v-if="row.status === 'APPROVED'" @click="postOpname(row.id)" class="text-brand hover:underline text-sm font-semibold">Post Opname</button>
                    </div>
                </template>
            </DataTable>
        </div>

        <!-- Tab 7: Planograms -->
        <div v-if="activeTab === 'planograms'" class="card-friendly p-lg">
            <DataTable :columns="planogramColumns" :rows="planograms">
                <template #cell-position_code="{ value }">
                    <span class="font-mono font-bold text-brand">{{ value }}</span>
                </template>
                <template #cell-location="{ row }">
                    <span>{{ row.location?.name }}</span>
                </template>
                <template #cell-product="{ row }">
                    <span class="font-semibold text-ink-primary">{{ row.variant?.product?.product_name }} ({{ row.variant?.product_code }})</span>
                </template>
                <template #cell-actions="{ row }">
                    <div class="flex gap-md justify-center">
                        <button @click="openEditPlanogram(row)" class="text-brand hover:underline text-sm font-semibold">Edit</button>
                        <button @click="deletePlanogram(row)" class="text-semantic-danger hover:underline text-sm font-semibold">Delete</button>
                    </div>
                </template>
            </DataTable>
        </div>

        <!-- Modals -->
        <!-- Location Modal -->
        <BaseModal :show="showLocationModal" @close="showLocationModal = false" :title="locationForm.id ? 'Edit Location' : 'Create Location'">
            <form @submit.prevent="submitLocation" class="space-y-md">
                <div class="grid grid-cols-2 gap-md">
                    <FormInput label="Location Code" v-model="locationForm.code" required />
                    <FormInput label="Location Name" v-model="locationForm.name" required />
                </div>
                <FormSelect label="Location Type" v-model="locationForm.type" required>
                    <option value="STORE_WAREHOUSE">Store Warehouse</option>
                    <option value="RENTED_WAREHOUSE">Rented Warehouse</option>
                    <option value="RACK">Rack (Planogram)</option>
                    <option value="DISPLAY">Display (Planogram)</option>
                    <option value="RECEIVING">Receiving Area</option>
                    <option value="RETURN_AREA">Return Area</option>
                    <option value="DAMAGED_AREA">Damaged Area</option>
                </FormSelect>
                <div class="grid grid-cols-2 gap-md pt-sm">
                    <label class="flex items-center gap-sm">
                        <input type="checkbox" v-model="locationForm.is_stock_bearing" />
                        <span class="text-sm font-semibold text-ink-primary">Stock Bearing (Menampung Saldo Stok)</span>
                    </label>
                    <label class="flex items-center gap-sm">
                        <input type="checkbox" v-model="locationForm.is_external" />
                        <span class="text-sm font-semibold text-ink-primary">External Location (Gudang Sewa/Pihak Ketiga)</span>
                    </label>
                </div>
                <FormTextarea label="Address" v-model="locationForm.address" />
                <div class="grid grid-cols-2 gap-md" v-if="locationForm.is_external">
                    <FormInput type="date" label="Valid From" v-model="locationForm.valid_from" required />
                    <FormInput type="date" label="Valid To" v-model="locationForm.valid_to" required />
                </div>

                <div class="flex justify-end gap-md pt-md border-t border-border-soft">
                    <BaseButton type="button" variant="secondary" @click="showLocationModal = false">Cancel</BaseButton>
                    <BaseButton type="submit">Save Location</BaseButton>
                </div>
            </form>
        </BaseModal>

        <!-- New Transfer Modal -->
        <BaseModal :show="showTransferModal" @close="showTransferModal = false" title="Create Stock Transfer">
            <form @submit.prevent="submitTransfer" class="space-y-md">
                <div class="grid grid-cols-2 gap-md">
                    <FormSelect label="Source Location" v-model="transferForm.source_location_id" required>
                        <option value="">-- Select Source --</option>
                        <option v-for="l in locations.filter(loc => loc.is_stock_bearing)" :key="l.id" :value="l.id">{{ l.name }}</option>
                    </FormSelect>
                    <FormSelect label="Destination Location" v-model="transferForm.destination_location_id" required>
                        <option value="">-- Select Destination --</option>
                        <option v-for="l in locations.filter(loc => loc.is_stock_bearing)" :key="l.id" :value="l.id">{{ l.name }}</option>
                    </FormSelect>
                </div>
                <div class="grid grid-cols-2 gap-md">
                    <FormInput type="date" label="Transfer Date" v-model="transferForm.transfer_date" required />
                    <FormInput label="Remarks" v-model="transferForm.remarks" />
                </div>

                <h4 class="font-bold text-sm text-ink-primary pt-md border-t border-border-soft">Items</h4>
                <div v-for="(item, idx) in transferForm.items" :key="idx" class="grid grid-cols-12 gap-md items-end">
                    <div class="col-span-6">
                        <FormSelect label="Batch / Product" v-model="item.inventory_batch_id" required>
                            <option value="">-- Select Batch --</option>
                            <option v-for="b in batches.filter(x => x.location_id == transferForm.source_location_id)" :key="b.id" :value="b.id">
                                {{ b.variant?.product?.product_name }} (Batch: {{ b.batch_no }}, Qty: {{ b.quantity }})
                            </option>
                        </FormSelect>
                    </div>
                    <div class="col-span-3">
                        <FormInput type="number" label="Qty" v-model="item.transfer_qty" required />
                    </div>
                    <div class="col-span-3 pb-md">
                        <button type="button" @click="removeTransferItem(idx)" class="text-semantic-danger hover:underline text-xs font-semibold">Delete</button>
                    </div>
                </div>
                <button type="button" @click="addTransferItem" class="text-brand hover:underline text-sm font-semibold">+ Add Item</button>

                <div class="flex justify-end gap-md pt-md border-t border-border-soft">
                    <BaseButton type="button" variant="secondary" @click="showTransferModal = false">Cancel</BaseButton>
                    <BaseButton type="submit">Create Transfer</BaseButton>
                </div>
            </form>
        </BaseModal>

        <!-- New Adjustment Modal -->
        <BaseModal :show="showAdjustmentModal" @close="showAdjustmentModal = false" title="Create Stock Adjustment">
            <form @submit.prevent="submitAdjustment" class="space-y-md">
                <div class="grid grid-cols-2 gap-md">
                    <FormSelect label="Adjustment Type" v-model="adjForm.adjustment_type" required>
                        <option value="PLUS">PLUS (Stock In / Found)</option>
                        <option value="MINUS">MINUS (Stock Out / Broken)</option>
                    </FormSelect>
                    <FormInput type="date" label="Adjustment Date" v-model="adjForm.adjustment_date" required />
                </div>
                <FormInput label="Reason" v-model="adjForm.reason" placeholder="Barang pecah / stock opname selisih..." required />

                <h4 class="font-bold text-sm text-ink-primary pt-md border-t border-border-soft">Items</h4>
                <div v-for="(item, idx) in adjForm.items" :key="idx" class="grid grid-cols-12 gap-md items-end">
                    <div class="col-span-5">
                        <FormSelect label="Batch / Product" v-model="item.inventory_batch_id" required>
                            <option value="">-- Select Batch --</option>
                            <option v-for="b in batches" :key="b.id" :value="b.id">
                                {{ b.variant?.product?.product_name }} (Loc: {{ b.location?.name }}, Qty: {{ b.quantity }})
                            </option>
                        </FormSelect>
                    </div>
                    <div class="col-span-3">
                        <FormInput type="number" label="Qty" v-model="item.adjustment_qty" required />
                    </div>
                    <div class="col-span-3">
                        <FormInput label="Notes" v-model="item.notes" />
                    </div>
                    <div class="col-span-1 pb-md">
                        <button type="button" @click="removeAdjItem(idx)" class="text-semantic-danger hover:underline text-xs font-semibold">Delete</button>
                    </div>
                </div>
                <button type="button" @click="addAdjItem" class="text-brand hover:underline text-sm font-semibold">+ Add Item</button>

                <div class="flex justify-end gap-md pt-md border-t border-border-soft">
                    <BaseButton type="button" variant="secondary" @click="showAdjustmentModal = false">Cancel</BaseButton>
                    <BaseButton type="submit">Create Adjustment</BaseButton>
                </div>
            </form>
        </BaseModal>

        <!-- New Opname Modal -->
        <BaseModal :show="showOpnameModal" @close="showOpnameModal = false" title="Start Stock Opname">
            <form @submit.prevent="submitOpname" class="space-y-md">
                <FormSelect label="Location to Audit" v-model="opnameForm.inventory_location_id" required>
                    <option value="">-- Select Location --</option>
                    <option v-for="l in locations.filter(loc => loc.is_stock_bearing)" :key="l.id" :value="l.id">{{ l.name }}</option>
                </FormSelect>
                <FormInput type="date" label="Opname Date" v-model="opnameForm.opname_date" required />

                <div class="flex justify-end gap-md pt-md border-t border-border-soft">
                    <BaseButton type="button" variant="secondary" @click="showOpnameModal = false">Cancel</BaseButton>
                    <BaseButton type="submit">Start Opname</BaseButton>
                </div>
            </form>
        </BaseModal>

        <!-- Count Opname Modal -->
        <BaseModal :show="showCountModal" @close="showCountModal = false" title="Input Physical Counts">
            <form @submit.prevent="submitCount" class="space-y-md" v-if="selectedOpname">
                <h4 class="font-bold text-sm text-ink-primary mb-md">Opname: {{ selectedOpname.opname_number }}</h4>
                
                <div class="space-y-sm max-h-96 overflow-y-auto">
                    <div v-for="(item, idx) in countForm.items" :key="item.id" class="p-md bg-surface-subtle border border-border-soft rounded-lg space-y-xs">
                        <div class="flex justify-between font-semibold text-sm">
                            <span>{{ item.product_name }}</span>
                            <span class="text-ink-secondary">System Qty: {{ item.system_qty }}</span>
                        </div>
                        <FormInput type="number" label="Physical Qty Found" v-model="item.physical_qty" required />
                    </div>
                </div>

                <div class="flex justify-end gap-md pt-md border-t border-border-soft">
                    <BaseButton type="button" variant="secondary" @click="showCountModal = false">Cancel</BaseButton>
                    <BaseButton type="submit">Save Counts</BaseButton>
                </div>
            </form>
        </BaseModal>

        <!-- Planogram Modal -->
        <BaseModal :show="showPlanogramModal" @close="showPlanogramModal = false" :title="planForm.id ? 'Edit Planogram' : 'Create Planogram'">
            <form @submit.prevent="submitPlanogram" class="space-y-md">
                <div class="grid grid-cols-2 gap-md">
                    <FormSelect label="Location" v-model="planForm.location_id" required>
                        <option value="">-- Select Location --</option>
                        <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name }} ({{ l.type }})</option>
                    </FormSelect>
                    <FormInput label="Position Code" v-model="planForm.position_code" placeholder="A-02-03" required />
                </div>
                <FormSelect label="Product Variant" v-model="planForm.product_variant_id" required>
                    <option value="">-- Select Variant --</option>
                    <option v-for="v in variants" :key="v.id" :value="v.id">{{ v.product?.product_name }} - {{ v.variant_name || 'Default' }} ({{ v.product_code }})</option>
                </FormSelect>
                <FormInput label="Notes" v-model="planForm.notes" />
                <FormSelect label="Status" v-model="planForm.is_active" required>
                    <option :value="true">Active</option>
                    <option :value="false">Inactive</option>
                </FormSelect>

                <div class="flex justify-end gap-md pt-md border-t border-border-soft">
                    <BaseButton type="button" variant="secondary" @click="showPlanogramModal = false">Cancel</BaseButton>
                    <BaseButton type="submit">Save Planogram</BaseButton>
                </div>
            </form>
        </BaseModal>

        <!-- Reject Adjustment Modal -->
        <BaseModal :show="showRejectAdjModal" @close="showRejectAdjModal = false" title="Reject Stock Adjustment">
            <form @submit.prevent="submitRejectAdjustment" class="space-y-md">
                <FormInput label="Reason for Rejection" v-model="rejectAdjForm.rejection_notes" required />
                <div class="flex justify-end gap-md pt-md border-t border-border-soft">
                    <BaseButton type="button" variant="secondary" @click="showRejectAdjModal = false">Cancel</BaseButton>
                    <BaseButton type="submit" variant="danger">Reject Adjustment</BaseButton>
                </div>
            </form>
        </BaseModal>

        <!-- Details Modals -->
        <!-- Transfer Detail Modal -->
        <BaseModal :show="!!selectedTransfer" @close="selectedTransfer = null" title="Stock Transfer Details">
            <div v-if="selectedTransfer" class="space-y-md">
                <div class="grid grid-cols-2 gap-md text-sm border-b border-border-soft pb-md">
                    <div>
                        <span class="text-ink-muted block">Transfer Number</span>
                        <span class="font-mono font-bold text-ink-primary">{{ selectedTransfer.transfer_number }}</span>
                    </div>
                    <div>
                        <span class="text-ink-muted block">Date</span>
                        <span class="font-medium text-ink-primary">{{ formatDate(selectedTransfer.transfer_date) }}</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-md text-sm border-b border-border-soft pb-md">
                    <div>
                        <span class="text-ink-muted block">Source Location</span>
                        <span class="font-bold text-ink-primary">{{ selectedTransfer.source?.name }}</span>
                    </div>
                    <div>
                        <span class="text-ink-muted block">Destination Location</span>
                        <span class="font-bold text-ink-primary">{{ selectedTransfer.destination?.name }}</span>
                    </div>
                </div>
                <div class="text-sm border-b border-border-soft pb-md" v-if="selectedTransfer.remarks">
                    <span class="text-ink-muted block">Remarks</span>
                    <span>{{ selectedTransfer.remarks }}</span>
                </div>

                <h4 class="font-bold text-sm text-ink-primary">Transfer Items</h4>
                <div class="space-y-xs">
                    <div v-for="item in selectedTransfer.items" :key="item.id" class="flex justify-between items-center bg-surface-subtle p-md rounded-xl text-sm border border-border-soft">
                        <span>{{ item.product_variant?.product?.product_name }}</span>
                        <span class="font-mono font-bold text-brand">{{ item.transfer_qty }} unit</span>
                    </div>
                </div>
            </div>
        </BaseModal>

        <!-- Adjustment Detail Modal -->
        <BaseModal :show="!!selectedAdjustment" @close="selectedAdjustment = null" title="Stock Adjustment Details">
            <div v-if="selectedAdjustment" class="space-y-md">
                <div class="grid grid-cols-2 gap-md text-sm border-b border-border-soft pb-md">
                    <div>
                        <span class="text-ink-muted block">Adjustment Number</span>
                        <span class="font-mono font-bold text-ink-primary">{{ selectedAdjustment.adjustment_number }}</span>
                    </div>
                    <div>
                        <span class="text-ink-muted block">Date</span>
                        <span class="font-medium text-ink-primary">{{ formatDate(selectedAdjustment.adjustment_date) }}</span>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-md text-sm border-b border-border-soft pb-md">
                    <div>
                        <span class="text-ink-muted block">Adjustment Type</span>
                        <span :class="selectedAdjustment.adjustment_type === 'PLUS' ? 'text-accent-mint' : 'text-semantic-danger'" class="font-bold">{{ selectedAdjustment.adjustment_type }}</span>
                    </div>
                    <div>
                        <span class="text-ink-muted block">Status</span>
                        <StatusBadge :status="selectedAdjustment.status" size="sm" variant="soft" />
                    </div>
                    <div>
                        <span class="text-ink-muted block">Reason</span>
                        <span>{{ selectedAdjustment.reason }}</span>
                    </div>
                </div>

                <h4 class="font-bold text-sm text-ink-primary">Adjustment Lines</h4>
                <div class="space-y-xs">
                    <div v-for="item in selectedAdjustment.items" :key="item.id" class="p-md bg-surface-subtle rounded-xl text-sm border border-border-soft space-y-xs">
                        <div class="flex justify-between font-semibold">
                            <span>{{ item.product_variant?.product?.product_name }}</span>
                            <span class="font-mono font-bold" :class="selectedAdjustment.adjustment_type === 'PLUS' ? 'text-accent-mint' : 'text-semantic-danger'">
                                {{ selectedAdjustment.adjustment_type === 'PLUS' ? '+' : '-' }}{{ item.adjustment_qty }}
                            </span>
                        </div>
                        <span class="text-xs text-ink-muted block" v-if="item.notes">Notes: {{ item.notes }}</span>
                    </div>
                </div>
            </div>
        </BaseModal>
    </DashboardLayout>
</template>