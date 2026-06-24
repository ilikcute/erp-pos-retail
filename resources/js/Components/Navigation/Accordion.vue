<script setup>
import {
    Disclosure,
    DisclosureButton,
    DisclosurePanel,
} from '@headlessui/vue';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    defaultOpen: {
        type: Boolean,
        default: false,
    },
    icon: {
        type: String,
        default: null,
    },
});
</script>

<template>
    <Disclosure v-slot="{ open }" :defaultOpen="defaultOpen">
        <div class="border border-border-soft rounded-md">
            <DisclosureButton
                class="flex w-full items-center justify-between px-4 py-3 bg-surface-subtle hover:bg-gray-100 transition-colors">
                <span class="flex items-center gap-3 font-medium text-ink-primary">
                    <i v-if="icon" :class="[icon, 'w-5 h-5']" />
                    {{ title }}
                </span>
                <svg :class="open ? 'rotate-180 transform' : ''"
                    class="h-5 w-5 text-ink-muted transition-transform duration-200" viewBox="0 0 20 20"
                    fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg>
            </DisclosureButton>

            <DisclosurePanel class="px-4 py-3 border-t border-border-soft">
                <slot />
            </DisclosurePanel>
        </div>
    </Disclosure>
</template>
