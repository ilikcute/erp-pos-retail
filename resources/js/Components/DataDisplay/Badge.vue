<script setup>
const props = defineProps({
    label: {
        type: String,
        required: true
    },
    color: {
        type: String,
        default: 'gray' // 'gray', 'red', 'yellow', 'green', 'blue', 'indigo', 'purple', 'pink'
    },
    variant: {
        type: String,
        default: 'solid' // 'solid', 'outlined', 'soft'
    },
    size: {
        type: String,
        default: 'md' // 'xs', 'sm', 'md', 'lg'
    },
    icon: {
        type: String,
        default: null
    },
    removable: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['remove']);

const colorClasses = {
    gray: { solid: 'bg-gray-200 text-gray-800', outlined: 'border border-gray-400 text-gray-800', soft: 'bg-gray-100 text-gray-700' },
    red: { solid: 'bg-red-200 text-red-800', outlined: 'border border-red-400 text-red-800', soft: 'bg-red-100 text-red-700' },
    yellow: { solid: 'bg-yellow-200 text-yellow-800', outlined: 'border border-yellow-400 text-yellow-800', soft: 'bg-yellow-100 text-yellow-700' },
    green: { solid: 'bg-green-200 text-green-800', outlined: 'border border-green-400 text-green-800', soft: 'bg-green-100 text-green-700' },
    blue: { solid: 'bg-blue-200 text-blue-800', outlined: 'border border-blue-400 text-blue-800', soft: 'bg-blue-100 text-blue-700' },
    indigo: { solid: 'bg-indigo-200 text-indigo-800', outlined: 'border border-indigo-400 text-indigo-800', soft: 'bg-indigo-100 text-indigo-700' },
    purple: { solid: 'bg-purple-200 text-purple-800', outlined: 'border border-purple-400 text-purple-800', soft: 'bg-purple-100 text-purple-700' },
    pink: { solid: 'bg-pink-200 text-pink-800', outlined: 'border border-pink-400 text-pink-800', soft: 'bg-pink-100 text-pink-700' },
};

const sizeClasses = {
    xs: 'px-2 py-0.5 text-xs',
    sm: 'px-2.5 py-1 text-xs',
    md: 'px-3 py-1.5 text-sm',
    lg: 'px-4 py-2 text-base',
};
</script>

<template>
    <span :class="[
        'inline-flex items-center gap-1.5 rounded-full font-medium',
        sizeClasses[size],
        colorClasses[color][variant]
    ]">
        <i v-if="icon" :class="[icon, 'w-3 h-3']" />
        {{ label }}
        <button v-if="removable" @click="emit('remove')" class="ml-1 hover:opacity-75 transition-opacity">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                    clip-rule="evenodd" />
            </svg>
        </button>
    </span>
</template>
