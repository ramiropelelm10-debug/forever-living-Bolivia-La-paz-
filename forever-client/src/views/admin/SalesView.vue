<template>
  <div class="bg-white p-8 rounded-[3rem] shadow-xl border border-slate-100 overflow-x-auto animate-fade-in">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h2 class="text-2xl font-black text-slate-800 uppercase italic leading-none">Historial de Ventas Bolivia</h2>
        <p class="text-[10px] text-slate-400 font-bold uppercase mt-1">Reportes y Control Fiscal</p>
      </div>

      <div class="flex items-center gap-3">
        <button @click="exportarVentas" class="bg-emerald-600 text-white px-5 py-2.5 rounded-2xl hover:bg-emerald-700 transition shadow-lg shadow-emerald-100 flex items-center gap-2 text-xs font-black uppercase tracking-widest">
          <i class="fas fa-file-excel"></i> Exportar Excel
        </button>

        <button @click="cargarVentas" class="p-2.5 bg-slate-100 text-slate-500 rounded-2xl hover:bg-yellow-400 hover:text-white transition">
          <i class="fas fa-sync-alt"></i>
        </button>
      </div>
    </div>

    <table class="w-full text-left">
      <thead>
        <tr class="bg-slate-50 text-slate-400 uppercase text-[10px] font-black tracking-widest border-b border-slate-100">
          <th class="p-4">Factura</th>
          <th class="p-4">Cliente (NIT/CI)</th>
          <th class="p-4 text-center">Productos</th>
          <th class="p-4">Total (Bs)</th>
          <th class="p-4 text-red-400">IVA (13%)</th>
          <th class="p-4">CC</th>
          <th class="p-4 text-center">Acción</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-50">
        <tr v-for="sale in sales" :key="sale.id" class="hover:bg-slate-50/50 transition">
          <td class="p-4 font-mono text-xs font-bold text-slate-500">{{ sale.nro_factura }}</td>
          <td class="p-4 font-bold text-slate-700 text-sm">
            <span>{{ sale.razon_social || 'Consumidor Final' }}</span><br>
            <span class="text-[10px] text-slate-400 font-mono italic">NIT: {{ sale.nit_ci || 'S/N' }}</span>
          </td>
          <td class="p-4 text-center font-bold text-xs">{{ sale.items ? sale.items.length : 0 }} Items</td>
          <td class="p-4 font-black text-emerald-600">Bs. {{ sale.monto_total }}</td>
          <td class="p-4 text-red-500 font-bold text-xs">Bs. {{ sale.monto_iva }}</td>
          <td class="p-4 font-black text-blue-600">{{ sale.total_cc }}</td>
          <td class="p-4 text-center">
            <button @click="descargarFactura(sale)" class="bg-red-50 text-red-600 px-3 py-2 rounded-xl border border-red-100 hover:bg-red-500 hover:text-white transition shadow-sm text-xs font-black uppercase tracking-widest flex items-center justify-center gap-2 w-full">
              <i class="fas fa-file-pdf"></i> PDF
            </button>
          </td>
        </tr>
        <tr v-if="!sales || sales.length === 0">
          <td colspan="7" class="py-20 text-center text-slate-300 uppercase font-black tracking-widest text-xs">
            No hay registros de ventas hoy
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { VentasService } from '../../composables/productService.js';
import Swal from 'sweetalert2';

const sales = ref([]);

const cargarVentas = async () => {
  try {
    const res = await VentasService.fetch();
    sales.value = res.data?.data || res.data || [];
  } catch (error) {
    console.error("Error al cargar ventas:", error);
    Swal.fire('Error', 'No se pudieron cargar las ventas del servidor', 'error');
  }
};

// 🔥 NUEVA FUNCIÓN: EXPORTAR A EXCEL
const exportarVentas = async () => {
  Swal.fire({
    title: 'Generando Excel',
    text: 'Preparando reporte contable...',
    allowOutsideClick: false,
    didOpen: () => { Swal.showLoading(); }
  });

  try {
    const response = await VentasService.exportarExcel();
    // Convertimos la respuesta binaria en un archivo descargable
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `Reporte_Ventas_Forever_${new Date().toLocaleDateString()}.xlsx`);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
    
    Swal.fire({ title: '¡Listo!', text: 'Reporte descargado con éxito', icon: 'success', timer: 2000, showConfirmButton: false });
  } catch (error) {
    console.error("Error al exportar Excel:", error);
    Swal.fire('Error', 'No se pudo generar el archivo Excel.', 'error');
  }
};

const descargarFactura = async (sale) => {
  Swal.fire({
    title: 'Generando PDF',
    text: `Preparando factura ${sale.nro_factura}...`,
    allowOutsideClick: false,
    didOpen: () => { Swal.showLoading(); }
  });

  try {
    const response = await VentasService.descargarPdf(sale.id);
    const url = window.URL.createObjectURL(new Blob([response.data], { type: 'application/pdf' }));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `Factura_${sale.nro_factura}.pdf`);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
    Swal.close();
  } catch (error) {
    console.error("Error al descargar el PDF:", error);
    Swal.fire('Error', 'No se pudo generar el PDF. Verifica tu servidor.', 'error');
  }
};

onMounted(() => {
  cargarVentas();
});
</script>

<style scoped>
@reference "tailwindcss";
.animate-fade-in { animation: fadeIn 0.4s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: scale(0.98); } to { opacity: 1; transform: scale(1); } }
</style>