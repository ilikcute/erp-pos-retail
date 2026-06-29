<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import DashboardLayout from "@/Layouts/DashboardLayout.vue";
import DataTable from "@/Components/Table/DataTable.vue";
import Pagination from "@/Components/Navigation/Pagination.vue";
import BaseButton from "@/Components/Base/BaseButton.vue";
import Icon from "@/Components/Base/Icon.vue";

const props = defineProps({
    notifications: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const columns = [
    { key: "message", label: "Message" },
    { key: "created_at", label: "Date" },
    { key: "actions", label: "Actions" },
];

function markAsRead(id) {
    router.post(route("system.notifications.read", id), {}, {
        preserveScroll: true,
    });
}

function markAllAsRead() {
    router.post(route("system.notifications.read-all"), {}, {
        preserveScroll: true,
    });
}

function toggleIncludeRead(e) {
    router.get(
        route("system.notifications.index"),
        { include_read: e.target.checked ? 1 : 0 },
        { preserveState: true, preserveScroll: true }
    );
}

function formatDate(dateStr) {
    if (!dateStr) return "-";
    return new Date(dateStr).toLocaleString("id-ID");
}

function getTitle(notif) {
    return notif.data?.title || notif.title || 'Notifikasi Baru';
}

function getMessage(notif) {
    return notif.data?.message || notif.message || notif.data?.description || 'Tidak ada detail.';
}
</script>

<template>
    <Head title="Notifications" />

    <DashboardLayout>
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Stay updated with system alerts and workflow requests.
                </p>
            </div>
            <div class="mt-4 sm:mt-0 flex flex-col sm:flex-row items-center gap-4">
                <label class="inline-flex items-center">
                    <input type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" :checked="filters.include_read" @change="toggleIncludeRead">
                    <span class="ml-2 text-sm text-gray-600">Tampilkan yang sudah dibaca</span>
                </label>
                
                <BaseButton variant="secondary" @click="markAllAsRead">
                    <Icon name="check-circle" size="4" class="mr-2" />
                    Tandai Semua Dibaca
                </BaseButton>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <ul v-if="notifications.data.length > 0" class="divide-y divide-gray-200">
                <li v-for="notif in notifications.data" :key="notif.id" :class="notif.read_at ? 'bg-white' : 'bg-blue-50'" class="p-4 hover:bg-gray-50 flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <div class="mt-1">
                            <Icon name="bell" size="5" :class="notif.read_at ? 'text-gray-400' : 'text-blue-500'" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">
                                {{ getTitle(notif) }}
                            </p>
                            <p class="text-sm text-gray-600 mt-0.5">
                                {{ getMessage(notif) }}
                            </p>
                            <p class="text-xs text-gray-400 mt-1">
                                {{ formatDate(notif.created_at) }}
                            </p>
                        </div>
                    </div>
                    <div>
                        <button v-if="!notif.read_at" @click="markAsRead(notif.id)" class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                            Tandai Dibaca
                        </button>
                    </div>
                </li>
            </ul>
            <div v-else class="p-8 text-center text-gray-500">
                Belum ada notifikasi saat ini.
            </div>
        </div>

        <div class="mt-4">
            <Pagination :links="notifications.links" :meta="notifications" />
        </div>

    </DashboardLayout>
</template>
