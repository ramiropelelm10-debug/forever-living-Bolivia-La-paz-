<template>
  <div class="bg-[#F8F9FA] min-h-screen pb-20">
    
    <div class="relative bg-[#002B19] pt-24 pb-32 px-6 lg:px-10 overflow-hidden min-h-[480px] flex items-center">
      
      <div class="absolute inset-0 w-full h-full bg-cover bg-center md:bg-right" style="background-image: url('/images/hero-fondo.png');"></div>
      
      <div class="absolute inset-0 bg-gradient-to-r from-[#002B19] via-[#002B19]/95 md:via-[#002B19]/60 to-transparent"></div>
      
      <div class="max-w-7xl mx-auto w-full relative z-10 flex items-center justify-between h-full">
        
        <div class="w-full md:w-3/5 text-left pt-4">
          <h1 class="text-white text-5xl md:text-[5.5rem] font-serif italic font-bold leading-[1.05] tracking-tight drop-shadow-md">
            Catálogo de <br>
            <span class="text-[#FFC600] font-serif italic">Productos</span>
          </h1>
          
          <div class="w-20 h-[2px] bg-[#FFC600] mt-5 mb-6 shadow-sm"></div>
          
          <p class="text-white/95 text-sm md:text-[15px] max-w-[420px] font-medium leading-relaxed drop-shadow-sm">
            Descubre nuestra exclusiva línea de productos naturales a base de Aloe Vera. Salud, nutrición y belleza para ti y tu familia.
          </p>
          
          <div v-if="userDiscount > 0" class="mt-8 inline-flex items-center bg-[#FFC600] text-[#002B19] px-6 py-2.5 rounded-full font-black text-xs uppercase tracking-widest shadow-lg">
            <i class="fas fa-star mr-2"></i> Tienes un {{ userDiscount }}% de descuento FBO
          </div>
        </div>

      </div>

      <img src="/images/hero-productos-madera.png" alt="Productos Forever" class="absolute bottom-0 right-0 md:right-[5%] z-10 h-[80%] md:h-[105%] w-auto object-contain object-bottom pointer-events-none drop-shadow-[0_20px_20px_rgba(0,0,0,0.4)]" />
    </div>

    <div class="max-w-[1000px] mx-auto px-6 relative z-20 -mt-10">
      <div class="bg-white rounded-[2rem] shadow-[0_15px_40px_rgba(0,0,0,0.08)] py-4 px-6 md:px-12 flex flex-wrap justify-between items-center border border-gray-100">
        
        <div class="flex items-center gap-3 py-2 w-full md:w-auto justify-center">
          <i class="fas fa-leaf text-[#002B19] text-[22px] opacity-90"></i>
          <div class="flex flex-col leading-tight">
            <span class="text-[9px] text-gray-500 font-bold uppercase tracking-[0.1em]">Ingredientes</span>
            <span class="text-[11px] text-[#002B19] font-black uppercase tracking-wide">Naturales</span>
          </div>
        </div>
        
        <div class="hidden md:block w-px h-8 bg-gray-200"></div>
        
        <div class="flex items-center gap-3 py-2 w-full md:w-auto justify-center">
          <i class="fas fa-award text-[#002B19] text-[22px] opacity-90"></i>
          <div class="flex flex-col leading-tight">
            <span class="text-[9px] text-gray-500 font-bold uppercase tracking-[0.1em]">Calidad</span>
            <span class="text-[11px] text-[#002B19] font-black uppercase tracking-wide">Garantizada</span>
          </div>
        </div>
        
        <div class="hidden md:block w-px h-8 bg-gray-200"></div>
        
        <div class="flex items-center gap-3 py-2 w-full md:w-auto justify-center">
          <i class="fas fa-heart text-[#002B19] text-[22px] opacity-90"></i>
          <div class="flex flex-col leading-tight">
            <span class="text-[9px] text-gray-500 font-bold uppercase tracking-[0.1em]">Bienestar para</span>
            <span class="text-[11px] text-[#002B19] font-black uppercase tracking-wide">Toda la familia</span>
          </div>
        </div>
        
        <div class="hidden md:block w-px h-8 bg-gray-200"></div>
        
        <div class="flex items-center gap-3 py-2 w-full md:w-auto justify-center">
          <i class="fas fa-shield-alt text-[#002B19] text-[22px] opacity-90"></i>
          <div class="flex flex-col leading-tight">
            <span class="text-[9px] text-gray-500 font-bold uppercase tracking-[0.1em]">Confianza</span>
            <span class="text-[11px] text-[#002B19] font-black uppercase tracking-wide">Desde 1978</span>
          </div>
        </div>
      </div>
    </div>

    <div v-if="isLoading" class="flex flex-col items-center justify-center py-20">
      <i class="fas fa-circle-notch fa-spin text-4xl text-[#002B19] mb-4"></i>
      <p class="font-black text-slate-400 uppercase tracking-widest text-xs">Cargando catálogo...</p>
    </div>

    <div v-else>
      <div class="max-w-7xl mx-auto px-6 lg:px-10 mt-16 mb-10 flex flex-wrap justify-center gap-4">
        <button 
          v-for="cat in categorias" 
          :key="cat.id"
          @click="categoriaActiva = cat.id"
          :class="categoriaActiva === cat.id 
            ? 'bg-[#002B19] text-white shadow-md' 
            : 'bg-white text-gray-600 border border-gray-200 hover:border-[#002B19] hover:text-[#002B19]'"
          class="px-8 py-2.5 rounded-full text-[10px] font-black uppercase tracking-[0.15em] transition-colors"
        >
          {{ cat.nombre }}
        </button>
      </div>

      <div class="max-w-7xl mx-auto px-6 lg:px-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div v-for="product in productosFiltrados" :key="product.id" class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-xl transition-all group relative flex flex-col justify-between">
          <span v-if="product.stock === 0" class="absolute top-4 left-4 bg-red-100 text-red-800 text-[9px] font-black uppercase tracking-widest px-3 py-1 rounded-full z-10">Agotado</span>
          <span v-else-if="product.stock < 20" class="absolute top-4 left-4 bg-amber-100 text-amber-800 text-[9px] font-black uppercase tracking-widest px-3 py-1 rounded-full z-10">Poco Stock</span>
          
          <button class="absolute top-4 right-4 text-gray-300 hover:text-red-500 transition-colors z-10">
            <i class="far fa-heart text-xl"></i>
          </button>

          <div>
            <div class="h-48 w-full flex items-center justify-center mb-6 overflow-hidden relative p-2">
              <img :src="product.foto_persona || '/images/cat-aloe.png'" 
                   :alt="product.name" 
                   class="max-h-full object-contain group-hover:scale-110 transition-transform duration-500">
            </div>
            
            <div class="flex justify-between items-center mb-2">
              <span class="text-[9px] text-gray-400 font-bold uppercase tracking-[0.1em]">{{ product.categoria || 'Sin Categoría' }}</span>
              <span class="text-[10px] font-black text-[#002B19] bg-green-50 px-2 py-1 rounded-md">{{ product.cc_value }} CC</span>
            </div>
            
            <h3 class="font-black text-[#002B19] text-lg leading-tight mb-2">{{ product.name }}</h3>
            
            <div class="mb-6">
              <p v-if="userDiscount > 0" class="text-[11px] text-gray-400 font-bold line-through mb-0.5">Bs. {{ parseFloat(product.price_bs).toFixed(2) }}</p>
              <p class="text-xl font-black text-gray-900">
                Bs. {{ (product.price_bs - (product.price_bs * (userDiscount / 100))).toFixed(2) }}
              </p>
            </div>
          </div>

          <button @click="agregarAlCarrito(product)" :disabled="product.stock === 0" 
                  class="w-full border border-[#002B19]/20 text-[#002B19] font-black text-[10px] uppercase tracking-[0.1em] py-3 rounded-full hover:bg-[#002B19] hover:text-white hover:border-[#002B19] transition-colors flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
            <i class="fas fa-shopping-cart"></i> {{ product.stock === 0 ? 'Sin Stock' : 'Añadir al carrito' }}
          </button>
        </div>
      </div>
      
      <div v-if="productosFiltrados.length === 0" class="text-center py-20">
        <i class="fas fa-box-open text-4xl text-gray-300 mb-4"></i>
        <h3 class="text-xl font-black text-gray-500">No hay productos disponibles</h3>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import Swal from 'sweetalert2';
