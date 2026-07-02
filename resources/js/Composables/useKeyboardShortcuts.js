import { onMounted, onUnmounted } from "vue";

export function useKeyboardShortcuts(handlers) {
    const handleKeyDown = (e) => {
        const tag = e.target.tagName;
        const isInput = tag === "INPUT" || tag === "TEXTAREA" || e.target.isContentEditable;
        const isSpecialKey = e.key.startsWith("F") || e.key === "Escape";

        if (isInput && !isSpecialKey)
            return;

        const map = {
            "/": "focusSearch",
            F5: "focusSearch",
            F1: "openNumpad",
            F2: "submitTransaction",
            F3: "toggleMobileView",
            F4: "showShortcuts",
            Escape: "closeAll",
        };

        const action = map[e.key];
        if (action && handlers[action]) {
            e.preventDefault();
            handlers[action]();
        }
    };

    onMounted(() => window.addEventListener("keydown", handleKeyDown));
    onUnmounted(() => window.removeEventListener("keydown", handleKeyDown));
}
