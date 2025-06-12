/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        primary: "#F53003",
        "primary-dark": "#F61500",
      },
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
        display: ['Instrument Sans', 'sans-serif'],
      }
    },
  },
  darkMode: 'class',
  plugins: [
    require('@tailwindcss/forms'),
  ],
}