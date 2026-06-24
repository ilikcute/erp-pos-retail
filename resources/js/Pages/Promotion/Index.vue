<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';

const props = defineProps({
    promotions: { type: Array, default: () => [] },
});

const searchQuery = ref('');
const selectedStatus = ref('');
const promotions = ref(props.promotions);
const simulationResult = ref(null);

const filteredPromotions = computed(() => {
    return promotions.value.filter((promo) => {
        const matchesSearch = promo.promotion_name?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            promo.description?.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesStatus = !selectedStatus.value || promo.status === selectedStatus.value;
        return matchesSearch && matchesStatus;
    });
});

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const getStatusClass = (status) => {
    const classes = {
        DRAFT: 'bg-surface-subtle text-ink-muted border border-border-soft',
        ACTIVE: 'bg-semantic-success-soft text-semantic-success',
        EXPIRED: 'bg-semantic-danger-soft text-semantic-danger',
    };
    return classes[status] || 'bg-surface-subtle text-ink-muted';
};

const simulatePromotion = (promoId) => {
    // Return mock simulation calculation
    const promo = promotions.value.find(p => p.id === promoId);
    if (!promo) return;

    if (promo.id === 1) {
        simulationResult.value = {
            promoName: promo.promotion_name,
            total_discount: 15000,
            discounted_items: [
                { name: 'Kopi Susu Gula Aren', originalPrice: 25000, discountPrice: 22500, discount: 2500 },
                { name: 'Roti Bakar Cokelat', originalPrice: 15000, discountPrice: 13500, discount: 1500 },
            ]
        };
    } else if (promo.id === 2) {
        simulationResult.value = {
            promoName: promo.promotion_name,
            total_discount: 50000,
            discounted_items: [
                { name: 'Kemeja Flanel Slim Fit', originalPrice: 350000, discountPrice: 300000, discount: 50000 }
            ]
        };
    } else {
        simulationResult.value = {
            promoName: promo.promotion_name,
            total_discount: 20000,
            discounted_items: [
                { name: 'Snack Kentang Pringles', originalPrice: 20000, discountPrice: 0, discount: 20000, note: 'Free Item (Buy 1 Get 1)' }
            ]
        };
    }
};
</script>

<template>
    <Head title="Promotions & Discounts" />

    <DashboardLayout>
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-ink-primary">
                    Promotions
                </h1>
                <p class="text-ink-secondary text-sm">
                    Kelola promosi diskon, reward bundling, dan sistem kupon penjualan.
                </p>
            </div>

            <BaseButton>
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Promotion
            </BaseButton>
        </div>

        <!-- Filters -->
        <div class="mb-6 bg-surface-card border border-border-soft p-xl rounded-xl shadow-soft flex flex-col md:flex-row gap-base">
            <div class="flex-1 flex items-center gap-md bg-surface-main rounded-lg border border-border-soft px-md py-sm">
                <svg class="w-5 h-5 text-ink-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search promotions..."
                    class="w-full bg-transparent border-none text-ink-primary text-sm focus:outline-none focus:ring-0 p-0"
                />
            </div>
            
            <div class="w-full md:w-48">
                <select
                    v-model="selectedStatus"
                    class="w-full bg-surface-main border border-border-soft text-ink-primary text-sm rounded-lg p-sm focus:outline-none focus:ring-2 focus:ring-brand"
                >
                    <option value="">All Status</option>
                    <option value="DRAFT">Draft</option>
                    <option value="ACTIVE">Active</option>
                    <option value="EXPIRED">Expired</option>
                </select>
            </div>
        </div>

        <!-- Promotions Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-xl">
            <div
                v-for="promo in filteredPromotions"
                :key="promo.id"
                class="bg-surface-card border border-border-soft rounded-2xl p-xl shadow-soft hover:shadow-medium transition-all duration-200 flex flex-col justify-between"
            >
                <div>
                    <div class="flex justify-between items-start mb-md">
                        <span
                            :class="getStatusClass(promo.status)"
                            class="px-2.5 py-0.5 rounded-full text-xs font-semibold"
                        >
                            {{ promo.status }}
                        </span>
                        
                        <div class="text-xs text-ink-secondary">
                            Valid: {{ formatDate(promo.start_date) }} - {{ formatDate(promo.end_date) }}
                        </div>
                    </div>

                    <h4 class="font-bold text-lg text-ink-primary mb-sm">{{ promo.promotion_name }}</h4>
                    <p class="text-sm text-ink-secondary mb-xl leading-relaxed">{{ promo.description }}</p>

                    <div class="bg-surface-main border border-border-soft rounded-xl p-md mb-xl">
                        <span class="text-xs font-semibold text-ink-muted uppercase tracking-wider block mb-sm">Rewards</span>
                        <div v-for="reward in promo.rewards" :key="reward.id" class="text-sm font-semibold text-brand">
                            <span v-if="reward.reward_type === 'DISCOUNT'">Diskon {{ reward.reward_value }}%</span>
                            <span v-else-if="reward.reward_type === 'DIRECT_DISCOUNT'">Potongan {{ formatCurrency(reward.reward_value) }}</span>
                            <span v-else-if="reward.reward_type === 'FREE_ITEM'">Gratis {{ reward.reward_value }} {{ reward.reward_unit }}</span>
                            <span v-else>{{ reward.reward_type }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex gap-md mt-auto pt-base border-t border-border-soft">
                    <BaseButton variant="secondary" class="flex-1 justify-center text-xs py-sm">
                        Edit
                    </BaseButton>
                    <BaseButton @click="simulatePromotion(promo.id)" variant="primary" class="flex-1 justify-center text-xs py-sm">
                        Simulate
                    </BaseButton>
                </div>
            </div>
        </div>

        <!-- Simulation Results Section -->
        <div v-if="simulationResult" class="mt-8 bg-brand-soft border border-brand/20 p-xl rounded-2xl shadow-soft animate-fade-in">
            <div class="flex justify-between items-center mb-base">
                <h3 class="text-lg font-bold text-brand">Simulation - {{ simulationResult.promoName }}</h3>
                <button @click="simulationResult = null" class="text-brand hover:text-ink-primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="space-y-md">
                <div v-for="(item, idx) in simulationResult.discounted_items" :key="idx" class="flex justify-between items-center border-b border-brand/10 pb-sm">
                    <div>
                        <p class="font-medium text-ink-primary text-sm">{{ item.name }}</p>
                        <p class="text-xs text-ink-secondary">Original: {{ formatCurrency(item.originalPrice) }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-brand text-sm">{{ formatCurrency(item.discountPrice) }}</p>
                        <p class="text-xs text-semantic-success">-{{ formatCurrency(item.discount) }}</p>
                    </div>
                </div>

                <div class="flex justify-between items-center pt-md">
                    <span class="font-semibold text-ink-primary text-sm">Total Discount</span>
                    <span class="font-bold text-brand text-lg">{{ formatCurrency(simulationResult.total_discount) }}</span>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
