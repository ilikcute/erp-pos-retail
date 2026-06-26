<script setup>
import { computed, watch } from 'vue';
const props = defineProps({
    show: { type: Boolean, default: false },
    isOpen: { type: Boolean, default: false },
    title: { type: String, default: '' },
    description: { type: String, default: '' },
    size: { type: String, default: 'md' },
    closeButton: { type: Boolean, default: true },
});
const emit = defineEmits(['close']);

const open = computed(() => props.show || props.isOpen);
const sizeClasses = {
    sm: 'max-w-md', md: 'max-w-2xl', lg: 'max-w-4xl', xl: 'max-w-6xl', '2xl': 'max-w-7xl',
};
const close = () => emit('close');

watch(open, (v) => {
    if (typeof document !== 'undefined') {
        document.body.style.overflow = v ? 'hidden' : '';
    }
});

const onKeydown = (e) => {
    if (e.key === 'Escape' && open.value) close();
};
</script>

<template>
    <Teleport to="body">
        <Transition name="modal">
            <div v-if="open" class="fixed inset-0 z-[100] flex items-center justify-center p-base" @keydown="onKeydown">
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-ink-primary/40 backdrop-blur-sm" @click="close"></div>
                <!-- Panel -->
                <div :class="['relative w-full bg-surface-card rounded-modal shadow-floating border border-border-soft flex flex-col max-h-[90vh] overflow-hidden', sizeClasses[size] || sizeClasses.md]">
                    <!-- Header -->
                    <div v-if="title || closeButton" class="flex items-start justify-between gap-base px-xl py-lg border-b border-border-soft">
                        <div>
                            <h3 v-if="title" class="text-section-title font-bold text-ink-primary">{{ title }}</h3>
                            <p v-if="description" class="text-sm text-ink-secondary mt-xs">{{ description }}</p>
                        </div>
                        <button v-if="closeButton" type="button" @click="close" class="w-9 h-9 rounded-pill flex items-center justify-center text-ink-muted hover:text-ink-primary hover:bg-surface-muted transition focus:outline-none focus:ring-2 focus:ring-brand">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <!-- Body -->
                    <div class="px-xl py-lg overflow-y-auto scroll-soft"><slot /></div>
                    <!-- Footer -->
                    <div v-if="$slots.footer" class="px-xl py-base border-t border-border-soft bg-surface-muted/50 flex justify-end gap-sm">
                        <slot name="footer" />
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: opacity 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-enter-active .relative { transition: transform 0.24s cubic-bezier(0.34, 1.56, 0.64, 1); }
.modal-enter-from .relative, .modal-leave-to .relative { transform: scale(0.95); }
</style>
