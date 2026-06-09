<template>
  <div class="bg-[#F8F9FA] min-h-screen pt-12 pb-24 font-sans">
    <div class="max-w-7xl mx-auto px-6 lg:px-10">
      
      <!-- HEADER DEL CARRITO -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-8 gap-4">
        <div class="flex items-start gap-4">
          <div class="text-[#84B59F] text-4xl mt-1">
            <i class="fas fa-leaf"></i>
          </div>
          <div>
            <h1 class="text-4xl font-black text-[#00311D] tracking-tight mb-1">Tu carrito de compras</h1>
            <p class="text-gray-500 font-medium text-sm">Agrega productos naturales para mejorar tu bienestar.</p>
          </div>
        </div>
        <div class="flex items-center gap-2 text-[#4A8B6B] font-medium text-sm bg-green-50 px-4 py-2 rounded-full">
          <i class="fas fa-shield-check"></i>
          <span>Compra 100% segura y protegida</span>
        </div>
      </div>

      <!-- ESTADO VACÍO -->
      <div v-if="cartItems.length === 0" class="bg-white rounded-[2rem] shadow-sm p-16 text-center border border-gray-100 animate-in fade-in zoom-in duration-300">
        <div class="w-24 h-24 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-6 text-[#00311D] text-4xl">
          <i class="fas fa-shopping-cart"></i>
        </div>
        <h2 class="text-2xl font-black text-[#00311D] mb-2">Tu carrito está vacío</h2>
        <p class="text-gray-500 mb-8 max-w-md mx-auto">Parece que aún no has agregado ningún producto. ¡Descubre todos los beneficios del Aloe Vera en nuestra tienda!</p>
        <router-link to="/tienda" class="inline-flex items-center gap-2 bg-[#00311D] text-white px-8 py-3.5 rounded-full font-black text-[11px] uppercase tracking-widest hover:bg-[#FFC600] hover:text-[#00311D] transition-colors">
          <i class="fas fa-arrow-left"></i> Ir a la tienda
        </router-link>
      </div>

      <!-- CONTENIDO DEL CARRITO -->
      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- COLUMNA IZQUIERDA: LISTA DE PRODUCTOS -->
        <div class="lg:col-span-2">
          <div class="bg-white rounded-[2rem] shadow-[0_10px_30px_-15px_rgba(0,0,0,0.08)] p-6 md:p-8 border border-gray-50">
            
            <div class="hidden md:flex justify-between items-center border-b border-gray-100 pb-4 mb-4">
              <span class="w-2/5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Producto</span>
              <span class="w-1/5 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Precio</span>
              <span class="w-1/5 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Cantidad</span>
              <span class="w-1/5 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Subtotal</span>
            </div>

            <div v-for="item in cartItems" :key="item.id" class="flex flex-col md:flex-row items-center justify-between py-6 border-b border-gray-50 gap-6 md:gap-0 group">
              
              <!-- Producto Info -->
              <div class="w-full md:w-2/5 flex items-center gap-4">
                <div class="w-20 h-20 bg-[#F8F9FA] rounded-2xl flex items-center justify-center p-2 flex-shrink-0">
                  <img :src="item.foto_persona || '/images/cat-aloe.png'" :alt="item.name" class="max-h-full object-contain mix-blend-multiply">
                </div>
                <div>
                  <h3 class="font-black text-[#00311D] text-base leading-tight mb-1">{{ item.name }}</h3>
                  <p class="text-xs text-gray-500 mb-2 line-clamp-2">{{ item.description || 'Producto natural a base de Aloe Vera.' }}</p>
                  <span class="inline-flex items-center gap-1 bg-[#F2F8F5] text-[#4A8B6B] text-[9px] font-black uppercase tracking-wider px-2 py-1 rounded">
                    <i class="fas fa-leaf text-[8px]"></i> {{ item.categoria || 'Cuidado Personal' }}
                  </span>
                </div>
              </div>

              <!-- Precio -->
              <div class="w-full md:w-1/5 flex justify-between md:justify-center items-center">
                <span class="md:hidden text-[10px] font-black text-gray-400 uppercase tracking-widest">Precio:</span>
                <span class="font-black text-[#00311D]">{{ formatCC(item.cc_value) }} CC</span>
              </div>

              <!-- Cantidad -->
              <div class="w-full md:w-1/5 flex justify-between md:justify-center items-center">
                <span class="md:hidden text-[10px] font-black text-gray-400 uppercase tracking-widest">Cantidad:</span>
                <div class="flex items-center justify-between border border-gray-200 rounded-full px-4 py-1.5 w-28 bg-white">
                  <button @click="updateQty(item, -1)" class="text-gray-400 hover:text-[#00311D] transition-colors pb-0.5">
                    <i class="fas fa-minus text-xs"></i>
                  </button>
                  <span class="font-bold text-[#00311D] text-sm">{{ item.quantity || item.cantidad || 1 }}</span>
                  <button @click="updateQty(item, 1)" class="text-gray-400 hover:text-[#00311D] transition-colors pb-0.5">
                    <i class="fas fa-plus text-xs"></i>
                  </button>
                </div>
              </div>

              <!-- Subtotal y Eliminar -->
              <div class="w-full md:w-1/5 flex justify-between md:justify-end items-center gap-4">
                <span class="md:hidden text-[10px] font-black text-gray-400 uppercase tracking-widest">Subtotal:</span>
                <span class="font-black text-[#4A8B6B]">{{ formatCC(item.cc_value * (item.quantity || item.cantidad || 1)) }} CC</span>
                
                <button @click="removeItem(item)" class="w-9 h-9 rounded-full border border-gray-200 flex items-center justify-center text-gray-400 hover:text-red-500 hover:border-red-200 hover:bg-red-50 transition-all">
                  <i class="far fa-trash-alt"></i>
                </button>
              </div>

            </div>

            <div class="mt-6 bg-[#F2F8F5] rounded-2xl p-4 flex items-center gap-3">
              <i class="fas fa-leaf text-[#4A8B6B] text-lg"></i>
              <p class="text-[#00311D] text-sm font-medium">Los productos Forever son 100% naturales y de la más alta calidad.</p>
            </div>
          </div>

          <div class="mt-8 text-center">
            <router-link to="/tienda" class="inline-flex items-center gap-2 text-[#00311D] font-black text-[10px] uppercase tracking-[0.15em] hover:text-[#FFC600] transition-colors">
              <i class="fas fa-arrow-left"></i> Seguir Comprando
            </router-link>
          </div>
        </div>

        <!-- COLUMNA DERECHA: RESUMEN DEL PEDIDO -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-[2rem] shadow-[0_10px_30px_-15px_rgba(0,0,0,0.08)] p-6 md:p-8 border border-gray-50 sticky top-28">
            
            <div class="flex items-center gap-3 mb-6">
              <div class="w-10 h-10 rounded-full bg-[#F2F8F5] flex items-center justify-center text-[#4A8B6B]">
                <i class="fas fa-shopping-bag"></i>
              </div>
              <h2 class="text-xl font-black text-[#00311D]">Resumen del pedido</h2>
            </div>

            <div class="space-y-4 mb-6">
              <div class="flex justify-between items-center text-sm font-medium text-gray-600">
                <span>Subtotal ({{ totalItems }} productos)</span>
                <span class="font-bold text-[#00311D]">{{ formatCC(totalCC) }} CC</span>
              </div>
              <div class="flex justify-between items-center text-sm font-medium text-gray-600">
                <span>Envío</span>
                <span class="font-bold text-[#4A8B6B]">Gratis</span>
              </div>
            </div>

            <div class="border-t border-gray-100 pt-6 mb-6">
              <div class="flex justify-between items-end">
                <span class="text-lg font-black text-[#00311D]">Total</span>
                <span class="text-2xl font-black text-[#00311D]">{{ formatCC(totalCC) }} CC</span>
              </div>
            </div>

            <div class="bg-[#F2F8F5] rounded-2xl p-5 mb-6 flex items-start gap-4">
              <div class="text-[#00311D] text-xl pt-0.5">
                <i class="fas fa-truck"></i>
              </div>
              <div>
                <h4 class="font-black text-[#00311D] text-sm mb-1">¡Envío gratis en toda Bolivia!</h4>
                <p class="text-xs text-gray-600 font-medium leading-relaxed">Recibe tus productos en la comodidad de tu hogar.</p>
              </div>
            </div>

            <button @click="finalizarCompra" class="w-full bg-[#00311D] text-white py-4 rounded-full font-black text-[11px] uppercase tracking-widest hover:bg-[#FFC600] hover:text-[#00311D] transition-colors flex items-center justify-center gap-2 mb-6 shadow-md">
              <i class="fas fa-lock"></i> Finalizar Compra
            </button>

            <div class="flex justify-center items-center gap-4 text-3xl">
              <i class="fab fa-cc-visa text-[#1A1F71] opacity-90"></i>
              <i class="fab fa-cc-mastercard text-[#EB001B] opacity-90"></i>
              <i class="fab fa-apple-pay text-black opacity-90 text-[2rem]"></i>
            </div>

          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import Swal from 'sweetalert2';

