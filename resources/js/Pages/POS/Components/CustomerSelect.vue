<script setup>
import { ref, computed } from "vue";

const props = defineProps({
    customers: Array,
    selected: Object,
    placeholder: { type: String, default: "Pilih pelanggan..." },
    tierOptions: Array,
    error: String,
});

const emit = defineEmits(["select"]);
const query = ref("");
const isOpen = ref(false);

const filtered = computed(() => {
    const q = query.value.toLowerCase();
    return (props.customers || [])
        .filter(
            (c) =>
                !q || c.name?.toLowerCase().includes(q) || c.phone?.includes(q),
        )
        .slice(0, 8);
});

const select = (c) => {
    emit("select", c);
    query.value = c.name;
    isOpen.value = false;
};

const clear = () => {
    emit("select", null);
    query.value = "";
};
</script>

<template>
    <div class="relative">
        <label class="block text-xs font-medium text-ink-secondary mb-1"
            >Pelanggan</label
        >
        <div class="relative">
            <input
                v-model="query"
                @focus="isOpen = true"
                @blur="setTimeout(() => (isOpen = false), 150)"
                type="text"
                :placeholder="placeholder"
                class="w-full h-10 pl-10 pr-10 rounded-xl border border-border-soft bg-surface-card text-sm focus:ring-2 focus:ring-brand"
            />
            <span
                class="absolute left-3 top-1/2 -translate-y-1/2 text-ink-muted"
                >👤</span
            >
            <button
                v-if="selected"
                @mousedown.prevent="clear"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-ink-muted hover:text-semantic-danger"
            >
                ×
            </button>
        </div>

        <div
            v-if="isOpen && !selected"
            class="absolute z-40 mt-1 w-full bg-surface-card border border-border-soft rounded-xl shadow-floating max-h-60 overflow-y-auto"
        >
            <div
                v-for="c in filtered"
                :key="c.id"
                @mousedown.prevent="select(c)"
                class="px-3 py-2 hover:bg-surface-muted cursor-pointer text-sm"
            >
                <p class="font-semibold text-ink-primary">{{ c.name }}</p>
                <p class="text-xs text-ink-muted">
                    {{ c.phone }} · Tier {{ c.loyalty_tier || "-" }}
                </p>
            </div>
            <div
                v-if="!filtered.length"
                class="px-3 py-4 text-center text-sm text-ink-muted"
            >
                Tidak ada pelanggan ditemukan
            </div>
        </div>

        <p v-if="error" class="mt-1 text-xs text-semantic-danger">
            {{ error }}
        </p>
    </div>
</template>
