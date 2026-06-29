<script setup>
import { ref, computed, watch } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import DataTable from '@/Components/Table/DataTable.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import BaseModal from '@/Components/Modal/BaseModal.vue';
import FormInput from '@/Components/Form/FormInput.vue';
import FormSelect from '@/Components/Form/FormSelect.vue';
import StatusBadge from '@/Components/DataDisplay/StatusBadge.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { formatCurrency, formatDate } from '@/Utils/formatters';

const props = defineProps({
    purchaseRequests: { type: Array, default: () => [] },
    purchaseOrders: { type: Array, default: () => [] },
    goodsReceipts: { type: Array, default: () => [] },
    supplierInvoices: { type: Array, default: () => [] },
    supplierPayments: { type: Array, default: () => [] },
    accountsPayables: { type: Array, default: () => [] },
    purchaseReturns: { type: Array, default: () => [] },
    landedCosts: { type: Array, default: () => [] },
    supplierPerformances: { type: Array, default: () => [] },
    
    // Master data for dropdowns
    suppliers: { type: Array, default: () => [] },
    variants: { type: Array, default: () => [] },
    locations: { type: Array, default: () => [] },
    paymentMethods: { type: Array, default: () => [] },
    activeTab: { type: String, default: 'orders' },
});

const activeTab = ref(props.activeTab || 'orders');

// Modals State
const showPrModal = ref(false);
const showGrModal = ref(false);
const showSiModal = ref(false);
const showSpModal = ref(false);
const showRetModal = ref(false);
const showLcModal = ref(false);
const showPerfModal = ref(false);
const showRejectPrModal = ref(false);

const selectedPr = ref(null);
const selectedGr = ref(null);
const selectedSi = ref(null);
const selectedSp = ref(null);
const selectedRet = ref(null);
const selectedSupplierIdForPerf = ref(null);
const rejectingPrId = ref(null);

// Forms
const prForm = useForm({
    request_date: new Date().toISOString().slice(0, 10),
    remarks: '',
    items: [{ product_variant_id: '', requested_qty: 1, notes: '' }],
});

const rejectPrForm = useForm({
    rejection_notes: '',
});

const grForm = useForm({
    purchase_order_id: '',
    location_id: '',
    receipt_date: new Date().toISOString().slice(0, 10),
    remarks: '',
    items: [],
});

const siForm = useForm({
    supplier_id: '',
    goods_receipt_id: '',
    supplier_invoice_no: '',
    invoice_date: new Date().toISOString().slice(0, 10),
    due_date: new Date().toISOString().slice(0, 10),
    remarks: '',
    items: [],
});

const spForm = useForm({
    supplier_id: '',
    payment_date: new Date().toISOString().slice(0, 10),
    payment_method: 'TRANSFER',
    reference_no: '',
    payment_method_account_id: '',
    remarks: '',
    allocations: [],
});

const retForm = useForm({
    goods_receipt_id: '',
    supplier_id: '',
    return_date: new Date().toISOString().slice(0, 10),
    reason: '',
    items: [],
});

const lcForm = useForm({
    goods_receipt_id: '',
    cost_type: 'FREIGHT',
    amount: 0,
    allocation_method: 'BY_QTY',
    notes: '',
});

const perfForm = useForm({
    evaluation_period: new Date().toISOString().slice(0, 10),
    on_time_delivery_score: 100,
    price_score: 100,
    quality_score: 100,
    service_score: 100,
    notes: '',
});

// Dynamic items helpers
function addPrItem() {
    prForm.items.push({ product_variant_id: '', requested_qty: 1, notes: '' });
}
function removePrItem(index) {
    prForm.items.splice(index, 1);
}

// Watch selected PO for GR Form items initialization
watch(() => grForm.purchase_order_id, (newVal) => {
    if (!newVal) {
        grForm.items = [];
        return;
    }
    const po = props.purchaseOrders.find(o => o.id === parseInt(newVal));
    if (po) {
        grForm.items = po.items.map(item => ({
            purchase_order_item_id: item.id,
            product_variant_id: item.product_variant_id,
            product_name: item.product_variant?.product?.product_name || 'Product',
            ordered_qty: item.ordered_qty,
            received_qty: item.ordered_qty - item.received_qty,
            unit_cost: item.unit_cost,
            batch_no: '',
            expiry_date: '',
        })).filter(item => item.received_qty > 0);
    }
});

// Watch selected GR for Return Form items initialization
watch(() => retForm.goods_receipt_id, (newVal) => {
    if (!newVal) {
        retForm.items = [];
        return;
    }
    const gr = props.goodsReceipts.find(r => r.id === parseInt(newVal));
    if (gr) {
        retForm.supplier_id = gr.purchase_order?.supplier_id || '';
        retForm.items = gr.items.map(item => ({
            goods_receipt_item_id: item.id,
            product_variant_id: item.product_variant_id,
            product_name: item.product_variant?.product?.product_name || 'Product',
            received_qty: item.received_qty,
            return_qty: item.received_qty,
            unit_cost: item.unit_cost,
        }));
    }
});

// Watch selected GR for Invoice Form items initialization
watch(() => siForm.goods_receipt_id, (newVal) => {
    if (!newVal) {
        siForm.items = [];
        return;
    }
    const gr = props.goodsReceipts.find(r => r.id === parseInt(newVal));
    if (gr) {
        siForm.supplier_id = gr.purchase_order?.supplier_id || '';
        siForm.items = gr.items.map(item => ({
            product_variant_id: item.product_variant_id,
            product_name: item.product_variant?.product?.product_name || 'Product',
            qty: item.received_qty,
            unit_cost: item.unit_cost,
            tax_amount: 0,
        }));
    }
});

// Watch supplier in payment form to populate allocations to unpaid accounts payables
watch(() => spForm.supplier_id, (newVal) => {
    if (!newVal) {
        spForm.allocations = [];
        return;
    }
    const openAp = props.accountsPayables.filter(ap => ap.supplier_id === parseInt(newVal) && ap.status !== 'PAID');
    spForm.allocations = openAp.map(ap => ({
        account_payable_id: ap.id,
        payable_number: ap.payable_number,
        due_date: ap.due_date,
        remaining_amount: ap.remaining_amount,
        allocated_amount: ap.remaining_amount,
    }));
});

