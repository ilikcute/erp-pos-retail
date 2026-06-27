<script setup>
import { ref, computed, watch } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import toast from '@/Utils/toast';
import { queueTransaction } from '@/Utils/offlineDb';
import { formatPrice } from '@/Utils/formatPrice';

import POSLayout from '@/Layouts/POSLayout.vue';

defineOptions({ layout: POSLayout });
import ProductGrid from './Components/ProductGrid.vue';
import CartPanel from './Components/CartPanel.vue';
import PaymentPanel from './Components/PaymentPanel.vue';
import CustomerSelect from './Components/CustomerSelect.vue';
import SummaryFooter from './Components/SummaryFooter.vue';
import VoidReasonModal from './Components/VoidReasonModal.vue';
import ShiftOpener from './Components/ShiftOpener.vue';
import NumpadModal from './Components/NumpadModal.vue';

import { useBarcodeScanner } from '@/Composables/useBarcodeScanner';
import { useCart } from '@/Composables/useCart';
import { usePricingPreview } from '@/Composables/usePricingPreview';
import { usePayment } from '@/Composables/usePayment';
import { useKeyboardShortcuts } from '@/Composables/useKeyboardShortcuts';

// === Props dari backend ===
const props = defineProps({
    carts: { type: Array, default: () => [] },
    carts_total: { type: Number, default: 0 },
    heldCarts: { type: Array, default: () => [] },
    customers: { type: Array, default: () => [] },
    products: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    initialPricingPreview: { type: Object, default: () => ({ items: [], summary: {} }) },
    paymentGateways: { type: Array, default: () => [] },
    defaultPaymentGateway: { type: String, default: 'cash' },
    bankAccounts: { type: Array, default: () => [] },
    loyaltyTierOptions: { type: Array, default: () => [] },
});

const { auth, errors, activeCashierShift } = usePage().props;

// Local shift state — works in frontend-only mode (no backend) and respects real backend shift.
const localShift = ref(activeCashierShift || null);
const onShiftOpened = (shift) => { localShift.value = shift; };

// === State lokal ===
const searchQuery = ref('');
const selectedCategory = ref(null);
const selectedCustomer = ref(null);
const discountInput = ref('');
const shippingInput = ref('');
const redeemPointsInput = ref('');
const selectedVoucherId = ref('');
const isSubmitting = ref(false);
const mobileView = ref('products');
const numpadOpen = ref(false);
const showShortcuts = ref(false);
const voidTarget = ref(null); // { id, productName }
const productGridRef = ref(null);

// === Composables ===
const { addToCart, updateQty, removeFromCart, holdCart, isHolding, removingItemId, updatingCartId, addingProductId } = useCart();

const { pricingPreview, isLoadingPricing, pricingItemsByCartId, summary } = usePricingPreview(
    props.initialPricingPreview,
    computed(() => [
        props.carts,
        selectedCustomer.value,
        discountInput.value,
        shippingInput.value,
        redeemPointsInput.value,
        selectedVoucherId.value,
    ])
);

const {
    paymentMode, paymentMethod, payLater, dueDate, cashInput,
    selectedBankAccount, paymentSplits, paymentOptions, isCashPayment,
    splitTotal, splitRemaining, isSplitComplete,
    switchMode, addSplit, updateSplitAmount, removeSplit,
    reset: resetPayment,
} = usePayment({
    defaultGateway: props.defaultPaymentGateway,
    gateways: props.paymentGateways,
});

// === Computed ===
const cartCount = computed(() => props.carts.reduce((t, i) => t + Number(i.qty), 0));
const baseSubtotal = computed(() => Number(summary.value?.base_subtotal ?? props.carts_total ?? 0));
const promoDiscount = computed(() => Number(summary.value?.promo_discount_total ?? 0));
const voucherDiscount = computed(() => Number(summary.value?.voucher_discount_total ?? 0));
const loyaltyDiscount = computed(() => Number(summary.value?.loyalty_discount_total ?? 0));
const taxTotal = computed(() => Number(summary.value?.tax_total ?? 0));
const payable = computed(() => Number(summary.value?.grand_total ?? 0));

// Sync payable ke payment composable
watch(payable, (v) => {
    // Gunakan internal setter jika ada
}, { immediate: true });

// === Barcode Scanner ===
useBarcodeScanner((barcode) => {
    const product = props.products.find(
        (p) => p.barcode?.toLowerCase() === barcode.toLowerCase()
    );
    if (product) {
        if (product.stock > 0) {
            addToCart(product);
        } else {
            toast.error(`${product.title} stok habis`);
        }
    } else {
        toast.error(`Produk tidak ditemukan: ${barcode}`);
    }
});

