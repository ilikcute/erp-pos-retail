<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Icon from '@/Components/Base/Icon.vue';

const page = usePage();
const user = page.props.auth.user;
const isSidebarOpen = ref(true);

const navigationGroups = [
    {
        group: 'Overview',
        items: [
            { name: 'Dashboard', href: '/dashboard', icon: 'home' },
            { name: 'Reporting', href: '/reporting', icon: 'trending-up' },
        ],
    },
    {
        group: 'Sales & POS',
        items: [
            { name: 'POS', href: '/pos', icon: 'shopping-cart' },
            { name: 'Orders', href: '/pos/sales', icon: 'list' },
        ],
    },
    {
        group: 'Inventory',
        items: [
            { name: 'Stock', href: '/inventory', icon: 'package' },
            { name: 'Transfer', href: '/inventory/transfer', icon: 'arrow-right-left' },
        ],
    },
    {
        group: 'Purchasing',
        items: [
            { name: 'PO', href: '/purchasing/po', icon: 'file-text' },
            { name: 'Goods Receipt', href: '/purchasing/receipt', icon: 'inbox' },
        ],
    },
    {
        group: 'Master Data',
        items: [
            { name: 'Products', href: '/product/products', icon: 'box' },
            { name: 'Categories', href: '/product/categories', icon: 'folder' },
            { name: 'Brands', href: '/product/brands', icon: 'tag' },
            { name: 'Suppliers', href: '/master-data/suppliers', icon: 'truck' },
            { name: 'Customers', href: '/master-data/customers', icon: 'users' },
        ],
    },
    {
        group: 'Marketing',
        items: [
            { name: 'Pricing', href: '/pricing', icon: 'percent' },
            { name: 'Promotions', href: '/promotions', icon: 'tag' },
            { name: 'Loyalty Program', href: '/loyalty', icon: 'users' },
        ],
    },
    {
        group: 'Accounting',
        items: [
            { name: 'Chart of Accounts', href: '/accounting/coa', icon: 'layout' },
            { name: 'Journals', href: '/accounting/journals', icon: 'book' },
        ],
    },
    {
        group: 'System',
        items: [
            { name: 'Users', href: '/system/users', icon: 'users' },
            { name: 'Roles', href: '/system/roles', icon: 'lock' },
        ],
    },
];

const isCurrentPage = (href) => page.url === href || page.url.startsWith(href + '/');

const collapsedGroups = ref({});

const isGroupExpanded = (groupName) => {
    return collapsedGroups.value[groupName] !== true;
};

const toggleGroup = (groupName) => {
    collapsedGroups.value[groupName] = !collapsedGroups.value[groupName];
};

// Initialize collapse states based on active page
navigationGroups.forEach(group => {
    const hasActiveItem = group.items.some(item => isCurrentPage(item.href));
    if (hasActiveItem || group.group === 'Overview') {
        collapsedGroups.value[group.group] = false;
    } else {
        collapsedGroups.value[group.group] = true;
    }
});
</script>

<template>
    <div class="min-h-screen flex bg-surface-main">
        <!-- Sidebar -->
        <aside
            :class="isSidebarOpen ? 'w-64' : 'w-20'"
            class="bg-ink-primary text-white transition-all duration-200 flex flex-col border-r border-border-soft fixed h-screen overflow-y-auto"
        >
            <!-- Logo -->
            <div class="h-16 flex items-center justify-center border-b border-white/10 flex-shrink-0">
                <h1 v-if="isSidebarOpen" class="text-lg font-bold">ERP POS</h1>
                <h1 v-else class="text-lg font-bold">E</h1>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 py-base space-y-md overflow-y-auto">
                <template v-for="(group, idx) in navigationGroups" :key="idx">
                    <!-- Group Header (Collapsible) -->
                    <div
                        v-if="isSidebarOpen"
                        @click="toggleGroup(group.group)"
                        class="px-base py-sm flex justify-between items-center cursor-pointer hover:bg-white/5 rounded-md transition-colors select-none"
                    >
                        <span class="text-xs font-semibold text-white/50 uppercase tracking-wider">
                            {{ group.group }}
                        </span>
                        <!-- Chevron icon -->
                        <svg
                            :class="isGroupExpanded(group.group) ? 'rotate-180' : ''"
                            class="w-3.5 h-3.5 text-white/30 transition-transform duration-200"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                    
                    <!-- Sub-menu Items -->
                    <ul v-show="!isSidebarOpen || isGroupExpanded(group.group)" class="space-y-sm">
                        <li v-for="item in group.items" :key="item.href">
                            <Link
                                :href="item.href"
                                :class="[
                                    'flex items-center px-base py-md gap-md rounded-md transition-colors duration-200',
                                    isCurrentPage(item.href)
                                        ? 'bg-brand text-white'
                                        : 'text-white/70 hover:bg-white/10'
                                ]"
                            >
                                <span class="w-5 h-5 flex-shrink-0">
                                    <Icon :name="item.icon" />
                                </span>
                                <span v-if="isSidebarOpen" class="text-sm font-medium">
                                    {{ item.name }}
                                </span>
                            </Link>
                        </li>
                    </ul>
                </template>
            </nav>
        </aside>

        <!-- Main Content -->
        <div :class="isSidebarOpen ? 'ml-64' : 'ml-20'" class="flex-1 flex flex-col transition-all duration-200">
            <!-- Topbar -->
            <header class="h-16 bg-surface-card border-b border-border-soft sticky top-0 z-40 flex items-center justify-between px-xl shadow-soft">
                <button
                    @click="isSidebarOpen = !isSidebarOpen"
                    class="p-md text-ink-secondary hover:text-ink-primary hover:bg-surface-subtle rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-brand"
                    aria-label="Toggle sidebar"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <div class="flex items-center gap-xl">
                    <span class="text-sm font-medium text-ink-primary">{{ user.name }}</span>
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="px-base py-md text-sm font-medium text-semantic-danger hover:bg-semantic-danger-soft rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-semantic-danger"
                    >
                        Logout
                    </Link>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-surface-main">
                <div class="p-xl">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
