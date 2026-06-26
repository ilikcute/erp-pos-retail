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
    <div class="space-y-1.5">
        <ListboxLabel v-if="label" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
            {{ label }}
        </ListboxLabel>
        <Listbox :modelValue="modelValue" @update:modelValue="(value) => emit('update:modelValue', value)"
            :multiple="multiple" :disabled="disabled" as="div" class="relative">
            <ListboxButton :class="[
                'relative w-full px-4 py-2.5 text-left bg-white dark:bg-slate-800 border rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-colors',
                error
                    ? 'border-red-600'
                    : 'border-slate-300 dark:border-slate-700 hover:border-slate-400',
                disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-default'
            ]">
                <span class="block truncate text-slate-800 dark:text-slate-200">{{ displayLabel }}</span>
                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                     <svg class="h-5 w-5 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 3a.75.75 0 01.53.22l3.25 3.25a.75.75 0 11-1.06 1.06L10 4.81 7.28 7.53a.75.75 0 01-1.06-1.06l3.25-3.25A.75.75 0 0110 3zM6.75 12.75a.75.75 0 011.06 0L10 15.19l2.72-2.72a.75.75 0 111.06 1.06l-3.25 3.25a.75.75 0 01-1.06 0l-3.25-3.25a.75.75 0 010-1.06z" clip-rule="evenodd" />
                    </svg>
                </span>
            </ListboxButton>

            <transition leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100" leave-to-class="opacity-0">
                <ListboxOptions
                    class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-lg max-h-60 overflow-auto focus:outline-none">
                    <ListboxOption v-for="option in normalizedOptions" v-slot="{ active, selected }" :key="option.value"
                        :value="option.value" :disabled="option.disabled">
                        <li :class="[
                            'px-4 py-2 text-sm cursor-default transition-colors',
                            active ? 'bg-emerald-600 text-white' : 'text-slate-800 dark:text-slate-200',
                            option.disabled ? 'opacity-50 cursor-not-allowed' : ''
                        ]">
                            <span :class="[selected ? 'font-semibold' : 'font-normal', 'block truncate']">
                                {{ option.label }}
                            </span>
                        </li>
                    </ListboxOption>
                </ListboxOptions>
            </transition>
        </Listbox>

        <p v-if="error" class="mt-1 text-sm text-red-600 dark:text-red-500">{{ error }}</p>
    </div>
</template>
