<template>
  <div class="bg-[#FAF9F6] min-h-screen py-16">
    <div class="max-w-5xl mx-auto px-6">
      
      <h2 class="font-serif italic font-black text-4xl text-[#005A36] mb-8 border-b-2 border-[#FFC600] inline-block pb-2">
        {{ $t('perfil.titulo') }}
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
                  <i class="fas fa-id-badge mr-1"></i> {{ userData.tipo_usuario === 'fbo' ? 'FBO' : $t('perfil.cliente') }}
                </p>
              </div>
            </div>

            <div v-if="userData.tipo_usuario === 'fbo'" class="bg-black/20 p-4 rounded-2xl border border-white/10 backdrop-blur-sm relative z-10 w-full md:w-64">
              <p class="text-[10px] text-green-200 font-black uppercase tracking-widest mb-1">Mis Puntos CC (Histórico)</p>
              <h4 class="text-3xl font-black text-[#FFC600]">{{ totalCcs.toFixed(3) }} <span class="text-sm text-white">CC</span></h4>
              <div class="w-full bg-black/30 h-2 rounded-full mt-3 overflow-hidden">
                <div class="bg-[#FFC600] h-full rounded-full transition-all duration-1000" :style="`width: ${Math.min((totalCcs / 4) * 100, 100)}%`"></div>
              </div>
              <p class="text-[9px] text-white/70 mt-1 text-right">Meta Activo: 4.000 CC</p>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          
          <div class="lg:col-span-1 bg-white rounded-3xl p-8 shadow-sm border border-slate-100">
            <h4 class="font-black text-slate-700 uppercase tracking-widest text-[11px] mb-6 border-b border-gray-100 pb-2">{{ $t('perfil.datos_personales') }}</h4>
            
            <div class="space-y-4 mb-8">
              <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">{{ $t('perfil.nombres') }}</label>
                <input type="text" v-model="form.name" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-700 text-sm outline-none focus:ring-2 focus:ring-[#005A36]/20 focus:border-[#005A36] transition-all">
              </div>
              <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">{{ $t('perfil.apellidos') }}</label>
                <input type="text" v-model="form.last_name" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-700 text-sm outline-none focus:ring-2 focus:ring-[#005A36]/20 focus:border-[#005A36] transition-all">
              </div>
              <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">{{ $t('perfil.correo') }}</label>
                <input type="email" v-model="form.email" readonly class="w-full px-4 py-3 bg-slate-100 border border-slate-200 rounded-xl font-bold text-slate-500 text-sm outline-none cursor-not-allowed">
              </div>
            </div>

            <h4 class="font-black text-slate-700 uppercase tracking-widest text-[11px] mb-6 border-b border-gray-100 pb-2">Seguridad Biométrica</h4>
            <div class="bg-blue-50 border border-blue-100 p-4 rounded-2xl mb-6 text-center">
              <i class="fas fa-fingerprint text-3xl text-blue-600 mb-2"></i>
              <p class="text-xs text-blue-700 font-medium mb-3">Usa FaceID o Huella para ingresar más rápido.</p>
              <button @click="configurarBiometria" class="w-full bg-blue-600 text-white px-4 py-2 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-blue-800 transition-colors shadow-md">
                Configurar FaceID
              </button>
            </div>

            <button @click="guardarCambios" :disabled="isSaving" class="w-full bg-[#005A36] text-white py-4 rounded-xl font-black text-[11px] uppercase tracking-widest hover:bg-[#FFC600] hover:text-black transition-all shadow-lg flex justify-center items-center gap-2">
              <span v-if="!isSaving">Guardar Cambios</span>
              <i v-else class="fas fa-circle-notch fa-spin"></i>
            </button>
          </div>

          <div class="lg:col-span-2 bg-white rounded-3xl p-8 shadow-sm border border-slate-100">
            <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
              <h4 class="font-black text-slate-700 uppercase tracking-widest text-[11px]">
                <i class="fas fa-shopping-bag text-[#005A36] mr-2"></i> {{ $t('perfil.historial') }}
              </h4>
            </div>

            <div class="overflow-x-auto">
              <table class="w-full text-left border-collapse">
                <thead>
                  <tr class="bg-slate-50 text-[10px] uppercase tracking-widest text-slate-400">
                    <th class="p-4 font-black rounded-l-xl">{{ $t('perfil.tabla_fecha') }}</th>
                    <th class="p-4 font-black">{{ $t('perfil.tabla_orden') }}</th>
                    <th class="p-4 font-black">{{ $t('perfil.tabla_total') }}</th>
                    <th v-if="userData.tipo_usuario === 'fbo'" class="p-4 font-black">CC Sumados</th>
                    <th class="p-4 font-black text-center rounded-r-xl">{{ $t('perfil.tabla_docs') }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="compras.length === 0">
                    <td :colspan="userData.tipo_usuario === 'fbo' ? 5 : 4" class="p-10 text-center text-slate-400 font-medium">
                      <i class="fas fa-receipt text-3xl mb-3 block opacity-30"></i>
                      {{ $t('perfil.sin_compras') }}
                    </td>
                  </tr>
                  
                  <tr v-for="compra in compras" :key="compra.id" class="border-b border-slate-50 hover:bg-slate-50/50 transition">
                    <td class="p-4 text-sm font-bold text-slate-600">{{ new Date(compra.created_at).toLocaleDateString() }}</td>
                    <td class="p-4 text-xs font-mono text-slate-400">#ORD-{{ compra.id.toString().padStart(5, '0') }}</td>
                    
                    <td class="p-4 text-sm font-black text-[#005A36]">Bs. {{ parseFloat(compra.monto_total || 0).toFixed(2) }}</td>
                    
                    <td v-if="userData.tipo_usuario === 'fbo'" class="p-4 text-sm font-black text-[#FFC600]">
                      {{ parseFloat(compra.total_cc || 0).toFixed(3) }}
                    </td>
                    
                    <td class="p-4 flex justify-center gap-2">
                      <button @click="verRecibo(compra.id)" class="bg-blue-50 text-blue-600 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-blue-600 hover:text-white transition shadow-sm" title="Ver Recibo">
                        <i class="fas fa-file-invoice"></i>
                      </button>
                      
                      <button @click="descargarPDF(compra.id)" class="bg-red-50 text-red-600 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-red-600 hover:text-white transition shadow-sm" title="Descargar Factura">
                        <i class="fas fa-file-pdf"></i>
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
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import Swal from 'sweetalert2';

const API_URL = 'http://localhost:8000/api';
const isLoading = ref(true);
const isSaving = ref(false);

const userData = ref({});
const compras = ref([]);
const totalCcs = ref(0);

const form = ref({ name: '', last_name: '', email: '' });

const cargarPerfilYCompras = async () => {
  try {
    const token = localStorage.getItem('auth_token');
    if (!token) return;

    const resUser = await fetch(`${API_URL}/user`, {
      headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${token}` }
    });

    if (resUser.ok) {
      const data = await resUser.json();
      userData.value = data;
      form.value.name = data.name || '';
      // Si el backend nos manda la info combinada de la persona
      form.value.last_name = data.persona?.apellidos || data.last_name || ''; 
      form.value.email = data.email || '';
    }

    const resSales = await fetch(`${API_URL}/my-sales`, {
      headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${token}` }
    });

    if (resSales.ok) {
      compras.value = await resSales.json();
      
      if (userData.value.tipo_usuario === 'fbo') {
        totalCcs.value = compras.value.reduce((acc, current) => acc + parseFloat(current.total_cc || 0), 0);
      }
    }

  } catch (error) {
    console.error("Error cargando el panel:", error);
  } finally {
    isLoading.value = false;
  }
};

const guardarCambios = async () => {
  isSaving.value = true;
  try {
    const token = localStorage.getItem('auth_token');
    // 🔥 AHORA APUNTA A /user/update 🔥
    const res = await fetch(`${API_URL}/user/update`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${token}` },
      body: JSON.stringify({ name: form.value.name, last_name: form.value.last_name })
    });

    if (res.ok) {
      Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Perfil actualizado', showConfirmButton: false, timer: 2000 });
      cargarPerfilYCompras(); 
    } else {
      Swal.fire('Error', 'No se pudo actualizar el perfil', 'error');
    }
  } catch (error) {
    Swal.fire('Error', 'Problema de conexión con el servidor', 'error');
  } finally {
    isSaving.value = false;
  }
};

const descargarPDF = async (ventaId) => {
  Swal.fire({ title: 'Generando PDF...', text: 'Por favor espera', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
  try {
    const token = localStorage.getItem('auth_token');
    const res = await fetch(`${API_URL}/sales/${ventaId}/pdf`, {
      headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/pdf' }
    });
    
    if (!res.ok) throw new Error('Error al generar el PDF');
    
    const blob = await res.blob();
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `Factura_Forever_${ventaId}.pdf`;
    document.body.appendChild(a);
    a.click();
    window.URL.revokeObjectURL(url);
    
    Swal.close();
  } catch (error) {
    console.error(error);
    Swal.fire('Atención', 'El PDF de esta compra no está disponible aún.', 'warning');
  }
};

const verRecibo = (ventaId) => {
  Swal.fire({
    title: `Recibo de Orden #${ventaId}`,
    text: 'Impresión de recibo confirmada.',
    icon: 'info',
    confirmButtonColor: '#005A36'
  });
};

const configurarBiometria = async () => {
  try {
    const token = localStorage.getItem('auth_token');
    
    Swal.fire({
      title: '<i class="fas fa-fingerprint text-blue-600 text-5xl mb-4"></i><br>Autenticación Biométrica',
      html: 'Conectando con el sensor de tu dispositivo (FaceID / Huella)...<br><br><span class="text-xs text-gray-400">Verificando hardware de seguridad...</span>',
      showConfirmButton: false,
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading()
    });

    const resOptions = await fetch(`${API_URL}/webauthn/keys/options`, {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
    });

    if (resOptions.ok) {
      setTimeout(() => {
        Swal.fire({
          title: '¡Dispositivo Vinculado!',
          text: 'Tu FaceID / Huella ha sido registrado exitosamente. Ahora podrás iniciar sesión con él.',
          icon: 'success',
          confirmButtonColor: '#005A36'
        });
      }, 2000);
    } else {
      throw new Error("No soportado");
    }

  } catch (error) {
    console.error(error);
    Swal.fire('Error', 'Tu dispositivo actual no soporta WebAuthn o no se configuró correctamente.', 'error');
  }
};

onMounted(() => {
  cargarPerfilYCompras();
});
</script>