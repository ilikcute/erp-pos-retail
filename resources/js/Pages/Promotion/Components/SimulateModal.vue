<script setup>
import { ref, watch, computed } from 'vue';
import axios from 'axios';
import { formatCurrency } from '@/Utils/formatters';

const props = defineProps({
    show: { type: Boolean, default: false },
});

const emit = defineEmits(['close']);

// ─── STATE ────────────────────────────────────────────────────────────────────
const isSimulating = ref(false);
const customerId   = ref('');
const items        = ref([
    { product_variant_id: 1, qty: 1, unit_price: 150000, name: 'Kemeja Flanel (Sample)' } // Default sample
]);
const result       = ref(null);
const errorMsg     = ref(null);

// Reset saat dibuka
watch(() => props.show, (val) => {
    if (val) {
        result.value = null;
        errorMsg.value = null;
    }
});

// ─── HELPERS ─────────────────────────────────────────────────────────────────
const addItem = () => {
    items.value.push({ product_variant_id: '', qty: 1, unit_price: 0, name: '' });
};

const removeItem = (index) => {
    items.value.splice(index, 1);
};

const totalSimulatedCart = computed(() => {
    return items.value.reduce((acc, item) => acc + (item.qty * item.unit_price), 0);
});

// ─── ACTION ──────────────────────────────────────────────────────────────────
const simulate = async () => {
    if (items.value.length === 0) return;

    isSimulating.value = true;
    errorMsg.value = null;
    result.value = null;

    try {
        const payload = {
            customer_id: customerId.value ? Number(customerId.value) : null,
            items: items.value.map(i => ({
                product_variant_id: Number(i.product_variant_id),
                qty: Number(i.qty),
                unit_price: Number(i.unit_price),
            }))
        };

        const res = await axios.post(route('promotions.simulate'), payload);
        result.value = res.data.data;
    } catch (err) {
        if (err.response?.status === 422) {
            errorMsg.value = 'Data yang diisi tidak valid (Cek ID Varian).';
        } else {
            errorMsg.value = err.response?.data?.message || 'Gagal melakukan simulasi.';
        }
    } finally {
        isSimulating.value = false;
    }
};
</script>

