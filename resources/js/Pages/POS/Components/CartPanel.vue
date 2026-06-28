<script setup>
import { computed } from "vue";
import { formatPrice } from "@/Utils/formatPrice";
import { getProductImageUrl } from "@/Utils/imageUrl";
import HeldTransactions from "./HeldTransactions.vue";

const props = defineProps({
    carts: { type: Array, default: () => [] },
    pricingItemsByCartId: { type: Object, default: () => ({}) },
    cartCount: { type: Number, default: 0 },
    heldCarts: { type: Array, default: () => [] },
    hasActiveCart: { type: Boolean, default: false },
    isHolding: { type: Boolean, default: false },
    removingItemId: { type: [Number, String, null], default: null },
    updatingCartId: { type: [Number, String, null], default: null },
});

const emit = defineEmits([
    "update-qty",
    "remove-item",
    "void-item",
    "hold-cart",
    "recall-held",
    "clear-cart",
]);

// ✅ NEW: Helper untuk ambil pricing data dengan safe access
const getPricingData = (cartId) => {
    return props.pricingItemsByCartId?.[cartId] || null;
};

// ✅ NEW: Helper untuk cek apakah item sedang di-update
const isUpdating = (itemId) => props.updatingCartId === itemId;
const isRemoving = (itemId) => props.removingItemId === itemId;

// ✅ NEW: Konfirmasi sebelum kosongkan cart
const handleClearCart = () => {
    if (props.carts.length === 0) return;
    if (confirm("Yakin ingin mengosongkan keranjang?")) {
        emit("clear-cart");
    }
};
</script>

