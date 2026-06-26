<script setup>
import { ref, computed } from "vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import DashboardLayout from "@/Layouts/DashboardLayout.vue";
import BaseModal from "@/Components/Modal/BaseModal.vue";
import BaseButton from "@/Components/Base/BaseButton.vue";
import FormInput from "@/Components/Form/FormInput.vue";
import FormTextarea from "@/Components/Form/FormTextarea.vue";
import DataTable from "@/Components/Table/DataTable.vue";
import Pagination from "@/Components/Navigation/Pagination.vue";
import { Head } from "@inertiajs/vue3";

const props = defineProps({
    roles: {
        type: Object,
        required: true,
    },
    permissions: {
        type: Array,
        required: true,
    },
});

const page = usePage();
// Cek permission user (sesuaikan dengan struktur data user Anda)
const canManage = true; // Untuk sementara kita set true agar tombol muncul

const columns = [
    { key: "no", label: "No" },
    { key: "name", label: "Name" },
    { key: "code", label: "Code" },
    { key: "is_active", label: "Status" },
    { key: "permissions", label: "Permissions" },
    { key: "actions", label: "Actions" },
];

// Modal State
const showModal = ref(false);
const editingRole = ref(null);

// Form
const form = useForm({
    name: "",
    description: "",
    is_active: true,
    permissions: [],
});

function openCreateModal() {
    editingRole.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
}

function openEditModal(role) {
    editingRole.value = role;

    // Clear errors dulu
    form.clearErrors();

    // Assign values secara eksplisit (lebih reliable daripada form.reset dengan parameter)
    form.name = role.name || "";
    form.description = role.description || "";
    form.is_active = role.is_active ?? true;

    // PENTING: Konversi permissions ke array of NUMBERS
    // Karena ID dari backend bisa berupa string, kita paksa ke number
    form.permissions = role.permissions?.map((p) => Number(p.id)) || [];

    // Debug: Uncomment baris ini untuk melihat data di console
    // console.log('Edit Role Data:', { role, formPermissions: form.permissions });

    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    editingRole.value = null;
    form.reset();
}

