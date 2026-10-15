<template>
  <div class="animate-fade-in space-y-8">

    <!-- RESUMEN -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div class="bg-white p-6 rounded-[2rem] shadow-lg border border-slate-100">
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Último respaldo</p>
        <p class="text-lg font-black text-slate-800 mt-2">{{ ultimo ? formatearFecha(ultimo) : 'Ninguno aún' }}</p>
      </div>
      <div class="bg-white p-6 rounded-[2rem] shadow-lg border border-slate-100">
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Próximo automático</p>
        <p class="text-lg font-black text-[#b48a2d] mt-2">{{ proximo ? formatearFecha(proximo) : '—' }}</p>
        <p class="text-[10px] text-slate-400 mt-1">Cada semana</p>
      </div>
      <div class="bg-white p-6 rounded-[2rem] shadow-lg border border-slate-100">
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Respaldos guardados</p>
        <p class="text-lg font-black text-slate-800 mt-2">{{ total }}</p>
      </div>
      <div class="bg-white p-6 rounded-[2rem] shadow-lg border border-slate-100">
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Espacio usado</p>
        <p class="text-lg font-black text-slate-800 mt-2">{{ formatearTamano(espacio) }}</p>
      </div>
    </div>

    <!-- LISTADO -->
    <section class="bg-white p-10 rounded-[3rem] shadow-xl border border-slate-100">
      <div class="flex justify-between items-center mb-10 flex-wrap gap-4">
        <div>
          <h2 class="text-2xl font-black text-slate-800 uppercase italic">Respaldos del Sistema</h2>
          <p class="text-xs text-slate-400 mt-1">Base de datos + imágenes de productos. Se crea uno automático cada semana y puedes crear otro manualmente cuando quieras.</p>
        </div>
        <button @click="crearRespaldo" :disabled="creando"
          class="px-6 py-3 bg-[#092615] text-white rounded-2xl font-black text-[11px] uppercase tracking-widest hover:bg-[#0d3a20] transition-all shadow-lg disabled:opacity-50 flex items-center gap-2">
          <i :class="creando ? 'fas fa-spinner fa-spin' : 'fas fa-database'"></i>
          {{ creando ? 'Creando...' : 'Crear respaldo ahora' }}
        </button>
      </div>

      <p v-if="sinPermiso" class="text-center text-red-500 font-bold py-10">
        No tienes permiso para ver los respaldos. Solo el administrador puede.
      </p>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-separate border-spacing-y-3">
          <thead>
            <tr class="text-slate-400 uppercase text-[10px] font-black tracking-widest">
              <th class="p-4">Fecha</th>
              <th class="p-4 text-center">Tipo</th>
              <th class="p-4">Creado por</th>
              <th class="p-4 text-center">Tamaño</th>
              <th class="p-4 text-center">Estado</th>
              <th class="p-4 text-right">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="!cargando && backups.length === 0">
              <td colspan="6" class="text-center text-slate-400 py-10 text-sm">Todavía no hay respaldos.</td>
            </tr>
            <tr v-for="b in backups" :key="b.id" class="bg-slate-50/30 hover:bg-white hover:shadow-md transition-all rounded-2xl">
              <td class="p-4 rounded-l-2xl">
                <p class="font-black text-slate-700 text-sm leading-none">{{ formatearFecha(b.created_at) }}</p>
                <p class="text-[10px] text-slate-400 font-mono italic mt-1">{{ b.filename }}</p>
              </td>
              <td class="p-4 text-center">
                <span :class="b.tipo === 'automatico' ? 'bg-blue-50 text-blue-500 border-blue-100' : 'bg-amber-100 text-amber-600 border-amber-200'"
                  class="px-4 py-1 rounded-full text-[10px] font-black uppercase border">
                  {{ b.tipo }}
                </span>
              </td>
              <td class="p-4 text-xs text-slate-600 font-bold">{{ b.creado_por }}</td>
              <td class="p-4 text-center text-xs text-slate-600 font-bold">{{ b.status === 'completado' ? formatearTamano(b.size_bytes) : '—' }}</td>
              <td class="p-4 text-center">
                <span :class="{
                  'bg-emerald-50 text-emerald-500': b.status === 'completado',
                  'bg-red-50 text-red-400': b.status === 'error',
                  'bg-slate-100 text-slate-400': b.status === 'en_proceso'
                }" class="px-4 py-1 rounded-full text-[10px] font-black uppercase border border-current opacity-80" :title="b.error || ''">
                  {{ b.status.replace('_', ' ') }}
                </span>
              </td>
              <td class="p-4 text-right rounded-r-2xl">
                <div class="flex justify-end gap-2">
                  <button v-if="b.disponible" @click="descargar(b)"
                    class="p-2 bg-emerald-50 text-emerald-600 rounded-xl hover:bg-emerald-500 hover:text-white transition-all shadow-sm border border-emerald-100"
                    title="Descargar">
                    <i class="fas fa-download"></i>
                  </button>
                  <button @click="eliminar(b)"
                    class="p-2 bg-red-50 text-red-500 rounded-xl hover:bg-red-500 hover:text-white transition-all shadow-sm border border-red-100"
                    title="Eliminar">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import Swal from 'sweetalert2';
