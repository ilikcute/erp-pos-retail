<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import DataTable from '@/Components/Table/DataTable.vue';
import Pagination from "@/Components/Navigation/Pagination.vue";
import BaseButton from '@/Components/Base/BaseButton.vue';
import BaseModal from '@/Components/Modal/BaseModal.vue';
import FormInput from '@/Components/Form/FormInput.vue';
import FormSelect from '@/Components/Form/FormSelect.vue';
import FormTextarea from '@/Components/Form/FormTextarea.vue';
import StatusBadge from '@/Components/DataDisplay/StatusBadge.vue';
import toast from "@/Utils/toast";
import axios from 'axios';
import { formatCurrency, formatDate } from '@/Utils/formatters';
import SessionOpener from '@/Pages/POS/Components/SessionOpener.vue';

const props = defineProps({
    shifts: { type: Object, default: () => ({ data: [] }) },
    sessions: { type: Array, default: () => [] },
    locations: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const activeTab = ref('sessions'); // 'sessions' or 'shifts'
const searchQuery = ref(props.filters.search || '');

const handleSearch = () => {
    router.get('/pos/shifts', { search: searchQuery.value }, { preserveState: true, replace: true });
};

// Columns for shifts
const shiftColumns = [
    { key: 'no', label: 'No' },
    { key: 'shift_code', label: 'Shift Code' },
    { key: 'shift_name', label: 'Shift Name' },
    { key: 'start_time', label: 'Start Time', align: 'center' },
    { key: 'end_time', label: 'End Time', align: 'center' },
    { key: 'status', label: 'Status', align: 'center' },
    { key: 'actions', label: 'Actions', align: 'center' },
];

// Columns for sessions
const sessionColumns = [
    { key: 'no', label: 'No' },
    { key: 'session_no', label: 'Session No' },
    { key: 'user', label: 'Kasir' },
    { key: 'shift', label: 'Shift' },
    { key: 'shift_start', label: 'Mulai Shift', align: 'center' },
    { key: 'shift_end', label: 'Akhir Shift', align: 'center' },
    { key: 'opened_at', label: 'Waktu Buka' },
    { key: 'closed_at', label: 'Waktu Tutup' },
    { key: 'opening_cash', label: 'Modal Awal', align: 'right' },
    { key: 'expected_cash', label: 'Estimasi Kas', align: 'right' },
    { key: 'closing_cash', label: 'Tunai Aktual', align: 'right' },
    { key: 'cash_difference', label: 'Selisih', align: 'right' },
    { key: 'reimbursement_amount', label: 'Penggantian', align: 'right' },
    { key: 'variance_reason', label: 'Informasi Variance' },
    { key: 'status', label: 'Status', align: 'center' },
    { key: 'actions', label: 'Aksi', align: 'center' },
];

// CRUD shifts state
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const form = ref({
    shift_code: '',
    shift_name: '',
    start_time: '',
    end_time: '',
    description: '',
    is_active: true,
});

const openCreateModal = () => {
    isEditing.value = false;
    editingId.value = null;
    form.value = {
        shift_code: '',
        shift_name: '',
        start_time: '08:00',
        end_time: '16:00',
        description: '',
        is_active: true,
    };
    showModal.value = true;
};

const openEditModal = (row) => {
    isEditing.value = true;
    editingId.value = row.id;
    form.value = {
        shift_code: row.shift_code,
        shift_name: row.shift_name,
        start_time: row.start_time,
        end_time: row.end_time,
        description: row.description || '',
        is_active: !!row.is_active,
    };
    showModal.value = true;
};

const submitForm = () => {
    if (isEditing.value) {
        router.put(`/pos/shifts/${editingId.value}`, form.value, {
            onSuccess: () => {
                showModal.value = false;
            }
        });
    } else {
        router.post('/pos/shifts', form.value, {
            onSuccess: () => {
                showModal.value = false;
            }
        });
    }
};

const deleteShift = (id) => {
    if (confirm('Yakin ingin menghapus shift ini?')) {
        router.delete(`/pos/shifts/${id}`);
    }
};

// Rupiah Formatter for Inputs
const formatRupiahInput = (value) => {
    if (value === null || value === undefined || value === '') return '';
    const numberString = String(value).replace(/[^0-9]/g, '');
    if (!numberString) return '';
    return numberString.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
};

const parseRupiahInput = (formattedValue) => {
    if (!formattedValue) return 0;
    const cleanString = String(formattedValue).replace(/[^0-9]/g, '');
    return Number(cleanString) || 0;
};

const openingCashDisplay = ref('');
const closingCashDisplay = ref('');

// Open Cashier Session state
const showOpenSessionModal = ref(false);

const openOpenSessionModal = () => {
    showOpenSessionModal.value = true;
};

const handleSessionOpened = () => {
    showOpenSessionModal.value = false;
    router.reload();
};

// Close cashier session state
const showCloseModal = ref(false);
const isClosing = ref(false);
const selectedSession = ref(null);
const closeForm = ref({
    closing_cash: 0,
    notes: '',
});

const showVarianceModal = ref(false);
const varianceForm = ref({
    reimbursement_amount: 0,
    variance_reason: '',
});
const reimbursementAmountDisplay = ref('');

const onReimbursementAmountInput = (e) => {
    const formatted = formatRupiahInput(e.target.value);
    reimbursementAmountDisplay.value = formatted;
    varianceForm.value.reimbursement_amount = parseRupiahInput(formatted);
};

const openCloseModal = (session) => {
    selectedSession.value = session;
    closeForm.value = {
        closing_cash: session.expected_cash || 0,
        notes: '',
    };
    closingCashDisplay.value = formatRupiahInput(session.expected_cash || 0);
    showCloseModal.value = true;
};

const onOpeningCashInput = (e) => {
    const formatted = formatRupiahInput(e.target.value);
    openingCashDisplay.value = formatted;
    openSessionForm.value.opening_cash = parseRupiahInput(formatted);
};

const onClosingCashInput = (e) => {
    const formatted = formatRupiahInput(e.target.value);
    closingCashDisplay.value = formatted;
    closeForm.value.closing_cash = parseRupiahInput(formatted);
};

const submitCloseSession = async (bypassVariance = false) => {
    if (!selectedSession.value) return;

    const diff = Number(closeForm.value.closing_cash) - Number(selectedSession.value.expected_cash);
    if (diff !== 0 && !bypassVariance) {
        varianceForm.value = {
            reimbursement_amount: 0,
            variance_reason: '',
        };
        reimbursementAmountDisplay.value = '';
        showVarianceModal.value = true;
        return;
    }

    isClosing.value = true;
    try {
        const payload = {
            closing_cash: Number(closeForm.value.closing_cash) || 0,
            notes: closeForm.value.notes,
        };

        if (diff !== 0) {
            payload.reimbursement_amount = Number(varianceForm.value.reimbursement_amount) || 0;
            payload.variance_reason = varianceForm.value.variance_reason;
        }

        const { data } = await axios.post(
            `/pos/sessions/${selectedSession.value.id}/close`,
            payload
        );
        if (data?.success !== false) {
            toast.success("Sesi kasir berhasil ditutup.");
            showCloseModal.value = false;
            showVarianceModal.value = false;
            selectedSession.value = null;
            router.reload();
        } else {
            toast.error(data?.message || "Gagal menutup sesi.");
        }
    } catch (e) {
        toast.error(e.response?.data?.message || "Terjadi kesalahan saat menutup sesi.");
    } finally {
        isClosing.value = false;
    }
};

const submitCloseSessionWithVariance = () => {
    submitCloseSession(true);
};
</script>

<template>
    <Head title="POS Shift & Session Management" />

    <DashboardLayout>
        <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-md">
            <div>
                <h1 class="text-2xl font-bold text-ink-primary">
                    Shift & Cashier Sessions ⏰
                </h1>
                <p class="text-ink-secondary text-sm">
                    Pantau sesi kasir yang sedang aktif dan kelola jadwal master shift operasional retail.
                </p>
            </div>
            <div>
                <BaseButton v-if="activeTab === 'sessions'" @click="openOpenSessionModal">
                    🟢 Open Session
                </BaseButton>
                <BaseButton v-if="activeTab === 'shifts'" @click="openCreateModal">
                    ➕ New Shift
                </BaseButton>
            </div>
        </div>

        <!-- Tabs -->
        <div class="flex space-x-1 mb-6 border-b border-border-soft">
            <button @click="activeTab = 'sessions'"
                :class="activeTab === 'sessions' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-3 px-6 font-medium transition-colors duration-200 cursor-pointer text-sm"
            >
                Sesi Kasir Aktif 🟢
            </button>
            <button @click="activeTab = 'shifts'"
                :class="activeTab === 'shifts' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-3 px-6 font-medium transition-colors duration-200 cursor-pointer text-sm"
            >
                Jadwal Shift ⏰
            </button>
        </div>

        <!-- Tab 1: Sessions List -->
        <div v-if="activeTab === 'sessions'" class="card-friendly p-lg overflow-x-auto">
            <DataTable :columns="sessionColumns" :rows="sessions">
                <template #cell-session_no="{ row }">
                    <span class="font-mono font-bold text-ink-primary text-xs">{{ row.session_no }}</span>
                </template>
                <template #cell-user="{ row }">
                    <span class="font-semibold text-ink-primary text-xs">{{ row.user?.name || 'Kasir' }}</span>
                </template>
                <template #cell-shift="{ row }">
                    <span class="text-xs">{{ row.shift?.shift_name || '-' }}</span>
                </template>
                <template #cell-shift_start="{ row }">
                    <span class="font-mono text-xs">{{ row.shift?.start_time || '-' }}</span>
                </template>
                <template #cell-shift_end="{ row }">
                    <span class="font-mono text-xs">{{ row.shift?.end_time || '-' }}</span>
                </template>
                <template #cell-opened_at="{ value }">
                    <span class="text-xs">{{ formatDate(value) }}</span>
                </template>
                <template #cell-closed_at="{ value }">
                    <span class="text-xs">{{ value ? formatDate(value) : '-' }}</span>
                </template>
                <template #cell-opening_cash="{ value }">
                    <span class="font-mono text-xs text-ink-muted">{{ formatCurrency(value || 0) }}</span>
                </template>
                <template #cell-expected_cash="{ value }">
                    <span class="font-mono text-xs text-ink-muted font-bold">{{ formatCurrency(value || 0) }}</span>
                </template>
                <template #cell-closing_cash="{ value }">
                    <span class="font-mono text-xs text-ink-primary font-bold">{{ value !== null ? formatCurrency(value) : '-' }}</span>
                </template>
                <template #cell-cash_difference="{ value, row }">
                    <span v-if="row.status === 'CLOSED'" 
                          :class="Number(value) > 0 ? 'text-semantic-success' : (Number(value) < 0 ? 'text-semantic-danger font-semibold' : 'text-ink-muted')"
                          class="font-mono text-xs font-bold">
                        {{ formatCurrency(value || 0) }}
                    </span>
                    <span v-else class="text-xs text-ink-muted">-</span>
                </template>
                <template #cell-reimbursement_amount="{ value, row }">
                    <span v-if="row.status === 'CLOSED' && Number(value) > 0" class="font-mono text-xs text-brand font-bold">
                        {{ formatCurrency(value) }}
                    </span>
                    <span v-else-if="row.status === 'CLOSED'" class="text-xs text-ink-muted">Rp 0</span>
                    <span v-else class="text-xs text-ink-muted">-</span>
                </template>
                <template #cell-variance_reason="{ value, row }">
                    <span v-if="row.status === 'CLOSED'" class="text-xs text-ink-secondary block max-w-[150px] truncate" :title="value">
                        {{ value || '-' }}
                    </span>
                    <span v-else class="text-xs text-ink-muted">-</span>
                </template>
                <template #cell-status="{ value }">
                    <StatusBadge :status="value" variant="soft" size="sm" />
                </template>
                <template #cell-actions="{ row }">
                    <div class="flex gap-sm justify-center">
                        <button v-if="row.status === 'OPEN'" @click="openCloseModal(row)"
                            class="px-2.5 py-1 rounded bg-semantic-danger-soft text-semantic-danger text-xs font-semibold hover:bg-semantic-danger/20 transition"
                        >
                            🔒 Tutup Sesi
                        </button>
                        <span v-else class="text-xs text-ink-muted">Selesai</span>
                    </div>
                </template>
            </DataTable>
        </div>

        <!-- Tab 2: Shifts List -->
        <div v-if="activeTab === 'shifts'">
            <!-- Search Bar -->
            <div class="mb-6 flex items-center gap-md max-w-md bg-surface-card rounded-lg border border-border-soft p-sm shadow-soft">
                <svg class="w-5 h-5 text-ink-muted ml-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input
                    v-model="searchQuery"
                    @input="handleSearch"
                    type="text"
                    placeholder="Search shifts..."
                    class="w-full bg-transparent border-none text-ink-primary text-sm focus:outline-none focus:ring-0 p-0"
                />
            </div>

            <div class="card-friendly p-lg">
                <DataTable :columns="shiftColumns" :rows="shifts.data" :paginated="false">
                    <template #cell-shift_code="{ value }">
                        <span class="font-mono text-xs font-bold text-brand">{{ value }}</span>
                    </template>
                    <template #cell-shift_name="{ value }">
                        <span class="font-semibold text-ink-primary">{{ value }}</span>
                    </template>
                    <template #cell-start_time="{ value }">
                        <span class="font-mono text-xs text-ink-primary">{{ value }}</span>
                    </template>
                    <template #cell-end_time="{ value }">
                        <span class="font-mono text-xs text-ink-primary">{{ value }}</span>
                    </template>
                    <template #cell-status="{ row }">
                        <span
                            :class="row.is_active ? 'bg-semantic-success-soft text-semantic-success' : 'bg-surface-subtle text-ink-muted'"
                            class="px-2.5 py-0.5 rounded-full text-xs font-semibold"
                        >
                            {{ row.is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </template>
                    <template #cell-actions="{ row }">
                        <div class="flex gap-sm justify-center">
                            <button @click="openEditModal(row)" class="text-brand hover:underline text-sm font-semibold">Edit</button>
                            <button @click="deleteShift(row.id)" class="text-semantic-danger hover:underline text-sm font-semibold ml-xs">Delete</button>
                        </div>
                    </template>
                </DataTable>
                <div class="mt-4">
                    <Pagination :links="shifts.links" :meta="shifts" />
                </div>
            </div>
        </div>

        <!-- Shift Create / Edit Modal -->
        <BaseModal :show="showModal" @close="showModal = false" :title="isEditing ? 'Edit Shift' : 'New Shift'">
            <form @submit.prevent="submitForm" class="space-y-md">
                <FormInput
                    label="Shift Code"
                    v-model="form.shift_code"
                    required
                    :disabled="isEditing"
                    placeholder="E.g., SH-PAGI"
                />
                
                <FormInput
                    label="Shift Name"
                    v-model="form.shift_name"
                    required
                    placeholder="E.g., Shift Pagi"
                />

                <div class="grid grid-cols-2 gap-md">
                    <FormInput
                        label="Start Time"
                        v-model="form.start_time"
                        type="time"
                        required
                    />
                    <FormInput
                        label="End Time"
                        v-model="form.end_time"
                        type="time"
                        required
                    />
                </div>

                <FormTextarea
                    label="Description"
                    v-model="form.description"
                    placeholder="Keterangan shift (opsional)"
                    rows="3"
                />

                <div class="flex items-center gap-sm">
                    <input
                        id="is_active"
                        v-model="form.is_active"
                        type="checkbox"
                        class="rounded border-border-soft text-brand focus:ring-brand"
                    />
                    <label for="is_active" class="text-sm font-medium text-ink-primary">Active</label>
                </div>

                <div class="flex justify-end gap-sm pt-md border-t border-border-soft">
                    <BaseButton type="button" variant="secondary" @click="showModal = false">Cancel</BaseButton>
                    <BaseButton type="submit" variant="primary">Save Changes</BaseButton>
                </div>
            </form>
        </BaseModal>

        <!-- Open Cashier Session Modal -->
        <BaseModal :show="showOpenSessionModal" @close="showOpenSessionModal = false" title="🟢 Buka Sesi Kasir Baru">
            <div class="p-6">
                <SessionOpener
                    :is-manager-mode="true"
                    :locations="locations"
                    :users="users"
                    @opened="handleSessionOpened"
                    @close="showOpenSessionModal = false"
                />
            </div>
        </BaseModal>

        <!-- Close Cashier Session Modal -->
        <BaseModal :show="showCloseModal" @close="showCloseModal = false" title="🔒 Tutup Sesi Kasir">
            <form @submit.prevent="submitCloseSession(false)" class="space-y-md" v-if="selectedSession">
                <div class="bg-surface-main p-lg rounded-xl border border-border-soft space-y-sm text-sm">
                    <div class="flex justify-between">
                        <span class="text-ink-muted">No Sesi:</span>
                        <span class="font-bold text-ink-primary font-mono">{{ selectedSession.session_no }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-ink-muted">Kasir:</span>
                        <span class="font-bold text-ink-primary">{{ selectedSession.user?.name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-ink-muted">Modal Awal Kas:</span>
                        <span class="font-semibold text-ink-primary font-mono">{{ formatCurrency(selectedSession.opening_cash || 0) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-ink-muted">Total Penjualan:</span>
                        <span class="font-semibold text-brand font-mono">{{ formatCurrency(selectedSession.total_sales || 0) }}</span>
                    </div>
                    <div class="border-t border-brand/20 bg-brand-soft/20 p-sm rounded-lg flex justify-between items-center mt-xs">
                        <span class="font-bold text-brand">Estimasi Uang Tunai di Laci:</span>
                        <span class="font-extrabold text-brand text-lg font-mono">{{ formatCurrency(selectedSession.expected_cash || 0) }}</span>
                    </div>
                </div>

                <FormInput
                    :model-value="closingCashDisplay"
                    @input="onClosingCashInput"
                    type="text"
                    label="Uang Kasir Aktual (Tunai Aktual)"
                    required
                    placeholder="Masukkan jumlah kas laci saat ini..."
                />

                <FormTextarea
                    v-model="closeForm.notes"
                    label="Catatan & Selisih Kas"
                    placeholder="Keterangan opsional..."
                    rows="3"
                />

                <div class="flex justify-end gap-sm pt-md border-t border-border-soft">
                    <BaseButton type="button" variant="secondary" @click="showCloseModal = false">Batal</BaseButton>
                    <BaseButton type="submit" variant="primary" :disabled="isClosing">
                        <span v-if="isClosing">Memproses...</span>
                        <span v-else>🔒 Tutup Sesi Kasir</span>
                    </BaseButton>
                </div>
            </form>
        </BaseModal>

        <!-- Variance Information Modal -->
        <BaseModal :show="showVarianceModal" @close="showVarianceModal = false" title="⚠️ Selisih Kas Terdeteksi">
            <form @submit.prevent="submitCloseSessionWithVariance" class="space-y-md" v-if="selectedSession">
                <div class="bg-semantic-warning-soft/30 p-lg rounded-xl border border-semantic-warning/20 space-y-sm text-sm">
                    <div class="flex justify-between">
                        <span class="text-ink-muted">Uang Estimasi (Expected):</span>
                        <span class="font-bold text-ink-primary font-mono">{{ formatCurrency(selectedSession.expected_cash || 0) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-ink-muted">Uang Aktual (Aktual):</span>
                        <span class="font-bold text-ink-primary font-mono">{{ formatCurrency(closeForm.closing_cash || 0) }}</span>
                    </div>
                    <div class="border-t border-semantic-warning/20 pt-sm flex justify-between items-center">
                        <span class="font-bold text-semantic-warning">Selisih Kas:</span>
                        <span class="font-extrabold text-semantic-warning text-lg font-mono">
                            {{ formatCurrency(closeForm.closing_cash - (selectedSession.expected_cash || 0)) }}
                        </span>
                    </div>
                </div>

                <div class="text-xs text-ink-muted leading-relaxed">
                    Terdapat perbedaan antara uang fisik di laci dengan estimasi sistem. Harap isi nilai penggantian (reimbursement) atau informasi/penjelasan mengenai selisih tersebut.
                </div>

                <FormInput
                    :model-value="reimbursementAmountDisplay"
                    @input="onReimbursementAmountInput"
                    type="text"
                    label="Nilai Penggantian (Reimbursement)"
                    placeholder="Masukkan nilai uang penggantian jika ada..."
                />

                <FormTextarea
                    v-model="varianceForm.variance_reason"
                    label="Informasi / Alasan Variance"
                    placeholder="Jelaskan alasan selisih kas (wajib jika tidak ada nilai penggantian)..."
                    rows="3"
                />

                <div class="flex justify-end gap-sm pt-md border-t border-border-soft">
                    <BaseButton type="button" variant="secondary" @click="showVarianceModal = false">Batal</BaseButton>
                    <BaseButton type="submit" variant="primary" :disabled="isClosing || (!varianceForm.reimbursement_amount && !varianceForm.variance_reason.trim())">
                        <span v-if="isClosing">Memproses...</span>
                        <span v-else>🔒 Konfirmasi & Tutup Sesi</span>
                    </BaseButton>
                </div>
            </form>
        </BaseModal>
    </DashboardLayout>
</template>
