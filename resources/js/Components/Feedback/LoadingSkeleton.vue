<script setup>
defineProps({
    rows: { type: Number, default: 5 },
});
</script>

<template>
    <div class="space-y-md">
        <div v-for="i in rows" :key="i" class="space-y-sm">
            <div class="h-4 bg-surface-subtle rounded-md animate-pulse w-full"></div>
            <div class="h-3 bg-surface-subtle rounded-md animate-pulse w-4/5"></div>
        </div>
    </div>
</template>
