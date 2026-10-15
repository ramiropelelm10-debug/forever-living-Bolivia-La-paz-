<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Logs del Sistema (Auditoría)</h1>
      <button @click="fetchLogs" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded shadow transition-colors flex items-center gap-2">
        <i class="fas fa-sync-alt"></i> Actualizar
      </button>
    </div>

    <div v-if="loading" class="text-center py-10">
      <p class="text-gray-500 font-medium">Cargando historial de eventos...</p>
    </div>

    <div v-else class="bg-white rounded-lg shadow-md border border-gray-100 overflow-hidden">
      <div class="overflow-x-auto max-h-[70vh]">
        <table class="min-w-full divide-y divide-gray-200 relative">
          <thead class="bg-gray-50 sticky top-0 z-10">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha y Hora</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acción</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Módulo</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descripción</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-100">
            <tr v-for="log in logs" :key="log.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-500">
                {{ formatFecha(log.created_at) }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ log.user ? log.user.name : 'Sistema / Anónimo' }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap">
                <span :class="colorAccion(log.action)" class="text-xs px-2 py-1 rounded font-bold">
                  {{ log.action }}
                </span>
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 font-medium">
                {{ log.module }}
              </td>
              <td class="px-4 py-3 text-sm text-gray-600">
                {{ log.description }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-400 font-mono">
                {{ log.ip_address || 'N/A' }}
              </td>
            </tr>
            <tr v-if="logs.length === 0">
              <td colspan="6" class="px-6 py-8 text-center text-gray-500">No hay registros en el sistema.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const logs = ref([]);
const loading = ref(true);

const fetchLogs = async () => {
  loading.value = true;
  try {
    const token = localStorage.getItem('auth_token');
    const response = await axios.get('http://localhost:8000/api/admin/logs', {
      headers: { Authorization: `Bearer ${token}` }
    });
    logs.value = response.data.data;
  } catch (error) {
    console.error("Error cargando logs", error);
  } finally {
    loading.value = false;
  }
};

const formatFecha = (fechaStr) => {
  const fecha = new Date(fechaStr);
  return fecha.toLocaleString('es-BO', { 
    year: 'numeric', month: 'short', day: 'numeric', 
    hour: '2-digit', minute: '2-digit', second: '2-digit'
  });
};

const colorAccion = (accion) => {
  if (!accion) return 'bg-gray-100 text-gray-600';
  
  const actions = {
    'CREAR': 'bg-green-100 text-green-800',
    'EDITAR': 'bg-blue-100 text-blue-800',
    'ELIMINAR': 'bg-red-100 text-red-800',
    'LOGIN': 'bg-purple-100 text-purple-800',
    'LOGOUT': 'bg-gray-200 text-gray-800',
  };
  return actions[accion.toUpperCase()] || 'bg-gray-100 text-gray-600';
};

onMounted(() => {
  fetchLogs();
});
</script>

<style scoped>
/* Agregamos esto para asegurar compatibilidad con tus estilos base de Tailwind */
@reference "tailwindcss";
</style>