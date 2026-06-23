<script setup>
import { ref, computed } from "vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import DashboardLayout from "@/Layouts/DashboardLayout.vue";
import BaseModal from "@/Components/Modal/BaseModal.vue";
import { Head } from "@inertiajs/vue3";

const props = defineProps({
    users: {
        type: Array,
        required: true,
    },
    roles: {
        type: Array,
        required: true,
    },
});

const page = usePage();
const canManage = true; // Untuk sementara kita set true

// Modal State
const showModal = ref(false);
const editingUser = ref(null);

// Form
const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    phone: "",
    is_active: true,
    roles: [],
});

function openCreateModal() {
    editingUser.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
}

function openEditModal(user) {
    editingUser.value = user;
    form.name = user.name || "";
    form.email = user.email || "";
    form.password = "";
    form.password_confirmation = "";
    form.phone = user.phone || "";
    form.is_active = user.is_active ?? true;
    form.roles = user.roles?.map((r) => Number(r.id)) || [];
    form.clearErrors();
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    editingUser.value = null;
    form.reset();
}

function submit() {
    if (editingUser.value) {
        form.put(`/system/users/${editingUser.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
            },
        });
    } else {
        form.post("/system/users", {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
            },
        });
    }
}

function deleteUser(user) {
    if (!confirm(`Are you sure you want to delete user "${user.name}"?`)) {
        return;
    }

    router.delete(`/system/users/${user.id}`, {
        preserveScroll: true,
    });
}

function toggleRole(roleId) {
    const id = Number(roleId);
    const index = form.roles.indexOf(id);
    if (index > -1) {
        form.roles.splice(index, 1);
    } else {
        form.roles.push(id);
    }
}
</script>

<template>
    <Head title="User Management" />

    <DashboardLayout>
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">
                    User Management
                </h1>
                <p class="text-gray-600">
                    Kelola pengguna sistem dan assign role
                </p>
            </div>

            <button
                v-if="canManage"
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
                Create User
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
                            Email
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Roles
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Status
                        </th>
                        <th
                            v-if="canManage"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr
                        v-for="user in users"
                        :key="user.id"
                        class="hover:bg-gray-50"
                    >
                        <td
                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                        >
                            {{ user.id }}
                        </td>
                        <td
                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                        >
                            {{ user.name }}
                        </td>
                        <td
                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                        >
                            {{ user.email }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <div class="flex flex-wrap gap-1">
                                <span
                                    v-for="role in user.roles"
                                    :key="role.id"
                                    class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-semibold"
                                >
                                    {{ role.name }}
                                </span>
                                <span
                                    v-if="
                                        !user.roles || user.roles.length === 0
                                    "
                                    class="text-gray-400"
                                >
                                    No roles
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                :class="
                                    user.is_active
                                        ? 'bg-green-100 text-green-800'
                                        : 'bg-red-100 text-red-800'
                                "
                                class="px-2 py-1 rounded-full text-xs font-semibold"
                            >
                                {{ user.is_active ? "Active" : "Inactive" }}
                            </span>
                        </td>
                        <td
                            v-if="canManage"
                            class="px-6 py-4 whitespace-nowrap text-sm"
                        >
                            <button
                                @click="openEditModal(user)"
                                class="text-blue-600 hover:text-blue-800 mr-3 font-medium"
                            >
                                Edit
                            </button>
                            <button
                                @click="deleteUser(user)"
                                class="text-red-600 hover:text-red-800 font-medium"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>

                    <!-- Empty State -->
                    <tr v-if="users.length === 0">
                        <td
                            :colspan="canManage ? 6 : 5"
                            class="px-6 py-12 text-center text-gray-500"
                        >
                            <p class="text-lg">Belum ada data user.</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal Form Create/Edit -->
        <BaseModal
            :show="showModal"
            :title="editingUser ? 'Edit User' : 'Create New User'"
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
                            placeholder="e.g. John Doe"
                        />
                        <p
                            v-if="form.errors.name"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Email *</label
                        >
                        <input
                            v-model="form.email"
                            type="email"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="{ 'border-red-500': form.errors.email }"
                            placeholder="e.g. john@example.com"
                        />
                        <p
                            v-if="form.errors.email"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            Password
                            {{
                                editingUser
                                    ? "(Leave blank to keep current)"
                                    : "*"
                            }}
                        </label>
                        <input
                            v-model="form.password"
                            type="password"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="{ 'border-red-500': form.errors.password }"
                            :placeholder="
                                editingUser
                                    ? '••••••••'
                                    : 'Minimum 8 characters'
                            "
                        />
                        <p
                            v-if="form.errors.password"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Password Confirmation -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Confirm Password</label
                        >
                        <input
                            v-model="form.password_confirmation"
                            type="password"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Repeat password"
                        />
                    </div>

                    <!-- Phone -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Phone</label
                        >
                        <input
                            v-model="form.phone"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="{ 'border-red-500': form.errors.phone }"
                            placeholder="e.g. 081234567890"
                        />
                        <p
                            v-if="form.errors.phone"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.phone }}
                        </p>
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

                    <!-- Roles -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Roles</label
                        >
                        <div
                            class="border border-gray-300 rounded-lg p-4 max-h-64 overflow-y-auto bg-gray-50"
                        >
                            <div
                                v-if="roles.length === 0"
                                class="text-center text-gray-500 py-4"
                            >
                                Belum ada role tersedia.
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <label
                                    v-for="role in roles"
                                    :key="role.id"
                                    class="flex items-center text-sm hover:bg-white p-2 rounded cursor-pointer"
                                >
                                    <input
                                        type="checkbox"
                                        :checked="
                                            form.roles.includes(Number(role.id))
                                        "
                                        @change="toggleRole(role.id)"
                                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                    />
                                    <span class="ml-2 font-medium">{{
                                        role.name
                                    }}</span>
                                    <span class="ml-2 text-xs text-gray-500"
                                        >({{ role.code }})</span
                                    >
                                </label>
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
                                : editingUser
                                  ? "Update User"
                                  : "Create User"
                        }}
                    </button>
                </div>
            </form>
        </BaseModal>
    </DashboardLayout>
</template>