function submit() {
    if (editingRole.value) {
        // UBAH URL MENJADI /system/roles/... (Web Route)
        form.put(`/system/roles/${editingRole.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
            },
        });
    } else {
        // UBAH URL MENJADI /system/roles (Web Route)
        form.post("/system/roles", {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
            },
        });
    }
}

function deleteRole(role) {
    if (!confirm(`Are you sure you want to delete role "${role.name}"?`)) {
        return;
    }

    // UBAH URL MENJADI /system/roles/... (Web Route)
    router.delete(`/system/roles/${role.id}`, {
        preserveScroll: true,
    });
}

function togglePermission(permId) {
    // PENTING: Konversi ke NUMBER untuk konsistensi
    const id = Number(permId);

    const index = form.permissions.indexOf(id);
    if (index > -1) {
        // Hapus dari array jika sudah ada
        form.permissions.splice(index, 1);
    } else {
        // Tambahkan ke array jika belum ada
        form.permissions.push(id);
    }
}

const getSelectedCountForModule = (perms) => {
    return perms.filter((p) => form.permissions.includes(Number(p.id))).length;
};

const isAllModuleSelected = (perms) => {
    return (
        perms.length > 0 && getSelectedCountForModule(perms) === perms.length
    );
};

const toggleAllModule = (perms) => {
    const permIds = perms.map((p) => Number(p.id));
    const allSelected = isAllModuleSelected(perms);

    if (allSelected) {
        // Deselect all
        form.permissions = form.permissions.filter(
            (id) => !permIds.includes(id),
        );
    } else {
        // Select all
        const toAdd = permIds.filter((id) => !form.permissions.includes(id));
        form.permissions.push(...toAdd);
    }
};

// Group permissions by module untuk tampilan yang rapi
const permissionsByModule = computed(() => {
    return props.permissions.reduce((acc, perm) => {
        if (!acc[perm.module]) {
            acc[perm.module] = [];
        }
        acc[perm.module].push(perm);
        return acc;
    }, {});
});
</script>

<template>
    <Head title="Role Management" />

    <DashboardLayout>
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">
                    Role Management
                </h1>
                <p class="text-gray-600">
                    Kelola role dan permission pengguna sistem
                </p>
            </div>

            <BaseButton @click="openCreateModal">
                <svg
                    class="w-5 h-5 mr-2"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 4v16m8-8H4"
                    />
                </svg>
                Create Role
            </BaseButton>
        </div>

        <!-- Data Table -->
        <DataTable :columns="columns" :rows="roles.data" :paginated="false">
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
            <template #cell-permissions="{ row }">
                <span class="font-semibold text-blue-600">{{
                    row.permissions?.length || 0
                }}</span>
                Permissions
            </template>
            <template #cell-actions="{ row }">
                <button
                    @click="openEditModal(row)"
                    class="text-blue-600 hover:text-blue-800 mr-3 font-medium cursor-pointer"
                >
                    Edit
                </button>
                <button
                    v-if="!row.is_system"
                    @click="deleteRole(row)"
                    class="text-red-600 hover:text-red-800 font-medium cursor-pointer"
                >
                    Delete
                </button>
            </template>
        </DataTable>
        
        <div class="mt-4">
            <Pagination :links="roles.links" :meta="roles" />
        </div>

        <!-- Modal Form Create/Edit -->
        <BaseModal
            :show="showModal"
            :title="editingRole ? 'Edit Role' : 'Create New Role'"
            size="lg"
            @close="closeModal"
        >
            <form @submit.prevent="submit">
                <div class="space-y-4">
                    <!-- Name -->
                    <FormInput
                        v-model="form.name"
                        label="Name"
                        :error="form.errors.name"
                        required
                        placeholder="e.g. Manager Toko"
                    />

                    <!-- Description -->
                    <FormTextarea
                        v-model="form.description"
                        label="Description"
                        :error="form.errors.description"
                        :rows="2"
                        placeholder="Deskripsi role..."
                    />

                    <!-- Is Active -->
                    <div class="flex items-center">
                        <input
                            v-model="form.is_active"
                            type="checkbox"
                            id="is_active"
                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        />
                        <label
                            for="is_active"
                            class="ml-2 text-sm font-medium text-gray-700"
                            >Active</label
                        >
                    </div>

                    <!-- Permissions Checkbox -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Permissions</label
                        >
                        <div
                            class="border border-gray-300 rounded-lg p-4 max-h-64 overflow-y-auto bg-gray-50"
                        >
                            <div
                                v-if="
                                    Object.keys(permissionsByModule).length ===
                                    0
                                "
                                class="text-center text-gray-500 py-4"
                            >
                                Belum ada permission tersedia.
                            </div>

                            <div
                                v-for="(perms, module) in permissionsByModule"
                                :key="module"
                                class="mb-4 last:mb-0"
                            >
                                <div
                                    class="flex items-center mb-2 border-b pb-1"
                                >
                                    <label
                                        class="flex items-center cursor-pointer select-none"
                                    >
                                        <input
                                            type="checkbox"
                                            :checked="
                                                isAllModuleSelected(perms)
                                            "
                                            @change="toggleAllModule(perms)"
                                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mr-2"
                                        />
                                        <span
                                            class="font-semibold text-gray-800 capitalize"
                                        >
                                            {{ module }} ({{
                                                getSelectedCountForModule(
                                                    perms,
                                                )
                                            }})
                                        </span>
                                    </label>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <label
                                        v-for="perm in perms"
                                        :key="perm.id"
                                        class="flex items-center text-sm hover:bg-white p-1 rounded cursor-pointer"
                                    >
                                        <input
                                            type="checkbox"
                                            :checked="
                                                form.permissions.includes(
                                                    Number(perm.id),
                                                )
                                            "
                                            @change="togglePermission(perm.id)"
                                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                        />
                                        <span class="ml-2">{{
                                            perm.name
                                        }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div class="mt-6 flex justify-end space-x-3">
                    <BaseButton variant="secondary" @click="closeModal">
                        Cancel
                    </BaseButton>
                    <BaseButton type="submit" :loading="form.processing">
                        {{ editingRole ? "Update Role" : "Create Role" }}
                    </BaseButton>
                </div>
            </form>
        </BaseModal>
    </DashboardLayout>
</template>
