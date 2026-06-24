<script setup>
const props = defineProps({
    variant: { type: String, default: 'primary' },
    size: { type: String, default: 'md' },
    loading: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    type: { type: String, default: 'submit' },
});

const variantClasses = {
    primary: 'bg-brand hover:bg-brand-hover text-white',
    secondary: 'bg-surface-subtle hover:bg-border-soft text-ink-primary border border-border-soft',
    ghost: 'text-ink-primary hover:bg-surface-subtle',
    soft: 'bg-brand-soft hover:bg-brand-border text-brand border border-brand-border',
    danger: 'bg-semantic-danger hover:bg-semantic-danger text-white',
    success: 'bg-semantic-success hover:bg-semantic-success text-white',
    icon: 'text-ink-secondary hover:text-ink-primary hover:bg-surface-subtle',
};

const sizeClasses = {
    sm: 'px-md py-sm text-xs gap-sm h-8',
    md: 'px-base py-md text-sm gap-md h-10',
    lg: 'px-lg py-lg text-base gap-md h-12',
    xl: 'px-xl py-lg text-base gap-lg h-14',
    icon: 'w-10 h-10 p-0',
};
</script>

<template>
    <button
        :type="type"
        :disabled="disabled || loading"
        :class="[
            variantClasses[variant],
            sizeClasses[size],
            'inline-flex items-center justify-center rounded-md font-medium transition-all duration-200',
            'focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2',
            'disabled:opacity-50 disabled:cursor-not-allowed',
            loading && 'opacity-75'
        ]"
    >
        <svg
            v-if="loading"
            class="animate-spin w-4 h-4"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
        >
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <slot />
    </button>
</template>
