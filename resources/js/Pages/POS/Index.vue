<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';



const props = defineProps({
    products: { type: Array, default: () => ([
        { id: 1,  name: 'Kopi Susu Gula Aren', sku: 'BEV-001', price: 22000, stock: 48, category: 'Minuman', emoji: '☕' },
        { id: 2,  name: 'Teh Lemon Segar',     sku: 'BEV-002', price: 18000, stock: 35, category: 'Minuman', emoji: '🍋' },
        { id: 3,  name: 'Roti Bakar Cokelat',  sku: 'FOO-001', price: 25000, stock: 20, category: 'Makanan', emoji: '🍞' },
        { id: 4,  name: 'Nasi Goreng Spesial', sku: 'FOO-002', price: 35000, stock: 15, category: 'Makanan', emoji: '🍛' },
        { id: 5,  name: 'Kentang Goreng',      sku: 'SNK-001', price: 20000, stock: 60, category: 'Snack',   emoji: '🍟' },
        { id: 6,  name: 'Donat Gula',          sku: 'SNK-002', price: 12000, stock: 40, category: 'Snack',   emoji: '🍩' },
        { id: 7,  name: 'Air Mineral 600ml',   sku: 'BEV-003', price: 6000,  stock: 120,category: 'Minuman', emoji: '💧' },
        { id: 8,  name: 'Es Krim Vanila',      sku: 'DES-001', price: 15000, stock: 25, category: 'Dessert', emoji: '🍦' },
        { id: 9,  name: 'Burger Daging',       sku: 'FOO-003', price: 38000, stock: 12, category: 'Makanan', emoji: '🍔' },
        { id: 10, name: 'Pizza Slice',         sku: 'FOO-004', price: 28000, stock: 8,  category: 'Makanan', emoji: '🍕' },
        { id: 11, name: 'Cokelat Bar',         sku: 'SNK-003', price: 14000, stock: 50, category: 'Snack',   emoji: '🍫' },
        { id: 12, name: 'Jus Jeruk Segar',     sku: 'BEV-004', price: 24000, stock: 30, category: 'Minuman', emoji: '🧃' },
    ]) },
    cashierName: { type: String, default: 'Kasir 01' },
    shiftLabel: { type: String, default: 'Shift Pagi' },
});

const palette = [
    { ring: 'ring-accent-violet/30', tint: 'bg-accent-violet-soft', text: 'text-accent-violet' },
    { ring: 'ring-accent-mint/30',   tint: 'bg-accent-mint-soft',   text: 'text-accent-mint' },
    { ring: 'ring-accent-sunny/30',  tint: 'bg-accent-sunny-soft',  text: 'text-accent-sunny' },
    { ring: 'ring-accent-sky/30',    tint: 'bg-accent-sky-soft',    text: 'text-accent-sky' },
    { ring: 'ring-accent-coral/30',  tint: 'bg-accent-coral-soft',  text: 'text-accent-coral' },
    { ring: 'ring-accent-grape/30',  tint: 'bg-accent-grape-soft',  text: 'text-accent-grape' },
];
const colorFor = (i) => palette[i % palette.length];

const search = ref('');
const activeCategory = ref('Semua');
const cart = ref([]);
const paid = ref(0);

const categories = computed(() => ['Semua', ...new Set(props.products.map(p => p.category))]);

const filteredProducts = computed(() => {
    const q = search.value.trim().toLowerCase();
    return props.products.filter(p => {
        const okCat = activeCategory.value === 'Semua' || p.category === activeCategory.value;
        const okSearch = !q || p.name.toLowerCase().includes(q) || p.sku.toLowerCase().includes(q);
        return okCat && okSearch;
    });
});

const addToCart = (p) => {
    const found = cart.value.find(i => i.id === p.id);
    if (found) { found.qty++; } else { cart.value.push({ ...p, qty: 1 }); }
};
const inc = (i) => { i.qty++; };
const dec = (i) => { i.qty--; if (i.qty <= 0) removeItem(i); };
const removeItem = (i) => { cart.value = cart.value.filter(x => x.id !== i.id); };
const clearCart = () => { cart.value = []; paid.value = 0; };

