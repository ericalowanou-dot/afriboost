import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/**/*.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Vert AfriBoost (carte wallet, navigation active)
                brand: {
                    50: '#effaf3',
                    100: '#d8f3e1',
                    200: '#b3e6c6',
                    300: '#80d2a2',
                    400: '#4bb77a',
                    500: '#279c5c',
                    600: '#1a8049',
                    700: '#16663d',
                    800: '#145133',
                    900: '#11432b',
                },
                // Rouge des CTA « Participer » / « Soumettre » et des récompenses
                cta: {
                    50: '#fff1f1',
                    100: '#ffdfdf',
                    400: '#f86b63',
                    500: '#ef3b31',
                    600: '#dc2318',
                    700: '#b91a11',
                },
                mint: '#cdeee0',
                cream: '#f6efc8',
            },
            boxShadow: {
                card: '0 1px 2px rgb(15 23 42 / 0.04), 0 4px 16px -4px rgb(15 23 42 / 0.08)',
                cta: '0 8px 20px -6px rgb(220 35 24 / 0.45)',
                nav: '0 -4px 24px -8px rgb(15 23 42 / 0.15)',
            },
            keyframes: {
                'slide-up': {
                    from: { transform: 'translateY(100%)' },
                    to: { transform: 'translateY(0)' },
                },
                'fade-in': {
                    from: { opacity: '0', transform: 'translateY(-4px)' },
                    to: { opacity: '1', transform: 'translateY(0)' },
                },
            },
            animation: {
                'slide-up': 'slide-up .25s ease-out',
                'fade-in': 'fade-in .2s ease-out',
            },
        },
    },

    plugins: [forms],
};
