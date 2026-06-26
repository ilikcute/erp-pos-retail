<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: {
        type: Array,
        required: true,
    },
    meta: {
        type: Object,
        default: null,
    },
});
</script>

<template>
    <div v-if="links && links.length > 3" class="mt-4 flex flex-col sm:flex-row justify-between items-center bg-surface-card px-base py-md border border-border-soft rounded-lg shadow-soft gap-base">
        <div>
            <p v-if="meta" class="text-xs text-ink-muted">
                Showing <span class="font-semibold text-ink-primary">{{ meta.from || 0 }}</span> to <span class="font-semibold text-ink-primary">{{ meta.to || 0 }}</span> of <span class="font-semibold text-ink-primary">{{ meta.total }}</span> results
            </p>
        </div>
        <div class="flex flex-wrap gap-xs">
            <Link
                v-for="(link, key) in links"
                :key="key"
                :href="link.url || '#'"
                v-html="link.label"
                :class="[
                    'px-3 py-1.5 border rounded text-xs transition-all font-medium',
                    link.active 
                        ? 'bg-brand-gradient text-white shadow-brand-glow border-brand' 
                        : 'bg-surface-card text-ink-secondary border-border-soft hover:bg-surface-subtle',
                    !link.url ? 'opacity-50 cursor-not-allowed pointer-events-none' : ''
                ]"
            />
        </div>
    </div>
</template>
