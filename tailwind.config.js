/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './**/*.php',
    '!./node_modules/**',
  ],
  theme: {
    extend: {
      colors: {
        primary: '#F22332',
        dark:    '#1A1A1A',
        purple:  '#7B6FA0',
      },
      fontFamily: {
        gontor: ['Gontor', 'Georgia', 'serif'],
        mono:   ['Gontor', 'Georgia', 'serif'],
      },
    },
  },
  plugins: [],
};
