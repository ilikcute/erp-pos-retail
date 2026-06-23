<script setup>
const props = defineProps({
    columns: { type: Array, required: true }, // [{key, label, sortable?}]
    rows: { type: Array, required: true },
    loading: { type: Boolean, default: false },
    emptyMessage: { type: String, default: "Belum ada data." },
});

const emit = defineEmits(["sort"]);
</script>

<template>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th
                            v-for="col in columns"
                            :key="col.key"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            :class="{
                                'cursor-pointer hover:bg-gray-100':
                                    col.sortable,
                            }"
                            @click="col.sortable && emit('sort', col.key)"
                        >
                            {{ col.label }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <!-- Loading State -->
                    <tr v-if="loading">
                        <td
                            :colspan="columns.length"
                            class="px-6 py-12 text-center"
                        >
                            <div class="flex justify-center items-center">
                                <svg
                                    class="animate-spin h-8 w-8 text-blue-600"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    ></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                                    ></path>
                                </svg>
                            </div>
                        </td>
                    </tr>

                    <!-- Empty State (BAB 6.21) -->
                    <tr v-else-if="rows.length === 0">
                        <td
                            :colspan="columns.length"
                            class="px-6 py-12 text-center text-gray-500"
                        >
                            <p class="text-lg">{{ emptyMessage }}</p>
                        </td>
                    </tr>

                    <!-- Data Rows -->
                    <tr
                        v-else
                        v-for="(row, idx) in rows"
                        :key="row.id || idx"
                        class="hover:bg-gray-50"
                    >
                        <td
                            v-for="col in columns"
                            :key="col.key"
                            class="px-6 py-4 whitespace-nowrap text-sm"
                        >
                            <slot
                                :name="`cell-${col.key}`"
                                :row="row"
                                :value="row[col.key]"
                            >
                                {{ row[col.key] }}
                            </slot>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
