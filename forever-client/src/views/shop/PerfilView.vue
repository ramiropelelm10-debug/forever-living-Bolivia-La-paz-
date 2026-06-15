<template>
  <!-- Contenedor Principal con la hoja flotante -->
  <div class="relative bg-[#F8F9FA] min-h-screen py-12 font-sans overflow-hidden">
    
    <img src="/images/aloe-flotante-top.png" class="absolute -top-10 -right-20 w-80 md:w-96 opacity-80 pointer-events-none z-0 object-contain drop-shadow-2xl" alt="Aloe" />

    <div class="max-w-[1100px] mx-auto px-6 relative z-10">
      
      <!-- TÍTULO PRINCIPAL: Mi Panel -->
      <div class="mb-8">
        <div class="flex items-center gap-3 mb-1">
          <div class="w-9 h-9 bg-[#EAF5F0] rounded-full flex items-center justify-center text-[#00311D] text-lg shadow-sm">
            <i class="fas fa-user"></i>
          </div>
          <h2 class="font-serif italic font-black text-4xl text-[#00311D]">Mi Panel</h2>
        </div>
        <p class="text-gray-500 font-medium text-sm ml-[3.25rem]">Gestiona tu información y revisa tu actividad.</p>
      </div>

      <!-- LOADER -->
      <div v-if="isLoading" class="flex flex-col items-center justify-center py-32 animate-pulse">
        <i class="fas fa-circle-notch fa-spin text-5xl text-[#00311D] mb-4"></i>
        <p class="text-gray-400 font-bold tracking-widest text-sm uppercase">Cargando tu información...</p>
      </div>

      <!-- CONTENIDO DEL DASHBOARD -->
      <div v-else class="space-y-6 animate-in fade-in zoom-in duration-500">
        
        <!-- HEADER DEL USUARIO (Tarjeta Verde Oscuro) -->
        <div class="bg-[#00311D] rounded-[2rem] p-6 md:p-8 text-white flex flex-col md:flex-row items-center gap-8 relative overflow-hidden shadow-[0_20px_50px_-10px_rgba(0,49,29,0.2)] border-b-[6px] border-[#FFC600]">
          <img src="/images/aguila-marca-agua.png" class="absolute left-1/3 top-1/2 transform -translate-y-1/2 h-[150%] opacity-10 pointer-events-none mix-blend-overlay object-contain" alt="Águila Watermark" />
          
          <!-- Avatar y Nombre -->
          <div class="flex items-center gap-5 relative z-10 md:w-1/3 border-b md:border-b-0 md:border-r border-white/10 pb-6 md:pb-0 pr-0 md:pr-6 w-full">
            <div class="w-[4.5rem] h-[4.5rem] bg-white rounded-full flex items-center justify-center text-[#00311D] text-3xl font-black shadow-xl border-[3px] border-[#FFC600] flex-shrink-0">
              {{ userData.name ? userData.name.charAt(0).toUpperCase() : 'U' }}
            </div>
            <div>
              <h3 class="text-2xl font-black mb-1.5 drop-shadow-md leading-none">{{ userData.name }}</h3>
              <span class="inline-flex items-center gap-1.5 bg-white/10 px-3 py-1 rounded-md text-[#FFC600] font-black text-[9px] tracking-widest uppercase border border-white/10 backdrop-blur-sm">
                <i class="fas fa-id-badge"></i> {{ userData.tipo_usuario === 'fbo' ? 'FBO' : 'CLIENTE' }}
              </span>
            </div>
          </div>

          <!-- Grid de Datos Rápidos -->
          <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-6 relative z-10 w-full">
            <div class="flex items-center gap-4">
              <div class="w-10 h-10 rounded-xl border border-white/20 flex items-center justify-center text-white/70">
                <i class="far fa-calendar-alt text-lg"></i>
              </div>
              <div class="flex flex-col">
                <span class="text-white/60 text-[10px] font-bold tracking-widest uppercase mb-0.5">Cliente desde</span>
                <span class="text-sm font-medium">{{ formatearFechaCorta(userData.created_at) }}</span>
              </div>
            </div>
            <div class="flex items-center gap-4">
              <div class="w-10 h-10 rounded-xl border border-white/20 flex items-center justify-center text-white/70">
                <i class="far fa-envelope text-lg"></i>
              </div>
              <div class="flex flex-col">
                <span class="text-white/60 text-[10px] font-bold tracking-widest uppercase mb-0.5">Correo</span>
                <span class="text-sm font-medium truncate max-w-[150px]">{{ userData.email }}</span>
              </div>
            </div>
            <div class="flex items-center gap-4">
              <div class="w-10 h-10 rounded-xl border border-white/20 flex items-center justify-center text-white/70">
                <i class="far fa-address-card text-lg"></i>
              </div>
              <div class="flex flex-col">
                <span class="text-white/60 text-[10px] font-bold tracking-widest uppercase mb-0.5">
                  {{ userData.tipo_usuario === 'fbo' ? 'Puntos Acumulados' : 'Código de cliente' }}
                </span>
                <span v-if="userData.tipo_usuario === 'fbo'" class="text-sm font-black text-[#FFC600]">{{ totalCcs.toFixed(3) }} CC</span>
                <span v-else class="text-sm font-medium">#CL-{{ userData.id?.toString().padStart(5, '0') }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          
          <!-- DATOS PERSONALES -->
          <div class="lg:col-span-1 bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100 flex flex-col h-full">
            <h4 class="font-black text-[#00311D] uppercase tracking-widest text-[11px] mb-6 flex items-center gap-2">
              <i class="fas fa-user-edit text-[#4A8B6B]"></i> DATOS PERSONALES
            </h4>
            <div class="space-y-4 mb-6 flex-grow">
              <div class="flex items-center justify-between border-b border-gray-50 pb-3">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Nombres</span>
                <input type="text" v-model="form.name" class="text-right font-bold text-[#00311D] text-sm outline-none bg-transparent w-2/3 focus:border-b focus:border-[#4A8B6B]">
              </div>
              <div class="flex items-center justify-between border-b border-gray-50 pb-3">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Apellidos</span>
                <input type="text" v-model="form.last_name" class="text-right font-bold text-[#00311D] text-sm outline-none bg-transparent w-2/3 focus:border-b focus:border-[#4A8B6B]">
              </div>
              <div class="flex items-center justify-between border-b border-gray-50 pb-3">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest w-1/3">Correo</span>
                <input type="email" v-model="form.email" readonly class="text-right font-bold text-gray-400 text-sm outline-none bg-transparent w-2/3 cursor-not-allowed">
              </div>
            </div>
            <button @click="guardarCambios" :disabled="isSaving" class="w-full bg-white border-2 border-[#00311D] text-[#00311D] py-3.5 rounded-xl font-black text-[11px] uppercase tracking-widest hover:bg-[#00311D] hover:text-white transition-all flex justify-center items-center gap-2">
              <span v-if="!isSaving"><i class="fas fa-edit mr-1"></i> Guardar Cambios</span>
              <i v-else class="fas fa-circle-notch fa-spin"></i>
            </button>
          </div>

          <!-- HISTORIAL DE COMPRAS -->
          <div class="lg:col-span-2 bg-white rounded-[2rem] p-6 md:p-8 shadow-sm border border-gray-100 flex flex-col h-full">
            <h4 class="font-black text-[#00311D] uppercase tracking-widest text-[11px] mb-6 flex items-center gap-2">
              <i class="fas fa-shopping-bag text-[#4A8B6B]"></i> HISTORIAL DE COMPRAS
            </h4>
            <div class="bg-[#F2F8F5] rounded-xl p-3.5 grid text-[9px] font-black text-[#4A8B6B] uppercase tracking-widest mb-4" :class="userData.tipo_usuario === 'fbo' ? 'grid-cols-5' : 'grid-cols-4'">
              <div class="text-left pl-3">FECHA</div><div class="text-center">NRO. ORDEN</div><div class="text-center">TOTAL</div>
              <div v-if="userData.tipo_usuario === 'fbo'" class="text-center">CC</div><div class="text-center">DOCUMENTOS</div>
            </div>
            <div v-if="compras.length === 0" class="py-12 text-center border-2 border-dashed border-gray-100 rounded-2xl flex-grow flex flex-col justify-center items-center">
              <i class="fas fa-receipt text-4xl text-gray-200 mb-3 block"></i>
              <p class="text-gray-400 font-medium text-sm">Aún no has realizado ninguna compra.</p>
            </div>
            <div v-else class="space-y-0 flex-grow">
              <div v-for="compra in compras.slice(0, 4)" :key="compra.id" class="grid items-center py-4 border-b border-gray-50 hover:bg-gray-50/50 transition-colors" :class="userData.tipo_usuario === 'fbo' ? 'grid-cols-5' : 'grid-cols-4'">
                <div class="text-left pl-3"><span class="font-medium text-gray-700 text-sm">{{ formatearFechaCorta(compra.created_at) }}</span></div>
                <div class="text-center"><span class="bg-[#EAF5F0] text-[#4A8B6B] px-3 py-1 rounded-md text-[10px] font-black tracking-widest">#{{ compra.id.toString().padStart(5, '0') }}</span></div>
                <div class="text-center"><span class="font-black text-[#00311D] text-sm">Bs. {{ parseFloat(compra.monto_total || 0).toFixed(2) }}</span></div>
                <div v-if="userData.tipo_usuario === 'fbo'" class="text-center"><span class="font-black text-[#FFC600] text-sm">{{ parseFloat(compra.total_cc || 0).toFixed(3) }} CC</span></div>
                <div class="text-center flex justify-center gap-2">
                  <button @click="descargarPDF(compra.id)" class="flex items-center gap-1 bg-[#EAF5F0] text-[#4A8B6B] px-2 md:px-3 py-1.5 rounded-md text-[8px] md:text-[9px] font-black hover:bg-[#4A8B6B] hover:text-white transition-colors" title="Descargar Factura">
                    <i class="fas fa-file-pdf"></i> <span class="hidden md:inline">FACTURA</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- BARRA INFERIOR: ESCÁNER FACIAL IA -->
        <div class="bg-white rounded-[2rem] p-6 md:p-8 shadow-sm border border-gray-100 flex flex-col md:flex-row justify-between items-center gap-6 relative overflow-hidden">
          <div class="flex items-center gap-6 w-full md:w-auto">
            <div class="w-16 h-16 bg-[#EAF5F0] rounded-full flex items-center justify-center text-[#4A8B6B] text-3xl shadow-inner flex-shrink-0">
              <i class="fas fa-camera"></i>
            </div>
            <div>
              <h4 class="font-black text-[#00311D] uppercase tracking-widest text-xs mb-1">Reconocimiento Facial</h4>
              <p class="text-xs text-gray-500 font-medium">Vincula tu rostro para iniciar sesión rápidamente sin contraseñas.</p>
            </div>
          </div>
          
          <button @click="abrirModalCamara" class="w-full md:w-auto bg-[#00311D] text-white px-8 py-4 rounded-xl font-black text-[11px] uppercase tracking-widest hover:bg-[#FFC600] hover:text-[#00311D] transition-colors shadow-lg flex items-center justify-center gap-2 flex-shrink-0">
            <i class="fas fa-expand text-sm"></i> VINCULAR MI ROSTRO
          </button>
        </div>

      </div>
    </div>

    <!-- 🔥 MODAL DE LA CÁMARA WEB / IA 🔥 -->
    <div v-if="showCameraModal" class="fixed inset-0 bg-[#00311D]/90 z-50 flex items-center justify-center p-4 backdrop-blur-md">
      <div class="bg-white rounded-[2rem] overflow-hidden shadow-2xl max-w-2xl w-full relative flex flex-col border-[4px] border-[#FFC600]">
        
        <div class="p-6 text-center border-b border-gray-100 relative">
          <h3 class="text-xl font-black text-[#00311D] uppercase tracking-widest">Escáner de Identidad</h3>
          <p class="text-sm text-gray-500 mt-1">Mira fijamente a la cámara con buena iluminación.</p>
          <button @click="cerrarModalCamara" class="absolute top-6 right-6 text-gray-400 hover:text-red-500 text-xl transition-colors">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <div class="relative w-full aspect-video bg-black flex items-center justify-center overflow-hidden">
          <!-- Si la cámara está cargando -->
          <div v-show="isCameraLoading" class="absolute inset-0 flex flex-col items-center justify-center z-10 bg-gray-900">
            <i class="fas fa-circle-notch fa-spin text-4xl text-[#FFC600] mb-3"></i>
            <span class="text-white font-bold tracking-widest text-xs uppercase">Cargando Motores de IA...</span>
          </div>

          <!-- Video y Canvas de IA -->
          <video ref="videoEl" autoplay muted class="w-full h-full object-cover"></video>
          <canvas ref="canvasEl" class="absolute top-0 left-0 w-full h-full pointer-events-none"></canvas>
          
          <!-- Escáner visual (Línea de escaneo) -->
          <div class="absolute inset-0 pointer-events-none border-[4px] border-white/20 m-8 rounded-3xl"></div>
        </div>

        <div class="p-6 bg-gray-50 flex justify-center">
          <button @click="escanearRostro" :disabled="isScanning" class="bg-[#00311D] text-white px-10 py-4 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-[#4A8B6B] transition-all flex items-center justify-center gap-2 shadow-lg disabled:opacity-50">
            <span v-if="!isScanning"><i class="fas fa-crosshairs"></i> REGISTRAR MI ROSTRO</span>
            <span v-else><i class="fas fa-circle-notch fa-spin"></i> ESCANEANDO...</span>
          </button>
        </div>
      </div>
    </div>
    
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import Swal from 'sweetalert2';
import * as faceapi from 'face-api.js';

