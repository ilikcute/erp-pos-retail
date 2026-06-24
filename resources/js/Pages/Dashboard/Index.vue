<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="text-gray-500 text-sm font-medium">Total Sales</div>
            <div class="text-3xl font-bold text-gray-900 mt-2">
              {{ formatCurrency(kpis.sales_kpi.total_sales) }}
            </div>
            <div class="text-gray-500 text-xs mt-2">
              {{ kpis.sales_kpi.total_transactions }} transactions
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="text-gray-500 text-sm font-medium">Inventory Value</div>
            <div class="text-3xl font-bold text-gray-900 mt-2">
              {{ formatCurrency(kpis.inventory_kpi.total_value) }}
            </div>
            <div class="text-red-600 text-xs mt-2" v-if="kpis.inventory_kpi.low_stock_count > 0">
              ⚠️ {{ kpis.inventory_kpi.low_stock_count }} low stock items
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="text-gray-500 text-sm font-medium">Gross Profit</div>
            <div class="text-3xl font-bold text-gray-900 mt-2">
              {{ formatCurrency(kpis.financial_kpi.gross_profit) }}
            </div>
            <div class="text-gray-500 text-xs mt-2">
              {{ kpis.financial_kpi.gross_margin_percent.toFixed(2) }}% margin
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="text-gray-500 text-sm font-medium">Customers</div>
            <div class="text-3xl font-bold text-gray-900 mt-2">
              {{ kpis.customer_kpi.total_customers }}
            </div>
            <div class="text-gray-500 text-xs mt-2">
              {{ kpis.customer_kpi.repeat_customers }} repeat
            </div>
          </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
          <!-- Sales Trend Chart -->
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Sales Trend</h3>
            <canvas id="salesChart"></canvas>
          </div>

          <!-- Top Products -->
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Products</h3>
            <div class="space-y-3">
              <div v-for="product in kpis.top_products" :key="product.product_id" class="flex justify-between items-center">
                <span class="text-gray-700">{{ product.product_name }}</span>
                <div class="text-right">
                  <div class="text-sm font-semibold text-gray-900">{{ product.total_qty }} units</div>
                  <div class="text-xs text-gray-500">{{ formatCurrency(product.total_sales) }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <Link href="/pos" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
              New Sale
            </Link>
            <Link href="/products" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
              Add Product
            </Link>
            <Link href="/inventory" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
              Inventory
            </Link>
            <Link href="/reporting" class="bg-orange-600 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded">
              Reports
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link } from '@inertiajs/vue3'
import Chart from 'chart.js/auto'

const props = defineProps({
  kpis: Object,
})

const kpis = ref(props.kpis)

const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(value)
}

onMounted(() => {
  // Sales Trend Chart
  const ctx = document.getElementById('salesChart')
  if (ctx) {
    const dates = Object.keys(kpis.value.sales_trend)
    const sales = Object.values(kpis.value.sales_trend)

    new Chart(ctx, {
      type: 'line',
      data: {
        labels: dates,
        datasets: [
          {
            label: 'Daily Sales',
            data: sales,
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
          },
        ],
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            display: true,
          },
        },
        scales: {
          y: {
            beginAtZero: true,
          },
        },
      },
    })
  }
})
</script>
