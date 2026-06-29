<script setup>
import { ref, computed } from 'vue';
import { useForm, Head, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import DataTable from '@/Components/Table/DataTable.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import BaseModal from '@/Components/Modal/BaseModal.vue';
import FormInput from '@/Components/Form/FormInput.vue';
import FormSelect from '@/Components/Form/FormSelect.vue';
import FormTextarea from '@/Components/Form/FormTextarea.vue';
import StatusBadge from '@/Components/DataDisplay/StatusBadge.vue';

const props = defineProps({
    chartOfAccounts: { type: Array, default: () => [] },
    journalEntries: { type: Array, default: () => [] },
    paymentMethods: { type: Array, default: () => [] },
    fiscalPeriods: { type: Array, default: () => [] },
    trialBalances: { type: Array, default: () => [] },
    ledgerLines: { type: Array, default: () => [] },
    journalTemplates: { type: Array, default: () => [] },
    accountingRules: { type: Array, default: () => [] },
    activeTab: { type: String, default: 'coa' },
});

const activeTab = ref(props.activeTab || 'coa');

// COA Modal state
const showCoaModal = ref(false);
const editingCoa = ref(null);
const coaForm = useForm({
    parent_id: '',
    account_code: '',
    account_name: '',
    account_type: 'ASSET',
    normal_balance: 'DEBIT',
    is_postable: true,
    is_active: true,
    description: '',
});

function openCreateCoa() {
    editingCoa.value = null;
    coaForm.reset();
    coaForm.clearErrors();
    showCoaModal.value = true;
}

function openEditCoa(row) {
    editingCoa.value = row;
    coaForm.parent_id = row.parent_id || '';
    coaForm.account_code = row.account_code;
    coaForm.account_name = row.account_name;
    coaForm.account_type = row.account_type;
    coaForm.normal_balance = row.normal_balance;
    coaForm.is_postable = !!row.is_postable;
    coaForm.is_active = !!row.is_active;
    coaForm.description = row.description || '';
    coaForm.clearErrors();
    showCoaModal.value = true;
}

function submitCoa() {
    if (editingCoa.value) {
        coaForm.put(`/accounting/coa/${editingCoa.value.id}`, {
            onSuccess: () => showCoaModal.value = false
        });
    } else {
        coaForm.post('/accounting/coa', {
            onSuccess: () => showCoaModal.value = false
        });
    }
}

function deleteCoa(row) {
    if (confirm(`Hapus akun "${row.account_name}"?`)) {
        router.delete(`/accounting/coa/${row.id}`);
    }
}

// Journal Entry Modal State
const showJournalModal = ref(false);
const journalForm = useForm({
    journal_date: new Date().toISOString().split('T')[0],
    description: '',
    lines: [
        { account_id: '', debit: 0, credit: 0, description: '' },
        { account_id: '', debit: 0, credit: 0, description: '' },
    ],
});

const journalTotals = computed(() => {
    let debits = 0;
    let credits = 0;
    journalForm.lines.forEach(l => {
        debits += parseFloat(l.debit) || 0;
        credits += parseFloat(l.credit) || 0;
    });
    return { debits, credits, isBalanced: Math.abs(debits - credits) < 0.01 };
});

function addJournalLine() {
    journalForm.lines.push({ account_id: '', debit: 0, credit: 0, description: '' });
}

function removeJournalLine(idx) {
    journalForm.lines.splice(idx, 1);
}

function openCreateJournal() {
    journalForm.reset();
    journalForm.clearErrors();
    showJournalModal.value = true;
}

function submitJournal() {
    if (!journalTotals.value.isBalanced) {
        alert("Total Debit dan Kredit harus balance/seimbang!");
        return;
    }
    journalForm.post('/accounting/journals', {
        onSuccess: () => showJournalModal.value = false
    });
}

// Payment Methods Modal State
const showMethodModal = ref(false);
const editingMethod = ref(null);
const methodForm = useForm({
    method_name: '',
    method_type: 'CASH',
    account_id: '',
    is_active: true,
    requires_reference: false,
});

function openCreateMethod() {
    editingMethod.value = null;
    methodForm.reset();
    methodForm.clearErrors();
    showMethodModal.value = true;
}

function openEditMethod(row) {
    editingMethod.value = row;
    methodForm.method_name = row.method_name;
    methodForm.method_type = row.method_type;
    methodForm.account_id = row.account_id;
    methodForm.is_active = !!row.is_active;
    methodForm.requires_reference = !!row.requires_reference;
    methodForm.clearErrors();
    showMethodModal.value = true;
}

function submitMethod() {
    if (editingMethod.value) {
        methodForm.put(`/accounting/payment-methods/${editingMethod.value.id}`, {
            onSuccess: () => showMethodModal.value = false
        });
    } else {
        methodForm.post('/accounting/payment-methods', {
            onSuccess: () => showMethodModal.value = false
        });
    }
}

function deleteMethod(row) {
    if (confirm(`Hapus metode pembayaran "${row.method_name}"?`)) {
        router.delete(`/accounting/payment-methods/${row.id}`);
    }
}

// Table configurations
const coaColumns = [
    { key: 'no', label: 'No' },
    { key: 'account_code', label: 'Account Code' },
    { key: 'account_name', label: 'Account Name' },
    { key: 'account_type', label: 'Type' },
    { key: 'balance', label: 'Balance', align: 'right' },
    { key: 'is_active', label: 'Status', align: 'center' },
    { key: 'actions', label: 'Actions', align: 'center' },
];

const journalColumns = [
    { key: 'no', label: 'No' },
    { key: 'journal_number', label: 'Journal No' },
    { key: 'journal_date', label: 'Date' },
    { key: 'description', label: 'Description' },
    { key: 'total_debit', label: 'Debit', align: 'right' },
    { key: 'total_credit', label: 'Credit', align: 'right' },
    { key: 'status', label: 'Status', align: 'center' },
];

const methodColumns = [
    { key: 'no', label: 'No' },
    { key: 'method_name', label: 'Name' },
    { key: 'method_type', label: 'Type', align: 'center' },
    { key: 'account', label: 'Linked COA' },
    { key: 'status', label: 'Status', align: 'center' },
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
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID');
};

// Journal Actions
function postJournalEntry(id) {
    if (confirm('Post jurnal ini ke buku besar?')) {
        router.post(`/accounting/journals/${id}/post`);
    }
}

function voidJournalEntry(id) {
    if (confirm('Void/batalkan jurnal ini?')) {
        router.post(`/accounting/journals/${id}/void`);
    }
}

// Journal Entry Filters
const urlParams = new URLSearchParams(window.location.search);
const filterStatus = ref(urlParams.get('status') || '');
const filterDateFrom = ref(urlParams.get('date_from') || '');
const filterDateTo = ref(urlParams.get('date_to') || '');
const filterRefType = ref(urlParams.get('reference_type') || '');

function applyJournalFilters() {
    router.get('/accounting', {
        activeTab: 'journals',
        status: filterStatus.value,
        date_from: filterDateFrom.value,
        date_to: filterDateTo.value,
        reference_type: filterRefType.value
    }, { preserveState: true });
}

// General Ledger Filter State
const ledgerAccountId = ref(urlParams.get('ledger_account_id') || '');
const ledgerDateFrom = ref(urlParams.get('date_from') || '');
const ledgerDateTo = ref(urlParams.get('date_to') || '');

function applyLedgerFilters() {
    router.get('/accounting', {
        activeTab: 'ledger',
        ledger_account_id: ledgerAccountId.value,
        date_from: ledgerDateFrom.value,
        date_to: ledgerDateTo.value
    }, { preserveState: true });
}

// Trial Balance Filter State
const trialPeriodId = ref(urlParams.get('trial_period_id') || '');

function applyTrialFilters() {
    router.get('/accounting', {
        activeTab: 'trial',
        trial_period_id: trialPeriodId.value
    }, { preserveState: true });
}

// Fiscal Period Modal & Form
const showPeriodModal = ref(false);
const periodForm = useForm({
    period_name: '',
    start_date: '',
    end_date: '',
});

function openCreatePeriod() {
    periodForm.reset();
    periodForm.clearErrors();
    showPeriodModal.value = true;
}

function submitPeriod() {
    periodForm.post('/accounting/fiscal-periods', {
        onSuccess: () => showPeriodModal.value = false
    });
}

function closePeriod(id) {
    if (confirm('Apakah Anda yakin ingin menutup periode fiskal ini? Transaksi baru tidak dapat dibuat di periode ini.')) {
        router.post(`/accounting/fiscal-periods/${id}/close`);
    }
}

// Journal Templates Modal & Form
const showTemplateModal = ref(false);
const templateForm = useForm({
    template_code: '',
    template_name: '',
    event_type: 'SALE',
    description: '',
    lines: [
        { account_id: '', direction: 'DEBIT', formula: 'grand_total', description: '' },
        { account_id: '', direction: 'CREDIT', formula: 'grand_total', description: '' },
    ]
});

function openCreateTemplate() {
    templateForm.reset();
    templateForm.clearErrors();
    showTemplateModal.value = true;
}

function addTemplateLine() {
    templateForm.lines.push({ account_id: '', direction: 'DEBIT', formula: 'grand_total', description: '' });
}

function removeTemplateLine(idx) {
    templateForm.lines.splice(idx, 1);
}

function submitTemplate() {
    templateForm.post('/accounting/journal-templates', {
        onSuccess: () => showTemplateModal.value = false
    });
}

function deleteTemplate(id) {
    if (confirm('Hapus template jurnal ini?')) {
        router.delete(`/accounting/journal-templates/${id}`);
    }
}

// Accounting Rules Modal & Form
const showRuleModal = ref(false);
const ruleForm = useForm({
    rule_name: '',
    event_type: 'SALE',
    journal_template_id: '',
});

function openCreateRule() {
    ruleForm.reset();
    ruleForm.clearErrors();
    showRuleModal.value = true;
}

function submitRule() {
    ruleForm.post('/accounting/rules', {
        onSuccess: () => showRuleModal.value = false
    });
}

// Additional columns configurations
const ledgerColumns = [
    { key: 'date', label: 'Tanggal' },
    { key: 'journal_number', label: 'No Jurnal' },
    { key: 'description', label: 'Keterangan' },
    { key: 'debit', label: 'Debit', align: 'right' },
    { key: 'credit', label: 'Kredit', align: 'right' },
];

const periodColumns = [
    { key: 'period_name', label: 'Nama Periode' },
    { key: 'start_date', label: 'Mulai' },
    { key: 'end_date', label: 'Selesai' },
    { key: 'status', label: 'Status', align: 'center' },
    { key: 'actions', label: 'Tindakan', align: 'center' },
];

const trialColumns = [
    { key: 'account_code', label: 'Kode Akun' },
    { key: 'account_name', label: 'Nama Akun' },
    { key: 'account_type', label: 'Tipe' },
    { key: 'debit_balance', label: 'Saldo Debit', align: 'right' },
    { key: 'credit_balance', label: 'Saldo Kredit', align: 'right' },
];

const templateColumns = [
    { key: 'template_code', label: 'Kode Template' },
    { key: 'template_name', label: 'Nama Template' },
    { key: 'event_type', label: 'Tipe Event' },
    { key: 'description', label: 'Keterangan' },
    { key: 'actions', label: 'Tindakan', align: 'center' },
];

const ruleColumns = [
    { key: 'rule_name', label: 'Nama Aturan' },
    { key: 'event_type', label: 'Event' },
    { key: 'template', label: 'Template Jurnal' },
    { key: 'status', label: 'Status', align: 'center' },
];
</script>

<template>
    <Head title="Accounting Management" />

    <DashboardLayout>
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-ink-primary">Accounting 📊</h1>
                <p class="text-ink-secondary text-sm">Kelola Chart of Accounts, Journal Entries, dan Metode Pembayaran Kasir.</p>
            </div>

            <div class="flex gap-md">
                <BaseButton v-if="activeTab === 'coa'" @click="openCreateCoa">
                    ➕ New Account
                </BaseButton>
                <BaseButton v-if="activeTab === 'journals'" @click="openCreateJournal">
                    📝 New Journal Entry
                </BaseButton>
                <BaseButton v-if="activeTab === 'methods'" @click="openCreateMethod">
                    💳 New Method
                </BaseButton>
                <BaseButton v-if="activeTab === 'periods'" @click="openCreatePeriod">
                    📅 New Fiscal Period
                </BaseButton>
                <BaseButton v-if="activeTab === 'templates'" @click="openCreateTemplate">
                    📋 New Template
                </BaseButton>
                <BaseButton v-if="activeTab === 'rules'" @click="openCreateRule">
                    ⚙️ New Rule
                </BaseButton>
            </div>
        </div>

        <div class="flex space-x-1 mb-6 border-b border-border-soft overflow-x-auto whitespace-nowrap scrollbar-none">
            <button
                @click="activeTab = 'coa'"
                :class="activeTab === 'coa' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-3 px-6 font-medium transition-colors duration-200 cursor-pointer text-sm"
            >
                Chart of Accounts 📂
            </button>
            <button
                @click="activeTab = 'journals'"
                :class="activeTab === 'journals' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-3 px-6 font-medium transition-colors duration-200 cursor-pointer text-sm"
            >
                Journal Entries 📑
            </button>
            <button
                @click="activeTab = 'methods'"
                :class="activeTab === 'methods' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-3 px-6 font-medium transition-colors duration-200 cursor-pointer text-sm"
            >
                Payment Methods 💳
            </button>
            <button
                @click="activeTab = 'ledger'"
                :class="activeTab === 'ledger' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-3 px-6 font-medium transition-colors duration-200 cursor-pointer text-sm"
            >
                General Ledger 📖
            </button>
            <button
                @click="activeTab = 'periods'"
                :class="activeTab === 'periods' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-3 px-6 font-medium transition-colors duration-200 cursor-pointer text-sm"
            >
                Fiscal Periods 📅
            </button>
            <button
                @click="activeTab = 'trial'"
                :class="activeTab === 'trial' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-3 px-6 font-medium transition-colors duration-200 cursor-pointer text-sm"
            >
                Trial Balance ⚖️
            </button>
            <button
                @click="activeTab = 'templates'"
                :class="activeTab === 'templates' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-3 px-6 font-medium transition-colors duration-200 cursor-pointer text-sm"
            >
                Journal Templates 📋
            </button>
            <button
                @click="activeTab = 'rules'"
                :class="activeTab === 'rules' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-3 px-6 font-medium transition-colors duration-200 cursor-pointer text-sm"
            >
                Accounting Rules ⚙️
            </button>
        </div>

        <!-- Chart of Accounts Tab -->
        <div v-if="activeTab === 'coa'">
            <DataTable :columns="coaColumns" :rows="chartOfAccounts">
                <template #cell-account_code="{ value }">
                    <span class="font-mono text-ink-primary font-semibold">{{ value }}</span>
                </template>
                <template #cell-account_type="{ value }">
                    <span class="px-2 py-0.5 bg-brand-soft text-brand rounded text-xs font-semibold uppercase">
                        {{ value }}
                    </span>
                </template>
                <template #cell-balance="{ value }">
                    <span class="font-mono text-ink-primary font-medium">{{ formatCurrency(value) }}</span>
                </template>
                <template #cell-is_active="{ row }">
                    <StatusBadge :status="row.is_active ? 'ACTIVE' : 'INACTIVE'" size="sm" variant="soft" />
                </template>
                <template #cell-actions="{ row }">
                    <div class="flex gap-md justify-center">
                        <button @click="openEditCoa(row)" class="text-brand hover:underline text-sm font-semibold">Edit</button>
                        <button @click="deleteCoa(row)" class="text-semantic-danger hover:underline text-sm font-semibold">Delete</button>
                    </div>
                </template>
            </DataTable>
        </div>

        <!-- Journal Entries Tab -->
        <div v-if="activeTab === 'journals'">
            <!-- Journal filters -->
            <div class="grid grid-cols-4 gap-md mb-base p-md bg-surface-subtle border border-border-soft rounded-lg">
                <FormSelect label="Status" v-model="filterStatus" @change="applyJournalFilters">
                    <option value="">Semua Status</option>
                    <option value="DRAFT">DRAFT</option>
                    <option value="POSTED">POSTED</option>
                    <option value="VOID">VOID</option>
                </FormSelect>
                <FormInput type="date" label="Mulai Tanggal" v-model="filterDateFrom" @change="applyJournalFilters" />
                <FormInput type="date" label="Sampai Tanggal" v-model="filterDateTo" @change="applyJournalFilters" />
                <FormSelect label="Jenis Referensi" v-model="filterRefType" @change="applyJournalFilters">
                    <option value="">Semua Jenis</option>
                    <option value="manual">Manual</option>
                    <option value="POS_TRANSACTION">POS</option>
                    <option value="PURCHASE">Pembelian</option>
                </FormSelect>
            </div>

            <DataTable :columns="[...journalColumns, { key: 'actions', label: 'Tindakan', align: 'center' }]" :rows="journalEntries">
                <template #cell-journal_number="{ value }">
                    <span class="font-mono text-ink-primary font-semibold">{{ value }}</span>
                </template>
                <template #cell-journal_date="{ value }">
                    <span>{{ formatDate(value) }}</span>
                </template>
                <template #cell-total_debit="{ value }">
                    <span class="font-mono text-ink-primary font-medium">{{ formatCurrency(value) }}</span>
                </template>
                <template #cell-total_credit="{ value }">
                    <span class="font-mono text-ink-primary font-medium">{{ formatCurrency(value) }}</span>
                </template>
                <template #cell-status="{ value }">
                    <StatusBadge :status="value" size="sm" variant="soft" />
                </template>
                <template #cell-actions="{ row }">
                    <div class="flex gap-md justify-center">
                        <button v-if="row.status === 'DRAFT'" @click="postJournalEntry(row.id)" class="text-brand hover:underline text-xs font-bold">POST</button>
                        <button v-if="row.status === 'POSTED'" @click="voidJournalEntry(row.id)" class="text-semantic-danger hover:underline text-xs font-bold">VOID</button>
                        <span v-else class="text-ink-muted text-xs">-</span>
                    </div>
                </template>
            </DataTable>
        </div>

        <!-- Payment Methods Tab -->
        <div v-if="activeTab === 'methods'">
            <DataTable :columns="methodColumns" :rows="paymentMethods">
                <template #cell-method_type="{ value }">
                    <span class="px-2 py-0.5 bg-surface-subtle text-ink-secondary rounded text-xs font-mono font-semibold uppercase">
                        {{ value }}
                    </span>
                </template>
                <template #cell-account="{ row }">
                    <span class="text-sm font-semibold">{{ row.account?.account_name }} ({{ row.account?.account_code }})</span>
                </template>
                <template #cell-status="{ row }">
                    <StatusBadge :status="row.is_active ? 'ACTIVE' : 'INACTIVE'" size="sm" variant="soft" />
                </template>
                <template #cell-actions="{ row }">
                    <div class="flex gap-md justify-center">
                        <button @click="openEditMethod(row)" class="text-brand hover:underline text-sm font-semibold">Edit</button>
                        <button @click="deleteMethod(row)" class="text-semantic-danger hover:underline text-sm font-semibold">Delete</button>
                    </div>
                </template>
            </DataTable>
        </div>

        <!-- General Ledger Tab -->
        <div v-if="activeTab === 'ledger'">
            <div class="grid grid-cols-3 gap-md mb-base p-md bg-surface-subtle border border-border-soft rounded-lg">
                <FormSelect label="Pilih Akun Buku Besar" v-model="ledgerAccountId" @change="applyLedgerFilters">
                    <option value="">-- Pilih Akun --</option>
                    <option v-for="c in chartOfAccounts" :key="c.id" :value="c.id">{{ c.account_name }} ({{ c.account_code }})</option>
                </FormSelect>
                <FormInput type="date" label="Mulai Tanggal" v-model="ledgerDateFrom" @change="applyLedgerFilters" />
                <FormInput type="date" label="Sampai Tanggal" v-model="ledgerDateTo" @change="applyLedgerFilters" />
            </div>

            <DataTable :columns="ledgerColumns" :rows="ledgerLines">
                <template #cell-date="{ value }">
                    <span>{{ formatDate(value) }}</span>
                </template>
                <template #cell-journal_number="{ value }">
                    <span class="font-mono font-semibold text-ink-primary">{{ value }}</span>
                </template>
                <template #cell-debit="{ value }">
                    <span class="font-mono text-ink-primary font-medium">{{ formatCurrency(value) }}</span>
                </template>
                <template #cell-credit="{ value }">
                    <span class="font-mono text-ink-primary font-medium">{{ formatCurrency(value) }}</span>
                </template>
            </DataTable>
        </div>

        <!-- Fiscal Periods Tab -->
        <div v-if="activeTab === 'periods'">
            <DataTable :columns="periodColumns" :rows="fiscalPeriods">
                <template #cell-start_date="{ value }">
                    <span>{{ formatDate(value) }}</span>
                </template>
                <template #cell-end_date="{ value }">
                    <span>{{ formatDate(value) }}</span>
                </template>
                <template #cell-status="{ value }">
                    <StatusBadge :status="value" size="sm" variant="soft" />
                </template>
                <template #cell-actions="{ row }">
                    <div class="flex gap-md justify-center">
                        <button v-if="row.status === 'OPEN'" @click="closePeriod(row.id)" class="text-semantic-danger hover:underline text-xs font-bold">CLOSE PERIOD</button>
                        <span v-else class="text-ink-muted text-xs">Closed</span>
                    </div>
                </template>
            </DataTable>
        </div>

        <!-- Trial Balance Tab -->
        <div v-if="activeTab === 'trial'">
            <div class="mb-base p-md bg-surface-subtle border border-border-soft rounded-lg">
                <FormSelect label="Periode Fiskal" v-model="trialPeriodId" @change="applyTrialFilters">
                    <option value="">-- Pilih Periode --</option>
                    <option v-for="p in fiscalPeriods" :key="p.id" :value="p.id">{{ p.period_name }} ({{ formatDate(p.start_date) }} s/d {{ formatDate(p.end_date) }})</option>
                </FormSelect>
            </div>

            <DataTable :columns="trialColumns" :rows="trialBalances">
                <template #cell-account_code="{ value }">
                    <span class="font-mono text-ink-primary font-semibold">{{ value }}</span>
                </template>
                <template #cell-debit_balance="{ value }">
                    <span class="font-mono text-ink-primary font-medium">{{ formatCurrency(value) }}</span>
                </template>
                <template #cell-credit_balance="{ value }">
                    <span class="font-mono text-ink-primary font-medium">{{ formatCurrency(value) }}</span>
                </template>
            </DataTable>
        </div>

        <!-- Journal Templates Tab -->
        <div v-if="activeTab === 'templates'">
            <DataTable :columns="templateColumns" :rows="journalTemplates">
                <template #cell-template_code="{ value }">
                    <span class="font-mono text-ink-primary font-semibold">{{ value }}</span>
                </template>
                <template #cell-actions="{ row }">
                    <div class="flex gap-md justify-center">
                        <button @click="deleteTemplate(row.id)" class="text-semantic-danger hover:underline text-xs font-bold">DELETE</button>
                    </div>
                </template>
            </DataTable>
        </div>

        <!-- Accounting Rules Tab -->
        <div v-if="activeTab === 'rules'">
            <DataTable :columns="ruleColumns" :rows="accountingRules">
                <template #cell-template="{ row }">
                    <span>{{ row.template?.template_name }} ({{ row.template?.template_code }})</span>
                </template>
                <template #cell-status="{ row }">
                    <StatusBadge :status="row.is_active ? 'ACTIVE' : 'INACTIVE'" size="sm" variant="soft" />
                </template>
            </DataTable>
        </div>

        <!-- COA Create/Edit Modal -->
        <BaseModal :show="showCoaModal" @close="showCoaModal = false" :title="editingCoa ? 'Edit Account' : 'New Chart of Account'">
            <form @submit.prevent="submitCoa" class="space-y-md">
                <div class="grid grid-cols-2 gap-md">
                    <FormInput label="Account Code" v-model="coaForm.account_code" :error="coaForm.errors.account_code" required placeholder="1001-01" />
                    <FormInput label="Account Name" v-model="coaForm.account_name" :error="coaForm.errors.account_name" required placeholder="Kas Toko" />
                </div>
                <div class="grid grid-cols-2 gap-md">
                    <FormSelect label="Account Type" v-model="coaForm.account_type" :error="coaForm.errors.account_type" required>
                        <option value="ASSET">ASSET</option>
                        <option value="LIABILITY">LIABILITY</option>
                        <option value="EQUITY">EQUITY</option>
                        <option value="REVENUE">REVENUE</option>
                        <option value="EXPENSE">EXPENSE</option>
                    </FormSelect>
                    <FormSelect label="Normal Balance" v-model="coaForm.normal_balance" :error="coaForm.errors.normal_balance" required>
                        <option value="DEBIT">DEBIT</option>
                        <option value="CREDIT">CREDIT</option>
                    </FormSelect>
                </div>
                <FormSelect label="Parent Account (Optional)" v-model="coaForm.parent_id" :error="coaForm.errors.parent_id">
                    <option value="">None (Top Level)</option>
                    <option v-for="c in chartOfAccounts" :key="c.id" :value="c.id">{{ c.account_name }} ({{ c.account_code }})</option>
                </FormSelect>
                <FormTextarea label="Description" v-model="coaForm.description" :error="coaForm.errors.description" placeholder="Catatan kegunaan akun..." rows="2" />
                
                <div class="flex items-center gap-md">
                    <label class="flex items-center gap-sm cursor-pointer">
                        <input type="checkbox" v-model="coaForm.is_postable" class="rounded text-brand" />
                        <span class="text-sm text-ink-primary">Postable (Bisa dijurnal langsung)</span>
                    </label>
                    <label class="flex items-center gap-sm cursor-pointer">
                        <input type="checkbox" v-model="coaForm.is_active" class="rounded text-brand" />
                        <span class="text-sm text-ink-primary">Active</span>
                    </label>
                </div>

                <div class="flex justify-end gap-md pt-md border-t border-border-soft">
                    <BaseButton type="button" variant="secondary" @click="showCoaModal = false">Cancel</BaseButton>
                    <BaseButton type="submit" :loading="coaForm.processing">Save Account</BaseButton>
                </div>
            </form>
        </BaseModal>

        <!-- Journal Entry Create Modal -->
        <BaseModal :show="showJournalModal" @close="showJournalModal = false" title="New Journal Entry" class="max-w-4xl">
            <form @submit.prevent="submitJournal" class="space-y-md">
                <div class="grid grid-cols-3 gap-md">
                    <FormInput type="date" label="Journal Date" v-model="journalForm.journal_date" :error="journalForm.errors.journal_date" required />
                    <div class="col-span-2">
                        <FormInput label="Description" v-model="journalForm.description" :error="journalForm.errors.description" required placeholder="Catatan transaksi jurnal..." />
                    </div>
                </div>

                <!-- Lines Table -->
                <div class="space-y-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-semibold text-ink-primary">Journal Lines</span>
                        <BaseButton type="button" variant="secondary" size="sm" @click="addJournalLine">➕ Add Row</BaseButton>
                    </div>
                    <div class="overflow-x-auto border border-border-soft rounded-lg">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-surface-subtle text-xs text-ink-secondary uppercase">
                                <tr>
                                    <th class="p-sm">Account</th>
                                    <th class="p-sm w-36 text-right">Debit</th>
                                    <th class="p-sm w-36 text-right">Credit</th>
                                    <th class="p-sm">Memo (Optional)</th>
                                    <th class="p-sm w-12"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border-soft">
                                <tr v-for="(line, idx) in journalForm.lines" :key="idx">
                                    <td class="p-sm">
                                        <FormSelect v-model="line.account_id" required>
                                            <option value="">Pilih Akun</option>
                                            <option v-for="c in chartOfAccounts" :key="c.id" :value="c.id">{{ c.account_name }} ({{ c.account_code }})</option>
                                        </FormSelect>
                                    </td>
                                    <td class="p-sm">
                                        <FormInput type="number" step="0.01" v-model="line.debit" class="text-right" />
                                    </td>
                                    <td class="p-sm">
                                        <FormInput type="number" step="0.01" v-model="line.credit" class="text-right" />
                                    </td>
                                    <td class="p-sm">
                                        <FormInput v-model="line.description" placeholder="Memo line..." />
                                    </td>
                                    <td class="p-sm text-center">
                                        <button type="button" @click="removeJournalLine(idx)" class="text-semantic-danger">✕</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Totals Section -->
                <div class="flex justify-end gap-xl text-sm font-semibold border-t border-border-soft pt-md">
                    <div>
                        <span class="text-ink-secondary">Total Debit: </span>
                        <span class="font-mono text-ink-primary">{{ formatCurrency(journalTotals.debits) }}</span>
                    </div>
                    <div>
                        <span class="text-ink-secondary">Total Kredit: </span>
                        <span class="font-mono text-ink-primary">{{ formatCurrency(journalTotals.credits) }}</span>
                    </div>
                    <div>
                        <span :class="journalTotals.isBalanced ? 'text-semantic-success bg-semantic-success-soft' : 'text-semantic-danger bg-semantic-danger-soft'" class="px-2 py-0.5 rounded text-xs">
                            {{ journalTotals.isBalanced ? '✓ Balanced' : '✕ Unbalanced' }}
                        </span>
                    </div>
                </div>

                <div class="flex justify-end gap-md pt-md border-t border-border-soft">
                    <BaseButton type="button" variant="secondary" @click="showJournalModal = false">Cancel</BaseButton>
                    <BaseButton type="submit" :loading="journalForm.processing" :disabled="!journalTotals.isBalanced">Post Journal</BaseButton>
                </div>
            </form>
        </BaseModal>

        <!-- Payment Method Modal -->
        <BaseModal :show="showMethodModal" @close="showMethodModal = false" :title="editingMethod ? 'Edit Payment Method' : 'New Payment Method'">
            <form @submit.prevent="submitMethod" class="space-y-md">
                <FormInput label="Method Name" v-model="methodForm.method_name" :error="methodForm.errors.method_name" required placeholder="Gopay / QRIS Bank" />
                <div class="grid grid-cols-2 gap-md">
                    <FormSelect label="Method Type" v-model="methodForm.method_type" :error="methodForm.errors.method_type" required>
                        <option value="CASH">CASH</option>
                        <option value="BANK">BANK</option>
                        <option value="CARD">CARD</option>
                        <option value="EWALLET">EWALLET</option>
                        <option value="QRIS">QRIS</option>
                        <option value="OTHER">OTHER</option>
                    </FormSelect>
                    <FormSelect label="Linked Cash/Bank COA" v-model="methodForm.account_id" :error="methodForm.errors.account_id" required>
                        <option value="">Pilih Akun Kas/Bank</option>
                        <option v-for="c in chartOfAccounts" :key="c.id" :value="c.id">{{ c.account_name }} ({{ c.account_code }})</option>
                    </FormSelect>
                </div>
                
                <div class="flex items-center gap-md">
                    <label class="flex items-center gap-sm cursor-pointer">
                        <input type="checkbox" v-model="methodForm.requires_reference" class="rounded text-brand" />
                        <span class="text-sm text-ink-primary">Membutuhkan Nomor Referensi / Struk</span>
                    </label>
                    <label class="flex items-center gap-sm cursor-pointer">
                        <input type="checkbox" v-model="methodForm.is_active" class="rounded text-brand" />
                        <span class="text-sm text-ink-primary">Active</span>
                    </label>
                </div>
 
                <div class="flex justify-end gap-md pt-md border-t border-border-soft">
                    <BaseButton type="button" variant="secondary" @click="showMethodModal = false">Cancel</BaseButton>
                    <BaseButton type="submit" :loading="methodForm.processing">Save Method</BaseButton>
                </div>
            </form>
        </BaseModal>

        <!-- Fiscal Period Create Modal -->
        <BaseModal :show="showPeriodModal" @close="showPeriodModal = false" title="New Fiscal Period">
            <form @submit.prevent="submitPeriod" class="space-y-md">
                <FormInput label="Nama Periode" v-model="periodForm.period_name" :error="periodForm.errors.period_name" required placeholder="Agustus 2024" />
                <div class="grid grid-cols-2 gap-md">
                    <FormInput type="date" label="Tanggal Mulai" v-model="periodForm.start_date" :error="periodForm.errors.start_date" required />
                    <FormInput type="date" label="Tanggal Selesai" v-model="periodForm.end_date" :error="periodForm.errors.end_date" required />
                </div>
                <div class="flex justify-end gap-md pt-md border-t border-border-soft">
                    <BaseButton type="button" variant="secondary" @click="showPeriodModal = false">Cancel</BaseButton>
                    <BaseButton type="submit" :loading="periodForm.processing">Simpan Periode</BaseButton>
                </div>
            </form>
        </BaseModal>

        <!-- Journal Template Create Modal -->
        <BaseModal :show="showTemplateModal" @close="showTemplateModal = false" title="New Journal Template" class="max-w-4xl">
            <form @submit.prevent="submitTemplate" class="space-y-md">
                <div class="grid grid-cols-3 gap-md">
                    <FormInput label="Kode Template" v-model="templateForm.template_code" :error="templateForm.errors.template_code" required placeholder="TMPL-SALE" />
                    <FormInput label="Nama Template" v-model="templateForm.template_name" :error="templateForm.errors.template_name" required placeholder="Template Penjualan" />
                    <FormSelect label="Tipe Event" v-model="templateForm.event_type" :error="templateForm.errors.event_type" required>
                        <option value="SALE">SALE</option>
                        <option value="PURCHASE">PURCHASE</option>
                        <option value="ADJUSTMENT">ADJUSTMENT</option>
                    </FormSelect>
                </div>
                <FormInput label="Keterangan" v-model="templateForm.description" :error="templateForm.errors.description" placeholder="Catatan kegunaan template..." />

                <div class="space-y-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-semibold text-ink-primary">Baris Aturan Jurnal</span>
                        <BaseButton type="button" variant="secondary" size="sm" @click="addTemplateLine">➕ Add Row</BaseButton>
                    </div>
                    <table class="w-full text-sm text-left border border-border-soft rounded-lg overflow-hidden">
                        <thead class="bg-surface-subtle text-xs uppercase text-ink-secondary">
                            <tr>
                                <th class="p-sm">Akun COA</th>
                                <th class="p-sm w-36">Posisi</th>
                                <th class="p-sm w-36">Rumus Nilai</th>
                                <th class="p-sm">Memo</th>
                                <th class="p-sm w-12"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(line, idx) in templateForm.lines" :key="idx">
                                <td class="p-sm">
                                    <FormSelect v-model="line.account_id" required>
                                        <option value="">Pilih Akun</option>
                                        <option v-for="c in chartOfAccounts" :key="c.id" :value="c.id">{{ c.account_name }} ({{ c.account_code }})</option>
                                    </FormSelect>
                                </td>
                                <td class="p-sm">
                                    <FormSelect v-model="line.direction" required>
                                        <option value="DEBIT">DEBIT</option>
                                        <option value="CREDIT">CREDIT</option>
                                    </FormSelect>
                                </td>
                                <td class="p-sm">
                                    <FormSelect v-model="line.formula" required>
                                        <option value="grand_total">Grand Total</option>
                                        <option value="subtotal">Subtotal</option>
                                        <option value="tax_amount">Total Pajak</option>
                                    </FormSelect>
                                </td>
                                <td class="p-sm">
                                    <FormInput v-model="line.description" placeholder="Memo..." />
                                </td>
                                <td class="p-sm text-center">
                                    <button type="button" @click="removeTemplateLine(idx)" class="text-semantic-danger">✕</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end gap-md pt-md border-t border-border-soft">
                    <BaseButton type="button" variant="secondary" @click="showTemplateModal = false">Cancel</BaseButton>
                    <BaseButton type="submit" :loading="templateForm.processing">Simpan Template</BaseButton>
                </div>
            </form>
        </BaseModal>

        <!-- Accounting Rule Create Modal -->
        <BaseModal :show="showRuleModal" @close="showRuleModal = false" title="New Accounting Rule">
            <form @submit.prevent="submitRule" class="space-y-md">
                <FormInput label="Nama Aturan" v-model="ruleForm.rule_name" :error="ruleForm.errors.rule_name" required placeholder="Auto Journal Penjualan POS" />
                <div class="grid grid-cols-2 gap-md">
                    <FormSelect label="Tipe Event" v-model="ruleForm.event_type" :error="ruleForm.errors.event_type" required>
                        <option value="SALE">SALE</option>
                        <option value="PURCHASE">PURCHASE</option>
                        <option value="ADJUSTMENT">ADJUSTMENT</option>
                    </FormSelect>
                    <FormSelect label="Pilih Template Jurnal" v-model="ruleForm.journal_template_id" :error="ruleForm.errors.journal_template_id" required>
                        <option value="">-- Pilih Template --</option>
                        <option v-for="t in journalTemplates" :key="t.id" :value="t.id">{{ t.template_name }} ({{ t.template_code }})</option>
                    </FormSelect>
                </div>
                <div class="flex justify-end gap-md pt-md border-t border-border-soft">
                    <BaseButton type="button" variant="secondary" @click="showRuleModal = false">Cancel</BaseButton>
                    <BaseButton type="submit" :loading="ruleForm.processing">Simpan Aturan</BaseButton>
                </div>
            </form>
        </BaseModal>
    </DashboardLayout>
</template>