const API_URL = 'http://localhost:8000/api';

const isLoading = ref(true);
const isSaving = ref(false);

const userData = ref({});
const compras = ref([]);
const totalCcs = ref(0);
const form = ref({ name: '', last_name: '', email: '' });

// Variables para la Cámara y la IA
const showCameraModal = ref(false);
const isCameraLoading = ref(true);
const isScanning = ref(false);
const videoEl = ref(null);
const canvasEl = ref(null);
let stream = null;
let scanInterval = null;

const formatearFechaCorta = (fechaString) => {
  if (!fechaString) return 'Hoy';
  const fecha = new Date(fechaString);
  return `${fecha.getDate().toString().padStart(2, '0')}/${(fecha.getMonth() + 1).toString().padStart(2, '0')}/${fecha.getFullYear()}`;
};

const cargarPerfilYCompras = async () => {
  try {
    const token = localStorage.getItem('auth_token');
    if (!token) return;

    const resUser = await fetch(`${API_URL}/user`, { headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${token}` }});
    if (resUser.ok) {
      const data = await resUser.json();
      userData.value = data;
      form.value.name = data.name || '';
      form.value.last_name = data.persona?.apellidos || data.last_name || ''; 
      form.value.email = data.email || '';
    }

    const resSales = await fetch(`${API_URL}/my-sales`, { headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${token}` }});
    if (resSales.ok) {
      const resp = await resSales.json();
      compras.value = Array.isArray(resp) ? resp : (resp.data || resp.ventas || []);
      if (userData.value.tipo_usuario === 'fbo') {
        totalCcs.value = compras.value.reduce((acc, c) => acc + parseFloat(c.total_cc || 0), 0);
      }
    }
  } catch (error) {
    console.error(error);
  } finally {
    isLoading.value = false;
  }
};

