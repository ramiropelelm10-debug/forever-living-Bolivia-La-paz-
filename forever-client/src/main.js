import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router' // Si estás usando Vue Router
import App from './App.vue'

// Aquí importas Tailwind y SweetAlert global si es necesario
import './style.css' 

const app = createApp(App)

app.use(createPinia())
app.use(router)
app.mount('#app')