<script setup>
import { ref } from "vue";
import { formatPrice } from "@/Utils/formatPrice";
import axios from "axios";

const activePromos = ref([]);
const currentIndex = ref(0);

onMounted(async () => {
    try {
        const { data } = await axios.get(route("pos.promotions.active"));
        if (data.success) {
            activePromos.value = data.data;
            // Auto slide setiap 5 detik
            if (activePromos.value.length > 1) {
                setInterval(() => {
                    currentIndex.value =
                        (currentIndex.value + 1) % activePromos.value.length;
                }, 5000);
            }
        }
    } catch (error) {
        console.error("Failed to load promos:", error);
    }
});

const currentPromo = computed(() => activePromos.value[currentIndex.value]);

import { onUnmounted } from "vue";
onUnmounted(() => {
    if (slideInterval) clearInterval(slideInterval);
});
</script>

<template>
    <div
        v-if="activePromos.length > 0"
        class="bg-gradient-to-r from-accent-mint-soft to-accent-sky-soft border-b border-border-soft px-4 py-2 flex items-center gap-3"
    >
        <span class="text-lg">🎉</span>
        <div class="flex-1 min-w-0">
            <transition name="fade" mode="out-in">
                <div :key="currentPromo?.id">
                    <p class="text-sm font-bold text-ink-primary truncate">
                        {{ currentPromo?.name }}
                    </p>
                    <p class="text-xs text-ink-muted">
                        {{ currentPromo?.rewards?.[0]?.label }} · s/d
                        {{ currentPromo?.valid_until }}
                    </p>
                </div>
            </transition>
        </div>
        <span v-if="activePromos.length > 1" class="text-xs text-ink-muted">
            {{ currentIndex + 1 }}/{{ activePromos.length }}
        </span>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
