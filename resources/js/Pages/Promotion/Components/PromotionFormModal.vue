<script setup>
import { ref, watch, computed } from 'vue';
import axios from 'axios';
import toast from '@/Utils/toast';

const props = defineProps({
    show:      { type: Boolean, default: false },
    promotion: { type: Object,  default: null },  // null = create mode
});

const emit = defineEmits(['close', 'saved']);

// ─── CONSTANTS ────────────────────────────────────────────────────────────────
const CONDITION_TYPES = [
    { value: 'MIN_AMOUNT',         label: 'Minimum Jumlah Belanja' },
    { value: 'MIN_QTY',            label: 'Minimum Jumlah Item' },
    { value: 'DAY_OF_WEEK',        label: 'Hari Tertentu' },
    { value: 'CUSTOMER_CATEGORY',  label: 'Kategori Pelanggan' },
    { value: 'PRODUCT',            label: 'Produk Tertentu' },
    { value: 'CATEGORY',           label: 'Kategori Produk' },
];

const REWARD_TYPES = [
    { value: 'PERCENTAGE',    label: 'Diskon Persentase (%)' },
    { value: 'FIXED_AMOUNT',  label: 'Potongan Harga Tetap (Rp)' },
    { value: 'FREE_PRODUCT',  label: 'Produk Gratis' },
    { value: 'SPECIAL_PRICE', label: 'Harga Spesial' },
];

const TARGET_TYPES = [
    { value: 'ALL_PRODUCT', label: 'Semua Produk' },
    { value: 'PRODUCT',     label: 'Produk Tertentu' },
    { value: 'CATEGORY',    label: 'Kategori Tertentu' },
];

const OPERATORS = ['>=', '<=', '=', '>', '<'];

// ─── STATE ────────────────────────────────────────────────────────────────────
const isSubmitting = ref(false);
const errors       = ref({});

const defaultForm = () => ({
    promotion_code:        '',
    promotion_name:        '',
    description:           '',
    priority:              0,
    stackable:             false,
    valid_from:            '',
    valid_until:           '',
    earn_point_allowed:    true,
    redeem_point_allowed:  true,
    conditions: [],
    rewards: [
        { reward_type: 'PERCENTAGE', reward_value: 0, max_discount: '' },
    ],
    targets: [
        { target_type: 'ALL_PRODUCT', target_id: null },
    ],
    limits: {
        max_usage:              '',
        max_usage_per_customer: '',
    },
});

const form = ref(defaultForm());

// ─── WATCH: reset/prefill saat modal dibuka ───────────────────────────────────
watch(() => props.show, (val) => {
    if (!val) return;
    errors.value = {};

    if (props.promotion) {
        // Edit mode — prefill
        const p = props.promotion;
        form.value = {
            promotion_code:       p.promotion_code       ?? '',
            promotion_name:       p.promotion_name       ?? '',
            description:          p.description          ?? '',
            priority:             p.priority             ?? 0,
            stackable:            p.stackable            ?? false,
            valid_from:           p.valid_from ? p.valid_from.substring(0, 16) : '',
            valid_until:          p.valid_until ? p.valid_until.substring(0, 16) : '',
            earn_point_allowed:   p.earn_point_allowed   ?? true,
            redeem_point_allowed: p.redeem_point_allowed ?? true,
            conditions: (p.conditions ?? []).map(c => ({
                condition_type:  c.condition_type,
                operator:        c.operator ?? '>=',
                condition_value: c.condition_value ?? '',
            })),
            rewards: (p.rewards ?? []).map(r => ({
                reward_type:  r.reward_type,
                reward_value: r.reward_value,
                max_discount: r.max_discount ?? '',
            })),
            targets: (p.targets ?? []).map(t => ({
                target_type: t.target_type,
                target_id:   t.target_id ?? null,
            })),
            limits: {
                max_usage:              p.limits?.max_usage              ?? '',
                max_usage_per_customer: p.limits?.max_usage_per_customer ?? '',
            },
        };
    } else {
        form.value = defaultForm();
    }
});

// ─── HELPERS ─────────────────────────────────────────────────────────────────
const isEdit = computed(() => !!props.promotion);

const addCondition = () => {
    form.value.conditions.push({ condition_type: 'MIN_AMOUNT', operator: '>=', condition_value: '' });
};
const removeCondition = (i) => form.value.conditions.splice(i, 1);

