<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import DataTable from '@/Components/Table/DataTable.vue';
import Pagination from "@/Components/Navigation/Pagination.vue";
import BaseButton from '@/Components/Base/BaseButton.vue';
import BaseModal from '@/Components/Modal/BaseModal.vue';
import FormInput from '@/Components/Form/FormInput.vue';
import FormTextarea from '@/Components/Form/FormTextarea.vue';

const props = defineProps({
    shifts: { type: Object, default: () => ({ data: [] }) },
    filters: { type: Object, default: () => ({}) },
});

const searchQuery = ref(props.filters.search || '');

const handleSearch = () => {
    router.get('/pos/shifts', { search: searchQuery.value }, { preserveState: true, replace: true });
};

const columns = [
    { key: 'no', label: 'No' },
    { key: 'shift_code', label: 'Shift Code' },
    { key: 'shift_name', label: 'Shift Name' },
    { key: 'start_time', label: 'Start Time', align: 'center' },
    { key: 'end_time', label: 'End Time', align: 'center' },
    { key: 'status', label: 'Status', align: 'center' },
    { key: 'actions', label: 'Actions', align: 'center' },
];

// CRUD State
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
</script>

<template>
    <Head title="Shift Management" />

    <DashboardLayout>
        <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-md">
            <div>
                <h1 class="text-2xl font-bold text-ink-primary">
                    Shift Management ⏰
                </h1>
                <p class="text-ink-secondary text-sm">
                    Kelola jam kerja operasional untuk kasir POS.
                </p>
            </div>
            <div>
                <BaseButton @click="openCreateModal">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Shift
                </BaseButton>
            </div>
        </div>

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

        <!-- Data Table -->
        <DataTable :columns="columns" :rows="shifts.data" :paginated="false">
            <template #cell-cashier="{ value }">
                <span class="font-semibold text-ink-primary">{{ value }}</span>
            </template>
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

        <!-- Create / Edit Modal -->
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
    </DashboardLayout>
</template>
