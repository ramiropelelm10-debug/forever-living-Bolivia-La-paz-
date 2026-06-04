<template>
  <div class="flex h-screen bg-[#F8F9FA] overflow-hidden font-sans">
    
    <!-- 🌿 SIDEBAR PREMIUM FOREVER LIVING -->
    <aside class="w-[260px] flex-shrink-0 relative bg-[#092615] shadow-2xl flex flex-col z-50">
      
      <!-- Fondo de Sábila (Aloe Vera) -->
      <div class="absolute inset-0 bg-[url('/images/sidebar-aloe-bg.png')] bg-cover bg-bottom opacity-40 mix-blend-overlay pointer-events-none"></div>

      <div class="relative z-10 flex flex-col h-full">
        
        <!-- LOGO DORADO -->
        <div class="pt-10 pb-8 flex flex-col items-center justify-center">
          <img src="/images/logo-forever-gold.png" alt="Forever Living" class="w-32 drop-shadow-2xl" />
        </div>

        <!-- MENÚ DE NAVEGACIÓN -->
        <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto custom-scrollbar">
          
          <router-link to="/admin/catalogo" class="nav-link" active-class="nav-link-active">
            <div class="w-8 flex justify-center"><i class="fas fa-home"></i></div>
            <span class="ml-2 text-xs font-bold tracking-wide">Dashboard</span>
          </router-link>

          <router-link v-if="userType === 'admin'" to="/admin/ventas" class="nav-link" active-class="nav-link-active">
            <div class="w-8 flex justify-center"><i class="fas fa-chart-line"></i></div>
            <span class="ml-2 text-xs font-bold tracking-wide">Ventas y Fiscal</span>
          </router-link>

          <router-link v-if="userType === 'admin'" to="/admin/clientes" class="nav-link" active-class="nav-link-active">
            <div class="w-8 flex justify-center"><i class="fas fa-users"></i></div>
            <span class="ml-2 text-xs font-bold tracking-wide">Clientes</span>
          </router-link>

          <router-link v-if="userType === 'admin'" to="/admin/solicitudes" class="nav-link flex items-center" active-class="nav-link-active">
            <div class="w-8 flex justify-center"><i class="fas fa-clipboard-list"></i></div>
            <span class="ml-2 text-xs font-bold tracking-wide">Solicitudes</span>
<span class="ml-auto bg-[#FFC600] text-[#092615] text-[10px] font-black px-2 py-0.5 rounded-full shadow-sm">0</span>          </router-link>

          <router-link v-if="userType === 'admin'" to="/admin/usuarios" class="nav-link" active-class="nav-link-active">
            <div class="w-8 flex justify-center"><i class="fas fa-user-cog"></i></div>
            <span class="ml-2 text-xs font-bold tracking-wide">Usuarios</span>
          </router-link>

          <router-link v-if="userType === 'admin'" to="/admin/fbo" class="nav-link" active-class="nav-link-active">
            <div class="w-8 flex justify-center"><i class="fas fa-id-badge"></i></div>
            <span class="ml-2 text-xs font-bold tracking-wide">Admin FBO</span>
          </router-link>

        </nav>

        <!-- FOOTER SIDEBAR -->
        <div class="p-6 mt-auto border-t border-white/10">
          <router-link to="/admin/perfil" class="nav-link mb-4" active-class="nav-link-active">
            <div class="w-8 flex justify-center"><i class="fas fa-cog"></i></div>
            <span class="ml-2 text-xs font-bold tracking-wide">Configuración</span>
          </router-link>
          
          <div class="text-[9px] text-white/50 leading-relaxed font-medium">
            <p class="mb-2 italic">"Ayudando a las personas a vivir una vida mejor y más saludable."</p>
            <p>© 2026 Forever Living Products Bolivia.<br>Todos los derechos reservados.</p>
          </div>
        </div>
      </div>
    </aside>

    <!-- 📊 CONTENIDO CENTRAL -->
    <div class="flex-1 flex flex-col min-h-screen relative overflow-hidden">
      <!-- HEADER CENTRAL SUPERIOR -->
      <header class="bg-white px-8 py-5 flex justify-between items-center shadow-sm z-10 border-b border-gray-100">
        <div>
          <h1 class="text-2xl font-black text-[#0A2617] tracking-tighter uppercase">Gestión Forever</h1>
          <p class="text-[9px] font-black text-[#b48a2d] uppercase tracking-widest mt-0.5">Sistema Administrativo Bolivia</p>
        </div>

        <div class="flex items-center gap-4">
          <button class="px-5 py-2.5 bg-[#f4f6f2] text-[#4a5d23] rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-[#e4ebd9] transition-all flex items-center gap-2">
            <i class="fas fa-file-excel"></i> Exportar Excel
          </button>
          
          <button @click="cerrarSesion" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition-all flex items-center gap-2">
            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
          </button>

          <div class="w-10 h-10 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 ml-2">
            <i class="fas fa-user"></i>
          </div>
        </div>
      </header>

      <!-- ÁREA DE RENDERIZADO (CatalogView) -->
      <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#F8F9FA] p-8">
        <router-view></router-view>
      </main>
    </div>

  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import Swal from 'sweetalert2';
import api from '../api.js'; 

const router = useRouter();
const userType = ref(localStorage.getItem('userType') || 'admin');

const cerrarSesion = async () => {
  Swal.fire({
    title: 'Cerrando sesión...',
    text: 'Desconectando de forma segura',
    allowOutsideClick: false,
    didOpen: () => { Swal.showLoading(); }
  });

  try {
    await api.post('/logout'); 
  } catch (error) {
    console.warn("Cerrando sesión localmente.");
  }

  localStorage.removeItem('auth_token');
  localStorage.removeItem('userType');
  window.location.href = '/admin-login'; 
};
</script>

<style scoped>
@reference "tailwindcss";

.nav-link {
  @apply flex items-center px-4 py-3 text-white/60 rounded-xl transition-all duration-300 hover:bg-white/10 hover:text-white mb-1 cursor-pointer;
}

.nav-link-active {
  @apply bg-[#D4AF37] text-white shadow-lg shadow-[#D4AF37]/20 hover:bg-[#C5A028];
}

.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 10px; }
</style>