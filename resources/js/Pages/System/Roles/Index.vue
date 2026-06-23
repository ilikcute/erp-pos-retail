<script setup>
import { ref, computed } from "vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import DashboardLayout from "@/Layouts/DashboardLayout.vue";
import BaseModal from "@/Components/Modal/BaseModal.vue";
import { Head } from "@inertiajs/vue3";

const props = defineProps({
    roles: {
        type: Array,
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

            <button
                @click="openCreateModal"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center"
            >
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
            </button>
        </div>

        <!-- Data Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            ID
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Name
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Code
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Status
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Permissions
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr
                        v-for="role in roles"
                        :key="role.id"
                        class="hover:bg-gray-50"
                    >
                        <td
                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                        >
                            {{ role.id }}
                        </td>
                        <td
                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                        >
                            {{ role.name }}
                        </td>
                        <td
                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                        >
                            <span
                                class="px-2 py-1 bg-slate-100 text-slate-800 rounded text-xs font-mono"
                                >{{ role.code }}</span
                            >
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                :class="
                                    role.is_active
                                        ? 'bg-green-100 text-green-800'
                                        : 'bg-red-100 text-red-800'
                                "
                                class="px-2 py-1 rounded-full text-xs font-semibold"
                            >
                                {{ role.is_active ? "Active" : "Inactive" }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <span class="font-semibold text-blue-600">{{
                                role.permissions?.length || 0
                            }}</span>
                            Permissions
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <button
                                @click="openEditModal(role)"
                                class="text-blue-600 hover:text-blue-800 mr-3 font-medium"
                            >
                                Edit
                            </button>
                            <button
                                v-if="!role.is_system"
                                @click="deleteRole(role)"
                                class="text-red-600 hover:text-red-800 font-medium"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>

                    <!-- Empty State -->
                    <tr v-if="roles.length === 0">
                        <td
                            colspan="6"
                            class="px-6 py-12 text-center text-gray-500"
                        >
                            <p class="text-lg">Belum ada data role.</p>
                        </td>
                    </tr>
                </tbody>
            </table>
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
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Name *</label
                        >
                        <input
                            v-model="form.name"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="{ 'border-red-500': form.errors.name }"
                            placeholder="e.g. Manager Toko"
                        />
                        <p
                            v-if="form.errors.name"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- Description -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Description</label
                        >
                        <textarea
                            v-model="form.description"
                            rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Deskripsi role..."
                        ></textarea>
                    </div>

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
                                <h4
                                    class="font-semibold text-gray-800 mb-2 capitalize border-b pb-1"
                                >
                                    {{ module }}
                                </h4>
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
                    <button
                        type="button"
                        @click="closeModal"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 flex items-center"
                    >
                        <svg
                            v-if="form.processing"
                            class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            ></circle>
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                            ></path>
                        </svg>
                        {{
                            form.processing
                                ? "Saving..."
                                : editingRole
                                  ? "Update Role"
                                  : "Create Role"
                        }}
                    </button>
                </div>
            </form>
        </BaseModal>
    </DashboardLayout>
</template>
