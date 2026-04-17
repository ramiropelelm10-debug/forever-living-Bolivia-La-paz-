<template>
  <div class="p-6 bg-gray-50 min-h-screen">
    <!-- CABECERA DEL PANEL -->
    <div class="flex justify-between items-center mb-8 bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
      <div>
        <h1 class="text-2xl font-black text-gray-800 uppercase tracking-tight">Gestión Forever Bolivia</h1>
        <p class="text-gray-400 text-xs font-bold uppercase tracking-widest">Control de Inventario y Facturación</p>
      </div>
      
      <!-- FILTRO DE SEGURIDAD PARA NUEVO PRODUCTO -->
      <button v-if="isAdmin() || isAlmacen()" class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-3 rounded-2xl font-bold shadow-lg shadow-yellow-100 transition-all active:scale-95">
        📦 NUEVO PRODUCTO (ADUANA)
      </button>
    </div>

    <!-- TABLA DE PRODUCTOS -->
    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-gray-900 text-white text-xs uppercase tracking-widest">
            <th class="px-6 py-5">Detalle del Producto</th>
            <th class="px-6 py-5 text-center">Stock Actual</th>
            <th class="px-6 py-5 text-right">Acciones de Control</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <!-- Iteración real de productos de la DB -->
          <tr v-for="p in productos" :key="p.id" class="hover:bg-gray-50 transition-colors group">
            <td class="px-6 py-4">
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center text-xl shadow-inner">
                  📦
                </div>
                <div>
                  <div class="font-bold text-gray-800">{{ p.name }}</div>
                  <div class="text-[10px] text-blue-500 font-black uppercase">SKU: {{ p.sku || '000' }}</div>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 text-center">
              <span :class="p.stock < 10 ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600'" 
                    class="px-4 py-1.5 rounded-full text-xs font-black shadow-sm">
                {{ p.stock }} Unid.
              </span>
            </td>
            <td class="px-6 py-4">
              <div class="flex items-center justify-end gap-3">
                
                <!-- BOTÓN VENDER (FACTURACIÓN LEY BOLIVIA) -->
                <!-- Solo visible para ADMIN y VENTAS -->
                <button 
                  v-if="isAdmin() || isVentas()"
                  @click="realizarVenta(p)" 
                  class="flex items-center gap-2 px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-black rounded-xl shadow-lg shadow-emerald-100 transition-all active:scale-90"
                >
                  💰 <span>FACTURAR</span>
                </button>

                <!-- BOTONES DE EDICIÓN (SOLO ADMIN) -->
                <button v-if="isAdmin()" class="p-2 text-blue-400 hover:bg-blue-50 rounded-lg transition-colors">
                  ✏️
                </button>
                <button v-if="isAdmin()" class="p-2 text-red-400 hover:bg-red-50 rounded-lg transition-colors">
                  🗑️
                </button>
                
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- ESTADO DE CARGA -->
      <div v-if="productos.length === 0" class="p-20 text-center text-gray-400 italic font-medium">
        <div class="animate-pulse mb-2 text-2xl">🔄</div>
        Sincronizando con la Base de Datos de Forever...
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../api'; // Asegúrate de que apunte a tu archivo api.js (axios)
import { isAdmin, isAlmacen, isVentas } from '../utils/auth';

// 1. Estado reactivo para los productos que vienen de PostgreSQL
const productos = ref([]);

// 2. Función para cargar productos desde Laravel
const cargarProductos = async () => {
  try {
    const response = await api.get('/products');
    // Laravel devuelve la lista de productos
    productos.value = response.data;
  } catch (error) {
    console.error("Error al conectar con la API:", error);
  }
};

// 3. Función de Venta / Facturación (Cálculo de IVA 13% e IT 3%)
const realizarVenta = async (producto) => {
  const cantidad = prompt(`REGISTRO DE VENTA PARA: ${producto.name}\n\nIngrese la cantidad a facturar:`);
  
  if (cantidad && cantidad > 0) {
    try {
      // Enviamos la orden al SaleController
      await api.post('/sales', {
        product_id: producto.id,
        cantidad: parseInt(cantidad)
      });
      
      // Notificación de éxito
      alert(`✅ FACTURA GENERADA\n\nProducto: ${producto.name}\nCantidad: ${cantidad}\n\nImpuestos aplicados (IVA 13% / IT 3%) correctamente en el sistema.`);
      
      // Actualizamos la tabla sin recargar toda la página
      cargarProductos(); 
      
    } catch (error) {
      const msg = error.response?.data?.message || "No se pudo procesar la factura";
      alert("❌ ERROR: " + msg);
    }
  }
};

// 4. Ciclo de vida: Cargar datos al entrar a la pantalla
onMounted(() => {
  cargarProductos();
});
</script>

<style scoped>
/* Transición suave para la tabla */
tbody tr {
  transition: all 0.2s ease;
}
</style>