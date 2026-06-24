<script setup>
import { ref } from "vue";
import { useForm, Head, Link, router } from "@inertiajs/vue3";
import DashboardLayout from "@/Layouts/DashboardLayout.vue";
import BaseButton from "@/Components/Base/BaseButton.vue";
import FormInput from "@/Components/Form/FormInput.vue";
import FormSelect from "@/Components/Form/FormSelect.vue";
import FormTextarea from "@/Components/Form/FormTextarea.vue";
import BaseModal from "@/Components/Modal/BaseModal.vue";

const props = defineProps({
    product: { type: Object, required: true },
    brands: { type: Array, required: true },
    categories: { type: Array, required: true },
    units: { type: Array, required: true },
});

const form = useForm({
    product_name: props.product.product_name,
    brand_id: props.product.brand_id || "",
    category_id: props.product.category_id || "",
    base_unit_id: props.product.base_unit_id,
    description: props.product.description || "",
    short_description: props.product.short_description || "",
    track_stock: props.product.track_stock,
    min_stock: Number(props.product.min_stock) || 0,
    max_stock: Number(props.product.max_stock) || 0,
    reorder_point: Number(props.product.reorder_point) || 0,
    is_active: props.product.is_active,
    is_sellable: props.product.is_sellable,
    is_purchasable: props.product.is_purchasable,
});

function submitProduct() {
    form.put(`/product/products/${props.product.id}`);
}

// ════════════════════════════════════════════════════════════════
// VARIANT MANAGEMENT (FOR VARIANT PRODUCTS)
// ════════════════════════════════════════════════════════════════

// Edit Variant Modal
const showEditVariantModal = ref(false);
const editingVariant = ref(null);
const editVariantForm = useForm({
    sku: "",
    variant_name: "",
    barcode: "",
    barcode_type: "EAN13",
    weight: 0,
    purchase_price: 0,
    is_active: true,
});

function openEditVariantModal(variant) {
    editingVariant.value = variant;
    
    // Find primary barcode
    const primaryBc = variant.barcodes?.find(b => b.is_primary) || variant.barcodes?.[0];
    
    editVariantForm.sku = variant.sku;
    editVariantForm.variant_name = variant.variant_name;
    editVariantForm.barcode = primaryBc ? primaryBc.barcode : "";
    editVariantForm.barcode_type = primaryBc ? primaryBc.barcode_type : "EAN13";
    editVariantForm.weight = Number(variant.weight) || 0;
    editVariantForm.purchase_price = Number(variant.purchase_price) || 0;
    editVariantForm.is_active = variant.is_active;
    
    editVariantForm.clearErrors();
    showEditVariantModal.value = true;
}

