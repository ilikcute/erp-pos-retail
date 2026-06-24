<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Lupa Password" />

        <div class="mb-6 text-center">
            <h2 class="text-2xl font-bold text-ink-primary">Lupa Password?</h2>
            <p class="text-sm text-ink-secondary mt-1">
                Masukkan email Anda dan kami akan mengirimkan tautan untuk mengatur ulang password Anda.
            </p>
        </div>

        <div
            v-if="status"
            class="mb-4 text-sm font-semibold text-semantic-success bg-semantic-success-soft border border-semantic-success/15 p-md rounded-xl animate-fade-in"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-base">
            <div>
                <InputLabel for="email" value="Email Address" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="nama@perusahaan.com"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="pt-md flex items-center justify-between gap-base">
                <Link
                    :href="route('login')"
                    class="text-sm text-ink-secondary hover:text-brand transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-brand rounded-md px-1"
                >
                    Kembali ke Login
                </Link>

                <BaseButton
                    variant="primary"
                    :loading="form.processing"
                    :disabled="form.processing"
                    class="px-base py-md text-sm font-bold shadow-soft"
                >
                    Kirim Link Reset
                </BaseButton>
            </div>
        </form>
    </GuestLayout>
</template>
