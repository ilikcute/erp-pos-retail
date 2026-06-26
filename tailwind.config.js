import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: [
                    'Inter',
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
                // Friendlier warm-neutral surfaces
                surface: {
                    main: '#F4F5FB',
                    card: '#FFFFFF',
                    subtle: '#FAFAFC',
                    muted: '#F0F1F8',
                },
                border: {
                    soft: '#E7E8F2',
                    strong: '#D4D6E6',
                },
                ink: {
                    primary: '#1B1B2F',
                    secondary: '#5B5B73',
                    muted: '#8A8AA0',
                },
                // Vibrant friendly brand (indigo-violet)
                brand: {
                    DEFAULT: '#6C5CE7',
                    hover: '#5A4BD4',
                    soft: '#EEEBFF',
                    border: '#CFC6FF',
                },
                // Playful accent palette for category tiles, stat cards, badges
                accent: {
                    violet: '#7C5CFC', 'violet-soft': '#EFEBFF',
                    grape:  '#A855F7', 'grape-soft':  '#F5ECFF',
                    pink:   '#EC4899', 'pink-soft':   '#FDE9F3',
                    coral:  '#FB7185', 'coral-soft':  '#FFEBEE',
                    sunny:  '#F59E0B', 'sunny-soft':  '#FFF3DC',
                    mint:   '#10B981', 'mint-soft':   '#E3FBF1',
                    teal:   '#14B8A6', 'teal-soft':   '#E0FBF7',
                    sky:    '#0EA5E9', 'sky-soft':    '#E5F6FF',
                    indigo: '#6366F1', 'indigo-soft': '#EBEDFF',
                },
                semantic: {
                    success: '#10B981', 'success-soft': '#E3FBF1',
                    warning: '#F59E0B', 'warning-soft': '#FFF3DC',
                    danger:  '#F43F5E', 'danger-soft':  '#FFE9ED',
                    info:    '#0EA5E9', 'info-soft':    '#E5F6FF',
                },
                accounting: {
                    debit: '#1B1B2F',
                    credit: '#1B1B2F',
                    'balance-ok': '#10B981',
                    'balance-warning': '#F59E0B',
                    'balance-error': '#F43F5E',
                },
            },
            backgroundImage: {
                'brand-gradient':  'linear-gradient(135deg, #6C5CE7 0%, #8B5CF6 55%, #A855F7 100%)',
                'sidebar-gradient':'linear-gradient(180deg, #4F46E5 0%, #6D28D9 60%, #7C3AED 100%)',
                'sunset-gradient': 'linear-gradient(135deg, #FB7185 0%, #F59E0B 100%)',
                'mint-gradient':   'linear-gradient(135deg, #10B981 0%, #0EA5E9 100%)',
                'grape-gradient':  'linear-gradient(135deg, #A855F7 0%, #EC4899 100%)',
            },
            borderRadius: {
                xs: '6px', sm: '8px', md: '12px', lg: '16px', xl: '20px',
                '2xl': '28px', card: '20px', modal: '24px', pill: '999px',
            },
            boxShadow: {
                soft: '0 1px 2px rgba(27, 27, 47, 0.05)',
                card: '0 8px 24px rgba(27, 27, 47, 0.07)',
                floating: '0 16px 48px rgba(27, 27, 47, 0.14)',
                'brand-glow': '0 10px 30px -8px rgba(108, 92, 231, 0.45)',
                'mint-glow': '0 10px 30px -8px rgba(16, 185, 129, 0.45)',
            },
            spacing: {
                xs: '4px', sm: '8px', md: '12px', base: '16px', lg: '20px',
                xl: '24px', '2xl': '32px', '3xl': '40px', '4xl': '48px', '5xl': '64px',
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
                sm: '640px', md: '768px', lg: '1024px', xl: '1280px', '2xl': '1440px',
            },
        },
    },

    plugins: [forms],
};
