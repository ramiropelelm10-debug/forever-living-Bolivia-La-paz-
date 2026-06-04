<template>
  <div class="bg-[#FAF9F6] min-h-screen py-16">
    <div class="max-w-4xl mx-auto px-6">
      
      <h2 class="font-serif italic font-black text-4xl text-[#005A36] mb-8 border-b-2 border-[#FFC600] inline-block pb-2">
        Tu Carrito de Compras
      </h2>

      <div v-if="cart.length === 0" class="bg-white rounded-3xl p-16 text-center shadow-sm border border-slate-100 animate-in fade-in zoom-in-95">
        <i class="fas fa-shopping-basket text-6xl text-slate-200 mb-6 block"></i>
        <h3 class="text-2xl font-black text-slate-700 mb-2">Tu carrito está vacío</h3>
        <p class="text-slate-400 mb-8 font-semibold">Agrega productos naturales para mejorar tu bienestar.</p>
        <button @click="$router.push('/tienda')" class="bg-[#005A36] text-white px-8 py-4 rounded-xl font-black text-[11px] uppercase tracking-widest hover:bg-[#FFC600] hover:text-black transition-colors shadow-lg">
          Ir a la Tienda
        </button>
      </div>

      <div v-else class="bg-white rounded-3xl p-8 shadow-sm border border-slate-100 animate-in fade-in zoom-in-95">
        
        <div class="space-y-6 mb-10">
          <div v-for="item in cart" :key="item.id" class="flex justify-between items-center border-b border-slate-50 pb-6">
            <div class="flex items-center gap-4">
              <div class="w-16 h-16 bg-slate-50 rounded-xl flex items-center justify-center border border-slate-100 p-2 overflow-hidden">
                <img :src="item.foto_persona || 'https://images.unsplash.com/photo-1629198725656-74b830d1fc0d?auto=format&fit=crop&q=80&w=400'" class="object-contain h-full" alt="Producto">
              </div>
              <div>
                <h4 class="font-black text-[#005A36] text-lg">{{ item.name }}</h4>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">
                  Bs. {{ item.price_bs }} x {{ item.quantity }} | {{ (item.cc_value * item.quantity).toFixed(3) }} CC
                </p>
              </div>
            </div>
            
            <div class="flex items-center gap-6">
              <p class="font-black text-xl text-slate-800">Bs. {{ (item.price_bs * item.quantity).toFixed(2) }}</p>
              <button @click="removeFromCart(item.id)" class="w-8 h-8 rounded-full bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-colors flex items-center justify-center" title="Eliminar">
                <i class="fas fa-trash-alt text-xs"></i>
              </button>
            </div>
          </div>
        </div>

        <div class="bg-slate-50 rounded-2xl p-8 border border-slate-100">
          <div class="flex justify-between items-center mb-2">
            <span class="text-slate-500 font-bold uppercase tracking-widest text-[11px]">Total Puntos (CC):</span>
            <span class="font-black text-[#005A36]">{{ cartTotalCC.toFixed(3) }} CC</span>
          </div>
          <div class="flex justify-between items-center mb-8 border-b-2 border-slate-200 pb-4">
            <span class="text-slate-800 font-black uppercase tracking-widest text-lg">Total a Pagar:</span>
            <span class="font-black text-4xl text-[#005A36]">Bs. {{ cartTotal.toFixed(2) }}</span>
          </div>

          <button @click="procesarPagoPayPal" class="w-full bg-[#003087] text-white py-5 rounded-xl font-black text-sm tracking-widest hover:bg-[#001c53] transition-all shadow-lg flex items-center justify-center gap-3">
            <i class="fab fa-paypal text-xl"></i> Pagar con PayPal
          </button>
          <p class="text-center text-[10px] text-slate-400 font-bold mt-4 uppercase tracking-widest">
            <i class="fas fa-lock mr-1"></i> Transacción Cifrada y Segura
          </p>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import Swal from 'sweetalert2';
import { cart, removeFromCart, clearCart, cartTotal, cartTotalCC } from '../../store/cart';

const router = useRouter();
const API_URL = 'http://localhost:8000/api';

const procesarPagoPayPal = async () => {
  const token = localStorage.getItem('auth_token');
  if (!token) {
    return Swal.fire({
      title: '¡Alto ahí!',
      text: 'Debes iniciar sesión o registrarte para poder finalizar tu compra.',
      icon: 'warning',
      confirmButtonColor: '#005A36',
      confirmButtonText: 'Ir a Iniciar Sesión'
    }).then(() => {
      router.push('/login');
    });
  }

  // 🔥 AHORA PEDIMOS NIT Y NOMBRE PARA LA FACTURA 🔥
  const { value: datosFactura } = await Swal.fire({
    title: 'Datos de Facturación',
    html: `
      <p class="text-sm text-gray-500 mb-4 font-sans">Ingresa tus datos para la factura y envío.</p>
      <input id="swal-razon" class="swal2-input font-sans text-sm" placeholder="Nombre o Razón Social (Ej: Juan Pérez)">
      <input id="swal-nit" type="number" class="swal2-input font-sans text-sm" placeholder="NIT / CI (Ej: 1234567)">
      <hr class="my-4 border-gray-200">
      <input id="swal-dir" class="swal2-input font-sans text-sm" placeholder="Dirección de envío">
      <input id="swal-tel" type="number" class="swal2-input font-sans text-sm" placeholder="Teléfono celular">
    `,
    focusConfirm: false,
    showCancelButton: true,
    confirmButtonText: 'Continuar al Pago',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#005A36',
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
        price_bs: item.price_bs
    }));

    const response = await fetch(`${API_URL}/paypal/create`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}` 
      },
      body: JSON.stringify({
        monto_total: cartTotal.value, 
        total_cc: cartTotalCC.value, 
        items: itemsParaEnviar,
        // 🔥 ENVIAMOS EL NIT Y LA RAZÓN SOCIAL AL BACKEND
        razon_social: datosFactura.razon_social,
        nit_ci: datosFactura.nit_ci
      })
    });

    const data = await response.json();

    if (response.ok && data.status === 'CREATED' && data.links) {
      const linkParaPagar = data.links.find(link => link.rel === 'approve');
      if (linkParaPagar) {
        window.location.href = linkParaPagar.href;
      } else {
        Swal.fire('Error', 'No se encontró el enlace de cobro de PayPal.', 'error');
      }
    } else {
      Swal.fire('Error', 'No se pudo generar la orden de pago. Verifica tu conexión.', 'error');
    }

  } catch (error) {
    Swal.fire('Error', 'Problema de comunicación con el servidor.', 'error');
  }
};
</script>

<style scoped>
@reference "tailwindcss";
</style>