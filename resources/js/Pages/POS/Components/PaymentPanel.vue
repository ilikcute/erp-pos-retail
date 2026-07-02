<script setup>
import { computed } from "vue";
import { formatPrice } from "@/Utils/formatPrice";

const props = defineProps({
    // Payment state
    paymentMode: String, // 'single' | 'split'
    paymentMethod: String,
    paymentOptions: Array,
    payLater: Boolean,
    dueDate: String,
    cashInput: String,
    selectedBankAccount: Object,
    bankAccounts: Array,
    // Split payment
    paymentSplits: Array,
    splitTotal: Number,
    splitRemaining: Number,
    isSplitComplete: Boolean,
    isCashPayment: Boolean,
    // Pricing
    payable: Number,
    promoDiscount: Number,
    voucherDiscount: Number,
    loyaltyDiscount: Number,
    taxTotal: Number,
    baseSubtotal: Number,
    appliedGroups: Array,
    // Customer
    selectedCustomer: Object,
    redeemPointsInput: String,
    selectedVoucherId: String,
    eligibleVouchers: Array,
    availablePoints: Number,
    // Validation
    isLoadingPricing: Boolean,
});

const emit = defineEmits([
    "update:paymentMode",
    "update:paymentMethod",
    "update:payLater",
    "update:dueDate",
    "update:cashInput",
    "update:selectedBankAccount",
    "update:redeemPointsInput",
    "update:selectedVoucherId",
    "add-split",
    "update-split-amount",
    "remove-split",
    "switch-mode",
]);

const change = computed(() => {
    if (!props.isCashPayment || props.payLater) return 0;
    return Math.max(0, Number(props.cashInput) - props.payable);
});

// ✅ Helper untuk konversi poin ke Rupiah (point_value = 100)
const POINT_VALUE = 100;
const redeemRupiahValue = computed(() => {
    return Number(props.redeemPointsInput || 0) * POINT_VALUE;
});
</script>

