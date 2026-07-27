import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Manrope', 'Inter', ...defaultTheme.fontFamily.sans],
                display: ['Manrope', 'Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                charcoal: {
                    50: '#f8f7f5',
                    100: '#eeeae6',
                    500: '#62564f',
                    700: '#292524',
                    800: '#1c1c1c',
                    900: '#141414',
                    950: '#090909',
                },
                orange: {
                    50: '#fff4ed',
                    100: '#ffead9',
                    300: '#fdba74',
                    400: '#fb923c',
                    500: '#f97316',
                    600: '#ea580c',
                    700: '#c2410c',
                },
                ivory: '#fff8f0',
                surface: '#fffdfc',
                warm: '#62564f',
                line: '#e8d8ca',
                // Legacy semantic aliases remain neutral during the staged V2 rollout.
                forest: {
                    50: '#f8f7f5',
                    100: '#eeeae6',
                    500: '#62564f',
                    700: '#292524',
                    800: '#1c1c1c',
                    900: '#141414',
                    950: '#090909',
                },
                gold: {
                    100: '#ffead9',
                    300: '#fdba74',
                    400: '#fb923c',
                    500: '#f97316',
                    600: '#ea580c',
                    700: '#c2410c',
                },
                cream: '#fff8f0',
                paper: '#fffdfc',
                ink: '#171717',
                muted: '#62564f',
            },
            boxShadow: {
                card: '0 12px 34px rgba(68, 45, 28, .09)',
                lift: '0 22px 44px rgba(68, 45, 28, .16)',
            },
        },
    },

    plugins: [forms],
};
