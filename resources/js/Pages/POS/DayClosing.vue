<script setup>
import { ref, onMounted } from "vue";
import { router, Head } from "@inertiajs/vue3";
import axios from "axios";
import toast from "@/Utils/toast";
import { formatPrice } from "@/Utils/formatPrice";
import { formatDate } from "@/Utils/formatters";
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import DataTable from '@/Components/Table/DataTable.vue';
import StatusBadge from '@/Components/DataDisplay/StatusBadge.vue';
import FormInput from '@/Components/Form/FormInput.vue';
import FormTextarea from '@/Components/Form/FormTextarea.vue';

const props = defineProps({
    dayClosings: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const columns = [
    { key: 'no', label: 'No' },
    { key: 'closing_number', label: 'Closing No' },
    { key: 'closing_date', label: 'Date' },
    { key: 'total_transactions', label: 'Transactions', align: 'right' },
    { key: 'total_sales', label: 'Total Sales', align: 'right' },
    { key: 'closed_by_user', label: 'Closed By' },
    { key: 'status', label: 'Status', align: 'center' },
];

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
    <Head title="Tutup Harian" />
    <DashboardLayout>
        <div class="mb-xl flex flex-col md:flex-row justify-between items-start md:items-center gap-md">
            <div>
                <h1 class="text-page-title font-extrabold text-ink-primary">
                    🔒 Tutup Harian (Day Closing)
                </h1>
                <p class="text-ink-secondary mt-xs text-base">
                    Rekonsiliasi transaksi kasir harian dan tutup pembukuan kas POS.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-base items-start mb-xl">
            <!-- Form Tutup -->
            <div class="card-friendly p-lg lg:col-span-2 space-y-md">
                <h3 class="text-lg font-bold text-ink-primary border-b border-border-soft pb-sm mb-sm">
                    Parameter Penutupan
                </h3>
                <div class="space-y-md">
                    <FormInput
                        v-model="closingDate"
                        type="date"
                        label="Tanggal Penutupan"
                        required
                        :max="new Date().toISOString().split('T')[0]"
                    />

                    <FormTextarea
                        v-model="notes"
                        label="Catatan"
                        placeholder="Catatan penutupan harian (opsional)"
                        rows="3"
                    />

                    <button
                        @click="handleClose"
                        :disabled="isLoading || todayStats?.open_sessions > 0"
                        class="w-full rounded-xl bg-semantic-danger px-5 py-3 text-white font-semibold hover:opacity-90 disabled:opacity-50 transition-opacity"
                    >
                        <span v-if="isLoading">Memproses...</span>
                        <span v-else>🔒 Tutup Hari Ini</span>
                    </button>

                    <p
                        v-if="todayStats?.open_sessions > 0"
                        class="text-sm text-semantic-danger text-center bg-semantic-danger-soft/10 p-sm rounded-lg border border-semantic-danger/20"
                    >
                        ⚠️ Masih ada {{ todayStats.open_sessions }} sesi kasir yang belum ditutup
                    </p>
                </div>
            </div>

            <!-- Statistik Hari Ini -->
            <div v-if="todayStats" class="space-y-base">
                <div class="card-friendly p-lg">
                    <p class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Total Transaksi</p>
                    <p class="text-3xl font-extrabold text-ink-primary mt-xs">
                        {{ todayStats.total_transactions }}
                    </p>
                </div>
                <div class="card-friendly p-lg">
                    <p class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Total Penjualan</p>
                    <p class="text-3xl font-extrabold text-brand mt-xs">
                        {{ formatPrice(todayStats.total_sales) }}
                    </p>
                </div>
                <div class="card-friendly p-lg">
                    <p class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Total Cash</p>
                    <p class="text-3xl font-extrabold text-accent-mint mt-xs">
                        {{ formatPrice(todayStats.total_cash) }}
                    </p>
                </div>
                <div class="card-friendly p-lg">
                    <p class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Sesi Aktif</p>
                    <p
                        class="text-3xl font-extrabold mt-xs"
                        :class="todayStats.open_sessions > 0 ? 'text-semantic-danger' : 'text-accent-mint'"
                    >
                        {{ todayStats.open_sessions }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Riwayat Tutup Harian -->
        <div>
            <h2 class="text-xl font-bold text-ink-primary mb-md">📜 Riwayat Tutup Harian</h2>
            <div class="card-friendly p-lg">
                <DataTable :columns="columns" :rows="dayClosings">
                    <template #cell-closing_number="{ value }">
                        <span class="font-mono text-xs font-bold text-brand">{{ value }}</span>
                    </template>
                    <template #cell-closing_date="{ value }">
                        <span>{{ formatDate(value) }}</span>
                    </template>
                    <template #cell-total_transactions="{ value }">
                        <span class="font-mono">{{ value }}</span>
                    </template>
                    <template #cell-total_sales="{ value }">
                        <span class="font-mono font-bold text-brand">{{ formatPrice(value) }}</span>
                    </template>
                    <template #cell-closed_by_user="{ row }">
                        <span>{{ row.closed_by_user?.name || '-' }}</span>
                    </template>
                    <template #cell-status="{ row }">
                        <StatusBadge status="POSTED" variant="soft" size="sm" />
                    </template>
                </DataTable>
                <div v-if="dayClosings.length === 0" class="text-center text-ink-secondary py-xl">
                    Belum ada riwayat penutupan harian.
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
