<script setup>
import { ref, watch } from "vue";
import { router, Link, useForm, usePage } from "@inertiajs/vue3";
import DashboardLayout from "@/Layouts/DashboardLayout.vue";
import BaseButton from "@/Components/Base/BaseButton.vue";
import DataTable from "@/Components/Table/DataTable.vue";
import FormInput from "@/Components/Form/FormInput.vue";
import FormSelect from "@/Components/Form/FormSelect.vue";
import Pagination from "@/Components/Navigation/Pagination.vue";
import { Head } from "@inertiajs/vue3";

function debounce(fn, delay) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn.apply(this, args), delay);
    };
}

const props = defineProps({
    products: { type: Object, required: true },
    brands: { type: Array, required: true },
    categories: { type: Array, required: true },
    filters: { type: Object, default: () => ({}) },
});

const page = usePage();
const showImportModal = ref(false);

const importForm = useForm({
    file: null,
});

function onFileChange(event) {
    importForm.file = event.target.files[0] ?? null;
}

function submitImport() {
    if (!importForm.file) return;

    importForm.post("/product/products/import", {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            showImportModal.value = false;
            importForm.reset();
        },
    });
}

const importResult = page.props.flash?.import_result ?? null;

const columns = [
    { key: "no", label: "No" },
    { key: "product_code", label: "Code" },
    { key: "product_name", label: "Name" },
    { key: "category", label: "Category" },
    { key: "brand", label: "Brand" },
    { key: "product_type", label: "Type" },
    { key: "is_active", label: "Status" },
    { key: "actions", label: "Actions" },
];

// Local filter state
const search = ref(props.filters.search || "");
const categoryId = ref(props.filters.category_id || "");
const brandId = ref(props.filters.brand_id || "");
const isActive = ref(props.filters.is_active !== undefined ? props.filters.is_active : "");

const handleSearch = debounce(() => {
    router.get(
        "/product/products",
        {
            search: search.value,
            category_id: categoryId.value,
            brand_id: brandId.value,
            is_active: isActive.value,
        },
        { preserveState: true, replace: true }
    );
}, 300);

watch([categoryId, brandId, isActive], () => {
    router.get(
        "/product/products",
        {
            search: search.value,
            category_id: categoryId.value,
            brand_id: brandId.value,
            is_active: isActive.value,
        },
        { preserveState: true }
    );
});

function deleteProduct(product) {
    if (!confirm(`Hapus produk "${product.product_name}"? Semua variannya juga akan dihapus.`)) return;

    router.delete(`/product/products/${product.id}`, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Products" />

    <DashboardLayout>
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Products</h1>
                <p class="text-gray-600">Kelola master data produk dan varian</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="/product/products/import/template">
                    <BaseButton variant="secondary">Download Template</BaseButton>
                </a>
                <BaseButton variant="secondary" @click="showImportModal = true">
                    Import CSV
                </BaseButton>
                <Link href="/product/products/create">
                    <BaseButton>+ Create Product</BaseButton>
                </Link>
            </div>
        </div>

        <!-- Import result -->
        <div
            v-if="importResult && importResult.errors?.length"
            class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900"
        >
            <p class="font-semibold mb-2">
                Import: {{ importResult.success }} berhasil, {{ importResult.failed }} gagal
            </p>
            <ul class="list-disc pl-5 space-y-1 max-h-40 overflow-y-auto">
                <li v-for="(err, idx) in importResult.errors.slice(0, 10)" :key="idx">
                    Baris {{ err.row }}: {{ err.message }}
                </li>
            </ul>
        </div>

        <!-- Filter Card -->
        <div class="bg-white rounded-lg shadow p-4 mb-6 grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <FormInput
                    v-model="search"
                    label="Search Code, Name, SKU"
                    placeholder="Search..."
                    @input="handleSearch"
                />
            </div>
            <div>
                <FormSelect
                    v-model="categoryId"
                    label="Category"
                    :options="[
                        { value: '', label: 'All Categories' },
                        ...categories.map(c => ({ value: c.id, label: c.category_name }))
                    ]"
                />
            </div>
            <div>
                <FormSelect
                    v-model="brandId"
                    label="Brand"
                    :options="[
                        { value: '', label: 'All Brands' },
                        ...brands.map(b => ({ value: b.id, label: b.brand_name }))
                    ]"
                />
            </div>
            <div>
                <FormSelect
                    v-model="isActive"
                    label="Status"
                    :options="[
                        { value: '', label: 'All Statuses' },
                        { value: '1', label: 'Active' },
                        { value: '0', label: 'Inactive' }
                    ]"
                />
            </div>
        </div>

        <!-- Table -->
        <DataTable :columns="columns" :rows="products.data || []" :paginated="false">
            <template #cell-product_code="{ value }">
                <span class="px-2 py-1 bg-slate-100 text-slate-800 rounded text-xs font-mono font-medium">
                    {{ value }}
                </span>
            </template>
            <template #cell-category="{ row }">
                <span class="text-gray-700">{{ row.category?.category_name || '-' }}</span>
            </template>
            <template #cell-brand="{ row }">
                <span class="text-gray-700">{{ row.brand?.brand_name || '-' }}</span>
            </template>
            <template #cell-product_type="{ value }">
                <span
                    :class="[
                        'px-2.5 py-0.5 rounded text-xs font-semibold uppercase tracking-wider',
                        value === 'VARIANT' ? 'bg-purple-100 text-purple-800' :
                        value === 'BUNDLE' ? 'bg-amber-100 text-amber-800' :
                        'bg-blue-100 text-blue-800'
                    ]"
                >
                    {{ value }}
                </span>
            </template>
            <template #cell-is_active="{ value }">
                <span
                    :class="value ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                    class="px-2 py-1 rounded-full text-xs font-semibold"
                >
                    {{ value ? "Active" : "Inactive" }}
                </span>
            </template>
            <template #cell-actions="{ row }">
                <Link :href="`/product/products/${row.id}/edit`" class="text-blue-600 hover:text-blue-800 mr-3 font-medium">
                    Edit
                </Link>
                <button
                    @click="deleteProduct(row)"
                    class="text-red-600 hover:text-red-800 font-medium cursor-pointer"
                >
                    Delete
                </button>
            </template>
        </DataTable>

        <!-- Pagination -->
        <Pagination :links="products.links" :meta="products" />

        <!-- Import Modal -->
        <div
            v-if="showImportModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
            @click.self="showImportModal = false"
        >
            <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-lg">
                <h2 class="text-lg font-semibold text-gray-800 mb-1">Import Produk dari CSV</h2>
                <p class="text-sm text-gray-600 mb-4">
                    Download template ZIP terlebih dahulu, edit file
                    <code class="text-xs bg-slate-100 px-1 rounded">product_import_template.csv</code>,
                    lalu upload di sini.
                </p>

                <form @submit.prevent="submitImport" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">File CSV</label>
                        <input
                            type="file"
                            accept=".csv,text/csv"
                            class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-emerald-50 file:px-3 file:py-2 file:text-emerald-700 hover:file:bg-emerald-100"
                            @change="onFileChange"
                        />
                        <p v-if="importForm.errors.file" class="text-sm text-red-600 mt-1">
                            {{ importForm.errors.file }}
                        </p>
                    </div>

                    <div class="flex justify-end gap-2">
                        <BaseButton type="button" variant="secondary" @click="showImportModal = false">
                            Batal
                        </BaseButton>
                        <BaseButton type="submit" :disabled="importForm.processing || !importForm.file">
                            {{ importForm.processing ? 'Mengimport...' : 'Upload & Import' }}
                        </BaseButton>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>
