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
import BaseModal from "@/Components/Modal/BaseModal.vue";
import FormInput from "@/Components/Form/FormInput.vue";
import FormTextarea from "@/Components/Form/FormTextarea.vue";
import BaseButton from "@/Components/Base/BaseButton.vue";

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
const discountInput = ref("0");
const shippingInput = ref("0");
const redeemPointsInput = ref("");
const selectedVoucherId = ref("");
const isSubmitting = ref(false);
const mobileView = ref("cart");
const numpadOpen = ref(false);
const showShortcuts = ref(false);
const voidTarget = ref(null);
const cartPanelRef = ref(null);
const showPaymentModal = ref(false);

watch(
    errors,
    (newErrors) => {
        if (newErrors && Object.keys(newErrors).length > 0) {
            const firstKey = Object.keys(newErrors)[0];
            toast.error(newErrors[firstKey]);
        }
    },
    { deep: true }
);

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
            if (data.data.shift_id) {
                localShift.value = {
                    id: data.data.shift_id,
                    name: data.data.shift_name,
                    shift_name: data.data.shift_name,
                };
            }
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
    if (session && session.shift_id) {
        localShift.value = {
            id: session.shift_id,
            name: session.shift_name,
            shift_name: session.shift_name,
        };
    }
};

const formatRupiahInput = (value) => {
    if (value === null || value === undefined || value === '') return '';
    const numberString = String(value).replace(/[^0-9]/g, '');
    if (!numberString) return '';
    return numberString.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
};

const parseRupiahInput = (formattedValue) => {
    if (!formattedValue) return 0;
    const cleanString = String(formattedValue).replace(/[^0-9]/g, '');
    return Number(cleanString) || 0;
};

const closingCashDisplay = ref('');

const onClosingCashInput = (e) => {
    const formatted = formatRupiahInput(e.target.value);
    closingCashDisplay.value = formatted;
    closeSessionForm.value.closing_cash = parseRupiahInput(formatted);
};

const showCloseSessionModal = ref(false);
const isClosingSession = ref(false);
const closeSessionForm = ref({
    closing_cash: 0,
    notes: "",
});

const showVarianceModal = ref(false);
const varianceForm = ref({
    reimbursement_amount: 0,
    variance_reason: '',
});
const reimbursementAmountDisplay = ref('');

const onReimbursementAmountInput = (e) => {
    const formatted = formatRupiahInput(e.target.value);
    reimbursementAmountDisplay.value = formatted;
    varianceForm.value.reimbursement_amount = parseRupiahInput(formatted);
};

const handleCloseSession = () => {
    if (!activeSession.value) return;
    
    // Set default closing cash to expected cash
    closeSessionForm.value = {
        closing_cash: activeSession.value.expected_cash || 0,
        notes: "",
    };
    closingCashDisplay.value = formatRupiahInput(activeSession.value.expected_cash || 0);
    showCloseSessionModal.value = true;
};

const submitCloseSession = async (bypassVariance = false) => {
    if (closeSessionForm.value.closing_cash < 0) {
        toast.error("Uang tutup tidak boleh negatif");
        return;
    }

    if (!activeSession.value) return;

    const diff = Number(closeSessionForm.value.closing_cash) - Number(activeSession.value.expected_cash);
    if (diff !== 0 && !bypassVariance) {
        varianceForm.value = {
            reimbursement_amount: 0,
            variance_reason: '',
        };
        reimbursementAmountDisplay.value = '';
        showVarianceModal.value = true;
        return;
    }

    isClosingSession.value = true;
    try {
        const payload = {
            closing_cash: Number(closeSessionForm.value.closing_cash) || 0,
            notes: closeSessionForm.value.notes,
        };

        if (diff !== 0) {
            payload.reimbursement_amount = Number(varianceForm.value.reimbursement_amount) || 0;
            payload.variance_reason = varianceForm.value.variance_reason;
        }

        const { data } = await axios.post(
            route("pos.sessions.close", activeSession.value.id),
            payload,
        );

        if (data?.success !== false) {
            toast.success("Sesi kasir berhasil ditutup");
            showCloseSessionModal.value = false;
            showVarianceModal.value = false;
            activeSession.value = null;
            router.reload();
        } else {
            toast.error(data?.message || "Gagal menutup sesi");
        }
    } catch (error) {
        toast.error(
            error.response?.data?.message || "Gagal menutup sesi kasir",
        );
    } finally {
        isClosingSession.value = false;
    }
};

