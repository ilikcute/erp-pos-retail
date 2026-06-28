<script setup>
import { ref, onMounted, computed } from "vue";
import axios from "axios";
import toast from "@/Utils/toast";
import { formatPrice } from "@/Utils/formatPrice";

const props = defineProps({
    errors: { type: Object, default: () => ({}) },
    canOpenSession: { type: Boolean, default: true },
});

const emit = defineEmits(["opened"]);

// ═══════════════════════════════════════════════════════════
// STATE
// ═══════════════════════════════════════════════════════════
const shifts = ref([]);
const selectedShiftId = ref(null);
const openingCash = ref("");
const notes = ref("");
const isLoading = ref(false);
const isFetchingShifts = ref(true);

// Quick cash amounts untuk modal awal
const quickAmounts = [100000, 200000, 300000, 500000, 750000, 1000000];

// ═══════════════════════════════════════════════════════════
// COMPUTED
// ═══════════════════════════════════════════════════════════
const canSubmit = computed(() => {
    return (
        !isLoading.value &&
        selectedShiftId.value !== null &&
        props.canOpenSession
    );
});

const formattedOpeningCash = computed(() => {
    const num = Number(openingCash.value) || 0;
    return num > 0 ? formatPrice(num) : "Rp 0";
});

// ═══════════════════════════════════════════════════════════
// LIFECYCLE
// ═══════════════════════════════════════════════════════════
onMounted(async () => {
    await fetchShifts();
});

// ═══════════════════════════════════════════════════════════
// METHODS
// ═══════════════════════════════════════════════════════════
const fetchShifts = async () => {
    isFetchingShifts.value = true;
    try {
        // Coba fetch dari API shifts
        const { data } = await axios.get(route("pos.shifts.list")).catch(() => {
            // Fallback: jika route tidak ada, gunakan data statis
            return {
                data: {
                    data: [
                        {
                            id: 1,
                            name: "Shift Pagi",
                            shift_name: "Shift Pagi",
                            start_time: "08:00",
                            end_time: "16:00",
                        },
                        {
                            id: 2,
                            name: "Shift Sore",
                            shift_name: "Shift Sore",
                            start_time: "16:00",
                            end_time: "00:00",
                        },
                    ],
                },
            };
        });

        shifts.value = data?.data || data || [];

        // Auto-select shift pertama
        if (shifts.value.length > 0 && !selectedShiftId.value) {
            selectedShiftId.value = shifts.value[0].id;
        }
    } catch (error) {
        console.error("Failed to load shifts:", error);
        toast.error("Gagal memuat daftar shift");
    } finally {
        isFetchingShifts.value = false;
    }
};

const setQuickAmount = (amount) => {
    openingCash.value = String(amount);
};

const handleOpen = async () => {
    if (!canSubmit.value) return;

    if (!selectedShiftId.value) {
        return toast.error("Pilih shift terlebih dahulu");
    }

    isLoading.value = true;

    try {
        const { data } = await axios.post(route("pos.sessions.open"), {
            shift_id: selectedShiftId.value,
            opening_cash: Number(openingCash.value) || 0,
            notes: notes.value || null,
        });

        if (data?.success !== false) {
            toast.success("Sesi kasir berhasil dibuka");
            emit("opened", data?.data || data);
        } else {
            toast.error(data?.message || "Gagal membuka sesi");
        }
    } catch (error) {
        const message =
            error.response?.data?.message ||
            error.response?.data?.errors?.shift_id?.[0] ||
            error.response?.data?.errors?.opening_cash?.[0] ||
            "Gagal membuka sesi kasir";
        toast.error(message);
    } finally {
        isLoading.value = false;
    }
};

const getShiftLabel = (shift) => {
    return shift.name || shift.shift_name || `Shift #${shift.id}`;
};

const getShiftTime = (shift) => {
    if (shift.start_time && shift.end_time) {
        return `${shift.start_time} - ${shift.end_time}`;
    }
    return null;
};
</script>

