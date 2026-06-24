<script setup>
import { ref, computed } from "vue";
import { useForm, Head, Link } from "@inertiajs/vue3";
import DashboardLayout from "@/Layouts/DashboardLayout.vue";
import BaseButton from "@/Components/Base/BaseButton.vue";
import FormInput from "@/Components/Form/FormInput.vue";
import FormSelect from "@/Components/Form/FormSelect.vue";
import FormTextarea from "@/Components/Form/FormTextarea.vue";

const props = defineProps({
    brands: { type: Array, required: true },
    categories: { type: Array, required: true },
    units: { type: Array, required: true },
});

const form = useForm({
    product_code: "",
    product_name: "",
    product_type: "SIMPLE",
    brand_id: "",
    category_id: "",
    base_unit_id: "",
    description: "",
    short_description: "",
    track_stock: false,
    min_stock: 0,
    max_stock: 0,
    reorder_point: 0,
    is_active: true,
    is_sellable: true,
    is_purchasable: true,
    
    // For SIMPLE products
    default_variant: {
        sku: "",
        barcode: "",
        barcode_type: "EAN13",
        weight: 0,
        purchase_price: 0,
    },
    
    // For VARIANT products
    attributes: [],
    variants: [],
});

// Attribute local state
const attributes = ref([
    { attribute_name: "", values: [] }
]);
const newValueInputs = ref([""]);

function addAttribute() {
    attributes.value.push({ attribute_name: "", values: [] });
    newValueInputs.value.push("");
}

function removeAttribute(index) {
    attributes.value.splice(index, 1);
    newValueInputs.value.splice(index, 1);
}

function addAttributeValue(attrIdx) {
    const text = newValueInputs.value[attrIdx]?.trim();
    if (!text) return;
    
    if (!attributes.value[attrIdx].values.includes(text)) {
        attributes.value[attrIdx].values.push(text);
    }
    newValueInputs.value[attrIdx] = "";
}

function removeAttributeValue(attrIdx, valIdx) {
    attributes.value[attrIdx].values.splice(valIdx, 1);
}

// Cartesian helper
const cartesian = (arrays) => {
    return arrays.reduce((a, b) => a.flatMap(d => b.map(e => [d, e].flat())), [[]]);
};

function generateVariants() {
    const activeAttrs = attributes.value.filter(a => a.attribute_name.trim() && a.values.length > 0);
    if (activeAttrs.length === 0) {
        alert("Definisikan setidaknya satu atribut dengan nilai!");
        return;
    }
    
    const valuesArrays = activeAttrs.map(a => a.values);
    const combinations = cartesian(valuesArrays);
    
    form.attributes = activeAttrs.map(a => ({
        attribute_name: a.attribute_name.trim(),
        values: a.values
    }));
    
    form.variants = combinations.map(combo => {
        const comboArray = Array.isArray(combo) ? combo : [combo];
        const variantName = comboArray.join(" - ");
        const skuSuffix = comboArray.map(s => s.toString().toUpperCase().replace(/[^A-Z0-9]/g, '')).join("-");
        const sku = `${form.product_code}-${skuSuffix}`.substring(0, 100);
        
        const attributeValues = comboArray.map((val, idx) => ({
            attribute_name: activeAttrs[idx].attribute_name.trim(),
            value: val
        }));
        
        return {
            variant_name: variantName,
            sku: sku,
            barcode: "",
            barcode_type: "EAN13",
            weight: 0,
            purchase_price: 0,
            is_active: true,
            attribute_values: attributeValues
        };
    });
}

function submit() {
    // Inject default variant sku if empty for SIMPLE product
    if (form.product_type === 'SIMPLE') {
        if (!form.default_variant.sku) {
            form.default_variant.sku = form.product_code;
        }
    }
    
    form.post("/product/products");
}
</script>

