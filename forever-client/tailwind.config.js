/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./tienda.html", // ¡Clave! Así Tailwind pinta tu nueva tienda
    "./main.js",
  ],
  theme: {
    extend: {
      colors: {
        'forever-yellow': '#FFC600', // El amarillo oficial (Supernova)
        'forever-green': '#2F6432',  // El verde oscuro corporativo
      }
    },
  },
  plugins: [],
}