const addReward = () => {
    form.value.rewards.push({ reward_type: 'PERCENTAGE', reward_value: 0, max_discount: '' });
};
const removeReward = (i) => form.value.rewards.splice(i, 1);

const addTarget = () => {
    form.value.targets.push({ target_type: 'ALL_PRODUCT', target_id: null });
};
const removeTarget = (i) => form.value.targets.splice(i, 1);

// ─── SUBMIT ──────────────────────────────────────────────────────────────────
const submit = async () => {
    isSubmitting.value = true;
    errors.value = {};

    const payload = {
        ...form.value,
        limits: {
            max_usage:              form.value.limits.max_usage              ? Number(form.value.limits.max_usage)              : null,
            max_usage_per_customer: form.value.limits.max_usage_per_customer ? Number(form.value.limits.max_usage_per_customer) : null,
        },
    };

    try {
        let res;
        if (isEdit.value) {
            res = await axios.put(route('promotions.update', props.promotion.id), payload);
        } else {
            res = await axios.post(route('promotions.store'), payload);
        }

        toast.success(res.data.message || 'Promosi berhasil disimpan');
        emit('saved', res.data.data);
        emit('close');
    } catch (err) {
        if (err.response?.status === 422) {
            errors.value = err.response.data.errors ?? {};
            toast.error('Periksa kembali data yang diisi');
        } else {
            toast.error(err.response?.data?.message || 'Gagal menyimpan promosi');
        }
    } finally {
        isSubmitting.value = false;
    }
};
</script>

