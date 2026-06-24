<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Email Verification" />

        <div class="mb-6 text-center">
            <h2 class="text-2xl font-bold text-ink-primary">Verifikasi Email</h2>
            <p class="text-sm text-ink-secondary mt-2 leading-relaxed">
                Terima kasih telah mendaftar! Sebelum memulai, silakan verifikasi alamat email Anda dengan mengklik tautan yang baru saja kami kirimkan ke email Anda. Jika Anda tidak menerima email tersebut, kami dengan senang hati akan mengirimkan ulang.
            </p>
        </div>

        <div
            v-if="verificationLinkSent"
            class="mb-4 text-sm font-semibold text-semantic-success bg-semantic-success-soft border border-semantic-success/15 p-md rounded-xl"
        >
            Tautan verifikasi baru telah dikirim ke alamat email yang Anda berikan saat pendaftaran.
        </div>

        <form @submit.prevent="submit" class="space-y-base">
            <div class="pt-md flex items-center justify-between gap-base">
                <BaseButton
                    variant="primary"
                    :loading="form.processing"
                    :disabled="form.processing"
                    class="px-base py-md text-sm font-bold shadow-soft"
                >
                    Resend Verification Email
                </BaseButton>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="text-sm text-ink-secondary hover:text-brand transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-brand rounded-md px-2 py-1 cursor-pointer"
                >
                    Log Out
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
