<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import DataTable from '@/Components/Table/DataTable.vue';
import Pagination from "@/Components/Navigation/Pagination.vue";
import BaseButton from '@/Components/Base/BaseButton.vue';

const props = defineProps({
    users: { type: Array, default: () => [] },
    roles: { type: Array, default: () => [] },
    settings: { type: Array, default: () => [] },
    documentTypes: { type: Array, default: () => [] },
    businessProfile: { type: Object, default: () => ({}) },
    auditLogs: { type: Object, default: () => ({ data: [] }) },
});

const activeTab = ref('users');
const searchQuery = ref('');

const filteredUsers = computed(() => {
    const q = searchQuery.value.toLowerCase();
    if (!q) return props.users;
    return props.users.filter(u => u.name?.toLowerCase().includes(q) || u.email?.toLowerCase().includes(q));
});

const filteredSettings = computed(() => {
    const q = searchQuery.value.toLowerCase();
    if (!q) return props.settings;
    return props.settings.filter(s => s.key?.toLowerCase().includes(q) || s.description?.toLowerCase().includes(q));
});

const filteredDocTypes = computed(() => {
    const q = searchQuery.value.toLowerCase();
    if (!q) return props.documentTypes;
    return props.documentTypes.filter(d => d.name?.toLowerCase().includes(q) || d.code?.toLowerCase().includes(q));
});

// Columns configurations
const userColumns = [
    { key: 'no', label: 'No' },
    { key: 'name', label: 'Name' },
    { key: 'email', label: 'Email' },
    { key: 'status', label: 'Status', align: 'center' },
];

const roleColumns = [
    { key: 'no', label: 'No' },
    { key: 'name', label: 'Role Name' },
    { key: 'display_name', label: 'Display Name' },
    { key: 'status', label: 'Status', align: 'center' },
];

const settingColumns = [
    { key: 'no', label: 'No' },
    { key: 'key', label: 'Setting Key' },
    { key: 'value', label: 'Value' },
    { key: 'group', label: 'Group', align: 'center' },
    { key: 'description', label: 'Description' },
];

const docTypeColumns = [
    { key: 'no', label: 'No' },
    { key: 'code', label: 'Code' },
    { key: 'name', label: 'Document Name' },
    { key: 'prefix', label: 'Prefix', align: 'center' },
    { key: 'date_format', label: 'Date Format', align: 'center' },
    { key: 'padding', label: 'Padding', align: 'center' },
    { key: 'status', label: 'Status', align: 'center' },
];

const auditColumns = [
    { key: 'no', label: 'No' },
    { key: 'created_at', label: 'Timestamp' },
    { key: 'user', label: 'User' },
    { key: 'module', label: 'Module', align: 'center' },
    { key: 'action', label: 'Action', align: 'center' },
    { key: 'table_name', label: 'Table' },
    { key: 'record_id', label: 'Record ID', align: 'center' },
];

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleString('id-ID');
};
</script>

