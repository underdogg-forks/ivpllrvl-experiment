/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./Modules/**/Resources/views/**/*.php",
    "./resources/views/**/*.php",
    "./resources/assets/**/*.js",
  ],
  theme: {
    extend: {
      colors: {
        primary: '#337ab7',
        success: '#5cb85c',
        info: '#5bc0de',
        warning: '#f0ad4e',
        danger: '#d9534f',
      },
    },
  },
  plugins: [],
  // Disable preflight to avoid conflicts with existing styles during migration
  corePlugins: {
    preflight: false,
  },
}
