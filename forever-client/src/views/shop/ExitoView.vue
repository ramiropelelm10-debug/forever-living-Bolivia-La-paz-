<template>
  <div class="relative w-full flex-grow min-h-[calc(100vh-80px)] flex items-center justify-center py-12 px-4 overflow-hidden">
    
    <div class="absolute inset-0 z-0 bg-cover bg-center bg-no-repeat" style="background-image: url('/images/bg-pago-exitoso.png');"></div>

    <div class="relative z-10 bg-white rounded-[2.5rem] shadow-[0_25px_60px_rgba(0,49,29,0.3)] p-10 md:p-14 max-w-[580px] w-full text-center animate-in fade-in zoom-in duration-500">

      <div class="relative w-32 h-32 mx-auto mb-6 flex items-center justify-center">
        <div class="absolute top-2 left-4 w-1.5 h-1.5 bg-[#FFC600] rounded-full animate-pulse"></div>
        <div class="absolute bottom-4 left-2 w-2 h-2 bg-[#004225] rounded-full opacity-40"></div>
        <div class="absolute top-4 right-2 w-2 h-2 bg-[#004225] rounded-full opacity-40"></div>
        <div class="absolute bottom-6 right-0 w-1.5 h-1.5 bg-[#FFC600] rounded-full animate-pulse"></div>
        <div class="absolute -top-2 left-1/2 w-1 h-3 bg-[#FFC600] rotate-45 rounded-full"></div>
        <div class="absolute top-1/2 -right-4 w-3 h-1 bg-[#004225] opacity-30 -rotate-12 rounded-full"></div>

        <div class="w-full h-full bg-[#EAF5F0] rounded-full flex items-center justify-center shadow-inner">
          <div class="w-24 h-24 bg-[#D1E8DD] rounded-full flex items-center justify-center shadow-sm">
            <div class="w-16 h-16 bg-[#00311D] rounded-full flex items-center justify-center text-white text-3xl shadow-lg transform hover:scale-110 transition-transform">
              <i class="fas fa-check"></i>
            </div>
          </div>
        </div>
      </div>

      <h1 class="font-serif italic font-black text-[2.5rem] text-[#00311D] mb-4">¡Pago Exitoso!</h1>
      
      <img src="/images/aguila-separador.png" alt="Forever Logo" class="h-10 md:h-12 w-auto mx-auto mb-6 object-contain opacity-90 scale-110" />

      <p class="text-gray-500 text-[15px] font-medium leading-relaxed px-4 md:px-8 mb-12">
        Tu transacción se ha completado con éxito.<br>
        Prepararemos tus productos de Forever Living<br>de inmediato.
      </p>

      <div class="flex justify-between items-start mb-12 relative px-2 md:px-4">
        <div class="absolute top-7 left-[15%] right-[15%] h-[2px] bg-gray-200 -z-10"></div>

        <div class="flex flex-col items-center gap-2 bg-transparent z-10 w-1/4">
          <div class="w-14 h-14 rounded-full border-[1.5px] border-[#00311D] bg-white text-[#00311D] flex items-center justify-center text-[1.3rem] shadow-sm">
            <i class="fas fa-box"></i>
          </div>
          <span class="text-[11px] text-gray-500 font-bold leading-tight mt-1">Pedido<br>Confirmado</span>
        </div>

        <div class="flex flex-col items-center gap-2 bg-transparent z-10 w-1/4">
          <div class="w-14 h-14 rounded-full border-[1.5px] border-[#00311D] bg-white text-[#00311D] flex items-center justify-center text-[1.3rem] shadow-sm">
            <i class="fas fa-money-check-dollar"></i>
          </div>
          <span class="text-[11px] text-gray-500 font-bold leading-tight mt-1">Pago<br>Aprobado</span>
        </div>

        <div class="flex flex-col items-center gap-2 bg-transparent z-10 w-1/4">
          <div class="w-14 h-14 rounded-full border-[1.5px] border-[#00311D] bg-white text-[#00311D] flex items-center justify-center text-[1.3rem] shadow-sm">
            <i class="fas fa-box-open"></i>
          </div>
          <span class="text-[11px] text-gray-500 font-bold leading-tight mt-1">Preparando<br>tu pedido</span>
        </div>

        <div class="flex flex-col items-center gap-2 bg-transparent z-10 w-1/4">
          <div class="w-14 h-14 rounded-full border-[1.5px] border-gray-300 bg-white text-gray-400 flex items-center justify-center text-[1.3rem]">
            <i class="fas fa-truck-fast"></i>
          </div>
          <span class="text-[11px] text-gray-400 font-bold leading-tight mt-1">En camino<br>a ti</span>
        </div>
      </div>

      <button @click="$router.push('/tienda')" class="w-full bg-[#FFC600] text-[#00311D] py-4 rounded-2xl font-black text-[13px] uppercase tracking-widest hover:bg-[#E5B200] hover:scale-[1.02] transition-all shadow-lg flex items-center justify-center gap-3">
        Seguir Comprando <i class="fas fa-chevron-right text-[11px] mt-[1px]"></i>
      </button>

    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { clearCart } from '../../store/cart'; 

const router = useRouter();

onMounted(() => {
  clearCart();
});
</script>

<style scoped>
@reference "tailwindcss";

.animate-in {
  animation: fadeInZoom 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes fadeInZoom {
  from {
    opacity: 0;
    transform: scale(0.95) translateY(15px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}
</style>