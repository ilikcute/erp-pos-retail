<script setup>
import { ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    transaction: Object,
});

const transaction = ref(props.transaction || {});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};

const getStatusClass = (status) => {
    const classes = {
        DRAFT: 'bg-surface-subtle text-ink-muted border border-border-soft',
        POSTED: 'bg-semantic-success-soft text-semantic-success',
        VOID: 'bg-semantic-danger-soft text-semantic-danger',
    };
    return classes[status] || 'bg-surface-subtle text-ink-muted';
};
</script>

<template>
    <Head :title="`Order Detail - ${transaction.transaction_no}`" />

    <DashboardLayout>
        <div class="mb-6 flex justify-between items-center">
            <div class="flex items-center gap-md">
                <Link href="/pos/sales">
                    <button class="p-2 rounded-md hover:bg-surface-subtle border border-border-soft transition-colors cursor-pointer">
                        <svg class="w-5 h-5 text-ink-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </button>
                </Link>
                <div>
                    <div class="flex items-center gap-sm">
                        <h1 class="text-2xl font-bold text-ink-primary font-mono">
                            {{ transaction.transaction_no }}
                        </h1>
                        <span
                            :class="getStatusClass(transaction.status)"
                            class="px-2 py-0.5 rounded-full text-xs font-semibold border border-transparent"
                        >
                            {{ transaction.status }}
                        </span>
                    </div>
                    <p class="text-ink-secondary">
                        Detail transaksi penjualan kasir
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Side: Order Items and Summary -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Items Card -->
                <div class="bg-surface-card rounded-card border border-border-soft p-6 shadow-card">
                    <h3 class="text-lg font-semibold text-ink-primary mb-4">Item Detail</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs uppercase bg-surface-subtle border-b border-border-soft">
                                <tr>
                                    <th class="px-4 py-3 text-ink-secondary">Product</th>
                                    <th class="px-4 py-3 text-right text-ink-secondary">Quantity</th>
                                    <th class="px-4 py-3 text-right text-ink-secondary">Unit Price</th>
                                    <th class="px-4 py-3 text-right text-ink-secondary">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border-soft">
                                <tr v-for="item in transaction.items" :key="item.id" class="hover:bg-surface-subtle transition-colors">
                                    <td class="px-4 py-4">
                                        <div class="font-medium text-ink-primary">{{ item.product?.name || '-' }}</div>
                                        <div v-if="item.product_variant" class="text-xs text-ink-muted">
                                            Variant: {{ item.product_variant.variant_name }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-right font-mono text-ink-primary">
                                        {{ item.quantity }} {{ item.unit?.unit_code || 'pcs' }}
                                    </td>
                                    <td class="px-4 py-4 text-right font-mono text-ink-secondary">
                                        {{ formatCurrency(item.unit_price) }}
                                    </td>
                                    <td class="px-4 py-4 text-right font-mono text-ink-primary font-medium">
                                        {{ formatCurrency(item.line_total) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Payment Details Card -->
                <div class="bg-surface-card rounded-card border border-border-soft p-6 shadow-card">
                    <h3 class="text-lg font-semibold text-ink-primary mb-4">Metode Pembayaran</h3>
                    <div class="divide-y divide-border-soft">
                        <div v-for="payment in transaction.payments" :key="payment.id" class="py-3 flex justify-between items-center">
                            <div>
                                <span class="font-semibold text-ink-primary">{{ payment.payment_method?.method_name || 'Cash' }}</span>
                                <span class="text-xs text-ink-secondary ml-2 font-mono">({{ payment.payment_method?.method_type || 'CASH' }})</span>
                            </div>
                            <span class="font-mono font-semibold text-ink-primary">{{ formatCurrency(payment.amount) }}</span>
                        </div>
                        <div v-if="!transaction.payments || transaction.payments.length === 0" class="py-3 text-ink-secondary text-center">
                            Tidak ada data pembayaran.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Metadata and Calculations -->
            <div class="space-y-6">
                <!-- Metadata Card -->
                <div class="bg-surface-card rounded-card border border-border-soft p-6 shadow-card">
                    <h3 class="text-lg font-semibold text-ink-primary mb-4">Metadata Transaksi</h3>
                    <div class="space-y-4 text-sm">
                        <div class="flex justify-between">
                            <span class="text-ink-secondary">Tanggal Transaksi</span>
                            <span class="font-medium text-ink-primary">{{ formatDate(transaction.transaction_date) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-ink-secondary">Kasir</span>
                            <span class="font-medium text-ink-primary">{{ transaction.cashier?.name || '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-ink-secondary">Pelanggan</span>
                            <span class="font-medium text-ink-primary">{{ transaction.customer?.name || 'Walk-in Customer' }}</span>
                        </div>
                        <div v-if="transaction.posted_at" class="flex justify-between">
                            <span class="text-ink-secondary">Posted At</span>
                            <span class="font-mono text-ink-primary text-xs">{{ new Date(transaction.posted_at).toLocaleString('id-ID') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Total Calculation Card -->
                <div class="bg-surface-card rounded-card border border-border-soft p-6 shadow-card bg-surface-subtle">
                    <h3 class="text-lg font-semibold text-ink-primary mb-4">Ringkasan Biaya</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between text-ink-secondary">
                            <span>Subtotal</span>
                            <span class="font-mono">{{ formatCurrency(transaction.subtotal) }}</span>
                        </div>
                        <div v-if="transaction.discount_amount > 0" class="flex justify-between text-semantic-danger font-medium">
                            <span>Diskon</span>
                            <span class="font-mono">-{{ formatCurrency(transaction.discount_amount) }}</span>
                        </div>
                        <div v-if="transaction.tax_amount > 0" class="flex justify-between text-ink-secondary">
                            <span>Pajak ({{ transaction.tax_rate }}%)</span>
                            <span class="font-mono">{{ formatCurrency(transaction.tax_amount) }}</span>
                        </div>
                        <div class="border-t border-border-soft pt-3 flex justify-between text-base font-bold text-ink-primary">
                            <span>Total Akhir</span>
                            <span class="font-mono text-brand">{{ formatCurrency(transaction.grand_total) }}</span>
                        </div>
                        <div class="border-t border-border-soft pt-3 flex justify-between text-ink-secondary">
                            <span>Jumlah Dibayar</span>
                            <span class="font-mono">{{ formatCurrency(transaction.paid_amount) }}</span>
                        </div>
                        <div class="flex justify-between text-semantic-success font-semibold">
                            <span>Kembalian</span>
                            <span class="font-mono">{{ formatCurrency(transaction.change_amount) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