// Estado local
const cartItems = ref([]);
const activeStorageKey = ref('cart'); // Guardaremos aquí el nombre que tu app está usando

// 1. CARGADOR INTELIGENTE (Busca tu carrito sin importar cómo se llame)
const loadCart = () => {
  const possibleKeys = ['cart', 'cartItems', 'carrito', 'forever_cart'];
  
  for (const key of possibleKeys) {
    const data = localStorage.getItem(key);
    if (data && JSON.parse(data).length > 0) {
      cartItems.value = JSON.parse(data);
      activeStorageKey.value = key; // Recordamos qué llave estabas usando
      return; 
    }
  }
  // Si no encuentra nada, lo deja vacío
  cartItems.value = [];
};

onMounted(() => {
  loadCart();
  // Nos suscribimos a eventos para que se recargue si agregas desde otra pestaña
  window.addEventListener('storage', loadCart);
  window.addEventListener('cart-updated', loadCart);
});

// 2. COMPUTADOS 
const totalItems = computed(() => {
  return cartItems.value.reduce((total, item) => total + (item.quantity || item.cantidad || 1), 0);
});

const totalCC = computed(() => {
  return cartItems.value.reduce((total, item) => {
    const qty = item.quantity || item.cantidad || 1;
    const cc = parseFloat(item.cc_value) || 0;
    return total + (cc * qty);
  }, 0);
});