<template>
    <div class="flex flex-col h-full">
        <!-- ═══════════════════════════════════════════════════════════ -->
        <!-- HELD TRANSACTIONS -->
        <!-- ═══════════════════════════════════════════════════════════ -->
        <HeldTransactions
            v-if="heldCarts?.length"
            :held-carts="heldCarts"
            :has-active-cart="hasActiveCart"
            @recall="(id) => emit('recall-held', id)"
        />

        <!-- ═══════════════════════════════════════════════════════════ -->
        <!-- HEADER -->
        <!-- ═══════════════════════════════════════════════════════════ -->
        <div
            class="px-lg py-base border-b border-border-soft flex items-center justify-between flex-shrink-0"
        >
            <div class="flex items-center gap-sm">
                <h2 class="text-section-title font-bold text-ink-primary">
                    Keranjang
                </h2>
                <span
                    v-if="cartCount"
                    class="chip bg-brand-soft text-brand px-sm py-0.5 text-xs font-bold tabular-nums"
                >
                    {{ cartCount }} item
                </span>
            </div>
            <div class="flex gap-xs">
                <button
                    v-if="hasActiveCart"
                    @click="emit('hold-cart')"
                    :disabled="isHolding"
                    class="btn-pill px-sm py-1 text-xs bg-accent-sunny-soft text-accent-sunny hover:bg-accent-sunny/20 disabled:opacity-50 transition"
                    title="Tahan transaksi sementara"
                >
                    <span v-if="isHolding" class="inline-block animate-spin"
                        >⏳</span
                    >
                    <span v-else>⏸️</span>
                    Hold
                </button>
                <button
                    v-if="hasActiveCart"
                    @click="handleClearCart"
                    class="btn-pill px-sm py-1 text-xs text-semantic-danger hover:bg-semantic-danger-soft transition"
                    title="Kosongkan keranjang"
                >
                    🗑️ Kosongkan
                </button>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════ -->
        <!-- CART ITEMS -->
        <!-- ═══════════════════════════════════════════════════════════ -->
        <div
            class="flex-1 overflow-y-auto scroll-soft px-lg py-base space-y-sm min-h-0"
        >
            <!-- Empty State -->
            <div
                v-if="!carts?.length"
                class="h-full flex flex-col items-center justify-center text-center text-ink-muted py-10"
            >
                <p class="text-5xl mb-base">🛍️</p>
                <p class="text-base font-medium">Keranjang masih kosong</p>
                <p class="text-sm mt-1">Scan barcode atau pilih produk</p>
            </div>

            <!-- ✅ NEW: Transition group untuk animasi -->
            <TransitionGroup name="cart-item" tag="div" class="space-y-sm">
                <div
                    v-for="item in carts"
                    :key="item.id"
                    :class="[
                        'group flex items-stretch gap-md rounded-xl bg-surface-muted p-sm transition-all',
                        isRemoving(item.id) ? 'opacity-50 scale-95' : '',
                        isUpdating(item.id) ? 'ring-2 ring-brand/30' : '',
                        'hover:bg-surface-card hover:shadow-sm',
                    ]"
                >
                    <!-- ═══════════════════════════════════════════════════════════ -->
                    <!-- IMAGE -->
                    <!-- ═══════════════════════════════════════════════════════════ -->
                    <div
                        class="w-12 h-12 rounded-lg bg-surface-card flex items-center justify-center flex-shrink-0 overflow-hidden border border-border-soft"
                    >
                        <img
                            v-if="item.product?.image"
                            :src="getProductImageUrl(item.product.image)"
                            :alt="item.product?.title"
                            class="w-full h-full object-cover"
                            loading="lazy"
                        />
                        <span v-else class="text-xl">📦</span>
                    </div>

                    <!-- ═══════════════════════════════════════════════════════════ -->
                    <!-- PRODUCT INFO (flex-1) -->
                    <!-- ═══════════════════════════════════════════════════════════ -->
                    <div class="min-w-0 flex-1 flex flex-col justify-between">
                        <!-- Title -->
                        <p
                            class="text-sm font-semibold text-ink-primary truncate leading-tight"
                        >
                            {{ item.product?.title }}
                        </p>

                        <!-- Pricing Details -->
                        <div class="text-xs text-ink-muted space-y-0.5 mt-1">
                            <!-- ✅ FIXED: Pakai helper dengan safe access -->
                            <template
                                v-if="getPricingData(item.id)?.pricing_rule"
                            >
                                <!-- Harga asli (coret) -->
                                <p
                                    class="line-through text-ink-muted/60 tabular-nums"
                                >
                                    {{
                                        formatPrice(
                                            getPricingData(item.id)
                                                .base_unit_price,
                                        )
                                    }}
                                    × {{ item.qty }}
                                </p>

                                <!-- Harga promo -->
                                <p
                                    class="text-accent-mint font-semibold tabular-nums"
                                >
                                    {{
                                        formatPrice(
                                            getPricingData(item.id)
                                                .effective_unit_price,
                                        )
                                    }}
                                    × {{ item.qty }}
                                </p>

                                <!-- ✅ NEW: Discount amount per item -->
                                <p
                                    v-if="
                                        getPricingData(item.id)
                                            .discount_amount > 0
                                    "
                                    class="text-[10px] text-accent-mint/80 tabular-nums"
                                >
                                    Hemat
                                    {{
                                        formatPrice(
                                            getPricingData(item.id)
                                                .discount_amount,
                                        )
                                    }}
                                </p>

                                <!-- Badge promo -->
                                <span
                                    class="inline-flex items-center gap-1 mt-1 px-1.5 py-0.5 rounded-md bg-accent-mint-soft text-accent-mint text-[10px] font-bold max-w-full"
                                >
                                    🎉
                                    <span class="truncate">
                                        {{
                                            getPricingData(item.id).pricing_rule
                                                .name
                                        }}
                                    </span>
                                </span>
                            </template>

                            <!-- Harga normal (tanpa promo) -->
                            <template v-else>
                                <p class="tabular-nums">
                                    {{
                                        formatPrice(
                                            getPricingData(item.id)
                                                ?.effective_unit_price ||
                                                item.price,
                                        )
                                    }}
                                    × {{ item.qty }}
                                </p>
                            </template>
                        </div>
                    </div>

                    <!-- ═══════════════════════════════════════════════════════════ -->
                    <!-- ✅ FIXED: TOTAL LINE (di luar flex-1, sejajar dengan buttons) -->
                    <!-- ═══════════════════════════════════════════════════════════ -->
                    <div
                        class="flex flex-col items-end justify-between py-0.5 min-w-[80px]"
                    >
                        <p class="text-sm font-bold text-brand tabular-nums">
                            {{
                                formatPrice(
                                    getPricingData(item.id)?.line_total ||
                                        (item.price || 0) * (item.qty || 0),
                                )
                            }}
                        </p>

                        <!-- ✅ NEW: Loading indicator saat updating -->
                        <div
                            v-if="isUpdating(item.id)"
                            class="text-[10px] text-brand animate-pulse"
                        >
                            Updating...
                        </div>
                    </div>

                    <!-- ═══════════════════════════════════════════════════════════ -->
                    <!-- ACTION BUTTONS -->
                    <!-- ═══════════════════════════════════════════════════════════ -->
                    <div
                        class="flex flex-col items-center justify-center gap-1 pl-2 border-l border-border-soft/50"
                    >
                        <!-- Plus -->
                        <button
                            @click="emit('update-qty', item.id, item.qty + 1)"
                            :disabled="isUpdating(item.id)"
                            class="w-7 h-7 rounded-full bg-brand text-white font-bold hover:bg-brand-hover transition disabled:opacity-40 flex items-center justify-center text-sm"
                            title="Tambah qty"
                        >
                            +
                        </button>

                        <!-- Qty display -->
                        <span
                            :class="[
                                'w-7 text-center text-sm font-bold tabular-nums',
                                isUpdating(item.id)
                                    ? 'text-brand'
                                    : 'text-ink-primary',
                            ]"
                        >
                            {{ item.qty }}
                        </span>

                        <!-- Minus -->
                        <button
                            @click="emit('update-qty', item.id, item.qty - 1)"
                            :disabled="item.qty <= 1 || isUpdating(item.id)"
                            class="w-7 h-7 rounded-full bg-surface-card border border-border-soft text-ink-secondary font-bold hover:bg-semantic-danger-soft hover:text-semantic-danger transition disabled:opacity-40 flex items-center justify-center text-sm"
                            title="Kurangi qty"
                        >
                            −
                        </button>

                        <!-- Void -->
                        <button
                            @click="emit('void-item', item)"
                            :disabled="isRemoving(item.id)"
                            class="w-7 h-7 rounded-full text-semantic-danger hover:bg-semantic-danger-soft transition flex items-center justify-center text-sm disabled:opacity-40"
                            title="Void (perlu alasan)"
                        >
                            🗑️
                        </button>
                    </div>
                </div>
            </TransitionGroup>
        </div>
    </div>
</template>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- ✅ NEW: Animasi untuk cart items -->
<!-- ═══════════════════════════════════════════════════════════ -->
<style scoped>
.cart-item-enter-active,
.cart-item-leave-active {
    transition: all 0.3s ease;
}

.cart-item-enter-from {
    opacity: 0;
    transform: translateX(-20px);
}

.cart-item-leave-to {
    opacity: 0;
    transform: translateX(20px) scale(0.95);
}

.cart-item-move {
    transition: transform 0.3s ease;
}
</style>
