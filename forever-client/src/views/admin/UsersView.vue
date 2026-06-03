<template>
  <div class="animate-fade-in">
    <section class="bg-white p-10 rounded-[3rem] shadow-xl border border-slate-100">
      
      <div class="flex justify-between items-center mb-10">
        <h2 class="text-2xl font-black text-slate-800 uppercase italic">Gestión de Usuarios y Rangos</h2>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-separate border-spacing-y-3">
          <thead>
            <tr class="text-slate-400 uppercase text-[10px] font-black tracking-widest px-4">
              <th class="p-4">Nombre Completo</th>
              <th class="p-4 text-center">Rango Actual</th>
              <th class="p-4 text-center">Estado</th>
              <th class="p-4 text-right">Acciones Administrativas</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in users" :key="user.id" class="bg-slate-50/30 hover:bg-white hover:shadow-md transition-all rounded-2xl">
              <td class="p-4 rounded-l-2xl">
                <p class="font-black text-slate-700 uppercase text-sm leading-none">
                  {{ user.persona?.nombres }} {{ user.persona?.apellidos }}
                </p>
                <p class="text-[10px] text-slate-400 font-mono italic mt-1">{{ user.email }}</p>
              </td>

              <td class="p-4 text-center">
                <span :class="user.tipo_usuario === 'fbo' ? 'bg-amber-100 text-amber-600 border-amber-200' : 'bg-blue-50 text-blue-500 border-blue-100'" 
                  class="px-4 py-1 rounded-full text-[10px] font-black uppercase border">
                  {{ user.tipo_usuario || 'CLIENTE' }}
                </span>
              </td>

              <td class="p-4 text-center">
                <span :class="user.status === 'activo' ? 'bg-emerald-50 text-emerald-500' : 'bg-red-50 text-red-400'"
                  class="px-4 py-1 rounded-full text-[10px] font-black uppercase border border-current opacity-80">
                  {{ user.status }}
                </span>
              </td>

              <td class="p-4 text-right rounded-r-2xl flex justify-end gap-2">
                <button v-if="user.tipo_usuario !== 'fbo'" 
                  @click="ascenderAFBO(user)" 
                  class="p-2 bg-amber-50 text-amber-600 rounded-xl hover:bg-amber-500 hover:text-white transition-all shadow-sm border border-amber-100"
                  title="Ascender a FBO">
                  <i class="fas fa-crown"></i>
                </button>

                <button @click="cambiarEstado(user)" 
                  class="px-4 py-2 bg-white border-2 border-slate-100 rounded-xl text-[10px] font-black uppercase text-slate-600 hover:bg-slate-800 hover:text-white transition-all">
                  Alternar
                </button>
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
import { UsuariosService } from '../../composables/productService.js';
import Swal from 'sweetalert2';

const users = ref([]);

const cargarUsuarios = async () => {
  try {
    const res = await UsuariosService.fetchAll();
    users.value = res.data.data || res.data || [];
  } catch (error) { console.error(error); }
};

// 🔥 FUNCIÓN PARA ASCENDER A FBO
const ascenderAFBO = async (user) => {
  const result = await Swal.fire({
    title: '¿Ascender a FBO?',
    html: `Confirma que <b>${user.persona?.nombres}</b> ahora es un distribuidor oficial.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'SÍ, DAR CORONA 👑',
    confirmButtonColor: '#d97706',
    cancelButtonText: 'CANCELAR'
  });

  if (result.isConfirmed) {
    try {
      await UsuariosService.promoteFBO(user.id);
      Swal.fire('¡Éxito!', 'El usuario ahora es FBO.', 'success');
      cargarUsuarios(); // Recargar lista
    } catch (error) {
      Swal.fire('Error', 'No se pudo realizar el ascenso.', 'error');
    }
  }
};

const cambiarEstado = async (user) => {
  try {
    await UsuariosService.toggleStatus(user.id);
    cargarUsuarios();
  } catch (error) {
    Swal.fire('Error', 'No se pudo cambiar el estado.', 'error');
  }
};

onMounted(cargarUsuarios);
</script>