<template>
    <div class="p-lg space-y-md overflow-y-auto">
        <!-- Mode Toggle: Single vs Split -->
        <div class="flex gap-2 p-1 rounded-xl bg-surface-muted">
            <button
                @click="emit('switch-mode', 'single')"
                :class="[
                    'flex-1 py-2 rounded-lg text-xs font-semibold transition',
                    paymentMode === 'single'
                        ? 'bg-brand text-white shadow-sm'
                        : 'text-ink-secondary',
                ]"
            >
                Pembayaran Tunggal
            </button>
            <button
                @click="emit('switch-mode', 'split')"
                :class="[
                    'flex-1 py-2 rounded-lg text-xs font-semibold transition',
                    paymentMode === 'split'
                        ? 'bg-brand text-white shadow-sm'
                        : 'text-ink-secondary',
                ]"
                :disabled="payLater"
            >
                Split Payment
            </button>
        </div>

        <!-- Pay Later Toggle -->
        <div
            class="flex items-center justify-between p-3 rounded-xl border border-border-soft bg-surface-muted"
        >
            <div>
                <p class="text-sm font-semibold text-ink-primary">
                    Bayar Belakangan
                </p>
                <p class="text-xs text-ink-muted">Catat sebagai piutang</p>
            </div>
            <label class="inline-flex items-center cursor-pointer">
                <input
                    type="checkbox"
                    class="sr-only"
                    :checked="payLater"
                    @change="emit('update:payLater', $event.target.checked)"
                />
                <span
                    :class="[
                        'w-11 h-6 flex items-center rounded-full p-1 transition',
                        payLater ? 'bg-brand' : 'bg-border-soft',
                    ]"
                >
                    <span
                        :class="[
                            'bg-white w-4 h-4 rounded-full shadow transform transition',
                            payLater ? 'translate-x-5' : '',
                        ]"
                    />
                </span>
            </label>
        </div>

        <div v-if="payLater">
            <label class="block text-xs font-medium text-ink-secondary mb-2">
                Tanggal Jatuh Tempo
            </label>
            <input
                type="date"
                :value="dueDate"
                @input="emit('update:dueDate', $event.target.value)"
                class="w-full h-11 px-3 rounded-xl border border-border-soft bg-surface-card text-sm focus:ring-2 focus:ring-brand"
            />
        </div>

        <!-- SINGLE PAYMENT MODE -->
        <template v-if="paymentMode === 'single' && !payLater">
            <div>
                <label
                    class="block text-xs font-medium text-ink-secondary mb-2"
                >
                    Metode Pembayaran
                </label>
                <div class="grid grid-cols-2 gap-2">
                    <button
                        v-for="method in paymentOptions"
                        :key="method.value"
                        @click="emit('update:paymentMethod', method.value)"
                        :class="[
                            'p-3 rounded-xl border-2 transition-all flex items-center gap-2',
                            paymentMethod === method.value
                                ? 'border-brand bg-brand-soft'
                                : 'border-border-soft hover:border-brand-border',
                        ]"
                    >
                        <div
                            :class="[
                                'w-8 h-8 rounded-lg flex items-center justify-center',
                                paymentMethod === method.value
                                    ? 'bg-brand text-white'
                                    : 'bg-surface-muted text-ink-muted',
                            ]"
                        >
                            <span class="text-sm">
                                {{
                                    method.value === "cash"
                                        ? "💵"
                                        : method.value === "bank_transfer"
                                          ? "🏦"
                                          : "💳"
                                }}
                            </span>
                        </div>
                        <p
                            :class="[
                                'text-sm font-semibold',
                                paymentMethod === method.value
                                    ? 'text-brand'
                                    : 'text-ink-primary',
                            ]"
                        >
                            {{ method.label }}
                        </p>
                    </button>
                </div>
            </div>

            <!-- Bank Selector -->
            <div
                v-if="paymentMethod === 'bank_transfer' && bankAccounts.length"
            >
                <label
                    class="block text-xs font-medium text-ink-secondary mb-2"
                >
                    Rekening Tujuan
                </label>
                <div class="grid grid-cols-1 gap-2">
                    <button
                        v-for="bank in bankAccounts"
                        :key="bank.id"
                        @click="emit('update:selectedBankAccount', bank)"
                        :class="[
                            'p-3 rounded-xl border-2 transition-colors flex items-center gap-3 text-left',
                            selectedBankAccount?.id === bank.id
                                ? 'border-brand bg-brand-soft'
                                : 'border-border-soft',
                        ]"
                    >
                        <div
                            class="w-10 h-10 rounded-lg bg-surface-card border border-border-soft flex items-center justify-center overflow-hidden"
                        >
                            <img
                                v-if="bank.logo_url"
                                :src="bank.logo_url"
                                class="max-w-full max-h-full object-contain"
                            />
                            <span v-else>🏦</span>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs font-semibold text-ink-primary">
                                {{ bank.bank_name }}
                            </p>
                            <p class="text-xs text-ink-muted">
                                {{ bank.account_number }}
                            </p>
                            <p class="text-[11px] text-ink-muted">
                                a.n. {{ bank.account_name }}
                            </p>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Cash Input -->
            <div v-if="isCashPayment">
                <label
                    class="block text-xs font-medium text-ink-secondary mb-2"
                >
                    Jumlah Bayar
                </label>
                <div class="relative">
                    <span
                        class="absolute left-3 top-1/2 -translate-y-1/2 text-ink-muted text-sm"
                    >
                        Rp
                    </span>
                    <input
                        type="text"
                        inputmode="numeric"
                        :value="cashInput"
                        @input="
                            emit(
                                'update:cashInput',
                                $event.target.value.replace(/[^\d]/g, ''),
                            )
                        "
                        class="w-full h-12 pl-10 pr-4 rounded-xl border border-border-soft bg-surface-card text-base font-semibold focus:ring-2 focus:ring-brand"
                    />
                </div>
                <div class="grid grid-cols-4 gap-2 mt-2">
                    <button
                        v-for="amt in [10000, 20000, 50000, 100000]"
                        :key="amt"
                        @click="emit('update:cashInput', String(amt))"
                        :class="[
                            'py-2 px-1 rounded-lg text-xs font-semibold transition',
                            Number(cashInput) === amt
                                ? 'bg-brand text-white'
                                : 'bg-surface-muted text-ink-secondary hover:bg-border-soft',
                        ]"
                    >
                        {{ formatPrice(amt) }}
                    </button>
                </div>
            </div>
        </template>

        <!-- SPLIT PAYMENT MODE -->
        <template v-if="paymentMode === 'split' && !payLater">
            <div class="space-y-2">
                <div class="grid grid-cols-4 gap-1">
                    <button
                        v-for="m in ['cash', 'qris', 'debit', 'bank_transfer']"
                        :key="m"
                        @click="emit('add-split', m)"
                        :disabled="splitRemaining <= 0"
                        class="py-2 rounded-lg text-[11px] font-semibold bg-surface-muted hover:bg-brand-soft hover:text-brand disabled:opacity-40 transition"
                    >
                        + {{ m.toUpperCase() }}
                    </button>
                </div>

                <div
                    v-for="(split, idx) in paymentSplits"
                    :key="idx"
                    class="flex items-center gap-2 p-2 rounded-lg bg-surface-muted"
                >
                    <span
                        class="text-xs font-bold uppercase w-24 text-ink-secondary"
                    >
                        {{ split.method }}
                    </span>
                    <input
                        type="text"
                        inputmode="numeric"
                        :value="split.amount"
                        @input="
                            emit(
                                'update-split-amount',
                                idx,
                                $event.target.value.replace(/[^\d]/g, ''),
                            )
                        "
                        class="flex-1 h-9 px-3 rounded-lg border border-border-soft bg-surface-card text-sm text-right font-semibold"
                        placeholder="0"
                    />
                    <button
                        @click="emit('remove-split', idx)"
                        class="text-semantic-danger text-lg w-7 h-7"
                    >
                        ×
                    </button>
                </div>

                <div
                    class="flex justify-between pt-2 border-t border-border-soft"
                >
                    <span class="text-xs font-semibold text-ink-muted">
                        Total Split
                    </span>
                    <span class="text-sm font-bold text-ink-primary">
                        {{ formatPrice(splitTotal) }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs font-semibold text-ink-muted">
                        Sisa
                    </span>
                    <span
                        :class="[
                            'text-sm font-bold',
                            splitRemaining === 0
                                ? 'text-accent-mint'
                                : 'text-semantic-danger',
                        ]"
                    >
                        {{ formatPrice(splitRemaining) }}
                    </span>
                </div>
            </div>
        </template>

        <!-- ═══════════════════════════════════════════════════════════ -->
        <!-- ✅ CUSTOMER LOYALTY (FIXED: template tanpa class) -->
        <!-- ═══════════════════════════════════════════════════════════ -->
        <template
            v-if="
                selectedCustomer?.is_loyalty_member ||
                selectedCustomer?.loyalty_account
            "
        >
            <div
                class="rounded-xl border border-brand-border bg-brand-soft p-3 space-y-3"
            >
                <!-- Loyalty Info Header -->
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-brand">
                            ⭐
                            {{
                                selectedCustomer.loyalty_account
                                    ?.membership_tier || "Member"
                            }}
                        </p>
                        <p class="text-xs text-ink-muted">
                            Saldo:
                            <span class="font-bold text-brand">
                                {{
                                    selectedCustomer.loyalty_account
                                        ?.current_balance || 0
                                }}
                                poin
                            </span>
                            <span class="ml-2">
                                (≈
                                {{
                                    formatPrice(
                                        (selectedCustomer.loyalty_account
                                            ?.current_balance || 0) *
                                            POINT_VALUE,
                                    )
                                }})
                            </span>
                        </p>
                    </div>
                </div>

                <!-- Redeem Points Input -->
                <div>
                    <label
                        class="block text-xs font-medium text-ink-secondary mb-2"
                    >
                        Redeem / Tukar Point Loyalty
                    </label>
                    <!-- ✅ FIXED: pakai :value + @input, BUKAN v-model -->
                    <input
                        type="number"
                        :value="redeemPointsInput"
                        @input="
                            emit(
                                'update:redeemPointsInput',
                                $event.target.value.replace(/[^\d]/g, ''),
                            )
                        "
                        :max="
                            selectedCustomer.loyalty_account?.current_balance ||
                            0
                        "
                        :min="0"
                        placeholder="0"
                        class="w-full h-10 px-4 rounded-xl border border-border-soft bg-surface-card text-sm focus:ring-2 focus:ring-brand"
                    />
                    <p
                        v-if="Number(redeemPointsInput) > 0"
                        class="text-xs text-accent-mint mt-1"
                    >
                        Potongan: {{ formatPrice(redeemRupiahValue) }}
                    </p>
                </div>

                <!-- Voucher Selector -->
                <div v-if="eligibleVouchers?.length">
                    <label
                        class="block text-xs font-medium text-ink-secondary mb-2"
                    >
                        Voucher
                    </label>
                    <select
                        :value="selectedVoucherId"
                        @change="
                            emit(
                                'update:selectedVoucherId',
                                $event.target.value,
                            )
                        "
                        class="w-full h-10 px-4 rounded-xl border border-border-soft bg-surface-card text-sm focus:ring-2 focus:ring-brand"
                    >
                        <option value="">Tanpa voucher</option>
                        <option
                            v-for="v in eligibleVouchers"
                            :key="v.id"
                            :value="v.id"
                        >
                            {{ v.code }} - {{ v.name }}
                        </option>
                    </select>
                </div>
            </div>
        </template>

        <!-- Change Display -->
        <div
            v-if="isCashPayment && !payLater && change > 0"
            class="rounded-xl bg-accent-mint-soft p-3 text-center"
        >
            <p class="text-xs text-accent-mint font-semibold">Kembalian</p>
            <p class="text-xl font-bold text-accent-mint">
                {{ formatPrice(change) }}
            </p>
        </div>
    </div>
</template>
