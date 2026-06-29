<script setup>
import { Head } from "@inertiajs/vue3";
import DashboardLayout from "@/Layouts/DashboardLayout.vue";
import DataTable from "@/Components/Table/DataTable.vue";
import Pagination from "@/Components/Navigation/Pagination.vue";

const props = defineProps({
    logs: {
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
    { key: "created_at", label: "Date" },
    { key: "user", label: "User" },
    { key: "module_name", label: "Module" },
    { key: "action", label: "Action" },
    { key: "description", label: "Description" },
    { key: "ip_address", label: "IP Address" },
];

function formatDate(dateStr) {
    if (!dateStr) return "-";
    return new Date(dateStr).toLocaleString("id-ID");
}
</script>

<template>
    <Head title="Activity Logs" />

    <DashboardLayout>
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Activity Logs</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Sistem pencatatan aktivitas pengguna non-transaksional.
                </p>
            </div>
        </div>

        <DataTable :columns="columns" :rows="logs.data" :paginated="false" :meta="logs">
            <template #cell-created_at="{ row }">
                {{ formatDate(row.created_at) }}
            </template>
            <template #cell-user="{ row }">
                {{ row.user?.name || 'System' }}
            </template>
        </DataTable>

        <div class="mt-4">
            <Pagination :links="logs.links" :meta="logs" />
        </div>
    </DashboardLayout>
</template>
