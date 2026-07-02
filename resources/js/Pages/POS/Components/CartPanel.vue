<script setup>
import { computed, ref } from "vue";
import { formatPrice } from "@/Utils/formatPrice";
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
    products: { type: Array, default: () => [] },
    searchQuery: { type: String, default: "" },
});

const emit = defineEmits([
    "update-qty",
    "remove-item",
    "void-item",
    "hold-cart",
    "recall-held",
    "clear-cart",
    "update:searchQuery",
    "add-to-cart",
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

const searchInput = ref(null);
defineExpose({ focusSearch: () => searchInput.value?.focus() });

const filteredSearchProducts = computed(() => {
    if (!props.searchQuery) return [];
    const query = props.searchQuery.toLowerCase();
    return props.products.filter(p => 
        p.title?.toLowerCase().includes(query) ||
        p.barcode?.toLowerCase().includes(query) ||
        p.sku?.toLowerCase().includes(query) ||
        (p.variant_skus && p.variant_skus.some(sku => sku.toLowerCase().includes(query))) ||
        (p.variant_barcodes && p.variant_barcodes.some(bc => bc.toLowerCase().includes(query)))
    ).slice(0, 8);
});

const handleSearchEnter = () => {
    if (filteredSearchProducts.value.length > 0) {
        emit('add-to-cart', filteredSearchProducts.value[0]);
        emit('update:searchQuery', '');
    }
};

const selectSearchProduct = (p) => {
    emit('add-to-cart', p);
    emit('update:searchQuery', '');
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

        <!-- Barcode Scan Input & Search Results Dropdown -->
        <div class="px-lg py-md border-b border-border-soft bg-surface-card relative flex-shrink-0">
            <div class="relative">
                <svg
                    class="w-5 h-5 absolute left-base top-1/2 -translate-y-1/2 text-ink-muted"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"
                    />
                </svg>
                <input
                    ref="searchInput"
                    :value="searchQuery"
                    @input="emit('update:searchQuery', $event.target.value)"
                    @keydown.enter="handleSearchEnter"
                    type="text"
                    placeholder="🔫 Scan barcode atau cari produk... (tekan /)"
                    class="w-full pl-12 pr-base py-md rounded-pill border border-border-soft bg-surface-main text-base focus:ring-2 focus:ring-brand focus:border-brand outline-none transition"
                />
            </div>

            <!-- Dropdown Results -->
            <div
                v-if="searchQuery && filteredSearchProducts.length > 0"
                class="absolute left-lg right-lg mt-1 bg-surface-card border border-border-soft rounded-xl shadow-floating z-50 overflow-hidden divide-y divide-border-soft max-h-[300px] overflow-y-auto"
            >
                <button
                    v-for="p in filteredSearchProducts"
                    :key="p.id"
                    @click="selectSearchProduct(p)"
                    type="button"
                    class="w-full px-lg py-md text-left hover:bg-surface-muted transition flex items-center justify-between gap-md"
                >
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-ink-primary truncate">
                            {{ p.title }}
                        </p>
                        <p class="text-xs font-mono text-ink-muted">
                            Kode: {{ p.barcode || p.sku || '-' }} · Stok: {{ p.stock }}
                        </p>
                    </div>
                    <span class="text-sm font-extrabold text-brand shrink-0">
                        {{ formatPrice(p.sell_price) }}
                    </span>
                </button>
            </div>
            <div
                v-else-if="searchQuery"
                class="absolute left-lg right-lg mt-1 bg-surface-card border border-border-soft rounded-xl shadow-floating z-50 p-lg text-center text-xs text-ink-muted"
            >
                Tidak ada produk cocok.
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

            <!-- Cart Table Header -->
            <div
                v-else
                class="flex items-center justify-between gap-md px-md py-xs text-[10px] font-extrabold text-ink-muted uppercase tracking-wider border-b border-border-soft/60 mb-sm shrink-0"
            >
                <div class="flex-[2] min-w-0">Produk</div>
                <div class="min-w-[120px] flex-1">Harga Satuan</div>
                <div class="min-w-[80px] shrink-0 text-center">Qty</div>
                <div class="hidden xl:block min-w-[120px] flex-1">Diskon / Promosi</div>
                <div class="min-w-[100px] shrink-0 text-right">Gross Total</div>
                <div class="shrink-0 w-[38px]"></div>
            </div>

            <!-- ✅ NEW: Transition group untuk animasi -->
            <TransitionGroup name="cart-item" tag="div" class="space-y-sm">
                <div
                    v-for="item in carts"
                    :key="item.id"
                    :class="[
                        'group flex items-center justify-between gap-md rounded-xl bg-surface-muted px-md py-sm border border-border-soft transition-all',
                        isRemoving(item.id) ? 'opacity-50 scale-95' : '',
                        isUpdating(item.id) ? 'ring-2 ring-brand' : '',
                        'hover:bg-surface-card hover:shadow-sm',
                    ]"
                >
                    <!-- Col 1: Code & Description (flex-1) -->
                    <div class="flex items-center gap-sm min-w-0 flex-[2]">
                        <span class="font-mono text-xs font-extrabold text-brand bg-brand-soft/30 px-1.5 py-0.5 rounded border border-brand/10 shrink-0">
                            {{ item.product?.product_code || item.product?.sku || '-' }}
                        </span>
                        <h3 class="text-sm font-bold text-ink-primary truncate" :title="item.product?.product_name || item.product?.title">
                            {{ item.product?.product_name || item.product?.title || 'Produk' }}
                        </h3>
                    </div>

                    <!-- Col 2: Price & Discount/Promo (flex-1) -->
                    <div class="flex flex-col items-start min-w-[120px] flex-1">
                        <!-- If there's promo -->
                        <template v-if="getPricingData(item.id)?.pricing_rule">
                            <span class="text-xs text-ink-muted/60 line-through leading-none mb-0.5">
                                {{ formatPrice(getPricingData(item.id).base_unit_price) }}
                            </span>
                            <span class="text-xs text-accent-mint font-bold leading-none">
                                {{ formatPrice(getPricingData(item.id).effective_unit_price) }}
                            </span>
                        </template>
                        <!-- Normal Price -->
                        <template v-else>
                            <span class="text-sm font-semibold text-ink-secondary">
                                {{ formatPrice(getPricingData(item.id)?.effective_unit_price || item.price) }}
                            </span>
                        </template>
                    </div>

                    <!-- Col 3: Qty Adjuster -->
                    <div class="flex items-center gap-xs bg-surface-card rounded-pill border border-border-soft p-0.5 min-w-[80px] justify-center shrink-0">
                        <button
                            @click="emit('update-qty', item.id, item.qty - 1)"
                            :disabled="item.qty <= 1 || isUpdating(item.id)"
                            class="w-6 h-6 rounded-full bg-surface-muted text-ink-primary font-bold hover:bg-semantic-danger-soft hover:text-semantic-danger transition disabled:opacity-40 flex items-center justify-center text-xs"
                            title="Kurangi qty"
                        >
                            −
                        </button>
                        <span class="w-6 text-center text-xs font-extrabold font-mono tabular-nums">
                            {{ item.qty }}
                        </span>
                        <button
                            @click="emit('update-qty', item.id, item.qty + 1)"
                            :disabled="isUpdating(item.id)"
                            class="w-6 h-6 rounded-full bg-brand text-white font-bold hover:bg-brand-hover transition disabled:opacity-40 flex items-center justify-center text-xs"
                            title="Tambah qty"
                        >
                            +
                        </button>
                    </div>

                    <!-- Col 4: Promo Badge / Save info -->
                    <div class="hidden xl:flex flex-col items-start min-w-[120px] flex-1">
                        <span v-if="getPricingData(item.id)?.pricing_rule"
                            class="px-1.5 py-0.5 rounded bg-accent-mint-soft text-accent-mint text-[10px] font-bold truncate max-w-full"
                            :title="getPricingData(item.id).pricing_rule.name"
                        >
                            🎉 {{ getPricingData(item.id).pricing_rule.name }}
                        </span>
                        <span v-if="getPricingData(item.id)?.discount_amount > 0" class="text-[10px] text-semantic-success font-semibold mt-0.5">
                            🏷️ Hemat {{ formatPrice(getPricingData(item.id).discount_amount) }}
                        </span>
                    </div>

                    <!-- Col 5: Gross Total -->
                    <div class="text-right min-w-[100px] shrink-0">
                        <span class="text-sm font-extrabold text-brand font-mono">
                            {{
                                formatPrice(
                                    getPricingData(item.id)?.line_total ||
                                        (item.price || 0) * (item.qty || 0),
                                )
                            }}
                        </span>
                        <!-- Updating Loader -->
                        <span v-if="isUpdating(item.id)" class="block text-[8px] text-brand animate-pulse">Updating...</span>
                    </div>

                    <!-- Col 6: Void Button -->
                    <button
                        @click="emit('void-item', item)"
                        :disabled="isRemoving(item.id)"
                        class="text-ink-muted hover:text-semantic-danger hover:bg-semantic-danger-soft p-1.5 rounded-full transition disabled:opacity-40 shrink-0 w-[38px] flex justify-center"
                        title="Void item (perlu alasan)"
                    >
                        🗑️
                    </button>
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