import api from '../../api.js';

const backups = ref([]);
const ultimo = ref(null);
const proximo = ref(null);
const total = ref(0);
const espacio = ref(0);
const cargando = ref(true);
const creando = ref(false);
const sinPermiso = ref(false);

const formatearFecha = (iso) =>
  new Date(iso).toLocaleString('es-BO', {
    timeZone: 'America/La_Paz',
    day: '2-digit', month: '2-digit', year: 'numeric',
    hour: '2-digit', minute: '2-digit'
  });

const formatearTamano = (bytes) => {
  if (!bytes) return '0 MB';
  const mb = bytes / 1024 / 1024;
  return mb >= 1024 ? (mb / 1024).toFixed(2) + ' GB' : mb.toFixed(2) + ' MB';
};

const cargar = async () => {
  cargando.value = true;
  try {
    const res = await api.get('/admin/backups');
    backups.value = res.data.backups;
    ultimo.value = res.data.ultimo;
    proximo.value = res.data.proximo_automatico;
    total.value = res.data.total;
    espacio.value = res.data.espacio_bytes;
    sinPermiso.value = false;
  } catch (error) {
    if (error.response?.status === 403) sinPermiso.value = true;
    else console.error(error);
  } finally {
    cargando.value = false;
  }
};

const crearRespaldo = async () => {
  creando.value = true;
  Swal.fire({
    title: 'Creando respaldo...',
    text: 'Esto puede tardar unos segundos',
    allowOutsideClick: false,
    didOpen: () => { Swal.showLoading(); }
  });
  try {
    await api.post('/admin/backups');
    Swal.fire('¡Listo!', 'El respaldo se creó correctamente.', 'success');
  } catch (error) {
    Swal.fire('Error', error.response?.data?.error || error.response?.data?.message || 'No se pudo crear el respaldo.', 'error');
  } finally {
    creando.value = false;
    cargar();
  }
};

const descargar = async (b) => {
  try {
    const res = await api.get(`/admin/backups/${b.id}/download`, { responseType: 'blob' });
    const url = window.URL.createObjectURL(new Blob([res.data]));
    const a = document.createElement('a');
    a.href = url;
    a.download = b.filename;
    document.body.appendChild(a);
    a.click();
    a.remove();
    window.URL.revokeObjectURL(url);
  } catch (error) {
    Swal.fire('Error', 'No se pudo descargar el respaldo.', 'error');
  }
};

const eliminar = async (b) => {
  const result = await Swal.fire({
    title: '¿Eliminar respaldo?',
    text: b.filename,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'SÍ, ELIMINAR',
    confirmButtonColor: '#dc2626',
    cancelButtonText: 'CANCELAR'
  });
  if (!result.isConfirmed) return;

  try {
    await api.delete(`/admin/backups/${b.id}`);
    cargar();
  } catch (error) {
    Swal.fire('Error', 'No se pudo eliminar el respaldo.', 'error');
  }
};

onMounted(cargar);
</script>