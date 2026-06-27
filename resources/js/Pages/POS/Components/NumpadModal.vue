<script setup>
import { ref, watch } from 'vue';
import { formatPrice } from '@/Utils/formatPrice';
const props = defineProps({
    initialValue: { type: Number, default: 0 },
});
const emit = defineEmits(['confirm', 'close']);

const value = ref(String(props.initialValue || ''));

watch(() => props.initialValue, (v) => { value.value = String(v || ''); });

function tap(d) {
    if (d === '000') { value.value = (value.value || '0') + '000'; return; }
    value.value = (value.value === '0' ? '' : value.value) + d;
}
function backspace() { value.value = value.value.slice(0, -1); }
function clearAll() { value.value = ''; }
function confirm() { emit('confirm', Number(value.value) || 0); }

const keys = ['1','2','3','4','5','6','7','8','9','000','0','⌫'];
function onKey(k) {
    if (k === '⌫') backspace();
    else tap(k);
}
</script>

<template>
    <Teleport to="body">
        <div class="fixed inset-0 z-[140] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="emit('close')"></div>
            <div class="relative w-full max-w-xs rounded-2xl bg-surface-card shadow-2xl border border-border-soft p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-bold text-ink-primary">Input Nominal</h3>
                    <button class="text-ink-muted hover:text-ink-primary" @click="emit('close')">✕</button>
                </div>

                <div class="mb-3 rounded-xl bg-surface-app px-4 py-3 text-right text-2xl font-extrabold text-brand">
                    {{ formatPrice(Number(value) || 0) }}
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <button
                        v-for="k in keys" :key="k"
                        type="button"
                        @click="onKey(k)"
                        class="rounded-xl bg-surface-app py-3 text-lg font-bold text-ink-primary hover:bg-brand/10 active:scale-95 transition"
                    >{{ k }}</button>
                </div>

                <div class="grid grid-cols-2 gap-2 mt-3">
                    <button type="button" @click="clearAll" class="rounded-pill py-2.5 text-sm font-semibold bg-slate-200 text-ink-secondary hover:bg-slate-300">Clear</button>
                    <button type="button" @click="confirm" class="rounded-pill py-2.5 text-sm font-bold text-white bg-brand-gradient hover:opacity-90">OK</button>
                </div>
            </div>
        </div>
    </Teleport>
</template>
