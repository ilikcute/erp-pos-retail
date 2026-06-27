<script setup>
const props = defineProps({
    carts: { type: Array, default: () => [] },
    selectedCustomer: { type: Object, default: null },
    payable: { type: Number, default: 0 },
    cashInput: { type: [Number, String], default: 0 },
    isCashPayment: { type: Boolean, default: false },
    payLater: { type: Boolean, default: false },
    paymentMode: { type: String, default: 'single' },
    isSplitComplete: { type: Boolean, default: false },
    isLoadingPricing: { type: Boolean, default: false },
    isSubmitting: { type: Boolean, default: false },
});

const emit = defineEmits(['submit']);

const itemCount = computed(() =>
    props.carts.reduce((s, c) => s + Number(c.qty ?? c.quantity ?? 0), 0)
);

const change = computed(() => {
    const paid = Number(props.cashInput) || 0;
    return Math.max(0, paid - props.payable);
});

const canSubmit = computed(() => {
    if (props.isSubmitting || props.isLoadingPricing) return false;
    if (!props.carts.length) return false;
    if (props.payLater) return true;
    if (props.paymentMode === 'split') return props.isSplitComplete;
    if (props.isCashPayment) return (Number(props.cashInput) || 0) >= props.payable;
    return true;
});

function onSubmit() {
    if (canSubmit.value) emit('submit');
}
</script>

<template>
    <div class="border-t border-border-soft bg-surface-card p-4 space-y-3">
        <div class="flex items-center justify-between text-sm text-ink-secondary">
            <span>{{ itemCount }} item</span>
            <span v-if="selectedCustomer">{{ selectedCustomer.name }}</span>
        </div>

        <div class="flex items-end justify-between">
            <span class="text-sm font-semibold text-ink-secondary">Total Bayar</span>
            <span class="text-2xl font-extrabold text-brand">{{ formatPrice(payable) }}</span>
        </div>

        <div v-if="isCashPayment && !payLater" class="flex items-center justify-between text-sm">
            <span class="text-ink-secondary">Kembalian</span>
            <span class="font-bold text-emerald-600">{{ formatPrice(change) }}</span>
        </div>

        <button
            type="button"
            :disabled="!canSubmit"
            @click="onSubmit"
            class="w-full rounded-pill py-3 text-base font-bold text-white transition disabled:opacity-50 disabled:cursor-not-allowed"
            :class="canSubmit ? 'bg-brand-gradient hover:opacity-90' : 'bg-slate-400'"
        >
            <span v-if="isSubmitting">Memproses…</span>
            <span v-else-if="isLoadingPricing">Menghitung…</span>
            <span v-else-if="payLater">Simpan (Bayar Nanti)</span>
            <span v-else>Bayar Sekarang (F1)</span>
        </button>
    </div>
</template>
