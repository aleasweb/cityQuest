/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/react-app/**/*.{js,ts,jsx,tsx}",
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        orange: {
          500: '#ed8e34',
          600: '#d67a23',
          700: '#b86419',
        },
        gray: {
          800: '#1a1a1a',
          900: '#0f0f0f',
        }
      },
    },
  },
  plugins: [],
};
