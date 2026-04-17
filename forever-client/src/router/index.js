import { createRouter, createWebHistory } from 'vue-router'
import Login from '../components/Login.vue'
import Dashboard from '../components/Dashboard.vue'

const routes = [
  { path: '/', component: Login },
  { 
    path: '/dashboard', 
    component: Dashboard,
    meta: { requiresAuth: true } 
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// EL GUARDIÁN CORREGIDO 🛡️
router.beforeEach((to, from, next) => {
  // CAMBIO IMPORTANTE: Usar 'auth_token' (el que guardamos en el login)
  const token = localStorage.getItem('auth_token');

  if (to.meta.requiresAuth && !token) {
    // Si no hay token, al login de cabeza
    next('/');
  } else {
    next();
  }
});

export default router