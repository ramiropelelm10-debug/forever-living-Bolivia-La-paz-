<template>
  <div class="min-h-screen flex flex-col bg-white font-sans overflow-x-hidden">
    
    <!-- NAVBAR (Barra blanca superior) -->
    <nav class="sticky top-0 z-50 bg-white border-b border-gray-100 shadow-sm transition-all duration-300">
      <div class="max-w-7xl mx-auto px-6 lg:px-10 flex justify-between items-center h-28">
        
        <!-- LOGO NAVBAR GIGANTE -->
        <router-link to="/" class="flex items-center transition-transform hover:scale-105">
          <img src="/images/logo-navbar.png" alt="Forever Living" class="h-20 md:h-[6.5rem] w-auto object-contain origin-left transform scale-110" />
        </router-link>

        <div class="hidden md:flex items-center gap-8 font-black text-[11px] uppercase tracking-[0.2em] text-[#00311D] h-full">
          
          <!-- ESTADO ACTIVO "TIENDA" -->
          <router-link to="/tienda" class="h-full flex items-center border-b-[3px] border-[#00311D] text-[#00311D] pt-[3px]">
            TIENDA
          </router-link>
          
          <div v-if="isLoggedIn" class="group h-full flex items-center">
            <button class="hover:text-[#FFC600] transition-colors flex items-center gap-1 uppercase">
              ACERCA DE <i class="fas fa-chevron-down text-[8px] transition-transform group-hover:rotate-180"></i>
            </button>
            <div class="absolute top-28 left-0 w-full opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 pointer-events-none group-hover:pointer-events-auto z-50">
              <div class="max-w-5xl mx-auto bg-white border-t-4 border-[#00311D] shadow-2xl rounded-b-2xl p-10">
                <p class="text-xs text-gray-500">Aquí van los enlaces corporativos...</p>
              </div>
            </div>
          </div>
        </div>

        <div class="flex items-center gap-4">
          <!-- SELECTOR DE IDIOMA -->
          <div class="hidden md:flex relative group border-r border-gray-200 pr-4">
            <button class="flex items-center gap-1.5 text-[#00311D] font-bold text-[11px] hover:text-[#FFC600] transition-colors">
              <span class="lowercase font-medium">bo</span> <span>Bolivia | Esp</span>
              <i class="fas fa-chevron-down text-[9px] ml-1"></i>
            </button>
          </div>

          <!-- BOTONES DE USUARIO -->
          <template v-if="!isLoggedIn">
            <router-link to="/login" class="text-[#00311D] font-black text-[10px] uppercase tracking-widest hover:text-[#FFC600] transition ml-2">INICIAR SESIÓN</router-link>
          </template>
          
          <template v-else>
            <router-link to="/perfil" class="flex items-center gap-2 text-[#00311D] font-black text-[11px] uppercase tracking-wider hover:text-[#FFC600] transition pr-4 border-r border-gray-200 ml-2">
              <i class="fas fa-user text-sm"></i> MI PERFIL
            </router-link>
            <button @click="cerrarSesion" class="text-[#D32F2F] font-black text-[11px] uppercase tracking-wider hover:text-red-800 transition ml-2">
              SALIR
            </button>
          </template>
          
          <!-- CARRITO CON CÍRCULO AMARILLO -->
          <router-link to="/carrito" class="relative p-2 text-[#00311D] hover:text-[#FFC600] transition-colors group ml-2">
            <i class="fas fa-shopping-cart text-[22px] group-hover:scale-110 transition-transform"></i>
            <span v-if="cartItemCount > 0" class="absolute top-0 right-0 bg-[#FFC600] text-[#00311D] w-[18px] h-[18px] rounded-full flex items-center justify-center text-[10px] font-black shadow-sm">
              {{ cartItemCount }}
            </span>
          </router-link>
        </div>
      </div>
    </nav>

    <main class="flex-grow">
      <router-view v-slot="{ Component }">
        <transition name="fade" mode="out-in">
          <component :is="Component" />
        </transition>
      </router-view>
    </main>

    <!-- FOOTER VERDE OSCURO (Sin la línea amarilla gruesa para un diseño más limpio) -->
    <footer class="bg-[#00311D] relative overflow-hidden pt-12 pb-6">
      <img src="/images/footer-planta.png" class="absolute bottom-0 right-0 h-48 md:h-64 w-auto object-cover opacity-90 pointer-events-none z-0" alt="Planta Aloe" />
      
      <div class="max-w-7xl mx-auto px-6 lg:px-10 relative z-10">
        <div class="flex flex-col md:flex-row justify-between items-center md:items-start gap-8 mb-10 border-b border-white/10 pb-8">
          
          <div class="text-center md:text-left flex flex-col items-center md:items-start">
            <img src="/images/logo-footer.png" alt="Forever Living" class="h-20 w-auto object-contain mb-4" />
          </div>

          <div class="text-center md:text-left flex flex-col gap-2">
            <p class="font-bold text-sm text-[#FFC600]">Forever Living Products Bolivia</p>
            <p class="text-xs text-white/80 leading-relaxed">Avenida Capitán Ravelo Pasaje Eduardo,<br>Sopocachi La Paz, Bolivia</p>
          </div>

          <div class="text-center md:text-left flex flex-col gap-2">
            <p class="text-xs font-bold text-[#FFC600] flex items-center justify-center md:justify-start gap-2">
              <i class="fas fa-phone-alt"></i> (591) 2 244 1990 - 244 1716
            </p>
            <p class="text-xs text-white/80 flex items-center justify-center md:justify-start gap-2 mt-1">
              <i class="far fa-envelope"></i> recepcion@foreverliving.com.bo
            </p>
          </div>

          <div class="text-center md:text-left">
            <p class="text-[10px] font-black text-white uppercase tracking-widest mb-3">Síguenos</p>
            <div class="flex items-center justify-center md:justify-start gap-3">
              <a href="#" class="w-8 h-8 rounded-full border border-[#FFC600] text-[#FFC600] flex items-center justify-center hover:bg-[#FFC600] hover:text-[#00311D] transition-colors"><i class="fab fa-facebook-f text-xs"></i></a>
              <a href="#" class="w-8 h-8 rounded-full border border-[#FFC600] text-[#FFC600] flex items-center justify-center hover:bg-[#FFC600] hover:text-[#00311D] transition-colors"><i class="fab fa-instagram text-xs"></i></a>
              <a href="#" class="w-8 h-8 rounded-full border border-[#FFC600] text-[#FFC600] flex items-center justify-center hover:bg-[#FFC600] hover:text-[#00311D] transition-colors"><i class="fab fa-whatsapp text-xs"></i></a>
            </div>
          </div>
          
        </div>

        <div class="flex flex-col md:flex-row justify-between items-center gap-4 text-[10px] text-white/60">
          <p>© 2026 Forever Living Products Bolivia. Todos los derechos reservados.</p>
          <div class="flex gap-4">
            <a href="#" class="hover:text-white transition-colors">Términos y Condiciones</a>
            <a href="#" class="hover:text-white transition-colors">Política de Privacidad</a>
            <a href="#" class="hover:text-white transition-colors">Política de Envíos y Devoluciones</a>
          </div>
        </div>
      </div>
    </footer>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { cartItemCount } from '../store/cart'; 

const router = useRouter();
const isLoggedIn = ref(false);
const userType = ref('cliente');

onMounted(() => {
  const token = localStorage.getItem('auth_token');
  if (token) {
    isLoggedIn.value = true;
    userType.value = localStorage.getItem('userType') || 'cliente';
  }
});

const cerrarSesion = () => {
  localStorage.removeItem('auth_token');
  localStorage.removeItem('userType');
  isLoggedIn.value = false;
  window.location.href = '/'; 
};
</script>

<style scoped>
@reference "tailwindcss";

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>