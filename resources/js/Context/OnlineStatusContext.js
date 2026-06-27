import { ref, onMounted, onUnmounted, readonly } from "vue";

const isOnline = ref(navigator.onLine);

const handleOnline = () => (isOnline.value = true);
const handleOffline = () => (isOnline.value = false);

let initialized = false;

export function useOnlineStatus() {
    if (!initialized) {
        onMounted(() => {
            window.addEventListener("online", handleOnline);
            window.addEventListener("offline", handleOffline);
        });
        onUnmounted(() => {
            window.removeEventListener("online", handleOnline);
            window.removeEventListener("offline", handleOffline);
        });
        initialized = true;
    }
    return readonly(isOnline);
}
