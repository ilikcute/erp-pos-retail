<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Confirm Password" />

        <div class="mb-6 text-center">
            <h2 class="text-2xl font-bold text-ink-primary">Konfirmasi Password</h2>
            <p class="text-sm text-ink-secondary mt-1">
                Ini adalah area aplikasi yang aman. Silakan konfirmasi password Anda sebelum melanjutkan.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-base">
            <div>
                <InputLabel for="password" value="Password" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    autofocus
                    placeholder="••••••••"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="pt-md">
                <BaseButton
                    variant="primary"
                    :loading="form.processing"
                    :disabled="form.processing"
                    class="w-full justify-center py-md text-sm font-bold shadow-soft"
                >
                    Confirm Password
                </BaseButton>
            </div>
        </form>
    </GuestLayout>
</template>
