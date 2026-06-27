<script setup>
import { computed } from 'vue';
import Icon from '@/Components/Base/Icon.vue';
const props = defineProps({
    title: { type: String, default: 'Target Bulan Ini' },
    current: { type: Number, default: 0 },
    target: { type: Number, default: 0 },
    icon: { type: String, default: 'target' },
});
const fmt = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v || 0);
const percentage = computed(() => props.target > 0 ? Math.min(100, Math.round((props.current / props.target) * 100)) : 0);
const isAchieved = computed(() => props.current >= props.target && props.target > 0);
</script>

<template>
    <div class="relative overflow-hidden rounded-2xl p-5 text-white shadow-card bg-gradient-to-br from-accent-grape to-brand-strong">
        <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full bg-white/10"></div>
        <div class="relative flex items-start justify-between">
            <div>
                <p class="text-sm font-medium text-white/80">{{ title }}</p>
                <p class="mt-2 text-2xl font-extrabold">{{ percentage }}%</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                <Icon :name="icon" size="6" class="text-white" />
            </div>
        </div>
        <div class="relative mt-3 h-2 w-full rounded-full bg-white/20 overflow-hidden">
            <div :class="['h-full rounded-full transition-all duration-500', isAchieved ? 'bg-accent-mint' : 'bg-white']" :style="{ width: percentage + '%' }"></div>
        </div>
        <p class="relative mt-2 text-xs text-white/80">{{ fmt(current) }} / {{ fmt(target) }}</p>
    </div>
</template>
