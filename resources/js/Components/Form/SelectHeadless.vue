<script setup>
import { computed } from 'vue';
import {
    Listbox,
    ListboxLabel,
    ListboxButton,
    ListboxOptions,
    ListboxOption,
} from '@headlessui/vue';

const props = defineProps({
    modelValue: {
        type: [String, Number, Object],
        required: true,
    },
    options: {
        type: Array,
        required: true,
        validator: (arr) => arr.every(opt =>
            (typeof opt === 'object' && opt.value !== undefined && opt.label) ||
            typeof opt === 'string'
        ),
    },
    label: {
        type: String,
        default: null,
    },
    placeholder: {
        type: String,
        default: 'Pilih opsi...',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    multiple: {
        type: Boolean,
        default: false,
    },
    searchable: {
        type: Boolean,
        default: false,
    },
    error: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(['update:modelValue']);

const normalizedOptions = computed(() => {
    return props.options.map(opt =>
        typeof opt === 'string'
            ? { label: opt, value: opt }
            : opt
    );
});

const selectedOption = computed(() => {
    return normalizedOptions.value.find(opt => opt.value === props.modelValue);
});

const displayLabel = computed(() => {
    if (Array.isArray(props.modelValue)) {
        return props.modelValue
            .map(val => normalizedOptions.value.find(opt => opt.value === val)?.label)
            .filter(Boolean)
            .join(', ') || props.placeholder;
    }
    return selectedOption.value?.label || props.placeholder;
});
</script>

<template>
    <div class="space-y-2">
        <ListboxLabel v-if="label" class="block text-sm font-medium text-ink-primary">
            {{ label }}
        </ListboxLabel>
        <Listbox :modelValue="modelValue" @update:modelValue="(value) => emit('update:modelValue', value)"
            :multiple="multiple" :disabled="disabled" as="div" class="relative">
            <ListboxButton :class="[
                'relative w-full px-4 py-2.5 text-left bg-surface-card border rounded-md focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent transition-colors',
                error
                    ? 'border-red-500'
                    : 'border-border-soft hover:border-border-strong',
                disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
            ]">
                <span class="block truncate text-ink-secondary">{{ displayLabel }}</span>
                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                    <svg class="h-5 w-5 text-ink-muted" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                </span>
            </ListboxButton>

            <ListboxOptions
                class="absolute z-50 w-full mt-1 bg-surface-card border border-border-soft rounded-md shadow-lg max-h-60 overflow-auto">
                <ListboxOption v-for="option in normalizedOptions" v-slot="{ active, selected }" :key="option.value"
                    :value="option.value" :disabled="option.disabled">
                    <li :class="[
                        'px-4 py-2.5 text-sm cursor-pointer transition-colors',
                        active ? 'bg-brand text-white' : 'text-ink-primary',
                        selected && !active ? 'bg-surface-subtle text-brand font-medium' : '',
                        option.disabled ? 'opacity-50 cursor-not-allowed' : ''
                    ]">
                        <span :class="selected ? 'font-medium' : ''" class="block truncate">
                            {{ option.label }}
                        </span>
                    </li>
                </ListboxOption>
            </ListboxOptions>
        </Listbox>

        <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
    </div>
</template>
