import { ref } from 'vue';

const toasts = ref([]);
let toastId = 0;

export function useToast() {
    const showToast = (message, type = 'info', duration = 3000) => {
        const id = toastId++;
        const toast = { id, message, type };

        toasts.value.push(toast);

        if (duration > 0) {
            setTimeout(() => {
                removeToast(id);
            }, duration);
        }

        return id;
    };

    const removeToast = (id) => {
        toasts.value = toasts.value.filter(t => t.id !== id);
    };

    const success = (message, duration) => showToast(message, 'success', duration);
    const error = (message, duration) => showToast(message, 'danger', duration);
    const warning = (message, duration) => showToast(message, 'warning', duration);
    const info = (message, duration) => showToast(message, 'info', duration);

    return { toasts, showToast, removeToast, success, error, warning, info };
}
