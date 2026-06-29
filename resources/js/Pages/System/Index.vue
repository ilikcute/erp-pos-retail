<script setup>
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import DataTable from '@/Components/Table/DataTable.vue';
import Pagination from "@/Components/Navigation/Pagination.vue";
import BaseButton from '@/Components/Base/BaseButton.vue';
import FormInput from '@/Components/Form/FormInput.vue';
import FormSelect from '@/Components/Form/FormSelect.vue';
import FormTextarea from '@/Components/Form/FormTextarea.vue';

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

// Computed filters
const filteredUsers = computed(() => {
    const q = searchQuery.value.toLowerCase();
    if (!q) return props.users;
    return props.users.filter(u => u.name?.toLowerCase().includes(q) || u.email?.toLowerCase().includes(q));
});

const filteredDocTypes = computed(() => {
    const q = searchQuery.value.toLowerCase();
    if (!q) return props.documentTypes;
    return props.documentTypes.filter(d => d.name?.toLowerCase().includes(q) || d.code?.toLowerCase().includes(q));
});

// Settings form
const settingsForm = useForm({
    settings: props.settings.map(s => ({
        id: s.id,
        key: s.key,
        value: s.value,
        group: s.group,
        description: s.description
    }))
});

function submitSettings() {
    settingsForm.post('/system/settings', {
        preserveScroll: true,
    });
}

// Business Profile form
const profileForm = useForm({
    business_name: props.businessProfile?.business_name || '',
    legal_name: props.businessProfile?.legal_name || '',
    tax_id: props.businessProfile?.tax_id || '',
    phone: props.businessProfile?.phone || '',
    email: props.businessProfile?.email || '',
    website: props.businessProfile?.website || '',
    address: props.businessProfile?.address || '',
    city: props.businessProfile?.city || '',
    province: props.businessProfile?.province || '',
    postal_code: props.businessProfile?.postal_code || '',
    country: props.businessProfile?.country || 'Indonesia',
    currency: props.businessProfile?.currency || 'IDR',
    timezone: props.businessProfile?.timezone || 'Asia/Jakarta',
});

function submitProfile() {
    profileForm.post('/system/business-profile', {
        preserveScroll: true,
    });
}

// Columns configs
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

        <!-- Search Bar -->
        <div v-if="activeTab === 'users' || activeTab === 'roles' || activeTab === 'document-types'" class="mb-6 flex items-center gap-md max-w-md bg-surface-card rounded-lg border border-border-soft p-sm shadow-soft">
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

        <!-- SETTINGS TAB (Editable Form) -->
        <div v-if="activeTab === 'settings'" class="space-y-xl">
            <form @submit.prevent="submitSettings" class="space-y-lg bg-surface-card rounded-lg border border-border-soft p-xl shadow-soft">
                <h2 class="text-lg font-bold text-ink-primary border-b pb-sm">Pengaturan Sistem ⚙️</h2>
                
                <div class="space-y-md">
                    <div v-for="(setting, idx) in settingsForm.settings" :key="setting.id" class="grid grid-cols-1 md:grid-cols-3 gap-md items-center py-sm border-b border-border-soft last:border-b-0">
                        <div>
                            <span class="font-mono text-xs font-bold text-brand uppercase block">{{ setting.key }}</span>
                            <span class="text-xs text-ink-secondary">{{ setting.description || 'Tidak ada deskripsi.' }}</span>
                        </div>
                        <div class="md:col-span-2">
                            <FormInput v-model="setting.value" required placeholder="Nilai setting..." />
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-md border-t border-border-soft">
                    <BaseButton type="submit" :loading="settingsForm.processing">💾 Simpan Konfigurasi</BaseButton>
                </div>
            </form>
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

        <!-- BUSINESS PROFILE TAB (Editable Form) -->
        <div v-if="activeTab === 'business-profile'" class="bg-surface-card rounded-lg border border-border-soft p-xl max-w-4xl shadow-soft">
            <form @submit.prevent="submitProfile" class="space-y-xl">
                <h2 class="text-lg font-bold text-ink-primary border-b pb-sm">Profil Perusahaan 🏢</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-xl">
                    <FormInput label="Nama Bisnis" v-model="profileForm.business_name" required :error="profileForm.errors.business_name" />
                    <FormInput label="Nama Legal" v-model="profileForm.legal_name" :error="profileForm.errors.legal_name" />
                    <FormInput label="NPWP (Tax ID)" v-model="profileForm.tax_id" :error="profileForm.errors.tax_id" />
                    <FormInput label="Mata Uang Utama" v-model="profileForm.currency" required :error="profileForm.errors.currency" />
                    <FormInput label="Telepon" v-model="profileForm.phone" :error="profileForm.errors.phone" />
                    <FormInput label="Email" type="email" v-model="profileForm.email" :error="profileForm.errors.email" />
                    <div class="md:col-span-2">
                        <FormInput label="Alamat Lengkap" v-model="profileForm.address" :error="profileForm.errors.address" />
                    </div>
                    <FormInput label="Kota" v-model="profileForm.city" :error="profileForm.errors.city" />
                    <FormInput label="Provinsi" v-model="profileForm.province" :error="profileForm.errors.province" />
                    <FormInput label="Kode Pos" v-model="profileForm.postal_code" :error="profileForm.errors.postal_code" />
                    <FormInput label="Negara" v-model="profileForm.country" :error="profileForm.errors.country" />
                    <FormInput label="Website" v-model="profileForm.website" :error="profileForm.errors.website" />
                    <FormInput label="Zona Waktu (Timezone)" v-model="profileForm.timezone" required :error="profileForm.errors.timezone" />
                </div>

                <div class="flex justify-end pt-md border-t border-border-soft">
                    <BaseButton type="submit" :loading="profileForm.processing">💾 Simpan Profil Bisnis</BaseButton>
                </div>
            </form>
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
