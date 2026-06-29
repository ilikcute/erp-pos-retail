<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Icon from '@/Components/Base/Icon.vue';

const props = defineProps({
    collapsed: { type: Boolean, default: false },
    isMobile: { type: Boolean, default: false },
    mobileOpen: { type: Boolean, default: false },
});
const emit = defineEmits(['close']);

const page = usePage();
const user = page.props.auth.user;

// On mobile the drawer always shows full labels; on desktop labels follow the mini-variant state
const expanded = computed(() => (props.isMobile ? true : !props.collapsed));

const onNavClick = () => { if (props.isMobile) emit("close"); };

// ---- navigation config + RBAC (moved out of the Layout) ----
const navigationGroups = [
    {
        group: 'Overview', icon: 'layout',
        items: [
            { name: 'Dashboard', href: '/dashboard', icon: 'home' },
            { name: 'Reporting', href: '/reporting', icon: 'trending-up', permission: 'pos.transaction.view' },
        ],
    },
    {
        group: 'Sales & POS', icon: 'shopping-cart',
        items: [
            { name: 'POS', href: '/pos', icon: 'shopping-cart', permission: 'pos.session.view' },
            { name: 'Orders', href: '/pos/sales', icon: 'list', permission: 'pos.transaction.view' },
            { name: 'Shifts', href: '/pos/shifts', icon: 'clock', permission: 'pos.shift.view' },
            { name: 'Tutup Harian', href: '/pos/day-closing', icon: 'lock', permission: 'pos.day-closing.manage' },
            { name: 'Tutup Bulanan', href: '/pos/month-closing', icon: 'shield', permission: 'pos.month-closing.manage' },
        ],
    },
    {
        group: 'Inventory', icon: 'package',
        items: [
            { name: 'Stock', href: '/inventory', icon: 'package', roles: ['superadmin', 'admin', 'gudang'] },
            { name: 'Transfer', href: '/inventory/transfer', icon: 'arrow-right-left', roles: ['superadmin', 'admin', 'gudang'] },
        ],
    },
    {
        group: 'Purchasing', icon: 'truck',
        items: [
            { name: 'PO', href: '/purchasing/po', icon: 'file-text', roles: ['superadmin', 'admin', 'purchasing'] },
            { name: 'Goods Receipt', href: '/purchasing/receipt', icon: 'inbox', roles: ['superadmin', 'admin', 'purchasing', 'gudang'] },
        ],
    },
    {
        group: 'Product & Pricing', icon: 'tag',
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
        group: 'Master Data', icon: 'folder',
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
        group: 'Accounting', icon: 'book',
        items: [
            { name: 'Chart of Accounts', href: '/accounting/coa', icon: 'layout', roles: ['superadmin', 'admin', 'accounting'] },
            { name: 'Journals', href: '/accounting/journals', icon: 'book', roles: ['superadmin', 'admin', 'accounting'] },
        ],
    },
    {
        group: 'System', icon: 'settings',
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
    <aside
        :class="[
            expanded ? 'w-56' : 'w-16',
            isMobile ? (mobileOpen ? 'translate-x-0' : '-translate-x-full') : 'translate-x-0',
        ]"
        class="bg-sidebar-gradient text-white transition-all duration-200 flex flex-col fixed h-screen z-50"
    >
        <!-- Logo -->
            <div class="h-16 flex items-center gap-md px-base border-b border-white/10 flex-shrink-0">
                <div class="w-10 h-10 rounded-xl bg-white/15 backdrop-blur flex items-center justify-center text-lg font-extrabold shadow-soft">
                    🛒
                </div>
                <div v-if="expanded" class="leading-tight">
                    <h1 class="text-base font-extrabold tracking-tight">ERP&nbsp;POS</h1>
                    <p class="text-[11px] text-white/60 font-medium">Retail Suite</p>
                </div>
            </div>

        <!-- Navigation -->
            <nav class="flex-1 py-base space-y-base overflow-y-auto scroll-soft px-sm">
                <template v-for="(group, idx) in visibleNavigationGroups" :key="idx">
                    <div v-if="!expanded" class="flex justify-center py-xs" :title="group.group">
                        <span class="w-5 h-5 text-white/40"><Icon :name="group.icon" /></span>
                    </div>
                    <div
                        v-if="expanded"
                        @click="toggleGroup(group.group)"
                        class="px-sm py-xs flex justify-between items-center cursor-pointer rounded-md transition-colors select-none hover:bg-white/5"
                    >
                        <span class="flex items-center gap-sm text-[11px] font-bold text-white/55 uppercase tracking-widest">
                            <span class="w-4 h-4 text-white/70"><Icon :name="group.icon" size="4" /></span>
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

                    <ul v-show="!expanded || isGroupExpanded(group.group)" class="space-y-xs mb-sm">
                        <li v-for="item in group.items" :key="item.href">
                            <Link @click="onNavClick"
                                :href="item.href"
                                :title="item.name"
                                :class="[
                                    'group flex items-center gap-md rounded-lg transition-all duration-150',
                                    expanded ? 'px-md py-sm' : 'px-0 py-sm justify-center',
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
                                <span v-if="expanded" class="text-sm">{{ item.name }}</span>
                            </Link>
                        </li>
                    </ul>
                </template>
            </nav>

            <!-- Sidebar footer user card -->
            <div v-if="expanded" class="p-sm border-t border-white/10 flex-shrink-0">
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
</template>