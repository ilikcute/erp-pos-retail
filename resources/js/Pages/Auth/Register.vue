<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <div class="mb-6 text-center">
            <h2 class="text-2xl font-bold text-ink-primary">Buat Akun Baru</h2>
            <p class="text-sm text-ink-secondary mt-1">Daftarkan akun sistem ERP POS Anda</p>
        </div>

        <form @submit.prevent="submit" class="space-y-base">
            <div>
                <InputLabel for="name" value="Name" />
                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="Nama Lengkap"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Email Address" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                    placeholder="nama@perusahaan.com"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Password" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                    placeholder="Minimal 8 karakter"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div>
                <InputLabel for="password_confirmation" value="Confirm Password" />
                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="Ulangi password"
                />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div class="pt-md flex items-center justify-between gap-base">
                <Link
                    :href="route('login')"
                    class="text-sm text-ink-secondary hover:text-brand transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-brand rounded-md px-1"
                >
                    Already registered?
                </Link>

                <BaseButton
                    variant="primary"
                    :loading="form.processing"
                    :disabled="form.processing"
                    class="px-base py-md text-sm font-bold shadow-soft"
                >
                    Register
                </BaseButton>
            </div>
        </form>
    </GuestLayout>
</template>
