<script setup>
import { Link, usePage }  from '@inertiajs/vue3';
import Icon from '@/Components/Base/Icon.vue';
defineProps({
    collapsed: { type: Boolean, default: false },
    isMobile: { type: Boolean, default: false },
});
const emit = defineEmits(['toggle']);
const page = usePage();
const user = page.props.auth.user;
</script>

<template>
    <header class="h-16 bg-surface-card/90 backdrop-blur border-b border-border-soft sticky top-0 z-20 flex items-center justify-between gap-md px-base md:px-lg">
        <div class="flex items-center gap-md min-w-0">
            <!-- Hamburger / collapse toggle -->
            <button
                type="button"
                @click="emit('toggle')"
                class="w-10 h-10 rounded-pill flex items-center justify-center text-ink-secondary hover:bg-surface-muted hover:text-ink-primary transition focus:outline-none focus:ring-2 focus:ring-brand"
                :aria-label="isMobile ? 'Buka menu' : 'Perkecil sidebar'"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
            </button>
            <!-- Search -->
            <div class="hidden sm:flex items-center gap-sm rounded-pill bg-surface-muted px-md py-xs w-64 max-w-full">
                <Icon name="search" size="4" class="text-ink-muted shrink-0" />
                <input type="text" placeholder="Cari produk, transaksi..." class="bg-transparent outline-none text-sm text-ink-primary placeholder:text-ink-muted w-full" />
            </div>
        </div>

        <div class="flex items-center gap-sm">
            <!-- Notifications -->
            <button type="button" class="relative w-10 h-10 rounded-pill flex items-center justify-center text-ink-secondary hover:bg-surface-muted hover:text-ink-primary transition">
                <Icon name="bell" size="5" />
                <span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-accent-coral"></span>
            </button>
            <!-- User -->
            <div class="flex items-center gap-sm pl-sm">
                <div class="w-9 h-9 rounded-full bg-sunset-gradient flex items-center justify-center text-sm font-bold text-white uppercase">{{ user.name?.charAt(0) }}</div>
                <div class="hidden md:block leading-tight">
                    <p class="text-sm font-semibold text-ink-primary truncate max-w-[10rem]">{{ user.name }}</p>
                    <p class="text-[11px] text-ink-muted truncate max-w-[10rem]">{{ user.email }}</p>
                </div>
            </div>
            <!-- Logout -->
            <Link href="/logout" method="post" as="button" class="w-10 h-10 rounded-pill flex items-center justify-center text-ink-secondary hover:bg-semantic-danger-soft hover:text-semantic-danger transition" title="Keluar">
                <Icon name="lock" size="5" />
            </Link>
        </div>
    </header>
</template>