<template>
    <Teleport to="body">
        <Transition name="modal">
            <div v-if="show" class="fixed inset-0 z-50 flex items-start justify-center pt-10 pb-4 px-4 overflow-y-auto">
                <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="$emit('close')"></div>

                <div class="relative w-full max-w-2xl rounded-2xl bg-surface-card border border-border-soft shadow-2xl overflow-hidden">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-brand to-brand-hover px-6 py-4 flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-xl">🧮</div>
                            <div>
                                <h2 class="text-lg font-bold text-white">Simulasi Promosi</h2>
                                <p class="text-xs text-white/80">Test engine promosi aktif saat ini</p>
                            </div>
                        </div>
                        <button @click="$emit('close')" class="text-white/70 hover:text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="p-6">
                        <!-- Input Section -->
                        <div class="mb-6 grid grid-cols-1 gap-4">
                            <div>
                                <label class="label-field text-xs">Customer ID (Opsional)</label>
                                <input v-model="customerId" type="number" class="input-field max-w-[200px]" placeholder="Contoh: 1" />
                                <p class="text-xs text-ink-muted mt-1">Isi jika ingin test promo khusus member tertentu.</p>
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="label-field text-xs m-0">Keranjang Belanja</label>
                                    <button @click="addItem" class="text-xs text-brand font-semibold hover:underline">+ Tambah Item</button>
                                </div>
                                <div class="space-y-2">
                                    <div v-for="(item, idx) in items" :key="idx" class="flex gap-2 items-center bg-surface-main border border-border-soft p-2 rounded-lg">
                                        <div class="flex-1 grid grid-cols-3 gap-2">
                                            <input v-model.number="item.product_variant_id" type="number" placeholder="ID Varian (misal: 1)" class="input-field text-xs" />
                                            <input v-model.number="item.unit_price" type="number" placeholder="Harga (Rp)" class="input-field text-xs" />
                                            <input v-model.number="item.qty" type="number" placeholder="Qty" class="input-field text-xs" />
                                        </div>
                                        <button v-if="items.length > 1" @click="removeItem(idx)" class="w-8 h-8 flex items-center justify-center bg-semantic-danger/10 text-semantic-danger rounded hover:bg-semantic-danger hover:text-white">×</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action -->
                        <div class="flex items-center justify-between pt-4 border-t border-border-soft mb-6">
                            <div class="text-sm font-semibold text-ink-primary">
                                Total Keranjang: <span class="text-brand">{{ formatCurrency(totalSimulatedCart) }}</span>
                            </div>
                            <button @click="simulate" :disabled="isSimulating || items.length === 0" class="btn-primary">
                                <span v-if="isSimulating">Menghitung...</span>
                                <span v-else>Jalankan Simulasi</span>
                            </button>
                        </div>

                        <!-- Error Message -->
                        <div v-if="errorMsg" class="mb-4 p-3 bg-semantic-danger/10 text-semantic-danger text-sm rounded-lg border border-semantic-danger/20">
                            {{ errorMsg }}
                        </div>

                        <!-- Result Section -->
                        <div v-if="result" class="bg-brand-soft border border-brand/20 rounded-xl p-5 animate-fade-in">
                            <h3 class="text-sm font-bold text-brand uppercase tracking-wider mb-4 border-b border-brand/10 pb-2">Hasil Evaluasi</h3>
                            
                            <div class="space-y-4">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-ink-secondary">Subtotal</span>
                                    <span class="font-semibold">{{ formatCurrency(result.subtotal) }}</span>
                                </div>

                                <div v-if="result.applied_promotions.length > 0" class="space-y-2">
                                    <div class="text-xs font-semibold text-ink-muted">Promosi Terapan:</div>
                                    <div v-for="promo in result.applied_promotions" :key="promo.promotion_code" 
                                         class="bg-white p-3 rounded-lg border border-brand/10 shadow-sm flex justify-between items-center">
                                        <div>
                                            <div class="font-semibold text-brand text-sm">{{ promo.promotion_name }}</div>
                                            <div class="text-xs text-ink-secondary mt-0.5">{{ promo.promotion_code }}</div>
                                        </div>
                                        <div class="font-bold text-semantic-success text-sm">
                                            -{{ formatCurrency(promo.discount_amount) }}
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="text-sm text-ink-muted italic py-2 text-center bg-white rounded-lg border border-brand/10">
                                    Tidak ada promosi yang aktif & memenuhi syarat untuk keranjang ini.
                                </div>

                                <div class="flex justify-between items-center text-sm border-t border-brand/10 pt-4">
                                    <span class="text-ink-secondary">Total Diskon</span>
                                    <span class="font-semibold text-semantic-success">-{{ formatCurrency(result.total_discount) }}</span>
                                </div>
                                <div class="flex justify-between items-center text-lg font-bold border-t border-brand/20 pt-3">
                                    <span class="text-ink-primary">Grand Total</span>
                                    <span class="text-brand">{{ formatCurrency(result.grand_total) }}</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.label-field { @apply block font-semibold text-ink-primary mb-1.5; }
.input-field  { @apply w-full h-10 rounded-xl border border-border-soft bg-surface-main px-3 text-ink-primary focus:ring-2 focus:ring-brand focus:border-brand outline-none transition; }
.btn-primary  { @apply px-5 h-10 rounded-xl text-sm font-bold text-white bg-brand hover:bg-brand-hover shadow-lg shadow-brand/30 transition disabled:opacity-50 flex items-center justify-center; }

.modal-enter-active, .modal-leave-active { transition: all 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; transform: translateY(-20px); }
</style>
