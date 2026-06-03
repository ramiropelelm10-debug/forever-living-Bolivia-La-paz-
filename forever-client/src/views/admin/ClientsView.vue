<template>
  <div class="animate-fade-in">
    <section class="bg-white p-10 rounded-[3rem] shadow-xl border border-slate-100">
      
      <div class="flex items-center gap-4 mb-8 border-b-2 border-slate-50 pb-4">
        <div class="bg-emerald-600 text-white p-3 rounded-2xl">
          <i class="fas fa-user-plus"></i>
        </div>
        <h2 class="text-2xl font-black text-slate-800 uppercase italic">Registro de Nuevo Cliente</h2>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        <div class="space-y-1">
          <label class="text-[10px] font-black uppercase text-slate-400 ml-4">Nombres</label>
          <input type="text" v-model="clientData.name" @input="normalizarLetras('name')" placeholder="Ej. Juan" autocomplete="off"
            class="w-full p-4 bg-slate-50 border-2 border-slate-100 rounded-2xl font-bold focus:border-emerald-500 outline-none transition">
        </div>
        
        <div class="space-y-1">
          <label class="text-[10px] font-black uppercase text-slate-400 ml-4">Apellidos</label>
          <input type="text" v-model="clientData.last_name" @input="normalizarLetras('last_name')" placeholder="Ej. Pérez" autocomplete="off"
            class="w-full p-4 bg-slate-50 border-2 border-slate-100 rounded-2xl font-bold focus:border-emerald-500 outline-none transition">
        </div>
        
        <div class="space-y-1">
          <label class="text-[10px] font-black uppercase text-slate-400 ml-4">Correo Electrónico</label>
          <input type="email" v-model="clientData.email" @input="normalizarCorreo" placeholder="juan@ejemplo.com" autocomplete="off"
            class="w-full p-4 bg-slate-50 border-2 border-slate-100 rounded-2xl font-bold focus:border-emerald-500 outline-none transition">
        </div>
        
        <div class="space-y-1">
          <label class="text-[10px] font-black uppercase text-slate-400 ml-4">CI o NIT</label>
          <input type="text" v-model="clientData.dni" @input="normalizarDNI" placeholder="Ej. 1234567 o 1234567-1B" autocomplete="off"
            class="w-full p-4 bg-slate-50 border-2 border-slate-100 rounded-2xl font-bold focus:border-emerald-500 outline-none transition uppercase tracking-widest">
        </div>
        
        <div class="space-y-1">
          <label class="text-[10px] font-black uppercase text-slate-400 ml-4">Celular / Teléfono</label>
          <input type="tel" v-model="clientData.phone" @input="normalizarTelefono" placeholder="Ej. 70000000" autocomplete="off"
            class="w-full p-4 bg-slate-50 border-2 border-slate-100 rounded-2xl font-bold focus:border-emerald-500 outline-none transition">
        </div>

        <div class="space-y-1">
          <label class="text-[10px] font-black uppercase text-emerald-600 ml-4 italic">Asignar Contraseña (Min. 6)</label>
          <div class="relative">
            <input type="password" v-model="clientData.password" placeholder="******" autocomplete="new-password"
              class="w-full p-4 bg-emerald-50 border-2 border-emerald-100 rounded-2xl font-bold focus:border-emerald-500 outline-none transition text-emerald-700">
            <i class="fas fa-lock absolute right-4 top-5 text-emerald-300"></i>
          </div>
        </div>
          
      </div>

      <button @click="guardarCliente" 
        class="mt-10 w-full bg-emerald-600 text-white py-5 rounded-[2rem] font-black uppercase text-xs hover:bg-emerald-700 active:scale-95 transition shadow-xl shadow-emerald-200 flex items-center justify-center gap-3">
        <i class="fas fa-save"></i> Guardar Cliente en Base de Datos
      </button>

    </section>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { UsuariosService } from '../../composables/productService.js';
import Swal from 'sweetalert2';

// 1. Objeto del cliente
const clientData = ref({ 
  name: '', 
  last_name: '', 
  email: '', 
  dni: '',       
  phone: '', 
  password: '' 
});

// 🔥 2. FUNCIONES DE NORMALIZACIÓN EN TIEMPO REAL 🔥

// Solo permite letras (incluyendo acentos y ñ) y espacios
const normalizarLetras = (campo) => {
  clientData.value[campo] = clientData.value[campo].replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
};

// Elimina espacios en blanco para el correo
const normalizarCorreo = () => {
  clientData.value.email = clientData.value.email.replace(/\s/g, '');
};

// Solo permite números y el signo +
const normalizarTelefono = () => {
  clientData.value.phone = clientData.value.phone.replace(/[^0-9+]/g, '');
};

// Convierte a mayúsculas y permite solo números, letras y el guion medio
const normalizarDNI = () => {
  clientData.value.dni = clientData.value.dni.toUpperCase().replace(/[^A-Z0-9-]/g, '');
};


// 3. Función para guardar
const guardarCliente = async () => {
  if(!clientData.value.name || !clientData.value.dni || !clientData.value.email || !clientData.value.password) {
    Swal.fire({
      title: 'Campos Incompletos',
      text: 'Por favor, rellene todos los campos, incluyendo la contraseña.',
      icon: 'warning',
      confirmButtonColor: '#b48a2d'
    });
    return;
  }

  if(clientData.value.password.length < 6) {
    Swal.fire('Contraseña Corta', 'La contraseña debe tener al menos 6 caracteres.', 'error');
    return;
  }

  try {
    await UsuariosService.saveClient(clientData.value);
    
    Swal.fire({
      title: '¡Registrado!',
      text: 'El cliente ha sido guardado con éxito.',
      icon: 'success',
      confirmButtonColor: '#059669'
    });

    clientData.value = { name: '', last_name: '', email: '', dni: '', phone: '', password: '' };
    
  } catch (error) {
    console.error("Error de Laravel:", error);
    
    const errors = error.response?.data?.errors;
    let errorMensaje = "";
    
    if (errors) {
      errorMensaje = Object.values(errors).flat().join('<br> • ');
    } else {
      errorMensaje = error.response?.data?.message || 'Error de comunicación';
    }

    Swal.fire({
      title: 'Error de Validación',
      html: `<div class="text-left text-sm text-red-600 font-bold uppercase mt-2">
              Verifique los datos:<br><br> • ${errorMensaje}
             </div>`,
      icon: 'error'
    });
  }
};
</script>

<style scoped>
@reference "tailwindcss";

.animate-fade-in {
  animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>