// Submissions
function submitPr() {
    prForm.post('/purchasing/requests', {
        onSuccess: () => {
            showPrModal.value = false;
            prForm.reset();
        }
    });
}
function approvePr(id) {
    if (confirm('Setujui Purchase Request ini?')) {
        router.post(`/purchasing/requests/${id}/approve`);
    }
}
function openRejectPr(id) {
    rejectingPrId.value = id;
    rejectPrForm.reset();
    showRejectPrModal.value = true;
}
function submitRejectPr() {
    rejectPrForm.post(`/purchasing/requests/${rejectingPrId.value}/reject`, {
        onSuccess: () => {
            showRejectPrModal.value = false;
            rejectingPrId.value = null;
        }
    });
}

function submitGr() {
    grForm.post('/purchasing/receipts', {
        onSuccess: () => {
            showGrModal.value = false;
            grForm.reset();
        }
    });
}
function postGr(id) {
    if (confirm('Posting Goods Receipt ini? Ini akan menambah stok inventaris dan memperbarui status pesanan.')) {
        router.post(`/purchasing/receipts/${id}/post`);
    }
}

function submitSi() {
    siForm.post('/purchasing/invoices', {
        onSuccess: () => {
            showSiModal.value = false;
            siForm.reset();
        }
    });
}
function postSi(id) {
    if (confirm('Posting Supplier Invoice ini? Ini akan membentuk Accounts Payable (Utang).')) {
        router.post(`/purchasing/invoices/${id}/post`);
    }
}

function submitSp() {
    spForm.post('/purchasing/payments', {
        onSuccess: () => {
            showSpModal.value = false;
            spForm.reset();
        }
    });
}
function postSp(id) {
    if (confirm('Posting Supplier Payment ini? Ini akan memotong utang usaha pemasok.')) {
        router.post(`/purchasing/payments/${id}/post`);
    }
}

function submitRet() {
    retForm.post('/purchasing/returns', {
        onSuccess: () => {
            showRetModal.value = false;
            retForm.reset();
        }
    });
}
function postRet(id) {
    if (confirm('Posting Purchase Return ini? Ini akan mengurangi stok inventaris Anda.')) {
        router.post(`/purchasing/returns/${id}/post`);
    }
}

function submitLc() {
    lcForm.post('/purchasing/landed-costs', {
        onSuccess: () => {
            showLcModal.value = false;
            lcForm.reset();
        }
    });
}

function openPerfModal(supplierId) {
    selectedSupplierIdForPerf.value = supplierId;
    perfForm.reset();
    showPerfModal.value = true;
}
function submitPerf() {
    perfForm.post(`/purchasing/suppliers/${selectedSupplierIdForPerf.value}/performance`, {
        onSuccess: () => {
            showPerfModal.value = false;
            selectedSupplierIdForPerf.value = null;
        }
    });
}

// Columns Definitions
const prColumns = [
    { key: 'no', label: 'No' },
    { key: 'pr_number', label: 'PR No' },
    { key: 'request_date', label: 'Request Date' },
    { key: 'requested_by', label: 'Requested By' },
    { key: 'status', label: 'Status', align: 'center' },
    { key: 'actions', label: 'Actions', align: 'center' },
];

const poColumns = [
    { key: 'no', label: 'No' },
    { key: 'po_number', label: 'PO No' },
    { key: 'supplier', label: 'Supplier' },
    { key: 'total_amount', label: 'Total', align: 'right' },
    { key: 'status', label: 'Status', align: 'center' },
    { key: 'order_date', label: 'Date' },
    { key: 'actions', label: 'Actions', align: 'center' },
];

const grColumns = [
    { key: 'no', label: 'No' },
    { key: 'gr_number', label: 'GR No' },
    { key: 'po_number', label: 'PO Ref' },
    { key: 'location', label: 'Location' },
    { key: 'receipt_date', label: 'Receipt Date' },
    { key: 'status', label: 'Status', align: 'center' },
    { key: 'actions', label: 'Actions', align: 'center' },
];

const siColumns = [
    { key: 'no', label: 'No' },
    { key: 'invoice_number', label: 'SI No' },
    { key: 'supplier', label: 'Supplier' },
    { key: 'supplier_invoice_no', label: 'Ref Inv No' },
    { key: 'total_amount', label: 'Total Amount', align: 'right' },
    { key: 'status', label: 'Status', align: 'center' },
    { key: 'actions', label: 'Actions', align: 'center' },
];

const spColumns = [
    { key: 'no', label: 'No' },
    { key: 'payment_number', label: 'Payment No' },
    { key: 'supplier', label: 'Supplier' },
    { key: 'payment_method', label: 'Method' },
    { key: 'total_amount', label: 'Total Paid', align: 'right' },
    { key: 'status', label: 'Status', align: 'center' },
    { key: 'actions', label: 'Actions', align: 'center' },
];

const apColumns = [
    { key: 'no', label: 'No' },
    { key: 'payable_number', label: 'AP No' },
    { key: 'supplier', label: 'Supplier' },
    { key: 'due_date', label: 'Due Date' },
    { key: 'total_amount', label: 'Total Amount', align: 'right' },
    { key: 'remaining_amount', label: 'Remaining', align: 'right' },
    { key: 'status', label: 'Status', align: 'center' },
];

const retColumns = [
    { key: 'no', label: 'No' },
    { key: 'return_number', label: 'Return No' },
    { key: 'supplier', label: 'Supplier' },
    { key: 'total_amount', label: 'Total Value', align: 'right' },
    { key: 'status', label: 'Status', align: 'center' },
    { key: 'actions', label: 'Actions', align: 'center' },
];

const lcColumns = [
    { key: 'no', label: 'No' },
    { key: 'goods_receipt', label: 'GR Number' },
    { key: 'cost_type', label: 'Cost Type' },
    { key: 'amount', label: 'Amount', align: 'right' },
    { key: 'allocation_method', label: 'Method' },
];

const perfColumns = [
    { key: 'no', label: 'No' },
    { key: 'supplier', label: 'Supplier' },
    { key: 'evaluation_period', label: 'Period' },
    { key: 'overall_score', label: 'Overall Score', align: 'right' },
    { key: 'notes', label: 'Evaluation' },
];
</script>

