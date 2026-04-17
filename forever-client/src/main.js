import { createApp } from 'vue'
import App from './App.vue'
import router from './router' // Esto busca automáticamente el index.js dentro de la carpeta router
import './style.css'

const app = createApp(App)
app.use(router) // Aquí es donde se activa la magia de la navegación
app.mount('#app')