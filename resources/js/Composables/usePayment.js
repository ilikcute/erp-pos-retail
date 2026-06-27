import { ref, computed, watch } from "vue";

export function usePayment({ defaultGateway = "cash", gateways = [] }) {
    const paymentMode = ref("single"); // 'single' | 'split'
    const paymentMethod = ref(defaultGateway);
    const payLater = ref(false);
    const dueDate = ref("");
    const cashInput = ref("");
    const selectedBankAccount = ref(null);

    // Split payment state
    const paymentSplits = ref([]);
    // [{ method: 'cash'|'qris'|'debit'|'bank_transfer', amount: 0, reference: '' }]

    const paymentOptions = computed(() => {
        const nonCash = Array.isArray(gateways)
            ? gateways.filter(
                  (g) => g?.value && g.value.toLowerCase() !== "cash",
              )
            : [];
        return [
            {
                value: "cash",
                label: "Tunai",
                description: "Pembayaran tunai langsung di kasir.",
            },
            ...nonCash,
        ];
    });

    // Auto-set cash input untuk non-cash
    watch(
        [() => paymentMethod.value, () => payable.value, () => payLater.value],
        ([method, payable, isLater]) => {
            if (method !== "cash" && !isLater && payable > 0) {
                cashInput.value = String(payable);
            }
        },
    );

    const isCashPayment = computed(
        () => !payLater.value && paymentMethod.value === "cash",
    );

    const switchMode = (mode) => {
        paymentMode.value = mode;
        if (mode === "split") {
            paymentSplits.value = [
                { method: "cash", amount: 0, reference: "" },
            ];
        } else {
            paymentSplits.value = [];
        }
    };

    const addSplit = (method) => {
        if (splitRemaining.value <= 0) return;
        paymentSplits.value.push({ method, amount: 0, reference: "" });
    };

    const updateSplitAmount = (index, amount) => {
        const newAmount = Math.min(
            Number(amount) || 0,
            splitRemaining.value +
                Number(paymentSplits.value[index].amount || 0),
        );
        paymentSplits.value[index].amount = newAmount;
    };

    const removeSplit = (index) => {
        paymentSplits.value.splice(index, 1);
    };

    const splitTotal = computed(() =>
        paymentSplits.value.reduce(
            (sum, p) => sum + (Number(p.amount) || 0),
            0,
        ),
    );

    const splitRemaining = computed(() => {
        const payable = Number(payableAmount.value) || 0;
        return Math.max(0, payable - splitTotal.value);
    });

    const isSplitComplete = computed(
        () => splitRemaining.value === 0 && paymentSplits.value.length > 0,
    );

    // Placeholder — akan di-inject dari parent
    const payableAmount = ref(0);
    const setPayableAmount = (v) => (payableAmount.value = v);

    const reset = () => {
        paymentMode.value = "single";
        paymentMethod.value = defaultGateway;
        payLater.value = false;
        dueDate.value = "";
        cashInput.value = "";
        selectedBankAccount.value = null;
        paymentSplits.value = [];
    };

    return {
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
        reset,
    };
}
