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
            'bg-surface-card rounded-card border border-border-soft transition-all duration-200',
            `p-${padding}`,
            `shadow-${shadow}`,
            hoverable && 'hover:shadow-floating hover:border-border-strong',
            clickable && 'cursor-pointer hover:bg-surface-subtle',
        ]"
    >
        <slot />
    </div>
</template>
