<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">System Settings</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Tabs -->
        <div class="flex space-x-4 mb-6 border-b">
          <button
            @click="activeTab = 'users'"
            :class="activeTab === 'users' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600'"
            class="py-2 px-4 font-medium"
          >
            Users
          </button>
          <button
            @click="activeTab = 'roles'"
            :class="activeTab === 'roles' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600'"
            class="py-2 px-4 font-medium"
          >
            Roles
          </button>
          <button
            @click="activeTab = 'settings'"
            :class="activeTab === 'settings' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600'"
            class="py-2 px-4 font-medium"
          >
            Settings
          </button>
          <button
            @click="activeTab = 'audit'"
            :class="activeTab === 'audit' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600'"
            class="py-2 px-4 font-medium"
          >
            Audit Logs
          </button>
        </div>

        <!-- Users -->
        <div v-if="activeTab === 'users'" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-semibold">Users</h3>
              <button class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                + New User
              </button>
            </div>

            <div class="overflow-x-auto">
              <table class="w-full text-sm text-left">
                <thead class="text-xs uppercase bg-gray-100">
                  <tr>
                    <th class="px-6 py-3">Name</th>
                    <th class="px-6 py-3">Email</th>
                    <th class="px-6 py-3">Roles</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Last Login</th>
                    <th class="px-6 py-3">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="user in users" :key="user.id" class="border-b hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium">{{ user.name }}</td>
                    <td class="px-6 py-4">{{ user.email }}</td>
                    <td class="px-6 py-4">
                      <span v-for="role in user.roles" :key="role.id" class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs mr-1">
                        {{ role.name }}
                      </span>
                    </td>
                    <td class="px-6 py-4">
                      <span v-if="user.status === 'ACTIVE'" class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">
                        Active
                      </span>
                      <span v-else class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">
                        Inactive
                      </span>
                    </td>
                    <td class="px-6 py-4 text-sm">{{ formatDate(user.last_login_at) }}</td>
                    <td class="px-6 py-4">
                      <button class="text-blue-600 hover:underline">Edit</button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Roles -->
        <div v-if="activeTab === 'roles'" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-semibold">Roles</h3>
              <button class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                + New Role
              </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div v-for="role in roles" :key="role.id" class="border rounded-lg p-4">
                <h4 class="font-semibold text-lg">{{ role.name }}</h4>
                <p class="text-sm text-gray-600 mb-3">{{ role.permissions?.length || 0 }} permissions</p>
                <div class="space-y-1">
                  <div v-for="perm in role.permissions?.slice(0, 3)" :key="perm.id" class="text-xs text-gray-700">
                    ✓ {{ perm.name }}
                  </div>
                  <div v-if="role.permissions?.length > 3" class="text-xs text-gray-500">
                    +{{ role.permissions.length - 3 }} more
                  </div>
                </div>
                <button class="mt-3 text-blue-600 hover:underline text-sm">Edit</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Settings -->
        <div v-if="activeTab === 'settings'" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <h3 class="text-lg font-semibold mb-4">System Settings</h3>
            <p class="text-gray-600">Settings management coming soon...</p>
          </div>
        </div>

        <!-- Audit Logs -->
        <div v-if="activeTab === 'audit'" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <h3 class="text-lg font-semibold mb-4">Audit Logs</h3>
            <div class="space-y-3">
              <div v-for="log in auditLogs" :key="log.id" class="border-b pb-3">
                <div class="flex justify-between">
                  <div>
                    <p class="font-semibold">{{ log.module }} - {{ log.action }}</p>
                    <p class="text-sm text-gray-600">{{ log.user?.name }}</p>
                  </div>
                  <span class="text-sm text-gray-500">{{ formatDateTime(log.created_at) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  users: Array,
  roles: Array,
  auditLogs: Array,
})

const activeTab = ref('users')
const users = ref(props.users || [])
const roles = ref(props.roles || [])
const auditLogs = ref(props.auditLogs || [])

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID')
}

const formatDateTime = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleString('id-ID')
}
</script>
