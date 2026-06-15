<template>
  <div class="min-h-screen flex items-center justify-center bg-[#F8F9FA] font-sans">
    <div class="bg-white p-10 lg:p-14 rounded-[3rem] shadow-2xl w-full max-w-md flex flex-col items-center border border-gray-100">
      
      <div class="flex flex-col items-center mb-8">
        <i class="fab fa-laravel text-[#FF2D20] text-5xl mb-3 drop-shadow-sm"></i>
        <h1 class="text-3xl font-black tracking-tighter text-[#1e293b]">FOREVER<span class="text-[#FFC600]">BOLIVIA</span></h1>
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-6">Acceso Administrativo</p>
      </div>

      <div v-if="!requiresOTP" class="w-full space-y-4 animate-in fade-in zoom-in duration-300">
        <input 
          type="email" 
          v-model="email" 
          placeholder="admin@forever.com" 
          class="w-full p-4 bg-[#EEF2F6] border-none rounded-2xl font-bold text-slate-700 text-sm outline-none focus:ring-2 focus:ring-slate-300 transition-all"
        >
        <input 
          type="password" 
          v-model="password" 
          @keyup.enter="loginAdmin" 
          placeholder="••••••••" 
          class="w-full p-4 bg-[#EEF2F6] border-none rounded-2xl font-bold text-slate-700 text-sm outline-none focus:ring-2 focus:ring-slate-300 transition-all"
        >

        <button 
          @click="loginAdmin" 
          :disabled="isLoading" 
          class="w-full bg-[#1E293B] text-white font-black py-4 rounded-2xl uppercase tracking-widest shadow-lg shadow-slate-300 hover:bg-black transition-all active:scale-95 disabled:opacity-50 mt-6 text-[11px] flex justify-center items-center gap-2"
        >
          <span v-if="!isLoading">Entrar al Sistema</span>
          <i v-else class="fas fa-circle-notch fa-spin"></i>
        </button>

        <button @click="abrirScanner" class="w-full text-center mt-8 text-[#00A859] font-black text-[10px] uppercase tracking-widest hover:text-[#005A36] transition-colors">
          <i class="fas fa-id-badge mr-1"></i> ¿Usar Biometría Facial?
        </button>
      </div>

      <div v-else class="w-full space-y-4 animate-in fade-in slide-in-from-right-8 duration-300">
        <div class="bg-yellow-50 p-4 rounded-2xl border border-yellow-100 text-center mb-4">
          <i class="fas fa-shield-alt text-[#FFC600] text-2xl mb-2"></i>
          <p class="text-[10px] font-black text-yellow-800 uppercase tracking-widest">Verificación de 2 pasos</p>
          <p class="text-xs text-yellow-700 mt-1 font-bold">Ingresa el código enviado a tu correo</p>
        </div>

        <input 
          type="text" 
          v-model="otpCode" 
          maxlength="6" 
          @keyup.enter="verifyOtpAdmin" 
          placeholder="000000" 
          class="w-full p-4 bg-[#EEF2F6] border-2 border-[#FFC600] rounded-2xl font-black text-center text-3xl tracking-[0.5em] text-gray-800 outline-none transition-all"
        >

        <button 
          @click="verifyOtpAdmin" 
          :disabled="isLoading" 
          class="w-full bg-[#FFC600] text-black font-black py-4 rounded-2xl uppercase tracking-widest shadow-lg shadow-[#FFC600]/30 hover:bg-black hover:text-white transition-all active:scale-95 mt-4 text-[11px] flex justify-center items-center gap-2"
        >
          <span v-if="!isLoading">Verificar Código</span>
          <i v-else class="fas fa-circle-notch fa-spin"></i>
        </button>

        <button @click="requiresOTP = false" class="w-full text-center mt-4 text-slate-400 font-black text-[10px] uppercase tracking-widest hover:text-red-500 transition-colors">
          Cancelar
        </button>
      </div>
    </div>

    <div v-if="mostrandoScanner" class="fixed inset-0 bg-slate-900/95 z-50 flex flex-col items-center justify-center p-6">
      <h2 class="text-white font-black text-xl mb-4 tracking-widest uppercase">Escaneo de Seguridad</h2>
      <p class="text-slate-400 text-xs mb-8">La IA analizará tus rasgos para confirmar tu identidad.</p>
      
      <div class="relative w-64 h-64 rounded-full overflow-hidden border-4 border-[#FFC600] shadow-[0_0_50px_rgba(255,198,0,0.5)] mb-8">
        <video ref="videoScannerRef" autoplay playsinline class="w-full h-full object-cover transform scale-x-[-1]"></video>
        <div v-if="analizando" class="absolute inset-0 bg-gradient-to-b from-transparent via-[#FFC600]/40 to-transparent w-full h-1/2 animate-[scan_2s_ease-in-out_infinite]"></div>
      </div>

      <div class="flex gap-4">
        <button @click="cancelarScanner" class="px-8 py-4 bg-slate-800 text-slate-300 font-black uppercase text-[10px] rounded-2xl tracking-widest hover:bg-slate-700 transition-colors">
          Cancelar
        </button>
        <button @click="escanearRostroIA" :disabled="analizando" class="px-8 py-4 bg-[#FFC600] text-black font-black uppercase text-[10px] rounded-2xl tracking-widest hover:bg-white transition-colors flex items-center">
          <span v-if="!analizando"><i class="fas fa-crosshairs mr-2"></i> Identificarme</span>
          <span v-else><i class="fas fa-brain fa-spin mr-2"></i> Analizando...</span>
        </button>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import Swal from 'sweetalert2';
