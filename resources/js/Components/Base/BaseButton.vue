<script setup>
defineProps({
    variant: { type: String, default: 'primary' },
    size: { type: String, default: 'md' },
    loading: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    type: { type: String, default: 'submit' },
});

const variantClasses = {
    primary:   'bg-brand-gradient text-white shadow-brand-glow hover:opacity-95',
    secondary: 'bg-surface-card text-ink-primary border border-border-soft hover:border-brand-border hover:bg-surface-muted',
    ghost:     'text-ink-primary hover:bg-surface-muted',
    soft:      'bg-brand-soft text-brand hover:bg-brand-border',
    danger:    'bg-semantic-danger text-white hover:opacity-90 shadow-soft',
    success:   'bg-mint-gradient text-white hover:opacity-95 shadow-mint-glow',
    warning:   'bg-sunset-gradient text-white hover:opacity-95',
    icon:      'text-ink-secondary hover:text-brand hover:bg-brand-soft',
};

const sizeClasses = {
    sm:   'px-md py-sm text-xs gap-sm h-8',
    md:   'px-lg py-md text-sm gap-sm h-10',
    lg:   'px-xl py-md text-base gap-md h-12',
    xl:   'px-2xl py-lg text-base gap-md h-14',
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
            'inline-flex items-center justify-center rounded-pill font-semibold transition-all duration-150 active:scale-95',
            'focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2',
            'disabled:opacity-40 disabled:cursor-not-allowed disabled:active:scale-100',
            loading && 'opacity-75'
        ]"
    >
        <svg v-if="loading" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <slot />
    </button>
</template>
