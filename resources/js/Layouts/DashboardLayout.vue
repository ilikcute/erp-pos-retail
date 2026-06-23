<script setup>
import { ref } from "vue";
import { Link, usePage } from "@inertiajs/vue3";

const page = usePage();
const user = page.props.auth.user;

const navigation = [
    { name: "Dashboard", href: "/dashboard", icon: "🏠" },
    { name: "Roles", href: "/system/roles", icon: "🔐" },
    { name: "Users", href: "/system/users", icon: "👥" },
];

const isSidebarOpen = ref(true);
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex">
        <!-- Sidebar -->
        <aside
            :class="isSidebarOpen ? 'w-64' : 'w-20'"
            class="bg-slate-800 text-white transition-all duration-300 flex flex-col"
        >
            <div
                class="h-16 flex items-center justify-center border-b border-slate-700"
            >
                <h1 v-if="isSidebarOpen" class="text-xl font-bold">ERP POS</h1>
                <h1 v-else class="text-xl font-bold">E</h1>
            </div>

            <nav class="flex-1 py-4">
                <ul>
                    <li v-for="item in navigation" :key="item.name">
                        <Link
                            :href="item.href"
                            class="flex items-center px-6 py-3 hover:bg-slate-700 transition-colors"
                            :class="{
                                'bg-slate-700': page.url.startsWith(item.href),
                            }"
                        >
                            <span class="text-xl">{{ item.icon }}</span>
                            <span v-if="isSidebarOpen" class="ml-4">{{
                                item.name
                            }}</span>
                        </Link>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Topbar -->
            <header
                class="h-16 bg-white shadow-sm flex items-center justify-between px-6"
            >
                <button
                    @click="isSidebarOpen = !isSidebarOpen"
                    class="text-gray-600 focus:outline-none"
                >
                    ☰
                </button>

                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-700">{{ user.name }}</span>
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="text-sm text-red-600 hover:underline"
                    >
                        Logout
                    </Link>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-6 overflow-y-auto">
                <slot />
            </main>
        </div>
    </div>
</template>
