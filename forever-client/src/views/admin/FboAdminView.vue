<template>
  <div class="animate-fade-in space-y-6 relative">
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 flex items-center gap-4">
        <div class="bg-emerald-100 text-emerald-600 p-4 rounded-2xl text-2xl"><i class="fas fa-id-badge"></i></div>
        <div>
          <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Total FBOs</p>
          <h3 class="text-2xl font-black text-slate-800">{{ fbos.length }}</h3>
        </div>
      </div>
      <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 flex items-center gap-4 text-emerald-600">
        <i class="fas fa-check-circle text-3xl"></i>
        <p class="font-black uppercase italic text-sm">Red Forever Activa</p>
      </div>
    </div>

    <section class="bg-white p-8 rounded-[3rem] shadow-xl border border-slate-100">
      <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <h2 class="text-2xl font-black text-slate-800 uppercase italic leading-none">Directorio de Liderazgo</h2>
        <div class="flex gap-3">
          <input type="text" v-model="searchQuery" placeholder="Buscar líder..." 
            class="pl-6 pr-4 py-3 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-emerald-500 outline-none transition font-bold text-sm">
          <button @click="showModal = true" class="bg-emerald-600 text-white px-6 py-3 rounded-2xl hover:bg-emerald-700 transition shadow-lg shadow-emerald-100 font-black uppercase text-xs">
            Registrar Nuevo FBO
          </button>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <thead>
            <tr class="text-slate-400 uppercase text-[10px] font-black tracking-widest border-b border-slate-100">
              <th class="p-4">Distribuidor / ID</th>
              <th class="p-4">CI (DNI)</th>
              <th class="p-4 text-center">Descuento (%)</th>
              <th class="p-4 text-right">Fecha Registro</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="fbo in filteredFbos" :key="fbo.id" class="hover:bg-slate-50/50 transition">
              <td class="p-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full bg-emerald-600 flex items-center justify-center text-white font-black text-xs uppercase">
                    {{ fbo.user?.persona?.nombres?.[0] || 'F' }}
                  </div>
                  <div>
                    <p class="font-black text-slate-700 text-sm leading-none">
                      {{ fbo.user?.persona?.nombres }} {{ fbo.user?.persona?.apellidos }}
                    </p>
                    <p class="text-[10px] text-slate-400 font-mono italic">ID: {{ fbo.fbo_id }}</p>
                  </div>
                </div>
              </td>
              <td class="p-4 font-bold text-slate-500 text-sm">{{ fbo.user?.persona?.ci || 'S/N' }}</td>
              <td class="p-4 text-center font-black text-emerald-600">{{ fbo.discount_rate }}%</td>
              <td class="p-4 text-right text-xs text-slate-400">{{ new Date(fbo.created_at).toLocaleDateString() }}</td>
            </tr>
            <tr v-if="fbos.length === 0">
              <td colspan="4" class="p-20 text-center text-slate-300 font-black uppercase tracking-widest text-xs">No hay distribuidores registrados</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <div v-if="showModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div class="bg-white w-full max-w-2xl rounded-[3rem] shadow-2xl overflow-hidden animate-pop">
        <div class="bg-emerald-600 p-8 text-white flex justify-between items-center">
          <h2 class="text-2xl font-black uppercase italic">Nuevo FBO Forever</h2>
          <button @click="showModal = false" class="text-white/50 hover:text-white transition text-2xl"><i class="fas fa-times"></i></button>
        </div>

        <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
          <input type="text" v-model="newFbo.name" placeholder="Nombres" class="w-full p-4 bg-slate-50 border-2 border-slate-100 rounded-2xl font-bold focus:border-emerald-500 outline-none transition text-sm">
          <input type="text" v-model="newFbo.last_name" placeholder="Apellidos" class="w-full p-4 bg-slate-50 border-2 border-slate-100 rounded-2xl font-bold focus:border-emerald-500 outline-none transition text-sm">
          <input type="text" v-model="newFbo.fbo_id" placeholder="ID FBO (Único)" class="w-full p-4 bg-slate-50 border-2 border-slate-100 rounded-2xl font-bold focus:border-emerald-500 outline-none transition text-sm">
          <input type="text" v-model="newFbo.dni" placeholder="CI o NIT" class="w-full p-4 bg-slate-50 border-2 border-slate-100 rounded-2xl font-bold focus:border-emerald-500 outline-none transition text-sm">
          <input type="email" v-model="newFbo.email" placeholder="Correo electrónico" class="w-full p-4 bg-slate-50 border-2 border-slate-100 rounded-2xl font-bold focus:border-emerald-500 outline-none transition text-sm">
          <input type="number" v-model="newFbo.discount_rate" placeholder="Descuento (%)" class="w-full p-4 bg-slate-50 border-2 border-slate-100 rounded-2xl font-bold focus:border-emerald-500 outline-none transition text-sm">
        </div>

        <div class="p-8 bg-slate-50 flex gap-4">
          <button @click="showModal = false" class="flex-1 py-4 font-black text-slate-400 uppercase text-xs">Cancelar</button>
          <button @click="saveFbo" class="flex-[2] bg-emerald-600 text-white py-4 rounded-2xl font-black uppercase text-xs shadow-lg shadow-emerald-200 hover:bg-emerald-700 transition">
            Crear Distribuidor
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import Swal from 'sweetalert2';

const API_URL = 'http://localhost:8000/api';
const fbos = ref([]);
const searchQuery = ref('');
const showModal = ref(false);

const newFbo = ref({ name: '', last_name: '', fbo_id: '', dni: '', email: '', discount_rate: 0 });

// 🔥 Conexión directa con Fetch a tu API
const loadData = async () => {
  try {
    const token = localStorage.getItem('auth_token');
    const res = await fetch(`${API_URL}/fbos`, {
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`
      }
    });
    
    if (res.ok) {
      const data = await res.json();
      fbos.value = data.data || data || [];
    }
  } catch (error) { 
    console.error("Error al cargar fbos:", error); 
  }
};

const saveFbo = async () => {
  Swal.fire({ title: 'Procesando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
  try {
    const token = localStorage.getItem('auth_token');
    const res = await fetch(`${API_URL}/fbos`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify(newFbo.value)
    });

    if (res.ok) {
      Swal.fire('¡Registrado!', 'FBO guardado exitosamente.', 'success');
      showModal.value = false;
      newFbo.value = { name: '', last_name: '', fbo_id: '', dni: '', email: '', discount_rate: 0 };
      loadData();
    } else {
      const errorData = await res.json();
      throw new Error(JSON.stringify(errorData));
    }
  } catch (error) {
    console.error(error);
    Swal.fire({ title: 'No se pudo crear', text: 'Revisa los datos e intenta de nuevo.', icon: 'error' });
  }
};

const filteredFbos = computed(() => {
  return fbos.value.filter(f => {
    // Protección anti-nulos
    const nombres = f.user?.persona?.nombres || '';
    const apellidos = f.user?.persona?.apellidos || '';
    const full = `${nombres} ${apellidos}`.toLowerCase();
    
    return full.includes(searchQuery.value.toLowerCase()) || (f.fbo_id && f.fbo_id.includes(searchQuery.value));
  });
});

onMounted(loadData);
</script>

<style scoped>
/* Estilos limpios sin @apply para evitar errores de compilación */
.animate-fade-in { animation: fadeIn 0.5s ease-out; }
.animate-pop { animation: pop 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }

@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes pop { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
</style>