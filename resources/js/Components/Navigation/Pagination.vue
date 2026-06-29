<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    links: {
        type: Array,
        default: null,
    },
    meta: {
        type: Object,
        default: null,
    },
    isClient: {
        type: Boolean,
        default: false,
    },
    totalItems: {
        type: Number,
        default: 0,
    },
    currentPage: {
        type: Number,
        default: 1,
    },
    pageSize: {
        type: Number,
        default: 15,
    },
});

const emit = defineEmits(['change-page']);

const clientPages = computed(() => {
    if (!props.isClient) return [];
    const totalPages = Math.ceil(props.totalItems / props.pageSize);
    const pages = [];
    
    // Add "Previous" button
    pages.push({
        label: '<',
        active: false,
        url: props.currentPage > 1 ? 'prev' : null,
        pageNumber: props.currentPage - 1
    });

    for (let i = 1; i <= totalPages; i++) {
        pages.push({
            label: String(i),
            active: props.currentPage === i,
            url: 'page',
            pageNumber: i
        });
    }

    // Add "Next" button
    pages.push({
        label: '>',
        active: false,
        url: props.currentPage < totalPages ? 'next' : null,
        pageNumber: props.currentPage + 1
    });

    return pages;
});
</script>

<template>
    <div
        v-if="(isClient && totalItems > pageSize) || (!isClient && links && links.length > 3)"
        class="mt-4 flex flex-col sm:flex-row justify-between items-center bg-surface-card px-base py-md border border-border-soft rounded-lg shadow-soft gap-base"
    >
        <div>
            <p v-if="isClient" class="text-xs text-ink-muted">
                Showing
                <span class="font-semibold text-ink-primary">{{ (currentPage - 1) * pageSize + 1 }}</span>
                to
                <span class="font-semibold text-ink-primary">{{ Math.min(currentPage * pageSize, totalItems) }}</span>
                of
                <span class="font-semibold text-ink-primary">{{ totalItems }}</span>
                results
            </p>
            <p v-else-if="meta" class="text-xs text-ink-muted">
                Showing
                <span class="font-semibold text-ink-primary">{{ meta.from || 0 }}</span>
                to
                <span class="font-semibold text-ink-primary">{{ meta.to || 0 }}</span>
                of
                <span class="font-semibold text-ink-primary">{{ meta.total }}</span>
                results
            </p>
        </div>
        
        <div class="flex flex-wrap gap-xs">
            <!-- Client Side -->
            <template v-if="isClient">
                <button
                    v-for="(link, key) in clientPages"
                    :key="'c-' + key"
                    type="button"
                    @click="link.url && emit('change-page', link.pageNumber)"
                    :disabled="!link.url"
                    :class="[
                        'px-3 py-1.5 border rounded-md text-xs transition-all font-medium flex items-center justify-center min-w-[32px] h-[32px]',
                        link.active
                            ? 'bg-brand text-white border-brand'
                            : 'bg-surface-card text-ink-secondary border-border-soft hover:bg-surface-subtle',
                        !link.url ? 'opacity-50 cursor-not-allowed' : '',
                    ]"
                >
                    <template v-if="link.label === '<'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </template>
                    <template v-else-if="link.label === '>'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </template>
                    <template v-else>
                        <span>{{ link.label }}</span>
                    </template>
                </button>
            </template>

            <!-- Server Side -->
            <template v-else>
                <Link
                    v-for="(link, key) in links"
                    :key="'s-' + key"
                    :href="link.url || '#'"
                    preserve-state
                    preserve-scroll
                    :class="[
                        'px-3 py-1.5 border rounded-md text-xs transition-all font-medium flex items-center justify-center min-w-[32px] h-[32px]',
                        link.active
                            ? 'bg-brand text-white border-brand'
                            : 'bg-surface-card text-ink-secondary border-border-soft hover:bg-surface-subtle',
                        !link.url ? 'opacity-50 cursor-not-allowed pointer-events-none' : '',
                    ]"
                >
                    <template v-if="link.label.includes('Previous') || link.label.includes('&laquo;') || link.label.includes('<')">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </template>
                    <template v-else-if="link.label.includes('Next') || link.label.includes('&raquo;') || link.label.includes('>')">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </template>
                    <template v-else>
                        <span v-html="link.label"></span>
                    </template>
                </Link>
            </template>
        </div>
    </div>
</template>
