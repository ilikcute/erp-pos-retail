<script setup>
// Persistent layout shell for the POS screen.
// Kept intentionally minimal so the POS page controls its own full-screen chrome.
</script>

<template>
    <Head title="Kasir POS" />
    <div class="pos-layout min-h-screen bg-surface-app text-ink-primary">
        <slot />
    </div>
</template>

<style scoped>
.pos-layout {
    height: 100vh;
    overflow: hidden;
}
</style>
