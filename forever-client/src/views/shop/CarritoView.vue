<template>
  <div class="bg-[#F8F9FA] min-h-screen pt-12 pb-24 font-sans">
    <div class="max-w-7xl mx-auto px-6 lg:px-10">
      
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
        <div class="flex items-center gap-2 text-[#4A8B6B] font-medium text-sm bg-green-50 px-4 py-2 rounded-full shadow-sm">
          <i class="fas fa-shield-check"></i>
          <span>Compra 100% segura y protegida</span>
        </div>
      </div>

      <div v-if="cart.length === 0" class="bg-white rounded-[2rem] shadow-sm p-16 text-center border border-gray-100 animate-in fade-in zoom-in duration-300">
        <div class="w-24 h-24 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-6 text-[#00311D] text-4xl">
          <i class="fas fa-shopping-cart"></i>
        </div>
        <h2 class="text-2xl font-black text-[#00311D] mb-2">Tu carrito está vacío</h2>
        <p class="text-gray-500 mb-8 max-w-md mx-auto">Parece que aún no has agregado ningún producto. ¡Descubre todos los beneficios del Aloe Vera en nuestra tienda!</p>
        <router-link to="/tienda" class="inline-flex items-center gap-2 bg-[#00311D] text-white px-8 py-3.5 rounded-full font-black text-[11px] uppercase tracking-widest hover:bg-[#FFC600] hover:text-[#00311D] transition-colors shadow-md">
          <i class="fas fa-arrow-left"></i> Ir a la tienda
        </router-link>
      </div>

      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2">
          <div class="bg-white rounded-[2rem] shadow-[0_10px_30px_-15px_rgba(0,0,0,0.08)] p-6 md:p-8 border border-gray-50">
            
            <div class="hidden md:flex justify-between items-center border-b border-gray-100 pb-4 mb-4">
              <span class="w-2/5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Producto</span>
              <span class="w-1/5 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Precio</span>
              <span class="w-1/5 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Cantidad</span>
              <span class="w-1/5 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Subtotal</span>
            </div>

            <div v-for="item in cart" :key="item.id" class="flex flex-col md:flex-row items-center justify-between py-6 border-b border-gray-50 gap-6 md:gap-0 group">
              
              <div class="w-full md:w-2/5 flex items-center gap-4">
                <div class="w-20 h-20 bg-[#F8F9FA] rounded-2xl flex items-center justify-center p-2 flex-shrink-0 border border-gray-100">
                  <img :src="item.foto_persona || '/images/cat-aloe.png'" :alt="item.name" class="max-h-full object-contain mix-blend-multiply transition-transform group-hover:scale-110">
                </div>
                <div>
                  <h3 class="font-black text-[#00311D] text-base leading-tight mb-1">{{ item.name }}</h3>
                  <p class="text-xs text-gray-500 mb-2 line-clamp-2">{{ item.description || 'Producto de alta calidad Forever Living.' }}</p>
                  <span class="inline-flex items-center gap-1 bg-[#F2F8F5] text-[#4A8B6B] text-[9px] font-black uppercase tracking-wider px-2 py-1 rounded">
                    <i class="fas fa-leaf text-[8px]"></i> {{ item.categoria || 'Cuidado Personal' }}
                  </span>
                </div>
              </div>

              <div class="w-full md:w-1/5 flex justify-between md:justify-center items-center">
                <span class="md:hidden text-[10px] font-black text-gray-400 uppercase tracking-widest">Precio:</span>
                <div class="flex flex-col md:items-center leading-tight">
                  <span class="font-black text-[#00311D]">{{ formatCC(item.cc_value) }} CC</span>
                  <span class="text-[11px] text-gray-400 font-bold">Bs. {{ formatBs(item.precio_final || item.price_bs) }}</span>
                </div>
              </div>

              <div class="w-full md:w-1/5 flex justify-between md:justify-center items-center">
                <span class="md:hidden text-[10px] font-black text-gray-400 uppercase tracking-widest">Cantidad:</span>
                <div class="flex items-center justify-between border border-gray-200 rounded-full px-4 py-1.5 w-28 bg-white shadow-sm">
                  <button @click="updateQuantity(item.id, -1)" class="text-gray-400 hover:text-[#00311D] transition-colors pb-0.5 outline-none">
                    <i class="fas fa-minus text-xs"></i>
                  </button>
                  <span class="font-bold text-[#00311D] text-sm">{{ item.quantity }}</span>
                  <button @click="updateQuantity(item.id, 1)" class="text-gray-400 hover:text-[#00311D] transition-colors pb-0.5 outline-none">
                    <i class="fas fa-plus text-xs"></i>
                  </button>
                </div>
              </div>

              <div class="w-full md:w-1/5 flex justify-between md:justify-end items-center gap-4">
                <span class="md:hidden text-[10px] font-black text-gray-400 uppercase tracking-widest">Subtotal:</span>
                <div class="flex flex-col items-end leading-tight">
                  <span class="font-black text-[#4A8B6B]">{{ formatCC(item.cc_value * item.quantity) }} CC</span>
                  <span class="text-[11px] text-gray-500 font-bold">Bs. {{ formatBs((item.precio_final || item.price_bs) * item.quantity) }}</span>
                </div>
                
                <button @click="confirmRemove(item)" class="w-9 h-9 rounded-full border border-gray-200 flex items-center justify-center text-gray-400 hover:text-[#D32F2F] hover:border-[#D32F2F] hover:bg-red-50 transition-all outline-none">
                  <i class="far fa-trash-alt"></i>
                </button>
              </div>

            </div>

            <div class="mt-6 bg-[#F2F8F5] rounded-2xl p-4 flex items-center gap-3 border border-[#E6F0EB]">
              <i class="fas fa-leaf text-[#4A8B6B] text-lg"></i>
              <p class="text-[#00311D] text-sm font-medium">Los productos Forever son 100% naturales y de la más alta calidad.</p>
            </div>
          </div>

          <div class="mt-8 text-center md:text-left md:pl-4">
            <router-link to="/tienda" class="inline-flex items-center gap-2 text-[#00311D] font-black text-[10px] uppercase tracking-[0.15em] hover:text-[#FFC600] transition-colors">
              <i class="fas fa-arrow-left"></i> Seguir Comprando
            </router-link>
          </div>
        </div>

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
                <span>Subtotal ({{ cartItemCount }} productos)</span>
                <div class="text-right">
                  <div class="font-bold text-[#00311D]">{{ formatCC(cartTotalCC) }} CC</div>
                  <div class="text-xs text-gray-400 font-bold">Bs. {{ formatBs(cartTotal) }}</div>
                </div>
              </div>
              <div class="flex justify-between items-center text-sm font-medium text-gray-600">
                <span>Envío</span>
                <span class="font-bold text-[#4A8B6B]">Gratis</span>
              </div>
            </div>

            <div class="border-t border-gray-100 pt-6 mb-6">
              <div class="flex justify-between items-end">
                <span class="text-lg font-black text-[#00311D]">Total</span>
                <div class="text-right">
                  <div class="text-2xl font-black text-[#00311D]">{{ formatCC(cartTotalCC) }} CC</div>
                  <div class="text-sm font-black text-gray-500">Bs. {{ formatBs(cartTotal) }}</div>
                </div>
              </div>
            </div>

            <div class="bg-[#F2F8F5] rounded-2xl p-5 mb-6 flex items-start gap-4 border border-[#E6F0EB]">
              <div class="text-[#00311D] text-xl pt-0.5">
                <i class="fas fa-truck"></i>
              </div>
              <div>
                <h4 class="font-black text-[#00311D] text-sm mb-1">¡Envío gratis en toda Bolivia!</h4>
                <p class="text-xs text-gray-600 font-medium leading-relaxed">Recibe tus productos en la comodidad de tu hogar.</p>
              </div>
            </div>

            <button @click="procesarPagoPayPal" class="w-full bg-[#00311D] text-white py-4 rounded-full font-black text-[11px] uppercase tracking-widest hover:bg-[#FFC600] hover:text-[#00311D] transition-all flex items-center justify-center gap-2 mb-6 shadow-[0_4px_14px_0_rgba(0,49,29,0.39)] hover:shadow-[0_6px_20px_rgba(255,198,0,0.23)]">
              <i class="fas fa-lock"></i> Finalizar Compra
            </button>

            <div class="flex justify-center items-center gap-6 text-3xl">
              <i class="fab fa-cc-visa text-[#1A1F71] opacity-90"></i>
              <i class="fab fa-cc-mastercard text-[#EB001B] opacity-90"></i>
              <i class="fab fa-apple-pay text-black opacity-90 text-[2.2rem]"></i>
            </div>

          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import Swal from 'sweetalert2';
