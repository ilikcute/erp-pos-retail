<script setup>
import { ref, computed } from "vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import DashboardLayout from "@/Layouts/DashboardLayout.vue";
import BaseModal from "@/Components/Modal/BaseModal.vue";   
import BaseButton from "@/Components/Base/BaseButton.vue";
import FormInput from "@/Components/Form/FormInput.vue";
import FormTextarea from "@/Components/Form/FormTextarea.vue";
import DataTable from "@/Components/Table/DataTable.vue";
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

const columns = [
    { key: "id", label: "ID" },
    { key: "name", label: "Name" },
    { key: "email", label: "Email" },
    { key: "roles", label: "Roles" },
    { key: "is_active", label: "Status" },
    { key: "actions", label: "Actions" },
];

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

            <BaseButton
                v-if="canManage"
                @click="openCreateModal"
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
            </BaseButton>
        </div>

        <!-- Data Table -->
        <DataTable :columns="columns" :rows="users">
            <template #cell-roles="{ row }">
                <div class="flex flex-wrap gap-1">
                    <span
                        v-for="role in row.roles"
                        :key="role.id"
                        class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-semibold"
                    >
                        {{ role.name }}
                    </span>
                    <span
                        v-if="!row.roles || row.roles.length === 0"
                        class="text-gray-400"
                    >
                        No roles
                    </span>
                </div>
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
                    v-if="canManage"
                    @click="openEditModal(row)"
                    class="text-blue-600 hover:text-blue-800 mr-3 font-medium cursor-pointer"
                >
                    Edit
                </button>
                <button
                    v-if="canManage"
                    @click="deleteUser(row)"
                    class="text-red-600 hover:text-red-800 font-medium cursor-pointer"
                >
                    Delete
                </button>
            </template>
        </DataTable>

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
                    <FormInput
                        v-model="form.name"
                        label="Name"
                        :error="form.errors.name"
                        required
                        placeholder="e.g. John Doe"
                    />

                    <!-- Email -->
                    <FormInput
                        v-model="form.email"
                        label="Email"
                        :error="form.errors.email"
                        type="email"
                        required
                        placeholder="e.g. john@example.com"
                    />

                    <!-- Password -->
                    <FormInput
                        v-model="form.password"
                        :label="editingUser ? 'Password (Leave blank to keep current)' : 'Password'"
                        :error="form.errors.password"
                        type="password"
                        :required="!editingUser"
                        :placeholder="editingUser ? '••••••••' : 'Minimum 8 characters'"
                    />

                    <!-- Password Confirmation -->
                    <FormInput
                        v-model="form.password_confirmation"
                        label="Confirm Password"
                        type="password"
                        placeholder="Repeat password"
                    />

                    <!-- Phone -->
                    <FormInput
                        v-model="form.phone"
                        label="Phone"
                        :error="form.errors.phone"
                        placeholder="e.g. 081234567890"
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
                    <BaseButton
                        variant="secondary"
                        @click="closeModal"
                    >
                        Cancel
                    </BaseButton>
                    <BaseButton
                        type="submit"
                        :loading="form.processing"
                    >
                        {{ editingUser ? "Update User" : "Create User" }}
                    </BaseButton>
                </div>
            </form>
        </BaseModal>
    </DashboardLayout>
</template>
