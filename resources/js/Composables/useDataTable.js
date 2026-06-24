import { ref, computed, reactive } from "vue";

export function useSearch(items = [], searchableFields = []) {
    const searchQuery = ref("");

    const filteredItems = computed(() => {
        if (!searchQuery.value.trim()) return items;

        const query = searchQuery.value.toLowerCase();
        return items.filter((item) =>
            searchableFields.some((field) => {
                const value = String(item[field] || "").toLowerCase();
                return value.includes(query);
            }),
        );
    });

    const setSearchQuery = (query) => {
        searchQuery.value = query;
    };

    const clearSearch = () => {
        searchQuery.value = "";
    };

    return {
        searchQuery,
        filteredItems,
        setSearchQuery,
        clearSearch,
    };
}

export function useFilter(items = []) {
    const filters = reactive({});
    const activeFilters = reactive({});

    const addFilter = (name, filterFn) => {
        filters[name] = filterFn;
    };

    const removeFilter = (name) => {
        delete filters[name];
        delete activeFilters[name];
    };

    const setFilterValue = (name, value) => {
        if (value === null || value === undefined) {
            delete activeFilters[name];
        } else {
            activeFilters[name] = value;
        }
    };

    const filteredItems = computed(() => {
        let result = items;

        Object.entries(activeFilters).forEach(([filterName, filterValue]) => {
            if (filters[filterName] && filterValue !== null) {
                result = result.filter((item) =>
                    filters[filterName](item, filterValue),
                );
            }
        });

        return result;
    });

    const clearFilters = () => {
        Object.keys(activeFilters).forEach((key) => delete activeFilters[key]);
    };

    const hasActiveFilters = computed(
        () => Object.keys(activeFilters).length > 0,
    );

    return {
        filters,
        activeFilters,
        addFilter,
        removeFilter,
        setFilterValue,
        filteredItems,
        clearFilters,
        hasActiveFilters,
    };
}

export function useSort(items = []) {
    const sortKey = ref(null);
    const sortOrder = ref("asc");

    const sortedItems = computed(() => {
        if (!sortKey.value) return items;

        const sorted = [...items].sort((a, b) => {
            const aVal = a[sortKey.value];
            const bVal = b[sortKey.value];

            if (aVal === bVal) return 0;
            if (aVal === null || aVal === undefined) return 1;
            if (bVal === null || bVal === undefined) return -1;

            const comparison = aVal < bVal ? -1 : 1;
            return sortOrder.value === "asc" ? comparison : -comparison;
        });

        return sorted;
    });

    const setSortKey = (key) => {
        if (sortKey.value === key) {
            sortOrder.value = sortOrder.value === "asc" ? "desc" : "asc";
        } else {
            sortKey.value = key;
            sortOrder.value = "asc";
        }
    };

    const clearSort = () => {
        sortKey.value = null;
        sortOrder.value = "asc";
    };

    return {
        sortKey,
        sortOrder,
        sortedItems,
        setSortKey,
        clearSort,
    };
}

export function usePagination(items = [], pageSize = 15) {
    const currentPage = ref(1);

    const paginatedItems = computed(() => {
        const start = (currentPage.value - 1) * pageSize;
        const end = start + pageSize;
        return items.slice(start, end);
    });

    const totalPages = computed(() => {
        return Math.ceil(items.length / pageSize);
    });

    const hasNextPage = computed(() => currentPage.value < totalPages.value);
    const hasPrevPage = computed(() => currentPage.value > 1);

    const goToPage = (page) => {
        const pageNum = Math.max(1, Math.min(page, totalPages.value));
        currentPage.value = pageNum;
    };

    const nextPage = () => {
        if (hasNextPage.value) currentPage.value++;
    };

    const prevPage = () => {
        if (hasPrevPage.value) currentPage.value--;
    };

    const resetPage = () => {
        currentPage.value = 1;
    };

    return {
        currentPage,
        paginatedItems,
        totalPages,
        hasNextPage,
        hasPrevPage,
        goToPage,
        nextPage,
        prevPage,
        resetPage,
    };
}

// Combined hook for complete data management
export function useDataTable(initialItems = [], options = {}) {
    const { searchableFields = [], pageSize = 15 } = options;

    const items = ref(initialItems);
    const search = useSearch(items, searchableFields);
    const filter = useFilter(search.filteredItems);
    const sort = useSort(filter.filteredItems);
    const pagination = usePagination(sort.sortedItems, pageSize);

    const updateItems = (newItems) => {
        items.value = newItems;
        pagination.resetPage();
    };

    return {
        items,
        search,
        filter,
        sort,
        pagination,
        updateItems,
    };
}