const formatCC = (value) => Number(value).toFixed(3);

// 3. FUNCIONES DE ACCIÓN
const saveCart = () => {
  // Guardamos usando LA MISMA llave que tu store original usa
  localStorage.setItem(activeStorageKey.value, JSON.stringify(cartItems.value));
  window.dispatchEvent(new Event('cart-updated')); // Actualiza el globito del navbar
};

const updateQty = (item, change) => {
  let currentQty = item.quantity !== undefined ? item.quantity : (item.cantidad || 1);
  const newQty = currentQty + change;
  
  if (newQty > 0) {
    const index = cartItems.value.findIndex(i => i.id === item.id);
    if (index !== -1) {
      if (item.quantity !== undefined) cartItems.value[index].quantity = newQty;
      else cartItems.value[index].cantidad = newQty;
      saveCart();
    }
  }
};

const removeItem = (item) => {
  Swal.fire({
    title: '¿Eliminar producto?',
    text: `¿Quitar ${item.name} del carrito?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#D32F2F',
    cancelButtonColor: '#00311D',
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar',
    reverseButtons: true
  }).then((result) => {
    if (result.isConfirmed) {
      cartItems.value = cartItems.value.filter(i => i.id !== item.id);
      saveCart();
      Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Producto eliminado', showConfirmButton: false, timer: 1500 });
    }
  });
};

const finalizarCompra = () => {
  Swal.fire({
    title: '¡Procesando pedido!',
    text: 'Redirigiendo a la pasarela de pago segura...',
    icon: 'info',
    showConfirmButton: false,
    timer: 2000
  });
};
</script>

<style scoped>
@reference "tailwindcss";
</style>