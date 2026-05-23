import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    darkMode: 'class',
    theme: {
        extend: {
            fontFamily: {
                sans: ['Plus Jakarta Sans', 'Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50:  '#F0FDF4',
                    100: '#DCFCE7',
                    200: '#BBF7D0',
                    300: '#86EFAC',
                    400: '#4ADE80',
                    500: '#22C55E',
                    600: '#16A34A',
                    700: '#15803D',
                    800: '#166534',
                    900: '#14532D',
                },
                ink: {
                    900: '#0F172A',
                    800: '#1E293B',
                    600: '#475569',
                    500: '#64748B',
                    200: '#E2E8F0',
                    50:  '#F8FAFC',
                },
            },
            boxShadow: {
                'soft': '0 4px 24px -6px rgba(15, 23, 42, 0.08)',
                'glow': '0 8px 32px -8px rgba(34, 197, 94, 0.35)',
            },
            borderRadius: {
                '2xl': '1rem',
                '3xl': '1.5rem',
            },
            backgroundImage: {
                'brand-gradient': 'linear-gradient(135deg, #16A34A 0%, #22C55E 100%)',
                'soft-gradient': 'linear-gradient(135deg, #F0FDF4 0%, #FFFFFF 100%)',
            },
        },
    },
    plugins: [forms],
};
