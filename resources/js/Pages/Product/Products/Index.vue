<script setup>
import { ref, watch } from "vue";
import { router, Link } from "@inertiajs/vue3";
import DashboardLayout from "@/Layouts/DashboardLayout.vue";
import BaseButton from "@/Components/Base/BaseButton.vue";
import DataTable from "@/Components/Table/DataTable.vue";
import FormInput from "@/Components/Form/FormInput.vue";
import FormSelect from "@/Components/Form/FormSelect.vue";
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

const columns = [
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
            <Link href="/product/products/create">
                <BaseButton>+ Create Product</BaseButton>
            </Link>
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
        <DataTable :columns="columns" :rows="products.data || []">
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
        <div v-if="products.links && products.links.length > 3" class="mt-4 flex justify-between items-center bg-white px-4 py-3 border border-gray-200 rounded-lg shadow-sm">
            <div>
                <p class="text-sm text-gray-700">
                    Showing <span class="font-medium">{{ products.from || 0 }}</span> to <span class="font-medium">{{ products.to || 0 }}</span> of <span class="font-medium">{{ products.total }}</span> results
                </p>
            </div>
            <div class="flex space-x-1">
                <Link
                    v-for="link in products.links"
                    :key="link.label"
                    :href="link.url || '#'"
                    v-html="link.label"
                    :class="[
                        'px-3 py-1.5 border rounded text-xs transition-all font-medium',
                        link.active ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50',
                        !link.url ? 'opacity-50 cursor-not-allowed pointer-events-none' : ''
                    ]"
                />
            </div>
        </div>
    </DashboardLayout>
</template>
