<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import BaseCard from '@/Components/Base/BaseCard.vue';
import KPICard from '@/Components/Dashboard/KPICard.vue';
import Icon from '@/Components/Base/Icon.vue';

const dashboardData = ref(null);

onMounted(async () => {
    const { data } = await axios.get('/api/v1/dashboard');
    dashboardData.value = data.data;
});
</script>

<template>
    <Head title="Dashboard" />

    <DashboardLayout>
        <!-- Top Section: Stats & Quick Links -->
        <div class="grid grid-cols-1 xl:grid-cols-4 gap-xl mb-2xl">
            <!-- Stats (Span 3) -->
            <div v-if="dashboardData" class="xl:col-span-3 grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-md">
                <KPICard label="Sales" :value="`Rp ${dashboardData.sales_kpi.total_sales.toLocaleString()}`" icon="trending-up" color="success" />
                <KPICard label="Txn" :value="dashboardData.sales_kpi.total_transactions.toString()" icon="shopping-cart" color="brand" />
                <KPICard label="Margin" :value="`${dashboardData.financial_kpi.gross_margin_percent.toFixed(1)}%`" icon="percent" color="info" />
                <KPICard label="Low Stock" :value="dashboardData.inventory_kpi.low_stock_count.toString()" icon="alert-circle" color="warning" />
                <KPICard label="Cust" :value="dashboardData.customer_kpi.total_customers.toString()" icon="users" color="brand" />
            </div>

            <!-- Quick Links -->
            <div class="bg-surface-elevated p-md rounded-xl border border-border-soft flex flex-col gap-sm">
                <h3 class="text-sm font-semibold text-ink-secondary">Quick Access</h3>
                <Link href="/pos" class="flex items-center gap-sm p-sm rounded-lg hover:bg-brand/10 text-brand">
                    <Icon name="shopping-cart" class="w-4 h-4" /> Buka Kasir
                </Link>
                <Link href="/purchasing/create" class="flex items-center gap-sm p-sm rounded-lg hover:bg-brand/10 text-brand">
                    <Icon name="plus-circle" class="w-4 h-4" /> Buat PO
                </Link>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-xl">

            <!-- Top Products -->
            <BaseCard v-if="dashboardData">
                <h2 class="text-card-title font-semibold mb-lg">Top Products</h2>
                <div class="space-y-md">
                    <div v-for="p in dashboardData.top_products" :key="p.product_id" class="flex justify-between items-center pb-md border-b border-border-soft">
                        <p class="text-sm text-ink-primary truncate">{{ p.product_name }}</p>
                        <span class="text-sm font-semibold">Rp {{ p.total_sales.toLocaleString() }}</span>
                    </div>
                </div>
            </BaseCard>
        </div>
    </DashboardLayout>
</template>