import { cart, updateQuantity, removeFromCart, clearCart, cartTotalCC, cartItemCount, cartTotal } from '../../store/cart';

const router = useRouter();
const API_URL = 'http://localhost:8000/api';

// Formateadores de moneda
const formatCC = (value) => Number(value || 0).toFixed(3);
const formatBs = (value) => Number(value || 0).toFixed(2);

// Eliminar producto
const confirmRemove = (item) => {
  Swal.fire({
    title: '¿Eliminar producto?',
    text: `¿Estás seguro de quitar ${item.name} de tu carrito?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#D32F2F',
    cancelButtonColor: '#00311D',
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar',
    reverseButtons: true
  }).then((result) => {
    if (result.isConfirmed) {
      removeFromCart(item.id);
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'Producto eliminado',
        showConfirmButton: false,
        timer: 1500,
        timerProgressBar: true
      });
    }
  });
};

// 🔥 TU FUNCIÓN DE PAGO CORREGIDA Y PULIDA 🔥
const procesarPagoPayPal = async () => {
  const token = localStorage.getItem('auth_token');
  if (!token) {
    return Swal.fire({
      title: '¡Alto ahí!',
      text: 'Debes iniciar sesión o registrarte para poder finalizar tu compra.',
      icon: 'warning',
      confirmButtonColor: '#00311D',
      confirmButtonText: 'Ir a Iniciar Sesión'
    }).then(() => {
      router.push('/login');
    });
  }

  // Formulario para Facturación y Envío
  const { value: datosFactura } = await Swal.fire({
    title: 'Datos de Facturación',
    html: `
      <p class="text-sm text-gray-500 mb-4 font-sans">Ingresa tus datos para la factura y envío.</p>
      <input id="swal-razon" class="swal2-input font-sans text-sm border border-gray-200" placeholder="Nombre o Razón Social (Ej: Juan Pérez)">
      <input id="swal-nit" type="number" class="swal2-input font-sans text-sm border border-gray-200" placeholder="NIT / CI (Ej: 1234567)">
      <hr class="my-4 border-gray-200">
      <input id="swal-dir" class="swal2-input font-sans text-sm border border-gray-200" placeholder="Dirección de envío">
      <input id="swal-tel" type="number" class="swal2-input font-sans text-sm border border-gray-200" placeholder="Teléfono celular">
    `,
    focusConfirm: false,
    showCancelButton: true,
    confirmButtonText: 'Continuar al Pago',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#00311D',
    preConfirm: () => {
      const razon_social = document.getElementById('swal-razon').value || 'Sin Nombre';
      const nit_ci = document.getElementById('swal-nit').value || '0';
      const direccion = document.getElementById('swal-dir').value;
      const telefono = document.getElementById('swal-tel').value;
      
      if (!direccion || !telefono) {
        Swal.showValidationMessage('Por favor completa al menos tu dirección y teléfono.');
      }
      return { razon_social, nit_ci, direccion, telefono };
    }
  });

  if (!datosFactura) return;

  try {
    Swal.fire({
      title: 'Conectando con PayPal...',
      html: 'Generando tu enlace de pago seguro.',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    const itemsParaEnviar = cart.value.map(item => ({
        id: item.id,
        quantity: item.quantity,
        price_bs: item.precio_final || item.price_bs
    }));

    // Calculamos el monto en DÓLARES (Dividiendo el total en Bs entre 6.96)
    const montoEnDolares = (cartTotal.value / 6.96).toFixed(2);

    const response = await fetch(`${API_URL}/paypal/create`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}` 
      },
      body: JSON.stringify({
        monto_total: montoEnDolares, // MANDAMOS DÓLARES A PAYPAL
        total_cc: cartTotalCC.value, 
        items: itemsParaEnviar,
        razon_social: datosFactura.razon_social,
        nit_ci: datosFactura.nit_ci
      })
    });

    const data = await response.json();

    // 🔥 BUSCAMOS EL LINK CORRECTO PARA APROBAR EL PAGO 🔥
    if (response.ok && data.id && data.links) {
      const linkParaPagar = data.links.find(link => link.rel === 'approve');
      
      if (linkParaPagar) {
        // Redirigimos al cliente a PayPal
        window.location.href = linkParaPagar.href;
      } else {
        Swal.fire('Error', 'No se encontró el enlace oficial de cobro de PayPal.', 'error');
      }
    } else {
      Swal.fire('Error', 'No se pudo generar la orden de pago. Revisa las credenciales en tu servidor.', 'error');
      console.log("Error desde Laravel/PayPal:", data);
    }

  } catch (error) {
    Swal.fire('Error', 'Problema de comunicación con el servidor.', 'error');
    console.error("Fallo de conexión:", error);
  }
};
</script>

<style scoped>
@reference "tailwindcss";

.animate-in {
  animation: fadeInZoom 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes fadeInZoom {
  from {
    opacity: 0;
    transform: scale(0.95) translateY(10px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}
</style>