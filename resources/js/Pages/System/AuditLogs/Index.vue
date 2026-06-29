<script setup>
import { Head, router } from "@inertiajs/vue3";
import DashboardLayout from "@/Layouts/DashboardLayout.vue";
import DataTable from "@/Components/Table/DataTable.vue";
import Pagination from "@/Components/Navigation/Pagination.vue";
import Icon from "@/Components/Base/Icon.vue";
import Badge from "@/Components/DataDisplay/Badge.vue";

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
    { key: "module", label: "Module" },
    { key: "action", label: "Action" },
    { key: "table_name", label: "Table" },
    { key: "ip_address", label: "IP Address" },
];

function formatDate(dateStr) {
    if (!dateStr) return "-";
    return new Date(dateStr).toLocaleString("id-ID");
}
</script>

<template>
    <Head title="Audit Logs" />

    <DashboardLayout>
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Audit Logs</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Sistem pencatatan perubahan data untuk keperluan audit.
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
            <template #cell-action="{ row }">
                <Badge 
                    :label="row.action" 
                    :color="(row.action === 'CREATE' || row.action === 'INSERT') ? 'green' : (row.action === 'UPDATE' ? 'blue' : (row.action === 'DELETE' ? 'red' : 'gray'))"
                    variant="soft"
                    size="sm"
                />
            </template>
        </DataTable>

        <div class="mt-4">
            <Pagination :links="logs.links" :meta="logs" />
        </div>
    </DashboardLayout>
</template>