<template>
    <Head title="Create Product" />

    <DashboardLayout>
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Create Product</h1>
                <p class="text-gray-600">Tambah master data produk baru</p>
            </div>
            <Link href="/product/products">
                <BaseButton variant="secondary">Back to List</BaseButton>
            </Link>
        </div>

        <form @submit.prevent="submit" class="space-y-6 max-w-5xl">
            <!-- Basic Product Info Card -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 space-y-4">
                <h2 class="text-lg font-bold text-gray-800 border-b pb-2">Informasi Produk Utama</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <FormInput
                        v-model="form.product_code"
                        label="Product Code"
                        :error="form.errors.product_code"
                        required
                        placeholder="e.g. PROD-0001"
                    />
                    <FormInput
                        v-model="form.product_name"
                        label="Product Name"
                        :error="form.errors.product_name"
                        required
                        placeholder="e.g. T-Shirt Polos"
                    />
                    <FormSelect
                        v-model="form.product_type"
                        label="Product Type"
                        :error="form.errors.product_type"
                        required
                        :options="[
                            { value: 'SIMPLE', label: 'Simple Product' },
                            { value: 'VARIANT', label: 'Variant Product' }
                        ]"
                    />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <FormSelect
                        v-model="form.category_id"
                        label="Category"
                        :error="form.errors.category_id"
                        required
                        :options="[
                            { value: '', label: '-- Select Category --' },
                            ...categories.map(c => ({ value: c.id, label: c.category_name }))
                        ]"
                    />
                    <FormSelect
                        v-model="form.brand_id"
                        label="Brand"
                        :error="form.errors.brand_id"
                        :options="[
                            { value: '', label: '-- Select Brand --' },
                            ...brands.map(b => ({ value: b.id, label: b.brand_name }))
                        ]"
                    />
                    <FormSelect
                        v-model="form.base_unit_id"
                        label="Base Unit"
                        :error="form.errors.base_unit_id"
                        required
                        :options="[
                            { value: '', label: '-- Select Unit --' },
                            ...units.map(u => ({ value: u.id, label: u.unit_name }))
                        ]"
                    />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <FormTextarea
                        v-model="form.description"
                        label="Description"
                        :error="form.errors.description"
                        placeholder="Detail produk..."
                    />
                    <FormInput
                        v-model="form.short_description"
                        label="Short Description"
                        :error="form.errors.short_description"
                        placeholder="Deskripsi singkat..."
                    />
                </div>

                <!-- Settings / Checkboxes -->
                <div class="border-t pt-4 grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="flex items-center">
                        <input
                            v-model="form.track_stock"
                            type="checkbox"
                            id="track_stock"
                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        />
                        <label for="track_stock" class="ml-2 text-sm font-semibold text-gray-700">Track Stock</label>
                    </div>
                    <div class="flex items-center">
                        <input
                            v-model="form.is_sellable"
                            type="checkbox"
                            id="is_sellable"
                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        />
                        <label for="is_sellable" class="ml-2 text-sm font-semibold text-gray-700">Can be Sold (Sellable)</label>
                    </div>
                    <div class="flex items-center">
                        <input
                            v-model="form.is_purchasable"
                            type="checkbox"
                            id="is_purchasable"
                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        />
                        <label for="is_purchasable" class="ml-2 text-sm font-semibold text-gray-700">Can be Purchased</label>
                    </div>
                    <div class="flex items-center">
                        <input
                            v-model="form.is_active"
                            type="checkbox"
                            id="is_active"
                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        />
                        <label for="is_active" class="ml-2 text-sm font-semibold text-gray-700">Active</label>
                    </div>
                </div>

                <!-- Stock Limits (Conditionally shown if track_stock) -->
                <div v-if="form.track_stock" class="grid grid-cols-1 md:grid-cols-3 gap-4 border-t pt-4">
                    <FormInput
                        v-model="form.min_stock"
                        type="number"
                        label="Min Stock"
                        :error="form.errors.min_stock"
                    />
                    <FormInput
                        v-model="form.max_stock"
                        type="number"
                        label="Max Stock"
                        :error="form.errors.max_stock"
                    />
                    <FormInput
                        v-model="form.reorder_point"
                        type="number"
                        label="Reorder Point"
                        :error="form.errors.reorder_point"
                    />
                </div>
            </div>

            <!-- Simple Product Default Variant Details -->
            <div v-if="form.product_type === 'SIMPLE'" class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 space-y-4">
                <h2 class="text-lg font-bold text-gray-800 border-b pb-2">Informasi Varian Utama</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <FormInput
                        v-model="form.default_variant.sku"
                        label="SKU (Leave blank to use product code)"
                        :error="form.errors['default_variant.sku']"
                    />
                    <FormInput
                        v-model="form.default_variant.barcode"
                        label="Barcode"
                        :error="form.errors['default_variant.barcode']"
                    />
                    <FormSelect
                        v-model="form.default_variant.barcode_type"
                        label="Barcode Type"
                        :error="form.errors['default_variant.barcode_type']"
                        :options="[
                            { value: 'EAN13', label: 'EAN-13' },
                            { value: 'EAN8', label: 'EAN-8' },
                            { value: 'QR', label: 'QR Code' },
                            { value: 'CODE128', label: 'Code 128' },
                            { value: 'CUSTOM', label: 'Custom' }
                        ]"
                    />
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <FormInput
                        v-model="form.default_variant.weight"
                        type="number"
                        label="Weight (gram)"
                        :error="form.errors['default_variant.weight']"
                    />
                    <FormInput
                        v-model="form.default_variant.purchase_price"
                        type="number"
                        label="Purchase Price (Rp)"
                        :error="form.errors['default_variant.purchase_price']"
                    />
                </div>
            </div>

            <!-- Variant Product Attributes Configuration -->
            <div v-if="form.product_type === 'VARIANT'" class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 space-y-4">
                <div class="flex justify-between items-center border-b pb-2">
                    <h2 class="text-lg font-bold text-gray-800">1. Konfigurasi Atribut Produk</h2>
                    <BaseButton type="button" variant="secondary" @click="addAttribute">+ Add Attribute</BaseButton>
                </div>

                <div class="space-y-4">
                    <div v-for="(attr, attrIdx) in attributes" :key="attrIdx" class="p-4 bg-gray-50 border rounded-lg space-y-3 relative">
                        <button
                            type="button"
                            @click="removeAttribute(attrIdx)"
                            class="absolute top-3 right-3 text-red-500 hover:text-red-700 font-semibold text-sm"
                        >
                            Delete
                        </button>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <FormInput
                                v-model="attr.attribute_name"
                                label="Nama Atribut (e.g. Warna, Ukuran)"
                                placeholder="Attribute Name"
                                required
                            />
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Nilai Atribut</label>
                                <div class="flex space-x-2">
                                    <input
                                        v-model="newValueInputs[attrIdx]"
                                        type="text"
                                        placeholder="Ketik nilai dan klik Add / tekan Enter"
                                        class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                        @keyup.enter.prevent="addAttributeValue(attrIdx)"
                                    />
                                    <BaseButton type="button" variant="secondary" @click="addAttributeValue(attrIdx)">Add</BaseButton>
                                </div>
                                <div class="flex flex-wrap gap-1.5 mt-2">
                                    <span
                                        v-for="(val, valIdx) in attr.values"
                                        :key="valIdx"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800"
                                    >
                                        {{ val }}
                                        <button
                                            type="button"
                                            @click="removeAttributeValue(attrIdx, valIdx)"
                                            class="ml-1.5 text-blue-500 hover:text-blue-700 font-bold text-sm"
                                        >
                                            &times;
                                        </button>
                                    </span>
                                    <span v-if="attr.values.length === 0" class="text-xs text-gray-400 italic">Belum ada nilai atribut</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-2 flex justify-end">
                    <BaseButton type="button" @click="generateVariants">Hasilkan Varian</BaseButton>
                </div>
            </div>

            <!-- Variant Product Combinations Table -->
            <div v-if="form.product_type === 'VARIANT' && form.variants.length > 0" class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 space-y-4">
                <h2 class="text-lg font-bold text-gray-800 border-b pb-2">2. Daftar Kombinasi Varian</h2>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Varian</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU *</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Barcode</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Berat (gr)</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga Beli</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Active</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="(v, vIdx) in form.variants" :key="vIdx">
                                <td class="px-4 py-2 text-sm font-semibold text-gray-800">
                                    {{ v.variant_name }}
                                </td>
                                <td class="px-4 py-2">
                                    <input
                                        v-model="v.sku"
                                        type="text"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm w-full font-mono"
                                        required
                                    />
                                    <p v-if="form.errors[`variants.${vIdx}.sku`]" class="text-red-500 text-xs mt-0.5">
                                        {{ form.errors[`variants.${vIdx}.sku`] }}
                                    </p>
                                </td>
                                <td class="px-4 py-2 col-span-2">
                                    <div class="flex space-x-1">
                                        <input
                                            v-model="v.barcode"
                                            type="text"
                                            placeholder="Barcode"
                                            class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm w-full"
                                        />
                                        <select
                                            v-model="v.barcode_type"
                                            class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-xs"
                                        >
                                            <option value="EAN13">EAN13</option>
                                            <option value="QR">QR</option>
                                            <option value="CODE128">C128</option>
                                        </select>
                                    </div>
                                    <p v-if="form.errors[`variants.${vIdx}.barcode`]" class="text-red-500 text-xs mt-0.5">
                                        {{ form.errors[`variants.${vIdx}.barcode`] }}
                                    </p>
                                </td>
                                <td class="px-4 py-2">
                                    <input
                                        v-model="v.weight"
                                        type="number"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm w-24"
                                    />
                                </td>
                                <td class="px-4 py-2">
                                    <input
                                        v-model="v.purchase_price"
                                        type="number"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm w-32"
                                    />
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <input
                                        v-model="v.is_active"
                                        type="checkbox"
                                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex justify-end space-x-3 bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                <Link href="/product/products">
                    <BaseButton type="button" variant="secondary">Cancel</BaseButton>
                </Link>
                <BaseButton type="submit" :loading="form.processing">Save Product</BaseButton>
            </div>
        </form>
    </DashboardLayout>
</template>
