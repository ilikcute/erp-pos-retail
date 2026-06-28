<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { router } from "@inertiajs/vue3";
import toast from "@/Utils/toast";

const props = defineProps({
    errors: Object,
    canOpenShift: Boolean,
});

const emit = defineEmits(["opened"]);

const shifts = ref([]);
const selectedShiftId = ref(null);
const openingCash = ref("");
const notes = ref("");
const isLoading = ref(false);

onMounted(async () => {
    try {
        // Fetch daftar shift aktif
        const { data } = await axios.get(route("pos.shifts.list"));
        shifts.value = data.data || [];
        if (shifts.value.length > 0) {
            selectedShiftId.value = shifts.value[0].id;
        }
    } catch (error) {
        console.error("Failed to load shifts:", error);
    }
});

const handleOpen = async () => {
    if (!selectedShiftId.value) {
        return toast.error("Pilih shift terlebih dahulu");
    }

    isLoading.value = true;
    try {
        const { data } = await axios.post(route("pos.sessions.open"), {
            shift_id: selectedShiftId.value,
            opening_cash: Number(openingCash.value) || 0,
            notes: notes.value,
        });

        if (data.success !== false) {
            toast.success("Sesi kasir berhasil dibuka");
            emit("opened", data.data);
            router.reload(); // Reload untuk dapat data sesi
        } else {
            toast.error(data.message || "Gagal membuka sesi");
        }
    } catch (error) {
        toast.error(error.response?.data?.message || "Gagal membuka sesi");
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <div
        class="mx-auto flex min-h-screen max-w-2xl items-center justify-center p-4"
    >
        <div
            class="w-full rounded-2xl border border-border-soft bg-surface-card p-8 shadow-2xl"
        >
            <div
                class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-accent-mint-soft text-accent-mint"
            >
                <span class="text-2xl">💰</span>
            </div>
            <h1 class="text-2xl font-bold text-ink-primary">Buka Sesi Kasir</h1>
            <p class="mt-2 text-sm text-ink-muted">
                Buka sesi untuk memulai transaksi. Pastikan modal awal sudah
                dihitung.
            </p>

            <div class="mt-6 space-y-4">
                <!-- Pilih Shift -->
                <div>
                    <label
                        class="mb-2 block text-sm font-medium text-ink-primary"
                        >Shift</label
                    >
                    <select
                        v-model="selectedShiftId"
                        class="w-full h-12 rounded-xl border border-border-soft bg-surface-main px-4 text-sm focus:ring-2 focus:ring-brand"
                    >
                        <option value="">Pilih shift...</option>
                        <option
                            v-for="shift in shifts"
                            :key="shift.id"
                            :value="shift.id"
                        >
                            {{ shift.name }} ({{ shift.start_time }} -
                            {{ shift.end_time }})
                        </option>
                    </select>
                </div>

                <!-- Modal Awal -->
                <div>
                    <label
                        class="mb-2 block text-sm font-medium text-ink-primary"
                        >Modal Awal</label
                    >
                    <div class="relative">
                        <span
                            class="absolute left-4 top-1/2 -translate-y-1/2 text-ink-muted"
                            >Rp</span
                        >
                        <input
                            v-model="openingCash"
                            type="number"
                            min="0"
                            class="h-12 w-full rounded-xl border border-border-soft bg-surface-main pl-10 pr-4 text-sm focus:ring-2 focus:ring-brand"
                            placeholder="0"
                        />
                    </div>
                </div>

                <!-- Catatan -->
                <div>
                    <label
                        class="mb-2 block text-sm font-medium text-ink-primary"
                        >Catatan</label
                    >
                    <input
                        v-model="notes"
                        type="text"
                        class="h-12 w-full rounded-xl border border-border-soft bg-surface-main px-4 text-sm focus:ring-2 focus:ring-brand"
                        placeholder="Opsional"
                    />
                </div>
            </div>

            <button
                @click="handleOpen"
                :disabled="isLoading || !selectedShiftId"
                class="mt-6 w-full rounded-xl bg-brand px-5 py-3 text-sm font-medium text-white hover:bg-brand-hover disabled:opacity-50"
            >
                <span v-if="isLoading">Membuka...</span>
                <span v-else>💰 Buka Sesi Sekarang</span>
            </button>
        </div>
    </div>
</template>
