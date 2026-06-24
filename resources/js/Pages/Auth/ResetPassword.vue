<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Reset Password" />

        <div class="mb-6 text-center">
            <h2 class="text-2xl font-bold text-ink-primary">Atur Ulang Password</h2>
            <p class="text-sm text-ink-secondary mt-1">Masukkan email dan password baru Anda</p>
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

            <div>
                <InputLabel for="password" value="New Password" />
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
                <InputLabel for="password_confirmation" value="Confirm New Password" />
                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="Ulangi password baru"
                />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div class="pt-md">
                <BaseButton
                    variant="primary"
                    :loading="form.processing"
                    :disabled="form.processing"
                    class="w-full justify-center py-md text-sm font-bold shadow-soft"
                >
                    Reset Password
                </BaseButton>
            </div>
        </form>
    </GuestLayout>
</template>
