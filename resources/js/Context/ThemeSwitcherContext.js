import { ref, readonly } from "vue";

const darkMode = ref(localStorage.getItem("darkMode") === "true");

const themeSwitcher = () => {
    darkMode.value = !darkMode.value;
    localStorage.setItem("darkMode", darkMode.value);
    document.documentElement.classList.toggle("dark", darkMode.value);
};

// Inisialisasi dark mode
if (darkMode.value) {
    document.documentElement.classList.add("dark");
}

export function useTheme() {
    return { darkMode: readonly(darkMode), themeSwitcher };
}
