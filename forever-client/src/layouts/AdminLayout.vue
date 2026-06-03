<template>
  <div class="flex min-h-screen bg-[#F8F9FA] font-sans">
    
    <aside class="w-[280px] bg-[#2C421C] text-white flex flex-col fixed inset-y-0 left-0 shadow-2xl z-20">
      
      <div class="flex flex-col items-center justify-center py-10 border-b border-white/10">
        <i class="fab fa-envira text-5xl text-[#FFC600] mb-3"></i>
        <h2 class="text-xl font-black tracking-widest uppercase text-white">Forever Living</h2>
      </div>

      <nav class="flex-1 px-4 py-8 space-y-2 overflow-y-auto custom-scrollbar">
        
        <router-link to="/admin/catalogo" class="sidebar-link" active-class="sidebar-link-active">
          <i class="fas fa-box w-6 text-center text-lg"></i> 
          <span>Inventario</span>
        </router-link>

        <router-link v-if="userType === 'admin'" to="/admin/ventas" class="sidebar-link" active-class="sidebar-link-active">
          <i class="fas fa-chart-line w-6 text-center text-lg"></i> 
          <span>Ventas y Fiscal</span>
        </router-link>

        <router-link v-if="userType === 'admin'" to="/admin/clientes" class="sidebar-link" active-class="sidebar-link-active">
          <i class="fas fa-users w-6 text-center text-lg"></i> 
          <span>Clientes</span>
        </router-link>

        <router-link v-if="userType === 'admin'" to="/admin/solicitudes" class="sidebar-link flex justify-between items-center" active-class="sidebar-link-active">
          <div class="flex items-center gap-3">
            <i class="fas fa-clipboard-list w-6 text-center text-lg"></i> 
            <span>Solicitudes</span>
          </div>
          <span class="bg-[#FFC600] text-black text-[10px] px-2 py-0.5 rounded-full font-black">5</span>
        </router-link>

        <router-link v-if="userType === 'admin'" to="/admin/usuarios" class="sidebar-link" active-class="sidebar-link-active">
          <i class="fas fa-user-cog w-6 text-center text-lg"></i> 
          <span>Usuarios</span>
        </router-link>

        <router-link v-if="userType === 'admin'" to="/admin/fbo" class="sidebar-link" active-class="sidebar-link-active">
          <i class="fas fa-id-card-alt w-6 text-center text-lg"></i> 
          <span>Admin FBO</span>
        </router-link>

        <div class="my-6 border-t border-white/10"></div> 

        <router-link to="/admin/perfil" class="sidebar-link" active-class="sidebar-link-active">
          <i class="fas fa-cog w-6 text-center text-lg"></i> 
          <span>Perfil Admin</span>
        </router-link>
        
      </nav>

      <div class="p-6 text-center border-t border-white/10 opacity-70">
        <p class="text-[9px] font-black tracking-widest uppercase">© 2026 Forever Living</p>
        <p class="text-[8px] font-bold tracking-wider text-slate-300">Products Bolivia</p>
      </div>
    </aside>

    <div class="flex-1 ml-[280px] flex flex-col min-h-screen relative">
      
      <header class="bg-white px-10 py-5 flex justify-between items-center shadow-sm sticky top-0 z-10">
        <div class="flex items-center gap-4">
          <div>
            <h1 class="text-2xl font-black text-[#2C421C] tracking-tighter uppercase">Gestión Forever</h1>
            <p class="text-[10px] font-black text-[#b48a2d] uppercase tracking-widest mt-0.5">Sistema Administrativo Bolivia</p>
          </div>
        </div>

        <div class="flex items-center gap-4">
          <button class="px-6 py-3 bg-[#4a5d23] text-white rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-[#38471a] transition-all flex items-center gap-2 shadow-lg shadow-[#4a5d23]/20">
            <i class="fas fa-file-excel"></i> Exportar Excel
          </button>
          
          <button @click="cerrarSesion" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition-all flex items-center gap-2">
            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
          </button>

          <div class="w-11 h-11 rounded-full bg-[#f4f6f2] border border-slate-200 flex items-center justify-center text-[#4a5d23] ml-2">
            <i class="fas fa-user"></i>
          </div>
        </div>
      </header>

      <main class="p-10 w-full max-w-7xl mx-auto">
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

// 🔥 LEEMOS EL ROL DEL USUARIO
// Esto activa los v-if del menú. Si es 'inventario', se ocultan las demás rutas.
const userType = ref(localStorage.getItem('userType') || 'admin');

// ==========================================
// FUNCIÓN PARA MATAR AL FANTASMA DEL LOGOUT
// ==========================================
const cerrarSesion = async () => {
  Swal.fire({
    title: 'Cerrando sesión...',
    text: 'Desconectando de forma segura',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });

  try {
    await api.post('/logout'); 
  } catch (error) {
    console.warn("Cerrando sesión localmente.");
  }

  localStorage.removeItem('auth_token');
  localStorage.removeItem('userType');

  // Recarga forzada
  window.location.href = '/admin-login'; 
};
</script>

<style scoped>
@reference "tailwindcss";

/* 🎨 BASE PARA LOS BOTONES DEL SIDEBAR */
.sidebar-link {
  @apply flex items-center gap-3 px-5 py-4 mb-2 rounded-[14px] text-[11px] font-black uppercase tracking-widest text-white/70 hover:bg-white/10 hover:text-white transition-all duration-300 cursor-pointer;
}

/* 🎨 ESTILO CUANDO ESTÁ ACTIVO (Boton Blanco con Letras Verdes como el Mockup) */
.sidebar-link-active {
  @apply bg-white text-[#2C421C] hover:bg-white hover:text-[#2C421C] shadow-lg shadow-black/10;
}

/* 🎨 ESTILIZAR LA BARRA DE SCROLL (Por si hay muchos módulos) */
.custom-scrollbar::-webkit-scrollbar {
  width: 5px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.15);
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
</style>