import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import toast from "@/Utils/toast";

export function useCart() {
    const addingProductId = ref(null);
    const removingItemId = ref(null);
    const updatingCartId = ref(null);
    const isHolding = ref(false);

    const addToCart = (product) => {
        if (!product?.id) return;
        addingProductId.value = product.id;

        router.post(
            route("pos.cart.add"),
            { product_id: product.id, sell_price: product.sell_price, qty: 1 },
            {
                preserveScroll: true,
                onSuccess: () => {
                    toast.success(`${product.title} ditambahkan`);
                    addingProductId.value = null;
                },
                onError: () => {
                    toast.error("Gagal menambahkan produk");
                    addingProductId.value = null;
                },
            },
        );
    };

    const updateQty = (cartId, newQty) => {
        if (newQty < 1) return;
        updatingCartId.value = cartId;

        router.patch(
            route("pos.cart.update", cartId),
            { qty: newQty },
            {
                preserveScroll: true,
                onSuccess: () => (updatingCartId.value = null),
                onError: (errors) => {
                    toast.error(errors?.message || "Gagal update quantity");
                    updatingCartId.value = null;
                },
            },
        );
    };

    const removeFromCart = (cartId, voidReason = null) => {
        removingItemId.value = cartId;

        router.delete(route("pos.cart.destroy", cartId), {
            data: voidReason ? { void_reason: voidReason } : {},
            preserveScroll: true,
            onSuccess: () => {
                toast.success(
                    voidReason ? `Item di-void: ${voidReason}` : "Item dihapus",
                );
                removingItemId.value = null;
            },
            onError: () => {
                toast.error("Gagal menghapus item");
                removingItemId.value = null;
            },
        });
    };

    const holdCart = (label = null) => {
        isHolding.value = true;

        router.post(
            route("pos.hold"),
            { label },
            {
                preserveScroll: true,
                onSuccess: () => {
                    toast.success("Transaksi ditahan");
                    isHolding.value = false;
                },
                onError: (errors) => {
                    toast.error(errors?.message || "Gagal menahan transaksi");
                    isHolding.value = false;
                },
            },
        );
    };

    return {
        addingProductId,
        removingItemId,
        updatingCartId,
        isHolding,
        addToCart,
        updateQty,
        removeFromCart,
        holdCart,
    };
}
