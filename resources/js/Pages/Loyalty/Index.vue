<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import DataTable from '@/Components/Table/DataTable.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import BaseModal from '@/Components/Modal/BaseModal.vue';
import FormSelect from '@/Components/Form/FormSelect.vue';
import FormInput from '@/Components/Form/FormInput.vue';
import StatusBadge from '@/Components/DataDisplay/StatusBadge.vue';

const props = defineProps({
    loyaltyAccounts: { type: Array, default: () => [] },
    loyaltyTransactions: { type: Array, default: () => [] },
    membershipTiers: { type: Array, default: () => [] },
    rewards: { type: Array, default: () => [] },
    customers: { type: Array, default: () => [] },
    loyaltyRedemptions: { type: Array, default: () => [] },
    loyaltyConfiguration: { type: Object, default: () => null },
    products: { type: Array, default: () => [] },
});

const activeTab = ref('accounts');

// Modals State
const showAccountModal = ref(false);
const showAdjustmentModal = ref(false);
const showRedeemModal = ref(false);
const showTierModal = ref(false);
const showRewardModal = ref(false);
const showRejectModal = ref(false);

const isEditingTier = ref(false);
const editingTierId = ref(null);
const isEditingReward = ref(false);
const editingRewardId = ref(null);
const rejectingRedemptionId = ref(null);

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

const rewardForm = useForm({
    reward_code: '',
    reward_name: '',
    reward_type: 'VOUCHER',
    point_required: 0,
    voucher_amount: '',
    discount_percentage: '',
    product_id: '',
    stock_qty: 0,
    description: '',
    is_active: true,
});

const rejectForm = useForm({
    rejection_notes: '',
});

