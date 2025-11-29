// tailwind.config.js
module.exports = {
  content: [
    './templates/**/*.html.twig',
    './assets/**/*.js',
    './assets/**/*.ts',
    './assets/**/*.vue',
    './assets/**/*.jsx',
    './assets/**/*.tsx',
  ],
  theme: {
    extend: {
      fontFamily: {
        poppins: ['Poppins', 'sans-serif'],
      colors: {
        carpilot: '#0b2c4c',
        accent: '#ff6600',
      },
    },
  },
  plugins: [],
}