<template>
    <Head title="System Management" />

    <DashboardLayout>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-ink-primary">System Settings 🖥️</h1>
            <p class="text-ink-secondary text-sm">Kelola pengguna, peranan (roles), konfigurasi sistem, format dokumen, profil perusahaan, dan audit log.</p>
        </div>

        <!-- Tabs -->
        <div class="flex space-x-1 mb-6 border-b border-border-soft overflow-x-auto whitespace-nowrap scrollbar-none">
            <button
                @click="activeTab = 'users'; searchQuery = ''"
                :class="activeTab === 'users' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-3 px-6 font-medium transition-colors duration-200 cursor-pointer text-sm"
            >
                Users 👥
            </button>
            <button
                @click="activeTab = 'roles'; searchQuery = ''"
                :class="activeTab === 'roles' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-3 px-6 font-medium transition-colors duration-200 cursor-pointer text-sm"
            >
                Roles & Permissions 🔒
            </button>
            <button
                @click="activeTab = 'settings'; searchQuery = ''"
                :class="activeTab === 'settings' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-3 px-6 font-medium transition-colors duration-200 cursor-pointer text-sm"
            >
                System Settings ⚙️
            </button>
            <button
                @click="activeTab = 'document-types'; searchQuery = ''"
                :class="activeTab === 'document-types' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-3 px-6 font-medium transition-colors duration-200 cursor-pointer text-sm"
            >
                Document Types 📄
            </button>
            <button
                @click="activeTab = 'business-profile'; searchQuery = ''"
                :class="activeTab === 'business-profile' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-3 px-6 font-medium transition-colors duration-200 cursor-pointer text-sm"
            >
                Business Profile 🏢
            </button>
            <button
                @click="activeTab = 'audit'; searchQuery = ''"
                :class="activeTab === 'audit' ? 'border-b-2 border-brand text-brand font-semibold' : 'text-ink-secondary hover:text-ink-primary'"
                class="py-3 px-6 font-medium transition-colors duration-200 cursor-pointer text-sm"
            >
                Audit Logs 📋
            </button>
        </div>

        <!-- Search Bar (hidden for Business Profile) -->
        <div v-if="activeTab !== 'business-profile'" class="mb-6 flex items-center gap-md max-w-md bg-surface-card rounded-lg border border-border-soft p-sm shadow-soft">
            <svg class="w-5 h-5 text-ink-muted ml-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input
                v-model="searchQuery"
                type="text"
                placeholder="Cari data..."
                class="w-full bg-transparent border-none text-ink-primary text-sm focus:outline-none focus:ring-0 p-0"
            />
        </div>

        <!-- USERS TAB -->
        <div v-if="activeTab === 'users'">
            <DataTable :columns="userColumns" :rows="filteredUsers">
                <template #cell-name="{ value }">
                    <span class="font-semibold text-ink-primary">{{ value }}</span>
                </template>
                <template #cell-status="{ row }">
                    <span
                        :class="row.status === 'ACTIVE' ? 'bg-semantic-success-soft text-semantic-success' : 'bg-surface-subtle text-ink-muted'"
                        class="px-2.5 py-0.5 rounded-full text-xs font-semibold"
                    >
                        {{ row.status }}
                    </span>
                </template>
            </DataTable>
        </div>

        <!-- ROLES TAB -->
        <div v-if="activeTab === 'roles'">
            <DataTable :columns="roleColumns" :rows="roles">
                <template #cell-name="{ value }">
                    <span class="font-semibold text-ink-primary font-mono">{{ value }}</span>
                </template>
                <template #cell-status="{ row }">
                    <span
                        :class="row.is_active ? 'bg-semantic-success-soft text-semantic-success' : 'bg-surface-subtle text-ink-muted'"
                        class="px-2.5 py-0.5 rounded-full text-xs font-semibold"
                    >
                        {{ row.is_active ? 'Active' : 'Inactive' }}
                    </span>
                </template>
            </DataTable>
        </div>

        <!-- SETTINGS TAB -->
        <div v-if="activeTab === 'settings'">
            <DataTable :columns="settingColumns" :rows="filteredSettings">
                <template #cell-key="{ value }">
                    <span class="font-mono text-xs font-bold text-brand">{{ value }}</span>
                </template>
                <template #cell-group="{ value }">
                    <span class="px-2 py-0.5 bg-surface-subtle text-ink-secondary rounded text-xs uppercase font-semibold">
                        {{ value }}
                    </span>
                </template>
            </DataTable>
        </div>

        <!-- DOCUMENT TYPES TAB -->
        <div v-if="activeTab === 'document-types'">
            <DataTable :columns="docTypeColumns" :rows="filteredDocTypes">
                <template #cell-code="{ value }">
                    <span class="font-mono text-xs font-bold text-ink-primary">{{ value }}</span>
                </template>
                <template #cell-status="{ row }">
                    <span
                        :class="row.is_active ? 'bg-semantic-success-soft text-semantic-success' : 'bg-surface-subtle text-ink-muted'"
                        class="px-2.5 py-0.5 rounded-full text-xs font-semibold"
                    >
                        {{ row.is_active ? 'Active' : 'Inactive' }}
                    </span>
                </template>
            </DataTable>
        </div>

        <!-- BUSINESS PROFILE TAB -->
        <div v-if="activeTab === 'business-profile'" class="bg-surface-card rounded-lg border border-border-soft p-xl max-w-4xl shadow-soft">
            <h2 class="text-lg font-bold text-ink-primary mb-6 border-b pb-sm flex items-center gap-md">
                Profil Perusahaan 🏢
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-xl">
                <div>
                    <label class="block text-xs font-semibold text-ink-muted uppercase mb-sm">Nama Bisnis</label>
                    <div class="text-ink-primary font-semibold py-sm border-b border-border-soft/50">{{ businessProfile?.business_name || '-' }}</div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-ink-muted uppercase mb-sm">Nama Legal</label>
                    <div class="text-ink-primary py-sm border-b border-border-soft/50">{{ businessProfile?.legal_name || '-' }}</div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-ink-muted uppercase mb-sm">NPWP (Tax ID)</label>
                    <div class="text-ink-primary font-mono py-sm border-b border-border-soft/50">{{ businessProfile?.tax_id || '-' }}</div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-ink-muted uppercase mb-sm">Mata Uang Utama</label>
                    <div class="text-ink-primary py-sm border-b border-border-soft/50">{{ businessProfile?.currency || 'IDR' }}</div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-ink-muted uppercase mb-sm">Telepon</label>
                    <div class="text-ink-primary py-sm border-b border-border-soft/50">{{ businessProfile?.phone || '-' }}</div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-ink-muted uppercase mb-sm">Email</label>
                    <div class="text-ink-primary py-sm border-b border-border-soft/50">{{ businessProfile?.email || '-' }}</div>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-ink-muted uppercase mb-sm">Alamat Lengkap</label>
                    <div class="text-ink-primary py-sm border-b border-border-soft/50">{{ businessProfile?.address || '-' }}, {{ businessProfile?.city || '-' }}, {{ businessProfile?.province || '-' }} {{ businessProfile?.postal_code || '' }}</div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-ink-muted uppercase mb-sm">Website</label>
                    <div class="text-brand py-sm border-b border-border-soft/50"><a :href="businessProfile?.website" target="_blank" class="hover:underline">{{ businessProfile?.website || '-' }}</a></div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-ink-muted uppercase mb-sm">Zona Waktu (Timezone)</label>
                    <div class="text-ink-primary py-sm border-b border-border-soft/50">{{ businessProfile?.timezone || 'Asia/Jakarta' }}</div>
                </div>
            </div>
        </div>

        <!-- AUDIT LOGS TAB -->
        <div v-if="activeTab === 'audit'">
            <DataTable :columns="auditColumns" :rows="auditLogs.data" :paginated="false">
                <template #cell-created_at="{ value }">
                    <span class="font-mono text-xs text-ink-muted">{{ formatDate(value) }}</span>
                </template>
                <template #cell-user="{ value }">
                    <span class="font-semibold text-ink-primary">{{ value?.name || 'System' }}</span>
                </template>
                <template #cell-module="{ value }">
                    <span class="px-2 py-0.5 bg-surface-subtle text-ink-secondary rounded text-xs uppercase font-semibold">
                        {{ value }}
                    </span>
                </template>
                <template #cell-action="{ value }">
                    <span class="px-2 py-0.5 bg-brand-soft text-brand rounded text-xs font-mono font-bold">
                        {{ value }}
                    </span>
                </template>
                <template #cell-table_name="{ value }">
                    <span class="font-mono text-xs text-ink-secondary">{{ value }}</span>
                </template>
                <template #cell-record_id="{ value }">
                    <span class="font-mono text-xs text-ink-muted">{{ value }}</span>
                </template>
            </DataTable>
            <div class="mt-4">
                <Pagination :links="auditLogs.links" :meta="auditLogs" />
            </div>
        </div>
    </DashboardLayout>
</template>