import { addToCart } from '../../store/cart'; 

const API_URL = 'https://forever-api-e5zr.onrender.com/api';
const products = ref([]);
const isLoading = ref(true);
const userDiscount = ref(0); 

const categorias = [
  { id: 'todos', nombre: 'Todos' },
  { id: 'bebidas', nombre: 'Bebidas' },
  { id: 'nutricion', nombre: 'Nutrición' },
  { id: 'cuidado', nombre: 'Cuidado Personal' },
  { id: 'colmena', nombre: 'Colmena' },
  { id: 'combos', nombre: 'Combos' }
];
const categoriaActiva = ref('todos'); 

const productosFiltrados = computed(() => {
  if (!Array.isArray(products.value)) return [];
  if (categoriaActiva.value === 'todos') return products.value;
  const catEncontrada = categorias.find(c => c.id === categoriaActiva.value);
  return products.value.filter(product => product.categoria === catEncontrada.nombre);
});

const fetchProducts = async () => {
  isLoading.value = true;
  try {
    const headers = { 'Accept': 'application/json' };
    const token = localStorage.getItem('auth_token');
    
    const resProd = await fetch(`${API_URL}/products`, { headers });
    if (resProd.ok) {
      const data = await resProd.json();
      products.value = Array.isArray(data) ? data : (data.data || []);
    }

    if (token) {
      headers['Authorization'] = `Bearer ${token}`;
      const resUser = await fetch(`${API_URL}/user`, { headers });
      if (resUser.ok) {
        const user = await resUser.json();
        if (user.tipo_usuario === 'fbo') {
           userDiscount.value = user.fbo?.discount_rate || 30; 
        }
      }
    }
  } catch (error) {
    console.error('Error:', error);
  } finally {
    isLoading.value = false;
  }
};

const agregarAlCarrito = (product) => {
  addToCart(product, userDiscount.value);
  Swal.fire({
    toast: true,
    position: 'top-end',
    icon: 'success',
    title: `${product.name} añadido`,
    showConfirmButton: false,
    timer: 1500,
    timerProgressBar: true
  });
};

onMounted(() => {
  fetchProducts();
});
</script>

<style scoped>
@reference "tailwindcss";
</style>