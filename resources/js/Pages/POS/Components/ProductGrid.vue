<script setup>
import { computed, ref } from "vue";
import { formatPrice } from "@/Utils/formatPrice";

const props = defineProps({
    products: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    selectedCategory: { type: [Number, String, null], default: null },
    searchQuery: { type: String, default: "" },
    isSearching: Boolean,
    addingProductId: { type: [Number, String, null], default: null },
});

const emit = defineEmits([
    "update:searchQuery",
    "update:selectedCategory",
    "add-to-cart",
]);

const searchInput = ref(null);
defineExpose({ focusSearch: () => searchInput.value?.focus() });

const palette = [
    {
        ring: "ring-accent-violet/30",
        tint: "bg-accent-violet-soft",
        text: "text-accent-violet",
    },
    {
        ring: "ring-accent-mint/30",
        tint: "bg-accent-mint-soft",
        text: "text-accent-mint",
    },
    {
        ring: "ring-accent-sunny/30",
        tint: "bg-accent-sunny-soft",
        text: "text-accent-sunny",
    },
    {
        ring: "ring-accent-sky/30",
        tint: "bg-accent-sky-soft",
        text: "text-accent-sky",
    },
    {
        ring: "ring-accent-coral/30",
        tint: "bg-accent-coral-soft",
        text: "text-accent-coral",
    },
];
const colorFor = (i) => palette[i % palette.length];
</script>

<template>
    <div class="flex flex-col h-full">
        <!-- Search + Categories -->
        <div
            class="px-xl pt-base space-y-base bg-surface-card border-b border-border-soft"
        >
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
                    type="text"
                    placeholder="🔫 Scan barcode atau cari produk... (tekan /)"
                    class="w-full pl-12 pr-base py-md rounded-pill border border-border-soft bg-surface-main text-base focus:ring-2 focus:ring-brand focus:border-brand outline-none transition"
                />
            </div>

            <div class="flex gap-sm overflow-x-auto scroll-soft pb-xs">
                <button
                    @click="emit('update:selectedCategory', null)"
                    :class="[
                        'chip whitespace-nowrap',
                        selectedCategory === null
                            ? 'bg-brand text-white shadow-brand-glow'
                            : 'bg-surface-card text-ink-secondary border border-border-soft',
                    ]"
                >
                    Semua
                </button>
                <button
                    v-for="cat in categories"
                    :key="cat.id"
                    @click="emit('update:selectedCategory', cat.id)"
                    :class="[
                        'chip whitespace-nowrap',
                        selectedCategory === cat.id
                            ? 'bg-brand text-white shadow-brand-glow'
                            : 'bg-surface-card text-ink-secondary border border-border-soft',
                    ]"
                >
                    {{ cat.name }}
                </button>
            </div>
        </div>

        <!-- Product Grid -->
        <div class="flex-1 overflow-y-auto scroll-soft px-xl py-base">
            <div
                class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-base"
            >
                <button
                    v-for="(p, idx) in products"
                    :key="p.id"
                    @click="emit('add-to-cart', p)"
                    :disabled="addingProductId === p.id"
                    class="card-friendly p-md text-left hover:shadow-floating hover:-translate-y-0.5 transition-all duration-150 active:scale-95 ring-1 ring-transparent focus:outline-none disabled:opacity-50 flex flex-col justify-between"
                    :class="colorFor(idx).ring"
                >
                    <div>
                        <div class="flex items-center justify-between gap-sm mb-xs">
                            <span class="font-mono text-[10px] font-bold text-ink-muted truncate max-w-[100px]" :title="p.barcode || p.sku">
                                🏷️ {{ p.barcode || p.sku || '-' }}
                            </span>
                            <span :class="p.stock <= 5 ? 'bg-semantic-danger-soft text-semantic-danger' : 'bg-surface-muted text-ink-muted'" class="text-[10px] px-1.5 py-0.5 rounded font-bold">
                                Stok: {{ p.stock }}
                            </span>
                        </div>
                        <p class="text-xs font-bold text-ink-primary leading-snug line-clamp-2 min-h-[2rem]">
                            {{ p.title }}
                        </p>
                    </div>
                    <div class="flex items-center justify-between mt-sm border-t border-border-soft pt-xs">
                        <span class="text-sm font-extrabold" :class="colorFor(idx).text">
                            {{ formatPrice(p.sell_price) }}
                        </span>
                        <span class="text-xs">➕</span>
                    </div>
                </button>
            </div>

            <div
                v-if="products.length === 0"
                class="text-center py-5xl text-ink-muted"
            >
                <p class="text-4xl mb-base">🔍</p>
                <p class="text-base">Produk tidak ditemukan</p>
            </div>
        </div>
    </div>
</template>