<template>
    <Teleport to="body">
        <Transition name="modal">
            <div v-if="show" class="fixed inset-0 z-50 flex items-start justify-center pt-4 pb-4 px-4 overflow-y-auto">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="$emit('close')" />

                <!-- Panel -->
                <div class="relative w-full max-w-3xl rounded-2xl bg-surface-card border border-border-soft shadow-2xl overflow-hidden my-auto">

                    <!-- Header -->
                    <div class="bg-gradient-to-r from-brand to-brand-hover px-6 py-5 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-xl">🎁</div>
                            <div>
                                <h2 class="text-lg font-bold text-white">
                                    {{ isEdit ? 'Edit Promosi' : 'Buat Promosi Baru' }}
                                </h2>
                                <p class="text-sm text-white/70">Isi detail promosi dengan lengkap</p>
                            </div>
                        </div>
                        <button @click="$emit('close')" class="text-white/70 hover:text-white transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-6 space-y-6 max-h-[75vh] overflow-y-auto">

                        <!-- ── INFO DASAR ── -->
                        <section>
                            <h3 class="text-sm font-bold text-ink-primary uppercase tracking-wider mb-4 flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-brand text-white text-xs flex items-center justify-center">1</span>
                                Informasi Dasar
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="label-field">Kode Promosi <span class="text-semantic-danger">*</span></label>
                                    <input v-model="form.promotion_code" type="text" placeholder="PROMO-AGUSTUS" class="input-field" :class="{ 'border-semantic-danger': errors.promotion_code }" />
                                    <p v-if="errors.promotion_code" class="error-msg">{{ errors.promotion_code[0] }}</p>
                                </div>
                                <div>
                                    <label class="label-field">Nama Promosi <span class="text-semantic-danger">*</span></label>
                                    <input v-model="form.promotion_name" type="text" placeholder="Diskon Kemerdekaan 17%" class="input-field" :class="{ 'border-semantic-danger': errors.promotion_name }" />
                                    <p v-if="errors.promotion_name" class="error-msg">{{ errors.promotion_name[0] }}</p>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="label-field">Deskripsi</label>
                                    <textarea v-model="form.description" rows="2" placeholder="Keterangan singkat promosi..." class="input-field resize-none" />
                                </div>
                                <div>
                                    <label class="label-field">Berlaku Dari <span class="text-semantic-danger">*</span></label>
                                    <input v-model="form.valid_from" type="datetime-local" class="input-field" />
                                    <p v-if="errors.valid_from" class="error-msg">{{ errors.valid_from[0] }}</p>
                                </div>
                                <div>
                                    <label class="label-field">Berlaku Hingga <span class="text-semantic-danger">*</span></label>
                                    <input v-model="form.valid_until" type="datetime-local" class="input-field" />
                                    <p v-if="errors.valid_until" class="error-msg">{{ errors.valid_until[0] }}</p>
                                </div>
                                <div>
                                    <label class="label-field">Prioritas</label>
                                    <input v-model.number="form.priority" type="number" min="0" class="input-field" />
                                </div>
                                <div class="flex flex-col gap-3 pt-1">
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input v-model="form.stackable" type="checkbox" class="w-4 h-4 rounded accent-brand" />
                                        <span class="text-sm text-ink-primary">Dapat digabung (Stackable)</span>
                                    </label>
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input v-model="form.earn_point_allowed" type="checkbox" class="w-4 h-4 rounded accent-brand" />
                                        <span class="text-sm text-ink-primary">Boleh kumpulkan poin</span>
                                    </label>
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input v-model="form.redeem_point_allowed" type="checkbox" class="w-4 h-4 rounded accent-brand" />
                                        <span class="text-sm text-ink-primary">Boleh tukar poin</span>
                                    </label>
                                </div>
                            </div>
                        </section>

                        <hr class="border-border-soft" />

                        <!-- ── REWARDS ── -->
                        <section>
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-sm font-bold text-ink-primary uppercase tracking-wider flex items-center gap-2">
                                    <span class="w-5 h-5 rounded-full bg-brand text-white text-xs flex items-center justify-center">2</span>
                                    Reward / Diskon
                                </h3>
                                <button @click="addReward" type="button" class="btn-add">+ Tambah</button>
                            </div>
                            <div class="space-y-3">
                                <div v-for="(reward, i) in form.rewards" :key="i"
                                     class="flex gap-3 items-start p-3 rounded-xl bg-surface-main border border-border-soft">
                                    <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-3">
                                        <div>
                                            <label class="label-field text-xs">Tipe Reward</label>
                                            <select v-model="reward.reward_type" class="input-field">
                                                <option v-for="t in REWARD_TYPES" :key="t.value" :value="t.value">{{ t.label }}</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="label-field text-xs">Nilai</label>
                                            <input v-model.number="reward.reward_value" type="number" min="0" class="input-field"
                                                   :placeholder="reward.reward_type === 'PERCENTAGE' ? '17' : '50000'" />
                                        </div>
                                        <div>
                                            <label class="label-field text-xs">Maks. Diskon (Rp)</label>
                                            <input v-model="reward.max_discount" type="number" min="0" class="input-field" placeholder="Opsional" />
                                        </div>
                                    </div>
                                    <button v-if="form.rewards.length > 1" @click="removeReward(i)" type="button" class="btn-remove mt-5">×</button>
                                </div>
                            </div>
                            <p v-if="errors['rewards']" class="error-msg mt-1">{{ errors['rewards'][0] }}</p>
                        </section>

                        <hr class="border-border-soft" />

                        <!-- ── CONDITIONS ── -->
                        <section>
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-sm font-bold text-ink-primary uppercase tracking-wider flex items-center gap-2">
                                    <span class="w-5 h-5 rounded-full bg-brand text-white text-xs flex items-center justify-center">3</span>
                                    Syarat / Kondisi
                                    <span class="text-xs font-normal text-ink-muted">(opsional)</span>
                                </h3>
                                <button @click="addCondition" type="button" class="btn-add">+ Tambah</button>
                            </div>
                            <div v-if="form.conditions.length === 0" class="text-sm text-ink-muted italic py-2">
                                Tidak ada kondisi — promosi berlaku untuk semua transaksi.
                            </div>
                            <div class="space-y-3">
                                <div v-for="(cond, i) in form.conditions" :key="i"
                                     class="flex gap-3 items-start p-3 rounded-xl bg-surface-main border border-border-soft">
                                    <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-3">
                                        <div>
                                            <label class="label-field text-xs">Tipe Kondisi</label>
                                            <select v-model="cond.condition_type" class="input-field">
                                                <option v-for="t in CONDITION_TYPES" :key="t.value" :value="t.value">{{ t.label }}</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="label-field text-xs">Operator</label>
                                            <select v-model="cond.operator" class="input-field">
                                                <option v-for="op in OPERATORS" :key="op" :value="op">{{ op }}</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="label-field text-xs">Nilai</label>
                                            <input v-model="cond.condition_value" type="text" class="input-field"
                                                   :placeholder="cond.condition_type === 'MIN_AMOUNT' ? '200000' : '2'" />
                                        </div>
                                    </div>
                                    <button @click="removeCondition(i)" type="button" class="btn-remove mt-5">×</button>
                                </div>
                            </div>
                        </section>

                        <hr class="border-border-soft" />

                        <!-- ── TARGETS ── -->
                        <section>
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-sm font-bold text-ink-primary uppercase tracking-wider flex items-center gap-2">
                                    <span class="w-5 h-5 rounded-full bg-brand text-white text-xs flex items-center justify-center">4</span>
                                    Target Produk
                                </h3>
                                <button @click="addTarget" type="button" class="btn-add">+ Tambah</button>
                            </div>
                            <div class="space-y-3">
                                <div v-for="(target, i) in form.targets" :key="i"
                                     class="flex gap-3 items-start p-3 rounded-xl bg-surface-main border border-border-soft">
                                    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div>
                                            <label class="label-field text-xs">Target Tipe</label>
                                            <select v-model="target.target_type" class="input-field">
                                                <option v-for="t in TARGET_TYPES" :key="t.value" :value="t.value">{{ t.label }}</option>
                                            </select>
                                        </div>
                                        <div v-if="target.target_type !== 'ALL_PRODUCT'">
                                            <label class="label-field text-xs">ID Produk / Kategori</label>
                                            <input v-model.number="target.target_id" type="number" class="input-field" placeholder="ID" />
                                        </div>
                                    </div>
                                    <button v-if="form.targets.length > 1" @click="removeTarget(i)" type="button" class="btn-remove mt-5">×</button>
                                </div>
                            </div>
                        </section>

                        <hr class="border-border-soft" />

                        <!-- ── LIMITS ── -->
                        <section>
                            <h3 class="text-sm font-bold text-ink-primary uppercase tracking-wider mb-4 flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-brand text-white text-xs flex items-center justify-center">5</span>
                                Batas Penggunaan
                                <span class="text-xs font-normal text-ink-muted">(opsional)</span>
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="label-field">Maks. Total Penggunaan</label>
                                    <input v-model="form.limits.max_usage" type="number" min="0" class="input-field" placeholder="Tidak terbatas" />
                                </div>
                                <div>
                                    <label class="label-field">Maks. Per Pelanggan</label>
                                    <input v-model="form.limits.max_usage_per_customer" type="number" min="0" class="input-field" placeholder="Tidak terbatas" />
                                </div>
                            </div>
                        </section>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 border-t border-border-soft flex justify-end gap-3 bg-surface-card">
                        <button @click="$emit('close')" type="button"
                                class="px-5 h-11 rounded-xl text-sm font-semibold bg-surface-muted text-ink-secondary border border-border-soft hover:bg-border-soft transition cursor-pointer">
                            Batal
                        </button>
                        <button @click="submit" :disabled="isSubmitting" type="button"
                                class="px-6 h-11 rounded-xl text-sm font-bold text-white bg-brand hover:bg-brand-hover shadow-lg shadow-brand/30 transition disabled:opacity-50 cursor-pointer flex items-center gap-2">
                            <svg v-if="isSubmitting" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            {{ isSubmitting ? 'Menyimpan...' : (isEdit ? 'Perbarui Promosi' : 'Buat Promosi') }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.label-field { @apply block text-sm font-semibold text-ink-primary mb-1.5; }
.input-field  { @apply w-full h-10 rounded-xl border border-border-soft bg-surface-main px-3 text-sm text-ink-primary focus:ring-2 focus:ring-brand focus:border-brand outline-none transition; }
textarea.input-field { @apply h-auto py-2; }
.error-msg    { @apply text-xs text-semantic-danger mt-1; }
.btn-add      { @apply text-xs font-semibold text-brand border border-brand/30 bg-brand-soft hover:bg-brand hover:text-white px-3 py-1.5 rounded-lg transition; }
.btn-remove   { @apply w-7 h-7 rounded-lg bg-semantic-danger/10 text-semantic-danger hover:bg-semantic-danger hover:text-white flex items-center justify-center text-lg font-bold transition cursor-pointer; }

.modal-enter-active, .modal-leave-active { transition: all 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; transform: scale(0.97); }
</style>
