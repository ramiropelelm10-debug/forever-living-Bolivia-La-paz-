<template>
  <div class="min-h-screen flex items-center justify-center bg-[#F8F9FA] font-sans p-4 md:p-8">
    <div class="bg-white rounded-[2.5rem] shadow-[0_20px_60px_rgba(0,0,0,0.05)] w-full max-w-4xl flex flex-col md:flex-row overflow-hidden border border-slate-100 min-h-[550px]">
      
      <div class="w-full md:w-[42%] bg-[#005A36] p-8 lg:p-12 flex flex-col justify-between text-white relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-[#004026] via-[#005A36] to-[#0d6efd]/10 opacity-90"></div>
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/5 rounded-full blur-2xl"></div>
        <div class="absolute -left-10 -bottom-10 w-52 h-52 bg-[#FFC600]/5 rounded-full blur-3xl"></div>

        <div class="relative z-10">
          <router-link to="/" class="font-serif italic font-black text-3xl tracking-tighter block mb-10 hover:text-[#FFC600] transition-colors">
            FOREVER®
          </router-link>
          <h2 class="text-3xl font-black mb-4 leading-tight tracking-tight">
            {{ isLogin ? 'Bienvenido de nuevo.' : 'Únete a la familia.' }}
          </h2>
          <p class="text-xs font-semibold text-green-100/80 leading-relaxed max-w-xs">
            {{ isLogin ? 'Accede a tu cuenta para gestionar tus compras y descubrir nuevos productos naturales.' : 'Regístrate hoy para empezar a disfrutar de los beneficios de los productos Forever.' }}
          </p>
        </div>
        
        <div class="relative z-10 mt-12 md:mt-0">
          <div class="flex items-center gap-3 bg-white/10 p-4 rounded-2xl backdrop-blur-md border border-white/10 shadow-inner">
            <div class="w-8 h-8 bg-[#FFC600] rounded-xl flex items-center justify-center shadow-sm">
              <i class="fas fa-leaf text-[#005A36] text-sm"></i>
            </div>
            <div>
              <p class="text-[9px] font-black uppercase tracking-widest text-green-200">Promesa Forever</p>
              <p class="text-xs font-bold tracking-tight text-white">100% Ingredientes Naturales</p>
            </div>
          </div>
        </div>
      </div>

      <div class="w-full md:w-[58%] p-8 lg:p-10 flex flex-col justify-center bg-white">
        
        <div class="flex gap-8 border-b border-slate-100 mb-8 self-start w-full">
          <button @click="isLogin = true; requiresOTP = false" 
            :class="isLogin ? 'border-[#005A36] text-[#005A36]' : 'border-transparent text-slate-400 hover:text-slate-600'" 
            class="pb-3 font-black text-[11px] uppercase tracking-widest border-b-2 transition-all duration-200">
            Iniciar Sesión
          </button>
          <button @click="isLogin = false; requiresOTP = false" 
            :class="!isLogin ? 'border-[#005A36] text-[#005A36]' : 'border-transparent text-slate-400 hover:text-slate-600'" 
            class="pb-3 font-black text-[11px] uppercase tracking-widest border-b-2 transition-all duration-200">
            Crear Cuenta
          </button>
        </div>

        <div v-if="isLogin" class="w-full space-y-6 animate-in fade-in zoom-in-95 duration-200">
          
          <div v-if="!requiresOTP" class="space-y-5">
            <h3 class="text-2xl font-black text-slate-800 tracking-tight mb-2">Acceso a tu Cuenta</h3>
            
            <div class="space-y-4">
              <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Correo Electrónico</label>
                <div class="relative">
                  <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-300"><i class="fas fa-envelope"></i></span>
                  <input type="email" v-model="formLogin.email" autocomplete="off" placeholder="tu@correo.com" class="w-full pl-11 pr-4 py-3.5 bg-white border border-slate-200 rounded-xl font-bold text-slate-700 text-sm outline-none focus:ring-2 focus:ring-[#005A36]/10 focus:border-[#005A36] transition-all shadow-sm autofill-fix">
                </div>
              </div>
              
              <div>
                <div class="flex justify-between items-center mb-1.5 ml-1">
                  <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Contraseña</label>
                  <a href="#" class="text-[9px] font-black uppercase tracking-wider text-[#005A36] hover:text-[#b48a2d] transition-colors">¿Olvidaste tu contraseña?</a>
                </div>
                <div class="relative">
                  <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-300"><i class="fas fa-lock"></i></span>
                  <input type="password" v-model="formLogin.password" autocomplete="new-password" @keyup.enter="handleLogin" placeholder="••••••••" class="w-full pl-11 pr-4 py-3.5 bg-white border border-slate-200 rounded-xl font-bold text-slate-700 text-sm outline-none focus:ring-2 focus:ring-[#005A36]/10 focus:border-[#005A36] transition-all shadow-sm autofill-fix">
                </div>
              </div>
            </div>

            <button @click="handleLogin" :disabled="isLoading" class="w-full bg-[#005A36] text-white font-black py-4 rounded-xl uppercase tracking-widest shadow-lg shadow-[#005A36]/20 hover:bg-[#004026] transition-all active:scale-95 disabled:opacity-50 mt-6 text-[11px] flex justify-center items-center gap-2">
              <span v-if="!isLoading">Ingresar al Sistema</span>
              <i v-else class="fas fa-circle-notch fa-spin"></i>
            </button>
          </div>

          <div v-else class="space-y-5 animate-in slide-in-from-right-8 duration-200">
            <div class="bg-emerald-50 p-5 rounded-2xl border border-emerald-100 text-center mb-4">
              <i class="fas fa-shield-alt text-[#005A36] text-3xl mb-3"></i>
              <p class="text-[11px] font-black text-emerald-800 uppercase tracking-widest">Verificación de 2 pasos</p>
              <p class="text-xs text-emerald-700 mt-1 font-semibold">Ingresa el código enviado a tu correo</p>
            </div>

            <input type="text" v-model="otpCode" autocomplete="off" maxlength="6" @keyup.enter="verifyOtp" placeholder="000000" class="w-full p-4 bg-white border-2 border-[#005A36] rounded-xl font-black text-center text-3xl tracking-[0.5em] text-slate-800 outline-none shadow-sm transition-all autofill-fix">

            <button @click="verifyOtp" :disabled="isLoading" class="w-full bg-[#FFC600] text-black font-black py-4 rounded-xl uppercase tracking-widest shadow-lg shadow-[#FFC600]/20 hover:bg-black hover:text-white transition-all active:scale-95 text-[11px] flex justify-center items-center gap-2 mt-2">
              <span v-if="!isLoading">Confirmar Código</span>
              <i v-else class="fas fa-circle-notch fa-spin"></i>
            </button>
            <button @click="requiresOTP = false" class="w-full text-center text-slate-400 font-black text-[10px] uppercase tracking-widest hover:text-red-500 transition-colors mt-2">Cancelar</button>
          </div>

        </div>

        <div v-else class="w-full space-y-5 animate-in fade-in zoom-in-95 duration-200">
          
          <div class="flex gap-4 p-1.5 bg-slate-50 border border-slate-100 rounded-xl mb-4">
            <button @click="isFboRequest = false" :class="!isFboRequest ? 'bg-white text-[#005A36] shadow-sm border border-slate-200/50 font-black' : 'text-slate-400 hover:text-slate-600 font-bold'" class="flex-1 py-2.5 rounded-lg text-[10px] uppercase tracking-widest transition-all">
              <i class="fas fa-shopping-bag mr-1.5"></i> Soy Cliente
            </button>
            <button @click="isFboRequest = true" :class="isFboRequest ? 'bg-[#FFC600] text-black shadow-sm font-black' : 'text-slate-400 hover:text-slate-600 font-bold'" class="flex-1 py-2.5 rounded-lg text-[10px] uppercase tracking-widest transition-all">
              <i class="fas fa-briefcase mr-1.5"></i> Seré FBO
            </button>
          </div>

          <form autocomplete="off" class="space-y-4" @submit.prevent>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Nombres</label>
                <div class="relative">
                  <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-300"><i class="fas fa-user"></i></span>
                  <input type="text" v-model="formRegister.name" autocomplete="off" class="w-full pl-10 pr-3 py-3 bg-white border border-slate-200 rounded-xl font-bold text-slate-700 text-sm outline-none focus:ring-2 focus:ring-[#005A36]/10 focus:border-[#005A36] transition-all shadow-sm autofill-fix">
                </div>
              </div>
              <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Apellidos</label>
                <div class="relative">
                  <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-300"><i class="fas fa-user"></i></span>
                  <input type="text" v-model="formRegister.last_name" autocomplete="off" class="w-full pl-10 pr-3 py-3 bg-white border border-slate-200 rounded-xl font-bold text-slate-700 text-sm outline-none focus:ring-2 focus:ring-[#005A36]/10 focus:border-[#005A36] transition-all shadow-sm autofill-fix">
                </div>
              </div>
            </div>

            <div>
              <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Correo Electrónico</label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-300"><i class="fas fa-envelope"></i></span>
                <input type="email" v-model="formRegister.email" autocomplete="off" class="w-full pl-10 pr-3 py-3 bg-white border border-slate-200 rounded-xl font-bold text-slate-700 text-sm outline-none focus:ring-2 focus:ring-[#005A36]/10 focus:border-[#005A36] transition-all shadow-sm autofill-fix">
              </div>
            </div>

            <div>
              <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Contraseña</label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-300"><i class="fas fa-lock"></i></span>
                <input type="password" v-model="formRegister.password" autocomplete="new-password" class="w-full pl-10 pr-3 py-3 bg-white border border-slate-200 rounded-xl font-bold text-slate-700 text-sm outline-none focus:ring-2 focus:ring-[#005A36]/10 focus:border-[#005A36] transition-all shadow-sm autofill-fix">
              </div>
            </div>

            <div v-if="isFboRequest" class="bg-amber-50 p-3.5 rounded-xl border border-amber-100/50 text-[11px] text-amber-800 font-semibold flex gap-3 items-center animate-in fade-in duration-200 mt-2">
              <i class="fas fa-info-circle text-amber-500 text-lg"></i>
              <span class="leading-tight">Como FBO, tu solicitud pasará por la revisión del Administrador para su aprobación oficial.</span>
            </div>

            <button type="button" @click="handleRegister" :disabled="isLoading" class="w-full bg-[#005A36] text-white font-black py-4 rounded-xl uppercase tracking-widest shadow-lg shadow-[#005A36]/20 hover:bg-[#004026] transition-all active:scale-95 disabled:opacity-50 mt-4 text-[11px] flex justify-center items-center gap-2">
              <span v-if="!isLoading">Registrar mi Cuenta</span>
              <i v-else class="fas fa-circle-notch fa-spin"></i>
            </button>
          </form>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import Swal from 'sweetalert2';

