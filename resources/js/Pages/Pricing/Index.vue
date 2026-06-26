<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import DataTable from '@/Components/Table/DataTable.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';

const props = defineProps({
    priceLists: { type: Array, default: () => [] },
    priceChangeRequests: { type: Array, default: () => [] },
});

const activeTab = ref('lists');
const priceLists = ref(props.priceLists);
const priceChangeRequests = ref(props.priceChangeRequests);

const listColumns = [
    { key: 'no', label: 'No' },
    { key: 'price_list_name', label: 'Price List Name' },
    { key: 'price_list_type', label: 'Type' },
    { key: 'items', label: 'Items' },
    { key: 'status', label: 'Status', align: 'center' },
    { key: 'actions', label: 'Actions', align: 'right' },
];

const requestColumns = [
    { key: 'no', label: 'No' },
    { key: 'request_no', label: 'Request No' },
    { key: 'price_list', label: 'Target Price List' },
    { key: 'effective_date', label: 'Effective Date' },
    { key: 'status', label: 'Status', align: 'center' },
    { key: 'actions', label: 'Actions', align: 'right' },
];

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const getStatusClass = (status) => {
    const classes = {
        DRAFT: 'bg-surface-subtle text-ink-muted border border-border-soft',
        PENDING: 'bg-semantic-warning-soft text-semantic-warning',
        APPROVED: 'bg-brand-soft text-brand',
        POSTED: 'bg-semantic-success-soft text-semantic-success',
        REJECTED: 'bg-semantic-danger-soft text-semantic-danger',
    };
    return classes[status] || 'bg-surface-subtle text-ink-muted';
};
</script>

<template>
    <Head title="Pricing & Price Lists" />

    <DashboardLayout>
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-ink-primary">
                    Pricing
                </h1>
                <p class="text-ink-secondary text-sm">
                    Kelola daftar harga produk (Price Lists) dan pengajuan perubahan harga (Price Change Requests).
                </p>
            </div>

            <div class="flex gap-md">
                <BaseButton v-if="activeTab === 'lists'">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Price List
                </BaseButton>
                <BaseButton v-if="activeTab === 'requests'">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Request
                </BaseButton>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex space-x-1 mb-6 border-b border-border-soft">
            <button
                @click="activeTab = 'lists'"
                :class="activeTab === 'lists' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-3 px-6 font-medium transition-colors duration-200 cursor-pointer text-sm"
            >
                Price Lists
            </button>
            <button
                @click="activeTab = 'requests'"
                :class="activeTab === 'requests' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-3 px-6 font-medium transition-colors duration-200 cursor-pointer text-sm"
            >
                Price Change Requests
            </button>
            <button
                @click="activeTab = 'history'"
                :class="activeTab === 'history' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-3 px-6 font-medium transition-colors duration-200 cursor-pointer text-sm"
            >
                History
            </button>
        </div>

        <!-- Price Lists Tab -->
        <div v-if="activeTab === 'lists'">
            <DataTable :columns="listColumns" :rows="priceLists">
                <template #cell-price_list_name="{ value }">
                    <span class="font-semibold text-ink-primary">{{ value }}</span>
                </template>
                <template #cell-price_list_type="{ value }">
                    <span class="px-2 py-0.5 bg-brand-soft text-brand rounded text-xs font-semibold">
                        {{ value }}
                    </span>
                </template>
                <template #cell-items="{ row }">
                    <span class="text-ink-secondary text-sm font-medium">{{ row.items?.length || 0 }} items</span>
                </template>
                <template #cell-status="{ row }">
                    <span
                        :class="row.is_active ? 'bg-semantic-success-soft text-semantic-success' : 'bg-surface-subtle text-ink-muted'"
                        class="px-2 py-0.5 rounded text-xs font-semibold"
                    >
                        {{ row.is_active ? 'Active' : 'Inactive' }}
                    </span>
                </template>
                <template #cell-actions>
                    <button class="text-brand hover:underline font-medium text-sm">Edit</button>
                </template>
            </DataTable>
        </div>

        <!-- Price Change Requests Tab -->
        <div v-if="activeTab === 'requests'">
            <DataTable :columns="requestColumns" :rows="priceChangeRequests">
                <template #cell-request_no="{ value }">
                    <span class="font-mono font-semibold text-ink-primary">{{ value }}</span>
                </template>
                <template #cell-price_list="{ row }">
                    <span class="text-ink-secondary text-sm font-medium">{{ row.price_list?.price_list_name }}</span>
                </template>
                <template #cell-effective_date="{ value }">
                    <span class="text-ink-secondary text-sm">{{ formatDate(value) }}</span>
                </template>
                <template #cell-status="{ value }">
                    <span
                        :class="getStatusClass(value)"
                        class="px-2.5 py-0.5 rounded-full text-xs font-semibold border border-transparent"
                    >
                        {{ value }}
                    </span>
                </template>
                <template #cell-actions="{ row }">
                    <div class="flex gap-sm justify-end">
                        <button v-if="row.status === 'PENDING'" class="text-semantic-success hover:underline font-medium text-sm">
                            Approve
                        </button>
                        <button v-if="row.status === 'PENDING'" class="text-semantic-danger hover:underline font-medium text-sm">
                            Reject
                        </button>
                        <button v-else class="text-brand hover:underline font-medium text-sm">
                            View
                        </button>
                    </div>
                </template>
            </DataTable>
        </div>

        <!-- History Tab -->
        <div v-if="activeTab === 'history'" class="bg-surface-card border border-border-soft rounded-xl p-xl shadow-soft text-center text-ink-secondary">
            <svg class="w-12 h-12 text-ink-muted mx-auto mb-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="font-semibold text-ink-primary mb-xs">History logs are currently empty</p>
            <p class="text-xs">No price change logs or approvals registered yet.</p>
        </div>
    </DashboardLayout>
</template>
