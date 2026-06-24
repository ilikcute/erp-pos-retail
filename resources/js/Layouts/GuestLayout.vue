<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-surface-main pt-6 sm:justify-center sm:pt-0"
    >
        <div>
            <Link href="/">
                <ApplicationLogo class="h-20 w-20 fill-current text-brand" />
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-surface-card px-6 py-4 shadow-card sm:max-w-md sm:rounded-card"
        >
            <slot />
        </div>
    </div>
</template>
