<script setup>
defineProps({
    padding: { type: String, default: 'xl' },
    shadow: { type: String, default: 'card' },
    hoverable: { type: Boolean, default: false },
    clickable: { type: Boolean, default: false },
});
</script>

<template>
    <div
        :class="[
            'bg-surface-card rounded-card border border-border-soft transition-all duration-150',
            `p-${padding}`,
            `shadow-${shadow}`,
            hoverable && 'hover:shadow-floating hover:-translate-y-0.5',
            clickable && 'cursor-pointer active:scale-[0.99] hover:border-brand-border',
        ]"
    >
        <slot />
    </div>
</template>