<template>
    <div
        class="min-h-screen flex items-center justify-center bg-surface-main p-4"
    >
        <div
            class="w-full max-w-md rounded-2xl border border-border-soft bg-surface-card shadow-2xl overflow-hidden"
        >
            <!-- ═══════════════════════════════════════════════════════ -->
            <!-- HEADER -->
            <!-- ═══════════════════════════════════════════════════════ -->
            <div
                class="bg-gradient-to-r from-brand to-brand-hover px-6 py-5 text-white"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center text-2xl"
                    >
                        💰
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">Buka Sesi Kasir</h1>
                        <p class="text-sm text-white/80">
                            Mulai transaksi dengan membuka sesi baru
                        </p>
                    </div>
                </div>
            </div>

            <!-- ═══════════════════════════════════════════════════════ -->
            <!-- FORM BODY -->
            <!-- ═══════════════════════════════════════════════════════ -->
            <div class="p-6 space-y-5">
                <!-- Pilih Shift -->
                <div>
                    <label
                        class="block text-sm font-semibold text-ink-primary mb-2"
                    >
                        Shift <span class="text-semantic-danger">*</span>
                    </label>

                    <!-- Loading state -->
                    <div
                        v-if="isFetchingShifts"
                        class="h-12 rounded-xl border border-border-soft bg-surface-muted flex items-center justify-center"
                    >
                        <div
                            class="w-5 h-5 border-2 border-brand/30 border-t-brand rounded-full animate-spin"
                        />
                    </div>

                    <!-- Shift selector -->
                    <select
                        v-else
                        v-model="selectedShiftId"
                        class="w-full h-12 rounded-xl border border-border-soft bg-surface-main px-4 text-sm text-ink-primary focus:ring-2 focus:ring-brand focus:border-brand outline-none transition"
                    >
                        <option :value="null" disabled>Pilih shift...</option>
                        <option
                            v-for="shift in shifts"
                            :key="shift.id"
                            :value="shift.id"
                        >
                            {{ getShiftLabel(shift) }}
                            <template v-if="getShiftTime(shift)">
                                ({{ getShiftTime(shift) }})
                            </template>
                        </option>
                    </select>

                    <p
                        v-if="errors?.shift_id"
                        class="mt-1.5 text-xs text-semantic-danger"
                    >
                        {{ errors.shift_id }}
                    </p>

                    <p
                        v-if="!isFetchingShifts && shifts.length === 0"
                        class="mt-1.5 text-xs text-ink-muted"
                    >
                        Tidak ada shift tersedia. Hubungi administrator.
                    </p>
                </div>

                <!-- Modal Awal -->
                <div>
                    <label
                        class="block text-sm font-semibold text-ink-primary mb-2"
                    >
                        Modal Awal (Uang di Laci)
                    </label>

                    <div class="relative">
                        <span
                            class="absolute left-4 top-1/2 -translate-y-1/2 text-ink-muted text-sm font-medium"
                        >
                            Rp
                        </span>
                        <input
                            v-model="openingCash"
                            type="text"
                            inputmode="numeric"
                            @input="
                                openingCash = $event.target.value.replace(
                                    /[^\d]/g,
                                    '',
                                )
                            "
                            class="w-full h-12 rounded-xl border border-border-soft bg-surface-main pl-12 pr-4 text-base font-semibold text-ink-primary focus:ring-2 focus:ring-brand focus:border-brand outline-none transition tabular-nums"
                            placeholder="0"
                        />
                    </div>

                    <p
                        v-if="errors?.opening_cash"
                        class="mt-1.5 text-xs text-semantic-danger"
                    >
                        {{ errors.opening_cash }}
                    </p>

                    <!-- Quick Amounts -->
                    <div class="grid grid-cols-3 gap-2 mt-3">
                        <button
                            v-for="amt in quickAmounts"
                            :key="amt"
                            @click="setQuickAmount(amt)"
                            type="button"
                            :class="[
                                'py-2 px-2 rounded-lg text-xs font-semibold transition',
                                Number(openingCash) === amt
                                    ? 'bg-brand text-white shadow-sm'
                                    : 'bg-surface-muted text-ink-secondary hover:bg-border-soft border border-border-soft',
                            ]"
                        >
                            {{ formatPrice(amt) }}
                        </button>
                    </div>
                </div>

                <!-- Catatan -->
                <div>
                    <label
                        class="block text-sm font-semibold text-ink-primary mb-2"
                    >
                        Catatan
                        <span class="text-ink-muted font-normal text-xs"
                            >(opsional)</span
                        >
                    </label>
                    <input
                        v-model="notes"
                        type="text"
                        class="w-full h-12 rounded-xl border border-border-soft bg-surface-main px-4 text-sm text-ink-primary focus:ring-2 focus:ring-brand focus:border-brand outline-none transition"
                        placeholder="Contoh: Modal awal dari bank"
                        maxlength="500"
                    />
                </div>

                <!-- ═══════════════════════════════════════════════════ -->
                <!-- INFO BOX -->
                <!-- ═══════════════════════════════════════════════════ -->
                <div
                    class="rounded-xl border border-accent-sky/30 bg-accent-sky-soft p-3 flex items-start gap-2"
                >
                    <span class="text-sm mt-0.5">ℹ️</span>
                    <p class="text-xs text-ink-secondary leading-relaxed">
                        Modal awal adalah jumlah uang tunai yang ada di laci
                        kasir saat shift dimulai. Pastikan sudah dihitung dengan
                        teliti.
                    </p>
                </div>
            </div>

            <!-- ═══════════════════════════════════════════════════════ -->
            <!-- FOOTER / SUBMIT -->
            <!-- ═══════════════════════════════════════════════════════ -->
            <div class="px-6 pb-6">
                <button
                    @click="handleOpen"
                    :disabled="!canSubmit"
                    type="button"
                    class="w-full h-12 rounded-xl text-sm font-bold text-white transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                    :class="
                        canSubmit
                            ? 'bg-brand hover:bg-brand-hover shadow-lg shadow-brand/30 active:scale-[0.98]'
                            : 'bg-surface-muted text-ink-muted'
                    "
                >
                    <!-- Spinner -->
                    <svg
                        v-if="isLoading"
                        class="animate-spin h-5 w-5 text-white"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <circle
                            class="opacity-25"
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            stroke-width="4"
                        />
                        <path
                            class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                        />
                    </svg>

                    <span v-else class="text-base">💰</span>

                    <span>
                        {{ isLoading ? "Membuka Sesi..." : "Buka Sesi Kasir" }}
                    </span>
                </button>

                <!-- Summary preview -->
                <div
                    v-if="Number(openingCash) > 0"
                    class="mt-3 text-center text-xs text-ink-muted"
                >
                    Modal awal:
                    <span class="font-bold text-ink-primary">
                        {{ formattedOpeningCash }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>