// === Keyboard Shortcuts ===
useKeyboardShortcuts({
    focusSearch: () => productGridRef.value?.focusSearch(),
    openNumpad: () => (numpadOpen.value = true),
    submitTransaction: () => {
        if (props.carts.length && selectedCustomer.value) handleSubmit();
    },
    toggleMobileView: () => (mobileView.value = mobileView.value === 'products' ? 'cart' : 'products'),
    showShortcuts: () => (showShortcuts.value = !showShortcuts.value),
    closeAll: () => {
        numpadOpen.value = false;
        showShortcuts.value = false;
        voidTarget.value = null;
    },
});

// === Handlers ===
const handleVoidItem = (item) => {
    voidTarget.value = { id: item.id, productName: item.product?.title };
};

const confirmVoid = (reason) => {
    removeFromCart(voidTarget.value.id, reason);
    voidTarget.value = null;
};

const handleSubmit = () => {
    if (!props.carts.length) return toast.error('Keranjang masih kosong');
    if (!selectedCustomer.value?.id) return toast.error('Pilih pelanggan terlebih dahulu');
    if (payLater.value && !dueDate.value) return toast.error('Isi tanggal jatuh tempo');

    // Validasi split payment
    if (paymentMode.value === 'split' && !payLater.value) {
        if (!isSplitComplete.value) {
            return toast.error(`Split payment kurang ${formatPrice(splitRemaining.value)}`);
        }
    } else if (isCashPayment.value && !payLater.value && Number(cashInput.value) < payable.value) {
        return toast.error('Jumlah pembayaran kurang');
    }

    if (paymentMethod.value === 'bank_transfer' && !selectedBankAccount.value) {
        return toast.error('Pilih rekening bank tujuan');
    }

    isSubmitting.value = true;

    const payload = {
        customer_id: selectedCustomer.value.id,
        discount: Number(discountInput.value) || 0,
        redeem_points: Number(redeemPointsInput.value) || 0,
        customer_voucher_id: selectedVoucherId.value || null,
        shipping_cost: Number(shippingInput.value) || 0,
        grand_total: payable.value,
        pay_later: payLater.value,
        due_date: payLater.value ? dueDate.value : null,
        // Single payment
        payment_gateway: paymentMode.value === 'single' && !payLater.value
            ? (isCashPayment.value ? null : paymentMethod.value)
            : null,
        cash: paymentMode.value === 'single' && isCashPayment.value ? Number(cashInput.value) : (payLater.value ? 0 : payable.value),
        change: isCashPayment.value && !payLater.value ? Math.max(Number(cashInput.value) - payable.value, 0) : 0,
        bank_account_id: paymentMethod.value === 'bank_transfer' ? selectedBankAccount.value?.id : null,
        // Split payment
        payment_splits: paymentMode.value === 'split' && !payLater.value ? paymentSplits.value : null,
    };

    // Offline queue
    if (!navigator.onLine) {
        queueTransaction(payload).then(() => {
            toast.success('Transaksi disimpan offline');
            resetAll();
        });
        isSubmitting.value = false;
        return;
    }

    router.post(route('transactions.store'), payload, {
        onSuccess: () => {
            toast.success('Transaksi berhasil!');
            resetAll();
        },
        onError: () => {
            toast.error('Gagal menyimpan transaksi');
            isSubmitting.value = false;
        },
    });
};

const resetAll = () => {
    discountInput.value = '';
    shippingInput.value = '';
    redeemPointsInput.value = '';
    selectedVoucherId.value = '';
    selectedCustomer.value = null;
    resetPayment();
    isSubmitting.value = false;
};

const handleNumpadConfirm = (value) => {
    cashInput.value = String(value);
    numpadOpen.value = false;
};

// Filter products
const allProducts = computed(() =>
    props.products.filter((p) => {
        const matchCat = selectedCategory.value === null || Number(p.category_id) === Number(selectedCategory.value);
        const matchSearch = !searchQuery.value ||
            p.title?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            p.barcode?.toLowerCase().includes(searchQuery.value.toLowerCase());
        return matchCat && matchSearch;
    })
);
</script>

