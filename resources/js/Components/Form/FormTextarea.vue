<script setup>
const props = defineProps({
    modelValue: { type: String, default: "" },
    label: { type: String, default: "" },
    error: { type: String, default: "" },
    rows: { type: Number, default: 3 },
    placeholder: { type: String, default: "" },
    required: { type: Boolean, default: false },
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
        <textarea
            :value="modelValue"
            @input="emit('update:modelValue', $event.target.value)"
            :rows="rows"
            :placeholder="placeholder"
            :class="{ 'border-red-500 focus:ring-red-500': error }"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        ></textarea>
        <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
    </div>
</template>
