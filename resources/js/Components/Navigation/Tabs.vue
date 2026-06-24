<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    tabs: {
        type: Array,
        required: true,
        validator: (arr) => arr.every(tab => tab.id && tab.label)
    },
    modelValue: {
        type: [String, Number],
        required: true
    },
    variant: {
        type: String,
        default: 'default', // 'default', 'pills', 'underline'
    },
    size: {
        type: String,
        default: 'md', // 'sm', 'md', 'lg'
    },
    fullWidth: {
        type: Boolean,
        default: false
    },
    lazy: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:modelValue', 'change']);

const activeTab = computed({
    get: () => props.modelValue,
    set: (value) => {
        emit('update:modelValue', value);
        emit('change', value);
    }
});

const sizeClasses = {
    sm: 'px-3 py-1.5 text-xs',
    md: 'px-4 py-2 text-sm',
    lg: 'px-6 py-3 text-base'
};

const variantClasses = {
    default: {
        tab: 'border-b-2 border-transparent text-ink-secondary hover:text-ink-primary hover:border-border-soft transition-colors',
        active: 'border-brand text-ink-primary font-medium'
    },
    pills: {
        tab: 'rounded-md text-ink-secondary bg-transparent hover:bg-surface-subtle transition-colors',
        active: 'bg-brand text-white font-medium'
    },
    underline: {
        tab: 'text-ink-secondary hover:text-ink-primary transition-colors pb-2 border-b-2 border-transparent',
        active: 'text-ink-primary border-brand font-medium'
    }
};
</script>

<template>
    <div class="space-y-md">
        <!-- Tabs Navigation -->
        <div :class="[
            'flex gap-md overflow-x-auto',
            fullWidth ? 'w-full' : '',
            variant === 'pills' ? 'p-md bg-surface-subtle rounded-lg' : 'border-b border-border-soft'
        ]">
            <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id" :class="[
                sizeClasses[size],
                'font-medium transition-all duration-200 whitespace-nowrap flex-shrink-0',
                activeTab === tab.id
                    ? variantClasses[variant].active
                    : variantClasses[variant].tab,
                fullWidth && variant !== 'pills' ? 'flex-1' : '',
                tab.disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
            ]" :disabled="tab.disabled">
                <span v-if="tab.icon" class="inline-flex items-center gap-2">
                    <i :class="tab.icon" />
                    {{ tab.label }}
                </span>
                <span v-else>{{ tab.label }}</span>
                <span v-if="tab.badge" class="ml-2 px-2 py-0.5 text-xs bg-brand text-white rounded-full">
                    {{ tab.badge }}
                </span>
            </button>
        </div>

        <!-- Tabs Content -->
        <div class="space-y-md">
            <slot v-for="tab in tabs" :key="tab.id" :name="`content-${tab.id}`" :active="activeTab === tab.id">
                <div v-if="!lazy || activeTab === tab.id" class="animate-fade-in">
                    <!-- Content akan di-slot dari parent -->
                </div>
            </slot>
        </div>
    </div>
</template>

<style scoped>
@keyframes fade-in {
    from {
        opacity: 0;
    }

    to {
        opacity: 1;
    }
}

.animate-fade-in {
    animation: fade-in 200ms ease-in-out;
}
</style>
