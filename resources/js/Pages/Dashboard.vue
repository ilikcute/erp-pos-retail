<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Head } from '@inertiajs/vue3';
import BaseCard from '@/Components/Base/BaseCard.vue';
import KPICard from '@/Components/Dashboard/KPICard.vue';
import Icon from '@/Components/Base/Icon.vue';
</script>

<template>
    <Head title="Dashboard" />

    <DashboardLayout>
        <!-- Page Header -->
        <div class="mb-2xl">
            <h1 class="text-page-title text-ink-primary">Selamat Pagi, Admin</h1>
            <p class="text-base text-ink-secondary mt-md">Ringkasan operasional toko hari ini</p>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-xl mb-2xl">
            <KPICard
                label="Penjualan Hari Ini"
                value="Rp 12.450.000"
                trend="up"
                trendValue="+8.2% dibanding kemarin"
                icon="trending-up"
                color="success"
            />
            <KPICard
                label="Transaksi"
                value="48"
                trend="up"
                trendValue="+12% dibanding kemarin"
                icon="shopping-cart"
                color="brand"
            />
            <KPICard
                label="Gross Margin"
                value="32.5%"
                trend="up"
                trendValue="+1.2% dibanding kemarin"
                icon="percent"
                color="info"
            />
            <KPICard
                label="Stok Kritis"
                value="5"
                trend="down"
                trendValue="-2 item"
                icon="alert-circle"
                color="warning"
            />
        </div>

        <!-- Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-xl">
            <!-- Sales Trend (2 cols) -->
            <BaseCard class="lg:col-span-2">
                <div class="mb-lg">
                    <h2 class="text-card-title font-semibold text-ink-primary">Tren Penjualan 7 Hari</h2>
                </div>
                <div class="h-64 bg-surface-subtle rounded-md flex items-center justify-center">
                    <p class="text-ink-muted">Chart placeholder</p>
                </div>
            </BaseCard>

            <!-- Payment Summary -->
            <BaseCard>
                <div class="mb-lg">
                    <h2 class="text-card-title font-semibold text-ink-primary">Metode Pembayaran</h2>
                </div>
                <div class="space-y-md">
                    <div class="flex justify-between items-center pb-md border-b border-border-soft">
                        <span class="text-sm text-ink-secondary">Cash</span>
                        <span class="text-sm font-semibold text-ink-primary">Rp 8.450.000</span>
                    </div>
                    <div class="flex justify-between items-center pb-md border-b border-border-soft">
                        <span class="text-sm text-ink-secondary">Kartu Debit</span>
                        <span class="text-sm font-semibold text-ink-primary">Rp 2.850.000</span>
                    </div>
                    <div class="flex justify-between items-center pb-md border-b border-border-soft">
                        <span class="text-sm text-ink-secondary">E-Wallet</span>
                        <span class="text-sm font-semibold text-ink-primary">Rp 1.150.000</span>
                    </div>
                </div>
            </BaseCard>
        </div>

        <!-- Operational Status -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-xl mt-2xl">
            <!-- Low Stock Alert -->
            <BaseCard>
                <div class="mb-lg">
                    <h2 class="text-card-title font-semibold text-ink-primary">Stok Perlu Restock</h2>
                </div>
                <div class="space-y-md">
                    <div class="flex justify-between items-center pb-md border-b border-border-soft">
                        <div>
                            <p class="text-sm font-medium text-ink-primary">Kopi Arabika Premium</p>
                            <p class="text-xs text-ink-muted mt-xs">Stok: 5 pcs</p>
                        </div>
                        <span class="px-md py-sm bg-semantic-warning-soft text-semantic-warning text-xs font-medium rounded-md">Rendah</span>
                    </div>
                    <div class="flex justify-between items-center pb-md border-b border-border-soft">
                        <div>
                            <p class="text-sm font-medium text-ink-primary">Susu Fresh 1L</p>
                            <p class="text-xs text-ink-muted mt-xs">Stok: 3 pcs</p>
                        </div>
                        <span class="px-md py-sm bg-semantic-danger-soft text-semantic-danger text-xs font-medium rounded-md">Kritis</span>
                    </div>
                </div>
            </BaseCard>

            <!-- Pending Approval -->
            <BaseCard>
                <div class="mb-lg">
                    <h2 class="text-card-title font-semibold text-ink-primary">Menunggu Persetujuan</h2>
                </div>
                <div class="space-y-md">
                    <div class="flex justify-between items-center pb-md border-b border-border-soft">
                        <div>
                            <p class="text-sm font-medium text-ink-primary">PO-2026-00145</p>
                            <p class="text-xs text-ink-muted mt-xs">dari Supplier XYZ</p>
                        </div>
                        <span class="px-md py-sm bg-semantic-info-soft text-semantic-info text-xs font-medium rounded-md">1 item</span>
                    </div>
                    <div class="flex justify-between items-center pb-md border-b border-border-soft">
                        <div>
                            <p class="text-sm font-medium text-ink-primary">Transfer Stok ST-001</p>
                            <p class="text-xs text-ink-muted mt-xs">ke Gudang Cabang</p>
                        </div>
                        <span class="px-md py-sm bg-semantic-info-soft text-semantic-info text-xs font-medium rounded-md">Persetujuan</span>
                    </div>
                </div>
            </BaseCard>
        </div>
    </DashboardLayout>
</template>

