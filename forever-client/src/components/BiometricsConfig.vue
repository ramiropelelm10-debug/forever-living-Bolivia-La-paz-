<template>
  <div class="bg-white rounded-3xl shadow-xl p-8 border border-blue-100 mb-8 text-center">
    <h2 class="text-2xl font-black text-blue-800 uppercase mb-2">Acceso con Face ID</h2>
    <p class="text-gray-600 mb-6">Usa tu rostro o huella para iniciar sesión de forma segura sin contraseñas.</p>

    <button @click="vincularFaceID" :disabled="cargando"
      class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-2xl shadow-lg transition-all active:scale-95 disabled:opacity-50 flex items-center justify-center mx-auto gap-2">
      <span v-if="!cargando">🆔</span>
      <span v-else>⏳</span>
      {{ cargando ? 'Esperando sensor...' : 'Configurar Face ID / Biometría' }}
    </button>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { registerBiometrics } from '../api.js';

const cargando = ref(false);

const vincularFaceID = async () => {
  try {
    cargando.value = true;
    
    // Ejecutamos la función que ya arreglamos en api.js (la que envía el email)
    await registerBiometrics();
    
    alert("¡Face ID vinculado con éxito! La próxima vez podrás entrar con un toque.");
    
  } catch (error) {
    // Si el error es por seguridad (dominio), damos un mensaje claro
    if (error.name === 'NotAllowedError' || error.name === 'SecurityError') {
      alert("Error de Seguridad: Asegúrate de usar 'localhost' en lugar de la IP y de tener el sensor listo.");
    } else {
      alert("No se pudo completar el registro. Verifica que tu navegador soporte Face ID.");
    }
    console.error("Detalle del error:", error);
  } finally {
    cargando.value = false;
  }
};
</script>