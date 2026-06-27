<script setup>
import Icon from '@/Components/Base/Icon.vue';
defineProps({
    title: { type: String, default: '' },
    subtitle: { type: String, default: '' },
    icon: { type: String, default: 'list' },
    emptyMessage: { type: String, default: 'Belum ada data' },
    isEmpty: { type: Boolean, default: false },
});
</script>

<template>
    <div class="rounded-2xl bg-surface-card border border-border-soft shadow-soft flex flex-col">
        <div class="flex items-center gap-3 px-5 py-4 border-b border-border-soft">
            <div class="w-9 h-9 rounded-xl bg-brand-soft flex items-center justify-center shrink-0">
                <Icon :name="icon" size="5" class="text-brand" />
            </div>
            <div class="min-w-0">
                <h3 class="text-sm font-bold text-ink-primary truncate">{{ title }}</h3>
                <p v-if="subtitle" class="text-xs text-ink-muted truncate">{{ subtitle }}</p>
            </div>
        </div>
        <div class="px-5 py-3 flex-1">
            <div v-if="isEmpty" class="h-28 flex items-center justify-center text-sm text-ink-muted">{{ emptyMessage }}</div>
            <slot v-else />
        </div>
    </div>
</template>
