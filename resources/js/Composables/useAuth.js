import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function useAuth() {
    const page = usePage();

    const user = computed(() => page.props.auth.user);

    const isAuthenticated = computed(() => !!user.value);

    const isAdmin = computed(() => {
        return user.value?.roles?.some(r => r.name === 'Admin');
    });

    return {
        user,
        isAuthenticated,
        isAdmin,
    };
}
