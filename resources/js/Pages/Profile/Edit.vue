<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="My Profile" />

    <DashboardLayout>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-ink-primary">
                My Profile
            </h1>
            <p class="text-ink-secondary text-sm">
                Kelola informasi akun, perbarui kata sandi, dan kelola privasi keanggotaan Anda.
            </p>
        </div>

        <div class="space-y-xl max-w-4xl">
            <div class="bg-surface-card border border-border-soft p-xl rounded-2xl shadow-soft">
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    class="max-w-xl"
                />
            </div>

            <div class="bg-surface-card border border-border-soft p-xl rounded-2xl shadow-soft">
                <UpdatePasswordForm class="max-w-xl" />
            </div>

            <div class="bg-surface-card border border-border-soft p-xl rounded-2xl shadow-soft">
                <DeleteUserForm class="max-w-xl" />
            </div>
        </div>
    </DashboardLayout>
</template>
