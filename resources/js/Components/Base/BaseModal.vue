<script setup>
import { computed } from 'vue';

const props = defineProps({
    isOpen: { type: Boolean, required: true },
    title: { type: String, default: null },
    description: { type: String, default: null },
    size: { type: String, default: 'md' },
    closeButton: { type: Boolean, default: true },
});

const emit = defineEmits(['close']);

const sizeClasses = {
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-lg',
    xl: 'max-w-xl',
    '2xl': 'max-w-2xl',
};

const modalSize = computed(() => sizeClasses[props.size]);
</script>

<template>
    <Teleport to="body">
        <Transition name="modal">
            <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center">
                <!-- Backdrop -->
                <div
                    class="absolute inset-0 bg-black/50 transition-opacity"
                    @click="$emit('close')"
                ></div>

                <!-- Modal -->
                <div
                    :class="['relative bg-surface-card rounded-modal shadow-floating w-full mx-base', modalSize]"
                    role="dialog"
                    aria-modal="true"
                >
                    <!-- Header -->
                    <div v-if="title || closeButton" class="flex items-start justify-between p-xl border-b border-border-soft">
                        <div v-if="title" class="flex-1">
                            <h2 class="text-card-title font-semibold text-ink-primary">{{ title }}</h2>
                            <p v-if="description" class="text-sm text-ink-secondary mt-sm">{{ description }}</p>
                        </div>
                        <button
                            v-if="closeButton"
                            @click="$emit('close')"
                            class="ml-xl p-sm text-ink-secondary hover:text-ink-primary hover:bg-surface-subtle rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-brand"
                            aria-label="Close modal"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Content -->
                    <div class="p-xl max-h-[calc(100vh-200px)] overflow-y-auto">
                        <slot />
                    </div>

                    <!-- Footer (optional) -->
                    <div v-if="$slots.footer" class="p-xl border-t border-border-soft flex gap-md justify-end">
                        <slot name="footer" />
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.24s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-active > div:nth-child(2),
.modal-leave-active > div:nth-child(2) {
    transition: transform 0.24s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.modal-enter-from > div:nth-child(2),
.modal-leave-to > div:nth-child(2) {
    transform: scale(0.95);
}
</style>
