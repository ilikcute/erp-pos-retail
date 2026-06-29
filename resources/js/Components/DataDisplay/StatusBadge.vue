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
        solid: 'bg-surface-muted text-ink-primary',
        outlined: 'border border-border-strong text-ink-secondary',
        soft: 'bg-surface-subtle text-ink-muted border border-border-soft',
    },
    blue: {
        solid: 'bg-brand text-white',
        outlined: 'border border-brand-border text-brand',
        soft: 'bg-brand-soft text-brand',
    },
    green: {
        solid: 'bg-semantic-success text-white',
        outlined: 'border border-semantic-success/30 text-semantic-success',
        soft: 'bg-semantic-success-soft text-semantic-success',
    },
    red: {
        solid: 'bg-semantic-danger text-white',
        outlined: 'border border-semantic-danger/30 text-semantic-danger',
        soft: 'bg-semantic-danger-soft text-semantic-danger',
    },
    yellow: {
        solid: 'bg-semantic-warning text-white',
        outlined: 'border border-semantic-warning/30 text-semantic-warning',
        soft: 'bg-semantic-warning-soft text-semantic-warning',
    },
    orange: {
        solid: 'bg-accent-coral text-white',
        outlined: 'border border-accent-coral/30 text-accent-coral',
        soft: 'bg-accent-coral-soft text-accent-coral',
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