// 🔥 IMPORTAMOS LA INTELIGENCIA ARTIFICIAL EN EL LOGIN
import * as faceapi from 'face-api.js';

const router = useRouter();
const email = ref('');
const password = ref('');
const otpCode = ref('');
const requiresOTP = ref(false); 
const isLoading = ref(false);
const API_URL = 'https://forever-api-e5zr.onrender.com/api';

const mostrandoScanner = ref(false);
const videoScannerRef = ref(null);
const scannerStream = ref(null);
const analizando = ref(false);

// ==========================================
// 🧠 MAGIA DE LA INTELIGENCIA ARTIFICIAL
// ==========================================
const abrirScanner = async () => {
  if (!email.value) {
    return Swal.fire('Atención', 'Por favor, escribe tu correo de administrador antes de usar la Biometría.', 'warning');
  }

  const descriptorGuardadoStr = localStorage.getItem('biometria_descriptor');
  const activoFaceId = localStorage.getItem('faceid_activo') === 'true';

  if (!descriptorGuardadoStr || !activoFaceId) {
    return Swal.fire('Face ID Inactivo', 'Debes registrar tus datos faciales y activar el Face ID en tu panel de Perfil Administrativo primero.', 'warning');
  }

  Swal.fire({ title: 'Iniciando Seguridad...', text: 'Cargando modelos neuronales', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
  try {
    await faceapi.nets.ssdMobilenetv1.loadFromUri('/models');
    await faceapi.nets.faceLandmark68Net.loadFromUri('/models');
    await faceapi.nets.faceRecognitionNet.loadFromUri('/models');
    Swal.close();
  } catch (e) {
    return Swal.fire('Error de IA', 'No se pudieron cargar los modelos de reconocimiento.', 'error');
  }

  mostrandoScanner.value = true;
  try {
    scannerStream.value = await navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } });
    if (videoScannerRef.value) videoScannerRef.value.srcObject = scannerStream.value;
  } catch (e) {
    mostrandoScanner.value = false;
    Swal.fire('Error', 'No se pudo acceder a la cámara.', 'error');
  }
}

