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
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['Playfair Display', 'Georgia', 'serif'],
            },
            colors: {
                forest: {
                    50: '#eef5ef',
                    100: '#dcebdd',
                    500: '#42643c',
                    700: '#1e3d2a',
                    800: '#153224',
                    900: '#0b2418',
                    950: '#07170f',
                },
                gold: {
                    100: '#f9ebcb',
                    300: '#e8c579',
                    400: '#d9a441',
                    500: '#b98427',
                    600: '#9d701d',
                    700: '#7a5313',
                },
                cream: '#fbf7ee',
                paper: '#fffdf8',
                ink: '#173024',
                muted: '#66746a',
            },
            boxShadow: {
                card: '0 12px 34px rgba(21, 50, 36, .10)',
                lift: '0 22px 44px rgba(21, 50, 36, .16)',
            },
        },
    },

    plugins: [forms],
};
