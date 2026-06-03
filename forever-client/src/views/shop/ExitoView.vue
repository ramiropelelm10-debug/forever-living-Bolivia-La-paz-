<template>
  <div class="bg-[#FAF9F6] min-h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full bg-white rounded-3xl p-10 text-center shadow-lg border border-slate-100 animate-in zoom-in duration-500">
      
      <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
        <i class="fas fa-check-circle text-5xl text-[#005A36]"></i>
      </div>
      
      <h2 class="font-serif italic font-black text-3xl text-[#005A36] mb-4">¡Pago Exitoso!</h2>
      <p class="text-slate-500 font-semibold mb-8">
        Tu transacción se ha completado con éxito. Prepararemos tus productos de Forever Living de inmediato.
      </p>
      
      <button @click="$router.push('/tienda')" class="w-full bg-[#FFC600] text-black px-8 py-4 rounded-xl font-black text-sm uppercase tracking-widest hover:bg-[#e6b300] transition-colors shadow-md">
        Seguir Comprando
      </button>
      
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { clearCart } from '../../store/cart'; // Asegúrate de que esta ruta apunte a tu store real

onMounted(() => {
  // Cuando el cliente llega a esta pantalla, le vaciamos el carrito automáticamente
  clearCart();
});
</script>

<style scoped>
@reference "tailwindcss";
</style>