/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './index.html',
    './src/**/*.{vue,js,ts,jsx,tsx}',
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#eef4ff',
          100: '#dde9ff',
          200: '#c2d7ff',
          300: '#9abfff',
          400: '#6d9dfb',
          500: '#3e7af4',
          600: '#2864e6',
          700: '#1f4dbb',
          800: '#1d408f',
          900: '#183571',
        },
        secondary: {
          50: '#f7f1ff',
          100: '#efe4ff',
          400: '#a47cff',
          500: '#8554f5',
          600: '#7140df',
          700: '#5830b5',
        },
        brand: {
          ink: '#14234b',
          muted: '#68738d',
          surface: '#f6f8ff',
        },
      },
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        display: ['Poppins', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
      boxShadow: {
        soft: '0 12px 30px -18px rgba(30, 70, 150, 0.32)',
        glow: '0 18px 38px -16px rgba(97, 74, 229, 0.45)',
      },
      animation: {
        'fade-in': 'fadeIn 0.4s ease-out',
        'slide-up': 'slideUp 0.4s ease-out',
        'scale-in': 'scaleIn 0.2s ease-out',
      },
      keyframes: {
        fadeIn: {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        slideUp: {
          '0%': { opacity: '0', transform: 'translateY(12px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
        scaleIn: {
          '0%': { opacity: '0', transform: 'scale(0.96)' },
          '100%': { opacity: '1', transform: 'scale(1)' },
        },
      },
    },
  },
  plugins: [],
}
