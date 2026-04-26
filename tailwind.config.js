import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    // ── Modo oscuro activado por clase ──────────────────────────────────
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './resources/js/**/*.ts',
    ],

    theme: {
        extend: {
            // ── Tipografía ────────────────────────────────────────────
            fontFamily: {
                sans:    ['Inter', 'Nunito', ...defaultTheme.fontFamily.sans],
                display: ['Fredoka', 'Inter', ...defaultTheme.fontFamily.sans],
                mono:    ['JetBrains Mono', ...defaultTheme.fontFamily.mono],
            },

            // ── Paleta Monasterios ────────────────────────────────────
            colors: {
                // Colores de marca
                primary: {
                    DEFAULT: '#FF2E7A',
                    50:  '#FFF0F5',
                    100: '#FFD6E7',
                    200: '#FFADCF',
                    300: '#FF85B8',
                    400: '#FF5C9F',
                    500: '#FF2E7A',   // ← Principal
                    600: '#E0005F',
                    700: '#B8004D',
                    800: '#8F003C',
                    900: '#66002B',
                    950: '#3D0019',
                },
                secondary: {
                    DEFAULT: '#FF92B7',
                    50:  '#FFF5F9',
                    100: '#FFDCEA',
                    200: '#FFB8D4',
                    300: '#FF92B7',   // ← Secondary
                    400: '#FF6B9D',
                    500: '#FF4585',
                    600: '#E02060',
                    700: '#B8004D',
                    800: '#8F003C',
                    900: '#66002B',
                },
                accent: {
                    DEFAULT: '#FFD232',
                    50:  '#FFFBF0',
                    100: '#FFF7E0',
                    200: '#FFF0B8',
                    300: '#FFE68A',
                    400: '#FFDB5C',
                    500: '#FFD232',   // ← Accent (Original Brand Yellow)
                    600: '#E0B81F',
                    700: '#B89614',
                    800: '#8F750D',
                    900: '#665306',
                },

                // Superficies
                surface: {
                    DEFAULT: '#FFFFFF',
                    dark:    '#1E1E2E',
                },

                // Fondo general
                bg: {
                    light: '#FAF9FF',
                    dark:  '#0F0F1A',
                },

                // Texto
                content: {
                    primary:   '#1A1A2E',
                    secondary: '#6B6B8A',
                    muted:     '#A0A0C0',
                    inverted:  '#FFFFFF',
                },

                // Estados
                success: { DEFAULT: '#10B981', light: '#D1FAE5', dark: '#065F46' },
                warning: { DEFAULT: '#F59E0B', light: '#FEF3C7', dark: '#78350F' },
                danger:  { DEFAULT: '#EF4444', light: '#FEE2E2', dark: '#7F1D1D' },
                info:    { DEFAULT: '#3B82F6', light: '#DBEAFE', dark: '#1E3A5F' },
            },

            // ── Sombras con acento Monasterios ────────────────────────
            boxShadow: {
                'primary-sm': '0 2px 8px rgba(255, 46, 122, 0.2)',
                'primary-md': '0 4px 20px rgba(255, 46, 122, 0.3)',
                'primary-lg': '0 8px 40px rgba(255, 46, 122, 0.4)',
                'accent-sm':  '0 2px 8px rgba(255, 210, 50, 0.3)',
                'glass':      '0 8px 32px rgba(0, 0, 0, 0.12)',
                'glass-dark': '0 8px 32px rgba(0, 0, 0, 0.4)',
            },

            // ── Bordes redondeados premium ────────────────────────────
            borderRadius: {
                '4xl': '2rem',
                '5xl': '2.5rem',
            },

            // ── Animaciones micro-interacciones ───────────────────────
            keyframes: {
                'slide-up': {
                    '0%':   { opacity: '0', transform: 'translateY(16px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                'slide-down': {
                    '0%':   { opacity: '0', transform: 'translateY(-16px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                'fade-in': {
                    '0%':   { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                'pop-in': {
                    '0%':   { opacity: '0', transform: 'scale(0.92)' },
                    '60%':  { transform: 'scale(1.03)' },
                    '100%': { opacity: '1', transform: 'scale(1)' },
                },
                'shimmer': {
                    '0%':   { backgroundPosition: '-200% 0' },
                    '100%': { backgroundPosition: '200% 0' },
                },
                'pulse-pink': {
                    '0%, 100%': { boxShadow: '0 0 0 0 rgba(255, 46, 122, 0.4)' },
                    '50%':      { boxShadow: '0 0 0 8px rgba(255, 46, 122, 0)' },
                },
            },
            animation: {
                'slide-up':   'slide-up 0.3s ease-out both',
                'slide-down': 'slide-down 0.3s ease-out both',
                'fade-in':    'fade-in 0.25s ease-out both',
                'pop-in':     'pop-in 0.35s cubic-bezier(0.34, 1.56, 0.64, 1) both',
                'shimmer':    'shimmer 1.8s ease-in-out infinite',
                'pulse-pink': 'pulse-pink 2s ease-in-out infinite',
            },

            // ── Transiciones ──────────────────────────────────────────
            transitionTimingFunction: {
                'spring': 'cubic-bezier(0.34, 1.56, 0.64, 1)',
                'smooth': 'cubic-bezier(0.4, 0, 0.2, 1)',
            },

            // ── Tipografía escalada ───────────────────────────────────
            fontSize: {
                '2xs': ['0.625rem', { lineHeight: '0.875rem' }],
            },
        },
    },

    plugins: [
        forms,
    ],
};