const router = useRouter();
const API_URL = 'https://forever-api-e5zr.onrender.com/api';

const isLogin = ref(true);
const isFboRequest = ref(false);
const requiresOTP = ref(false);
const isLoading = ref(false);
const otpCode = ref('');

const formLogin = ref({ email: '', password: '' });
const formRegister = ref({ name: '', last_name: '', email: '', password: '' });

const handleLogin = async () => {
  if (!formLogin.value.email || !formLogin.value.password) {
    return Swal.fire('Atención', 'Por favor completa todas las credenciales.', 'warning');
  }
  
  isLoading.value = true;
  try {
    const res = await fetch(`${API_URL}/login`, {
      method: 'POST', 
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ email: formLogin.value.email, password: formLogin.value.password })
    });
    const data = await res.json();
    
    if (res.ok) {
      if (data.requires_otp) {
        requiresOTP.value = true;
      } else {
        completarLoginExitoso(data);
      }
    } else {
      Swal.fire('Error', data.message || 'Credenciales incorrectas.', 'error');
    }
  } catch (error) { 
    Swal.fire('Error', 'Fallo de comunicación con el servidor.', 'error'); 
  } finally { 
    isLoading.value = false; 
  }
};

const verifyOtp = async () => {
  if (!otpCode.value) return Swal.fire('Atención', 'Ingresa el código.', 'warning');
  
  isLoading.value = true;
  try {
    const res = await fetch(`${API_URL}/verify-otp`, {
      method: 'POST', 
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ email: formLogin.value.email, code: otpCode.value })
    });
    const data = await res.json();
    
    if (res.ok && (data.token || data.access_token)) {
      completarLoginExitoso(data);
    } else {
      Swal.fire('Error', 'El código ingresado es incorrecto o ha caducado.', 'error');
    }
  } catch (error) {
    Swal.fire('Error', 'Error en el proceso de autenticación.', 'error');
  } finally { 
    isLoading.value = false; 
  }
};

