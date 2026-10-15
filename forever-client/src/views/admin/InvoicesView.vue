<template>
  <div class="p-8 bg-gray-50 min-h-screen">
    <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
      
      <!-- Controles Superiores -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
          <h2 class="text-2xl font-black text-slate-800 italic uppercase tracking-tight">Historial de Ventas Bolivia</h2>
          <p class="text-xs text-slate-500 font-semibold tracking-widest uppercase">Reportes y Control Fiscal</p>
        </div>

        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
          
          <!-- 🔥 NUEVO: FILTRO DE FECHAS -->
          <select 
            v-model="dateFilter" 
            class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-[#008751] focus:border-[#008751] block p-2.5 outline-none transition-all cursor-pointer font-medium"
          >
            <option value="todas">Todas las fechas</option>
            <option value="hoy">Hoy</option>
            <option value="semana">Últimos 7 días</option>
            <option value="mes">Este mes</option>
            <option value="anio">Este año</option>
          </select>

          <!-- BARRA DE BÚSQUEDA DE TEXTO -->
          <div class="relative w-full md:w-72">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
            </div>
            <input
              v-model="searchQuery"
              type="text"
              class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-[#008751] focus:border-[#008751] block w-full pl-10 p-2.5 transition-all outline-none"
              placeholder="Buscar cliente, NIT o factura..."
            >
          </div>

          <button class="bg-[#008751] hover:bg-green-800 text-white font-bold py-2.5 px-4 rounded-lg text-sm flex items-center gap-2 transition-colors whitespace-nowrap">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
            EXPORTAR EXCEL
          </button>
        </div>
      </div>

      <!-- Tabla -->
      <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-600">
          <thead class="text-xs text-gray-400 uppercase bg-gray-50/50 border-b border-gray-100">
            <tr>
              <th scope="col" class="px-4 py-3 font-bold tracking-wider">Factura</th>
              <th scope="col" class="px-4 py-3 font-bold tracking-wider">Fecha</th>
              <th scope="col" class="px-4 py-3 font-bold tracking-wider">Cliente (NIT/CI)</th>
              <th scope="col" class="px-4 py-3 font-bold tracking-wider">Total (Bs)</th>
              <th scope="col" class="px-4 py-3 font-bold tracking-wider text-red-500">IVA (13%)</th>
              <th scope="col" class="px-4 py-3 font-bold tracking-wider text-blue-600">CC</th>
              <th scope="col" class="px-4 py-3 font-bold tracking-wider text-center">Acción</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="factura in facturasFiltradas" :key="factura.id" class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
              <td class="px-4 py-4 font-mono text-gray-500">{{ factura.codigo }}</td>
              <td class="px-4 py-4 font-medium text-gray-700">{{ formatearFecha(factura.fecha) }}</td>
              <td class="px-4 py-4">
                <div class="font-bold text-gray-800 capitalize">{{ factura.cliente }}</div>
                <div class="text-xs text-gray-400 italic">NIT: {{ factura.nit }}</div>
              </td>
              <td class="px-4 py-4 font-bold text-green-600">Bs. {{ factura.total }}</td>
              <td class="px-4 py-4 font-bold text-red-500">Bs. {{ factura.iva }}</td>
              <td class="px-4 py-4 font-bold text-blue-600">{{ factura.cc }}</td>
              <td class="px-4 py-4 text-center">
                <button class="bg-red-50 text-red-600 font-bold py-1.5 px-4 rounded-md text-xs hover:bg-red-100 transition-colors flex items-center justify-center gap-1 mx-auto border border-red-100">
                  PDF
                </button>
              </td>
            </tr>
            
            <tr v-if="facturasFiltradas.length === 0">
              <td colspan="7" class="px-4 py-8 text-center text-gray-400">
                No se encontraron facturas para los filtros seleccionados.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

// Variables Reactivas para los filtros
const searchQuery = ref('')
const dateFilter = ref('todas')

// Datos de prueba con el campo "fecha" añadido
const facturas = ref([
  { id: 1, codigo: 'FAC-PP-6AB4488278CB1', cliente: 'ramirezzzz', nit: '8888888', total: '439.52', iva: '57.14', cc: '122.059', fecha: '2026-09-24T10:30:00' }, // Hoy
  { id: 2, codigo: 'FAC-PP-6A36DF897BEE0', cliente: 'Quintana', nit: '8275143', total: '94.52', iva: '12.29', cc: '0.047', fecha: '2026-09-21T14:15:00' }, // Esta semana
  { id: 3, codigo: 'FAC-PP-6A36D812CB176', cliente: 'ramirezzzz', nit: '8888888', total: '49.00', iva: '6.37', cc: '0.032', fecha: '2026-09-05T09:00:00' }, // Este mes
  { id: 4, codigo: 'FAC-PP-6A31FA14F3E4F', cliente: 'Ramires', nit: '8888888', total: '350.02', iva: '45.50', cc: '0.160', fecha: '2026-05-12T16:45:00' }  // Este año
])

// Propiedad computada que aplica AMBOS filtros al mismo tiempo
const facturasFiltradas = computed(() => {
  let resultado = facturas.value
  const hoyReal = new Date()

  // 1. Aplicar filtro de fechas primero
  if (dateFilter.value !== 'todas') {
    resultado = resultado.filter(factura => {
      const fechaFactura = new Date(factura.fecha)
      
      if (dateFilter.value === 'hoy') {
        return fechaFactura.toDateString() === hoyReal.toDateString()
      }
      
      if (dateFilter.value === 'semana') {
        const haceUnaSemana = new Date()
        haceUnaSemana.setDate(hoyReal.getDate() - 7)
        return fechaFactura >= haceUnaSemana
      }
      
      if (dateFilter.value === 'mes') {
        return fechaFactura.getMonth() === hoyReal.getMonth() && fechaFactura.getFullYear() === hoyReal.getFullYear()
      }
      
      if (dateFilter.value === 'anio') {
        return fechaFactura.getFullYear() === hoyReal.getFullYear()
      }
      return true
    })
  }

  // 2. Aplicar filtro de texto sobre los resultados de la fecha
  const query = searchQuery.value.toLowerCase().trim()
  if (query) {
    resultado = resultado.filter(factura => {
      return (
        factura.cliente.toLowerCase().includes(query) ||
        factura.nit.includes(query) ||
        factura.codigo.toLowerCase().includes(query)
      )
    })
  }

  return resultado
})

// Función auxiliar para mostrar la fecha de forma legible en la tabla
const formatearFecha = (fechaISO) => {
  const opciones = { year: 'numeric', month: 'short', day: 'numeric' }
  return new Date(fechaISO).toLocaleDateString('es-ES', opciones)
}
</script>

<style scoped>
@reference "tailwindcss";
</style>