const subtotal = computed(() => cart.value.reduce((s, i) => s + i.price * i.qty, 0));
const tax = computed(() => Math.round(subtotal.value * 0.11));
const total = computed(() => subtotal.value + tax.value);
const change = computed(() => Math.max(0, paid.value - total.value));
const itemCount = computed(() => cart.value.reduce((s, i) => s + i.qty, 0));

const quickCash = [50000, 100000, 150000, 200000];
const setPaid = (v) => { paid.value = v; };

const rupiah = (n) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n || 0);
</script>

<template>
    <Head title="Kasir POS" />
    <div class="h-screen flex bg-surface-main overflow-hidden">
        <!-- ===== LEFT: Product catalog ===== -->
        <section class="flex-1 flex flex-col min-w-0">
            <!-- Header -->
            <header class="flex items-center gap-base px-xl py-base bg-surface-card border-b border-border-soft">
                <div class="w-11 h-11 rounded-xl bg-brand-gradient flex items-center justify-center text-xl shadow-brand-glow">🛒</div>
                <div class="mr-auto leading-tight">
                    <h1 class="text-section-title font-bold text-ink-primary">Kasir POS</h1>
                    <p class="text-sm text-ink-muted">{{ cashierName }} · {{ shiftLabel }}</p>
                </div>
                <Link href="/dashboard" class="btn-pill px-base py-sm text-sm text-ink-secondary bg-surface-muted hover:bg-border-soft">
                    ← Dashboard
                </Link>
            </header>

            <!-- Search + categories -->
            <div class="px-xl pt-base space-y-base">
                <div class="relative">
                    <svg class="w-5 h-5 absolute left-base top-1/2 -translate-y-1/2 text-ink-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
                    <input v-model="search" type="text" placeholder="Scan barcode atau cari produk..." class="w-full pl-12 pr-base py-md rounded-pill border border-border-soft bg-surface-card text-base focus:ring-2 focus:ring-brand focus:border-brand outline-none transition" />
                </div>
                <div class="flex gap-sm overflow-x-auto scroll-soft pb-xs">
                    <button v-for="cat in categories" :key="cat" @click="activeCategory = cat"
                        :class="['chip whitespace-nowrap', activeCategory === cat ? 'bg-brand text-white shadow-brand-glow' : 'bg-surface-card text-ink-secondary border border-border-soft hover:border-brand-border']">
                        {{ cat }}
                    </button>
                </div>
            </div>

            <!-- Product grid -->
            <div class="flex-1 overflow-y-auto scroll-soft px-xl py-base">
                <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-base">
                    <button v-for="(p, idx) in filteredProducts" :key="p.id" @click="addToCart(p)"
                        class="card-friendly p-base text-left hover:shadow-floating hover:-translate-y-0.5 transition-all duration-150 active:scale-95 ring-1 ring-transparent focus:outline-none"
                        :class="colorFor(idx).ring">
                        <div class="w-full aspect-square rounded-xl flex items-center justify-center text-4xl mb-md" :class="colorFor(idx).tint">
                            {{ p.emoji }}
                        </div>
                        <p class="text-sm font-semibold text-ink-primary leading-snug line-clamp-2 min-h-[2.5rem]">{{ p.name }}</p>
                        <div class="flex items-center justify-between mt-xs">
                            <span class="text-card-title font-bold" :class="colorFor(idx).text">{{ rupiah(p.price) }}</span>
                        </div>
                        <span class="inline-block mt-xs text-[11px] font-medium text-ink-muted">Stok: {{ p.stock }}</span>
                    </button>
                </div>
                <div v-if="filteredProducts.length === 0" class="text-center py-5xl text-ink-muted">
                    <p class="text-4xl mb-base">🔍</p>
                    <p class="text-base">Produk tidak ditemukan</p>
                </div>
            </div>
        </section>

        <!-- ===== RIGHT: Cart ===== -->
        <aside class="w-[400px] flex-shrink-0 bg-surface-card border-l border-border-soft flex flex-col">
            <div class="px-lg py-base border-b border-border-soft flex items-center justify-between">
                <div class="flex items-center gap-sm">
                    <h2 class="text-section-title font-bold text-ink-primary">Keranjang</h2>
                    <span class="chip bg-brand-soft text-brand px-sm py-0.5 text-xs">{{ itemCount }} item</span>
                </div>
                <button v-if="cart.length" @click="clearCart" class="text-sm font-semibold text-semantic-danger hover:bg-semantic-danger-soft rounded-pill px-md py-xs transition">Kosongkan</button>
            </div>

            <!-- Cart items -->
            <div class="flex-1 overflow-y-auto scroll-soft px-lg py-base space-y-sm">
                <div v-if="cart.length === 0" class="h-full flex flex-col items-center justify-center text-center text-ink-muted">
                    <p class="text-5xl mb-base">🛍️</p>
                    <p class="text-base font-medium">Keranjang masih kosong</p>
                    <p class="text-sm">Pilih produk untuk memulai transaksi</p>
                </div>
                <div v-for="item in cart" :key="item.id" class="flex items-center gap-md rounded-lg bg-surface-muted p-sm">
                    <div class="w-11 h-11 rounded-lg bg-surface-card flex items-center justify-center text-xl flex-shrink-0">{{ item.emoji }}</div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-ink-primary truncate">{{ item.name }}</p>
                        <p class="text-sm text-ink-muted">{{ rupiah(item.price) }}</p>
                    </div>
                    <div class="flex items-center gap-xs">
                        <button @click="dec(item)" class="w-7 h-7 rounded-full bg-surface-card border border-border-soft text-ink-secondary font-bold hover:bg-semantic-danger-soft hover:text-semantic-danger transition">−</button>
                        <span class="w-6 text-center text-sm font-bold text-ink-primary">{{ item.qty }}</span>
                        <button @click="inc(item)" class="w-7 h-7 rounded-full bg-brand text-white font-bold hover:bg-brand-hover transition">+</button>
                    </div>
                </div>
            </div>

            <!-- Totals + payment -->
            <div class="border-t border-border-soft p-lg space-y-md">
                <div class="space-y-xs text-sm">
                    <div class="flex justify-between text-ink-secondary"><span>Subtotal</span><span>{{ rupiah(subtotal) }}</span></div>
                    <div class="flex justify-between text-ink-secondary"><span>PPN 11%</span><span>{{ rupiah(tax) }}</span></div>
                    <div class="flex justify-between items-center pt-sm border-t border-border-soft">
                        <span class="text-card-title font-bold text-ink-primary">Total</span>
                        <span class="text-page-title-sm font-extrabold text-gradient-brand">{{ rupiah(total) }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-4 gap-xs">
                    <button v-for="c in quickCash" :key="c" @click="setPaid(c)"
                        :class="['btn-pill py-sm text-xs', paid === c ? 'bg-accent-mint text-white' : 'bg-accent-mint-soft text-accent-mint']">
                        {{ (c/1000) }}k
                    </button>
                </div>
                <div v-if="paid > 0" class="flex justify-between text-sm font-semibold">
                    <span class="text-ink-secondary">Kembalian</span>
                    <span class="text-accent-mint">{{ rupiah(change) }}</span>
                </div>

                <button :disabled="cart.length === 0"
                    class="btn-pill w-full py-base text-base text-white bg-brand-gradient shadow-brand-glow hover:opacity-95 disabled:opacity-40 disabled:cursor-not-allowed">
                    💳 Bayar Sekarang
                </button>
            </div>
        </aside>
    </div>
</template>

<style scoped>
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>