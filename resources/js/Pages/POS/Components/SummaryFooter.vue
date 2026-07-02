<script setup>
import { computed } from "vue";
import { formatPrice } from "@/Utils/formatPrice";

// ═══════════════════════════════════════════════════════════
// PROPS
// ═══════════════════════════════════════════════════════════
const props = defineProps({
    // Cart & Customer
    carts: { type: Array, default: () => [] },
    selectedCustomer: { type: Object, default: null },

    // Pricing breakdown
    baseSubtotal: { type: Number, default: 0 },
    promoDiscount: { type: Number, default: 0 }, // ✅ FIXED: sekarang di props
    voucherDiscount: { type: Number, default: 0 },
    loyaltyDiscount: { type: Number, default: 0 },
    taxTotal: { type: Number, default: 0 },
    payable: { type: Number, default: 0 },

    // Payment state
    cashInput: { type: [Number, String], default: 0 },
    isCashPayment: { type: Boolean, default: false },
    payLater: { type: Boolean, default: false },
    paymentMode: { type: String, default: "single" },
    isSplitComplete: { type: Boolean, default: false },

    // Loading state
    isLoadingPricing: { type: Boolean, default: false },
    isSubmitting: { type: Boolean, default: false },

    // Promotions
    appliedPromotions: { type: Array, default: () => [] },
});

const emit = defineEmits(["submit"]);

// ═══════════════════════════════════════════════════════════
// COMPUTED
// ═══════════════════════════════════════════════════════════
const itemCount = computed(() =>
    props.carts.reduce((s, c) => s + Number(c.qty ?? 0), 0),
);

const change = computed(() => {
    if (!props.isCashPayment || props.payLater) return 0;
    const paid = Number(props.cashInput) || 0;
    return Math.max(0, paid - props.payable);
});

// ✅ NEW: Apakah cash kurang dari payable?
const isCashShort = computed(() => {
    if (!props.isCashPayment || props.payLater) return false;
    return (Number(props.cashInput) || 0) < props.payable;
});

const cashShortage = computed(() => {
    if (!isCashShort.value) return 0;
    return props.payable - (Number(props.cashInput) || 0);
});

// ✅ NEW: Apakah ada detail breakdown yang perlu ditampilkan?
const hasBreakdown = computed(
    () =>
        props.promoDiscount > 0 ||
        props.voucherDiscount > 0 ||
        props.loyaltyDiscount > 0 ||
        props.taxTotal > 0 ||
        props.appliedPromotions.length > 0,
);

const canSubmit = computed(() => {
    if (props.isSubmitting || props.isLoadingPricing) return false;
    if (!props.carts.length) return false;
    if (!props.selectedCustomer?.id) return false;
    return true;
});

const submitLabel = computed(() => {
    if (props.isSubmitting) return "Memproses…";
    if (props.isLoadingPricing) return "Menghitung…";
    if (!props.carts.length) return "Keranjang Kosong";
    if (!props.selectedCustomer?.id) return "Pilih Pelanggan";
    return "Proses Pembayaran (F2)";
});

function onSubmit() {
    if (canSubmit.value) emit("submit");
}
</script>

