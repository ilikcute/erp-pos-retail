<script setup>
import { ref, computed, watch } from 'vue';
import LoadingSkeleton from '@/Components/Feedback/LoadingSkeleton.vue';
import EmptyState from '@/Components/Feedback/EmptyState.vue';

const props = defineProps({
    columns: { type: Array, required: true },
    rows: { type: Array, required: true },
    loading: { type: Boolean, default: false },
    paginated: { type: Boolean, default: true },
    pageSize: { type: Number, default: 15 },
    searchable: { type: Boolean, default: false },
    searchableColumns: { type: Array, default: () => [] },
    striped: { type: Boolean, default: true },
    emptyTitle: { type: String, default: 'Belum ada data' },
    emptyDescription: { type: String, default: '' },
    selectable: { type: Boolean, default: false },
    selectionMode: { type: String, default: 'single' }, // 'single' or 'multiple'
    responsive: { type: Boolean, default: true },
});

const emit = defineEmits(['sort', 'search', 'row-click', 'selection-change']);

const currentPage = ref(1);
const searchQuery = ref('');
const sortKey = ref(null);
const sortOrder = ref('asc');
const selectedRows = ref([]);

const filteredRows = computed(() => {
    let result = props.rows;

    if (searchQuery.value && props.searchableColumns.length > 0) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(row =>
            props.searchableColumns.some(col => {
                const value = String(row[col]).toLowerCase();
                return value.includes(query);
            })
        );
    }

    return result;
});

const paginatedRows = computed(() => {
    if (!props.paginated) return filteredRows.value;

    const start = (currentPage.value - 1) * props.pageSize;
    const end = start + props.pageSize;
    return filteredRows.value.slice(start, end);
});

const totalPages = computed(() => {
    if (!props.paginated) return 1;
    return Math.ceil(filteredRows.value.length / props.pageSize);
});

const handleSort = (key) => {
    if (sortKey.value === key) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortKey.value = key;
        sortOrder.value = 'asc';
    }
    emit('sort', { key, order: sortOrder.value });
};

const handleSearch = (query) => {
    searchQuery.value = query;
    currentPage.value = 1;
    emit('search', query);
};

const toggleRowSelection = (row) => {
    if (props.selectionMode === 'single') {
        selectedRows.value = selectedRows.value[0]?.id === row.id ? [] : [row];
    } else {
        const index = selectedRows.value.findIndex(r => r.id === row.id);
        if (index > -1) {
            selectedRows.value.splice(index, 1);
        } else {
            selectedRows.value.push(row);
        }
    }
    emit('selection-change', selectedRows.value);
};

const isRowSelected = (row) => {
    return selectedRows.value.some(r => r.id === row.id);
};

const handleRowClick = (row) => {
    if (props.selectable) {
        toggleRowSelection(row);
    }
    emit('row-click', row);
};

// Reset selection when rows change
watch(() => props.rows, () => {
    selectedRows.value = [];
});
</script>

<template>
    <div class="space-y-base">
        <!-- Search Bar -->
        <div v-if="searchable && searchableColumns.length > 0" class="flex gap-md">
            <div class="flex-1 relative">
                <input :value="searchQuery" @input="handleSearch($event.target.value)" placeholder="Cari..."
                    class="w-full px-base py-md pl-2xl rounded-md border border-border-soft bg-surface-card text-ink-primary placeholder-ink-muted focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent" />
                <svg class="absolute left-base top-1/2 -translate-y-1/2 w-4 h-4 text-ink-muted" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>

        <!-- Table -->
        <div class="border border-border-soft rounded-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <!-- Header -->
                    <thead class="bg-surface-subtle border-b border-border-soft">
                        <tr>
                            <th v-for="col in columns" :key="col.key" :class="[
                                'px-base py-md text-left text-xs font-semibold text-ink-secondary uppercase tracking-wider',
                                col.sortable && 'cursor-pointer hover:bg-surface-main transition-colors'
                            ]" @click="col.sortable && handleSort(col.key)">
                                <div class="flex items-center gap-sm">
                                    {{ col.label }}
                                    <svg v-if="col.sortable" :class="[
                                        'w-4 h-4 text-ink-muted transition-transform',
                                        sortKey === col.key && 'text-ink-primary',
                                        sortKey === col.key && sortOrder === 'desc' && 'rotate-180'
                                    ]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 16V4m0 0L3 8m4-4l4 4" />
                                    </svg>
                                </div>
                            </th>
                        </tr>
                    </thead>

                    <!-- Body -->
                    <tbody class="divide-y divide-border-soft">
                        <!-- Loading State -->
                        <tr v-if="loading">
                            <td :colspan="columns.length" class="px-base py-2xl">
                                <LoadingSkeleton :rows="5" />
                            </td>
                        </tr>

                        <!-- Empty State -->
                        <tr v-else-if="filteredRows.length === 0">
                            <td :colspan="columns.length" class="px-base py-2xl">
                                <EmptyState :title="emptyTitle" :description="emptyDescription" />
                            </td>
                        </tr>

                        <!-- Data Rows -->
                        <tr v-for="(row, idx) in paginatedRows" :key="row.id || idx" :class="[
                            'transition-colors hover:bg-surface-subtle',
                            striped && idx % 2 === 0 && 'bg-surface-main'
                        ]">
                            <td v-for="col in columns" :key="col.key" :class="[
                                'px-base py-md text-sm text-ink-primary',
                                col.align === 'right' && 'text-right',
                                col.align === 'center' && 'text-center'
                            ]">
                                <slot :name="`cell-${col.key}`" :row="row" :value="row[col.key]" :rowIndex="filteredRows.indexOf(row) + 1">
                                    <template v-if="col.key === 'index' || col.key === 'no' || col.key === 'row_num' || col.key === 'row_number'">
                                        {{ filteredRows.indexOf(row) + 1 }}
                                    </template>
                                    <template v-else>
                                        {{ row[col.key] }}
                                    </template>
                                </slot>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="paginated && totalPages > 1" class="flex items-center justify-between px-base py-md">
            <span class="text-xs text-ink-muted">
                {{ (currentPage - 1) * pageSize + 1 }}-{{ Math.min(currentPage * pageSize, filteredRows.length) }} dari
                {{ filteredRows.length }}
            </span>

            <div class="flex gap-sm">
                <button @click="currentPage = Math.max(1, currentPage - 1)" :disabled="currentPage === 1"
                    class="p-md rounded-md border border-border-soft text-ink-secondary hover:bg-surface-subtle disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <div class="flex items-center gap-xs">
                    <button v-for="page in totalPages" :key="page" @click="currentPage = page" :class="[
                        'px-md py-sm rounded-md text-xs font-medium transition-colors',
                        currentPage === page
                            ? 'bg-brand text-white'
                            : 'border border-border-soft text-ink-secondary hover:bg-surface-subtle'
                    ]">
                        {{ page }}
                    </button>
                </div>

                <button @click="currentPage = Math.min(totalPages, currentPage + 1)"
                    :disabled="currentPage === totalPages"
                    class="p-md rounded-md border border-border-soft text-ink-secondary hover:bg-surface-subtle disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>
