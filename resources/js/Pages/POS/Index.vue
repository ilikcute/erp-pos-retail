<script setup>
import { ref, computed } from 'vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import { Head, Link } from '@inertiajs/vue3';

const barcode = ref('');
const cartItems = ref([
    { product_name: 'Apple Studio Display 27"', unit_price: 24999000, quantity: 1, line_total: 24999000 },
    { product_name: 'Magic Keyboard with Touch ID', unit_price: 2799000, quantity: 2, line_total: 5598000 }
]);
const paymentMethod = ref('CASH');
const amountPaid = ref(35000000);
const taxRate = ref(11);
const discountAmount = ref(1500000);

const showSuccessModal = ref(false);
const receiptData = ref(null);

const subtotal = computed(() => {
    return cartItems.value.reduce((sum, item) => sum + item.line_total, 0);
});

const taxAmount = computed(() => {
    return Math.round((subtotal.value - discountAmount.value) * (taxRate.value / 100));
});

const grandTotal = computed(() => {
    return subtotal.value - discountAmount.value + taxAmount.value;
});

const change = computed(() => {
    return Math.max(0, amountPaid.value - grandTotal.value);
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

// Mock product catalogue for scanner
const mockProducts = {
    '111': { name: 'MacBook Pro 14" M3', price: 28999000 },
    '222': { name: 'iPhone 15 Pro Max 256GB', price: 22499000 },
    '333': { name: 'iPad Air 11" M2', price: 10999000 },
    '444': { name: 'AirPods Pro 2nd Gen', price: 3899000 },
    '555': { name: 'Apple Watch Series 9', price: 6799000 },
};

const addProduct = () => {
    const code = barcode.value.trim();
    if (!code) return;

    const matchedProduct = mockProducts[code];
    if (matchedProduct) {
        const existingItem = cartItems.value.find(item => item.product_name === matchedProduct.name);
        if (existingItem) {
            existingItem.quantity += 1;
            existingItem.line_total = existingItem.quantity * existingItem.unit_price;
        } else {
            cartItems.value.push({
                product_name: matchedProduct.name,
                unit_price: matchedProduct.price,
                quantity: 1,
                line_total: matchedProduct.price,
            });
        }
    } else {
        const price = Math.floor(Math.random() * 50 + 1) * 10000;
        cartItems.value.push({
            product_name: `Product [${code}]`,
            unit_price: price,
            quantity: 1,
            line_total: price,
        });
    }

    barcode.value = '';
    
    if (amountPaid.value < grandTotal.value) {
        amountPaid.value = Math.ceil(grandTotal.value / 100000) * 100000;
    }
};

const updateQuantity = (index, delta) => {
    const item = cartItems.value[index];
    item.quantity = Math.max(1, item.quantity + delta);
    item.line_total = item.quantity * item.unit_price;

    if (amountPaid.value < grandTotal.value) {
        amountPaid.value = Math.ceil(grandTotal.value / 100000) * 100000;
    }
};

const removeItem = (index) => {
    cartItems.value.splice(index, 1);
    
    if (amountPaid.value < grandTotal.value) {
        amountPaid.value = Math.ceil(grandTotal.value / 100000) * 100000;
    }
};

const completeSale = () => {
    if (cartItems.value.length === 0 || !paymentMethod.value) return;

    const randNo = Math.floor(1000 + Math.random() * 9000);
    const dateStr = new Date().toISOString().slice(0, 10).replace(/-/g, '');
    const transactionNo = `TR-${dateStr}-${randNo}`;

    receiptData.value = {
        transactionNo,
        items: [...cartItems.value],
        subtotal: subtotal.value,
        discountAmount: discountAmount.value,
        taxAmount: taxAmount.value,
        grandTotal: grandTotal.value,
        amountPaid: amountPaid.value,
        change: change.value,
        paymentMethod: paymentMethod.value,
        date: new Date().toLocaleString('id-ID'),
    };

    showSuccessModal.value = true;
};

const resetSale = () => {
    cartItems.value = [];
    paymentMethod.value = 'CASH';
    amountPaid.value = 0;
    discountAmount.value = 0;
    showSuccessModal.value = false;
    receiptData.value = null;
};

const holdTransaction = () => {
    alert('☕ Transaksi telah ditahan sementara (Hold Bill).');
};

const cancelTransaction = () => {
    if (confirm('Apakah Anda yakin ingin membatalkan transaksi ini?')) {
        cartItems.value = [];
        paymentMethod.value = 'CASH';
        amountPaid.value = 0;
        discountAmount.value = 0;
    }
};

const quickPay = (amount) => {
    amountPaid.value = amount;
};

// Date time update
const currentTime = ref(new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }));
setInterval(() => {
    currentTime.value = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
}, 60000);
const currentDate = new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'short' });
</script>

