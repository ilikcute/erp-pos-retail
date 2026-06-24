<script setup>
import { ref, computed } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import DashboardLayout from "@/Layouts/DashboardLayout.vue";
import BaseModal from "@/Components/Modal/BaseModal.vue";
import BaseButton from "@/Components/Base/BaseButton.vue";
import FormInput from "@/Components/Form/FormInput.vue";
import FormSelect from "@/Components/Form/FormSelect.vue";
import FormTextarea from "@/Components/Form/FormTextarea.vue";
import CategoryTreeItem from "@/Components/DataDisplay/CategoryTreeItem.vue";
import { Head } from "@inertiajs/vue3";

const props = defineProps({
    categories: { type: Array, required: true },
    flatCategories: { type: Array, required: true },
});

// Modal State
const showModal = ref(false);
const editingCategory = ref(null);

// Form
const form = useForm({
    name: "",
    description: "",
    parent_id: null,
    is_active: true,
});

// Options untuk dropdown parent (exclude self saat edit)
const parentOptions = computed(() => {
    return props.flatCategories
        .filter((cat) => {
            if (editingCategory.value) {
                // Jangan tampilkan dirinya sendiri dan descendants-nya
                return cat.id !== editingCategory.value.id;
            }
            return true;
        })
        .map((cat) => ({
            value: cat.id,
            label: `${cat.code} - ${cat.name}`,
        }));
});

function openCreateModal() {
    editingCategory.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
}

function openEditModal(category) {
    editingCategory.value = category;
    form.name = category.name || "";
    form.description = category.description || "";
    form.parent_id = category.parent_id || null;
    form.is_active = category.is_active ?? true;
    form.clearErrors();
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    editingCategory.value = null;
    form.reset();
}

function submit() {
    if (editingCategory.value) {
        form.put(`/product/categories/${editingCategory.value.id}`, {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    } else {
        form.post("/product/categories", {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    }
}

function deleteCategory(category) {
    const childCount = category.children_recursive?.length || 0;
    const msg =
        childCount > 0
            ? `Kategori "${category.name}" memiliki ${childCount} sub-kategori. Tidak bisa dihapus.`
            : `Hapus kategori "${category.name}"?`;

    if (!confirm(msg)) return;
    if (childCount > 0) return; // Block jika ada children

    router.delete(`/product/categories/${category.id}`, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Product Categories" />

    <DashboardLayout>
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">
                    Product Categories
                </h1>
                <p class="text-gray-600">
                    Kelola kategori produk dengan struktur hierarki
                </p>
            </div>
            <BaseButton @click="openCreateModal">+ Create Category</BaseButton>
        </div>

        <!-- Tree View -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div
                v-if="categories.length === 0"
                class="p-12 text-center text-gray-500"
            >
                <p class="text-lg">Belum ada kategori.</p>
                <p class="text-sm mt-2">
                    Klik "Create Category" untuk memulai.
                </p>
            </div>

            <div v-else class="p-4">
                <CategoryTreeItem
                    v-for="category in categories"
                    :key="category.id"
                    :category="category"
                    :level="0"
                    :onEdit="openEditModal"
                    :onDelete="deleteCategory"
                />
            </div>
        </div>

        <!-- Modal Form -->
        <BaseModal
            :show="showModal"
            :title="editingCategory ? 'Edit Category' : 'Create Category'"
            @close="closeModal"
        >
            <form @submit.prevent="submit" class="space-y-4">
                <FormInput
                    v-model="form.name"
                    label="Name"
                    :error="form.errors.name"
                    placeholder="e.g. Minuman"
                    required
                />

                <FormSelect
                    v-model="form.parent_id"
                    label="Parent Category"
                    :options="parentOptions"
                    :error="form.errors.parent_id"
                    placeholder="-- No Parent (Root Category) --"
                />

                <FormTextarea
                    v-model="form.description"
                    label="Description"
                    :error="form.errors.description"
                    placeholder="Deskripsi kategori..."
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
                    >
                        Active
                    </label>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <BaseButton variant="secondary" @click="closeModal"
                        >Cancel</BaseButton
                    >
                    <BaseButton type="submit" :loading="form.processing">
                        {{ editingCategory ? "Update" : "Create" }}
                    </BaseButton>
                </div>
            </form>
        </BaseModal>
    </DashboardLayout>
</template>
