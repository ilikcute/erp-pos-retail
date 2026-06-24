<script setup>
const props = defineProps({
    isOpen: { type: Boolean, required: true },
    title: { type: String, default: null },
    height: { type: String, default: 'auto' },
});

const emit = defineEmits(['close']);
</script>

<template>
    <Teleport to="body">
        <Transition name="bottom-sheet">
            <div v-if="isOpen" class="fixed inset-0 z-50 md:hidden flex flex-col">
                <!-- Backdrop -->
                <div
                    class="absolute inset-0 bg-black/50 transition-opacity"
                    @click="$emit('close')"
                ></div>

                <!-- Bottom Sheet -->
                <div class="relative ml-auto flex flex-col w-full bg-surface-card rounded-t-2xl shadow-floating mt-auto max-h-[90vh]">
                    <!-- Handle -->
                    <div class="flex justify-center pt-md pb-md">
                        <div class="w-12 h-1 bg-border-soft rounded-full"></div>
                    </div>

                    <!-- Header -->
                    <div v-if="title" class="px-base pb-md border-b border-border-soft">
                        <h2 class="text-card-title font-semibold text-ink-primary">{{ title }}</h2>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 overflow-y-auto px-base py-md">
                        <slot />
                    </div>

                    <!-- Footer (optional) -->
                    <div v-if="$slots.footer" class="p-base border-t border-border-soft sticky bottom-0 bg-surface-card">
                        <slot name="footer" />
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.bottom-sheet-enter-active,
.bottom-sheet-leave-active {
    transition: opacity 0.24s ease;
}

.bottom-sheet-enter-from,
.bottom-sheet-leave-to {
    opacity: 0;
}

.bottom-sheet-enter-active > div:nth-child(2),
.bottom-sheet-leave-active > div:nth-child(2) {
    transition: transform 0.24s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.bottom-sheet-enter-from > div:nth-child(2),
.bottom-sheet-leave-to > div:nth-child(2) {
    transform: translateY(100%);
}
</style>
