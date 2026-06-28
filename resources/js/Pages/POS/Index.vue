<script>
import POSLayout from "@/Layouts/POSLayout.vue";
export default { layout: POSLayout };
</script>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from "vue";
import { Head, usePage, router } from "@inertiajs/vue3";
import axios from "axios"; // ✅ FIXED: Import axios
import toast from "@/Utils/toast";
import { queueTransaction } from "@/Utils/offlineDb";
import { formatPrice } from "@/Utils/formatPrice";

// Components
import ProductGrid from "./Components/ProductGrid.vue";
import CartPanel from "./Components/CartPanel.vue";
import PaymentPanel from "./Components/PaymentPanel.vue";
import CustomerSelect from "./Components/CustomerSelect.vue";
import SummaryFooter from "./Components/SummaryFooter.vue";
import VoidReasonModal from "./Components/VoidReasonModal.vue";
import ShiftOpener from "./Components/ShiftOpener.vue";
import SessionOpener from "./Components/SessionOpener.vue";
import NumpadModal from "./Components/NumpadModal.vue";
import PromoBanner from "./Components/PromoBanner.vue";

// Composables
import { useBarcodeScanner } from "@/Composables/useBarcodeScanner";
import { useCart } from "@/Composables/useCart";
import { usePricingPreview } from "@/Composables/usePricingPreview";
import { usePayment } from "@/Composables/usePayment";
import { useKeyboardShortcuts } from "@/Composables/useKeyboardShortcuts";

// ═══════════════════════════════════════════════════════════
// PROPS
// ═══════════════════════════════════════════════════════════
const props = defineProps({
    carts: { type: Array, default: () => [] },
    carts_total: { type: Number, default: 0 },
    heldCarts: { type: Array, default: () => [] },
    customers: { type: Array, default: () => [] },
    products: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    initialPricingPreview: {
        type: Object,
        default: () => ({ items: [], summary: {}, applied_promotions: [] }),
    },
    paymentGateways: { type: Array, default: () => [] },
    paymentMethods: { type: Array, default: () => [] },
    defaultPaymentGateway: { type: String, default: "cash" },
    bankAccounts: { type: Array, default: () => [] },
    loyaltyTierOptions: { type: Array, default: () => [] },
    currentLocationId: { type: [Number, null], default: null },
});

// ✅ FIXED: Pakai usePage() dengan benar
const page = usePage();
const auth = computed(() => page.props.auth);
const errors = computed(() => page.props.errors || {});
const activeCashierShift = computed(() => page.props.activeCashierShift);

// ═══════════════════════════════════════════════════════════
// LOCAL STATE
// ═══════════════════════════════════════════════════════════
const localShift = ref(activeCashierShift.value || null);
const activeSession = ref(null);
const isCheckingSession = ref(true);

const searchQuery = ref("");
const selectedCategory = ref(null);
const selectedCustomer = ref(null);
const discountInput = ref("");
const shippingInput = ref("");
const redeemPointsInput = ref("");
const selectedVoucherId = ref("");
const isSubmitting = ref(false);
const mobileView = ref("products");
const numpadOpen = ref(false);
const showShortcuts = ref(false);
const voidTarget = ref(null);
const productGridRef = ref(null);

// ═══════════════════════════════════════════════════════════
// LIFECYCLE
// ═══════════════════════════════════════════════════════════
onMounted(async () => {
    // ✅ FIXED: Fetch active session dengan proper error handling
    await checkActiveSession();
});

// ═══════════════════════════════════════════════════════════
// SESSION MANAGEMENT
// ═══════════════════════════════════════════════════════════
const checkActiveSession = async () => {
    isCheckingSession.value = true;
    try {
        const { data } = await axios.get(route("pos.sessions.active"));
        if (data?.data) {
            activeSession.value = data.data;
        }
    } catch (error) {
        // Jika route belum ada atau error, anggap tidak ada sesi
        console.warn("Session check failed:", error.message);
        activeSession.value = null;
    } finally {
        isCheckingSession.value = false;
    }
};

const onShiftOpened = (shift) => {
    localShift.value = shift;
    // Setelah shift dibuka, cek session
    checkActiveSession();
};

const handleSessionOpened = (session) => {
    activeSession.value = session;
};