function submitEditVariant() {
    editVariantForm.put(`/product/products/${props.product.id}/variants/${editingVariant.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showEditVariantModal.value = false;
        }
    });
}

function deleteVariant(variant) {
    if (!confirm(`Hapus varian "${variant.variant_name}"?`)) return;
    
    router.delete(`/product/products/${props.product.id}/variants/${variant.id}`, {
        preserveScroll: true,
    });
}

// Add Variant Modal
const showAddVariantModal = ref(false);
const selectedAttributes = ref({}); // Maps attribute_id -> attribute_value_id
const addVariantForm = useForm({
    sku: "",
    variant_name: "",
    barcode: "",
    barcode_type: "EAN13",
    weight: 0,
    purchase_price: 0,
    attributes: [], // Array of { attribute_id, attribute_value_id }
});

function openAddVariantModal() {
    selectedAttributes.value = {};
    
    // Pre-populate empty selection
    props.product.attributes.forEach(attr => {
        selectedAttributes.value[attr.id] = "";
    });
    
    addVariantForm.reset();
    
    // Auto variant name generation helper when attribute value selected
    addVariantForm.sku = "";
    addVariantForm.variant_name = "";
    addVariantForm.barcode = "";
    addVariantForm.barcode_type = "EAN13";
    addVariantForm.weight = 0;
    addVariantForm.purchase_price = 0;
    
    addVariantForm.clearErrors();
    showAddVariantModal.value = true;
}

function updateVariantNameAndSku() {
    // Generate name based on selected attribute values
    const names = [];
    const skuParts = [];
    
    let allSelected = true;
    
    props.product.attributes.forEach(attr => {
        const valId = selectedAttributes.value[attr.id];
        if (!valId) {
            allSelected = false;
            return;
        }
        const valObj = attr.values.find(v => v.id === Number(valId));
        if (valObj) {
            names.push(valObj.value);
            skuParts.push(valObj.value.toUpperCase().replace(/[^A-Z0-9]/g, ''));
        }
    });
    
    if (names.length > 0) {
        addVariantForm.variant_name = names.join(" - ");
        if (allSelected) {
            addVariantForm.sku = `${props.product.product_code}-${skuParts.join("-")}`;
        }
    }
}

function submitAddVariant() {
    // Map selected attributes to attributes array
    const mapped = [];
    let missing = false;
    
    props.product.attributes.forEach(attr => {
        const valId = selectedAttributes.value[attr.id];
        if (!valId) {
            missing = true;
            return;
        }
        mapped.push({
            attribute_id: attr.id,
            attribute_value_id: Number(valId)
        });
    });
    
    if (missing) {
        alert("Pilih nilai untuk semua atribut terlebih dahulu!");
        return;
    }
    
    addVariantForm.attributes = mapped;
    addVariantForm.post(`/product/products/${props.product.id}/variants`, {
        preserveScroll: true,
        onSuccess: () => {
            showAddVariantModal.value = false;
        }
    });
}
</script>

<template>
    <Head title="Edit Product" />

    <DashboardLayout>
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Edit Product</h1>
                <p class="text-gray-600">Perbarui master data produk dan varian</p>
            </div>
            <Link href="/product/products">
                <BaseButton variant="secondary">Back to List</BaseButton>
            </Link>
        </div>

        <div class="space-y-6 max-w-5xl">
            <!-- Basic Product Info Form -->
            <form @submit.prevent="submitProduct" class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 space-y-4">
                <h2 class="text-lg font-bold text-gray-800 border-b pb-2">Informasi Produk Utama</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Product Code</label>
                        <input
                            :value="product.product_code"
                            disabled
                            type="text"
                            class="w-full rounded-md border-gray-300 bg-gray-100 text-gray-500 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm font-mono"
                        />
                        <p class="text-xs text-gray-400 mt-1">Product code tidak dapat diubah</p>
                    </div>
                    <FormInput
                        v-model="form.product_name"
                        label="Product Name"
                        :error="form.errors.product_name"
                        required
                    />
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Product Type</label>
                        <span class="inline-flex items-center px-3 py-2 rounded-md border border-gray-300 bg-gray-100 text-gray-700 font-semibold text-sm w-full">
                            {{ product.product_type }}
                        </span>
                        <p class="text-xs text-gray-400 mt-1">Product type tidak dapat diubah</p>
                    </div>
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
                    />
                    <FormInput
                        v-model="form.short_description"
                        label="Short Description"
                        :error="form.errors.short_description"
                    />
                </div>

                <!-- Checkboxes -->
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
                        <label for="is_active" class="ml-2 text-sm font-semibold text-gray-700">Active Status</label>
                    </div>
                </div>

                <!-- Stock Limits -->
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

                <div class="flex justify-end pt-4">
                    <BaseButton type="submit" :loading="form.processing">Save Product Details</BaseButton>
                </div>
            </form>

            <!-- Varian Utama (For SIMPLE product) -->
            <div v-if="product.product_type === 'SIMPLE'" class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 space-y-4">
                <h2 class="text-lg font-bold text-gray-800 border-b pb-2">Informasi Varian Utama</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="block text-sm font-medium text-gray-500">SKU</span>
                        <span class="text-sm font-mono font-bold text-gray-800">{{ product.variants?.[0]?.sku || '-' }}</span>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-gray-500">Barcode</span>
                        <span class="text-sm text-gray-800">{{ product.variants?.[0]?.barcodes?.[0]?.barcode || '-' }}</span>
                    </div>
                </div>
                <p class="text-xs text-gray-400">Untuk mengedit SKU/barcode varian utama, gunakan modul penyesuaian SKU atau ubah di database.</p>
            </div>

            <!-- Varian Detail Table (For VARIANT product) -->
            <div v-if="product.product_type === 'VARIANT'" class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 space-y-4">
                <div class="flex justify-between items-center border-b pb-2">
                    <div>
                        <h2 class="text-lg font-bold text-gray-800">Daftar Varian Produk</h2>
                        <div class="flex gap-2 mt-1">
                            <span v-for="attr in product.attributes" :key="attr.id" class="text-xs bg-slate-100 text-slate-700 px-2 py-0.5 rounded-full font-semibold">
                                {{ attr.attribute_name }}: {{ attr.values.map(v => v.value).join(', ') }}
                            </span>
                        </div>
                    </div>
                    <BaseButton type="button" @click="openAddVariantModal">+ Add New Variant</BaseButton>
                </div>

                <div v-if="$page.props.errors.delete_variant" class="p-3 bg-red-100 border border-red-200 text-red-800 rounded text-sm">
                    {{ $page.props.errors.delete_variant }}
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Varian</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Barcode</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Berat (gr)</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Harga Beli</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="v in product.variants" :key="v.id" class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-semibold text-gray-800">
                                    {{ v.variant_name }}
                                </td>
                                <td class="px-4 py-3 text-sm font-mono text-gray-700">
                                    {{ v.sku }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    {{ v.barcodes?.find(b => b.is_primary)?.barcode || v.barcodes?.[0]?.barcode || '-' }}
                                    <span v-if="v.barcodes?.find(b => b.is_primary)?.barcode_type" class="text-xs text-gray-400">
                                        ({{ v.barcodes?.find(b => b.is_primary)?.barcode_type }})
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-gray-700">
                                    {{ Number(v.weight) || 0 }} gr
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-gray-700 font-medium">
                                    Rp {{ Number(v.purchase_price).toLocaleString('id-ID') }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        :class="v.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                        class="px-2 py-0.5 rounded-full text-xs font-semibold"
                                    >
                                        {{ v.is_active ? "Active" : "Inactive" }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center text-sm">
                                    <button
                                        type="button"
                                        @click="openEditVariantModal(v)"
                                        class="text-blue-600 hover:text-blue-800 mr-3 font-semibold cursor-pointer"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        v-if="product.variants.length > 1"
                                        type="button"
                                        @click="deleteVariant(v)"
                                        class="text-red-600 hover:text-red-800 font-semibold cursor-pointer"
                                    >
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════════ -->
        <!-- EDIT VARIANT MODAL -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <BaseModal
            :show="showEditVariantModal"
            title="Edit Variant"
            @close="showEditVariantModal = false"
        >
            <form @submit.prevent="submitEditVariant" class="space-y-4">
                <FormInput
                    v-model="editVariantForm.variant_name"
                    label="Variant Name"
                    :error="editVariantForm.errors.variant_name"
                    required
                />
                
                <FormInput
                    v-model="editVariantForm.sku"
                    label="SKU"
                    :error="editVariantForm.errors.sku"
                    required
                />

                <div class="grid grid-cols-2 gap-3">
                    <FormInput
                        v-model="editVariantForm.barcode"
                        label="Barcode"
                        :error="editVariantForm.errors.barcode"
                    />
                    <FormSelect
                        v-model="editVariantForm.barcode_type"
                        label="Barcode Type"
                        :error="editVariantForm.errors.barcode_type"
                        :options="[
                            { value: 'EAN13', label: 'EAN13' },
                            { value: 'EAN8', label: 'EAN8' },
                            { value: 'QR', label: 'QR' },
                            { value: 'CODE128', label: 'CODE128' },
                            { value: 'CUSTOM', label: 'CUSTOM' }
                        ]"
                    />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <FormInput
                        v-model="editVariantForm.weight"
                        type="number"
                        label="Weight (gram)"
                        :error="editVariantForm.errors.weight"
                    />
                    <FormInput
                        v-model="editVariantForm.purchase_price"
                        type="number"
                        label="Purchase Price (Rp)"
                        :error="editVariantForm.errors.purchase_price"
                    />
                </div>

                <div class="flex items-center">
                    <input
                        v-model="editVariantForm.is_active"
                        type="checkbox"
                        id="variant_active"
                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                    />
                    <label for="variant_active" class="ml-2 text-sm font-semibold text-gray-700">Active</label>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <BaseButton type="button" variant="secondary" @click="showEditVariantModal = false">Cancel</BaseButton>
                    <BaseButton type="submit" :loading="editVariantForm.processing">Update Variant</BaseButton>
                </div>
            </form>
        </BaseModal>

        <!-- ════════════════════════════════════════════════════════════════ -->
        <!-- ADD VARIANT MODAL -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <BaseModal
            :show="showAddVariantModal"
            title="Add New Variant"
            @close="showAddVariantModal = false"
        >
            <form @submit.prevent="submitAddVariant" class="space-y-4">
                
                <!-- Attribute Selectors -->
                <div class="bg-gray-50 p-4 border rounded-lg space-y-3">
                    <h3 class="text-sm font-bold text-gray-700">Pilih Atribut</h3>
                    
                    <div v-for="attr in product.attributes" :key="attr.id" class="grid grid-cols-1 gap-2">
                        <FormSelect
                            v-model="selectedAttributes[attr.id]"
                            :label="attr.attribute_name"
                            required
                            :options="[
                                { value: '', label: '-- Pilih --' },
                                ...attr.values.map(v => ({ value: v.id, label: v.value }))
                            ]"
                            @change="updateVariantNameAndSku"
                        />
                    </div>
                </div>

                <FormInput
                    v-model="addVariantForm.variant_name"
                    label="Variant Name (Auto-generated)"
                    :error="addVariantForm.errors.variant_name"
                    required
                />
                
                <FormInput
                    v-model="addVariantForm.sku"
                    label="SKU"
                    :error="addVariantForm.errors.sku"
                    required
                />

                <div class="grid grid-cols-2 gap-3">
                    <FormInput
                        v-model="addVariantForm.barcode"
                        label="Barcode"
                        :error="addVariantForm.errors.barcode"
                    />
                    <FormSelect
                        v-model="addVariantForm.barcode_type"
                        label="Barcode Type"
                        :error="addVariantForm.errors.barcode_type"
                        :options="[
                            { value: 'EAN13', label: 'EAN13' },
                            { value: 'EAN8', label: 'EAN8' },
                            { value: 'QR', label: 'QR' },
                            { value: 'CODE128', label: 'CODE128' },
                            { value: 'CUSTOM', label: 'CUSTOM' }
                        ]"
                    />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <FormInput
                        v-model="addVariantForm.weight"
                        type="number"
                        label="Weight (gram)"
                        :error="addVariantForm.errors.weight"
                    />
                    <FormInput
                        v-model="addVariantForm.purchase_price"
                        type="number"
                        label="Purchase Price (Rp)"
                        :error="addVariantForm.errors.purchase_price"
                    />
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <BaseButton type="button" variant="secondary" @click="showAddVariantModal = false">Cancel</BaseButton>
                    <BaseButton type="submit" :loading="addVariantForm.processing">Add Variant</BaseButton>
                </div>
            </form>
        </BaseModal>
    </DashboardLayout>
</template>
