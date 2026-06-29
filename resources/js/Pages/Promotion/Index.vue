<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import { formatCurrency, formatDate } from '@/Utils/formatters';
import toast from '@/Utils/toast';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import StatusBadge from '@/Components/DataDisplay/StatusBadge.vue';
import PromotionFormModal from './Components/PromotionFormModal.vue';
import SimulateModal from './Components/SimulateModal.vue';

const props = defineProps({
    promotions: { type: Array, default: () => [] },
});

// ─── STATE ────────────────────────────────────────────────────────────────────
const searchQuery = ref('');
const selectedStatus = ref('');
const showFormModal = ref(false);
const showSimulateModal = ref(false);
const selectedPromo = ref(null); // null = create mode
const isActioning = ref(false); // loading state for activate/deactivate

// ─── COMPUTED ────────────────────────────────────────────────────────────────
const filteredPromotions = computed(() => {
    return props.promotions.filter((promo) => {
        const searchStr = searchQuery.value.toLowerCase();
        const matchesSearch = 
            promo.promotion_name?.toLowerCase().includes(searchStr) ||
            promo.promotion_code?.toLowerCase().includes(searchStr) ||
            promo.description?.toLowerCase().includes(searchStr);
            
        const matchesStatus = !selectedStatus.value || promo.status === selectedStatus.value;
        return matchesSearch && matchesStatus;
    });
});

// ─── ACTIONS ─────────────────────────────────────────────────────────────────
const openCreateForm = () => {
    selectedPromo.value = null;
    showFormModal.value = true;
};

const openEditForm = (promo) => {
    selectedPromo.value = promo;
    showFormModal.value = true;
};

const openSimulate = () => {
    showSimulateModal.value = true;
};

const reloadData = () => {
    router.reload({ only: ['promotions'] });
};

const toggleStatus = async (promo) => {
    if (!confirm(`Yakin ingin ${promo.status === 'ACTIVE' ? 'menonaktifkan' : 'mengaktifkan'} promosi ini?`)) {
        return;
    }

    isActioning.value = true;
    try {
        const action = promo.status === 'ACTIVE' ? 'deactivate' : 'activate';
        const res = await axios.post(route(`promotions.${action}`, promo.id));
        toast.success(res.data.message);
        reloadData();
    } catch (err) {
        toast.error(err.response?.data?.message || 'Gagal mengubah status promosi');
    } finally {
        isActioning.value = false;
    }
};

</script>

<template>
    <Head title="Promotions & Discounts" />

    <DashboardLayout>
        <div class="mb-6 flex flex-col md:flex-row justify-between md:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-ink-primary">Promotions</h1>
                <p class="text-ink-secondary text-sm">Kelola promosi diskon, reward bundling, dan sistem kupon.</p>
            </div>

            <div class="flex gap-3">
                <BaseButton @click="openSimulate" variant="secondary" class="bg-white">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    Simulasi
                </BaseButton>
                <BaseButton @click="openCreateForm">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Promotion
                </BaseButton>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-6 bg-surface-card border border-border-soft p-2 md:p-3 rounded-2xl shadow-sm flex flex-col md:flex-row gap-3">
            <div class="flex-1 flex items-center gap-3 bg-surface-main rounded-xl border border-border-soft px-4 py-2">
                <svg class="w-5 h-5 text-ink-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input v-model="searchQuery" type="text" placeholder="Cari nama, kode, deskripsi..." class="w-full bg-transparent border-none text-ink-primary text-sm focus:outline-none focus:ring-0 p-0" />
            </div>
            
            <div class="w-full md:w-56">
                <select v-model="selectedStatus" class="w-full bg-surface-main border border-border-soft text-ink-primary text-sm rounded-xl px-4 py-2.5 focus:outline-none focus:border-brand">
                    <option value="">Semua Status</option>
                    <option value="DRAFT">Draft</option>
                    <option value="ACTIVE">Active</option>
                    <option value="INACTIVE">Inactive</option>
                    <option value="EXPIRED">Expired</option>
                </select>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="filteredPromotions.length === 0" class="bg-surface-card border border-border-soft rounded-2xl p-12 text-center shadow-sm">
            <div class="w-16 h-16 bg-brand-soft text-brand rounded-2xl flex items-center justify-center mx-auto mb-4 text-3xl">🎁</div>
            <h3 class="text-lg font-bold text-ink-primary mb-1">Belum ada promosi</h3>
            <p class="text-ink-secondary text-sm mb-6">Buat promosi pertamamu untuk menarik lebih banyak pelanggan.</p>
            <BaseButton @click="openCreateForm">Buat Promosi</BaseButton>
        </div>

        <!-- Promotions Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <div v-for="promo in filteredPromotions" :key="promo.id" class="bg-surface-card border border-border-soft rounded-2xl p-5 shadow-sm hover:shadow-md transition flex flex-col h-full group">
                <div class="flex-1">
                    <div class="flex justify-between items-start mb-4">
                        <StatusBadge :status="promo.status" variant="soft" size="sm" />
                        <div class="text-[11px] font-semibold text-ink-muted bg-surface-main px-2 py-1 rounded-md border border-border-soft">
                            {{ promo.promotion_code }}
                        </div>
                    </div>

                    <h4 class="font-bold text-lg text-ink-primary mb-1 line-clamp-1" :title="promo.promotion_name">{{ promo.promotion_name }}</h4>
                    <p class="text-sm text-ink-secondary mb-4 line-clamp-2 min-h-[40px]">{{ promo.description || 'Tidak ada deskripsi' }}</p>

                    <div class="space-y-2 mb-5">
                        <div class="flex justify-between text-xs border-b border-border-soft pb-2">
                            <span class="text-ink-muted">Berlaku Dari</span>
                            <span class="font-medium text-ink-primary">{{ formatDate(promo.valid_from) }}</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-ink-muted">Berlaku Hingga</span>
                            <span class="font-medium text-ink-primary">{{ formatDate(promo.valid_until) }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex gap-2 mt-auto pt-4 border-t border-border-soft">
                    <button @click="openEditForm(promo)" class="flex-1 h-9 rounded-lg text-xs font-semibold text-ink-secondary bg-surface-main border border-border-soft hover:bg-border-soft transition">
                        Edit
                    </button>
                    <button v-if="promo.status !== 'EXPIRED'" 
                            @click="toggleStatus(promo)" 
                            :disabled="isActioning"
                            class="flex-1 h-9 rounded-lg text-xs font-semibold transition text-white shadow-sm disabled:opacity-50"
                            :class="promo.status === 'ACTIVE' ? 'bg-semantic-danger hover:bg-semantic-danger/90' : 'bg-semantic-success hover:bg-semantic-success/90'">
                        {{ promo.status === 'ACTIVE' ? 'Nonaktifkan' : 'Aktifkan' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modals -->
        <PromotionFormModal 
            :show="showFormModal" 
            :promotion="selectedPromo"
            @close="showFormModal = false"
            @saved="reloadData"
        />

        <SimulateModal 
            :show="showSimulateModal"
            @close="showSimulateModal = false"
        />
        
    </DashboardLayout>
</template>
