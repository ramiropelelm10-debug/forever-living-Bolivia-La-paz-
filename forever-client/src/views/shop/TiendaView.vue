<template>
  <div class="bg-[#FAF9F6] min-h-screen pb-20">
    
    <div class="bg-[#005A36] text-white py-16 relative overflow-hidden">
      <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1616238618037-7f89766dbb72?auto=format&fit=crop&q=80')] opacity-10 bg-cover bg-center mix-blend-overlay"></div>
      <div class="max-w-7xl mx-auto px-6 lg:px-10 relative z-10 text-center">
        <h1 class="font-serif italic font-black text-5xl mb-4">Catálogo de Productos</h1>
        <p class="text-green-100 font-medium max-w-2xl mx-auto">Descubre nuestra exclusiva línea de productos naturales a base de Aloe Vera. Salud, nutrición y belleza para ti y tu familia.</p>
        
        <!-- 🔥 AVISO DE DESCUENTO FBO 🔥 -->
        <div v-if="userDiscount > 0" class="mt-6 inline-block bg-[#FFC600] text-[#005A36] px-6 py-2 rounded-full font-black text-xs uppercase tracking-widest shadow-lg animate-bounce">
          <i class="fas fa-star mr-2"></i> Tienes un {{ userDiscount }}% de descuento FBO aplicado
        </div>
      </div>
    </div>

    <div v-if="isLoading" class="flex flex-col items-center justify-center py-20">
      <i class="fas fa-circle-notch fa-spin text-4xl text-[#005A36] mb-4"></i>
      <p class="font-black text-slate-400 uppercase tracking-widest text-xs">Cargando catálogo...</p>
    </div>

    <div v-else>
      
      <div class="max-w-7xl mx-auto px-6 lg:px-10 mt-8 mb-10 flex flex-wrap gap-4 justify-center">
        <button 
          v-for="cat in categorias" 
          :key="cat"
          @click="categoriaActiva = cat"
          :class="categoriaActiva === cat 
            ? 'bg-[#FFC600] text-black shadow-md border-transparent' 
            : 'bg-white text-gray-500 hover:text-[#005A36] border-gray-200 hover:border-[#005A36]'"
          class="border px-6 py-2 rounded-full font-black text-[10px] uppercase tracking-widest transition-all"
        >
          {{ cat }}
        </button>
      </div>

      <div class="max-w-7xl mx-auto px-6 lg:px-10 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
        
        <div v-for="product in productosFiltrados" :key="product.id" class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 hover:shadow-xl transition-all group relative flex flex-col justify-between animate-in fade-in zoom-in-95 duration-300">
          
          <span v-if="product.stock === 0" class="absolute top-4 left-4 bg-red-100 text-red-800 text-[9px] font-black uppercase tracking-widest px-3 py-1 rounded-full z-10">Agotado</span>
          <span v-else-if="product.stock < 20" class="absolute top-4 left-4 bg-amber-100 text-amber-800 text-[9px] font-black uppercase tracking-widest px-3 py-1 rounded-full z-10">Poco Stock</span>

          <div>
            <div class="h-48 w-full flex items-center justify-center mb-6 overflow-hidden relative">
              <img :src="product.foto_persona || 'https://images.unsplash.com/photo-1629198725656-74b830d1fc0d?auto=format&fit=crop&q=80&w=400'" 
                   :alt="product.name" 
                   class="object-contain h-full group-hover:scale-110 transition-transform duration-500">
            </div>
            
            <div class="flex justify-between items-start mb-1">
              <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest">{{ product.categoria || 'Sin Categoría' }}</p>
              <p class="text-[9px] text-[#005A36] font-black uppercase tracking-widest bg-green-50 px-2 py-0.5 rounded">{{ product.cc_value }} CC</p>
            </div>
            
            <h3 class="font-black text-[#005A36] text-lg leading-tight mb-2">{{ product.name }}</h3>
            
            <!-- 🔥 LÓGICA VISUAL DE PRECIOS CON DESCUENTO 🔥 -->
            <div class="mb-6">
              <p v-if="userDiscount > 0" class="text-[11px] text-gray-400 font-bold line-through mb-0.5">Bs. {{ parseFloat(product.price_bs).toFixed(2) }}</p>
              <p class="text-2xl font-black text-gray-900">
                Bs. {{ (product.price_bs - (product.price_bs * (userDiscount / 100))).toFixed(2) }}
              </p>
            </div>
          </div>

          <!-- 🔥 Mandamos el producto y el descuento al carrito 🔥 -->
          <button @click="agregarAlCarrito(product)" :disabled="product.stock === 0" 
                  class="w-full bg-[#FAF9F6] border border-[#005A36]/20 text-[#005A36] group-hover:bg-[#005A36] group-hover:text-white py-3 rounded-xl font-black text-[10px] uppercase tracking-widest transition-colors flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
            <i class="fas fa-shopping-cart"></i> {{ product.stock === 0 ? 'Sin Stock' : 'Añadir al carrito' }}
          </button>
        </div>

      </div>
      
      <div v-if="productosFiltrados.length === 0" class="text-center py-20">
        <i class="fas fa-box-open text-4xl text-gray-300 mb-4"></i>
        <h3 class="text-xl font-black text-gray-500">No hay productos disponibles</h3>
        <p class="text-sm text-gray-400">Intenta seleccionando otra categoría.</p>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import Swal from 'sweetalert2';
import { addToCart } from '../../store/cart'; 

const API_URL = 'http://localhost:8000/api';
const products = ref([]);
const isLoading = ref(true);

// 🔥 Variable para almacenar el descuento del usuario
const userDiscount = ref(0); 

const categorias = ['Todos', 'Bebidas', 'Nutrición', 'Cuidado Personal', 'Colmena', 'Combos'];
const categoriaActiva = ref('Todos'); 

const productosFiltrados = computed(() => {
  if (!Array.isArray(products.value)) return [];
  if (categoriaActiva.value === 'Todos') return products.value;
  return products.value.filter(product => product.categoria === categoriaActiva.value);
});

const fetchProducts = async () => {
  isLoading.value = true;
  try {
    const headers = { 'Accept': 'application/json' };
    const token = localStorage.getItem('auth_token');
    
    // 1. Cargar Productos
    const resProd = await fetch(`${API_URL}/products`, { headers });
    if (resProd.ok) {
      const data = await resProd.json();
      products.value = Array.isArray(data) ? data : (data.data || []);
    }

    // 2. Si está logueado, verificar si es FBO y obtener su descuento
    if (token) {
      headers['Authorization'] = `Bearer ${token}`;
      const resUser = await fetch(`${API_URL}/user`, { headers });
      if (resUser.ok) {
        const user = await resUser.json();
        // Verificamos si tiene el rol FBO y si trae la relación (ajusta 'fbo' a como se llame tu relación en Laravel)
        if (user.tipo_usuario === 'fbo') {
           // Asignamos el descuento (por ejemplo 30%). Ajusta la ruta a la propiedad real de tu BD
           userDiscount.value = user.fbo?.discount_rate || 30; 
        }
      }
    }
  } catch (error) {
    console.error('Error de red al cargar el catálogo:', error);
  } finally {
    isLoading.value = false;
  }
};

const agregarAlCarrito = (product) => {
  // 🔥 Le enviamos el producto y el descuento actual al cerebro del carrito
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