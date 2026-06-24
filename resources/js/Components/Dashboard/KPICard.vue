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

const colorClasses = {
    brand: 'text-brand',
    success: 'text-semantic-success',
    warning: 'text-semantic-warning',
    danger: 'text-semantic-danger',
    info: 'text-semantic-info',
};
</script>

<template>
    <BaseCard class="space-y-lg">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <p class="text-sm font-medium text-ink-secondary">{{ label }}</p>
                <p class="text-3xl font-bold text-ink-primary mt-md">{{ value }}</p>
            </div>
            <div v-if="icon" :class="['w-12 h-12 rounded-lg flex items-center justify-center bg-surface-subtle', colorClasses[color]]">
                <Icon :name="icon" class="w-6 h-6" />
            </div>
        </div>

        <div v-if="trend" class="flex items-center gap-sm">
            <svg
                v-if="trend === 'up'"
                class="w-4 h-4 text-semantic-success"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8L5.586 19.414" />
            </svg>
            <svg
                v-else-if="trend === 'down'"
                class="w-4 h-4 text-semantic-danger"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8L5.586 4.586" />
            </svg>
            <span class="text-xs font-medium" :class="trend === 'up' ? 'text-semantic-success' : 'text-semantic-danger'">
                {{ trendValue }}
            </span>
        </div>

        <slot />
    </BaseCard>
</template>
