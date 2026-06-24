import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: [
                    'ui-sans-serif',
                    'system-ui',
                    '-apple-system',
                    'BlinkMacSystemFont',
                    '"Segoe UI"',
                    'Roboto',
                    '"Helvetica Neue"',
                    'Arial',
                    '"Noto Sans"',
                    'sans-serif',
                    '"Apple Color Emoji"',
                    '"Segoe UI Emoji"',
                    '"Segoe UI Symbol"',
                    '"Noto Color Emoji"',
                ],
            },
            colors: {
                surface: {
                    main: '#F5F5F7',
                    card: '#FFFFFF',
                    subtle: '#FAFAFA',
                },
                border: {
                    soft: '#E5E5EA',
                    strong: '#D1D1D6',
                },
                ink: {
                    primary: '#1D1D1F',
                    secondary: '#6E6E73',
                    muted: '#8E8E93',
                },
                brand: {
                    DEFAULT: '#0071E3',
                    hover: '#0066CC',
                    soft: '#E8F2FF',
                    border: '#B9DCFF',
                },
                semantic: {
                    success: '#34C759',
                    'success-soft': '#EAF8EF',
                    warning: '#FF9500',
                    'warning-soft': '#FFF4E5',
                    danger: '#FF3B30',
                    'danger-soft': '#FFECEC',
                    info: '#5AC8FA',
                    'info-soft': '#EAF8FF',
                },
                accounting: {
                    debit: '#1D1D1F',
                    credit: '#1D1D1F',
                    'balance-ok': '#34C759',
                    'balance-warning': '#FF9500',
                    'balance-error': '#FF3B30',
                },
            },
            borderRadius: {
                xs: '6px',
                sm: '8px',
                md: '12px',
                lg: '16px',
                xl: '20px',
                '2xl': '28px',
                card: '20px',
                modal: '24px',
                pill: '999px',
            },
            boxShadow: {
                soft: '0 1px 2px rgba(0, 0, 0, 0.04)',
                card: '0 8px 24px rgba(0, 0, 0, 0.06)',
                floating: '0 16px 48px rgba(0, 0, 0, 0.12)',
            },
            spacing: {
                xs: '4px',
                sm: '8px',
                md: '12px',
                base: '16px',
                lg: '20px',
                xl: '24px',
                '2xl': '32px',
                '3xl': '40px',
                '4xl': '48px',
                '5xl': '64px',
            },
            fontSize: {
                display: ['40px', { lineHeight: '1.2', fontWeight: '700' }],
                'display-sm': ['32px', { lineHeight: '1.2', fontWeight: '700' }],
                'page-title': ['28px', { lineHeight: '1.3', fontWeight: '700' }],
                'page-title-sm': ['24px', { lineHeight: '1.3', fontWeight: '700' }],
                'section-title': ['20px', { lineHeight: '1.4', fontWeight: '600' }],
                'section-title-sm': ['18px', { lineHeight: '1.4', fontWeight: '600' }],
                'card-title': ['16px', { lineHeight: '1.5', fontWeight: '600' }],
                base: ['14px', { lineHeight: '1.5', fontWeight: '400' }],
                sm: ['13px', { lineHeight: '1.4', fontWeight: '400' }],
                xs: ['12px', { lineHeight: '1.4', fontWeight: '400' }],
            },
            screens: {
                xs: '360px',
                sm: '640px',
                md: '768px',
                lg: '1024px',
                xl: '1280px',
                '2xl': '1440px',
            },
        },
    },

    plugins: [forms],
};