const configForm = useForm({
    point_expiry_months: props.loyaltyConfiguration?.point_expiry_months || 12,
    minimum_redeem_points: props.loyaltyConfiguration?.minimum_redeem_points || 100,
    point_value: props.loyaltyConfiguration?.point_value || 100,
    earn_rate: props.loyaltyConfiguration?.earn_rate || 1000,
    allow_negative_point: props.loyaltyConfiguration?.allow_negative_point ?? false,
    is_enabled: props.loyaltyConfiguration?.is_enabled ?? true,
    terms_and_conditions: props.loyaltyConfiguration?.terms_and_conditions || '',
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
    isEditingTier.value = false;
    editingTierId.value = null;
    tierForm.reset();
    tierForm.clearErrors();
    showTierModal.value = true;
}

function openEditTier(tier) {
    isEditingTier.value = true;
    editingTierId.value = tier.id;
    tierForm.clearErrors();
    tierForm.tier_name = tier.tier_name;
    tierForm.minimum_points = tier.minimum_points;
    tierForm.point_multiplier = tier.point_multiplier;
    tierForm.discount_percentage = tier.discount_percentage;
    showTierModal.value = true;
}

function submitTier() {
    if (isEditingTier.value) {
        tierForm.put(`/loyalty/tiers/${editingTierId.value}`, {
            onSuccess: () => showTierModal.value = false
        });
    } else {
        tierForm.post('/loyalty/tiers', {
            onSuccess: () => showTierModal.value = false
        });
    }
}

function deleteTier(id) {
    if (confirm('Apakah Anda yakin ingin menghapus tier ini?')) {
        tierForm.delete(`/loyalty/tiers/${id}`);
    }
}

function openCreateReward() {
    isEditingReward.value = false;
    editingRewardId.value = null;
    rewardForm.reset();
    rewardForm.clearErrors();
    showRewardModal.value = true;
}

function openEditReward(reward) {
    isEditingReward.value = true;
    editingRewardId.value = reward.id;
    rewardForm.clearErrors();
    rewardForm.reward_code = reward.reward_code;
    rewardForm.reward_name = reward.reward_name;
    rewardForm.reward_type = reward.reward_type;
    rewardForm.point_required = reward.point_required;
    rewardForm.voucher_amount = reward.voucher_amount;
    rewardForm.discount_percentage = reward.discount_percentage;
    rewardForm.product_id = reward.product_id || '';
    rewardForm.stock_qty = reward.stock_qty;
    rewardForm.description = reward.description || '';
    rewardForm.is_active = reward.is_active;
    showRewardModal.value = true;
}

function submitReward() {
    if (isEditingReward.value) {
        rewardForm.put(`/loyalty/rewards/${editingRewardId.value}`, {
            onSuccess: () => showRewardModal.value = false
        });
    } else {
        rewardForm.post('/loyalty/rewards', {
            onSuccess: () => showRewardModal.value = false
        });
    }
}

function deleteReward(id) {
    if (confirm('Apakah Anda yakin ingin menghapus reward ini dari katalog?')) {
        rewardForm.delete(`/loyalty/rewards/${id}`);
    }
}

function approveClaim(id) {
    if (confirm('Setujui penukaran poin ini?')) {
        router.post(`/loyalty/redemptions/${id}/approve`);
    }
}

function openRejectClaim(id) {
    rejectingRedemptionId.value = id;
    rejectForm.reset();
    rejectForm.clearErrors();
    showRejectModal.value = true;
}

function submitRejectClaim() {
    rejectForm.post(`/loyalty/redemptions/${rejectingRedemptionId.value}/reject`, {
        onSuccess: () => {
            showRejectModal.value = false;
            rejectingRedemptionId.value = null;
        }
    });
}

function submitConfig() {
    configForm.post('/loyalty/configuration');
}

const accountColumns = [
    { key: 'no', label: 'No' },
    { key: 'customer', label: 'Customer Name' },
    { key: 'account_number', label: 'Account Number' },
    { key: 'current_points', label: 'Current Points', align: 'right' },
    { key: 'tier', label: 'Loyalty Tier' },
    { key: 'status', label: 'Status', align: 'center' },
];

const redemptionColumns = [
    { key: 'redemption_number', label: 'Redemption No' },
    { key: 'customer', label: 'Customer' },
    { key: 'reward_name', label: 'Reward' },
    { key: 'points_used', label: 'Points Used', align: 'right' },
    { key: 'status', label: 'Status', align: 'center' },
    { key: 'actions', label: 'Actions', align: 'center' },
];

const rewardColumns = [
    { key: 'reward_code', label: 'Code' },
    { key: 'reward_name', label: 'Name' },
    { key: 'reward_type', label: 'Type' },
    { key: 'point_required', label: 'Points Required', align: 'right' },
    { key: 'stock_qty', label: 'Stock', align: 'right' },
    { key: 'redeemed_qty', label: 'Redeemed', align: 'right' },
    { key: 'is_active', label: 'Status', align: 'center' },
    { key: 'actions', label: 'Actions', align: 'center' },
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
                    Kelola akun poin pelanggan, riwayat penukaran poin, katalog hadiah, dan tingkatan keanggotaan (Membership Tiers).
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
                <BaseButton v-if="activeTab === 'rewards'" @click="openCreateReward">
                    ➕ New Reward
                </BaseButton>
                <BaseButton v-if="activeTab === 'tiers'" @click="openCreateTier">
                    ➕ New Tier
                </BaseButton>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex space-x-1 mb-6 border-b border-border-soft overflow-x-auto">
            <button
                @click="activeTab = 'accounts'"
                :class="activeTab === 'accounts' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-3 px-6 font-medium transition-colors duration-200 cursor-pointer text-sm whitespace-nowrap"
            >
                Loyalty Accounts 👥
            </button>
            <button
                @click="activeTab = 'transactions'"
                :class="activeTab === 'transactions' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-3 px-6 font-medium transition-colors duration-200 cursor-pointer text-sm whitespace-nowrap"
            >
                Points Transactions 🔄
            </button>
            <button
                @click="activeTab = 'redemptions'"
                :class="activeTab === 'redemptions' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-3 px-6 font-medium transition-colors duration-200 cursor-pointer text-sm whitespace-nowrap"
            >
                Redemptions 🎁
            </button>
            <button
                @click="activeTab = 'rewards'"
                :class="activeTab === 'rewards' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-3 px-6 font-medium transition-colors duration-200 cursor-pointer text-sm whitespace-nowrap"
            >
                Reward Catalog 📋
            </button>
            <button
                @click="activeTab = 'tiers'"
                :class="activeTab === 'tiers' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-3 px-6 font-medium transition-colors duration-200 cursor-pointer text-sm whitespace-nowrap"
            >
                Membership Tiers 👑
            </button>
            <button
                @click="activeTab = 'configuration'"
                :class="activeTab === 'configuration' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-3 px-6 font-medium transition-colors duration-200 cursor-pointer text-sm whitespace-nowrap"
            >
                Configuration ⚙️
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
                    <StatusBadge :status="row.is_active ? 'ACTIVE' : 'INACTIVE'" size="sm" variant="soft" />
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
            <div v-if="loyaltyTransactions.length === 0" class="text-center text-ink-secondary py-xl">
                Belum ada transaksi poin.
            </div>
        </div>

        <!-- Redemptions Tab -->
        <div v-if="activeTab === 'redemptions'">
            <DataTable :columns="redemptionColumns" :rows="loyaltyRedemptions">
                <template #cell-customer="{ row }">
                    <span class="font-semibold text-ink-primary">{{ row.account?.customer?.customer_name }}</span>
                </template>
                <template #cell-reward_name="{ row }">
                    <span>{{ row.reward?.reward_name }}</span>
                </template>
                <template #cell-points_used="{ value }">
                    <span class="font-mono text-ink-primary font-semibold">{{ value }} pts</span>
                </template>
                <template #cell-status="{ value }">
                    <StatusBadge :status="value" size="sm" variant="soft" />
                </template>
                <template #cell-actions="{ row }">
                    <div class="flex gap-md justify-center" v-if="row.status === 'PENDING'">
                        <button @click="approveClaim(row.id)" class="text-brand hover:underline text-sm font-semibold">Approve</button>
                        <button @click="openRejectClaim(row.id)" class="text-semantic-danger hover:underline text-sm font-semibold">Reject</button>
                    </div>
                    <span v-else class="text-ink-muted text-xs">
                        {{ row.status === 'REJECTED' ? `Rejected: ${row.rejection_notes || '-'}` : 'Processed' }}
                    </span>
                </template>
            </DataTable>
        </div>

        <!-- Reward Catalog Tab -->
        <div v-if="activeTab === 'rewards'">
            <DataTable :columns="rewardColumns" :rows="rewards">
                <template #cell-reward_type="{ value }">
                    <span class="px-2 py-0.5 bg-surface-subtle text-ink-secondary rounded text-xs font-mono font-semibold">
                        {{ value }}
                    </span>
                </template>
                <template #cell-point_required="{ value }">
                    <span class="font-mono text-ink-primary font-semibold">{{ value }} pts</span>
                </template>
                <template #cell-is_active="{ value }">
                    <StatusBadge :status="value ? 'ACTIVE' : 'INACTIVE'" size="sm" variant="soft" />
                </template>
                <template #cell-actions="{ row }">
                    <div class="flex gap-md justify-center">
                        <button @click="openEditReward(row)" class="text-brand hover:underline text-sm font-semibold">Edit</button>
                        <button @click="deleteReward(row.id)" class="text-semantic-danger hover:underline text-sm font-semibold">Delete</button>
                    </div>
                </template>
            </DataTable>
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

                    <div class="mt-xl flex gap-md justify-end pt-md border-t border-border-soft">
                        <button @click="openEditTier(tier)" class="text-brand hover:underline text-sm font-semibold">Edit</button>
                        <button @click="deleteTier(tier.id)" class="text-semantic-danger hover:underline text-sm font-semibold">Delete</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configuration Tab -->
        <div v-if="activeTab === 'configuration'" class="bg-surface-card border border-border-soft p-xl rounded-2xl shadow-soft max-w-2xl">
            <h3 class="text-lg font-bold text-ink-primary mb-xl">Global Loyalty Configurations</h3>
            <form @submit.prevent="submitConfig" class="space-y-md">
                <div class="grid grid-cols-2 gap-md">
                    <FormInput type="number" label="Point Expiry Months" v-model="configForm.point_expiry_months" required />
                    <FormInput type="number" label="Minimum Redeem Points" v-model="configForm.minimum_redeem_points" required />
                </div>
                <div class="grid grid-cols-2 gap-md">
                    <FormInput type="number" label="Point Value (Rp per Point)" v-model="configForm.point_value" required />
                    <FormInput type="number" step="0.0001" label="Earn Rate (Spend Rp for 1 Point)" v-model="configForm.earn_rate" required />
                </div>
                <div class="flex gap-xl py-md">
                    <label class="flex items-center gap-md text-sm font-medium text-ink-primary">
                        <input type="checkbox" v-model="configForm.allow_negative_point" class="rounded border-border-soft text-brand focus:ring-brand" />
                        Allow Negative Point Balance
                    </label>
                    <label class="flex items-center gap-md text-sm font-medium text-ink-primary">
                        <input type="checkbox" v-model="configForm.is_enabled" class="rounded border-border-soft text-brand focus:ring-brand" />
                        Loyalty Program Enabled
                    </label>
                </div>
                <div class="space-y-sm">
                    <label class="text-sm font-medium text-ink-primary">Terms & Conditions</label>
                    <textarea v-model="configForm.terms_and_conditions" rows="4" class="w-full px-base py-md rounded-md border border-border-soft bg-surface-card text-ink-primary placeholder-ink-muted focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent"></textarea>
                </div>
                <div class="flex justify-end pt-md border-t border-border-soft">
                    <BaseButton type="submit" :loading="configForm.processing">Save Configurations</BaseButton>
                </div>
            </form>
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
                    <option v-for="r in rewards" :key="r.id" :value="r.id">{{ r.reward_name }} ({{ r.point_required }} pts)</option>
                </FormSelect>
                
                <div class="flex justify-end gap-md pt-md border-t border-border-soft">
                    <BaseButton type="button" variant="secondary" @click="showRedeemModal = false">Cancel</BaseButton>
                    <BaseButton type="submit" :loading="redeemForm.processing">Process Redemption</BaseButton>
                </div>
            </form>
        </BaseModal>

        <!-- New Tier Modal -->
        <BaseModal :show="showTierModal" @close="showTierModal = false" :title="isEditingTier ? 'Edit Membership Tier' : 'Create Membership Tier'">
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

        <!-- New/Edit Reward Modal -->
        <BaseModal :show="showRewardModal" @close="showRewardModal = false" :title="isEditingReward ? 'Edit Reward Catalog Item' : 'Create Reward Catalog Item'">
            <form @submit.prevent="submitReward" class="space-y-md">
                <div class="grid grid-cols-2 gap-md">
                    <FormInput label="Reward Code" v-model="rewardForm.reward_code" :error="rewardForm.errors.reward_code" required placeholder="RWD-001" />
                    <FormInput label="Reward Name" v-model="rewardForm.reward_name" :error="rewardForm.errors.reward_name" required placeholder="Voucher 50K" />
                </div>
                <div class="grid grid-cols-3 gap-md">
                    <FormSelect label="Reward Type" v-model="rewardForm.reward_type" required>
                        <option value="VOUCHER">Voucher</option>
                        <option value="PRODUCT">Free Product</option>
                        <option value="LUCKY_DRAW">Lucky Draw</option>
                    </FormSelect>
                    <FormInput type="number" label="Points Required" v-model="rewardForm.point_required" :error="rewardForm.errors.point_required" required />
                    <FormInput type="number" label="Stock Quantity" v-model="rewardForm.stock_qty" :error="rewardForm.errors.stock_qty" required />
                </div>
                <div class="grid grid-cols-2 gap-md" v-if="rewardForm.reward_type === 'VOUCHER'">
                    <FormInput type="number" label="Voucher Amount (Rp)" v-model="rewardForm.voucher_amount" :error="rewardForm.errors.voucher_amount" />
                    <FormInput type="number" step="0.1" label="Discount % (Optional)" v-model="rewardForm.discount_percentage" :error="rewardForm.errors.discount_percentage" />
                </div>
                <div v-if="rewardForm.reward_type === 'PRODUCT'">
                    <FormSelect label="Product to Claim" v-model="rewardForm.product_id" :error="rewardForm.errors.product_id">
                        <option value="">-- Select Product --</option>
                        <option v-for="p in products" :key="p.id" :value="p.id">{{ p.product_name }} ({{ p.product_code }})</option>
                    </FormSelect>
                </div>
                <div class="space-y-sm">
                    <label class="text-sm font-medium text-ink-primary">Description</label>
                    <textarea v-model="rewardForm.description" rows="3" class="w-full px-base py-md rounded-md border border-border-soft bg-surface-card text-ink-primary placeholder-ink-muted focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent"></textarea>
                </div>
                <div class="flex items-center gap-md">
                    <input type="checkbox" v-model="rewardForm.is_active" id="reward_active" class="rounded border-border-soft text-brand focus:ring-brand" />
                    <label for="reward_active" class="text-sm font-medium text-ink-primary">Active and Redeemable</label>
                </div>
                <div class="flex justify-end gap-md pt-md border-t border-border-soft">
                    <BaseButton type="button" variant="secondary" @click="showRewardModal = false">Cancel</BaseButton>
                    <BaseButton type="submit" :loading="rewardForm.processing">{{ isEditingReward ? 'Update Reward' : 'Create Reward' }}</BaseButton>
                </div>
            </form>
        </BaseModal>

        <!-- Reject Claim Modal -->
        <BaseModal :show="showRejectModal" @close="showRejectModal = false" title="Reject Point Redemption Claim">
            <form @submit.prevent="submitRejectClaim" class="space-y-md">
                <FormInput label="Reason for Rejection" v-model="rejectForm.rejection_notes" :error="rejectForm.errors.rejection_notes" required placeholder="Poin tidak mencukupi, akun ditangguhkan..." />
                <div class="flex justify-end gap-md pt-md border-t border-border-soft">
                    <BaseButton type="button" variant="secondary" @click="showRejectModal = false">Cancel</BaseButton>
                    <BaseButton type="submit" variant="danger" :loading="rejectForm.processing">Reject Claim</BaseButton>
                </div>
            </form>
        </BaseModal>
    </DashboardLayout>
</template>
