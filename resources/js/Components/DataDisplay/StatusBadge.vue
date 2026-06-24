<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: String,
        required: true
    },
    variant: {
        type: String,
        default: 'solid', // 'solid', 'outlined', 'soft'
    },
    size: {
        type: String,
        default: 'md', // 'sm', 'md', 'lg'
    },
    label: {
        type: String,
        default: null
    },
    icon: {
        type: String,
        default: null
    }
});

const statusConfig = {
    DRAFT: { color: 'gray', label: 'Draft' },
    PENDING: { color: 'yellow', label: 'Pending' },
    APPROVED: { color: 'blue', label: 'Approved' },
    POSTED: { color: 'green', label: 'Posted' },
    CANCELLED: { color: 'red', label: 'Cancelled' },
    VOID: { color: 'red', label: 'Void' },
    COMPLETED: { color: 'green', label: 'Completed' },
    REJECTED: { color: 'red', label: 'Rejected' },
    ACTIVE: { color: 'green', label: 'Active' },
    INACTIVE: { color: 'gray', label: 'Inactive' },
    PAID: { color: 'green', label: 'Paid' },
    UNPAID: { color: 'red', label: 'Unpaid' },
    PARTIAL: { color: 'yellow', label: 'Partial' },
    SHIPPED: { color: 'blue', label: 'Shipped' },
    RECEIVED: { color: 'green', label: 'Received' },
    RETURNED: { color: 'orange', label: 'Returned' },
};

const colorClasses = {
    gray: {
        solid: 'bg-gray-100 text-gray-800',
        outlined: 'border border-gray-300 text-gray-800',
        soft: 'bg-gray-50 text-gray-700',
    },
    blue: {
        solid: 'bg-blue-100 text-blue-800',
        outlined: 'border border-blue-300 text-blue-800',
        soft: 'bg-blue-50 text-blue-700',
    },
    green: {
        solid: 'bg-green-100 text-green-800',
        outlined: 'border border-green-300 text-green-800',
        soft: 'bg-green-50 text-green-700',
    },
    red: {
        solid: 'bg-red-100 text-red-800',
        outlined: 'border border-red-300 text-red-800',
        soft: 'bg-red-50 text-red-700',
    },
    yellow: {
        solid: 'bg-yellow-100 text-yellow-800',
        outlined: 'border border-yellow-300 text-yellow-800',
        soft: 'bg-yellow-50 text-yellow-700',
    },
    orange: {
        solid: 'bg-orange-100 text-orange-800',
        outlined: 'border border-orange-300 text-orange-800',
        soft: 'bg-orange-50 text-orange-700',
    },
};

const sizeClasses = {
    sm: 'px-2 py-0.5 text-xs',
    md: 'px-3 py-1 text-sm',
    lg: 'px-4 py-2 text-base',
};

const config = computed(() => statusConfig[props.status] || { color: 'gray', label: props.status });
const badgeClass = computed(() => colorClasses[config.value.color]?.[props.variant] || '');
const displayLabel = computed(() => props.label || config.value.label || props.status);
</script>

<template>
    <span :class="[
        'inline-flex items-center gap-2 rounded-full font-medium transition-colors',
        sizeClasses[size],
        badgeClass
    ]">
        <i v-if="icon" :class="[icon, 'w-4 h-4']" />
        {{ displayLabel }}
    </span>
</template>
