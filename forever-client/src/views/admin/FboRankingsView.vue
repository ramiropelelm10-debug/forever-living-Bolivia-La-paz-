<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Ranking de FBOs (Mes Actual)</h1>
      <button @click="fetchRankings" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded shadow transition-colors">
        Actualizar Datos
      </button>
    </div>

    <!-- Indicador de carga -->
    <div v-if="loading" class="text-center py-10">
      <p class="text-gray-500 font-medium">Calculando Puntos CC y armando la red...</p>
    </div>

    <!-- Tabla de Ranking -->
    <div v-else class="overflow-x-auto bg-white rounded-lg shadow-md border border-gray-100">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Top</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Distribuidor (FBO)</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patrocinador</th>
            <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Total CC</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="(fbo, index) in rankings" :key="fbo.id" class="hover:bg-gray-50 transition-colors">
            <!-- Medallas para el Top 3, números para el resto -->
            <td class="px-6 py-4 whitespace-nowrap">
              <span v-if="index === 0" class="text-2xl" title="1er Lugar">🥇</span>
              <span v-else-if="index === 1" class="text-2xl" title="2do Lugar">🥈</span>
              <span v-else-if="index === 2" class="text-2xl" title="3er Lugar">🥉</span>
              <span v-else class="text-sm font-bold text-gray-500 ml-2">#{{ index + 1 }}</span>
            </td>
            
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
              {{ fbo.name }}
            </td>
            
            <!-- Si no tiene sponsor, mostramos Sede Central para proteger la estructura -->
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              <span v-if="fbo.sponsor" class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
                {{ fbo.sponsor.name }}
              </span>
              <span v-else class="text-gray-400 italic">Directo / Sede Central</span>
            </td>
            
            <!-- Aquí leemos la propiedad dinámica que creó Laravel con withSum() -->
            <td class="px-6 py-4 whitespace-nowrap text-sm font-black text-green-600">
              {{ Number(fbo.ventas_sum_total_cc || 0).toFixed(3) }} CC
            </td>
          </tr>
          
          <!-- Mensaje si no hay ventas en el mes -->
          <tr v-if="rankings.length === 0">
            <td colspan="4" class="px-6 py-8 text-center text-gray-500">
              Aún no hay FBOs con compras registradas en este mes.
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

// Variables reactivas
const rankings = ref([]);
const loading = ref(true);

// Función para llamar a tu API
const fetchRankings = async () => {
  loading.value = true;
  try {
    // Tomamos el token correcto de tu sistema
    const token = localStorage.getItem('auth_token'); 
    
    // Hacemos la petición al backend de Laravel
    const response = await axios.get('http://localhost:8000/api/admin/fbo-rankings', {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json'
      }
    });
    
    rankings.value = response.data.data;
  } catch (error) {
    console.error("Error obteniendo el ranking:", error);
    // Mensaje opcional para que sepas si el error es de permisos o de red
    if (error.response && error.response.status === 401) {
      alert("Tu sesión expiró o no tienes permisos.");
    } else {
      alert("Hubo un problema al cargar los datos. Revisa la consola.");
    }
  } finally {
    loading.value = false;
  }
};

// Se ejecuta automáticamente al abrir la pantalla
onMounted(() => {
  fetchRankings();
});
</script>