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
import BaseButton from '@/Components/Base/BaseButton.vue';
import FormSelect from '@/Components/Form/FormSelect.vue';
import FormTextarea from '@/Components/Form/FormTextarea.vue';

const props = defineProps({
    monthClosings: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const columns = [
    { key: 'no', label: 'No' },
    { key: 'closing_number', label: 'Closing No' },
    { key: 'period', label: 'Period' },
    { key: 'total_transactions', label: 'Transactions', align: 'right' },
    { key: 'total_sales', label: 'Total Sales', align: 'right' },
    { key: 'closed_by_user', label: 'Closed By' },
    { key: 'status', label: 'Status', align: 'center' },
];

const selectedYear = ref(new Date().getFullYear());
const selectedMonth = ref(new Date().getMonth() + 1);
const notes = ref("");
const isLoading = ref(false);
const periodStatus = ref(null);

const months = [
    { value: 1, label: 'Januari' },
    { value: 2, label: 'Februari' },
    { value: 3, label: 'Maret' },
    { value: 4, label: 'April' },
    { value: 5, label: 'Mei' },
    { value: 6, label: 'Juni' },
    { value: 7, label: 'Juli' },
    { value: 8, label: 'Agustus' },
    { value: 9, label: 'September' },
    { value: 10, label: 'Oktober' },
    { value: 11, label: 'November' },
    { value: 12, label: 'Desember' },
];

const checkPeriodStatus = async () => {
    try {
        const { data } = await axios.get(route("pos.month-closings.show", { year: selectedYear.value, month: selectedMonth.value }));
        periodStatus.value = data.data;
    } catch (error) {
        console.error("Failed to load period status:", error);
    }
};

onMounted(() => {
    checkPeriodStatus();
});

const handleCloseMonth = async () => {
    if (!confirm(`Yakin ingin melakukan tutup buku bulanan untuk periode ${selectedYear.value}-${String(selectedMonth.value).padStart(2, '0')}? Tindakan ini akan mengunci semua transaksi pada periode ini.`)) {
        return;
    }

    isLoading.value = true;
    try {
        const { data } = await axios.post(route("pos.month-closings.close"), {
            closing_year: selectedYear.value,
            closing_month: selectedMonth.value,
            notes: notes.value,
        });

        if (data.success !== false) {
            toast.success("Tutup bulanan berhasil");
            router.reload();
            checkPeriodStatus();
            notes.value = "";
        } else {
            toast.error(data.message);
        }
    } catch (error) {
        toast.error(error.response?.data?.message || "Gagal melakukan tutup buku bulanan");
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <Head title="Tutup Bulanan" />
    <DashboardLayout>
        <div class="mb-xl flex flex-col md:flex-row justify-between items-start md:items-center gap-md">
            <div>
                <h1 class="text-page-title font-extrabold text-ink-primary">
                    🛡️ Tutup Bulanan (Month Closing)
                </h1>
                <p class="text-ink-secondary mt-xs text-base">
                    Lakukan tutup buku bulanan dan pembekuan transaksi keuangan periode terpilih.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-base items-start mb-xl">
            <!-- Parameter Form -->
            <div class="card-friendly p-lg lg:col-span-2 space-y-md">
                <h3 class="text-lg font-bold text-ink-primary border-b border-border-soft pb-sm mb-sm">
                    Parameter Penutupan Bulanan
                </h3>
                <div class="space-y-md">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-md">
                        <FormSelect
                            label="Tahun Buku"
                            v-model="selectedYear"
                            required
                            @change="checkPeriodStatus"
                        >
                            <option v-for="y in [2025, 2026, 2027]" :key="y" :value="y">{{ y }}</option>
                        </FormSelect>
                        
                        <FormSelect
                            label="Bulan Buku"
                            v-model="selectedMonth"
                            required
                            @change="checkPeriodStatus"
                        >
                            <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
                        </FormSelect>
                    </div>

                    <FormTextarea
                        v-model="notes"
                        label="Catatan"
                        placeholder="Catatan penutupan bulanan (opsional)"
                        rows="3"
                    />

                    <BaseButton
                        @click="handleCloseMonth"
                        :disabled="isLoading || periodStatus?.status === 'CLOSED'"
                        variant="primary"
                        class="w-full justify-center py-3 text-base font-semibold"
                    >
                        <span v-if="isLoading">Memproses...</span>
                        <span v-else>🛡️ Tutup Buku Periode</span>
                    </BaseButton>
                </div>
            </div>

            <!-- Status Card -->
            <div v-if="periodStatus" class="card-friendly p-lg space-y-md flex flex-col justify-between self-stretch">
                <div>
                    <h3 class="text-lg font-bold text-ink-primary border-b border-border-soft pb-sm mb-base">
                        Status Periode
                    </h3>
                    <div class="space-y-md">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Status Pembukuan</p>
                            <div class="mt-xs">
                                <StatusBadge :status="periodStatus.status === 'CLOSED' ? 'CLOSED' : 'OPEN'" variant="soft" size="md" />
                            </div>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Hari Terkunci</p>
                            <p class="text-2xl font-extrabold text-ink-primary mt-xs">{{ periodStatus.days_closed }} Hari</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Total Penjualan</p>
                            <p class="text-2xl font-extrabold text-brand mt-xs">{{ formatPrice(periodStatus.total_sales) }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="card-friendly p-lg text-sm text-ink-secondary">
                Memuat status periode...
            </div>
        </div>

        <!-- Riwayat Tutup Bulanan -->
        <div>
            <h2 class="text-xl font-bold text-ink-primary mb-md">📜 Riwayat Tutup Bulanan</h2>
            <div class="card-friendly p-lg">
                <DataTable :columns="columns" :rows="monthClosings">
                    <template #cell-closing_number="{ value }">
                        <span class="font-mono text-xs font-bold text-brand">{{ value }}</span>
                    </template>
                    <template #cell-period="{ row }">
                        <span>{{ months.find(m => m.value === row.closing_month)?.label }} {{ row.closing_year }}</span>
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
                        <StatusBadge status="CLOSED" variant="soft" size="sm" />
                    </template>
                </DataTable>
                <div v-if="monthClosings.length === 0" class="text-center text-ink-secondary py-xl">
                    Belum ada riwayat penutupan bulanan.
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
