<script setup>
import { ref } from "vue";

const props = defineProps({
    productName: String,
});

const emit = defineEmits(["confirm", "close"]);

const COMMON_REASONS = [
    "Customer batal ambil",
    "Salah scan / salah input",
    "Barang rusak / tidak ada",
    "Salah harga",
    "Lainnya",
];

const selectedReason = ref("");
const customReason = ref("");

const handleSubmit = () => {
    const finalReason =
        selectedReason.value === "Lainnya"
            ? customReason.value
            : selectedReason.value;
    if (!finalReason?.trim()) return;
    emit("confirm", finalReason.trim());
};
</script>

<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div
            class="absolute inset-0 bg-ink-primary/60 backdrop-blur-sm"
            @click="emit('close')"
        />
        <div
            class="relative bg-surface-card rounded-2xl shadow-2xl p-6 max-w-md w-full animate-slide-up"
        >
            <div class="flex items-center gap-3 mb-4">
                <div
                    class="w-10 h-10 rounded-full bg-semantic-danger-soft flex items-center justify-center"
                >
                    <span class="text-xl">⚠️</span>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-bold text-ink-primary">
                        Void Item
                    </h3>
                    <p class="text-xs text-ink-muted truncate">
                        {{ productName }}
                    </p>
                </div>
            </div>

            <p class="text-sm text-ink-secondary mb-4">
                Pilih alasan void. Tindakan ini akan dicatat untuk audit.
            </p>

            <div class="space-y-2 mb-4">
                <button
                    v-for="r in COMMON_REASONS"
                    :key="r"
                    @click="selectedReason = r"
                    :class="[
                        'w-full text-left px-3 py-2.5 rounded-lg text-sm transition border-2',
                        selectedReason === r
                            ? 'bg-brand-soft border-brand text-brand font-semibold'
                            : 'bg-surface-muted border-transparent text-ink-primary hover:bg-border-soft',
                    ]"
                >
                    {{ r }}
                </button>
            </div>

            <input
                v-if="selectedReason === 'Lainnya'"
                v-model="customReason"
                type="text"
                placeholder="Tulis alasan..."
                class="w-full h-10 px-3 rounded-lg border border-border-soft bg-surface-main text-sm mb-4 focus:ring-2 focus:ring-brand"
                autofocus
            />

            <div class="flex gap-2">
                <button
                    @click="emit('close')"
                    class="flex-1 py-2.5 rounded-xl border border-border-soft text-sm font-medium text-ink-primary hover:bg-surface-muted"
                >
                    Batal
                </button>
                <button
                    @click="handleSubmit"
                    :disabled="
                        !selectedReason ||
                        (selectedReason === 'Lainnya' && !customReason.trim())
                    "
                    class="flex-1 py-2.5 rounded-xl bg-semantic-danger text-white text-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed hover:opacity-90"
                >
                    Void Item
                </button>
            </div>
        </div>
    </div>
</template>
