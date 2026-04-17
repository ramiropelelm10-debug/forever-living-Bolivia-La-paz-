<template>
  <div class="min-h-screen bg-gray-50 p-4 md:p-8">
    <div class="max-w-6xl mx-auto">
      
      <div class="bg-white rounded-3xl shadow-xl p-8 border border-yellow-100 mb-8">
        <div class="flex justify-between items-center">
          <div>
            <h1 class="text-3xl font-black text-yellow-600 uppercase">Panel de Control</h1>
            <p class="text-gray-600">Bienvenido al sistema de <strong>Forever Distribuidora La Paz</strong></p>
          </div>
          <div class="flex gap-4">
            <button 
              @click="handleRegisterBiometrics" 
              class="flex items-center gap-2 bg-blue-600 text-white px-6 py-2 rounded-xl font-bold hover:bg-blue-700 transition-all shadow-lg shadow-blue-200">
              <i class="fas fa-fingerprint"></i> 
              Registrar FaceID / Huella
            </button>

            <button @click="logout" class="bg-red-50 text-red-500 px-4 py-2 rounded-xl font-bold hover:bg-red-100 transition-colors">
              Cerrar Sesión
            </button>
          </div>
        </div>
      </div>

      <BiometricsConfig />

      <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100">
        <ProductsComponent />
      </div>

      <div class="mt-8 p-4 bg-blue-50 rounded-2xl border border-blue-100 text-center">
        <p class="text-blue-800 text-xs font-bold uppercase tracking-widest">
          Sesión Segura ✅ | Verificación 2FA Superada | Distribuidora Forever Bolivia
        </p>
      </div>

    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { registerBiometrics } from '../api' // Ajusta esta ruta según tu carpeta
import ProductsComponent from './Products.vue' 
import BiometricsConfig from './BiometricsConfig.vue'
import Swal from 'sweetalert2'

const router = useRouter()

// LÓGICA PARA REGISTRAR LA HUELLA
const handleRegisterBiometrics = async () => {
  try {
    // Llamamos a la API para activar el sensor
    await registerBiometrics();
    
    Swal.fire({
      icon: 'success',
      title: '¡Dispositivo de Confianza!',
      text: 'Tu FaceID/Huella ha sido vinculada a Forever Bolivia correctamente.',
      confirmButtonColor: '#EAB308'
    });
  } catch (error) {
    console.error(error);
    Swal.fire({
      icon: 'error',
      title: 'Error de Biometría',
      text: 'No se pudo registrar. Asegúrate de usar localhost y tener un lector activo.',
    });
  }
}

const logout = () => {
  localStorage.removeItem('auth_token');
  localStorage.removeItem('user_role');
  localStorage.removeItem('user_name');
  router.push('/');
}
</script>