const completarLoginExitoso = (data) => {
  const token = data.token || data.access_token || data.data?.token;
  const userType = data.user?.tipo_usuario || data.user?.role || data.tipo_usuario || 'cliente';

  localStorage.setItem('auth_token', token);
  localStorage.setItem('userType', userType);
  
  Swal.fire({ title: '¡Acceso Concedido!', icon: 'success', timer: 1000, showConfirmButton: false });
  
  if (userType === 'admin' || userType === 'inventario') {
    window.location.href = '/admin/catalogo';
  } else {
    // 🔥 AQUÍ SE APLICÓ EL CAMBIO: Ahora redirige a la raíz de la tienda
    window.location.href = '/'; 
  }
};

const handleRegister = async () => {
  if (!formRegister.value.name || !formRegister.value.last_name || !formRegister.value.email || !formRegister.value.password) {
    return Swal.fire('Atención', 'Por favor rellena todos los campos obligatorios.', 'warning');
  }

  isLoading.value = true;
  try {
    const payload = {
      name: formRegister.value.name,
      last_name: formRegister.value.last_name,
      email: formRegister.value.email,
      password: formRegister.value.password,
      isFboRequest: isFboRequest.value
    };

    const res = await fetch(`${API_URL}/register-request`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify(payload)
    });
    
    const data = await res.json();
    
    if (res.ok) {
      Swal.fire({
        title: '¡Registro Exitoso!',
        text: isFboRequest.value 
          ? 'Tu solicitud de FBO ha sido registrada. Un administrador la evaluará en la brevedad posible.' 
          : 'Tu cuenta de cliente ha sido enviada correctamente y está a la espera de activación.',
        icon: 'success'
      });
      formRegister.value = { name: '', last_name: '', email: '', password: '' };
      isLogin.value = true;
    } else {
      Swal.fire('Error', data.message || 'Error al procesar la solicitud de registro.', 'error');
    }
  } catch (error) {
    Swal.fire('Error', 'Servidor no disponible en este momento.', 'error');
  } finally {
    isLoading.value = false;
  }
};
</script>

<style scoped>
@reference "tailwindcss";

.autofill-fix:-webkit-autofill,
.autofill-fix:-webkit-autofill:hover, 
.autofill-fix:-webkit-autofill:focus, 
.autofill-fix:-webkit-autofill:active {
  -webkit-box-shadow: 0 0 0 30px white inset !important;
  -webkit-text-fill-color: #334155 !important;
  transition: background-color 5000s ease-in-out 0s;
}
</style>