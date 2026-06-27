import { ref, onMounted, onUnmounted } from "vue";

/**
 * Composable untuk mendeteksi input dari barcode scanner.
 * Barcode scanner bekerja seperti keyboard cepat — mengirim karakter + Enter dalam <50ms.
 */
export function useBarcodeScanner(onScan, options = {}) {
    const { enabled = true, minLength = 3, timeoutMs = 50 } = options;
    const isScanning = ref(false);
    const buffer = ref("");
    let lastKeyTime = 0;
    let timer = null;

    const handleKeyDown = (e) => {
        if (!enabled) return;
        // Abaikan jika user sedang mengetik di input
        const tag = e.target.tagName;
        if (tag === "INPUT" || tag === "TEXTAREA" || e.target.isContentEditable)
            return;

        if (e.key === "Enter") {
            if (buffer.value.length >= minLength) {
                isScanning.value = true;
                onScan?.(buffer.value);
                setTimeout(() => (isScanning.value = false), 300);
            }
            buffer.value = "";
            lastKeyTime = 0;
            return;
        }

        // Hanya terima karakter tunggal (bukan modifier keys)
        if (e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
            const now = Date.now();
            // Reset buffer jika jeda antar-key terlalu lama (bukan scanner)
            if (lastKeyTime && now - lastKeyTime > timeoutMs * 3) {
                buffer.value = "";
            }
            buffer.value += e.key;
            lastKeyTime = now;

            clearTimeout(timer);
            timer = setTimeout(() => {
                buffer.value = "";
            }, 500);
        }
    };

    onMounted(() => window.addEventListener("keydown", handleKeyDown));
    onUnmounted(() => {
        window.removeEventListener("keydown", handleKeyDown);
        clearTimeout(timer);
    });

    return { isScanning, buffer };
}
