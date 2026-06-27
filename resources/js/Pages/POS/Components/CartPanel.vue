<script setup>
import { computed } from "vue";
import { formatPrice } from "@/Utils/formatPrice";
import { getProductImageUrl } from "@/Utils/imageUrl";
import HeldTransactions from "./HeldTransactions.vue";

const props = defineProps({
    carts: Array,
    pricingItemsByCartId: Object,
    cartCount: Number,
    heldCarts: Array,
    hasActiveCart: Boolean,
    isHolding: Boolean,
    removingItemId: [Number, String],
    updatingCartId: [Number, String],
});

const emit = defineEmits([
    "update-qty",
    "remove-item",
    "void-item",
    "hold-cart",
    "recall-held",
    "clear-cart",
]);
</script>

<template>
    <div class="flex flex-col h-full">
        <!-- Held Transactions -->
        <HeldTransactions
            v-if="heldCarts?.length"
            :held-carts="heldCarts"
            :has-active-cart="hasActiveCart"
            @recall="(id) => emit('recall-held', id)"
        />

        <!-- Header -->
        <div
            class="px-lg py-base border-b border-border-soft flex items-center justify-between flex-shrink-0"
        >
            <div class="flex items-center gap-sm">
                <h2 class="text-section-title font-bold text-ink-primary">
                    Keranjang
                </h2>
                <span
                    v-if="cartCount"
                    class="chip bg-brand-soft text-brand px-sm py-0.5 text-xs"
                >
                    {{ cartCount }} item
                </span>
            </div>
            <div class="flex gap-xs">
                <button
                    v-if="hasActiveCart"
                    @click="emit('hold-cart')"
                    :disabled="isHolding"
                    class="btn-pill px-sm py-1 text-xs bg-accent-sunny-soft text-accent-sunny hover:bg-accent-sunny/20 disabled:opacity-50"
                >
                    ⏸️ Hold
                </button>
                <button
                    v-if="hasActiveCart"
                    @click="emit('clear-cart')"
                    class="btn-pill px-sm py-1 text-xs text-semantic-danger hover:bg-semantic-danger-soft"
                >
                    Kosongkan
                </button>
            </div>
        </div>

        <!-- Cart Items -->
        <div
            class="flex-1 overflow-y-auto scroll-soft px-lg py-base space-y-sm min-h-0"
        >
            <div
                v-if="!carts?.length"
                class="h-full flex flex-col items-center justify-center text-center text-ink-muted"
            >
                <p class="text-5xl mb-base">🛍️</p>
                <p class="text-base font-medium">Keranjang masih kosong</p>
                <p class="text-sm">Scan barcode atau pilih produk</p>
            </div>

            <div
                v-for="item in carts"
                :key="item.id"
                class="flex items-center gap-md rounded-lg bg-surface-muted p-sm"
            >
                <div
                    class="w-11 h-11 rounded-lg bg-surface-card flex items-center justify-center flex-shrink-0 overflow-hidden"
                >
                    <img
                        v-if="item.product?.image"
                        :src="getProductImageUrl(item.product.image)"
                        class="w-full h-full object-cover"
                    />
                    <span v-else class="text-xl">{{
                        item.product?.emoji || "📦"
                    }}</span>
                </div>

                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-ink-primary truncate">
                        {{ item.product?.title }}
                    </p>
                    <div class="text-xs text-ink-muted">
                        <p
                            v-if="pricingItemsByCartId[item.id]?.pricing_rule"
                            class="line-through text-slate-400"
                        >
                            {{
                                formatPrice(
                                    pricingItemsByCartId[item.id]
                                        ?.base_unit_price,
                                )
                            }}
                            × {{ item.qty }}
                        </p>
                        <p>
                            {{
                                formatPrice(
                                    pricingItemsByCartId[item.id]
                                        ?.effective_unit_price || item.price,
                                )
                            }}
                            × {{ item.qty }}
                        </p>
                        <p
                            v-if="pricingItemsByCartId[item.id]?.pricing_rule"
                            class="text-[11px] font-medium text-accent-coral"
                        >
                            {{
                                pricingItemsByCartId[item.id].pricing_rule.name
                            }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-xs">
                    <button
                        @click="emit('update-qty', item.id, item.qty - 1)"
                        :disabled="item.qty <= 1 || updatingCartId === item.id"
                        class="w-7 h-7 rounded-full bg-surface-card border border-border-soft text-ink-secondary font-bold hover:bg-semantic-danger-soft hover:text-semantic-danger transition disabled:opacity-40"
                    >
                        −
                    </button>
                    <span
                        class="w-6 text-center text-sm font-bold text-ink-primary"
                        >{{ item.qty }}</span
                    >
                    <button
                        @click="emit('update-qty', item.id, item.qty + 1)"
                        :disabled="updatingCartId === item.id"
                        class="w-7 h-7 rounded-full bg-brand text-white font-bold hover:bg-brand-hover transition disabled:opacity-40"
                    >
                        +
                    </button>
                    <button
                        @click="emit('void-item', item)"
                        class="w-7 h-7 rounded-full text-semantic-danger hover:bg-semantic-danger-soft transition ml-1"
                        title="Void (perlu alasan)"
                    >
                        🗑️
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
