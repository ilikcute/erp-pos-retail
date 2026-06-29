<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref, onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const emailInput = ref(null);

onMounted(() => {
    emailInput.value?.focus?.();
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log In" />

        <div class="mb-6 text-center">
            <h2 class="text-2xl font-bold text-ink-primary">Selamat Datang</h2>
            <p class="text-sm text-ink-secondary mt-1">Masuk ke akun ERP POS Anda</p>
        </div>

        <div v-if="status" class="mb-4 text-sm font-semibold text-semantic-success bg-semantic-success-soft border border-semantic-success/15 p-md rounded-xl">
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
                    autocomplete="username"
                    placeholder="nama@perusahaan.com"
                    ref="emailInput"
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
                    autocomplete="current-password"
                    placeholder="••••••••"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center cursor-pointer select-none">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2 text-sm text-ink-secondary hover:text-ink-primary transition-colors">Remember me</span>
                </label>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm text-ink-secondary hover:text-brand transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-brand rounded-md px-1"
                >
                    Forgot password?
                </Link>
            </div>

            <div class="pt-md">
                <BaseButton
                    class="w-full justify-center py-md text-sm font-bold shadow-soft hover:shadow-medium"
                    variant="primary"
                    :loading="form.processing"
                    :disabled="form.processing"
                >
                    Log In
                </BaseButton>
            </div>
            
            <div class="text-center pt-md border-t border-border-soft">
                <span class="text-sm text-ink-secondary">Belum memiliki akun? </span>
                <Link
                    :href="route('register')"
                    class="text-sm font-bold text-brand hover:underline focus:outline-none focus:ring-2 focus:ring-brand rounded-md px-1"
                >
                    Daftar Sekarang
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
