<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import DataTable from '@/Components/Table/DataTable.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';

const props = defineProps({
    loyaltyAccounts: { type: Array, default: () => [] },
    loyaltyTransactions: { type: Array, default: () => [] },
    membershipTiers: { type: Array, default: () => [] },
});

const activeTab = ref('accounts');
const loyaltyAccounts = ref(props.loyaltyAccounts);
const loyaltyTransactions = ref(props.loyaltyTransactions);
const membershipTiers = ref(props.membershipTiers);

const accountColumns = [
    { key: 'customer', label: 'Customer Name' },
    { key: 'account_number', label: 'Account Number' },
    { key: 'current_points', label: 'Current Points', align: 'right' },
    { key: 'tier', label: 'Loyalty Tier' },
    { key: 'status', label: 'Status', align: 'center' },
];

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <Head title="Loyalty Program Management" />

    <DashboardLayout>
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-ink-primary">
                    Loyalty Program
                </h1>
                <p class="text-ink-secondary text-sm">
                    Kelola akun poin pelanggan, riwayat penukaran poin, dan tingkatan keanggotaan (Membership Tiers).
                </p>
            </div>

            <BaseButton v-if="activeTab === 'accounts'">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Account
            </BaseButton>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex space-x-1 mb-6 border-b border-border-soft">
            <button
                @click="activeTab = 'accounts'"
                :class="activeTab === 'accounts' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-3 px-6 font-medium transition-colors duration-200 cursor-pointer text-sm"
            >
                Loyalty Accounts
            </button>
            <button
                @click="activeTab = 'transactions'"
                :class="activeTab === 'transactions' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-3 px-6 font-medium transition-colors duration-200 cursor-pointer text-sm"
            >
                Points Transactions
            </button>
            <button
                @click="activeTab = 'tiers'"
                :class="activeTab === 'tiers' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-3 px-6 font-medium transition-colors duration-200 cursor-pointer text-sm"
            >
                Membership Tiers
            </button>
        </div>

        <!-- Loyalty Accounts Tab -->
        <div v-if="activeTab === 'accounts'">
            <DataTable :columns="accountColumns" :rows="loyaltyAccounts">
                <template #cell-customer="{ row }">
                    <span class="font-semibold text-ink-primary">{{ row.customer?.customer_name }}</span>
                </template>
                <template #cell-account_number="{ value }">
                    <span class="font-mono text-ink-secondary text-sm">{{ value }}</span>
                </template>
                <template #cell-current_points="{ value }">
                    <span class="font-mono font-bold text-brand">{{ value }} pts</span>
                </template>
                <template #cell-tier="{ row }">
                    <span
                        :class="[
                            row.tier?.tier_name === 'Platinum' ? 'bg-indigo-50 text-indigo-600 border border-indigo-200' : '',
                            row.tier?.tier_name === 'Gold' ? 'bg-yellow-50 text-yellow-700 border border-yellow-200' : '',
                            row.tier?.tier_name === 'Regular' ? 'bg-surface-subtle text-ink-secondary' : '',
                        ]"
                        class="px-2.5 py-0.5 rounded-full text-xs font-semibold"
                    >
                        {{ row.tier?.tier_name || 'Regular' }}
                    </span>
                </template>
                <template #cell-status="{ row }">
                    <span
                        :class="row.is_active ? 'bg-semantic-success-soft text-semantic-success' : 'bg-surface-subtle text-ink-muted'"
                        class="px-2 py-0.5 rounded text-xs font-semibold"
                    >
                        {{ row.is_active ? 'Active' : 'Inactive' }}
                    </span>
                </template>
            </DataTable>
        </div>

        <!-- Points Transactions Tab -->
        <div v-if="activeTab === 'transactions'" class="space-y-base">
            <div
                v-for="txn in loyaltyTransactions"
                :key="txn.id"
                class="bg-surface-card border border-border-soft p-xl rounded-2xl shadow-soft flex justify-between items-center"
            >
                <div class="space-y-xs">
                    <h4 class="font-bold text-ink-primary">{{ txn.loyalty_account?.customer?.customer_name }}</h4>
                    <p class="text-sm text-ink-secondary">{{ txn.reason }}</p>
                    <span class="text-xs text-ink-muted block">{{ formatDate(txn.transaction_date) }}</span>
                </div>
                <div class="text-right">
                    <span
                        :class="txn.transaction_type === 'EARN' ? 'text-semantic-success bg-semantic-success-soft' : 'text-semantic-danger bg-semantic-danger-soft'"
                        class="px-3 py-1 rounded-full text-sm font-bold border border-transparent"
                    >
                        {{ txn.transaction_type === 'EARN' ? '+' : '-' }}{{ txn.points }} points
                    </span>
                </div>
            </div>
        </div>

        <!-- Membership Tiers Tab -->
        <div v-if="activeTab === 'tiers'" class="grid grid-cols-1 md:grid-cols-3 gap-xl">
            <div
                v-for="tier in membershipTiers"
                :key="tier.id"
                :class="[
                    tier.tier_name === 'Platinum' ? 'border-indigo-400 ring-2 ring-indigo-50' : 'border-border-soft',
                ]"
                class="bg-surface-card border rounded-2xl p-xl shadow-soft hover:shadow-medium transition-all duration-200 flex flex-col justify-between"
            >
                <div>
                    <div class="flex justify-between items-start mb-base">
                        <h4 class="font-bold text-lg text-ink-primary">{{ tier.tier_name }}</h4>
                        <span
                            :class="[
                                tier.tier_name === 'Platinum' ? 'bg-indigo-50 text-indigo-600 border border-indigo-200' : '',
                                tier.tier_name === 'Gold' ? 'bg-yellow-50 text-yellow-700 border border-yellow-200' : '',
                                tier.tier_name === 'Regular' ? 'bg-surface-subtle text-ink-secondary' : '',
                            ]"
                            class="px-2.5 py-0.5 rounded-full text-xs font-semibold"
                        >
                            {{ tier.tier_name }}
                        </span>
                    </div>

                    <div class="mb-xl">
                        <span class="text-xs text-ink-muted block mb-xs">Minimum Points</span>
                        <span class="font-mono font-bold text-2xl text-ink-primary">{{ tier.minimum_points }} pts</span>
                    </div>

                    <div class="space-y-sm bg-surface-main p-md rounded-xl border border-border-soft">
                        <div class="flex justify-between text-sm">
                            <span class="text-ink-secondary">Point Multiplier</span>
                            <span class="font-semibold text-brand">{{ tier.point_multiplier }}x</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-ink-secondary">Special Discount</span>
                            <span class="font-semibold text-brand">{{ tier.discount_percentage ? `${tier.discount_percentage}%` : 'None' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
