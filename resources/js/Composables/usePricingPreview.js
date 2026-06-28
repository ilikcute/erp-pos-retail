import { ref, computed, watch, onUnmounted } from "vue";
import axios from "axios";
import toast from "@/Utils/toast";

export function usePricingPreview(initialPreview, deps) {
    const pricingPreview = ref(
        initialPreview || {
            items: [],
            summary: {},
            applied_promotions: [], // ✅ BARU
        },
    );
    const isLoadingPricing = ref(false);
    let debounceTimer = null;

    const pricingItemsByCartId = computed(() => {
        return (pricingPreview.value?.items || []).reduce((acc, item) => {
            if (item?.cart_id !== undefined) {
                acc[item.cart_id] = item;
            }
            return acc;
        }, {});
    });

    const summary = computed(() => pricingPreview.value?.summary || {});

    // ✅ BARU: Applied promotions
    const appliedPromotions = computed(
        () => pricingPreview.value?.applied_promotions || [],
    );

    const fetchPricing = async () => {
        const depsValue = deps.value;
        const carts = Array.isArray(depsValue) ? depsValue[0] : [];

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
                    available_loyalty_points: 0,
                },
                applied_promotions: [],
                eligible_vouchers: [],
            };
            isLoadingPricing.value = false;
            return;
        }

        isLoadingPricing.value = true;

        try {
            const [
                ,
                selectedCustomer,
                discount,
                shipping,
                redeemPoints,
                voucherId,
            ] = depsValue;

            const { data } = await axios.post(route("pos.pricing-preview"), {
                customer_id: selectedCustomer?.id ?? null,
                discount: Number(discount) || 0,
                shipping_cost: Number(shipping) || 0,
                redeem_points: Number(redeemPoints) || 0,
                customer_voucher_id: voucherId || null,
            });

            pricingPreview.value = data?.data || pricingPreview.value;
        } catch (error) {
            console.error("[usePricingPreview] Error:", error);
            toast.error("Gagal memuat kalkulasi harga");
        } finally {
            isLoadingPricing.value = false;
        }
    };

    const stopWatch = watch(
        deps,
        () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => fetchPricing(), 300);
        },
        { deep: true, immediate: true },
    );

    onUnmounted(() => {
        clearTimeout(debounceTimer);
        if (typeof stopWatch === "function") stopWatch();
    });

    return {
        pricingPreview,
        isLoadingPricing,
        pricingItemsByCartId,
        summary,
        appliedPromotions, // ✅ BARU
        fetchPricing,
    };
}
