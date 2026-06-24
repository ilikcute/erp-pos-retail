import { ref, computed, watch } from 'vue';

const breakpoints = {
    xs: 360,
    sm: 640,
    md: 768,
    lg: 1024,
    xl: 1280,
    '2xl': 1440,
};

export function useResponsive() {
    const windowWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 0);

    const isXs = computed(() => windowWidth.value < breakpoints.sm);
    const isSm = computed(() => windowWidth.value >= breakpoints.sm && windowWidth.value < breakpoints.md);
    const isMd = computed(() => windowWidth.value >= breakpoints.md && windowWidth.value < breakpoints.lg);
    const isLg = computed(() => windowWidth.value >= breakpoints.lg && windowWidth.value < breakpoints.xl);
    const isXl = computed(() => windowWidth.value >= breakpoints.xl && windowWidth.value < breakpoints['2xl']);
    const is2xl = computed(() => windowWidth.value >= breakpoints['2xl']);

    const isMobile = computed(() => windowWidth.value < breakpoints.md);
    const isTablet = computed(() => windowWidth.value >= breakpoints.md && windowWidth.value < breakpoints.lg);
    const isDesktop = computed(() => windowWidth.value >= breakpoints.lg);

    if (typeof window !== 'undefined') {
        window.addEventListener('resize', () => {
            windowWidth.value = window.innerWidth;
        });
    }

    return {
        windowWidth,
        isXs,
        isSm,
        isMd,
        isLg,
        isXl,
        is2xl,
        isMobile,
        isTablet,
        isDesktop,
    };
}
