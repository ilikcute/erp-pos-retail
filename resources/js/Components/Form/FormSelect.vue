<script setup>
const props = defineProps({
    modelValue: { type: [String, Number, null], default: null },
    label: { type: String, default: "" },
    error: { type: String, default: "" },
    options: { type: Array, default: () => [] }, // [{value, label}]
    placeholder: { type: String, default: "Pilih..." },
    required: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
});

const emit = defineEmits(["update:modelValue"]);
</script>

<template>
    <div>
        <label
            v-if="label"
            class="block text-sm font-medium text-gray-700 mb-1"
        >
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>
        <select
            :value="modelValue"
            @change="emit('update:modelValue', $event.target.value)"
            :disabled="disabled"
            :class="{ 'border-red-500 focus:ring-red-500': error }"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100"
        >
            <option value="" disabled>{{ placeholder }}</option>
            <option v-for="opt in options" :key="opt.value" :value="opt.value">
                {{ opt.label }}
            </option>
        </select>
        <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
    </div>
</template>