// 🔥 MAGIA DE LA IA Y LA CÁMARA WEB 🔥

const abrirModalCamara = async () => {
  showCameraModal.value = true;
  isCameraLoading.value = true;
  
  await nextTick(); // Espera a que el modal se dibuje en pantalla

  try {
    // 1. Cargar los modelos de la IA desde public/models (¡los mismos del Admin!)
    await Promise.all([
      faceapi.nets.ssdMobilenetv1.loadFromUri('/models'),
      faceapi.nets.faceLandmark68Net.loadFromUri('/models'),
      faceapi.nets.faceRecognitionNet.loadFromUri('/models')
    ]);

    // 2. Encender la cámara web
    stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" }, audio: false });
    videoEl.value.srcObject = stream;

    // 3. Cuando el video empiece a reproducirse, dibujamos en vivo
    videoEl.value.onplay = () => {
      isCameraLoading.value = false;
      const displaySize = { width: videoEl.value.videoWidth, height: videoEl.value.videoHeight };
      faceapi.matchDimensions(canvasEl.value, displaySize);

      // Bucle para dibujar el cuadrito verde en la cara en tiempo real
      scanInterval = setInterval(async () => {
        if(!videoEl.value || videoEl.value.paused || videoEl.value.ended) return;
        
        const detections = await faceapi.detectAllFaces(videoEl.value).withFaceLandmarks();
        const resizedDetections = faceapi.resizeResults(detections, displaySize);
        
        const ctx = canvasEl.value.getContext('2d');
        ctx.clearRect(0, 0, canvasEl.value.width, canvasEl.value.height);
        
        // Estilo Tony Stark: Cuadro Verde de Escaneo
        faceapi.draw.drawDetections(canvasEl.value, resizedDetections, { withScore: false, boxColor: '#00FF00', lineWidth: 4 });
      }, 100);
    };

  } catch (error) {
    console.error("Error con la cámara o IA:", error);
    cerrarModalCamara();
    Swal.fire('Error', 'No se pudo acceder a la cámara o cargar la Inteligencia Artificial. Da permisos al navegador.', 'error');
  }
};

