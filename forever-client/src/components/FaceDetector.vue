<template>
  <div class="relative w-full max-w-lg mx-auto">
    <h2 class="text-xl font-bold text-gray-800 mb-4">Detector de Operador en Caja</h2>
    
    <video 
      ref="videoRef" 
      autoplay 
      muted 
      playsinline 
      class="w-full rounded-lg shadow-lg border-2 border-yellow-400"
    ></video>
    
    <canvas 
      ref="canvasRef" 
      class="absolute top-0 left-0 w-full h-full"
    ></canvas>

    <p v-if="cargando" class="mt-2 text-blue-600">Cargando modelos de IA...</p>
    <p v-if="rostroDetectado" class="mt-2 text-green-600 font-bold">¡Operador detectado! Caja Desbloqueada.</p>
    <p v-else-if="!cargando" class="mt-2 text-red-600 font-bold">Caja Bloqueada: No hay nadie en la silla.</p>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import * as faceapi from 'face-api.js';

const videoRef = ref(null);
const canvasRef = ref(null);
const cargando = ref(true);
const rostroDetectado = ref(false);
let intervaloDeteccion;

// 1. Cargar los modelos desde la carpeta public/models
const cargarModelos = async () => {
  const rutaModelos = '/models'; // Ruta en Laravel public
  await Promise.all([
    faceapi.nets.tinyFaceDetector.loadFromUri(rutaModelos),
    // Si quieres detectar emociones, descomenta la siguiente línea:
    // faceapi.nets.faceExpressionNet.loadFromUri(rutaModelos)
  ]);
  cargando.value = false;
  iniciarCamara();
};

// 2. Encender la Webcam
const iniciarCamara = () => {
  navigator.mediaDevices.getUserMedia({ video: true })
    .then((stream) => {
      videoRef.value.srcObject = stream;
    })
    .catch((err) => console.error("Error al acceder a la cámara:", err));
};

// 3. Detectar rostros cada 100 milisegundos
const detectarRostros = async () => {
  if (!videoRef.value || !canvasRef.value) return;

  // Usamos TinyFaceDetector porque es más rápido para navegadores
  const detecciones = await faceapi.detectAllFaces(
    videoRef.value, 
    new faceapi.TinyFaceDetectorOptions()
  );

  // Si detecta al menos 1 cara (length > 0)
  rostroDetectado.value = detecciones.length > 0;

  // Dibujar el cuadrito en el canvas (opcional, para que se vea cool)
  const displaySize = { width: videoRef.value.videoWidth, height: videoRef.value.videoHeight };
  faceapi.matchDimensions(canvasRef.value, displaySize);
  const deteccionesRedimensionadas = faceapi.resizeResults(detecciones, displaySize);
  
  canvasRef.value.getContext('2d').clearRect(0, 0, canvasRef.value.width, canvasRef.value.height);
  faceapi.draw.drawDetections(canvasRef.value, deteccionesRedimensionadas);
};

// Ciclo de vida del componente
onMounted(() => {
  cargarModelos();
  // Cuando el video empieza a reproducirse, iniciamos el bucle de detección
  videoRef.value.addEventListener('play', () => {
    intervaloDeteccion = setInterval(detectarRostros, 100);
  });
});

onBeforeUnmount(() => {
  clearInterval(intervaloDeteccion); // Limpiar memoria al salir
  if (videoRef.value && videoRef.value.srcObject) {
    videoRef.value.srcObject.getTracks().forEach(track => track.stop()); // Apagar cámara
  }
});
</script>