const handleCloseSession = async () => {
    if (!activeSession.value) return;

    if (!confirm("Yakin ingin menutup sesi kasir?")) return;

    const closingCash = prompt("Masukkan uang tunai di laci kasir:");
    if (closingCash === null) return;

    try {
        const { data } = await axios.post(
            route("pos.sessions.close", activeSession.value.id),
            { closing_cash: Number(closingCash) || 0 },
        );

        if (data?.success !== false) {
            toast.success("Sesi kasir berhasil ditutup");
            activeSession.value = null;
            router.reload();
        } else {
            toast.error(data?.message || "Gagal menutup sesi");
        }
    } catch (error) {
        toast.error(
            error.response?.data?.message || "Gagal menutup sesi kasir",
        );
    }
};

// ═══════════════════════════════════════════════════════════
// COMPOSABLES
// ═══════════════════════════════════════════════════════════
const {
    addToCart,
    updateQty,
    removeFromCart,
    holdCart,
    isHolding,
    removingItemId,
    updatingCartId,
    addingProductId,
} = useCart();

const {
    pricingPreview,
    isLoadingPricing,
    pricingItemsByCartId,
    summary,
    appliedPromotions,
} = usePricingPreview(
    props.initialPricingPreview,
    computed(() => [
        props.carts,
        selectedCustomer.value,
        discountInput.value,
        shippingInput.value,
        redeemPointsInput.value,
        selectedVoucherId.value,
    ]),
);

const {
    paymentMode,
    paymentMethod,
    payLater,
    dueDate,
    cashInput,
    selectedBankAccount,
    paymentSplits,
    paymentOptions,
    isCashPayment,
    splitTotal,
    splitRemaining,
    isSplitComplete,
    switchMode,
    addSplit,
    updateSplitAmount,
    removeSplit,
    setPayableAmount,
    reset: resetPayment,
} = usePayment({
    defaultGateway: props.defaultPaymentGateway,
    gateways: props.paymentGateways,
});

// Sync payable to usePayment's payableAmount
watch(payable, (newVal) => {
    setPayableAmount(newVal);
}, { immediate: true });

// ═══════════════════════════════════════════════════════════
// COMPUTED
// ═══════════════════════════════════════════════════════════
const cartCount = computed(() =>
    props.carts.reduce((t, i) => t + Number(i.qty), 0),
);

const baseSubtotal = computed(() =>
    Number(summary.value?.base_subtotal ?? props.carts_total ?? 0),
);

const promoDiscount = computed(() =>
    Number(summary.value?.promo_discount_total ?? 0),
);

const voucherDiscount = computed(() =>
    Number(summary.value?.voucher_discount_total ?? 0),
);

const loyaltyDiscount = computed(() =>
    Number(summary.value?.loyalty_discount_total ?? 0),
);

const taxTotal = computed(() => Number(summary.value?.tax_total ?? 0));
const payable = computed(() => Number(summary.value?.grand_total ?? 0));

const allProducts = computed(() =>
    props.products.filter((p) => {
        const matchCat =
            selectedCategory.value === null ||
            Number(p.category_id) === Number(selectedCategory.value);
        const matchSearch =
            !searchQuery.value ||
            p.title?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            p.barcode?.toLowerCase().includes(searchQuery.value.toLowerCase());
        return matchCat && matchSearch;
    }),
);

// ✅ FIXED: Permission check tanpa $page
const canOpenShift = computed(() => {
    const permissions = auth.value?.can || {};
    return permissions["cashier-shifts-open"] ?? true; // default true jika tidak ada permission system
});

// ✅ NEW: Computed untuk menentukan state tampilan
const viewState = computed(() => {
    if (isCheckingSession.value) return "loading";
    if (!localShift.value) return "shift";
    if (!activeSession.value) return "session";
    return "pos";
});

