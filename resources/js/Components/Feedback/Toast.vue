<script setup>
import Icon from '@/Components/Base/Icon.vue';
import { useToast } from '@/Composables/useToast';

const { toasts, removeToast } = useToast();

const typeClasses = {
    success: 'bg-semantic-success-soft border-semantic-success text-semantic-success',
    danger: 'bg-semantic-danger-soft border-semantic-danger text-semantic-danger',
    warning: 'bg-semantic-warning-soft border-semantic-warning text-semantic-warning',
    info: 'bg-semantic-info-soft border-semantic-info text-semantic-info',
};

const typeIcons = {
    success: 'check-circle',
    danger: 'alert-circle',
    warning: 'alert-triangle',
    info: 'info',
};
</script>

<template>
    <Teleport to="body">
        <div class="fixed bottom-base right-base z-50 space-y-md pointer-events-none">
            <Transition
                v-for="toast in toasts"
                :key="toast.id"
                name="toast"
                @leave="() => removeToast(toast.id)"
            >
                <div
                    :class="[
                        'px-base py-md rounded-md border flex items-center gap-md pointer-events-auto',
                        'shadow-card backdrop-blur-sm',
                        typeClasses[toast.type]
                    ]"
                >
                    <Icon :name="typeIcons[toast.type]" class="w-5 h-5 flex-shrink-0" />
                    <p class="text-sm font-medium flex-1">{{ toast.message }}</p>
                    <button
                        @click="removeToast(toast.id)"
                        class="p-xs text-current hover:opacity-75 transition-opacity"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </Transition>
        </div>
    </Teleport>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.24s ease;
}

.toast-enter-from {
    opacity: 0;
    transform: translateX(100%);
}

.toast-leave-to {
    opacity: 0;
    transform: translateX(100%);
}
</style>
