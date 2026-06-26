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
            { name: 'Reporting', href: '/reporting', icon: 'trending-up', permission: 'pos.transaction.view' },
        ],
    },
    {
        group: 'Sales & POS',
        items: [
            { name: 'POS', href: '/pos', icon: 'shopping-cart', permission: 'pos.session.view' },
            { name: 'Orders', href: '/pos/sales', icon: 'list', permission: 'pos.transaction.view' },
            { name: 'Shifts', href: '/pos/shifts', icon: 'clock', permission: 'pos.shift.view' },
        ],
    },
    {
        group: 'Inventory',
        items: [
            { name: 'Stock', href: '/inventory', icon: 'package', roles: ['superadmin', 'admin', 'gudang'] },
            { name: 'Transfer', href: '/inventory/transfer', icon: 'arrow-right-left', roles: ['superadmin', 'admin', 'gudang'] },
        ],
    },
    {
        group: 'Purchasing',
        items: [
            { name: 'PO', href: '/purchasing/po', icon: 'file-text', roles: ['superadmin', 'admin', 'purchasing'] },
            { name: 'Goods Receipt', href: '/purchasing/receipt', icon: 'inbox', roles: ['superadmin', 'admin', 'purchasing', 'gudang'] },
        ],
    },
    {
        group: 'Product & Pricing',
        items: [
            { name: 'Products', href: '/product/products', icon: 'box', permission: 'product.product.view' },
            { name: 'Categories', href: '/product/categories', icon: 'folder', permission: 'product.category.view' },
            { name: 'Brands', href: '/product/brands', icon: 'tag', permission: 'product.brand.view' },
            { name: 'Pricing', href: '/pricing', icon: 'percent', permission: 'pricing.price-list.view' },
            { name: 'Promotions', href: '/promotions', icon: 'tag', roles: ['superadmin', 'admin', 'manager'] },
            { name: 'Loyalty Program', href: '/loyalty', icon: 'users', roles: ['superadmin', 'admin', 'manager'] },
        ],
    },
    {
        group: 'Master Data',
        items: [
            { name: 'Suppliers', href: '/master-data/suppliers', icon: 'truck', permission: 'master-data.supplier.view' },
            { name: 'Customers', href: '/master-data/customers', icon: 'users', permission: 'master-data.customer.view' },
            { name: 'Customer Categories', href: '/master-data/customer-categories', icon: 'tag', permission: 'master-data.customer.view' },
            { name: 'Currencies', href: '/master-data/currencies', icon: 'dollar-sign', permission: 'system.setting.view' },
            { name: 'Taxes', href: '/master-data/taxes', icon: 'percent', permission: 'master-data.tax.view' },
            { name: 'Units', href: '/master-data/units', icon: 'layout', permission: 'master-data.unit.view' },
            { name: 'Unit Conversions', href: '/master-data/unit-conversions', icon: 'arrow-right-left', permission: 'master-data.unit.view' },
            { name: 'Price Lists', href: '/master-data/price-lists', icon: 'list', permission: 'pricing.price-list.view' },
        ],
    },
    {
        group: 'Accounting',
        items: [
            { name: 'Chart of Accounts', href: '/accounting/coa', icon: 'layout', roles: ['superadmin', 'admin', 'accounting'] },
            { name: 'Journals', href: '/accounting/journals', icon: 'book', roles: ['superadmin', 'admin', 'accounting'] },
        ],
    },
    {
        group: 'System',
        items: [
            { name: 'Users', href: '/system/users', icon: 'users', permission: 'system.user.view' },
            { name: 'Roles', href: '/system/roles', icon: 'lock', permission: 'system.role.view' },
            { name: 'Settings', href: '/system', icon: 'settings', permission: 'system.setting.view' },
        ],
    },
];

const checkPermission = (item) => {
    const auth = page.props.auth;
    if (!auth?.user) return false;

    // Superadmin bypass
    if (auth.roles?.includes('superadmin')) {
        return true;
    }

    // Role check (if specified)
    if (item.roles && item.roles.length > 0) {
        const hasRole = auth.roles?.some(r => item.roles.includes(r));
        if (hasRole) return true;
    }

    // Permission check (if specified)
    if (item.permission) {
        return auth.permissions?.includes(item.permission) === true;
    }

    // Default: if no roles and no permission are specified, show to everyone authenticated
    return !item.roles && !item.permission;
};

