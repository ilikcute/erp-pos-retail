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

const props = defineProps({
    chartOfAccounts: { type: Array, default: () => [] },
    journalEntries: { type: Array, default: () => [] },
    paymentMethods: { type: Array, default: () => [] },
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

const getStatusClass = (status) => {
    const classes = {
        DRAFT: 'bg-surface-subtle text-ink-muted border border-border-soft',
        PENDING: 'bg-semantic-warning-soft text-semantic-warning',
        APPROVED: 'bg-brand-soft text-brand',
        POSTED: 'bg-semantic-success-soft text-semantic-success',
    };
    return classes[status] || 'bg-surface-subtle text-ink-muted';
};
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
            </div>
        </div>

        <!-- Tabs -->
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
            <DataTable :columns="journalColumns" :rows="journalEntries">
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
                    <span :class="getStatusClass(value)" class="px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase">
                        {{ value }}
                    </span>
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
                    <span :class="row.is_active ? 'bg-semantic-success-soft text-semantic-success' : 'bg-surface-subtle text-ink-muted'" class="px-2 py-0.5 rounded text-xs font-semibold">
                        {{ row.is_active ? 'Active' : 'Inactive' }}
                    </span>
                </template>
                <template #cell-actions="{ row }">
                    <div class="flex gap-md justify-center">
                        <button @click="openEditMethod(row)" class="text-brand hover:underline text-sm font-semibold">Edit</button>
                        <button @click="deleteMethod(row)" class="text-semantic-danger hover:underline text-sm font-semibold">Delete</button>
                    </div>
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
    </DashboardLayout>
</template>
