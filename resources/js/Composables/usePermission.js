import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function usePermission() {
    const page = usePage();

    const userPermissions = computed(() => {
        return page.props.auth?.permissions || [];
    });

    const userRoles = computed(() => {
        return page.props.auth?.roles || [];
    });

    const hasPermission = (permission) => {
        return userPermissions.value.includes(permission);
    };

    const hasRole = (role) => {
        return userRoles.value.includes(role);
    };

    const hasAnyPermission = (permissions) => {
        return permissions.some(p => hasPermission(p));
    };

    const hasAllPermissions = (permissions) => {
        return permissions.every(p => hasPermission(p));
    };

    return {
        userPermissions,
        userRoles,
        hasPermission,
        hasRole,
        hasAnyPermission,
        hasAllPermissions,
    };
}