const escanearRostroIA = async () => {
  analizando.value = true;
  
  try {
    const deteccionEnVivo = await faceapi.detectSingleFace(videoScannerRef.value).withFaceLandmarks().withFaceDescriptor();
    
    if (!deteccionEnVivo) {
      analizando.value = false;
      return Swal.fire('Rostro no detectado', 'Ubícate bien frente a la cámara y asegúrate de tener buena luz.', 'warning');
    }

    const arrayGuardado = JSON.parse(localStorage.getItem('biometria_descriptor'));
    const descriptorGuardado = new Float32Array(arrayGuardado); 
    
    const distancia = faceapi.euclideanDistance(deteccionEnVivo.descriptor, descriptorGuardado);
    console.log("📏 Distancia matemática entre rostros (menor a 0.50 es match):", distancia);

    if (distancia <= 0.50) {
      const res = await fetch(`${API_URL}/login-faceid`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ email: email.value }) 
      });
      
      const data = await res.json();
      
      if (res.ok) {
        cancelarScanner();
        Swal.fire({ title: 'Identidad Confirmada', text: '¡Bienvenido al sistema!', icon: 'success', timer: 1500, showConfirmButton: false });
        
        // 🔐 CORRECCIÓN: Prioridad a "role" sobre "tipo_usuario"
        const userType = data.user?.role || data.user?.tipo_usuario || 'admin';
        
        if (userType !== 'admin' && userType !== 'inventario') {
            return Swal.fire('Denegado', 'Solo admins o personal de inventario.', 'error');
        }

        localStorage.setItem('auth_token', data.token);
        localStorage.setItem('userType', userType);
        
        router.push('/admin/catalogo');
      } else {
        analizando.value = false;
        Swal.fire('Error del Servidor', data.message || 'El Face ID funcionó, pero Laravel rechazó el acceso.', 'error');
      }

    } else {
      analizando.value = false;
      Swal.fire({ title: '¡ACCESO DENEGADO!', text: 'El rostro no coincide con el registrado. Intento bloqueado.', icon: 'error' });
    }

  } catch (error) {
    analizando.value = false;
    Swal.fire('Error', 'Hubo un fallo al procesar la biometría.', 'error');
  }
}

const cancelarScanner = () => {
  mostrandoScanner.value = false;
  analizando.value = false;
  if (scannerStream.value) scannerStream.value.getTracks().forEach(t => t.stop());
}

// ==========================================
// LOGIN TRADICIONAL Y OTP
// ==========================================
const loginAdmin = async () => {
  if (!email.value || !password.value) return Swal.fire('Atención', 'Completa tus credenciales.', 'warning');
  isLoading.value = true;
  try {
    const res = await fetch(`${API_URL}/login`, {
      method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ email: email.value, password: password.value })
    });
    const data = await res.json();
    if (res.ok) {
      if (data.requires_otp) {
        requiresOTP.value = true;
        return; 
      }
      completarLogin(data);
    } else {
      Swal.fire('Error', data.message || 'Credenciales incorrectas.', 'error');
    }
  } catch (error) { 
    Swal.fire('Error', 'Servidor desconectado.', 'error'); 
  } finally { isLoading.value = false; }
};

const verifyOtpAdmin = async () => {
  if (!otpCode.value) return Swal.fire('Atención', 'Ingresa el código.', 'warning');
  isLoading.value = true;
  try {
    const res = await fetch(`${API_URL}/verify-otp`, {
      method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ email: email.value, code: otpCode.value })
    });
    const data = await res.json();
    if (res.ok && (data.token || data.access_token)) {
      completarLogin(data);
    } else {
      Swal.fire('Error', 'El código de seguridad no es válido.', 'error');
    }
  } catch (error) {
    Swal.fire('Error', 'Fallo al verificar el código.', 'error');
  } finally { isLoading.value = false; }
};

const completarLogin = (data) => {
  // 🔐 CORRECCIÓN: Prioridad a "role" ("inventario") en vez de "tipo_usuario" ("empleado")
  const userType = data.user?.role || data.user?.tipo_usuario || data.tipo_usuario || 'admin';
  const token = data.token || data.access_token || data.data?.token;

  if (userType !== 'admin' && userType !== 'inventario') return Swal.fire('Denegado', 'Solo admins o personal de inventario.', 'error');

  localStorage.setItem('auth_token', token);
  localStorage.setItem('userType', userType);
  Swal.fire({ title: '¡Acceso Autorizado!', icon: 'success', timer: 1000, showConfirmButton: false });
  router.push('/admin/catalogo');
};
</script>

<style scoped>
@reference "tailwindcss";
@keyframes scan {
  0% { transform: translateY(-100%); opacity: 0; }
  50% { opacity: 1; }
  100% { transform: translateY(200%); opacity: 0; }
}
</style>