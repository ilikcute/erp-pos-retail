<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { useTheme } from "@/Context/ThemeSwitcherContext";
import { useOnlineStatus } from "@/Context/OnlineStatusContext";

const { auth, storeProfile, activeCashierShift } = usePage().props;
const { darkMode, themeSwitcher } = useTheme();
const isOnline = useOnlineStatus();

const currentTime = ref(new Date());
const showMobileMenu = ref(false);

const formatTime = (date) =>
    date.toLocaleTimeString("id-ID", { hour: "2-digit", minute: "2-digit" });

const formatDate = (date) =>
    date.toLocaleDateString("id-ID", {
        weekday: "long",
        day: "numeric",
        month: "short",
    });

let timeInterval = null;
onMounted(() => {
    timeInterval = setInterval(() => (currentTime.value = new Date()), 60000);
});
onUnmounted(() => clearInterval(timeInterval));

const storeInitial = computed(() =>
    (storeProfile?.name || "K").charAt(0).toUpperCase(),
);
const formatCash = (n) => new Intl.NumberFormat("id-ID").format(Number(n || 0));
</script>

<template>
    <div class="min-h-screen flex flex-col bg-surface-app">
        <!-- Header - 4rem tinggi -->
        <header
            class="h-16 flex items-center justify-between px-4 bg-surface-card border-b border-border-soft flex-shrink-0"
        >
            <!-- Left -->
            <div class="flex items-center gap-4">
                <Link
                    :href="route('dashboard')"
                    class="flex items-center gap-2"
                >
                    <div
                        class="w-9 h-9 rounded-lg bg-brand text-white flex items-center justify-center font-bold"
                    >
                        {{ storeInitial }}
                    </div>
                    <span
                        class="hidden sm:block text-lg font-bold text-ink-primary"
                    >
                        {{ storeProfile?.name || "POS" }}
                    </span>
                </Link>

                <div class="hidden md:flex items-center gap-3">
                    <div
                        class="text-xl font-semibold text-ink-primary tabular-nums"
                    >
                        {{ formatTime(currentTime) }}
                    </div>
                    <div class="text-xs text-ink-muted">
                        {{ formatDate(currentTime) }}
                    </div>
                </div>
            </div>

            <!-- Right -->
            <div class="flex items-center gap-3">
                <button
                    @click="themeSwitcher"
                    class="p-2 rounded-lg hover:bg-surface-muted transition"
                    :title="darkMode ? 'Light Mode' : 'Dark Mode'"
                >
                    {{ darkMode ? "☀️" : "🌙" }}
                </button>

                <div
                    class="flex items-center gap-2 pl-3 border-l border-border-soft"
                >
                    <Link
                        v-if="activeCashierShift"
                        :href="route('pos.shifts.index')"
                        class="hidden lg:flex items-center gap-2 rounded-lg bg-accent-mint-soft px-3 py-1.5 text-xs font-semibold text-accent-mint"
                    >
                        💰 Shift aktif
                    </Link>
                    <p class="text-sm font-medium text-ink-primary">
                        {{ auth.user.name }}
                    </p>
                </div>
            </div>
        </header>

        <!-- Offline Indicator -->
        <div
            v-if="!isOnline"
            class="bg-accent-sunny text-white text-center text-xs font-medium py-1.5"
        >
            ⚠️ Offline mode — transaksi akan disinkronkan saat online
        </div>

        <!-- Main Content -->
        <main class="flex-1 overflow-hidden">
            <slot />
        </main>
    </div>
</template>
