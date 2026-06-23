<script setup>
import DashboardLayout from "@/Layouts/DashboardLayout.vue";
import { Head } from "@inertiajs/vue3";
</script>

<template>
    <Head title="Dashboard" />

    <DashboardLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h1 class="text-2xl font-bold mb-4">
                            Selamat Datang di ERP POS Retail!
                        </h1>
                        <p class="text-gray-600">
                            Sistem Anda telah berhasil di-setup menggunakan
                            arsitektur Modular Layered Monolith.
                        </p>

                        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div
                                class="bg-blue-50 p-4 rounded-lg border border-blue-200"
                            >
                                <h3 class="font-semibold text-blue-800">
                                    Phase 1 Selesai
                                </h3>
                                <p class="text-sm text-blue-600 mt-1">
                                    Auth, System, MasterData
                                </p>
                            </div>
                            <div
                                class="bg-gray-50 p-4 rounded-lg border border-gray-200"
                            >
                                <h3 class="font-semibold text-gray-800">
                                    Phase 2
                                </h3>
                                <p class="text-sm text-gray-600 mt-1">
                                    Product & Pricing
                                </p>
                            </div>
                            <div
                                class="bg-gray-50 p-4 rounded-lg border border-gray-200"
                            >
                                <h3 class="font-semibold text-gray-800">
                                    Phase 3
                                </h3>
                                <p class="text-sm text-gray-600 mt-1">
                                    POS Basic
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