<template>
    <div class="border-t border-border-soft bg-surface-card flex-shrink-0">
        <!-- ═══════════════════════════════════════════════════════════ -->
        <!-- SCROLLABLE BREAKDOWN AREA -->
        <!-- ═══════════════════════════════════════════════════════════ -->
        <div class="p-4 space-y-3 max-h-[40vh] overflow-y-auto">
            <!-- Header: Item count + Customer -->
            <div class="flex items-center justify-between text-sm">
                <span class="text-ink-secondary flex items-center gap-1.5">
                    <span class="font-semibold">{{ itemCount }}</span> item
                </span>
                <span
                    v-if="selectedCustomer"
                    class="text-ink-muted truncate max-w-[200px]"
                >
                    👤 {{ selectedCustomer.name }}
                </span>
            </div>

            <!-- ═══════════════════════════════════════════════════════════ -->
            <!-- BREAKDOWN (Selalu Tampil) -->
            <!-- ═══════════════════════════════════════════════════════════ -->
            <div class="space-y-2 text-sm">
                <!-- Subtotal Dasar -->
                <div class="flex justify-between text-ink-secondary">
                    <span>Subtotal</span>
                    <span class="font-medium font-mono tabular-nums">
                        {{ formatPrice(baseSubtotal) }}
                    </span>
                </div>

                <!-- Diskon Promo -->
                <div class="flex justify-between text-accent-mint">
                    <span>Diskon Promo</span>
                    <span class="font-medium font-mono tabular-nums">
                        -{{ formatPrice(promoDiscount || 0) }}
                    </span>
                </div>

                <!-- ✅ Applied Promotions (detail per promo jika ada) -->
                <div v-if="appliedPromotions.length > 0" class="space-y-1 pl-3 border-l-2 border-accent-mint/30 my-xs">
                    <div
                        v-for="promo in appliedPromotions"
                        :key="promo.promotion_id"
                        class="flex justify-between text-xs text-accent-mint/80"
                    >
                        <span>└ {{ promo.promotion_name }}</span>
                        <span class="font-semibold tabular-nums">-{{ formatPrice(promo.discount_amount) }}</span>
                    </div>
                </div>

                <!-- Voucher -->
                <div class="flex justify-between text-brand">
                    <span>Voucher</span>
                    <span class="font-medium font-mono tabular-nums">
                        -{{ formatPrice(voucherDiscount || 0) }}
                    </span>
                </div>

                <!-- Loyalty Points -->
                <div class="flex justify-between text-brand">
                    <span>Redeem Poin</span>
                    <span class="font-medium font-mono tabular-nums">
                        -{{ formatPrice(loyaltyDiscount || 0) }}
                    </span>
                </div>

                <!-- Pajak -->
                <div class="flex justify-between text-ink-secondary">
                    <span>PPN (11%)</span>
                    <span class="font-medium font-mono tabular-nums">
                        +{{ formatPrice(taxTotal || 0) }}
                    </span>
                </div>
            </div>

            <!-- ═══════════════════════════════════════════════════════════ -->
            <!-- GRAND TOTAL (selalu tampil) -->
            <!-- ═══════════════════════════════════════════════════════════ -->
            <div
                class="flex items-end justify-between pt-2 border-t border-border-soft"
            >
                <span class="text-sm font-semibold text-ink-primary">
                    Total Bayar
                </span>
                <div class="text-right">
                    <span
                        class="text-2xl font-extrabold text-brand tabular-nums"
                    >
                        {{ formatPrice(payable) }}
                    </span>
                </div>
            </div>

            <!-- ═══════════════════════════════════════════════════════════ -->
            <!-- KEMBALIAN (hanya cash, bukan pay later) -->
            <!-- ═══════════════════════════════════════════════════════════ -->
            <div
                v-if="isCashPayment && !payLater && change > 0"
                class="flex items-center justify-between p-2.5 rounded-lg bg-accent-mint-soft"
            >
                <span class="text-sm font-semibold text-accent-mint">
                    💵 Kembalian
                </span>
                <span class="text-lg font-bold text-accent-mint tabular-nums">
                    {{ formatPrice(change) }}
                </span>
            </div>

            <!-- ✅ NEW: Warning jika cash kurang -->
            <div
                v-if="isCashShort"
                class="flex items-center justify-between p-2.5 rounded-lg bg-semantic-danger-soft"
            >
                <span class="text-sm font-semibold text-semantic-danger">
                    ⚠️ Kurang Bayar
                </span>
                <span
                    class="text-lg font-bold text-semantic-danger tabular-nums"
                >
                    {{ formatPrice(cashShortage) }}
                </span>
            </div>

            <!-- Pay Later indicator -->
            <div
                v-if="payLater"
                class="flex items-center gap-2 p-2.5 rounded-lg bg-accent-sunny-soft"
            >
                <span class="text-sm">📝</span>
                <span class="text-xs font-semibold text-accent-sunny">
                    Pembayaran dicatat sebagai piutang
                </span>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════ -->
        <!-- SUBMIT BUTTON (fixed di bawah) -->
        <!-- ═══════════════════════════════════════════════════════════ -->
        <div class="p-4 pt-0">
            <button
                type="button"
                :disabled="!canSubmit"
                @click="onSubmit"
                class="w-full rounded-pill py-3.5 text-base font-bold text-white transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                :class="
                    canSubmit
                        ? 'bg-brand-gradient hover:opacity-90 shadow-lg shadow-brand/20'
                        : 'bg-slate-400'
                "
            >
                <!-- ✅ NEW: Spinner saat loading -->
                <svg
                    v-if="isSubmitting || isLoadingPricing"
                    class="animate-spin h-5 w-5 text-white"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                >
                    <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                    />
                    <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                    />
                </svg>

                <span v-else-if="!carts.length">🛒</span>
                <span v-else-if="!selectedCustomer?.id">👤</span>
                <span v-else-if="payLater">📝</span>
                <span v-else>💳</span>

                <span>{{ submitLabel }}</span>
            </button>

            <!-- Keyboard shortcut hint -->
            <p class="text-center text-[10px] text-ink-muted mt-2">
                Tekan
                <kbd
                    class="px-1.5 py-0.5 bg-surface-muted border border-border-soft rounded text-[10px] font-mono"
                    >F2</kbd
                >
                untuk submit cepat
            </p>
        </div>
    </div>
</template>