const submitCloseSessionWithVariance = () => {
    submitCloseSession(true);
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

// Sync payable to usePayment's payableAmount
watch(payable, (newVal) => {
    setPayableAmount(newVal);
}, { immediate: true });

const handleOpenPaymentModal = () => {
    if (!props.carts.length) return toast.error("Keranjang masih kosong");
    if (!selectedCustomer.value?.id) return toast.error("Pilih pelanggan terlebih dahulu");
    showPaymentModal.value = true;
};

const isCashShort = computed(() => {
    if (!isCashPayment.value || payLater.value) return false;
    return (Number(cashInput.value) || 0) < payable.value;
});

const cashShortage = computed(() => {
    if (!isCashShort.value) return 0;
    return payable.value - (Number(cashInput.value) || 0);
});

const canSubmitCheckout = computed(() => {
    if (isSubmitting.value || isLoadingPricing.value) return false;
    if (!props.carts.length) return false;
    if (!selectedCustomer.value?.id) return false;
    if (payLater.value) return true;
    if (paymentMode.value === "split") return isSplitComplete.value;
    if (isCashPayment.value) {
        return (Number(cashInput.value) || 0) >= payable.value;
    }
    return true;
});

const checkoutSubmitLabel = computed(() => {
    if (isSubmitting.value) return "Memproses…";
    if (isLoadingPricing.value) return "Menghitung…";
    if (payLater.value) return "Simpan (Bayar Nanti)";
    if (paymentMode.value === "split" && !isSplitComplete.value) {
        return `Kurang ${formatPrice(splitRemaining.value || 0)}`;
    }
    if (isCashShort.value) {
        return `Kurang ${formatPrice(cashShortage.value)}`;
    }
    return "Konfirmasi Pembayaran (F2)";
});

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
    focusSearch: () => cartPanelRef.value?.focusSearch(),
    openNumpad: () => (numpadOpen.value = true),
    submitTransaction: () => {
        if (!props.carts.length) return toast.error("Keranjang masih kosong");
        if (!selectedCustomer.value?.id) return toast.error("Pilih pelanggan terlebih dahulu");
        
        if (!showPaymentModal.value) {
            showPaymentModal.value = true;
        } else {
            handleSubmit();
        }
    },
    toggleMobileView: () => {
        mobileView.value =
            mobileView.value === "cart" ? "payment" : "cart";
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
    discountInput.value = "0";
    shippingInput.value = "0";
    redeemPointsInput.value = "";
    selectedVoucherId.value = "";
    selectedCustomer.value = null;
    resetPayment();
    isSubmitting.value = false;
    showPaymentModal.value = false;
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
        :current-location-id="props.currentLocationId"
    />

    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- STATE 3: SESSION OPENER (jika sesi belum dibuka) -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <SessionOpener
        v-else-if="viewState === 'session'"
        @opened="handleSessionOpened"
        :errors="errors"
        :current-location-id="props.currentLocationId"
    />

    <!-- STATE 4: MAIN POS INTERFACE -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <div v-else class="h-[calc(100vh-4rem)] flex flex-col bg-surface-main">
        <!-- Mobile Tab Switcher -->
        <div
            class="lg:hidden flex border-b border-border-soft bg-surface-card flex-shrink-0"
        >
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
            <button
                @click="mobileView = 'payment'"
                :class="[
                    'flex-1 py-3 text-sm font-medium transition',
                    mobileView === 'payment'
                        ? 'text-brand border-b-2 border-brand'
                        : 'text-ink-muted',
                ]"
            >
                💳 Pembayaran
            </button>
        </div>

        <!-- Main Content (3 Columns) -->
        <div class="flex-1 flex flex-col lg:flex-row overflow-hidden">
            <!-- Col 1: Promosi (20% Lebar Layar) -->
            <div
                :class="[
                    'w-full lg:w-[20%] xl:w-[20%] min-w-[240px] flex flex-col bg-surface-card border-r border-border-soft p-base overflow-y-auto shrink-0',
                    mobileView !== 'cart' ? 'hidden lg:flex' : 'hidden lg:flex',
                ]"
            >
                <div class="space-y-sm">
                    <h3 class="text-[11px] font-extrabold text-ink-muted uppercase tracking-wider flex items-center gap-xs mb-sm">
                        📢 Promosi & Info
                    </h3>
                    <PromoBanner />
                </div>
            </div>

            <!-- Col 2: Keranjang Belanjaan (60% Lebar Layar) -->
            <div
                :class="[
                    'w-full lg:w-[60%] xl:w-[60%] flex-1 flex flex-col overflow-hidden bg-surface-main',
                    mobileView !== 'cart' ? 'hidden lg:flex' : 'flex',
                ]"
            >
                <div class="flex-1 min-h-0 overflow-hidden flex flex-col">
                    <CartPanel
                        ref="cartPanelRef"
                        :carts="carts"
                        :pricing-items-by-cart-id="pricingItemsByCartId"
                        :cart-count="cartCount"
                        :held-carts="heldCarts"
                        :has-active-cart="carts.length > 0"
                        :is-holding="isHolding"
                        :removing-item-id="removingItemId"
                        :updating-cart-id="updatingCartId"
                        :products="products"
                        :search-query="searchQuery"
                        @update-qty="updateQty"
                        @void-item="handleVoidItem"
                        @hold-cart="holdCart()"
                        @recall-held="handleRecallHeld"
                        @clear-cart="handleClearCart"
                        @update:search-query="searchQuery = $event"
                        @add-to-cart="addToCart"
                    />
                </div>
            </div>

            <!-- Col 3: Payment & Rangkuman (20% Lebar Layar) -->
            <div
                :class="[
                    'w-full lg:w-[20%] xl:w-[20%] min-w-[320px] flex flex-col bg-surface-card border-l border-border-soft min-h-0 overflow-hidden shrink-0',
                    mobileView !== 'payment' ? 'hidden lg:flex' : 'flex',
                ]"
            >
                <!-- Active Session (pindahan dari atas banner) -->
                <div
                    v-if="activeSession"
                    class="bg-accent-mint-soft border-b border-accent-mint/30 px-md py-sm flex flex-col gap-xs flex-shrink-0"
                >
                    <div class="flex items-center justify-between">
                        <span class="font-extrabold text-[10px] text-accent-mint uppercase tracking-wider">
                            🟢 Sesi Aktif
                        </span>
                        <button
                            @click="handleCloseSession"
                            class="px-2 py-0.5 rounded bg-semantic-danger-soft text-semantic-danger text-[9px] font-bold hover:bg-semantic-danger/20 transition"
                        >
                            🔒 Tutup Sesi
                        </button>
                    </div>
                    <div class="text-[11px] text-ink-muted leading-tight">
                        <span class="font-bold text-ink-primary">{{ activeSession.session_no }}</span> · 
                        {{ activeSession.shift_name }} · 
                        {{ activeSession.total_transactions || 0 }} trx · 
                        <span class="font-semibold text-brand font-mono">{{ formatPrice(activeSession.total_sales || 0) }}</span>
                    </div>
                </div>

                <!-- Customer Select -->
                <div class="p-3 border-b border-border-soft flex-shrink-0">
                    <CustomerSelect
                        :customers="customers"
                        :selected="selectedCustomer"
                        @select="selectedCustomer = $event"
                        :error="errors?.customer_id"
                    />
                </div>

                <!-- Rangkuman detail belanjaan -->
                <div class="flex-1 p-lg overflow-y-auto space-y-lg">
                    <!-- Loyalty Member Badge -->
                    <div v-if="selectedCustomer?.is_loyalty_member || selectedCustomer?.loyalty_account"
                         class="rounded-xl border border-brand-border bg-brand-soft p-md flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-brand uppercase tracking-wider">⭐ Member Loyalty</p>
                            <p class="text-sm font-semibold text-ink-primary mt-1">{{ selectedCustomer.name }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-ink-muted">Saldo Poin</p>
                            <p class="text-sm font-bold text-brand">{{ selectedCustomer.loyalty_account?.current_balance || 0 }} pts</p>
                        </div>
                    </div>
                </div>

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
                    :tax-total="taxTotal"
                    :applied-promotions="appliedPromotions"
                    @submit="handleOpenPaymentModal"
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

    <!-- Close Cashier Session Modal (Tutup Shift) -->
    <BaseModal :show="showCloseSessionModal" @close="showCloseSessionModal = false" title="🔒 Tutup Sesi Kasir & Shift">
        <form @submit.prevent="submitCloseSession(false)" class="space-y-md">
            <div class="bg-surface-main p-lg rounded-xl border border-border-soft space-y-sm text-sm">
                <div class="flex justify-between">
                    <span class="text-ink-muted">Kode / No Sesi:</span>
                    <span class="font-bold text-ink-primary font-mono">{{ activeSession?.session_no }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-ink-muted">Nama Shift:</span>
                    <span class="font-bold text-ink-primary">{{ activeSession?.shift_name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-ink-muted">Waktu Buka Sesi:</span>
                    <span class="font-medium text-ink-secondary">{{ activeSession?.opened_at }}</span>
                </div>
                <div class="border-t border-border-soft my-sm pt-sm flex justify-between">
                    <span class="text-ink-muted">Modal Awal Kas:</span>
                    <span class="font-semibold text-ink-primary font-mono">{{ formatPrice(activeSession?.opening_cash || 0) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-ink-muted">Total Penjualan:</span>
                    <span class="font-semibold text-brand font-mono">{{ formatPrice(activeSession?.total_sales || 0) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-ink-muted">Jumlah Transaksi:</span>
                    <span class="font-semibold text-ink-primary font-mono">{{ activeSession?.total_transactions || 0 }}</span>
                </div>
                <div class="border-t border-brand/20 bg-brand-soft/20 p-sm rounded-lg flex justify-between items-center mt-xs">
                    <span class="font-bold text-brand">Estimasi Uang Tunai di Laci:</span>
                    <span class="font-extrabold text-brand text-lg font-mono">{{ formatPrice(activeSession?.expected_cash || 0) }}</span>
                </div>
            </div>

            <FormInput
                :model-value="closingCashDisplay"
                @input="onClosingCashInput"
                type="text"
                label="Uang Kasir Aktual (Tunai Aktual)"
                required
                placeholder="E.g. 1.250.000"
            />

            <FormTextarea
                v-model="closeSessionForm.notes"
                label="Catatan & Selisih Kas"
                placeholder="Catatan penutupan sesi / alasan selisih uang (opsional)"
                rows="3"
            />

            <div class="flex justify-end gap-sm pt-md border-t border-border-soft">
                <BaseButton type="button" variant="secondary" @click="showCloseSessionModal = false">Batal</BaseButton>
                <BaseButton type="submit" variant="primary" :disabled="isClosingSession">
                    <span v-if="isClosingSession">Memproses...</span>
                    <span v-else>🔒 Tutup Sesi & Shift</span>
                </BaseButton>
            </div>
        </form>
    </BaseModal>

    <!-- Variance Information Modal -->
    <BaseModal :show="showVarianceModal" @close="showVarianceModal = false" title="⚠️ Selisih Kas Terdeteksi">
        <form @submit.prevent="submitCloseSessionWithVariance" class="space-y-md" v-if="activeSession">
            <div class="bg-semantic-warning-soft/30 p-lg rounded-xl border border-semantic-warning/20 space-y-sm text-sm">
                <div class="flex justify-between">
                    <span class="text-ink-muted">Uang Estimasi (Expected):</span>
                    <span class="font-bold text-ink-primary font-mono">{{ formatPrice(activeSession.expected_cash || 0) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-ink-muted">Uang Aktual (Aktual):</span>
                    <span class="font-bold text-ink-primary font-mono">{{ formatPrice(closeSessionForm.closing_cash || 0) }}</span>
                </div>
                <div class="border-t border-semantic-warning/20 pt-sm flex justify-between items-center">
                    <span class="font-bold text-semantic-warning">Selisih Kas:</span>
                    <span class="font-extrabold text-semantic-warning text-lg font-mono">
                        {{ formatPrice(closeSessionForm.closing_cash - (activeSession.expected_cash || 0)) }}
                    </span>
                </div>
            </div>

            <div class="text-xs text-ink-muted leading-relaxed">
                Terdapat perbedaan antara uang fisik di laci dengan estimasi sistem. Harap isi nilai penggantian (reimbursement) atau informasi/penjelasan mengenai selisih tersebut.
            </div>

            <FormInput
                :model-value="reimbursementAmountDisplay"
                @input="onReimbursementAmountInput"
                type="text"
                label="Nilai Penggantian (Reimbursement)"
                placeholder="Masukkan nilai uang penggantian jika ada..."
            />

            <FormTextarea
                v-model="varianceForm.variance_reason"
                label="Informasi / Alasan Variance"
                placeholder="Jelaskan alasan selisih kas (wajib jika tidak ada nilai penggantian)..."
                rows="3"
            />

            <div class="flex justify-end gap-sm pt-md border-t border-border-soft">
                <BaseButton type="button" variant="secondary" @click="showVarianceModal = false">Batal</BaseButton>
                <BaseButton type="submit" variant="primary" :disabled="isClosingSession || (!varianceForm.reimbursement_amount && !varianceForm.variance_reason.trim())">
                    <span v-if="isClosingSession">Memproses...</span>
                    <span v-else>🔒 Konfirmasi & Tutup Sesi</span>
                </BaseButton>
            </div>
        </form>
    </BaseModal>

    <!-- Payment Confirmation Modal -->
    <BaseModal :show="showPaymentModal" @close="showPaymentModal = false" title="💳 Proses Pembayaran">
        <div class="space-y-md">
            <!-- Wrap in max-height scrollable container for safe layout -->
            <div class="max-h-[80vh] overflow-y-auto scroll-soft">
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
                    @switch-mode="switchMode"
                    @add-split="addSplit"
                    @update-split-amount="updateSplitAmount"
                    @remove-split="removeSplit"
                />
            </div>

            <!-- Modal Action Buttons -->
            <div class="flex justify-end gap-sm pt-md border-t border-border-soft px-lg pb-base">
                <BaseButton type="button" variant="secondary" @click="showPaymentModal = false">Batal</BaseButton>
                <BaseButton 
                    type="button" 
                    variant="primary" 
                    :disabled="!canSubmitCheckout"
                    @click="handleSubmit"
                    class="bg-brand-gradient text-white font-bold min-w-[180px]"
                >
                    <span v-if="isSubmitting">Memproses...</span>
                    <span v-else>{{ checkoutSubmitLabel }}</span>
                </BaseButton>
            </div>
        </div>
    </BaseModal>
</template>
