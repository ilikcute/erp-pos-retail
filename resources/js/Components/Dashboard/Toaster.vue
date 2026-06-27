<script setup>
import { ref, watch }  from 'vue';
import { usePage }  from '@inertiajs/vue3';
const page = usePage();
const toasts = ref([]);
let seq = 0;
const push = (type, message) => {
    if (!message) return;
    const id = ++seq;
    toasts.value.push({ id, type, message });
    setTimeout(() => { toasts.value = toasts.value.filter(t => t.id !== id); }, 3000);
};
// React to Inertia flash messages (page.props.flash.success / .error)
watch(() => page.props.flash, (flash) => {
    if (!flash) return;
    push('success', flash.success);
    push('error', flash.error);
}, { deep: true, immediate: true });
</script>

<template>
    <div class="fixed top-4 right-4 z-[200] flex flex-col gap-2 w-80 max-w-[calc(100vw-2rem)]">
        <transition-group name="toast">
            <div
                v-for="t in toasts"
                :key="t.id"
                :class="['rounded-xl px-4 py-3 text-sm font-medium shadow-floating border flex items-start gap-2', t.type === 'error' ? 'bg-semantic-danger-soft text-semantic-danger border-semantic-danger/20' : 'bg-accent-mint-soft text-accent-mint border-accent-mint/20']"
            >
                <span class="font-bold">{{ t.type === 'error' ? '✕' : '✓' }}</span>
                <span class="flex-1">{{ t.message }}</span>
            </div>
        </transition-group>
    </div>
</template>

<style scoped>
.toast-enter-active, .toast-leave-active { transition: all 0.25s ease; }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateX(20px); }
</style>
