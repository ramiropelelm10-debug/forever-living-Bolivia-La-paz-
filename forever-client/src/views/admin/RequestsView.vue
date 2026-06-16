<template>
  <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8 animate-in fade-in zoom-in-95 duration-300">
    
    <div class="flex justify-between items-center mb-8">
      <div>
        <h2 class="text-2xl font-black text-[#4a5d23] uppercase tracking-tight">Solicitudes Pendientes</h2>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Gestión de afiliaciones y pedidos</p>
      </div>
      <button @click="cargarSolicitudes" class="bg-[#b48a2d] text-white px-6 py-2.5 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-[#8a6a22] transition-colors shadow-lg shadow-[#b48a2d]/20 flex items-center gap-2">
        <i class="fas fa-sync-alt" :class="{ 'fa-spin': isLoading }"></i> Actualizar
      </button>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="border-b-2 border-slate-100">
            <th class="py-4 px-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Fecha</th>
            <th class="py-4 px-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Nombre del Aspirante</th>
            <th class="py-4 px-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Correo Electrónico</th>
            <th class="py-4 px-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Tipo de Cuenta</th>
            <th class="py-4 px-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Acciones</th>
          </tr>
        </thead>
        <tbody class="text-sm">
          
          <tr v-if="isLoading">
            <td colspan="5" class="py-12 text-center text-slate-400 font-bold text-xs uppercase tracking-widest">
              <i class="fas fa-circle-notch fa-spin text-2xl text-[#4a5d23] mb-3 block"></i>
              Buscando solicitudes en la base de datos...
            </td>
          </tr>

          <tr v-else-if="solicitudes.length > 0" v-for="solicitud in solicitudes" :key="solicitud.id" class="border-b border-slate-50 hover:bg-slate-50 transition-colors">
            <td class="py-4 px-4 font-bold text-slate-400 text-xs">{{ formatearFecha(solicitud.created_at) }}</td>
            <td class="py-4 px-4 font-black text-[#4a5d23]">{{ solicitud.name }} {{ solicitud.last_name }}</td>
            <td class="py-4 px-4 font-semibold text-slate-500">{{ solicitud.email }}</td>
            <td class="py-4 px-4 text-center">
              <span v-if="solicitud.tipo_usuario === 'fbo'" class="bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest">
                Pendiente FBO
              </span>
              <span v-else class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest">
                Pendiente Cliente
              </span>
            </td>
            <td class="py-4 px-4 flex justify-center gap-3">
              <button @click="aprobarSolicitud(solicitud.id, solicitud.tipo_usuario)" class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 hover:bg-emerald-500 hover:text-white transition-all flex items-center justify-center shadow-sm" title="Aprobar">
                <i class="fas fa-check"></i>
              </button>
              <button @click="rechazarSolicitud(solicitud.id)" class="w-9 h-9 rounded-xl bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-all flex items-center justify-center shadow-sm" title="Rechazar">
                <i class="fas fa-times"></i>
              </button>
            </td>
          </tr>

          <tr v-else>
            <td colspan="5" class="py-16 text-center">
              <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-inbox text-slate-300 text-2xl"></i>
              </div>
              <h3 class="text-slate-800 font-black text-lg">Todo al día</h3>
              <p class="text-slate-400 text-sm font-semibold mt-1">No hay afiliaciones nuevas esperando revisión.</p>
            </td>
          </tr>

        </tbody>
      </table>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import Swal from 'sweetalert2';

// 🔥 RUTA CORREGIDA: Apuntando a tu backend en la nube
const API_URL = 'https://forever-api-e5zr.onrender.com/api';
const solicitudes = ref([]);
const isLoading = ref(true);

// Cabeceras con el token de seguridad del Administrador
const getHeaders = () => {
  return {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
  };
};

// ==========================================
// 1. OBTENER SOLICITUDES DESDE LARAVEL
// ==========================================
const cargarSolicitudes = async () => {
  isLoading.value = true;
  try {
    const res = await fetch(`${API_URL}/admin/requests`, { headers: getHeaders() });
    const data = await res.json();
    if (res.ok) {
      solicitudes.value = data; // Guardamos los datos de la DB en la tabla
    }
  } catch (error) {
    console.error("Error cargando solicitudes:", error);
  } finally {
    isLoading.value = false;
  }
};

// Cargar automáticamente al entrar a la pantalla
onMounted(() => {
  cargarSolicitudes();
});

// ==========================================
// 2. LÓGICA DE APROBACIÓN / RECHAZO
// ==========================================
const aprobarSolicitud = (id, tipo) => {
  const rolTexto = tipo === 'fbo' ? 'Empresario FBO' : 'Cliente';
  
  Swal.fire({
    title: `¿Aprobar como ${rolTexto}?`,
    text: "El usuario será activado y tendrá acceso al sistema.",
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#4a5d23', 
    cancelButtonColor: '#94a3b8',
    confirmButtonText: 'Sí, aprobar',
    cancelButtonText: 'Cancelar',
    reverseButtons: true
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        const res = await fetch(`${API_URL}/admin/users/${id}/respond`, { 
          method: 'POST', 
          headers: getHeaders(),
          body: JSON.stringify({ status: 'aprobado' })
        });
        
        if (res.ok) {
          Swal.fire({ title: '¡Aprobado!', text: 'El usuario ha sido activado exitosamente.', icon: 'success', confirmButtonColor: '#4a5d23' });
          cargarSolicitudes(); // Recargar la tabla
        } else {
          const errorData = await res.json();
          Swal.fire('Error', errorData.message || 'Error al aprobar la solicitud.', 'error');
        }
      } catch (error) {
        Swal.fire('Error', 'No se pudo comunicar con el servidor.', 'error');
      }
    }
  });
};

const rechazarSolicitud = (id) => {
  Swal.fire({
    title: '¿Rechazar solicitud?',
    text: "Esta cuenta pasará a estado rechazado.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444', 
    cancelButtonColor: '#94a3b8',
    confirmButtonText: 'Sí, rechazar',
    cancelButtonText: 'Cancelar',
    reverseButtons: true
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        const res = await fetch(`${API_URL}/admin/users/${id}/respond`, { 
          method: 'POST', 
          headers: getHeaders(),
          body: JSON.stringify({ status: 'rechazado' })
        });
        
        if (res.ok) {
          Swal.fire({ title: 'Rechazada', text: 'La solicitud ha sido rechazada.', icon: 'info', confirmButtonColor: '#4a5d23' });
          cargarSolicitudes(); // Recargar la tabla
        } else {
          const errorData = await res.json();
          Swal.fire('Error', errorData.message || 'Error al rechazar la solicitud.', 'error');
        }
      } catch (error) {
        Swal.fire('Error', 'Hubo un error al rechazar.', 'error');
      }
    }
  });
};

// ==========================================
// UTILERÍA: Formatear Fecha
// ==========================================
const formatearFecha = (fechaString) => {
  if (!fechaString) return 'Sin fecha';
  const opciones = { day: '2-digit', month: 'short', year: 'numeric' };
  return new Date(fechaString).toLocaleDateString('es-ES', opciones);
};
</script>

<style scoped>
@reference "tailwindcss";
</style>