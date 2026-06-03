<template>
  <div class="bg-[#FAF9F6]"> 
    <section class="relative bg-white pt-10 pb-20 overflow-hidden border-b border-[#005A36]/10">
      <div class="max-w-7xl mx-auto px-6 lg:px-10 flex flex-col lg:flex-row items-center relative z-10">
        <div class="w-full lg:w-1/2 pr-10 z-20">
          <h2 class="font-serif italic font-black text-[#005A36] text-7xl md:text-[8rem] leading-[0.85] mb-8 drop-shadow-sm">
            Pura.<br>Real.
          </h2>
          <p class="font-black uppercase tracking-[0.3em] text-[10px] text-gray-900 mb-4 border-b-2 border-[#FFC600] inline-block pb-1">
            De la planta, al producto, a ti
          </p>
          <p class="text-gray-600 text-lg md:text-xl font-medium max-w-md leading-relaxed mb-10">
            Nuestra promesa de 6 horas: procesamos el gel puro del interior de la hoja inmediatamente para conservar su <span class="font-black">potencia natural.</span>
          </p>
          <button @click="$router.push('/tienda')" class="bg-[#FFC600] text-black px-10 py-4 rounded-full font-black uppercase text-[10px] tracking-widest hover:bg-[#005A36] hover:text-[#FFC600] transition-all shadow-xl hover:-translate-y-1">
            Explorar Catálogo →
          </button>
        </div>
        <div class="w-full lg:w-1/2 mt-16 lg:mt-0 relative">
          <div class="absolute -inset-10 bg-gradient-to-r from-transparent to-[#2F6432]/10 rounded-full blur-3xl"></div>
          <img src="https://images.unsplash.com/photo-1596040033229-a9821ebd058d?auto=format&fit=crop&q=80" class="relative z-10 rounded-3xl shadow-2xl object-cover h-[500px] w-full" alt="Aloe Vera Fresco">
          <div class="absolute top-10 -right-4 z-20 bg-[#659b41] text-white p-6 rounded-[2rem] rounded-tr-none shadow-xl transform rotate-6">
            <p class="font-black text-2xl text-center leading-tight">100%<br><span class="text-xs font-bold uppercase tracking-wider">Ingredientes<br>Naturales</span></p>
          </div>
        </div>
      </div>
    </section>

    <section class="max-w-7xl mx-auto px-6 lg:px-10 py-24 text-center">
      <h3 class="font-serif italic font-black text-5xl text-[#005A36] mb-4">Bienestar que transforma vidas</h3>
      <p class="text-sm font-bold text-gray-600 mb-16 uppercase tracking-widest">Productos naturales, personas reales, un propósito que trasciende.</p>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-left">
        <div class="bg-white rounded-3xl overflow-hidden shadow-lg border border-gray-100 group cursor-pointer hover:-translate-y-2 transition-all">
          <img src="https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?auto=format&fit=crop&q=80" class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-500" alt="Forever Giving">
          <div class="p-8 text-center flex flex-col items-center">
            <div class="w-12 h-12 bg-[#005A36] text-white rounded-full flex items-center justify-center mb-4 -mt-14 relative z-10 border-4 border-white"><i class="fas fa-heart"></i></div>
            <h4 class="font-serif italic font-black text-2xl text-[#005A36] mb-3">Forever Giving</h4>
            <p class="text-xs text-gray-500 font-medium mb-6">Cambiamos vidas y construimos un mundo mejor.</p>
            <span class="text-[10px] font-black uppercase text-[#005A36] tracking-widest">Conoce más →</span>
          </div>
        </div>
        <div class="bg-white rounded-3xl overflow-hidden shadow-lg border border-gray-100 group cursor-pointer hover:-translate-y-2 transition-all">
          <img src="https://images.unsplash.com/photo-1555626906-fcf10d6851b4?auto=format&fit=crop&q=80" class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-500" alt="Planta al Producto">
          <div class="p-8 text-center flex flex-col items-center">
            <div class="w-12 h-12 bg-[#005A36] text-white rounded-full flex items-center justify-center mb-4 -mt-14 relative z-10 border-4 border-white"><i class="fas fa-leaf"></i></div>
            <h4 class="font-serif italic font-black text-2xl text-[#005A36] mb-3 leading-tight">De la planta<br>al producto</h4>
            <p class="text-xs text-gray-500 font-medium mb-6">Calidad y pureza en cada paso del proceso.</p>
            <span class="text-[10px] font-black uppercase text-[#005A36] tracking-widest">Nuestro Proceso →</span>
          </div>
        </div>
        <div @click="$router.push('/tienda')" class="bg-white rounded-3xl overflow-hidden shadow-lg border border-gray-100 group cursor-pointer hover:-translate-y-2 transition-all">
          <img src="https://images.unsplash.com/photo-1629198688000-71f23e745b6e?auto=format&fit=crop&q=80" class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-500" alt="Tienda">
          <div class="p-8 text-center flex flex-col items-center">
            <div class="w-12 h-12 bg-[#005A36] text-white rounded-full flex items-center justify-center mb-4 -mt-14 relative z-10 border-4 border-white"><i class="fas fa-shopping-bag"></i></div>
            <h4 class="font-serif italic font-black text-2xl text-[#005A36] mb-3">Tienda Oficial</h4>
            <p class="text-xs text-gray-500 font-medium mb-6">Descubre nuestros productos y encuentra tu favorito.</p>
            <span class="text-[10px] font-black uppercase text-[#005A36] tracking-widest">Ir a Tienda →</span>
          </div>
        </div>
      </div>
    </section>

    <section class="border-y border-[#005A36]/10 bg-white py-16">
      <div class="max-w-7xl mx-auto px-6 lg:px-10 grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
        <div class="flex flex-col items-center">
          <i class="fas fa-seedling text-4xl text-[#005A36] mb-4"></i>
          <h5 class="font-black text-[#005A36] mb-2">100% Naturales</h5>
          <p class="text-[10px] text-gray-500 font-bold px-4">Ingredientes puros y de la más alta calidad.</p>
        </div>
        <div class="flex flex-col items-center border-l border-gray-100">
          <i class="far fa-clock text-4xl text-[#005A36] mb-4"></i>
          <h5 class="font-black text-[#005A36] mb-2">Promesa de 6 horas</h5>
          <p class="text-[10px] text-gray-500 font-bold px-4">Procesamos el gel de aloe en menos de 6 horas.</p>
        </div>
        <div class="flex flex-col items-center border-l border-gray-100">
          <i class="fas fa-hand-holding-heart text-4xl text-[#005A36] mb-4"></i>
          <h5 class="font-black text-[#005A36] mb-2">Sostenibles</h5>
          <p class="text-[10px] text-gray-500 font-bold px-4">Prácticas responsables con el medio ambiente.</p>
        </div>
        <div class="flex flex-col items-center border-l border-gray-100">
          <i class="fas fa-medal text-4xl text-[#005A36] mb-4"></i>
          <h5 class="font-black text-[#005A36] mb-2">Calidad Garantizada</h5>
          <p class="text-[10px] text-gray-500 font-bold px-4">Más de 45 años respaldan nuestra calidad.</p>
        </div>
      </div>
    </section>

    <section class="max-w-7xl mx-auto px-6 lg:px-10 py-24 flex flex-col md:flex-row items-center gap-16">
      <div class="w-full md:w-1/2">
        <p class="text-[10px] font-black uppercase text-gray-400 tracking-[0.3em] mb-4">Nuestros Productos</p>
        <h3 class="font-serif italic font-black text-5xl text-[#005A36] leading-tight mb-6">Bienestar puro,<br>directo de la<br>naturaleza.</h3>
        <p class="text-sm font-medium text-gray-600 mb-8 max-w-sm">Descubre nuestra línea exclusiva de bebidas de aloe vera, cuidado personal y nutrición. Diseñados con los más altos estándares para potenciar tu salud desde adentro hacia afuera.</p>
        <button @click="$router.push('/tienda')" class="bg-[#005A36] text-white px-8 py-3 rounded-full font-black text-[10px] uppercase tracking-widest hover:bg-[#FFC600] hover:text-black transition-colors shadow-lg">
          Ver Catálogo Completo
        </button>
      </div>
      <div class="w-full md:w-1/2 relative">
        <img src="https://images.unsplash.com/photo-1608248543803-ba4f8c70ae0b?auto=format&fit=crop&q=80" class="rounded-3xl shadow-2xl object-cover w-full h-[400px]" alt="Productos Forever">
        <div class="absolute -bottom-8 -right-8 bg-[#659b41] text-white p-8 rounded-[3rem] rounded-tl-none shadow-xl">
          <p class="font-serif italic text-2xl font-black text-center leading-tight">100%<br>Aloe Vera<br>Puro</p>
        </div>
      </div>
    </section>

    <section class="bg-[#005A36] py-16 text-white mt-10">
      <div class="max-w-7xl mx-auto px-6 lg:px-10 grid grid-cols-2 md:grid-cols-4 gap-8 divide-x divide-white/20">
        <div class="flex items-center justify-center gap-4">
          <i class="fas fa-users text-4xl text-[#FFC600]"></i>
          <div><p class="text-3xl font-black">45+</p><p class="text-[9px] uppercase tracking-widest">Años de experiencia</p></div>
        </div>
        <div class="flex items-center justify-center gap-4">
          <i class="fas fa-globe text-4xl text-[#FFC600]"></i>
          <div><p class="text-3xl font-black">160+</p><p class="text-[9px] uppercase tracking-widest">Países presentes</p></div>
        </div>
        <div class="flex items-center justify-center gap-4">
          <i class="fas fa-leaf text-4xl text-[#FFC600]"></i>
          <div><p class="text-[10px] text-[#FFC600] uppercase font-bold mb-[-5px]">Más de</p><p class="text-3xl font-black">400</p><p class="text-[9px] uppercase tracking-widest">Especies de aloe</p></div>
        </div>
        <div class="flex items-center justify-center gap-4">
          <i class="fas fa-heart text-4xl text-[#FFC600]"></i>
          <div><p class="text-3xl font-black">Millones</p><p class="text-[9px] uppercase tracking-widest">Vidas transformadas</p></div>
        </div>
      </div>
    </section>

    <section class="max-w-7xl mx-auto px-6 lg:px-10 py-24 flex flex-col md:flex-row items-center gap-16">
      <div class="w-full md:w-1/2">
        <img src="https://images.unsplash.com/photo-1596434484964-b528c11ceab3?auto=format&fit=crop&q=80" class="rounded-3xl shadow-xl object-cover w-full h-[350px]" alt="Fileteando Aloe Vera">
      </div>
      <div class="w-full md:w-1/2">
        <p class="text-[10px] font-black uppercase text-gray-400 tracking-[0.3em] mb-4">De la planta al producto</p>
        <h3 class="font-serif italic font-black text-5xl text-[#005A36] leading-tight mb-6">Calidad que se siente,<br>pureza que transforma.</h3>
        <p class="text-sm font-medium text-gray-600 leading-relaxed mb-8">
          Hay más de 400 especies de plantas de Aloe, pero solo una se ha proclamado como la mejor: <span class="font-black text-black">Aloe Vera.</span><br><br>
          En nuestros cultivos, atendemos con cuidado nuestro Aloe y lo fileteamos a mano para ofrecer mejor calidad. Y eso es solo el comienzo del viaje.
        </p>
        
        <template v-if="!isLoggedIn">
          <button @click="$router.push('/login')" class="bg-[#005A36] text-white px-8 py-4 rounded-xl font-black text-[11px] uppercase tracking-widest hover:bg-[#FFC600] hover:text-black transition-colors shadow-lg flex items-center gap-2">
            <i class="fas fa-user-plus text-sm"></i>
            Regístrate como FBO o Cliente
          </button>
        </template>
        <template v-else>
          <button @click="$router.push('/tienda')" class="bg-[#FFC600] text-black px-8 py-4 rounded-xl font-black text-[11px] uppercase tracking-widest hover:bg-[#005A36] hover:text-white transition-colors shadow-lg flex items-center gap-2">
            <i class="fas fa-shopping-cart text-sm"></i>
            Ir a mis compras
          </button>
        </template>
        
      </div>
    </section>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const isLoggedIn = ref(false);

onMounted(() => {
  // Comprobamos si el usuario tiene sesión activa
  const token = localStorage.getItem('auth_token');
  if (token) {
    isLoggedIn.value = true;
  }
});
</script>