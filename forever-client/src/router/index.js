import { createRouter, createWebHistory } from 'vue-router'

// --- 📦 LAYOUTS (Caparazones) ---
import AdminLayout from '../layouts/AdminLayout.vue'
import ShopLayout from '../layouts/ShopLayout.vue'

// --- 🔐 VISTAS DE LOGIN (Las Dos Puertas) ---
import Login from '../views/Login.vue'
import AdminLogin from '../views/AdminLogin.vue'

// --- 🛍️ VISTAS DE LA TIENDA ---
import HomeView from '../views/shop/HomeView.vue'
import TiendaView from '../views/shop/TiendaView.vue' 
import CarritoView from '../views/shop/CarritoView.vue' 
import PerfilView from '../views/shop/PerfilView.vue' 
// 🔥 NUEVO: Importamos la vista de Éxito
import ExitoView from '../views/shop/ExitoView.vue' 

// --- 🛡️ VISTAS DEL ADMIN ---
import CatalogView from '../views/admin/CatalogView.vue'
import SalesView from '../views/admin/SalesView.vue'
import ClientsView from '../views/admin/ClientsView.vue'
import FboAdminView from '../views/admin/FboAdminView.vue'
import ProfileView from '../views/admin/ProfileView.vue'
import RequestsView from '../views/admin/RequestsView.vue'
import UsersView from '../views/admin/UsersView.vue'

const routes = [
  // ----------------------------------------------------
  // 🛍️ 1. LA ENTRADA PRINCIPAL (Pública para todos)
  // ----------------------------------------------------
  {
    path: '/', 
    component: ShopLayout,
    children: [
      { path: '', name: 'home', component: HomeView },
      { path: 'tienda', name: 'tienda', component: TiendaView },
      { path: 'carrito', name: 'carrito', component: CarritoView },
      
      // 🔥 NUEVA RUTA: Pago Exitoso (Pública para que reciba la redirección)
      { path: 'pago-exitoso', name: 'exito', component: ExitoView },
      
      // 🔥 RUTA DE PERFIL (Solo para usuarios logueados)
      { 
        path: 'perfil', 
        name: 'perfil', 
        component: PerfilView,
        meta: { requiresClientAuth: true } 
      }
    ]
  },

  // ----------------------------------------------------
  // 🔐 2. PANTALLAS DE LOGIN (Ocultas)
  // ----------------------------------------------------
  { path: '/login', name: 'Login', component: Login },
  { path: '/admin-login', name: 'AdminLogin', component: AdminLogin },

  // ----------------------------------------------------
  // 🛡️ 3. ZONA DEL ADMINISTRADOR (Protegida)
  // ----------------------------------------------------
  {
    path: '/admin',
    component: AdminLayout,
    meta: { requiresAuth: true }, 
    children: [
      { path: '', redirect: '/admin/catalogo' }, 
      { path: 'catalogo', name: 'admin-catalogo', component: CatalogView },
      { path: 'ventas', name: 'admin-ventas', component: SalesView },
      { path: 'clientes', name: 'admin-clientes', component: ClientsView },
      { path: 'fbo', name: 'admin-fbo', component: FboAdminView }, 
      { path: 'perfil', name: 'admin-perfil', component: ProfileView },
      { path: 'solicitudes', name: 'admin-solicitudes', component: RequestsView },
      { path: 'usuarios', name: 'admin-usuarios', component: UsersView }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// ==========================================
// 🛡️ EL GUARDIÁN BLINDADO
// ==========================================
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('auth_token');
  const userType = localStorage.getItem('userType'); 

  // 1. Proteger ZONA ADMIN (Solo Admin o Inventario)
  if (to.matched.some(record => record.meta.requiresAuth)) {
    if (!token) {
      next('/admin-login'); 
    } else if (userType !== 'admin' && userType !== 'inventario') {
      next('/'); 
    } else {
      next(); 
    }
  } 
  
  // 2. Proteger ZONA DE CLIENTES (Perfil)
  else if (to.matched.some(record => record.meta.requiresClientAuth)) {
    if (!token) {
      next('/login'); 
    } else {
      next(); 
    }
  }

  // 3. Proteger LOGIN DE ADMIN (Evitar que vuelva al login si ya está dentro)
  else if (to.path === '/admin-login' && token) {
    if (userType === 'admin' || userType === 'inventario') {
      next('/admin/catalogo'); 
    } else {
      next('/'); 
    }
  }

  // 4. Proteger LOGIN DE CLIENTES (Evitar que vuelva al login si ya está dentro)
  else if (to.path === '/login' && token) {
    if (userType === 'admin' || userType === 'inventario') {
      next('/admin/catalogo'); 
    } else {
      next('/'); 
    }
  }

  // 5. Todo lo demás (La tienda pública y el carrito)
  else {
    next(); 
  }
});

export default router;