/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: false, // disable dark mode
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
  ],
  theme: {
    extend: {
      colors: {
        'primary-light': '#E9DCCD',
      },
    },
  },
  plugins: [],
};
