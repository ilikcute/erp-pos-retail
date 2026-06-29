<script setup>
import { ref } from "vue";
import { Head, useForm, router } from "@inertiajs/vue3";
import DashboardLayout from "@/Layouts/DashboardLayout.vue";
import DataTable from "@/Components/Table/DataTable.vue";
import Pagination from "@/Components/Navigation/Pagination.vue";
import BaseModal from "@/Components/Modal/BaseModal.vue";
import BaseButton from "@/Components/Base/BaseButton.vue";
import FormTextarea from "@/Components/Form/FormTextarea.vue";
import StatusBadge from "@/Components/DataDisplay/StatusBadge.vue";

const props = defineProps({
    approvals: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const columns = [
    { key: "no", label: "No" },
    { key: "approval_number", label: "Number" },
    { key: "module", label: "Module" },
    { key: "requestor", label: "Requested By" },
    { key: "status", label: "Status" },
    { key: "created_at", label: "Date" },
    { key: "actions", label: "Actions" },
];

const showModal = ref(false);
const actionType = ref(""); // 'approve' or 'reject'
const selectedApproval = ref(null);

const form = useForm({
    notes: "",
});

function openModal(approval, type) {
    selectedApproval.value = approval;
    actionType.value = type;
    form.reset();
    form.clearErrors();
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    selectedApproval.value = null;
    actionType.value = "";
    form.reset();
}

function submit() {
    if (!selectedApproval.value) return;
    
    if (actionType.value === "approve") {
        form.post(route("system.approvals.approve", selectedApproval.value.id), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    } else if (actionType.value === "reject") {
        form.post(route("system.approvals.reject", selectedApproval.value.id), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    }
}

function formatDate(dateStr) {
    if (!dateStr) return "-";
    return new Date(dateStr).toLocaleString("id-ID");
}
</script>

<template>
    <Head title="Pending Approvals" />

    <DashboardLayout>
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Pending Approvals</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Manage requests that need your authorization.
                </p>
            </div>
        </div>

        <DataTable :columns="columns" :rows="approvals.data" :paginated="false" :meta="approvals">
            <template #cell-requestor="{ row }">
                {{ row.requestor?.name || '-' }}
            </template>
            <template #cell-created_at="{ row }">
                {{ formatDate(row.created_at) }}
            </template>
            <template #cell-status="{ row }">
                <StatusBadge :status="row.status" size="sm" variant="soft" />
            </template>
            <template #cell-actions="{ row }">
                <button
                    @click="openModal(row, 'approve')"
                    class="mr-3 font-medium cursor-pointer text-green-600 hover:text-green-800"
                >
                    Approve
                </button>
                <button
                    @click="openModal(row, 'reject')"
                    class="font-medium cursor-pointer text-red-600 hover:text-red-800"
                >
                    Reject
                </button>
            </template>
        </DataTable>

        <div class="mt-4">
            <Pagination :links="approvals.links" :meta="approvals" />
        </div>

        <BaseModal
            :show="showModal"
            :title="actionType === 'approve' ? 'Approve Request' : 'Reject Request'"
            size="md"
            @close="closeModal"
        >
            <form @submit.prevent="submit">
                <div class="space-y-4">
                    <p class="text-sm text-gray-600">
                        Anda akan {{ actionType === 'approve' ? 'menyetujui' : 'menolak' }} 
                        request <strong>{{ selectedApproval?.approval_number }}</strong>.
                    </p>
                    
                    <FormTextarea
                        v-model="form.notes"
                        label="Notes (Optional for Approve, Required for Reject)"
                        :error="form.errors.notes"
                        :required="actionType === 'reject'"
                        :rows="3"
                        placeholder="Berikan alasan atau catatan tambahan..."
                    />
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <BaseButton variant="secondary" @click="closeModal" type="button">
                        Cancel
                    </BaseButton>
                    <BaseButton type="submit" :variant="actionType === 'approve' ? 'primary' : 'danger'" :loading="form.processing">
                        {{ actionType === 'approve' ? 'Approve Request' : 'Reject Request' }}
                    </BaseButton>
                </div>
            </form>
        </BaseModal>
    </DashboardLayout>
</template>