<template>
    <Head title="Purchasing & Supplier Payments Management" />

    <DashboardLayout>
        <div class="mb-6 flex justify-between items-center flex-wrap gap-md">
            <div>
                <h1 class="text-2xl font-bold text-ink-primary">Purchasing & AP 📦</h1>
                <p class="text-ink-secondary text-sm">
                    Kelola alur PR, PO, Goods Receipt, Tagihan Supplier Invoice, Pembayaran Utang (AP), Retur, dan Landed Cost.
                </p>
            </div>

            <div class="flex gap-md">
                <BaseButton v-if="activeTab === 'requests'" @click="showPrModal = true">
                    ➕ New Request
                </BaseButton>
                <Link v-if="activeTab === 'orders'" href="/purchasing/create">
                    <BaseButton>➕ New Order</BaseButton>
                </Link>
                <BaseButton v-if="activeTab === 'receipts'" @click="showGrModal = true">
                    🚚 New Receipt
                </BaseButton>
                <BaseButton v-if="activeTab === 'invoices'" @click="showSiModal = true">
                    🧾 New Invoice
                </BaseButton>
                <BaseButton v-if="activeTab === 'payments'" @click="showSpModal = true">
                    💸 New Payment
                </BaseButton>
                <BaseButton v-if="activeTab === 'returns'" @click="showRetModal = true">
                    ↩️ New Return
                </BaseButton>
                <BaseButton v-if="activeTab === 'landed'" @click="showLcModal = true">
                    ⚓ New Landed Cost
                </BaseButton>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex space-x-1 mb-6 border-b border-border-soft overflow-x-auto">
            <button v-for="tab in [
                { key: 'requests', label: 'Requests 📝' },
                { key: 'orders', label: 'Orders 📦' },
                { key: 'receipts', label: 'Receipts 🚚' },
                { key: 'invoices', label: 'Invoices 🧾' },
                { key: 'payments', label: 'Payments 💸' },
                { key: 'payables', label: 'Payables (AP) ⚖️' },
                { key: 'returns', label: 'Returns ↩️' },
                { key: 'landed', label: 'Landed Costs ⚓' },
                { key: 'performance', label: 'Performance 📈' }
            ]" :key="tab.key" @click="activeTab = tab.key"
                :class="activeTab === tab.key ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-3 px-6 font-medium transition-colors duration-200 cursor-pointer text-sm whitespace-nowrap"
            >
                {{ tab.label }}
            </button>
        </div>

        <!-- Tabs Content -->
        <!-- Tab 1: Requests -->
        <div v-if="activeTab === 'requests'">
            <DataTable :columns="prColumns" :rows="purchaseRequests">
                <template #cell-pr_number="{ row }">
                    <button @click="selectedPr = row" class="font-mono text-brand hover:underline font-bold text-left">{{ row.pr_number }}</button>
                </template>
                <template #cell-request_date="{ value }">
                    <span>{{ formatDate(value) }}</span>
                </template>
                <template #cell-requested_by="{ row }">
                    <span>{{ row.requester?.name || '-' }}</span>
                </template>
                <template #cell-status="{ value }">
                    <StatusBadge :status="value" size="sm" variant="soft" />
                </template>
                <template #cell-actions="{ row }">
                    <div class="flex gap-md justify-center" v-if="row.status === 'DRAFT'">
                        <button @click="approvePr(row.id)" class="text-brand hover:underline text-sm font-semibold">Approve</button>
                        <button @click="openRejectPr(row.id)" class="text-semantic-danger hover:underline text-sm font-semibold">Reject</button>
                    </div>
                    <span v-else class="text-ink-muted text-xs">
                        {{ row.status === 'REJECTED' ? `Rejected: ${row.rejection_notes || '-'}` : 'Processed' }}
                    </span>
                </template>
            </DataTable>
        </div>

        <!-- Tab 2: Orders -->
        <div v-if="activeTab === 'orders'">
            <DataTable :columns="poColumns" :rows="purchaseOrders">
                <template #cell-po_number="{ row }">
                    <Link :href="`/purchasing/${row.id}`" class="font-mono text-brand hover:underline font-bold">{{ row.po_number }}</Link>
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
                    <Link :href="`/purchasing/${row.id}`" class="text-brand hover:underline font-medium">View</Link>
                </template>
            </DataTable>
        </div>

        <!-- Tab 3: Receipts -->
        <div v-if="activeTab === 'receipts'">
            <DataTable :columns="grColumns" :rows="goodsReceipts">
                <template #cell-gr_number="{ row }">
                    <button @click="selectedGr = row" class="font-mono text-brand hover:underline font-bold text-left">{{ row.gr_number }}</button>
                </template>
                <template #cell-po_number="{ row }">
                    <span class="font-mono text-ink-secondary">{{ row.purchase_order?.po_number }}</span>
                </template>
                <template #cell-location="{ row }">
                    <span>{{ row.location?.name || '-' }}</span>
                </template>
                <template #cell-receipt_date="{ value }">
                    <span>{{ formatDate(value) }}</span>
                </template>
                <template #cell-status="{ value }">
                    <StatusBadge :status="value" variant="soft" size="sm" />
                </template>
                <template #cell-actions="{ row }">
                    <div class="flex gap-md justify-center">
                        <button v-if="row.status === 'DRAFT'" @click="postGr(row.id)" class="text-brand hover:underline text-sm font-semibold">Post Receipt</button>
                        <button @click="selectedGr = row" class="text-ink-secondary hover:underline text-sm font-semibold">Detail</button>
                    </div>
                </template>
            </DataTable>
        </div>

        <!-- Tab 4: Invoices -->
        <div v-if="activeTab === 'invoices'">
            <DataTable :columns="siColumns" :rows="supplierInvoices">
                <template #cell-invoice_number="{ row }">
                    <button @click="selectedSi = row" class="font-mono text-brand hover:underline font-bold text-left">{{ row.invoice_number }}</button>
                </template>
                <template #cell-supplier="{ row }">
                    <span>{{ row.supplier?.supplier_name }}</span>
                </template>
                <template #cell-total_amount="{ value }">
                    <span class="font-mono text-ink-primary font-medium">{{ formatCurrency(value) }}</span>
                </template>
                <template #cell-status="{ value }">
                    <StatusBadge :status="value" variant="soft" size="sm" />
                </template>
                <template #cell-actions="{ row }">
                    <div class="flex gap-md justify-center">
                        <button v-if="row.status === 'DRAFT'" @click="postSi(row.id)" class="text-brand hover:underline text-sm font-semibold">Post Invoice</button>
                        <button @click="selectedSi = row" class="text-ink-secondary hover:underline text-sm font-semibold">Detail</button>
                    </div>
                </template>
            </DataTable>
        </div>

        <!-- Tab 5: Payments -->
        <div v-if="activeTab === 'payments'">
            <DataTable :columns="spColumns" :rows="supplierPayments">
                <template #cell-payment_number="{ row }">
                    <button @click="selectedSp = row" class="font-mono text-brand hover:underline font-bold text-left">{{ row.payment_number }}</button>
                </template>
                <template #cell-supplier="{ row }">
                    <span>{{ row.supplier?.supplier_name }}</span>
                </template>
                <template #cell-total_amount="{ value }">
                    <span class="font-mono text-ink-primary font-medium">{{ formatCurrency(value) }}</span>
                </template>
                <template #cell-status="{ value }">
                    <StatusBadge :status="value" variant="soft" size="sm" />
                </template>
                <template #cell-actions="{ row }">
                    <div class="flex gap-md justify-center">
                        <button v-if="row.status === 'DRAFT'" @click="postSp(row.id)" class="text-brand hover:underline text-sm font-semibold">Post Payment</button>
                        <button @click="selectedSp = row" class="text-ink-secondary hover:underline text-sm font-semibold">Detail</button>
                    </div>
                </template>
            </DataTable>
        </div>

        <!-- Tab 6: Payables -->
        <div v-if="activeTab === 'payables'">
            <DataTable :columns="apColumns" :rows="accountsPayables">
                <template #cell-payable_number="{ value }">
                    <span class="font-mono text-ink-primary font-semibold">{{ value }}</span>
                </template>
                <template #cell-supplier="{ row }">
                    <span>{{ row.supplier?.supplier_name }}</span>
                </template>
                <template #cell-due_date="{ value }">
                    <span>{{ formatDate(value) }}</span>
                </template>
                <template #cell-total_amount="{ value }">
                    <span class="font-mono text-ink-primary font-medium">{{ formatCurrency(value) }}</span>
                </template>
                <template #cell-remaining_amount="{ value }">
                    <span class="font-mono font-bold" :class="value > 0 ? 'text-semantic-danger' : 'text-semantic-success'">{{ formatCurrency(value) }}</span>
                </template>
                <template #cell-status="{ value }">
                    <StatusBadge :status="value" variant="soft" size="sm" />
                </template>
            </DataTable>
        </div>

        <!-- Tab 7: Returns -->
        <div v-if="activeTab === 'returns'">
            <DataTable :columns="retColumns" :rows="purchaseReturns">
                <template #cell-return_number="{ row }">
                    <button @click="selectedRet = row" class="font-mono text-brand hover:underline font-bold text-left">{{ row.return_number }}</button>
                </template>
                <template #cell-supplier="{ row }">
                    <span>{{ row.supplier?.supplier_name }}</span>
                </template>
                <template #cell-total_amount="{ value }">
                    <span class="font-mono text-ink-primary font-medium">{{ formatCurrency(value) }}</span>
                </template>
                <template #cell-status="{ value }">
                    <StatusBadge :status="value" variant="soft" size="sm" />
                </template>
                <template #cell-actions="{ row }">
                    <div class="flex gap-md justify-center">
                        <button v-if="row.status === 'DRAFT'" @click="postRet(row.id)" class="text-brand hover:underline text-sm font-semibold">Post Return</button>
                        <button @click="selectedRet = row" class="text-ink-secondary hover:underline text-sm font-semibold">Detail</button>
                    </div>
                </template>
            </DataTable>
        </div>

        <!-- Tab 8: Landed Costs -->
        <div v-if="activeTab === 'landed'">
            <DataTable :columns="lcColumns" :rows="landedCosts">
                <template #cell-goods_receipt="{ row }">
                    <span class="font-mono font-semibold">{{ row.goods_receipt?.gr_number }}</span>
                </template>
                <template #cell-amount="{ value }">
                    <span class="font-mono text-ink-primary font-medium">{{ formatCurrency(value) }}</span>
                </template>
            </DataTable>
        </div>

        <!-- Tab 9: Performance -->
        <div v-if="activeTab === 'performance'">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-md mb-6">
                <div v-for="supplier in suppliers" :key="supplier.id" class="bg-surface-card border border-border-soft p-xl rounded-2xl shadow-soft flex flex-col justify-between">
                    <div>
                        <h4 class="font-bold text-lg text-ink-primary mb-xs">{{ supplier.supplier_name }}</h4>
                        <span class="text-xs text-ink-muted block mb-xl">{{ supplier.phone || 'No phone' }}</span>
                        
                        <div class="bg-surface-subtle p-md rounded-xl space-y-sm text-sm border border-border-soft">
                            <div class="flex justify-between">
                                <span class="text-ink-secondary">Overall Score</span>
                                <span class="font-bold text-brand">
                                    {{ supplierPerformances.find(p => p.supplier_id === supplier.id)?.overall_score || 'Not Evaluated' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-xl pt-md border-t border-border-soft flex justify-end">
                        <BaseButton size="sm" @click="openPerfModal(supplier.id)">Evaluate</BaseButton>
                    </div>
                </div>
            </div>
            
            <h3 class="text-lg font-bold text-ink-primary mb-xl">Evaluation History</h3>
            <DataTable :columns="perfColumns" :rows="supplierPerformances">
                <template #cell-supplier="{ row }">
                    <span>{{ row.supplier?.supplier_name }}</span>
                </template>
                <template #cell-evaluation_period="{ value }">
                    <span>{{ formatDate(value) }}</span>
                </template>
            </DataTable>
        </div>

        <!-- Modals Section -->
        <!-- New PR Modal -->
        <BaseModal :show="showPrModal" @close="showPrModal = false" title="Create Purchase Request">
            <form @submit.prevent="submitPr" class="space-y-md">
                <FormInput type="date" label="Request Date" v-model="prForm.request_date" required />
                <FormInput label="Remarks" v-model="prForm.remarks" placeholder="Permintaan restok bulanan..." />
                
                <h4 class="font-bold text-sm text-ink-primary pt-md border-t border-border-soft">Items</h4>
                <div v-for="(item, idx) in prForm.items" :key="idx" class="grid grid-cols-12 gap-md items-end">
                    <div class="col-span-6">
                        <FormSelect label="Product Variant" v-model="item.product_variant_id" required>
                            <option value="">-- Pilih Produk --</option>
                            <option v-for="v in variants" :key="v.id" :value="v.id">{{ v.product?.product_name }} ({{ v.product_code }})</option>
                        </FormSelect>
                    </div>
                    <div class="col-span-3">
                        <FormInput type="number" label="Qty" v-model="item.requested_qty" required />
                    </div>
                    <div class="col-span-3 pb-md">
                        <button type="button" @click="removePrItem(idx)" class="text-semantic-danger hover:underline text-xs font-semibold">Delete</button>
                    </div>
                </div>
                <button type="button" @click="addPrItem" class="text-brand hover:underline text-sm font-semibold">+ Add Product</button>

                <div class="flex justify-end gap-md pt-md border-t border-border-soft">
                    <BaseButton type="button" variant="secondary" @click="showPrModal = false">Cancel</BaseButton>
                    <BaseButton type="submit" :loading="prForm.processing">Create Request</BaseButton>
                </div>
            </form>
        </BaseModal>

        <!-- New Goods Receipt Modal -->
        <BaseModal :show="showGrModal" @close="showGrModal = false" title="Create Goods Receipt">
            <form @submit.prevent="submitGr" class="space-y-md">
                <div class="grid grid-cols-2 gap-md">
                    <FormSelect label="PO Reference" v-model="grForm.purchase_order_id" required>
                        <option value="">-- Select PO --</option>
                        <option v-for="po in purchaseOrders.filter(o => o.status === 'APPROVED' || o.status === 'PARTIAL')" :key="po.id" :value="po.id">
                            {{ po.po_number }} ({{ po.supplier?.supplier_name }})
                        </option>
                    </FormSelect>
                    <FormSelect label="Location" v-model="grForm.location_id" required>
                        <option value="">-- Select Location --</option>
                        <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name }}</option>
                    </FormSelect>
                </div>
                <div class="grid grid-cols-2 gap-md">
                    <FormInput type="date" label="Receipt Date" v-model="grForm.receipt_date" required />
                    <FormInput label="Remarks" v-model="grForm.remarks" />
                </div>
                
                <h4 class="font-bold text-sm text-ink-primary pt-md border-t border-border-soft" v-if="grForm.items.length > 0">Receipt Items</h4>
                <div v-for="(item, idx) in grForm.items" :key="idx" class="p-md bg-surface-subtle border border-border-soft rounded-lg space-y-sm">
                    <div class="flex justify-between text-sm font-semibold">
                        <span>{{ item.product_name }}</span>
                        <span>Pending Qty: {{ item.received_qty }}</span>
                    </div>
                    <div class="grid grid-cols-3 gap-md">
                        <FormInput type="number" label="Qty Received" v-model="item.received_qty" required />
                        <FormInput label="Batch Number (Optional)" v-model="item.batch_no" />
                        <FormInput type="date" label="Expiry Date (Optional)" v-model="item.expiry_date" />
                    </div>
                </div>

                <div class="flex justify-end gap-md pt-md border-t border-border-soft">
                    <BaseButton type="button" variant="secondary" @click="showGrModal = false">Cancel</BaseButton>
                    <BaseButton type="submit" :loading="grForm.processing">Create Receipt</BaseButton>
                </div>
            </form>
        </BaseModal>

        <!-- New Supplier Invoice Modal -->
        <BaseModal :show="showSiModal" @close="showSiModal = false" title="Create Supplier Invoice">
            <form @submit.prevent="submitSi" class="space-y-md">
                <div class="grid grid-cols-2 gap-md">
                    <FormSelect label="Supplier" v-model="siForm.supplier_id" required>
                        <option value="">-- Select Supplier --</option>
                        <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.supplier_name }}</option>
                    </FormSelect>
                    <FormSelect label="GR Reference (Optional)" v-model="siForm.goods_receipt_id">
                        <option value="">-- None / Manual Invoice --</option>
                        <option v-for="g in goodsReceipts.filter(r => r.status === 'POSTED')" :key="g.id" :value="g.id">
                            {{ g.gr_number }} (PO: {{ g.purchase_order?.po_number }})
                        </option>
                    </FormSelect>
                </div>
                <div class="grid grid-cols-3 gap-md">
                    <FormInput label="Supplier Invoice No" v-model="siForm.supplier_invoice_no" required />
                    <FormInput type="date" label="Invoice Date" v-model="siForm.invoice_date" required />
                    <FormInput type="date" label="Due Date" v-model="siForm.due_date" required />
                </div>
                <FormInput label="Remarks" v-model="siForm.remarks" />

                <h4 class="font-bold text-sm text-ink-primary pt-md border-t border-border-soft" v-if="siForm.items.length > 0">Invoice Lines</h4>
                <div v-for="(item, idx) in siForm.items" :key="idx" class="p-md bg-surface-subtle border border-border-soft rounded-lg space-y-sm">
                    <div class="flex justify-between text-sm font-semibold">
                        <span>{{ item.product_name }}</span>
                    </div>
                    <div class="grid grid-cols-3 gap-md">
                        <FormInput type="number" label="Qty" v-model="item.qty" required />
                        <FormInput type="number" label="Unit Cost (Rp)" v-model="item.unit_cost" required />
                        <FormInput type="number" label="Tax Amount (Rp)" v-model="item.tax_amount" />
                    </div>
                </div>

                <div class="flex justify-end gap-md pt-md border-t border-border-soft">
                    <BaseButton type="button" variant="secondary" @click="showSiModal = false">Cancel</BaseButton>
                    <BaseButton type="submit" :loading="siForm.processing">Create Invoice</BaseButton>
                </div>
            </form>
        </BaseModal>

        <!-- New Supplier Payment Modal -->
        <BaseModal :show="showSpModal" @close="showSpModal = false" title="Create Supplier Payment">
            <form @submit.prevent="submitSp" class="space-y-md">
                <div class="grid grid-cols-2 gap-md">
                    <FormSelect label="Supplier" v-model="spForm.supplier_id" required>
                        <option value="">-- Select Supplier --</option>
                        <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.supplier_name }}</option>
                    </FormSelect>
                    <FormSelect label="Payment Method Account" v-model="spForm.payment_method_account_id" required>
                        <option value="">-- Select Account --</option>
                        <option v-for="m in paymentMethods" :key="m.id" :value="m.id">{{ m.name }}</option>
                    </FormSelect>
                </div>
                <div class="grid grid-cols-3 gap-md">
                    <FormSelect label="Payment Method" v-model="spForm.payment_method" required>
                        <option value="CASH">Cash</option>
                        <option value="TRANSFER">Transfer</option>
                        <option value="GIRO">Giro</option>
                        <option value="CHEQUE">Cheque</option>
                    </FormSelect>
                    <FormInput label="Ref No (Giro/Cheque)" v-model="spForm.reference_no" />
                    <FormInput type="date" label="Payment Date" v-model="spForm.payment_date" required />
                </div>
                <FormInput label="Remarks" v-model="spForm.remarks" />

                <h4 class="font-bold text-sm text-ink-primary pt-md border-t border-border-soft" v-if="spForm.allocations.length > 0">Allocations to AP</h4>
                <div v-for="(alloc, idx) in spForm.allocations" :key="idx" class="flex justify-between items-center bg-surface-subtle p-md border border-border-soft rounded-lg">
                    <div class="text-xs">
                        <span class="font-bold text-ink-primary block">{{ alloc.payable_number }}</span>
                        <span class="text-ink-muted">Due Date: {{ formatDate(alloc.due_date) }}</span>
                    </div>
                    <div class="w-1/3">
                        <FormInput type="number" label="Allocated Amount" v-model="alloc.allocated_amount" required />
                    </div>
                </div>

                <div class="flex justify-end gap-md pt-md border-t border-border-soft">
                    <BaseButton type="button" variant="secondary" @click="showSpModal = false">Cancel</BaseButton>
                    <BaseButton type="submit" :loading="spForm.processing">Create Payment</BaseButton>
                </div>
            </form>
        </BaseModal>

        <!-- New Return Modal -->
        <BaseModal :show="showRetModal" @close="showRetModal = false" title="Create Purchase Return">
            <form @submit.prevent="submitRet" class="space-y-md">
                <FormSelect label="Goods Receipt Reference" v-model="retForm.goods_receipt_id" required>
                    <option value="">-- Select GR --</option>
                    <option v-for="g in goodsReceipts.filter(r => r.status === 'POSTED')" :key="g.id" :value="g.id">
                        {{ g.gr_number }} (PO: {{ g.purchase_order?.po_number }})
                    </option>
                </FormSelect>
                <div class="grid grid-cols-2 gap-md">
                    <FormInput type="date" label="Return Date" v-model="retForm.return_date" required />
                    <FormInput label="Reason for Return" v-model="retForm.reason" required />
                </div>

                <h4 class="font-bold text-sm text-ink-primary pt-md border-t border-border-soft" v-if="retForm.items.length > 0">Return Items</h4>
                <div v-for="(item, idx) in retForm.items" :key="idx" class="p-md bg-surface-subtle border border-border-soft rounded-lg space-y-sm">
                    <div class="flex justify-between text-sm font-semibold">
                        <span>{{ item.product_name }}</span>
                        <span>Max Qty: {{ item.received_qty }}</span>
                    </div>
                    <div class="grid grid-cols-2 gap-md">
                        <FormInput type="number" label="Return Qty" v-model="item.return_qty" required />
                        <FormInput type="number" label="Unit Cost" v-model="item.unit_cost" disabled />
                    </div>
                </div>

                <div class="flex justify-end gap-md pt-md border-t border-border-soft">
                    <BaseButton type="button" variant="secondary" @click="showRetModal = false">Cancel</BaseButton>
                    <BaseButton type="submit" :loading="retForm.processing">Create Return</BaseButton>
                </div>
            </form>
        </BaseModal>

        <!-- New Landed Cost Modal -->
        <BaseModal :show="showLcModal" @close="showLcModal = false" title="Create Landed Cost">
            <form @submit.prevent="submitLc" class="space-y-md">
                <FormSelect label="Goods Receipt" v-model="lcForm.goods_receipt_id" required>
                    <option value="">-- Select GR --</option>
                    <option v-for="g in goodsReceipts.filter(r => r.status === 'POSTED')" :key="g.id" :value="g.id">
                        {{ g.gr_number }} (PO: {{ g.purchase_order?.po_number }})
                    </option>
                </FormSelect>
                <div class="grid grid-cols-3 gap-md">
                    <FormSelect label="Cost Type" v-model="lcForm.cost_type" required>
                        <option value="FREIGHT">Freight</option>
                        <option value="INSURANCE">Insurance</option>
                        <option value="CUSTOMS">Customs</option>
                        <option value="OTHER">Other</option>
                    </FormSelect>
                    <FormInput type="number" label="Amount" v-model="lcForm.amount" required />
                    <FormSelect label="Allocation Method" v-model="lcForm.allocation_method" required>
                        <option value="BY_QTY">By Qty</option>
                        <option value="BY_VALUE">By Value</option>
                        <option value="EVEN">Even</option>
                    </FormSelect>
                </div>
                <FormInput label="Notes" v-model="lcForm.notes" />

                <div class="flex justify-end gap-md pt-md border-t border-border-soft">
                    <BaseButton type="button" variant="secondary" @click="showLcModal = false">Cancel</BaseButton>
                    <BaseButton type="submit" :loading="lcForm.processing">Save Landed Cost</BaseButton>
                </div>
            </form>
        </BaseModal>

        <!-- Supplier Performance Modal -->
        <BaseModal :show="showPerfModal" @close="showPerfModal = false" title="Supplier Performance Evaluation">
            <form @submit.prevent="submitPerf" class="space-y-md">
                <FormInput type="date" label="Evaluation Period" v-model="perfForm.evaluation_period" required />
                <div class="grid grid-cols-2 gap-md">
                    <FormInput type="number" label="On Time Delivery Score" v-model="perfForm.on_time_delivery_score" required />
                    <FormInput type="number" label="Price Score" v-model="perfForm.price_score" required />
                </div>
                <div class="grid grid-cols-2 gap-md">
                    <FormInput type="number" label="Quality Score" v-model="perfForm.quality_score" required />
                    <FormInput type="number" label="Service Score" v-model="perfForm.service_score" required />
                </div>
                <FormInput label="Evaluation Notes" v-model="perfForm.notes" />

                <div class="flex justify-end gap-md pt-md border-t border-border-soft">
                    <BaseButton type="button" variant="secondary" @click="showPerfModal = false">Cancel</BaseButton>
                    <BaseButton type="submit" :loading="perfForm.processing">Save Evaluation</BaseButton>
                </div>
            </form>
        </BaseModal>

        <!-- Reject PR Modal -->
        <BaseModal :show="showRejectPrModal" @close="showRejectPrModal = false" title="Reject Purchase Request">
            <form @submit.prevent="submitRejectPr" class="space-y-md">
                <FormInput label="Reason for Rejection" v-model="rejectPrForm.rejection_notes" required />
                <div class="flex justify-end gap-md pt-md border-t border-border-soft">
                    <BaseButton type="button" variant="secondary" @click="showRejectPrModal = false">Cancel</BaseButton>
                    <BaseButton type="submit" variant="danger" :loading="rejectPrForm.processing">Reject Request</BaseButton>
                </div>
            </form>
        </BaseModal>

        <!-- Details Modals -->
        <!-- PR Detail Modal -->
        <BaseModal :show="!!selectedPr" @close="selectedPr = null" title="Purchase Request Details">
            <div v-if="selectedPr" class="space-y-md">
                <div class="grid grid-cols-2 gap-md text-sm border-b border-border-soft pb-md">
                    <div>
                        <span class="text-ink-muted block">PR Number</span>
                        <span class="font-mono font-bold text-ink-primary">{{ selectedPr.pr_number }}</span>
                    </div>
                    <div>
                        <span class="text-ink-muted block">Date</span>
                        <span class="font-medium text-ink-primary">{{ formatDate(selectedPr.request_date) }}</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-md text-sm border-b border-border-soft pb-md">
                    <div>
                        <span class="text-ink-muted block">Status</span>
                        <StatusBadge :status="selectedPr.status" size="sm" variant="soft" />
                    </div>
                    <div>
                        <span class="text-ink-muted block">Requested By</span>
                        <span class="font-medium text-ink-primary">{{ selectedPr.requester?.name || '-' }}</span>
                    </div>
                </div>
                <div class="text-sm border-b border-border-soft pb-md" v-if="selectedPr.remarks">
                    <span class="text-ink-muted block">Remarks</span>
                    <span class="font-medium text-ink-primary">{{ selectedPr.remarks }}</span>
                </div>
                <div class="text-sm border-b border-border-soft pb-md" v-if="selectedPr.status === 'REJECTED'">
                    <span class="text-semantic-danger font-semibold block">Rejection Notes</span>
                    <span class="text-semantic-danger font-medium">{{ selectedPr.rejection_notes || '-' }}</span>
                </div>
                
                <h4 class="font-bold text-sm text-ink-primary">Request Items</h4>
                <div class="space-y-xs">
                    <div v-for="item in selectedPr.items" :key="item.id" class="flex justify-between items-center bg-surface-subtle p-md rounded-xl text-sm border border-border-soft">
                        <span class="font-semibold">{{ item.product_variant?.product?.product_name }} ({{ item.product_variant?.product_code }})</span>
                        <span class="font-bold text-brand">{{ item.requested_qty }} unit</span>
                    </div>
                </div>
            </div>
        </BaseModal>

        <!-- GR Detail Modal -->
        <BaseModal :show="!!selectedGr" @close="selectedGr = null" title="Goods Receipt Details">
            <div v-if="selectedGr" class="space-y-md">
                <div class="grid grid-cols-2 gap-md text-sm border-b border-border-soft pb-md">
                    <div>
                        <span class="text-ink-muted block">GR Number</span>
                        <span class="font-mono font-bold text-ink-primary">{{ selectedGr.gr_number }}</span>
                    </div>
                    <div>
                        <span class="text-ink-muted block">Date Received</span>
                        <span class="font-medium text-ink-primary">{{ formatDate(selectedGr.receipt_date) }}</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-md text-sm border-b border-border-soft pb-md">
                    <div>
                        <span class="text-ink-muted block">PO Reference</span>
                        <span class="font-mono font-bold text-ink-primary">{{ selectedGr.purchase_order?.po_number }}</span>
                    </div>
                    <div>
                        <span class="text-ink-muted block">Location</span>
                        <span class="font-medium text-ink-primary">{{ selectedGr.location?.name }}</span>
                    </div>
                </div>
                <div class="text-sm border-b border-border-soft pb-md" v-if="selectedGr.remarks">
                    <span class="text-ink-muted block">Remarks</span>
                    <span class="font-medium text-ink-primary">{{ selectedGr.remarks }}</span>
                </div>
                
                <h4 class="font-bold text-sm text-ink-primary">Receipt Items</h4>
                <div class="space-y-xs">
                    <div v-for="item in selectedGr.items" :key="item.id" class="p-md bg-surface-subtle rounded-xl text-sm border border-border-soft">
                        <div class="flex justify-between font-semibold">
                            <span>{{ item.product_variant?.product?.product_name }}</span>
                            <span class="text-brand font-bold">{{ item.received_qty }} units</span>
                        </div>
                        <div class="grid grid-cols-2 gap-md text-xs text-ink-secondary mt-sm">
                            <span>Batch: {{ item.batch_no || '-' }}</span>
                            <span>Expiry: {{ item.expiry_date ? formatDate(item.expiry_date) : '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </BaseModal>

        <!-- Supplier Invoice Detail Modal -->
        <BaseModal :show="!!selectedSi" @close="selectedSi = null" title="Supplier Invoice Details">
            <div v-if="selectedSi" class="space-y-md">
                <div class="grid grid-cols-2 gap-md text-sm border-b border-border-soft pb-md">
                    <div>
                        <span class="text-ink-muted block">Invoice Number</span>
                        <span class="font-mono font-bold text-ink-primary">{{ selectedSi.invoice_number }}</span>
                    </div>
                    <div>
                        <span class="text-ink-muted block">Supplier Invoice No</span>
                        <span class="font-mono font-bold text-ink-primary">{{ selectedSi.supplier_invoice_no }}</span>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-md text-sm border-b border-border-soft pb-md">
                    <div>
                        <span class="text-ink-muted block">Invoice Date</span>
                        <span>{{ formatDate(selectedSi.invoice_date) }}</span>
                    </div>
                    <div>
                        <span class="text-ink-muted block">Due Date</span>
                        <span>{{ formatDate(selectedSi.due_date) }}</span>
                    </div>
                    <div>
                        <span class="text-ink-muted block">Status</span>
                        <StatusBadge :status="selectedSi.status" size="sm" variant="soft" />
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-md text-sm border-b border-border-soft pb-md">
                    <div>
                        <span class="text-ink-muted block">Subtotal</span>
                        <span class="font-mono">{{ formatCurrency(selectedSi.subtotal) }}</span>
                    </div>
                    <div>
                        <span class="text-ink-muted block">Tax Amount</span>
                        <span class="font-mono">{{ formatCurrency(selectedSi.tax_amount) }}</span>
                    </div>
                    <div>
                        <span class="text-ink-muted block">Total Amount</span>
                        <span class="font-mono font-bold text-brand">{{ formatCurrency(selectedSi.total_amount) }}</span>
                    </div>
                </div>

                <h4 class="font-bold text-sm text-ink-primary">Invoice lines</h4>
                <div class="space-y-xs">
                    <div v-for="item in selectedSi.items" :key="item.id" class="flex justify-between items-center bg-surface-subtle p-md rounded-xl text-sm border border-border-soft">
                        <span>{{ item.product_variant?.product?.product_name }}</span>
                        <span class="font-mono font-bold">{{ item.qty }} x {{ formatCurrency(item.unit_cost) }}</span>
                    </div>
                </div>
            </div>
        </BaseModal>

        <!-- Supplier Payment Detail Modal -->
        <BaseModal :show="!!selectedSp" @close="selectedSp = null" title="Supplier Payment Details">
            <div v-if="selectedSp" class="space-y-md">
                <div class="grid grid-cols-2 gap-md text-sm border-b border-border-soft pb-md">
                    <div>
                        <span class="text-ink-muted block">Payment Number</span>
                        <span class="font-mono font-bold text-ink-primary">{{ selectedSp.payment_number }}</span>
                    </div>
                    <div>
                        <span class="text-ink-muted block">Supplier</span>
                        <span class="font-bold text-ink-primary">{{ selectedSp.supplier?.supplier_name }}</span>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-md text-sm border-b border-border-soft pb-md">
                    <div>
                        <span class="text-ink-muted block">Payment Date</span>
                        <span>{{ formatDate(selectedSp.payment_date) }}</span>
                    </div>
                    <div>
                        <span class="text-ink-muted block">Payment Method</span>
                        <span>{{ selectedSp.payment_method }}</span>
                    </div>
                    <div>
                        <span class="text-ink-muted block">Total Paid</span>
                        <span class="font-mono font-bold text-brand">{{ formatCurrency(selectedSp.total_amount) }}</span>
                    </div>
                </div>

                <h4 class="font-bold text-sm text-ink-primary">Allocations to AP</h4>
                <div class="space-y-xs">
                    <div v-for="alloc in selectedSp.allocations" :key="alloc.id" class="flex justify-between items-center bg-surface-subtle p-md rounded-xl text-sm border border-border-soft">
                        <span class="font-mono">{{ alloc.account_payable?.payable_number }}</span>
                        <span class="font-mono font-bold text-brand">{{ formatCurrency(alloc.allocated_amount) }}</span>
                    </div>
                </div>
            </div>
        </BaseModal>

        <!-- Return Detail Modal -->
        <BaseModal :show="!!selectedRet" @close="selectedRet = null" title="Purchase Return Details">
            <div v-if="selectedRet" class="space-y-md">
                <div class="grid grid-cols-2 gap-md text-sm border-b border-border-soft pb-md">
                    <div>
                        <span class="text-ink-muted block">Return Number</span>
                        <span class="font-mono font-bold text-ink-primary">{{ selectedRet.return_number }}</span>
                    </div>
                    <div>
                        <span class="text-ink-muted block">Supplier</span>
                        <span class="font-bold text-ink-primary">{{ selectedRet.supplier?.supplier_name }}</span>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-md text-sm border-b border-border-soft pb-md">
                    <div>
                        <span class="text-ink-muted block">Return Date</span>
                        <span>{{ formatDate(selectedRet.return_date) }}</span>
                    </div>
                    <div>
                        <span class="text-ink-muted block">Total Value</span>
                        <span class="font-mono font-bold text-brand">{{ formatCurrency(selectedRet.total_amount) }}</span>
                    </div>
                    <div>
                        <span class="text-ink-muted block">Status</span>
                        <StatusBadge :status="selectedRet.status" size="sm" variant="soft" />
                    </div>
                </div>
                <div class="text-sm border-b border-border-soft pb-md" v-if="selectedRet.reason">
                    <span class="text-ink-muted block">Reason</span>
                    <span>{{ selectedRet.reason }}</span>
                </div>

                <h4 class="font-bold text-sm text-ink-primary">Returned Items</h4>
                <div class="space-y-xs">
                    <div v-for="item in selectedRet.items" :key="item.id" class="flex justify-between items-center bg-surface-subtle p-md rounded-xl text-sm border border-border-soft">
                        <span>{{ item.product_variant?.product?.product_name }}</span>
                        <span class="font-mono font-bold">{{ item.return_qty }} x {{ formatCurrency(item.unit_cost) }}</span>
                    </div>
                </div>
            </div>
        </BaseModal>
    </DashboardLayout>
</template>
