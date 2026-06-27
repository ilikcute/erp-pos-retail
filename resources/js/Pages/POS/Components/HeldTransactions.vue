<script setup>
import { formatPrice } from "@/Utils/formatPrice";

defineProps({
    heldCarts: Array,
    hasActiveCart: Boolean,
});

const emit = defineEmits(["recall"]);
</script>

<template>
    <div class="px-lg py-3 border-b border-border-soft bg-accent-sunny-soft/30">
        <p
            class="text-[11px] font-bold uppercase tracking-wide text-accent-sunny mb-2"
        >
            ⏸️ Transaksi Ditahan ({{ heldCarts.length }})
        </p>
        <div class="space-y-1.5 max-h-24 overflow-y-auto">
            <button
                v-for="held in heldCarts"
                :key="held.id"
                @click="emit('recall', held.id)"
                class="w-full flex justify-between items-center p-2 rounded-lg bg-surface-card hover:bg-brand-soft transition text-left"
            >
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold text-ink-primary truncate">
                        {{ held.label || "Tanpa label" }}
                    </p>
                    <p class="text-[11px] text-ink-muted">
                        {{ held.items_count || 0 }} item
                    </p>
                </div>
                <span class="text-xs font-bold text-ink-primary">{{
                    formatPrice(held.total)
                }}</span>
            </button>
        </div>
    </div>
</template>