const visibleNavigationGroups = computed(() => {
    return navigationGroups.map(group => {
        const visibleItems = group.items.filter(item => checkPermission(item));
        return {
            ...group,
            items: visibleItems
        };
    }).filter(group => group.items.length > 0);
});

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
            class="bg-sidebar-gradient text-white transition-all duration-200 flex flex-col fixed h-screen z-50"
        >
            <!-- Logo -->
            <div class="h-16 flex items-center gap-md px-base border-b border-white/10 flex-shrink-0">
                <div class="w-10 h-10 rounded-xl bg-white/15 backdrop-blur flex items-center justify-center text-lg font-extrabold shadow-soft">
                    🛒
                </div>
                <div v-if="isSidebarOpen" class="leading-tight">
                    <h1 class="text-base font-extrabold tracking-tight">ERP&nbsp;POS</h1>
                    <p class="text-[11px] text-white/60 font-medium">Retail Suite</p>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 py-base space-y-base overflow-y-auto scroll-soft px-sm">
                <template v-for="(group, idx) in visibleNavigationGroups" :key="idx">
                    <div
                        v-if="isSidebarOpen"
                        @click="toggleGroup(group.group)"
                        class="px-sm py-xs flex justify-between items-center cursor-pointer rounded-md transition-colors select-none hover:bg-white/5"
                    >
                        <span class="text-[11px] font-bold text-white/45 uppercase tracking-widest">
                            {{ group.group }}
                        </span>
                        <svg
                            :class="isGroupExpanded(group.group) ? 'rotate-180' : ''"
                            class="w-3 h-3 text-white/35 transition-transform duration-200"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>

                    <ul v-show="!isSidebarOpen || isGroupExpanded(group.group)" class="space-y-xs mb-sm">
                        <li v-for="item in group.items" :key="item.href">
                            <Link
                                :href="item.href"
                                :title="item.name"
                                :class="[
                                    'group flex items-center gap-md rounded-lg transition-all duration-150',
                                    isSidebarOpen ? 'px-md py-sm' : 'px-0 py-sm justify-center',
                                    isCurrentPage(item.href)
                                        ? 'bg-white text-brand font-semibold shadow-brand-glow'
                                        : 'text-white/75 hover:bg-white/10 hover:text-white'
                                ]"
                            >
                                <span
                                    :class="[
                                        'w-5 h-5 flex-shrink-0 transition-transform group-hover:scale-110',
                                        isCurrentPage(item.href) ? 'text-brand' : 'text-white/80'
                                    ]"
                                >
                                    <Icon :name="item.icon" />
                                </span>
                                <span v-if="isSidebarOpen" class="text-sm">{{ item.name }}</span>
                            </Link>
                        </li>
                    </ul>
                </template>
            </nav>

            <!-- Sidebar footer user card -->
            <div v-if="isSidebarOpen" class="p-sm border-t border-white/10 flex-shrink-0">
                <div class="flex items-center gap-md rounded-xl bg-white/10 px-md py-sm">
                    <div class="w-9 h-9 rounded-full bg-sunset-gradient flex items-center justify-center text-sm font-bold uppercase">
                        {{ user.name?.charAt(0) }}
                    </div>
                    <div class="leading-tight overflow-hidden">
                        <p class="text-sm font-semibold truncate">{{ user.name }}</p>
                        <p class="text-[11px] text-white/60 truncate">{{ user.email }}</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div :class="isSidebarOpen ? 'ml-64' : 'ml-20'" class="flex-1 flex flex-col transition-all duration-200">
            <!-- Topbar -->
            <header class="h-16 bg-surface-card/90 backdrop-blur border-b border-border-soft sticky top-0 z-40 flex items-center justify-between px-xl">
                <button
                    @click="isSidebarOpen = !isSidebarOpen"
                    class="p-sm text-ink-secondary hover:text-brand hover:bg-brand-soft rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-brand"
                    aria-label="Toggle sidebar"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <div class="flex items-center gap-base">
                    <div class="hidden sm:flex items-center gap-sm rounded-pill bg-surface-muted px-base py-sm text-ink-muted">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
                        <span class="text-sm">Cari menu, produk...</span>
                    </div>
                    <div class="flex items-center gap-md pl-base border-l border-border-soft">
                        <div class="w-9 h-9 rounded-full bg-brand-gradient flex items-center justify-center text-white text-sm font-bold uppercase shadow-brand-glow">
                            {{ user.name?.charAt(0) }}
                        </div>
                        <span class="hidden sm:block text-sm font-semibold text-ink-primary">{{ user.name }}</span>
                        <Link
                            :href="route('logout')" method="post" as="button"
                            class="px-base py-sm text-sm font-semibold text-semantic-danger hover:bg-semantic-danger-soft rounded-pill transition-colors"
                        >
                            Logout
                        </Link>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto scroll-soft bg-surface-main">
                <div class="p-xl">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