<template>
    <Head title="Transaksi POS" />

    <ShiftOpener
        v-if="!localShift"
            @opened="onShiftOpened"
        :errors="errors"
        :can-open-shift="$page.props.auth?.can?.['cashier-shifts-open']"
    />

    <div v-else class="h-[calc(100vh-4rem)] flex flex-col lg:flex-row bg-surface-main">
        <!-- Mobile Tab Switcher -->
        <div class="lg:hidden flex border-b border-border-soft bg-surface-card flex-shrink-0">
            <button
                @click="mobileView = 'products'"
                :class="['flex-1 py-3 text-sm font-medium transition', mobileView === 'products' ? 'text-brand border-b-2 border-brand' : 'text-ink-muted']"
            >🛒 Produk</button>
            <button
                @click="mobileView = 'cart'"
                :class="['flex-1 py-3 text-sm font-medium transition relative', mobileView === 'cart' ? 'text-brand border-b-2 border-brand' : 'text-ink-muted']"
            >
                🧾 Keranjang
                <span v-if="cartCount" class="ml-1 px-1.5 min-w-[20px] h-5 inline-flex items-center justify-center text-[11px] font-bold bg-brand text-white rounded-full">
                    {{ cartCount }}
                </span>
            </button>
        </div>

        <!-- Left: Products -->
        <div :class="['flex-1 overflow-hidden', mobileView !== 'products' ? 'hidden lg:flex lg:flex-col' : 'flex flex-col']">
            <ProductGrid
                ref="productGridRef"
                :products="allProducts"
                :categories="categories"
                :selected-category="selectedCategory"
                :search-query="searchQuery"
                :adding-product-id="addingProductId"
                @update:selected-category="selectedCategory = $event"
                @update:search-query="searchQuery = $event"
                @add-to-cart="addToCart"
            />
        </div>

        <!-- Right: Cart + Payment -->
        <div :class="['w-full lg:w-[420px] xl:w-[480px] flex flex-col bg-surface-card border-l border-border-soft min-h-0 overflow-hidden', mobileView !== 'cart' ? 'hidden lg:flex' : 'flex']">
            <!-- Customer -->
            <div class="p-3 border-b border-border-soft flex-shrink-0">
                <CustomerSelect
                    :customers="customers"
                    :selected="selectedCustomer"
                    @select="selectedCustomer = $event"
                    :error="errors?.customer_id"
                />
            </div>

            <!-- Cart -->
            <div class="flex-1 min-h-0 overflow-hidden flex flex-col">
                <CartPanel
                    :carts="carts"
                    :pricing-items-by-cart-id="pricingItemsByCartId"
                    :cart-count="cartCount"
                    :held-carts="heldCarts"
                    :has-active-cart="carts.length > 0"
                    :is-holding="isHolding"
                    :removing-item-id="removingItemId"
                    :updating-cart-id="updatingCartId"
                    @update-qty="updateQty"
                    @void-item="handleVoidItem"
                    @hold-cart="holdCart()"
                    @recall-held="(id) => router.post(route('transactions.recallHold', id))"
                    @clear-cart="router.delete(route('transactions.clearCart'))"
                />
            </div>

            <!-- Payment -->
            <PaymentPanel
                :payment-mode="paymentMode"
                :payment-method="paymentMethod"
                :payment-options="paymentOptions"
                :pay-later="payLater"
                :due-date="dueDate"
                :cash-input="cashInput"
                :selected-bank-account="selectedBankAccount"
                :bank-accounts="bankAccounts"
                :payment-splits="paymentSplits"
                :split-total="splitTotal"
                :split-remaining="splitRemaining"
                :is-split-complete="isSplitComplete"
                :is-cash-payment="isCashPayment"
                :payable="payable"
                :promo-discount="promoDiscount"
                :voucher-discount="voucherDiscount"
                :loyalty-discount="loyaltyDiscount"
                :discount-manual="discountInput"
                :shipping="shippingInput"
                :tax-total="taxTotal"
                :base-subtotal="baseSubtotal"
                :applied-groups="pricingPreview?.applied_groups || []"
                :selected-customer="selectedCustomer"
                :redeem-points-input="redeemPointsInput"
                :selected-voucher-id="selectedVoucherId"
                :eligible-vouchers="pricingPreview?.eligible_vouchers || []"
                :available-points="pricingPreview?.summary?.available_loyalty_points || 0"
                :is-loading-pricing="isLoadingPricing"
                @update:payment-mode="paymentMode = $event"
                @update:payment-method="paymentMethod = $event"
                @update:pay-later="payLater = $event"
                @update:due-date="dueDate = $event"
                @update:cash-input="cashInput = $event"
                @update:selected-bank-account="selectedBankAccount = $event"
                @update:redeem-points-input="redeemPointsInput = $event"
                @update:selected-voucher-id="selectedVoucherId = $event"
                @update:discount-manual="discountInput = $event"
                @update:shipping="shippingInput = $event"
                @switch-mode="switchMode"
                @add-split="addSplit"
                @update-split-amount="updateSplitAmount"
                @remove-split="removeSplit"
            />

            <!-- Summary + Submit -->
            <SummaryFooter
                :carts="carts"
                :selected-customer="selectedCustomer"
                :payable="payable"
                :cash-input="cashInput"
                :is-cash-payment="isCashPayment"
                :pay-later="payLater"
                :payment-mode="paymentMode"
                :is-split-complete="isSplitComplete"
                :is-loading-pricing="isLoadingPricing"
                :is-submitting="isSubmitting"
                @submit="handleSubmit"
            />
        </div>
    </div>

    <!-- Modals -->
    <VoidReasonModal
        v-if="voidTarget"
        :product-name="voidTarget.productName"
        @confirm="confirmVoid"
        @close="voidTarget = null"
    />

    <NumpadModal
        v-if="numpadOpen"
        :initial-value="Number(cashInput) || 0"
        @confirm="handleNumpadConfirm"
        @close="numpadOpen = false"
    />
</template>
