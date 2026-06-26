<script setup>
import { ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import DataTable from '@/Components/Table/DataTable.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    chartOfAccounts: Array,
    journalEntries: Array,
    activeTab: { type: String, default: 'coa' },
});

const activeTab = ref(props.activeTab || 'coa');
const chartOfAccounts = ref(props.chartOfAccounts || []);
const journalEntries = ref(props.journalEntries || []);

const coaColumns = [
    { key: 'no', label: 'No' },
    { key: 'account_code', label: 'Account Code' },
    { key: 'account_name', label: 'Account Name' },
    { key: 'account_type', label: 'Type' },
    { key: 'balance', label: 'Balance', align: 'right' },
];

const journalColumns = [
    { key: 'no', label: 'No' },
    { key: 'journal_no', label: 'Journal No' },
    { key: 'journal_date', label: 'Date' },
    { key: 'description', label: 'Description' },
    { key: 'total_debit', label: 'Debit', align: 'right' },
    { key: 'total_credit', label: 'Credit', align: 'right' },
    { key: 'status', label: 'Status', align: 'center' },
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
    };
    return classes[status] || 'bg-surface-subtle text-ink-muted';
};
</script>

<template>
    <Head title="Accounting Management" />

    <DashboardLayout>
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-ink-primary">
                    Accounting
                </h1>
                <p class="text-ink-secondary">
                    Kelola Chart of Accounts, Journal Entries, dan General Ledger
                </p>
            </div>

            <div class="flex gap-md">
                <BaseButton v-if="activeTab === 'coa'">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Account
                </BaseButton>
                <BaseButton v-if="activeTab === 'journals'">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Entry
                </BaseButton>
            </div>
        </div>

        <!-- Tabs -->
        <div class="flex space-x-4 mb-6 border-b border-border-soft">
            <button
                @click="activeTab = 'coa'"
                :class="activeTab === 'coa' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-2 px-4 font-medium transition-colors cursor-pointer"
            >
                Chart of Accounts
            </button>
            <button
                @click="activeTab = 'journals'"
                :class="activeTab === 'journals' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-2 px-4 font-medium transition-colors cursor-pointer"
            >
                Journal Entries
            </button>
            <button
                @click="activeTab = 'ledger'"
                :class="activeTab === 'ledger' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-2 px-4 font-medium transition-colors cursor-pointer"
            >
                General Ledger
            </button>
        </div>

        <!-- Chart of Accounts -->
        <div v-if="activeTab === 'coa'">
            <DataTable :columns="coaColumns" :rows="chartOfAccounts">
                <template #cell-account_code="{ value }">
                    <span class="font-mono text-ink-primary font-semibold">{{ value }}</span>
                </template>
                <template #cell-account_type="{ value }">
                    <span class="px-2 py-1 bg-brand-soft text-brand rounded text-xs font-semibold">
                        {{ value }}
                    </span>
                </template>
                <template #cell-balance="{ value }">
                    <span class="font-mono text-ink-primary font-medium">{{ formatCurrency(value) }}</span>
                </template>
            </DataTable>
        </div>

        <!-- Journal Entries -->
        <div v-if="activeTab === 'journals'">
            <DataTable :columns="journalColumns" :rows="journalEntries">
                <template #cell-journal_no="{ value }">
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
                    <span
                        :class="getStatusClass(value)"
                        class="px-2 py-1 rounded-full text-xs font-semibold border border-transparent"
                    >
                        {{ value }}
                    </span>
                </template>
            </DataTable>
        </div>

        <!-- General Ledger -->
        <div v-if="activeTab === 'ledger'" class="bg-surface-card border border-border-soft rounded-md p-6 text-center text-ink-secondary">
            General Ledger coming soon...
        </div>
    </DashboardLayout>
</template>
