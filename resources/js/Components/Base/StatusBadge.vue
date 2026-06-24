<script setup>
const props = defineProps({
    status: { type: String, required: true },
    size: { type: String, default: 'md' },
});

const statusConfig = {
    DRAFT: { bg: 'bg-surface-subtle', text: 'text-ink-secondary', border: 'border-border-soft' },
    PENDING: { bg: 'bg-semantic-info-soft', text: 'text-semantic-info', border: 'border-semantic-info' },
    APPROVED: { bg: 'bg-semantic-success-soft', text: 'text-semantic-success', border: 'border-semantic-success' },
    POSTED: { bg: 'bg-semantic-success-soft', text: 'text-semantic-success', border: 'border-semantic-success' },
    CANCELLED: { bg: 'bg-semantic-danger-soft', text: 'text-semantic-danger', border: 'border-semantic-danger' },
    REJECTED: { bg: 'bg-semantic-danger-soft', text: 'text-semantic-danger', border: 'border-semantic-danger' },
    VOID: { bg: 'bg-semantic-danger-soft', text: 'text-semantic-danger', border: 'border-semantic-danger' },
    CLOSED: { bg: 'bg-surface-subtle', text: 'text-ink-secondary', border: 'border-border-soft' },
    OPEN: { bg: 'bg-semantic-info-soft', text: 'text-semantic-info', border: 'border-semantic-info' },
    SYNCED: { bg: 'bg-semantic-success-soft', text: 'text-semantic-success', border: 'border-semantic-success' },
    OFFLINE: { bg: 'bg-semantic-warning-soft', text: 'text-semantic-warning', border: 'border-semantic-warning' },
    FAILED: { bg: 'bg-semantic-danger-soft', text: 'text-semantic-danger', border: 'border-semantic-danger' },
};

const sizeClasses = {
    sm: 'px-sm py-xs text-xs',
    md: 'px-md py-sm text-xs',
    lg: 'px-lg py-md text-sm',
};

const config = statusConfig[props.status] || statusConfig.DRAFT;
const sizeClass = sizeClasses[props.size];
</script>

<template>
    <span
        :class="[
            config.bg,
            config.text,
            config.border,
            sizeClass,
            'inline-flex items-center rounded-pill font-medium border'
        ]"
    >
        {{ status }}
    </span>
</template>
