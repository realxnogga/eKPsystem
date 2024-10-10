/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './*.php',      // If you have PHP files in the root directory
    './**/*.php',    // If you have PHP files in subdirectories
    './node_modules/flowbite/**/*.js',
  ],
  theme: {
    extend: {},
  },
  plugins: [
    require('flowbite/plugin')
  ],
}

