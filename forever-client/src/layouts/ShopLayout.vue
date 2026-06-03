<template>
  <div class="min-h-screen flex flex-col bg-white font-sans overflow-x-hidden">
    
    <nav class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-gray-100 shadow-sm transition-all duration-300 relative">
      <div class="max-w-7xl mx-auto px-6 lg:px-10 py-4 flex justify-between items-center">
        
        <router-link to="/" class="flex items-center gap-2">
          <span class="font-serif italic font-black text-3xl text-[#005A36] tracking-tighter">FOREVER®</span>
        </router-link>

        <div class="hidden md:flex items-center gap-8 font-bold text-[11px] uppercase tracking-[0.2em] text-[#005A36]">
          <router-link to="/tienda" class="hover:text-[#FFC600] transition-colors">Tienda</router-link>
          <a href="#" class="hover:text-[#FFC600] transition-colors">Unirse</a>
          
          <div v-if="isLoggedIn" class="relative group">
            <button class="hover:text-[#FFC600] transition-colors flex items-center gap-1 uppercase pb-4 -mb-4">
              Acerca De <i class="fas fa-chevron-down text-[8px] transition-transform group-hover:rotate-180"></i>
            </button>
            
            <div class="absolute top-full left-1/2 -translate-x-1/2 mt-4 w-screen max-w-5xl bg-white border-t-4 border-[#005A36] shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 pointer-events-none group-hover:pointer-events-auto flex p-10 gap-10 z-50">
              
              <div class="w-1/3">
                <h4 class="font-black text-[#005A36] uppercase tracking-widest text-[11px] mb-6 border-b border-gray-100 pb-2">Aprenda más sobre Para Siempre</h4>
                <ul class="space-y-4 text-xs font-bold text-gray-500">
                  <li><a href="#" class="hover:text-[#FFC600] transition-colors">Acerca de Forever Living</a></li>
                  <li><a href="#" class="hover:text-[#FFC600] transition-colors">Nuestro Liderazgo</a></li>
                  <li><a href="#" class="hover:text-[#FFC600] transition-colors">De la planta al producto para usted</a></li>
                  <li><a href="#" class="hover:text-[#FFC600] transition-colors">Preguntas más frecuentes</a></li>
                </ul>
              </div>

              <div class="w-1/3 border-l border-gray-100 pl-10">
                <h4 class="font-black text-[#005A36] uppercase tracking-widest text-[11px] mb-6 border-b border-gray-100 pb-2">Desde el Blog</h4>
                <div class="relative rounded-xl overflow-hidden group/blog cursor-pointer">
                  <img src="https://images.unsplash.com/photo-1540575467063-178a50c2df87?auto=format&fit=crop&q=80" class="w-full h-40 object-cover group-hover/blog:scale-105 transition-transform duration-500" alt="Evento Forever">
                  <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent flex items-end p-4">
                    <h5 class="text-white font-black text-lg leading-tight">La gran oportunidad FOREVER</h5>
                  </div>
                </div>
              </div>

              <div class="w-1/3 border-l border-gray-100 pl-10">
                <h4 class="font-black text-[#005A36] uppercase tracking-widest text-[11px] mb-6 border-b border-gray-100 pb-2">Contacta con Nosotros</h4>
                <p class="font-black text-[#005A36] text-sm mb-2">Forever Living Products Bolivia</p>
                <p class="text-xs text-gray-500 font-medium leading-relaxed mb-4">
                  Av. Capitán Ravelo, Edificio Centrika Ravelo Piso: PB Local 1 Nro 2393,<br>
                  Nuestra Señora de La Paz, Bolivia entre Rosendo Gutiérrez y Belisario Salinas.
                </p>
                <p class="text-[11px] font-black uppercase text-[#005A36] tracking-widest mb-1">Atención al cliente:</p>
                <p class="text-sm font-bold text-gray-600">22-441716</p>
              </div>

            </div>
          </div>
        </div>

        <div class="flex items-center gap-5">
          <div class="hidden md:flex items-center gap-2 text-[#005A36] font-bold text-xs mr-4 border-r border-gray-200 pr-4">
            <span>🇧🇴 Bolivia | Esp</span>
            <i class="fas fa-chevron-down text-[10px]"></i>
          </div>

          <template v-if="!isLoggedIn">
            <router-link to="/login" class="hidden md:block text-[#005A36] font-black text-[10px] uppercase tracking-widest hover:text-black transition">
              Iniciar Sesión
            </router-link>
            <router-link to="/login" class="bg-[#FFC600] text-black px-6 py-2.5 rounded-full font-black text-[10px] uppercase tracking-widest hover:bg-[#005A36] hover:text-white transition-all shadow-md inline-block">
              Registrarse
            </router-link>
          </template>
          
          <template v-else>
            <router-link v-if="userType === 'admin' || userType === 'inventario'" to="/admin" class="text-[#005A36] font-black text-[10px] uppercase tracking-widest hover:text-[#FFC600] transition mr-2 border-r border-gray-200 pr-2">
              Panel Admin
            </router-link>
            
            <router-link to="/perfil" class="text-[#005A36] font-black text-[10px] uppercase tracking-widest hover:text-[#FFC600] transition mr-2 border-r border-gray-200 pr-2">
              Mi Perfil
            </router-link>

             <button @click="cerrarSesion" class="text-red-500 font-black text-[10px] uppercase tracking-widest hover:text-red-700 transition">
               Salir
             </button>
          </template>
          
          <router-link to="/carrito" class="relative p-2 text-[#005A36] hover:text-[#FFC600] transition-colors group cursor-pointer block">
            <i class="fas fa-shopping-cart text-xl group-hover:scale-110 transition-transform"></i>
            <span v-if="cartItemCount > 0" class="absolute top-0 right-0 bg-red-500 text-white w-4 h-4 rounded-full flex items-center justify-center text-[9px] font-bold shadow-sm animate-in zoom-in duration-300">
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

    <footer class="bg-[#FFC600] pt-16 pb-6 border-t-[8px] border-[#005A36]">
      <div class="max-w-7xl mx-auto px-6 lg:px-10">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12 mb-12">
           <div>
            <span class="font-serif italic font-black text-3xl text-[#005A36] tracking-tighter mb-6 block">FOREVER®</span>
            <p class="font-bold text-sm text-[#005A36] mb-1">Forever Living Products Bolivia</p>
            <p class="text-xs text-[#005A36]/80 leading-relaxed mb-4">Avenida Capitán Ravelo Pasaje Eduardo,<br>Sopocachi La Paz, Bolivia</p>
            <p class="text-sm font-black text-[#005A36] mb-1">(591) 2 244 1990 - 244 1716</p>
            <p class="text-xs text-[#005A36] font-bold">recepcion@foreverliving.com.bo</p>
          </div>
          </div>

        <div class="border-t border-[#005A36]/20 pt-6 flex flex-col md:flex-row justify-between items-end gap-4 relative">
          <div class="flex flex-col gap-2">
             <div class="flex items-center gap-2">
              <i class="fas fa-handshake text-[#005A36] text-xl"></i>
              <span class="text-[10px] font-bold text-[#005A36] uppercase tracking-wider">Somos miembros de la<br>Asociación de Ventas Directas.</span>
            </div>
            <span class="text-[9px] font-black text-[#005A36]/60 mt-4 uppercase tracking-[0.2em]">Versión 4.2</span>
          </div>

          <p class="text-[10px] text-[#005A36] font-bold text-center md:text-right">
            Copyright © 2026 Forever Living.com, LLC y entidades relacionadas.<br>Todos los derechos reservados.
          </p>
        </div>
      </div>
    </footer>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
// Importamos el contador del carrito global
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