<template>
  <div class="min-h-screen flex items-center justify-center p-6 bg-gray-100">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-2xl p-8 border border-yellow-100">
      
      <div class="text-center mb-10">
        <h1 class="text-4xl font-black text-yellow-600 tracking-tighter uppercase">Forever</h1>
        <p class="text-gray-400 text-xs uppercase tracking-[0.3em] mt-1 font-bold">Distribuidora La Paz</p>
      </div>

      <form v-if="!step2" @submit.prevent="handleLogin" class="space-y-5">
        <div>
          <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Correo Electrónico</label>
          <input v-model="form.email" type="email" placeholder="admin@forever.com" required 
            class="w-full px-5 py-3 rounded-2xl border border-gray-200 focus:border-yellow-500 focus:ring-4 focus:ring-yellow-100 outline-none transition-all">
        </div>
        <div>
          <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Contraseña</label>
          <input v-model="form.password" type="password" placeholder="••••••••" required 
            class="w-full px-5 py-3 rounded-2xl border border-gray-200 focus:border-yellow-500 focus:ring-4 focus:ring-yellow-100 outline-none transition-all">
        </div>
        <button type="submit" class="w-full bg-yellow-500 hover:bg-yellow-600 text-white font-extrabold py-4 rounded-2xl shadow-lg shadow-yellow-200 transition-all transform active:scale-95 uppercase">
          Entrar al Sistema
        </button>
        
        <!-- BOTÓN DE BIOMETRÍA -->
        <button type="button" @click="handleBiometricLogin" class="w-full bg-blue-50 hover:bg-blue-100 text-blue-600 font-extrabold py-4 rounded-2xl shadow-sm border border-blue-200 transition-all transform active:scale-95 flex justify-center items-center gap-2">
          <span>👆</span> Usar Huella / FaceID
        </button>
      </form>

      <form v-else @submit.prevent="handleVerify" class="space-y-6 text-center">
        <div class="bg-blue-50 text-blue-700 p-4 rounded-2xl text-sm font-bold border border-blue-100">
          🛡️ Verificación de Seguridad: Ingresa el código de 6 dígitos.
        </div>
        <input v-model="form.code" type="text" maxlength="6" placeholder="000000" required 
          class="w-full text-center text-5xl font-mono tracking-[0.3em] py-4 border-b-4 border-yellow-500 outline-none bg-transparent text-gray-800">
        <button type="submit" class="w-full bg-gray-900 hover:bg-black text-white font-bold py-4 rounded-2xl transition-all shadow-xl">
          CONFIRMAR CÓDIGO
        </button>
        <button @click="step2 = false" type="button" class="text-xs text-gray-400 hover:text-gray-600 underline">
          Volver al login
        </button>
      </form>

      <div v-if="errorMsg" class="mt-6 text-red-600 text-center text-sm font-bold bg-red-50 py-3 rounded-xl border border-red-200 italic">
        ⚠️ {{ errorMsg }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router' 
import api, { loginWithBiometrics } from '../api' 

const router = useRouter()
const step2 = ref(false)
const errorMsg = ref('')

const form = ref({
  email: '',
  password: '',
  code: ''
})

const handleLogin = async () => {
  try {
    errorMsg.value = ''
    const res = await api.post('/login', {
      email: form.value.email,
      password: form.value.password
    })
    
    if (res.data.require_2fa) {
      step2.value = true
    }
  } catch (err) {
    errorMsg.value = err.response?.data?.message || 'Credenciales incorrectas'
  }
}

// --- FUNCIÓN PARA LOGIN BIOMÉTRICO ---
const handleBiometricLogin = async () => {
  if (!form.value.email) {
    errorMsg.value = 'Por favor, ingresa tu correo arriba primero para buscar tu huella.';
    return;
  }

  try {
    errorMsg.value = '';
    
    const data = await loginWithBiometrics(form.value.email);
    
    if (data.token) {
      localStorage.setItem('auth_token', data.token);
      localStorage.setItem('user_role', data.user.role || 'ventas');
      localStorage.setItem('user_name', data.user.name);
      
      alert("¡Huella validada con éxito! Bienvenido.");
      router.push('/dashboard'); 
    }
  } catch (err) {
    errorMsg.value = err.response?.data?.message || 'Error al leer la huella o dispositivo no vinculado.';
  }
}

const handleVerify = async () => {
  try {
    errorMsg.value = ''
    const response = await api.post('/verify-otp', {
      email: form.value.email,
      code: form.value.code
    })

    if (response.data.token) {
      // 1. IMPORTANTE: Cambia 'token' por 'auth_token' 
      // para que coincida con tu api.js
      localStorage.setItem('auth_token', response.data.token)
      
      // 2. Guardamos el ROL (indispensable para los niveles de acceso)
      localStorage.setItem('user_role', response.data.user.role)
      
      // 3. Guardamos el NOMBRE para el saludo
      localStorage.setItem('user_name', response.data.user.name)
      
      alert("¡Acceso concedido! Bienvenido al sistema.")

      // Redirigimos al dashboard
      router.push('/dashboard') 
    }
  } catch (err) {
    // Si el error viene del backend, mostramos el mensaje real
    errorMsg.value = err.response?.data?.message || "Código inválido o ha expirado"
  }
}
</script>

<style scoped>
form {
  animation: slideUp 0.4s ease-out;
}
@keyframes slideUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>