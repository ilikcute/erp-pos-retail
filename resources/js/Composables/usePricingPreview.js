import { ref, watch, computed } from "vue";
import axios from "axios";
import toast from "@/Utils/toast";

/**
 * Debounced pricing preview — memanggil server untuk menghitung
 * promo, voucher, loyalty, pajak secara real-time.
 */
export function usePricingPreview(initialPreview, deps) {
    const pricingPreview = ref(initialPreview);
    const isLoadingPricing = ref(false);

    const pricingItemsByCartId = computed(() => {
        return (pricingPreview.value?.items || []).reduce((acc, item) => {
            acc[item.cart_id] = item;
            return acc;
        }, {});
    });

    const summary = computed(() => pricingPreview.value?.summary || {});

    // Watch semua dependency dengan debounce
    let debounceTimer = null;
    watch(
        deps,
        () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => fetchPricing(), 300);
        },
        { deep: true, immediate: true },
    );

    const fetchPricing = async () => {
        const [
            carts,
            selectedCustomer,
            discount,
            shipping,
            redeemPoints,
            voucherId,
        ] = deps.value;

        if (!carts || carts.length === 0) {
            pricingPreview.value = {
                items: [],
                summary: {
                    base_subtotal: 0,
                    promo_discount_total: 0,
                    subtotal_after_promo: 0,
                    voucher_discount_total: 0,
                    loyalty_discount_total: 0,
                    manual_discount_total: 0,
                    shipping_cost: 0,
                    tax_total: 0,
                    grand_total: 0,
                },
            };
            return;
        }

        isLoadingPricing.value = true;
        try {
            const { data } = await axios.post(
                route("pos.pricing-preview"),
                {
                    customer_id: selectedCustomer?.id ?? null,
                    discount: Number(discount) || 0,
                    shipping_cost: Number(shipping) || 0,
                    redeem_points: Number(redeemPoints) || 0,
                    customer_voucher_id: voucherId || null,
                },
            );
            pricingPreview.value = data?.data || pricingPreview.value;
        } catch {
            toast.error("Gagal memuat promo aktif");
        } finally {
            isLoadingPricing.value = false;
        }
    };

    return { pricingPreview, isLoadingPricing, pricingItemsByCartId, summary };
}
