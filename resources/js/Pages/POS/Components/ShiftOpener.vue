<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({ errors: Object, canOpenShift: Boolean });

const openingCash = ref("");
const notes = ref("");

const handleOpen = () => {
    router.post(route("cashier-shifts.store"), {
        opening_cash: Number(openingCash.value || 0),
        notes: notes.value,
        redirect_to: "transactions",
    });
};
</script>

<template>
    <div
        class="mx-auto flex min-h-[calc(100vh-8rem)] max-w-3xl items-center justify-center px-4 py-10"
    >
        <div
            class="w-full rounded-3xl border border-border-soft bg-surface-card p-8 shadow-2xl"
        >
            <div
                class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-accent-mint-soft text-accent-mint"
            >
                <span class="text-2xl">💰</span>
            </div>
            <h1 class="text-2xl font-bold text-ink-primary">
                Shift kasir belum dibuka
            </h1>
            <p class="mt-2 text-sm text-ink-muted">
                Buka shift terlebih dulu untuk mengaktifkan transaksi dan cash
                closing.
            </p>

            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div>
                    <label
                        class="mb-2 block text-sm font-medium text-ink-primary"
                        >Modal Awal</label
                    >
                    <input
                        v-model="openingCash"
                        type="number"
                        min="0"
                        class="h-12 w-full rounded-2xl border border-border-soft bg-surface-main px-4 text-sm text-ink-primary outline-none focus:border-brand focus:ring-2 focus:ring-brand/20"
                        placeholder="0"
                    />
                    <p
                        v-if="errors?.opening_cash"
                        class="mt-2 text-xs text-semantic-danger"
                    >
                        {{ errors.opening_cash }}
                    </p>
                </div>
                <div>
                    <label
                        class="mb-2 block text-sm font-medium text-ink-primary"
                        >Catatan</label
                    >
                    <input
                        v-model="notes"
                        type="text"
                        class="h-12 w-full rounded-2xl border border-border-soft bg-surface-main px-4 text-sm text-ink-primary outline-none focus:border-brand focus:ring-2 focus:ring-brand/20"
                        placeholder="Opsional"
                    />
                </div>
            </div>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                <button
                    v-if="canOpenShift"
                    @click="handleOpen"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-brand px-5 py-3 text-sm font-medium text-white hover:bg-brand-hover"
                >
                    💰 Buka Shift Sekarang
                </button>
                <a
                    :href="route('cashier-shifts.index')"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl border border-border-soft px-5 py-3 text-sm font-medium text-ink-primary hover:bg-surface-muted"
                >
                    Lihat Histori Shift
                </a>
            </div>
        </div>
    </div>
</template>
