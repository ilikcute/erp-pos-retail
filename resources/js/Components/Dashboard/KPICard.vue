<script setup>
import BaseCard from '@/Components/Base/BaseCard.vue';
import Icon from '@/Components/Base/Icon.vue';

defineProps({
    label: { type: String, required: true },
    value: { type: [String, Number], required: true },
    trend: { type: String, default: null },
    trendValue: { type: String, default: null },
    icon: { type: String, default: null },
    color: { type: String, default: 'brand' },
});

const gradientClasses = {
    brand:   'bg-brand-gradient',
    success: 'bg-mint-gradient',
    mint:    'bg-mint-gradient',
    warning: 'bg-sunset-gradient',
    sunny:   'bg-sunset-gradient',
    danger:  'bg-grape-gradient',
    info:    'bg-gradient-to-br from-accent-sky to-accent-indigo',
    grape:   'bg-grape-gradient',
};
</script>

<template>
    <BaseCard hoverable class="space-y-base">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <p class="text-sm font-medium text-ink-secondary">{{ label }}</p>
                <p class="text-page-title-sm font-extrabold text-ink-primary mt-xs">{{ value }}</p>
            </div>
            <div v-if="icon" :class="['w-12 h-12 rounded-xl flex items-center justify-center text-white shadow-soft', gradientClasses[color] || gradientClasses.brand]">
                <Icon :name="icon" class="w-6 h-6" />
            </div>
        </div>
        <div v-if="trend" class="flex items-center gap-xs">
            <span :class="['chip text-xs px-sm py-0.5', trend === 'up' ? 'bg-accent-mint-soft text-accent-mint' : 'bg-semantic-danger-soft text-semantic-danger']">
                {{ trend === 'up' ? '▲' : '▼' }} {{ trendValue }}
            </span>
        </div>
        <slot />
    </BaseCard>
</template>