// ═══════════════════════════════════════════════════════════
// BARCODE SCANNER
// ═══════════════════════════════════════════════════════════
useBarcodeScanner((barcode) => {
    const product = props.products.find(
        (p) => p.barcode?.toLowerCase() === barcode.toLowerCase(),
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

// ═══════════════════════════════════════════════════════════
// KEYBOARD SHORTCUTS
// ═══════════════════════════════════════════════════════════
useKeyboardShortcuts({
    focusSearch: () => productGridRef.value?.focusSearch(),
    openNumpad: () => (numpadOpen.value = true),
    submitTransaction: () => {
        if (props.carts.length && selectedCustomer.value) handleSubmit();
    },
    toggleMobileView: () => {
        mobileView.value =
            mobileView.value === "products" ? "cart" : "products";
    },
    showShortcuts: () => (showShortcuts.value = !showShortcuts.value),
    closeAll: () => {
        numpadOpen.value = false;
        showShortcuts.value = false;
        voidTarget.value = null;
    },
});

// ═══════════════════════════════════════════════════════════
// HANDLERS
// ═══════════════════════════════════════════════════════════
const handleVoidItem = (item) => {
    voidTarget.value = { id: item.id, productName: item.product?.title };
};

const confirmVoid = (reason) => {
    removeFromCart(voidTarget.value.id, reason);
    voidTarget.value = null;
};

const handleSubmit = () => {
    if (!props.carts.length) return toast.error("Keranjang masih kosong");
    if (!selectedCustomer.value?.id)
        return toast.error("Pilih pelanggan terlebih dahulu");
    if (payLater.value && !dueDate.value)
        return toast.error("Isi tanggal jatuh tempo");

    if (paymentMode.value === "split" && !payLater.value) {
        if (!isSplitComplete.value) {
            return toast.error(
                `Split payment kurang ${formatPrice(splitRemaining.value)}`,
            );
        }
    } else if (
        isCashPayment.value &&
        !payLater.value &&
        Number(cashInput.value) < payable.value
    ) {
        return toast.error("Jumlah pembayaran kurang");
    }

    if (paymentMethod.value === "bank_transfer" && !selectedBankAccount.value) {
        return toast.error("Pilih rekening bank tujuan");
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
        location_id: props.currentLocationId,
        payment_gateway:
            paymentMode.value === "single" && !payLater.value
                ? isCashPayment.value
                    ? null
                    : paymentMethod.value
                : null,
        cash:
            paymentMode.value === "single" && isCashPayment.value
                ? Number(cashInput.value)
                : payLater.value
                  ? 0
                  : payable.value,
        change:
            isCashPayment.value && !payLater.value
                ? Math.max(Number(cashInput.value) - payable.value, 0)
                : 0,
        bank_account_id:
            paymentMethod.value === "bank_transfer"
                ? selectedBankAccount.value?.id
                : null,
        payment_splits:
            paymentMode.value === "split" && !payLater.value
                ? paymentSplits.value
                : null,
        applied_promotions: appliedPromotions.value.map((p) => ({
            promotion_id: p.promotion_id,
            promotion_code: p.promotion_code,
            discount_amount: p.discount_amount,
        })),
    };

    if (!navigator.onLine) {
        queueTransaction(payload).then(() => {
            toast.success("Transaksi disimpan offline");
            resetAll();
        });
        isSubmitting.value = false;
        return;
    }

    router.post(route("pos.checkout"), payload, {
        onSuccess: () => {
            toast.success("Transaksi berhasil!");
            resetAll();
        },
        onError: (errors) => {
            const errorMsg =
                errors?.stock ||
                errors?.redeem_points ||
                errors?.session ||
                errors?.transaction?.[0] ||
                "Gagal menyimpan transaksi";
            toast.error(errorMsg);
            isSubmitting.value = false;
        },
    });
};

const resetAll = () => {
    discountInput.value = "";
    shippingInput.value = "";
    redeemPointsInput.value = "";
    selectedVoucherId.value = "";
    selectedCustomer.value = null;
    resetPayment();
    isSubmitting.value = false;
};

const handleNumpadConfirm = (value) => {
    cashInput.value = String(value);
    numpadOpen.value = false;
};

const handleRecallHeld = (id) => {
    router.post(route("pos.hold.recall", id));
};

const handleClearCart = () => {
    router.delete(route("pos.cart.clear"));
};
</script>

<template>
    <Head title="Transaksi POS" />

    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- STATE 1: LOADING (cek sesi aktif) -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <div
        v-if="viewState === 'loading'"
        class="min-h-screen flex items-center justify-center bg-surface-main"
    >
        <div class="text-center">
            <div
                class="w-12 h-12 border-4 border-brand/30 border-t-brand rounded-full animate-spin mx-auto mb-4"
            />
            <p class="text-ink-muted">Memuat sesi kasir...</p>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- STATE 2: SHIFT OPENER (jika shift belum dibuka) -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <ShiftOpener
        v-else-if="viewState === 'shift'"
        @opened="onShiftOpened"
        :errors="errors"
        :can-open-shift="canOpenShift"
    />

    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- STATE 3: SESSION OPENER (jika sesi belum dibuka) -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <SessionOpener
        v-else-if="viewState === 'session'"
        @opened="handleSessionOpened"
        :errors="errors"
    />

    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- STATE 4: MAIN POS INTERFACE -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <div v-else class="h-[calc(100vh-4rem)] flex flex-col bg-surface-main">
        <!-- Session Info Bar -->
        <div
            v-if="activeSession"
            class="bg-accent-mint-soft border-b border-accent-mint/30 px-4 py-2 flex items-center justify-between flex-shrink-0"
        >
            <div class="flex items-center gap-3 text-xs">
                <span class="font-bold text-accent-mint">
                    🟢 {{ activeSession.session_no }}
                </span>
                <span class="text-ink-muted">
                    {{ activeSession.shift_name }} ·
                    {{ activeSession.total_transactions || 0 }} transaksi ·
                    {{ formatPrice(activeSession.total_sales || 0) }}
                </span>
            </div>
            <button
                @click="handleCloseSession"
                class="px-3 py-1 rounded-lg bg-semantic-danger-soft text-semantic-danger text-xs font-semibold hover:bg-semantic-danger/20 transition"
            >
                🔒 Tutup Sesi
            </button>
        </div>

        <!-- Mobile Tab Switcher -->
        <div
            class="lg:hidden flex border-b border-border-soft bg-surface-card flex-shrink-0"
        >
            <button
                @click="mobileView = 'products'"
                :class="[
                    'flex-1 py-3 text-sm font-medium transition',
                    mobileView === 'products'
                        ? 'text-brand border-b-2 border-brand'
                        : 'text-ink-muted',
                ]"
            >
                🛒 Produk
            </button>
            <button
                @click="mobileView = 'cart'"
                :class="[
                    'flex-1 py-3 text-sm font-medium transition relative',
                    mobileView === 'cart'
                        ? 'text-brand border-b-2 border-brand'
                        : 'text-ink-muted',
                ]"
            >
                🧾 Keranjang
                <span
                    v-if="cartCount"
                    class="ml-1 px-1.5 min-w-[20px] h-5 inline-flex items-center justify-center text-[11px] font-bold bg-brand text-white rounded-full"
                >
                    {{ cartCount }}
                </span>
            </button>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col lg:flex-row overflow-hidden">
            <!-- Left Panel: Products -->
            <div
                :class="[
                    'flex-1 overflow-hidden flex flex-col',
                    mobileView !== 'products' ? 'hidden lg:flex' : 'flex',
                ]"
            >
                <PromoBanner />
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

            <!-- Right Panel: Cart + Payment + Summary -->
            <div
                :class="[
                    'w-full lg:w-[420px] xl:w-[480px] flex flex-col bg-surface-card border-l border-border-soft min-h-0 overflow-hidden',
                    mobileView !== 'cart' ? 'hidden lg:flex' : 'flex',
                ]"
            >
                <!-- Customer Select -->
                <div class="p-3 border-b border-border-soft flex-shrink-0">
                    <CustomerSelect
                        :customers="customers"
                        :selected="selectedCustomer"
                        @select="selectedCustomer = $event"
                        :error="errors?.customer_id"
                    />
                </div>

                <!-- Cart Panel -->
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
                        @recall-held="handleRecallHeld"
                        @clear-cart="handleClearCart"
                    />
                </div>

                <!-- Payment Panel -->
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
                    :available-points="
                        pricingPreview?.summary?.available_loyalty_points || 0
                    "
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

                <!-- Summary Footer -->
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
                    :base-subtotal="baseSubtotal"
                    :promo-discount="promoDiscount"
                    :voucher-discount="voucherDiscount"
                    :loyalty-discount="loyaltyDiscount"
                    :discount-manual="Number(discountInput) || 0"
                    :shipping="Number(shippingInput) || 0"
                    :tax-total="taxTotal"
                    :applied-promotions="appliedPromotions"
                    @submit="handleSubmit"
                />
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- MODALS -->
    <!-- ═══════════════════════════════════════════════════════════ -->
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