const escanearRostro = async () => {
  isScanning.value = true;
  
  try {
    // Escaneamos buscando un ÚNICO rostro, sacando sus marcas (ojos, nariz) y su descriptor (128 números)
    const deteccion = await faceapi.detectSingleFace(videoEl.value).withFaceLandmarks().withFaceDescriptor();

    if (!deteccion) {
      Swal.fire('No te veo', 'Por favor, mira directamente a la cámara y asegúrate de tener buena luz.', 'warning');
      isScanning.value = false;
      return;
    }

    // Convertimos el mapa de la cara a un arreglo normal para enviarlo por internet
    const faceDescriptor = Array.from(deteccion.descriptor);

    // Lo enviamos a nuestro backend de Laravel para guardarlo en la Base de Datos
    const token = localStorage.getItem('auth_token');
    const response = await fetch(`${API_URL}/user/save-face`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify({ face_descriptor: faceDescriptor })
    });

    if (response.ok) {
      cerrarModalCamara();
      Swal.fire({
        title: '¡Rostro Vinculado!',
        text: '¡Perfecto! Ya puedes iniciar sesión con tu cámara web en la tienda.',
        icon: 'success',
        confirmButtonColor: '#00311D'
      });
    } else {
      throw new Error('Fallo al guardar en el servidor');
    }

  } catch (error) {
    console.error(error);
    Swal.fire('Error', 'Hubo un problema al guardar tu rostro.', 'error');
  } finally {
    isScanning.value = false;
  }
};

const cerrarModalCamara = () => {
  showCameraModal.value = false;
  isScanning.value = false;
  clearInterval(scanInterval);
  if (stream) {
    stream.getTracks().forEach(track => track.stop()); // Apaga la luz de la cámara
  }
};

const descargarPDF = async (ventaId) => { /* Código del PDF intacto */ };
const verRecibo = (ventaId) => { /* Código del Recibo intacto */ };
const guardarCambios = async () => { /* Código guardar perfil intacto */ };

onMounted(() => {
  cargarPerfilYCompras();
});
</script>

<style scoped>
@reference "tailwindcss";
.animate-in { animation: fadeInZoom 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
@keyframes fadeInZoom { from { opacity: 0; transform: scale(0.95) translateY(10px); } to { opacity: 1; transform: scale(1) translateY(0); } }
</style>