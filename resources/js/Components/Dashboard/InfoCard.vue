<script setup>
import Icon from '@/Components/Base/Icon.vue';
defineProps({
    title: { type: String, default: '' },
    value: { type: [String, Number], default: 0 },
    subtitle: { type: String, default: '' },
    icon: { type: String, default: 'box' },
});
</script>

<template>
    <div class="rounded-2xl bg-surface-card border border-border-soft p-4 shadow-soft">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-brand-soft flex items-center justify-center shrink-0">
                <Icon :name="icon" size="5" class="text-brand" />
            </div>
            <div class="min-w-0">
                <p class="text-xl font-bold text-ink-primary leading-tight">{{ value }}</p>
                <p class="text-xs text-ink-secondary truncate">{{ title }}</p>
                <p v-if="subtitle" class="text-xs text-ink-muted truncate">{{ subtitle }}</p>
            </div>
        </div>
    </div>
</template>
