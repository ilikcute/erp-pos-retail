<script setup>
const props = defineProps({
    modelValue: { type: [String, Number, null], default: null },
    label: { type: String, default: '' },
    error: { type: String, default: '' },
    helper: { type: String, default: '' },
    options: { type: Array, default: () => [] },
    placeholder: { type: String, default: 'Pilih...' },
    required: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <div class="space-y-sm">
        <div v-if="label" class="flex items-center justify-between">
            <label class="text-sm font-medium text-ink-primary">
                {{ label }}
                <span v-if="required" class="text-semantic-danger ml-xs">*</span>
            </label>
        </div>

        <div class="relative">
            <select
                :value="modelValue"
                @change="emit('update:modelValue', $event.target.value)"
                :disabled="disabled"
                :class="[
                    'w-full px-base py-md rounded-lg border transition-all duration-200',
                    'text-base text-ink-primary bg-surface-card appearance-none cursor-pointer',
                    'focus:outline-none focus:ring-2 focus:ring-offset-0',
                    error
                        ? 'border-semantic-danger focus:ring-semantic-danger'
                        : 'border-border-soft focus:border-brand focus:ring-brand',
                    disabled && 'bg-surface-subtle cursor-not-allowed opacity-50'
                ]"
            >
                <option value="" disabled>{{ placeholder }}</option>
                <slot>
                    <option v-for="opt in options" :key="opt.value" :value="opt.value">
                        {{ opt.label }}
                    </option>
                </slot>
            </select>
            <svg class="absolute right-base top-1/2 -translate-y-1/2 w-4 h-4 text-ink-secondary pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
            </svg>
        </div>

        <div v-if="error || helper" class="flex items-start gap-sm">
            <svg
                v-if="error"
                class="w-4 h-4 text-semantic-danger flex-shrink-0 mt-px"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p :class="error ? 'text-semantic-danger' : 'text-ink-muted'" class="text-xs">
                {{ error || helper }}
            </p>
        </div>
    </div>
</template>

