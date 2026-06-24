<script setup>
import BaseButton from '@/Components/Base/BaseButton.vue';
import BaseModal from '@/Components/Base/BaseModal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Delete Account
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Once your account is deleted, all of its resources and data will
                be permanently deleted. Before deleting your account, please
                download any data or information that you wish to retain.
            </p>
        </header>

        <BaseButton variant="danger" @click="confirmUserDeletion">Delete Account</BaseButton>

        <BaseModal :is-open="confirmingUserDeletion" title="Delete Account" @close="closeModal">
            <div class="space-y-base">
                <p class="text-sm text-ink-secondary">
                    Once your account is deleted, all of its resources and data
                    will be permanently deleted. Please enter your password to
                    confirm you would like to permanently delete your account.
                </p>

                <div>
                    <InputLabel
                        for="password"
                        value="Password"
                    />

                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        placeholder="Password"
                        @keyup.enter="deleteUser"
                    />

                    <InputError :message="form.errors.password" class="mt-2" />
                </div>
            </div>

            <template #footer>
                <BaseButton variant="secondary" @click="closeModal">
                    Cancel
                </BaseButton>

                <BaseButton
                    variant="danger"
                    :loading="form.processing"
                    @click="deleteUser"
                >
                    Delete Account
                </BaseButton>
            </template>
        </BaseModal>
    </section>
</template>