<template>
    <Head title="POS Terminal (Full Screen)" />

    <div class="h-screen flex flex-col bg-surface-main overflow-hidden text-ink-primary font-sans">
        <!-- POS Full-Screen Header -->
        <header class="h-16 bg-white border-b border-border-soft px-6 flex items-center justify-between shrink-0 shadow-sm z-10">
            <div class="flex items-center gap-4">
                <!-- Exit Button -->
                <Link href="/dashboard" class="flex items-center gap-1.5 text-ink-secondary hover:text-ink-primary bg-surface-main border border-border-soft px-3 py-1.5 rounded-md text-xs font-semibold shadow-soft cursor-pointer transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Dashboard
                </Link>
                <div class="h-5 w-[1px] bg-border-soft"></div>
                <div class="flex items-center gap-2">
                    <span class="text-base font-bold tracking-tight text-brand">ERP POS Terminal</span>
                    <span class="bg-semantic-success-soft text-semantic-success text-[10px] font-bold px-2 py-0.5 rounded-full border border-transparent">
                        Open Session
                    </span>
                </div>
            </div>

            <!-- Time & Cashier Info -->
            <div class="flex items-center gap-5 text-sm">
                <div class="text-right">
                    <p class="font-semibold text-ink-primary">{{ currentDate }}</p>
                    <p class="text-xs text-ink-secondary font-mono">{{ currentTime }}</p>
                </div>
                <div class="h-8 w-[1px] bg-border-soft"></div>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-brand-soft text-brand flex items-center justify-center font-bold text-xs">
                        SA
                    </div>
                    <div class="text-left">
                        <p class="text-xs text-ink-secondary">Kasir</p>
                        <p class="text-xs font-bold text-ink-primary">Superadmin</p>
                    </div>
                </div>
            </div>
        </header>

        <!-- POS Main Body -->
        <div class="flex-1 flex overflow-hidden">
            
            <!-- Left Side: Product scanner and Cart Items list (Scrollable) -->
            <div class="flex-1 flex flex-col p-6 overflow-hidden space-y-6">
                <!-- Scanner Input Card -->
                <div class="bg-surface-card rounded-card border border-border-soft p-5 shadow-card shrink-0">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-ink-secondary mb-2.5">Barcode Scanner / Kode Produk</h3>
                    <div class="relative">
                        <input
                            v-model="barcode"
                            @keyup.enter="addProduct"
                            type="text"
                            placeholder="Scan barcode (e.g. 111, 222, 333) lalu tekan Enter..."
                            class="w-full px-base py-md pl-2xl rounded-md border border-border-strong bg-surface-card text-ink-primary placeholder-ink-muted focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent font-mono text-sm"
                            autofocus
                        />
                        <svg class="absolute left-base top-1/2 -translate-y-1/2 w-5 h-5 text-ink-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                    </div>
                    <div class="mt-2.5 flex items-center gap-sm overflow-x-auto py-0.5">
                        <span class="text-[11px] text-ink-muted whitespace-nowrap">💡 Simulator Barcode:</span>
                        <button @click="barcode = '111'; addProduct()" class="text-[10px] bg-surface-main hover:bg-border-soft border border-border-soft px-2 py-0.5 rounded text-ink-primary font-mono cursor-pointer transition-colors">111 (MacBook)</button>
                        <button @click="barcode = '222'; addProduct()" class="text-[10px] bg-surface-main hover:bg-border-soft border border-border-soft px-2 py-0.5 rounded text-ink-primary font-mono cursor-pointer transition-colors">222 (iPhone)</button>
                        <button @click="barcode = '333'; addProduct()" class="text-[10px] bg-surface-main hover:bg-border-soft border border-border-soft px-2 py-0.5 rounded text-ink-primary font-mono cursor-pointer transition-colors">333 (iPad)</button>
                        <button @click="barcode = '444'; addProduct()" class="text-[10px] bg-surface-main hover:bg-border-soft border border-border-soft px-2 py-0.5 rounded text-ink-primary font-mono cursor-pointer transition-colors">444 (AirPods)</button>
                        <button @click="barcode = '555'; addProduct()" class="text-[10px] bg-surface-main hover:bg-border-soft border border-border-soft px-2 py-0.5 rounded text-ink-primary font-mono cursor-pointer transition-colors">555 (Watch)</button>
                    </div>
                </div>

                <!-- Cart Items Card (Scrollable inside) -->
                <div class="bg-surface-card rounded-card border border-border-soft shadow-card flex-1 flex flex-col overflow-hidden">
                    <div class="p-5 border-b border-border-soft flex justify-between items-center shrink-0">
                        <h3 class="text-base font-bold text-ink-primary">Daftar Belanja</h3>
                        <span class="bg-brand-soft text-brand text-xs font-bold px-2.5 py-1 rounded-full">{{ cartItems.length }} Item</span>
                    </div>

                    <!-- Cart Content -->
                    <div class="flex-1 overflow-y-auto p-5">
                        <div v-if="cartItems.length === 0" class="h-full flex flex-col items-center justify-center text-ink-secondary py-10">
                            <svg class="w-16 h-16 text-ink-muted mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            <p class="font-semibold text-ink-secondary">Belum Ada Item Terpilih</p>
                            <p class="text-xs text-ink-muted mt-1">Scan produk atau gunakan simulator di atas untuk memasukkan barang</p>
                        </div>
                        
                        <div v-else class="divide-y divide-border-soft">
                            <div v-for="(item, index) in cartItems" :key="index" class="py-3.5 flex justify-between items-center gap-md first:pt-0 last:pb-0">
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-bold text-ink-primary truncate">{{ item.product_name }}</h4>
                                    <p class="text-xs text-ink-secondary font-mono mt-0.5">
                                        {{ formatCurrency(item.unit_price) }}
                                    </p>
                                </div>

                                <!-- Quantity Control -->
                                <div class="flex items-center border border-border-soft rounded-md bg-surface-main shrink-0">
                                    <button
                                        @click="updateQuantity(index, -1)"
                                        class="px-2.5 py-1 text-ink-secondary hover:text-ink-primary font-bold hover:bg-border-soft transition-colors cursor-pointer rounded-l-md"
                                    >
                                        -
                                    </button>
                                    <span class="px-2.5 text-xs font-bold text-ink-primary font-mono w-9 text-center">
                                        {{ item.quantity }}
                                    </span>
                                    <button
                                        @click="updateQuantity(index, 1)"
                                        class="px-2.5 py-1 text-ink-secondary hover:text-ink-primary font-bold hover:bg-border-soft transition-colors cursor-pointer rounded-r-md"
                                    >
                                        +
                                    </button>
                                </div>

                                <!-- Line Total and Delete -->
                                <div class="text-right flex items-center gap-sm shrink-0">
                                    <div class="min-w-[100px]">
                                        <p class="font-bold text-ink-primary font-mono text-sm">{{ formatCurrency(item.line_total) }}</p>
                                    </div>
                                    <button
                                        @click="removeItem(index)"
                                        class="p-1.5 text-semantic-danger-soft hover:text-semantic-danger rounded-md hover:bg-semantic-danger-soft/10 transition-colors cursor-pointer"
                                        title="Hapus"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Check-out and Payments (Fixed Height Layout) -->
            <div class="w-[420px] bg-white border-l border-border-soft shadow-xl shrink-0 flex flex-col justify-between overflow-hidden">
                <div class="flex-1 overflow-y-auto p-6 space-y-6">
                    <h3 class="text-base font-bold text-ink-primary border-b border-border-soft pb-3">Kalkulasi & Pembayaran</h3>

                    <!-- Totals Summary -->
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between text-ink-secondary">
                            <span>Subtotal</span>
                            <span class="font-mono">{{ formatCurrency(subtotal) }}</span>
                        </div>
                        <div class="flex justify-between text-semantic-danger font-medium">
                            <span>Diskon</span>
                            <span class="font-mono">-{{ formatCurrency(discountAmount) }}</span>
                        </div>
                        <div class="flex justify-between text-ink-secondary">
                            <span>Pajak ({{ taxRate }}%)</span>
                            <span class="font-mono">{{ formatCurrency(taxAmount) }}</span>
                        </div>
                        <div class="border-t border-border-soft pt-3 flex justify-between text-lg font-extrabold text-ink-primary">
                            <span>Total Akhir</span>
                            <span class="font-mono text-brand">{{ formatCurrency(grandTotal) }}</span>
                        </div>
                    </div>

                    <!-- Payment Setup -->
                    <div class="space-y-4 pt-4 border-t border-border-soft">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-ink-secondary mb-2">Metode Pembayaran</label>
                            <select
                                v-model="paymentMethod"
                                class="w-full px-base py-md rounded-md border border-border-strong bg-white text-ink-primary font-semibold text-sm focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent cursor-pointer"
                            >
                                <option value="CASH">Tunai (Cash)</option>
                                <option value="CARD">Debit / Kredit (Card)</option>
                                <option value="TRANSFER">Transfer Bank</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-ink-secondary mb-2">Jumlah Dibayar (Uang Masuk)</label>
                            <input
                                v-model.number="amountPaid"
                                type="number"
                                class="w-full px-base py-md rounded-md border border-border-strong bg-white text-ink-primary font-mono text-sm focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent font-bold"
                            />
                        </div>

                        <!-- Quick Cash Buttons -->
                        <div v-if="paymentMethod === 'CASH'" class="grid grid-cols-3 gap-2">
                            <button @click="quickPay(grandTotal)" class="text-xs bg-surface-main hover:bg-border-soft border border-border-soft py-1.5 rounded text-ink-primary font-mono font-semibold cursor-pointer transition-colors">Uang Pas</button>
                            <button @click="quickPay(50000)" class="text-xs bg-surface-main hover:bg-border-soft border border-border-soft py-1.5 rounded text-ink-primary font-mono font-semibold cursor-pointer transition-colors">50.000</button>
                            <button @click="quickPay(100000)" class="text-xs bg-surface-main hover:bg-border-soft border border-border-soft py-1.5 rounded text-ink-primary font-mono font-semibold cursor-pointer transition-colors">100.000</button>
                            <button @click="quickPay(200000)" class="text-xs bg-surface-main hover:bg-border-soft border border-border-soft py-1.5 rounded text-ink-primary font-mono font-semibold cursor-pointer transition-colors">200.000</button>
                            <button @click="quickPay(500000)" class="text-xs bg-surface-main hover:bg-border-soft border border-border-soft py-1.5 rounded text-ink-primary font-mono font-semibold cursor-pointer transition-colors">500.000</button>
                            <button @click="quickPay(1000000)" class="text-xs bg-surface-main hover:bg-border-soft border border-border-soft py-1.5 rounded text-ink-primary font-mono font-semibold cursor-pointer transition-colors">1.000.000</button>
                        </div>

                        <!-- Calculation Message Box -->
                        <div :class="amountPaid >= grandTotal ? 'bg-semantic-success-soft/20 text-semantic-success' : 'bg-semantic-danger-soft/20 text-semantic-danger'" class="p-4 rounded-md transition-colors flex justify-between items-center shrink-0">
                            <span class="text-xs font-bold uppercase tracking-wider">{{ amountPaid >= grandTotal ? 'Kembalian:' : 'Kurang:' }}</span>
                            <span class="text-xl font-extrabold font-mono">{{ formatCurrency(Math.abs(amountPaid - grandTotal)) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Footer Checkout Button Area -->
                <div class="p-6 border-t border-border-soft bg-surface-main shrink-0 space-y-3">
                    <button
                        @click="completeSale"
                        :disabled="cartItems.length === 0 || amountPaid < grandTotal"
                        class="w-full bg-brand hover:bg-brand/90 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold py-3.5 rounded-md transition-colors cursor-pointer text-sm shadow-sm"
                    >
                        Selesaikan Transaksi (Complete)
                    </button>
                    <div class="grid grid-cols-2 gap-3">
                        <button
                            @click="holdTransaction"
                            :disabled="cartItems.length === 0"
                            class="bg-white hover:bg-surface-subtle disabled:opacity-50 disabled:cursor-not-allowed border border-border-soft text-ink-primary font-semibold py-2 rounded-md transition-colors cursor-pointer text-xs"
                        >
                            Hold Bill
                        </button>
                        <button
                            @click="cancelTransaction"
                            :disabled="cartItems.length === 0"
                            class="bg-semantic-danger-soft text-semantic-danger hover:bg-semantic-danger-soft/80 disabled:opacity-50 border border-transparent font-semibold py-2 rounded-md transition-colors cursor-pointer text-xs"
                        >
                            Batalkan
                        </button>
                    </div>
                </div>
            </div>

        </div>

        <!-- Success Checkout Receipt Modal -->
        <div v-if="showSuccessModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4 animate-fade-in">
            <div class="bg-surface-card rounded-card border border-border-soft shadow-2xl max-w-md w-full p-6 space-y-6 transform scale-100 transition-transform">
                <div class="text-center">
                    <div class="w-12 h-12 bg-semantic-success-soft text-semantic-success rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-ink-primary">Transaksi Berhasil!</h3>
                    <p class="text-xs text-ink-secondary mt-1">Pembayaran telah diterima dan dicatat.</p>
                </div>

                <!-- Simple Print Receipt Representation -->
                <div class="border border-border-soft bg-surface-main p-4 rounded-md font-mono text-xs text-ink-primary space-y-3">
                    <div class="text-center border-b border-border-soft pb-2 space-y-1">
                        <h4 class="font-bold">ERP POS RETAIL</h4>
                        <p class="text-[10px] text-ink-secondary">Jl. Retail Raya No. 42</p>
                    </div>
                    <div class="space-y-1 text-[11px]">
                        <p class="flex justify-between"><span>No Transaksi:</span> <span>{{ receiptData?.transactionNo }}</span></p>
                        <p class="flex justify-between"><span>Tanggal:</span> <span>{{ receiptData?.date }}</span></p>
                        <p class="flex justify-between"><span>Kasir:</span> <span>Superadmin</span></p>
                        <p class="flex justify-between"><span>Metode:</span> <span>{{ receiptData?.paymentMethod }}</span></p>
                    </div>
                    <div class="border-t border-b border-border-soft py-2 space-y-1.5 text-[11px]">
                        <div v-for="(item, idx) in receiptData?.items" :key="idx" class="flex justify-between">
                            <div class="max-w-[200px] truncate">
                                <span>{{ item.product_name }}</span>
                                <p class="text-[10px] text-ink-secondary">{{ item.quantity }} x {{ formatCurrency(item.unit_price) }}</p>
                            </div>
                            <span>{{ formatCurrency(item.line_total) }}</span>
                        </div>
                    </div>
                    <div class="space-y-1 text-[11px] font-bold">
                        <p class="flex justify-between font-normal text-ink-secondary"><span>Subtotal:</span> <span>{{ formatCurrency(receiptData?.subtotal) }}</span></p>
                        <p class="flex justify-between font-normal text-semantic-danger"><span>Diskon:</span> <span>-{{ formatCurrency(receiptData?.discountAmount) }}</span></p>
                        <p class="flex justify-between font-normal text-ink-secondary"><span>Pajak:</span> <span>{{ formatCurrency(receiptData?.taxAmount) }}</span></p>
                        <p class="flex justify-between text-brand border-t border-border-soft pt-1"><span>TOTAL:</span> <span>{{ formatCurrency(receiptData?.grandTotal) }}</span></p>
                        <p class="flex justify-between font-normal border-t border-border-soft pt-1"><span>Dibayar:</span> <span>{{ formatCurrency(receiptData?.amountPaid) }}</span></p>
                        <p class="flex justify-between text-semantic-success"><span>Kembalian:</span> <span>{{ formatCurrency(receiptData?.change) }}</span></p>
                    </div>
                    <div class="text-center text-[10px] text-ink-secondary border-t border-border-soft pt-2">
                        Terima kasih atas kunjungan Anda!
                    </div>
                </div>

                <div class="flex gap-md">
                    <button
                        @click="resetSale"
                        class="flex-1 bg-surface-main hover:bg-border-soft border border-border-soft text-ink-primary font-bold py-2.5 rounded-md transition-colors cursor-pointer text-xs"
                    >
                        Tutup & Transaksi Baru
                    </button>
                    <button
                        @click="window.print()"
                        class="flex-1 bg-brand hover:bg-brand/90 text-white font-bold py-2.5 rounded-md transition-colors cursor-pointer text-xs flex items-center justify-center gap-xs"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Cetak Struk
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
