<script setup>
import { ref, onMounted } from "vue";
import { router } from "@inertiajs/vue3";
import axios from "axios";
import toast from "@/Utils/toast";
import { formatPrice } from "@/Utils/formatPrice";

const todayStats = ref(null);
const closingDate = ref(new Date().toISOString().split("T")[0]);
const notes = ref("");
const isLoading = ref(false);

onMounted(async () => {
    try {
        const { data } = await axios.get(route("pos.day-closings.today"));
        todayStats.value = data.data;
    } catch (error) {
        console.error("Failed to load stats:", error);
    }
});

const handleClose = async () => {
    if (
        !confirm(
            "Yakin ingin menutup hari ini? Tindakan ini tidak dapat dibatalkan.",
        )
    ) {
        return;
    }

    isLoading.value = true;
    try {
        const { data } = await axios.post(route("pos.day-closings.close"), {
            closing_date: closingDate.value,
            notes: notes.value,
        });

        if (data.success !== false) {
            toast.success("Tutup harian berhasil");
            router.reload();
        } else {
            toast.error(data.message);
        }
    } catch (error) {
        toast.error(error.response?.data?.message || "Gagal menutup hari");
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <div class="max-w-4xl mx-auto p-6">
        <h1 class="text-2xl font-bold text-ink-primary mb-6">
            🔒 Tutup Harian
        </h1>

        <!-- Statistik Hari Ini -->
        <div
            v-if="todayStats"
            class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6"
        >
            <div
                class="bg-surface-card border border-border-soft rounded-xl p-4"
            >
                <p class="text-xs text-ink-muted">Total Transaksi</p>
                <p class="text-2xl font-bold text-ink-primary">
                    {{ todayStats.total_transactions }}
                </p>
            </div>
            <div
                class="bg-surface-card border border-border-soft rounded-xl p-4"
            >
                <p class="text-xs text-ink-muted">Total Penjualan</p>
                <p class="text-2xl font-bold text-brand">
                    {{ formatPrice(todayStats.total_sales) }}
                </p>
            </div>
            <div
                class="bg-surface-card border border-border-soft rounded-xl p-4"
            >
                <p class="text-xs text-ink-muted">Total Cash</p>
                <p class="text-2xl font-bold text-accent-mint">
                    {{ formatPrice(todayStats.total_cash) }}
                </p>
            </div>
            <div
                class="bg-surface-card border border-border-soft rounded-xl p-4"
            >
                <p class="text-xs text-ink-muted">Sesi Aktif</p>
                <p
                    class="text-2xl font-bold"
                    :class="
                        todayStats.open_sessions > 0
                            ? 'text-semantic-danger'
                            : 'text-accent-mint'
                    "
                >
                    {{ todayStats.open_sessions }}
                </p>
            </div>
        </div>

        <!-- Form Tutup -->
        <div class="bg-surface-card border border-border-soft rounded-xl p-6">
            <div class="space-y-4">
                <div>
                    <label
                        class="block text-sm font-medium text-ink-primary mb-2"
                        >Tanggal</label
                    >
                    <input
                        v-model="closingDate"
                        type="date"
                        :max="new Date().toISOString().split('T')[0]"
                        class="w-full h-12 rounded-xl border border-border-soft bg-surface-main px-4"
                    />
                </div>

                <div>
                    <label
                        class="block text-sm font-medium text-ink-primary mb-2"
                        >Catatan</label
                    >
                    <textarea
                        v-model="notes"
                        rows="3"
                        class="w-full rounded-xl border border-border-soft bg-surface-main px-4 py-2"
                        placeholder="Catatan penutupan (opsional)"
                    ></textarea>
                </div>

                <button
                    @click="handleClose"
                    :disabled="isLoading || todayStats?.open_sessions > 0"
                    class="w-full rounded-xl bg-semantic-danger px-5 py-3 text-white font-semibold hover:opacity-90 disabled:opacity-50"
                >
                    <span v-if="isLoading">Memproses...</span>
                    <span v-else>🔒 Tutup Hari Ini</span>
                </button>

                <p
                    v-if="todayStats?.open_sessions > 0"
                    class="text-sm text-semantic-danger text-center"
                >
                    ⚠️ Masih ada {{ todayStats.open_sessions }} sesi kasir yang
                    belum ditutup
                </p>
            </div>
        </div>
    </div>
</template>
