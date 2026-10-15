<template>
  <div class="animate-fade-in max-w-2xl mx-auto">
    <section class="bg-white p-10 rounded-[3rem] shadow-xl border border-slate-100 text-center">
      <div class="w-20 h-20 mx-auto rounded-full flex items-center justify-center text-3xl mb-6"
        :class="registrado ? 'bg-emerald-50 text-emerald-500' : 'bg-slate-100 text-slate-400'">
        <i class="fas fa-user-shield"></i>
      </div>

      <h2 class="text-2xl font-black text-slate-800 uppercase italic">Face ID</h2>
      <p class="text-xs text-slate-400 mt-2 mb-8">
        Registra tu rostro para entrar al panel sin escribir la contraseña. Se guardan solo datos matemáticos de tus rasgos, no fotos.
      </p>

      <div v-if="cargandoEstado" class="py-6"><i class="fas fa-circle-notch fa-spin text-2xl text-slate-400"></i></div>

      <template v-else>
        <p class="text-[11px] font-black uppercase tracking-widest mb-6"
          :class="registrado ? 'text-emerald-500' : 'text-slate-400'">
          {{ registrado ? 'Face ID activo en tu cuenta' : 'Face ID no registrado' }}
        </p>

        <div class="flex justify-center gap-3 flex-wrap">
          <button @click="abrirRegistro"
            class="px-6 py-3 bg-[#092615] text-white rounded-2xl font-black text-[11px] uppercase tracking-widest hover:bg-[#0d3a20] transition-all shadow-lg">
            <i class="fas fa-camera mr-2"></i>{{ registrado ? 'Volver a registrar mi rostro' : 'Registrar mi rostro' }}
          </button>
          <button v-if="registrado" @click="desactivar"
            class="px-6 py-3 bg-red-50 text-red-500 border border-red-100 rounded-2xl font-black text-[11px] uppercase tracking-widest hover:bg-red-500 hover:text-white transition-all">
            Desactivar
          </button>
        </div>
      </template>
    </section>

    <!-- MODAL DE CAPTURA -->
    <div v-if="mostrandoCamara" class="fixed inset-0 bg-slate-900/95 z-50 flex flex-col items-center justify-center p-6">
      <h2 class="text-white font-black text-xl mb-2 tracking-widest uppercase">Registro facial</h2>
      <p class="text-[#FFC600] text-sm font-bold mb-1">Captura {{ capturas.length + 1 }} de {{ TOTAL }}</p>
      <p class="text-slate-400 text-xs mb-6">{{ instrucciones[capturas.length] }}</p>

      <div class="w-64 h-64 rounded-full overflow-hidden border-4 border-[#FFC600] shadow-[0_0_50px_rgba(255,198,0,0.5)] mb-8">
        <video ref="videoRef" autoplay muted playsinline class="w-full h-full object-cover transform scale-x-[-1]"></video>
      </div>

      <div class="flex gap-4">
        <button @click="cerrarCamara" class="px-8 py-4 bg-slate-800 text-slate-300 font-black uppercase text-[10px] rounded-2xl tracking-widest hover:bg-slate-700">
          Cancelar
        </button>
        <button @click="capturar" :disabled="procesando"
          class="px-8 py-4 bg-[#FFC600] text-black font-black uppercase text-[10px] rounded-2xl tracking-widest hover:bg-white transition-colors disabled:opacity-50">
          <span v-if="!procesando"><i class="fas fa-crosshairs mr-2"></i>Capturar</span>
          <span v-else><i class="fas fa-brain fa-spin mr-2"></i>Analizando...</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, nextTick } from 'vue';
import Swal from 'sweetalert2';
import { cargarModelos, iniciarCamara, detenerCamara, obtenerDescriptor, faceIdApi } from '../../composables/faceIdService.js';

const TOTAL = 3;
const instrucciones = [
  'Mira de frente a la cámara',
  'Gira un poco la cabeza a la izquierda',
  'Gira un poco la cabeza a la derecha',
];

const registrado = ref(false);
const cargandoEstado = ref(true);
const mostrandoCamara = ref(false);
const procesando = ref(false);
const videoRef = ref(null);
const stream = ref(null);
const capturas = ref([]);

const cargarEstado = async () => {
  try {
    const res = await faceIdApi.status();
    registrado.value = res.data.registrado;
  } catch (e) {
    console.error(e);
  } finally {
    cargandoEstado.value = false;
  }
};

const abrirRegistro = async () => {
  capturas.value = [];
  Swal.fire({ title: 'Cargando reconocimiento facial...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
  try {
    await cargarModelos();
    Swal.close();
  } catch (e) {
    return Swal.fire('Error', 'No se pudieron cargar los modelos de reconocimiento (carpeta /models).', 'error');
  }

  mostrandoCamara.value = true;
  await nextTick();
  try {
    stream.value = await iniciarCamara(videoRef.value);
  } catch (e) {
    mostrandoCamara.value = false;
    Swal.fire('Error', 'No se pudo acceder a la cámara. Revisa los permisos del navegador.', 'error');
  }
};

const cerrarCamara = () => {
  detenerCamara(stream.value);
  stream.value = null;
  mostrandoCamara.value = false;
  procesando.value = false;
};

const capturar = async () => {
  procesando.value = true;
  try {
    const descriptor = await obtenerDescriptor(videoRef.value);
    if (!descriptor) {
      procesando.value = false;
      return Swal.fire('Rostro no detectado', 'Ponte frente a la cámara con buena luz e intenta otra vez.', 'warning');
    }
    capturas.value.push(descriptor);
    procesando.value = false;

    if (capturas.value.length >= TOTAL) {
      const descriptors = [...capturas.value];
      cerrarCamara();
      await guardar(descriptors);
    }
  } catch (e) {
    procesando.value = false;
    Swal.fire('Error', 'Falló el análisis del rostro.', 'error');
  }
};

const guardar = async (descriptors) => {
  const { value: password } = await Swal.fire({
    title: 'Confirma tu contraseña',
    input: 'password',
    inputPlaceholder: 'Tu contraseña actual',
    showCancelButton: true,
    confirmButtonText: 'GUARDAR',
    cancelButtonText: 'CANCELAR',
    confirmButtonColor: '#092615',
  });
  if (!password) return;

  try {
    await faceIdApi.register(descriptors, password);
    registrado.value = true;
    Swal.fire('¡Listo!', 'Face ID activado. Ya puedes entrar con tu rostro desde el login.', 'success');
  } catch (error) {
    const msg = error.response?.data?.message || 'No se pudo guardar el Face ID.';
    Swal.fire('Error', msg, 'error');
  }
};

const desactivar = async () => {
  const r = await Swal.fire({
    title: '¿Desactivar Face ID?',
    text: 'Se borrará tu rostro registrado.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'SÍ, DESACTIVAR',
    confirmButtonColor: '#dc2626',
    cancelButtonText: 'CANCELAR',
  });
  if (!r.isConfirmed) return;
  try {
    await faceIdApi.remove();
    registrado.value = false;
  } catch (e) {
    Swal.fire('Error', 'No se pudo desactivar.', 'error');
  }
};

onMounted(cargarEstado);
onBeforeUnmount(() => detenerCamara(stream.value));
</script>