<script setup>
import { ref } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import DashboardLayout from "@/Layouts/DashboardLayout.vue";
import BaseModal from "@/Components/Modal/BaseModal.vue";
import BaseButton from "@/Components/Base/BaseButton.vue";
import FormInput from "@/Components/Form/FormInput.vue";
import FormTextarea from "@/Components/Form/FormTextarea.vue";
import DataTable from "@/Components/Table/DataTable.vue";
import { Head } from "@inertiajs/vue3";

const props = defineProps({
    brands: { type: Array, required: true },
});

const columns = [
    { key: "code", label: "Code" },
    { key: "name", label: "Name" },
    { key: "description", label: "Description" },
    { key: "is_active", label: "Status" },
];

// Modal State
const showModal = ref(false);
const editingBrand = ref(null);

// Form
const form = useForm({
    code: "",
    name: "",
    description: "",
    is_active: true,
});

function openCreateModal() {
    editingBrand.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
}

function openEditModal(brand) {
    editingBrand.value = brand;
    form.code = brand.code || "";
    form.name = brand.name || "";
    form.description = brand.description || "";
    form.is_active = brand.is_active ?? true;
    form.clearErrors();
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    editingBrand.value = null;
    form.reset();
}

function submit() {
    if (editingBrand.value) {
        form.put(`/product/brands/${editingBrand.value.id}`, {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    } else {
        form.post("/product/brands", {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    }
}

function deleteBrand(brand) {
    if (!confirm(`Hapus brand "${brand.name}"?`)) return;

    router.delete(`/product/brands/${brand.id}`, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Product Brands" />

    <DashboardLayout>
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Product Brands</h1>
                <p class="text-gray-600">Kelola brand produk</p>
            </div>
            <BaseButton @click="openCreateModal">+ Create Brand</BaseButton>
        </div>

        <DataTable :columns="columns" :rows="brands">
            <template #cell-code="{ value }">
                <span
                    class="px-2 py-1 bg-slate-100 text-slate-800 rounded text-xs font-mono"
                    >{{ value }}</span
                >
            </template>
            <template #cell-is_active="{ value }">
                <span
                    :class="
                        value
                            ? 'bg-green-100 text-green-800'
                            : 'bg-red-100 text-red-800'
                    "
                    class="px-2 py-1 rounded-full text-xs font-semibold"
                >
                    {{ value ? "Active" : "Inactive" }}
                </span>
            </template>
            <template #cell-actions="{ row }">
                <button
                    @click="openEditModal(row)"
                    class="text-blue-600 hover:text-blue-800 mr-3 font-medium"
                >
                    Edit
                </button>
                <button
                    @click="deleteBrand(row)"
                    class="text-red-600 hover:text-red-800 font-medium"
                >
                    Delete
                </button>
            </template>
        </DataTable>

        <!-- Tambahkan kolom actions -->
        <template v-if="brands.length > 0">
            <!-- Actions column sudah ada di slot cell-actions -->
        </template>

        <BaseModal
            :show="showModal"
            :title="editingBrand ? 'Edit Brand' : 'Create Brand'"
            @close="closeModal"
        >
            <form @submit.prevent="submit" class="space-y-4">
                <FormInput
                    v-model="form.code"
                    label="Code"
                    :error="form.errors.code"
                    required
                />
                <FormInput
                    v-model="form.name"
                    label="Name"
                    :error="form.errors.name"
                    required
                />
                <FormTextarea
                    v-model="form.description"
                    label="Description"
                    :error="form.errors.description"
                />

                <div class="flex items-center">
                    <input
                        v-model="form.is_active"
                        type="checkbox"
                        id="is_active"
                        class="w-4 h-4 text-blue-600 rounded"
                    />
                    <label
                        for="is_active"
                        class="ml-2 text-sm font-medium text-gray-700"
                        >Active</label
                    >
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <BaseButton variant="secondary" @click="closeModal"
                        >Cancel</BaseButton
                    >
                    <BaseButton type="submit" :loading="form.processing">
                        {{ editingBrand ? "Update" : "Create" }}
                    </BaseButton>
                </div>
            </form>
        </BaseModal>
    </DashboardLayout>
</template>
