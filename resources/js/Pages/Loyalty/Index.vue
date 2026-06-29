<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import DataTable from '@/Components/Table/DataTable.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import BaseModal from '@/Components/Modal/BaseModal.vue';
import FormSelect from '@/Components/Form/FormSelect.vue';
import FormInput from '@/Components/Form/FormInput.vue';

const props = defineProps({
    loyaltyAccounts: { type: Array, default: () => [] },
    loyaltyTransactions: { type: Array, default: () => [] },
    membershipTiers: { type: Array, default: () => [] },
    rewards: { type: Array, default: () => [] },
    customers: { type: Array, default: () => [] },
});

const activeTab = ref('accounts');

// Modals State
const showAccountModal = ref(false);
const showAdjustmentModal = ref(false);
const showRedeemModal = ref(false);
const showTierModal = ref(false);

// Forms
const accountForm = useForm({
    customer_id: '',
});

const adjustmentForm = useForm({
    loyalty_account_id: '',
    points: 0,
    adjustment_type: 'ADD',
    remarks: '',
});

const redeemForm = useForm({
    loyalty_account_id: '',
    reward_id: '',
});

const tierForm = useForm({
    tier_name: '',
    minimum_points: 0,
    point_multiplier: 1.0,
    discount_percentage: 0,
});

function openCreateAccount() {
    accountForm.reset();
    accountForm.clearErrors();
    showAccountModal.value = true;
}

function submitAccount() {
    accountForm.post('/loyalty/accounts', {
        onSuccess: () => showAccountModal.value = false
    });
}

function openCreateAdjustment() {
    adjustmentForm.reset();
    adjustmentForm.clearErrors();
    showAdjustmentModal.value = true;
}

function submitAdjustment() {
    adjustmentForm.post('/loyalty/adjustments', {
        onSuccess: () => showAdjustmentModal.value = false
    });
}

function openCreateRedeem() {
    redeemForm.reset();
    redeemForm.clearErrors();
    showRedeemModal.value = true;
}

function submitRedeem() {
    redeemForm.post('/loyalty/redeem', {
        onSuccess: () => showRedeemModal.value = false
    });
}

function openCreateTier() {
    tierForm.reset();
    tierForm.clearErrors();
    showTierModal.value = true;
}

function submitTier() {
    tierForm.post('/loyalty/tiers', {
        onSuccess: () => showTierModal.value = false
    });
}

