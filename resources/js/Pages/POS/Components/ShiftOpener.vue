<script setup>
import toast from "@/Utils/toast";

const props = defineProps({
    errors: { type: Object, default: () => ({}) },
    // Default true so the open button always shows in frontend-only mode.
    canOpenShift: { type: Boolean, default: true },
});

const emit = defineEmits(["opened"]);

const openingCash = ref("");
const notes = ref("");
const isSubmitting = ref(false);

// True only if the Laravel route is actually registered (Ziggy).
const hasBackend = () => {
    try { return typeof route === "function" && route().has("cashier-shifts.store"); }
    catch (e) { return false; }
};
const safeRoute = (name, params) => {
    try { return (typeof route === "function" && route().has(name)) ? route(name, params) : "#"; }
    catch (e) { return "#"; }
};

const handleOpen = () => {
    const cash = Number(openingCash.value);
    if (!openingCash.value || isNaN(cash) || cash < 0) {
        toast.error("Masukkan modal awal yang valid");
        return;
    }

    const payload = { opening_cash: cash, notes: notes.value };

    if (hasBackend()) {
        // Real backend flow
        isSubmitting.value = true;
        router.post(safeRoute("cashier-shifts.store"), payload, {
            preserveScroll: true,
            onFinish: () => { isSubmitting.value = false; },
        });
        return;
    }

    // Frontend-only fallback: open the shift client-side so the POS becomes usable.
    isSubmitting.value = true;
    setTimeout(() => {
        emit("opened", {
            id: "local-" + Date.now(),
            opening_cash: cash,
            notes: notes.value,
            opened_at: new Date().toISOString(),
            is_local: true,
        });
        toast.success("Shift dibuka (mode lokal)");
        openingCash.value = "";
        notes.value = "";
        isSubmitting.value = false;
    }, 250);
};
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-surface-app p-4">
        <div class="w-full max-w-md rounded-3xl bg-surface-card shadow-xl border border-border-soft p-7">
            <div class="text-center mb-6">
                <div class="mx-auto mb-3 h-14 w-14 rounded-2xl bg-brand-gradient flex items-center justify-center text-2xl">🛒</div>
                <h1 class="text-xl font-extrabold text-ink-primary">Buka Shift Kasir</h1>
                <p class="text-sm text-ink-secondary mt-1">Masukkan modal awal untuk memulai sesi penjualan.</p>
            </div>

            <form @submit.prevent="handleOpen" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-ink-secondary mb-1">Modal Awal (Rp)</label>
                    <input
                        v-model="openingCash"
                        type="number" min="0" step="1000" inputmode="numeric" autofocus
                        placeholder="contoh: 500000"
                        class="h-12 w-full rounded-2xl border border-border-soft bg-surface-main px-4 text-sm text-ink-primary outline-none focus:border-brand focus:ring-2 focus:ring-brand/20"
                    />
                    <p v-if="errors?.opening_cash" class="text-xs text-rose-500 mt-1">{{ errors.opening_cash }}</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-ink-secondary mb-1">Catatan (opsional)</label>
                    <textarea
                        v-model="notes" rows="2"
                        placeholder="mis. shift pagi, kas dari brankas"
                        class="w-full rounded-2xl border border-border-soft bg-surface-main px-4 py-3 text-sm text-ink-primary outline-none focus:border-brand focus:ring-2 focus:ring-brand/20"
                    ></textarea>
                </div>

                <button
                    type="submit"
                    :disabled="isSubmitting || !canOpenShift"
                    class="h-12 w-full rounded-2xl bg-brand-gradient text-white text-sm font-bold transition hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span v-if="isSubmitting">Membuka…</span>
                    <span v-else>Buka Shift</span>
                </button>

                <p v-if="!canOpenShift" class="text-center text-xs text-rose-500">
                    Anda tidak memiliki izin untuk membuka shift.
                </p>

                <a
                    :href="safeRoute('cashier-shifts.index')"
                    class="block text-center rounded-2xl border border-border-soft py-3 text-sm font-medium text-ink-primary hover:bg-surface-muted"
                >
                    Lihat Histori Shift
                </a>
            </form>
        </div>
    </div>
</template>
