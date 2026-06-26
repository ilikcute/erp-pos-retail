<script setup>
const props = defineProps({
    modelValue: { type: [String, Number], default: '' },
    label: { type: String, default: '' },
    error: { type: String, default: '' },
    helper: { type: String, default: '' },
    type: { type: String, default: 'text' },
    placeholder: { type: String, default: '' },
    required: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    readonly: { type: Boolean, default: false },
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

        <input
            :type="type"
            :value="modelValue"
            @input="emit('update:modelValue', $event.target.value)"
            :placeholder="placeholder"
            :disabled="disabled"
            :readonly="readonly"
            :class="[
                'w-full px-base py-md rounded-lg border transition-all duration-200',
                'text-base text-ink-primary placeholder-ink-muted',
                'focus:outline-none focus:ring-2 focus:ring-offset-0',
                error
                    ? 'border-semantic-danger focus:ring-semantic-danger'
                    : 'border-border-soft focus:border-brand focus:ring-brand',
                disabled && 'bg-surface-subtle cursor-not-allowed opacity-50',
                readonly && 'bg-surface-subtle cursor-default'
            ]"
        />

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

