<script setup>
import Icon from '@/Components/Base/Icon.vue';

defineProps({
    icon: { type: String, default: 'inbox' },
    title: { type: String, required: true },
    description: { type: String, default: '' },
});
</script>

<template>
    <div class="flex flex-col items-center justify-center py-3xl px-base text-center">
        <div class="w-16 h-16 rounded-2xl bg-surface-subtle flex items-center justify-center mb-lg">
            <Icon :name="icon" class="w-8 h-8 text-ink-muted" />
        </div>
        <h3 class="text-card-title font-semibold text-ink-primary mb-sm">{{ title }}</h3>
        <p v-if="description" class="text-sm text-ink-secondary mb-lg max-w-sm">{{ description }}</p>
        <slot />
    </div>
</template>
