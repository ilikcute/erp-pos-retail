<script setup>
import { ref, computed } from "vue";
import { useForm, Head, Link } from "@inertiajs/vue3";
import DashboardLayout from "@/Layouts/DashboardLayout.vue";
import BaseButton from "@/Components/Base/BaseButton.vue";
import FormInput from "@/Components/Form/FormInput.vue";
import FormSelect from "@/Components/Form/FormSelect.vue";
import FormTextarea from "@/Components/Form/FormTextarea.vue";

const props = defineProps({
    suppliers: { type: Array, required: true },
    variants: { type: Array, required: true },
    units: { type: Array, required: true },
});

const form = useForm({
    supplier_id: "",
    order_date: new Date().toISOString().split("T")[0],
    expected_date: "",
    remarks: "",
    items: [],
});

function addItem() {
    form.items.push({
        product_variant_id: "",
        ordered_qty: 1,
        unit_cost: 0,
        discount_amount: 0,
        tax_amount: 0,
        notes: "",
    });
}

function removeItem(index) {
    form.items.splice(index, 1);
}

function onVariantChange(index) {
    const selectedId = form.items[index].product_variant_id;
    const variant = props.variants.find(v => v.id === selectedId);
    if (variant) {
        form.items[index].unit_cost = parseFloat(variant.purchase_price) || 0;
    }
}

// Computeds for totals
const totals = computed(() => {
    let subtotal = 0;
    let discount = 0;
    let tax = 0;
    
    form.items.forEach(item => {
        const qty = parseFloat(item.ordered_qty) || 0;
        const cost = parseFloat(item.unit_cost) || 0;
        const disc = parseFloat(item.discount_amount) || 0;
        const tx = parseFloat(item.tax_amount) || 0;
        
        subtotal += qty * cost;
        discount += disc;
        tax += tx;
    });
    
    return {
        subtotal,
        discount,
        tax,
        grandTotal: subtotal - discount + tax
    };
});

function submit() {
    form.post("/purchasing", {
        onSuccess: () => {
            // Managed by Inertia redirection
        }
    });
}

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};
</script>

<template>
    <Head title="Create Purchase Order" />

    <DashboardLayout>
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-ink-primary">New Purchase Order 🛒</h1>
                <p class="text-ink-secondary text-sm">Buat dokumen pemesanan barang baru ke supplier.</p>
            </div>
            <Link href="/purchasing/po">
                <BaseButton variant="secondary">Cancel</BaseButton>
            </Link>
        </div>

        <form @submit.prevent="submit" class="space-y-xl">
            <!-- Form Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-xl bg-surface-card rounded-lg border border-border-soft p-xl shadow-soft">
                <div class="space-y-sm">
                    <label class="block text-sm font-semibold text-ink-primary">Supplier 🏢</label>
                    <FormSelect v-model="form.supplier_id" :error="form.errors.supplier_id" required>
                        <option value="">Pilih Supplier</option>
                        <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.supplier_name }}</option>
                    </FormSelect>
                </div>
                <div class="space-y-sm">
                    <label class="block text-sm font-semibold text-ink-primary">Order Date 📅</label>
                    <FormInput type="date" v-model="form.order_date" :error="form.errors.order_date" required />
                </div>
                <div class="space-y-sm">
                    <label class="block text-sm font-semibold text-ink-primary">Expected Delivery Date 🚚</label>
                    <FormInput type="date" v-model="form.expected_date" :error="form.errors.expected_date" required />
                </div>
                <div class="md:col-span-3 space-y-sm">
                    <label class="block text-sm font-semibold text-ink-primary">Remarks / Notes 📝</label>
                    <FormTextarea v-model="form.remarks" :error="form.errors.remarks" placeholder="Catatan tambahan..." rows="2" />
                </div>
            </div>

            <!-- Items Table Section -->
            <div class="bg-surface-card rounded-lg border border-border-soft p-xl shadow-soft space-y-md">
                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-bold text-ink-primary">Order Items 📦</h2>
                    <BaseButton type="button" @click="addItem" variant="secondary" size="sm">
                        ➕ Add Item
                    </BaseButton>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-ink-primary">
                        <thead class="bg-surface-subtle text-ink-secondary uppercase text-xs">
                            <tr>
                                <th class="p-md">Product / Variant</th>
                                <th class="p-md w-32 text-right">Qty</th>
                                <th class="p-md w-40 text-right">Unit Cost</th>
                                <th class="p-md w-36 text-right">Discount</th>
                                <th class="p-md w-36 text-right">Tax</th>
                                <th class="p-md w-40 text-right">Line Total</th>
                                <th class="p-md w-16 text-center"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-soft">
                            <tr v-for="(item, idx) in form.items" :key="idx">
                                <td class="p-md">
                                    <FormSelect v-model="item.product_variant_id" @change="onVariantChange(idx)" required>
                                        <option value="">Pilih Produk/Varian</option>
                                        <option v-for="v in variants" :key="v.id" :value="v.id">
                                            {{ v.product?.product_name }} - {{ v.variant_name || 'Default' }} ({{ v.sku }})
                                        </option>
                                    </FormSelect>
                                </td>
                                <td class="p-md">
                                    <FormInput type="number" step="any" v-model="item.ordered_qty" required class="text-right" />
                                </td>
                                <td class="p-md">
                                    <FormInput type="number" step="0.01" v-model="item.unit_cost" required class="text-right" />
                                </td>
                                <td class="p-md">
                                    <FormInput type="number" step="0.01" v-model="item.discount_amount" class="text-right" />
                                </td>
                                <td class="p-md">
                                    <FormInput type="number" step="0.01" v-model="item.tax_amount" class="text-right" />
                                </td>
                                <td class="p-md text-right font-mono font-semibold vertical-middle pt-5">
                                    {{ formatCurrency((item.ordered_qty * item.unit_cost) - (item.discount_amount || 0) + (item.tax_amount || 0)) }}
                                </td>
                                <td class="p-md text-center">
                                    <button type="button" @click="removeItem(idx)" class="text-semantic-danger hover:text-red-700 transition">
                                        ❌
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="form.items.length === 0">
                                <td colspan="7" class="p-xl text-center text-ink-secondary">
                                    Belum ada produk ditambahkan. Klik tombol "Add Item" untuk mulai.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Totals Section -->
                <div class="flex justify-end pt-md border-t border-border-soft">
                    <div class="w-80 space-y-sm text-sm">
                        <div class="flex justify-between">
                            <span class="text-ink-secondary">Subtotal</span>
                            <span class="font-mono font-medium">{{ formatCurrency(totals.subtotal) }}</span>
                        </div>
                        <div class="flex justify-between text-semantic-danger">
                            <span>Discount Total</span>
                            <span class="font-mono font-medium">-{{ formatCurrency(totals.discount) }}</span>
                        </div>
                        <div class="flex justify-between text-brand">
                            <span>Tax Total</span>
                            <span class="font-mono font-medium">+{{ formatCurrency(totals.tax) }}</span>
                        </div>
                        <div class="flex justify-between text-base font-bold border-t border-border-soft pt-sm">
                            <span class="text-ink-primary">Grand Total</span>
                            <span class="font-mono text-brand">{{ formatCurrency(totals.grandTotal) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end gap-md">
                <Link href="/purchasing/po">
                    <BaseButton type="button" variant="secondary">Cancel</BaseButton>
                </Link>
                <BaseButton type="submit" :loading="form.processing">
                    💾 Save Purchase Order
                </BaseButton>
            </div>
        </form>
    </DashboardLayout>
</template>
