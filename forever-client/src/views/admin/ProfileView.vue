<template>
  <div class="bg-[#FAF9F6] min-h-screen py-16">
    <div class="max-w-5xl mx-auto px-6">
      
      <h2 class="font-serif italic font-black text-4xl text-[#005A36] mb-8 border-b-2 border-[#FFC600] inline-block pb-2">
        Mi Panel
      </h2>

      <div v-if="isLoading" class="flex justify-center py-20">
        <i class="fas fa-circle-notch fa-spin text-4xl text-[#005A36]"></i>
      </div>

      <div v-else class="space-y-8 animate-in fade-in zoom-in-95">
        
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
          <div class="bg-[#005A36] p-8 text-white flex flex-col md:flex-row justify-between md:items-center gap-6 relative overflow-hidden">
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
            
            <div class="flex items-center gap-6 relative z-10">
              <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center text-[#005A36] text-2xl font-black shadow-lg border-4 border-[#FFC600]">
                {{ userData.name ? userData.name.charAt(0) : 'U' }}
              </div>
              <div>
                <h3 class="text-3xl font-black mb-1">{{ userData.name }} {{ userData.last_name || '' }}</h3>
                <p class="text-green-200 font-bold text-sm tracking-widest uppercase">
                  <i class="fas fa-id-badge mr-1"></i> {{ userData.tipo_usuario || 'Cliente' }}
                </p>
              </div>
            </div>

            <div v-if="userData.tipo_usuario === 'fbo'" class="bg-black/20 p-4 rounded-2xl border border-white/10 backdrop-blur-sm relative z-10 w-full md:w-64">
              <p class="text-[10px] text-green-200 font-black uppercase tracking-widest mb-1">Mis Puntos CC (Mes Actual)</p>
              <h4 class="text-3xl font-black text-[#FFC600]">{{ totalCcs.toFixed(3) }} <span class="text-sm text-white">CC</span></h4>
              <div class="w-full bg-black/30 h-2 rounded-full mt-3 overflow-hidden">
                <div class="bg-[#FFC600] h-full rounded-full transition-all duration-1000" :style="`width: ${Math.min((totalCcs / 4) * 100, 100)}%`"></div>
              </div>
              <p class="text-[9px] text-white/70 mt-1 text-right">Meta Activo: 4.000 CC</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
          <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
            <h4 class="font-black text-slate-700 uppercase tracking-widest text-[11px]">
              <i class="fas fa-shopping-bag text-[#005A36] mr-2"></i> Historial de Compras
            </h4>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-slate-50 text-[10px] uppercase tracking-widest text-slate-400">
                  <th class="p-4 font-black rounded-l-xl">Fecha</th>
                  <th class="p-4 font-black">Nro. Orden</th>
                  <th class="p-4 font-black">Total</th>
                  <th v-if="userData.tipo_usuario === 'fbo'" class="p-4 font-black">CC Sumados</th>
                  <th class="p-4 font-black text-center rounded-r-xl">Documentos</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="compras.length === 0">
                  <td colspan="5" class="p-10 text-center text-slate-400 font-medium">
                    <i class="fas fa-receipt text-3xl mb-3 block opacity-30"></i>
                    Aún no has realizado ninguna compra.
                  </td>
                </tr>
                <tr v-for="compra in compras" :key="compra.id" class="border-b border-slate-50 hover:bg-slate-50/50 transition">
                  <td class="p-4 text-sm font-bold text-slate-600">{{ compra.fecha }}</td>
                  <td class="p-4 text-xs font-mono text-slate-400">#ORD-{{ compra.id.toString().padStart(5, '0') }}</td>
                  <td class="p-4 text-sm font-black text-[#005A36]">Bs. {{ parseFloat(compra.total_bs).toFixed(2) }}</td>
                  <td v-if="userData.tipo_usuario === 'fbo'" class="p-4 text-sm font-black text-[#FFC600]">{{ parseFloat(compra.total_cc).toFixed(3) }}</td>
                  <td class="p-4 flex justify-center gap-2">
                    <button class="bg-blue-50 text-blue-600 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-blue-600 hover:text-white transition shadow-sm">
                      <i class="fas fa-file-invoice mr-1"></i> Recibo
                    </button>
                    <button v-if="userData.tipo_usuario !== 'fbo'" class="bg-red-50 text-red-600 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-red-600 hover:text-white transition shadow-sm">
                      <i class="fas fa-file-pdf mr-1"></i> Factura
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const API_URL = 'http://localhost:8000/api';
const isLoading = ref(true);

const userData = ref({});
const compras = ref([]);
const totalCcs = ref(0);

const cargarDatos = async () => {
  try {
    const token = localStorage.getItem('auth_token');
    if (!token) return;

    // 1. Cargar Perfil
    const resUser = await fetch(`${API_URL}/user`, {
      headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${token}` }
    });

    if (resUser.ok) {
      userData.value = await resUser.json();
    }

    // 2. Cargar Historial de Compras (SIMULACIÓN HASTA CONECTAR BACKEND)
    // Cuando tengas la ruta en Laravel (Ej: /my-sales), reemplaza este bloque.
    /*
    const resSales = await fetch(`${API_URL}/my-sales`, { ... });
    if(resSales.ok) {
       compras.value = await resSales.json();
       // Calcular total de CC si es FBO
       if(userData.value.tipo_usuario === 'fbo') {
         totalCcs.value = compras.value.reduce((acc, current) => acc + parseFloat(current.total_cc), 0);
       }
    }
    */
    
    // 🔥 DATA FALSA PARA QUE VEAS EL DISEÑO HASTA QUE CONECTEMOS LA BD 🔥
    compras.value = [
      { id: 1042, fecha: '2026-05-24', total_bs: 430.00, total_cc: 0.188 },
      { id: 981, fecha: '2026-04-15', total_bs: 1250.00, total_cc: 0.675 }
    ];
    if (userData.value.tipo_usuario === 'fbo') {
      totalCcs.value = 0.863; // Suma de la data falsa
    }

  } catch (error) {
    console.error("Error cargando el panel", error);
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  cargarDatos();
});
</script>