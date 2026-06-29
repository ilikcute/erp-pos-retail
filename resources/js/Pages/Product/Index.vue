<template>
  <Head title="Products" />
  <DashboardLayout>
    <div class="space-y-6">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
          <!-- Header -->
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold">Product List</h3>
            <Link href="/products/create" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
              + New Product
            </Link>
          </div>

          <!-- Search & Filter -->
          <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search products..."
              class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <select
              v-model="selectedCategory"
              class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">All Categories</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                {{ cat.name }}
              </option>
            </select>
            <button
              @click="filterProducts"
              class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
            >
              Filter
            </button>
          </div>

          <!-- Products Table -->
          <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
              <thead class="text-xs uppercase bg-gray-100">
                <tr>
                  <th class="px-6 py-3">Product Name</th>
                  <th class="px-6 py-3">SKU</th>
                  <th class="px-6 py-3">Category</th>
                  <th class="px-6 py-3">Type</th>
                  <th class="px-6 py-3">Status</th>
                  <th class="px-6 py-3">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="product in filteredProducts" :key="product.id" class="border-b hover:bg-gray-50">
                  <td class="px-6 py-4 font-medium">{{ product.product_name }}</td>
                  <td class="px-6 py-4">{{ product.sku }}</td>
                  <td class="px-6 py-4">{{ product.category?.name }}</td>
                  <td class="px-6 py-4">
                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">
                      {{ product.product_type }}
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <span v-if="product.is_active" class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">
                      Active
                    </span>
                    <span v-else class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">
                      Inactive
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <Link :href="`/products/${product.id}/edit`" class="text-blue-600 hover:underline">
                      Edit
                    </Link>
                    <button @click="deleteProduct(product.id)" class="text-red-600 hover:underline ml-2">
                      Delete
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="mt-6 flex justify-between items-center">
            <div class="text-sm text-gray-600">
              Showing {{ products.length }} products
            </div>
            <div class="space-x-2">
              <button v-if="currentPage > 1" @click="previousPage" class="px-4 py-2 bg-gray-200 rounded">
                Previous
              </button>
              <button @click="nextPage" class="px-4 py-2 bg-gray-200 rounded">
                Next
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { Link, Head, router } from '@inertiajs/vue3'

const props = defineProps({
  products: Array,
  categories: Array,
})

const products = ref(props.products)
const categories = ref(props.categories)
const searchQuery = ref('')
const selectedCategory = ref('')
const currentPage = ref(1)
const itemsPerPage = ref(10)

const filteredProducts = computed(() => {
  let filtered = products.value.filter((p) => {
    const matchesSearch =
      p.product_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      p.sku.toLowerCase().includes(searchQuery.value.toLowerCase())

    const matchesCategory = !selectedCategory.value || p.category_id == selectedCategory.value

    return matchesSearch && matchesCategory
  })

  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value

  return filtered.slice(start, end)
})

const filterProducts = () => {
  currentPage.value = 1
}

const previousPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
  }
}

const nextPage = () => {
  currentPage.value++
}

const deleteProduct = (id) => {
  if (confirm('Are you sure you want to delete this product?')) {
    router.delete(`/api/v1/products/${id}`)
  }
}
</script>
