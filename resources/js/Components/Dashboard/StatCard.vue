<script setup>
import Icon from '@/Components/Base/Icon.vue';
defineProps({
    title: { type: String, default: '' },
    value: { type: [String, Number], default: 0 },
    subtitle: { type: String, default: '' },
    icon: { type: String, default: 'dollar-sign' },
    gradient: { type: String, default: 'from-brand to-brand-strong' },
    trend: { type: String, default: '' },
});
</script>

<template>
    <div :class="['relative overflow-hidden rounded-2xl p-5 text-white shadow-card bg-gradient-to-br', gradient]">
        <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full bg-white/10"></div>
        <div class="relative flex items-start justify-between">
            <div>
                <p class="text-sm font-medium text-white/80">{{ title }}</p>
                <p class="mt-2 text-2xl font-extrabold tracking-tight">{{ value }}</p>
                <p v-if="subtitle" class="mt-1 text-xs text-white/70">{{ subtitle }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                <Icon :name="icon" size="6" class="text-white" />
            </div>
        </div>
        <div v-if="trend" class="relative mt-3 inline-flex items-center gap-1 rounded-pill bg-white/20 px-2 py-0.5 text-xs font-semibold">
            <Icon name="trending-up" size="4" /> {{ trend }}
        </div>
    </div>
</template>