const accountColumns = [
    { key: 'no', label: 'No' },
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
        <div class="mb-6 flex justify-between items-center flex-wrap gap-md">
            <div>
                <h1 class="text-2xl font-bold text-ink-primary">Loyalty Program 🏆</h1>
                <p class="text-ink-secondary text-sm">
                    Kelola akun poin pelanggan, riwayat penukaran poin, dan tingkatan keanggotaan (Membership Tiers).
                </p>
            </div>

            <div class="flex gap-md">
                <BaseButton v-if="activeTab === 'accounts'" @click="openCreateAccount">
                    ➕ New Account
                </BaseButton>
                <BaseButton v-if="activeTab === 'accounts'" @click="openCreateAdjustment" variant="secondary">
                    ⚙️ Point Adjustment
                </BaseButton>
                <BaseButton v-if="activeTab === 'accounts'" @click="openCreateRedeem" variant="secondary">
                    🎁 Redeem Reward
                </BaseButton>
                <BaseButton v-if="activeTab === 'tiers'" @click="openCreateTier">
                    ➕ New Tier
                </BaseButton>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex space-x-1 mb-6 border-b border-border-soft">
            <button
                @click="activeTab = 'accounts'"
                :class="activeTab === 'accounts' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-3 px-6 font-medium transition-colors duration-200 cursor-pointer text-sm"
            >
                Loyalty Accounts 👥
            </button>
            <button
                @click="activeTab = 'transactions'"
                :class="activeTab === 'transactions' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-3 px-6 font-medium transition-colors duration-200 cursor-pointer text-sm"
            >
                Points Transactions 🔄
            </button>
            <button
                @click="activeTab = 'tiers'"
                :class="activeTab === 'tiers' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-3 px-6 font-medium transition-colors duration-200 cursor-pointer text-sm"
            >
                Membership Tiers 👑
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
                        class="px-3 py-1 rounded-full text-sm font-bold border border-transparent animate-pulse"
                    >
                        {{ txn.transaction_type === 'EARN' ? '+' : '-' }}{{ txn.points }} points
                    </span>
                </div>
            </div>
            <div v-if="loyaltyTransactions.length === 0" class="text-center text-ink-secondary py-xl">
                Belum ada transaksi poin.
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

        <!-- Modals -->
        <!-- New Account Modal -->
        <BaseModal :show="showAccountModal" @close="showAccountModal = false" title="Register New Loyalty Account">
            <form @submit.prevent="submitAccount" class="space-y-md">
                <FormSelect label="Select Customer" v-model="accountForm.customer_id" :error="accountForm.errors.customer_id" required>
                    <option value="">Pilih Pelanggan</option>
                    <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.customer_name }} ({{ c.phone || 'No phone' }})</option>
                </FormSelect>
                <div class="flex justify-end gap-md pt-md border-t border-border-soft">
                    <BaseButton type="button" variant="secondary" @click="showAccountModal = false">Cancel</BaseButton>
                    <BaseButton type="submit" :loading="accountForm.processing">Register Account</BaseButton>
                </div>
            </form>
        </BaseModal>

        <!-- Point Adjustment Modal -->
        <BaseModal :show="showAdjustmentModal" @close="showAdjustmentModal = false" title="Point Adjustment">
            <form @submit.prevent="submitAdjustment" class="space-y-md">
                <FormSelect label="Select Loyalty Account" v-model="adjustmentForm.loyalty_account_id" :error="adjustmentForm.errors.loyalty_account_id" required>
                    <option value="">Pilih Akun</option>
                    <option v-for="a in loyaltyAccounts" :key="a.id" :value="a.id">{{ a.customer?.customer_name }} ({{ a.account_number }})</option>
                </FormSelect>
                <div class="grid grid-cols-2 gap-md">
                    <FormSelect label="Adjustment Type" v-model="adjustmentForm.adjustment_type" required>
                        <option value="ADD">➕ Add Points</option>
                        <option value="SUBTRACT">➖ Subtract Points</option>
                    </FormSelect>
                    <FormInput type="number" label="Points" v-model="adjustmentForm.points" :error="adjustmentForm.errors.points" required />
                </div>
                <FormInput label="Reason / Remarks" v-model="adjustmentForm.remarks" :error="adjustmentForm.errors.remarks" required placeholder="Bonus pendaftaran, koreksi manual..." />
                
                <div class="flex justify-end gap-md pt-md border-t border-border-soft">
                    <BaseButton type="button" variant="secondary" @click="showAdjustmentModal = false">Cancel</BaseButton>
                    <BaseButton type="submit" :loading="adjustmentForm.processing">Save Adjustment</BaseButton>
                </div>
            </form>
        </BaseModal>

        <!-- Redeem Reward Modal -->
        <BaseModal :show="showRedeemModal" @close="showRedeemModal = false" title="Redeem Point Reward">
            <form @submit.prevent="submitRedeem" class="space-y-md">
                <FormSelect label="Select Loyalty Account" v-model="redeemForm.loyalty_account_id" :error="redeemForm.errors.loyalty_account_id" required>
                    <option value="">Pilih Akun</option>
                    <option v-for="a in loyaltyAccounts" :key="a.id" :value="a.id">{{ a.customer?.customer_name }} ({{ a.account_number }} - {{ a.current_points }} pts)</option>
                </FormSelect>
                <FormSelect label="Select Reward" v-model="redeemForm.reward_id" :error="redeemForm.errors.reward_id" required>
                    <option value="">Pilih Hadiah</option>
                    <option v-for="r in rewards" :key="r.id" :value="r.id">{{ r.reward_name }} ({{ r.points_required }} pts)</option>
                </FormSelect>
                
                <div class="flex justify-end gap-md pt-md border-t border-border-soft">
                    <BaseButton type="button" variant="secondary" @click="showRedeemModal = false">Cancel</BaseButton>
                    <BaseButton type="submit" :loading="redeemForm.processing">Process Redemption</BaseButton>
                </div>
            </form>
        </BaseModal>

        <!-- New Tier Modal -->
        <BaseModal :show="showTierModal" @close="showTierModal = false" title="Create Membership Tier">
            <form @submit.prevent="submitTier" class="space-y-md">
                <FormInput label="Tier Name" v-model="tierForm.tier_name" :error="tierForm.errors.tier_name" required placeholder="Silver / Gold" />
                <div class="grid grid-cols-3 gap-md">
                    <FormInput type="number" label="Min Points" v-model="tierForm.minimum_points" :error="tierForm.errors.minimum_points" required />
                    <FormInput type="number" step="0.1" label="Point Multiplier" v-model="tierForm.point_multiplier" :error="tierForm.errors.point_multiplier" required />
                    <FormInput type="number" step="0.1" label="Discount %" v-model="tierForm.discount_percentage" :error="tierForm.errors.discount_percentage" required />
                </div>
                
                <div class="flex justify-end gap-md pt-md border-t border-border-soft">
                    <BaseButton type="button" variant="secondary" @click="showTierModal = false">Cancel</BaseButton>
                    <BaseButton type="submit" :loading="tierForm.processing">Save Tier</BaseButton>
                </div>
            </form>
        </BaseModal>
    </DashboardLayout>
</template>
