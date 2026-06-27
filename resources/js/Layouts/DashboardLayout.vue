<script setup>
import { ref, computed, onMounted, onUnmounted, watch }  from 'vue';
import { usePage }  from '@inertiajs/vue3';
import Sidebar from '@/Components/Dashboard/Sidebar.vue';
import Navbar from '@/Components/Dashboard/Navbar.vue';
import Toaster from '@/Components/Dashboard/Toaster.vue';

const page = usePage();

// ---- responsive + persisted UI state (Shell Layout A) ----
const isMobile = ref(false);
const collapsed = ref(false);   // desktop mini-variant (w-56 / w-16)
const mobileOpen = ref(false);  // mobile off-canvas drawer

const STORAGE_KEY = "sidebarCollapsed";

const applyResponsive = () => {
    if (typeof window === "undefined") return;
    isMobile.value = window.innerWidth < 768;
    if (isMobile.value) mobileOpen.value = false;
};

// toggle: mobile -> open/close drawer; desktop -> collapse mini-variant
const toggleSidebar = () => {
    if (isMobile.value) {
        mobileOpen.value = !mobileOpen.value;
    } else {
        collapsed.value = !collapsed.value;
    }
};

// persist mini-variant state to localStorage (Layout A behavior + Layout B mini-variant)
watch(collapsed, (v) => {
    if (typeof window !== "undefined") localStorage.setItem(STORAGE_KEY, v ? "true" : "false");
});

onMounted(() => {
    if (typeof window !== "undefined") {
        const stored = localStorage.getItem(STORAGE_KEY);
        if (stored !== null) collapsed.value = stored === "true";
        applyResponsive();
        window.addEventListener("resize", applyResponsive);
    }
});
onUnmounted(() => {
    if (typeof window !== "undefined") window.removeEventListener("resize", applyResponsive);
});

// ---- security warnings (Layout A) ----
const securityWarnings = computed(() => page.props.security?.warnings ?? []);
const showSecurityWarnings = computed(() => page.props.auth?.super === true && securityWarnings.value.length > 0);

// main content margin follows desktop sidebar width; on mobile no offset
const contentMargin = computed(() => {
    if (isMobile.value) return "ml-0";
    return collapsed.value ? "ml-16" : "ml-56";
});
</script>

<template>
    <div class="min-h-screen flex bg-surface-main transition-colors duration-200">
        <!-- Sidebar (nav logic + mini-variant + accordion live inside) -->
        <Sidebar :collapsed="collapsed" :is-mobile="isMobile" :mobile-open="mobileOpen" @close="mobileOpen = false" />

        <!-- Mobile overlay (Layout A) -->
        <div
            :class="mobileOpen ? 'opacity-100 pointer-events-auto z-40' : 'opacity-0 pointer-events-none'"
            class="fixed inset-0 bg-ink-primary/40 md:hidden transition-opacity duration-300"
            @click="mobileOpen = false"
        ></div>

        <!-- Main column -->
        <div :class="contentMargin" class="flex-1 flex flex-col min-w-0 transition-all duration-200">
            <Navbar :collapsed="collapsed" :is-mobile="isMobile" @toggle="toggleSidebar" />

            <main class="dashboard-scrollbar flex-1 overflow-y-auto scroll-soft bg-surface-main">
                <div class="w-full py-6 px-4 md:px-6 lg:px-8 pb-20 md:pb-6">
                    <!-- Security baseline warnings (Layout A) -->
                    <div v-if="showSecurityWarnings" class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4 text-amber-800">
                        <p class="text-sm font-semibold">Production security baseline warning</p>
                        <ul class="mt-2 space-y-1 text-sm">
                            <li v-for="warning in securityWarnings" :key="warning.key">- {{ warning.message }}</li>
                        </ul>
                    </div>

                    <slot />
                </div>
            </main>
        </div>

        <!-- Toast notifications (Layout A) -->
        <Toaster />
    </div>
</template>