<template>
  <div class="space-y-6 animate-in fade-in duration-500 relative">
    
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 bg-white p-4 rounded-2xl shadow-sm border border-slate-100">
      <div class="flex items-center gap-2 text-[#4a5d23] font-black tracking-widest uppercase text-sm">
        <i class="fas fa-box-open text-xl"></i> Inventario
      </div>
      
      <div class="flex items-center gap-4 w-full md:w-auto">
        <div class="relative w-full md:w-72">
          <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
          <input type="text" v-model="searchQuery" placeholder="Buscar por nombre o SKU..." class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-600 focus:outline-none focus:border-[#4a5d23]">
        </div>
        
        <select v-model="selectedStatus" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-600 focus:outline-none cursor-pointer hidden md:block">
          <option value="Todos los estados">Todos los estados</option>
          <option value="Disponible">Disponibles</option>
          <option value="Stock Bajo">Stock Bajo</option>
          <option value="Agotado">Agotados</option>
        </select>

        <button @click="abrirModalNuevo" class="px-6 py-2.5 bg-[#4a5d23] text-white rounded-xl font-black text-[11px] uppercase tracking-widest hover:bg-[#38471a] transition-all shadow-lg shadow-[#4a5d23]/30 whitespace-nowrap">
          <i class="fas fa-plus mr-1"></i> Nuevo Producto
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex justify-between items-center group">
        <div>
          <p class="text-[10px] font-black text-[#4a5d23] uppercase tracking-widest mb-1">Productos</p>
          <h3 class="text-3xl font-black text-slate-800">
            <span v-if="estaCargando"><i class="fas fa-spinner fa-spin text-xl"></i></span>
            <span v-else>{{ productosFiltrados.length }}</span>
          </h3>
          <p class="text-[9px] font-bold text-slate-400 uppercase mt-1">Total Activos</p>
        </div>
        <div class="w-12 h-12 rounded-full bg-green-50 flex items-center justify-center text-[#4a5d23] text-xl">
          <i class="fas fa-shopping-bag"></i>
        </div>
      </div>
      
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex justify-between items-center group">
        <div>
          <p class="text-[10px] font-black text-orange-600 uppercase tracking-widest mb-1">Stock Crítico</p>
          <h3 class="text-3xl font-black text-slate-800">
            <span v-if="estaCargando"><i class="fas fa-spinner fa-spin text-xl"></i></span>
            <span v-else>{{ stockCriticoCount }}</span>
          </h3>
          <p class="text-[9px] font-bold text-slate-400 uppercase mt-1">Productos (1 a 15 u.)</p>
        </div>
        <div class="w-12 h-12 rounded-full bg-orange-50 flex items-center justify-center text-orange-500 text-xl">
          <i class="fas fa-exclamation-triangle"></i>
        </div>
      </div>

      <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex justify-between items-center group">
        <div>
          <p class="text-[10px] font-black text-[#4a5d23] uppercase tracking-widest mb-1">Inversión (Bs)</p>
          <h3 class="text-3xl font-black text-slate-800">
            <span v-if="estaCargando"><i class="fas fa-spinner fa-spin text-xl"></i></span>
            <span v-else>{{ totalInversion.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) }}</span>
          </h3>
          <p class="text-[9px] font-bold text-slate-400 uppercase mt-1">Valor de Inventario</p>
        </div>
        <div class="w-12 h-12 rounded-full bg-green-50 flex items-center justify-center text-[#4a5d23] text-xl">
          <i class="fas fa-money-bill-wave"></i>
        </div>
      </div>

      <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex justify-between items-center group">
        <div>
          <p class="text-[10px] font-black text-[#4a5d23] uppercase tracking-widest mb-1">Total CC</p>
          <h3 class="text-3xl font-black text-slate-800">
            <span v-if="estaCargando"><i class="fas fa-spinner fa-spin text-xl"></i></span>
            <span v-else>{{ totalCC.toFixed(3) }}</span>
          </h3>
          <p class="text-[9px] font-bold text-slate-400 uppercase mt-1">CC Acumulados</p>
        </div>
        <div class="w-12 h-12 rounded-full bg-green-50 flex items-center justify-center text-[#4a5d23] text-xl">
          <i class="fas fa-user-circle"></i>
        </div>
      </div>
    </div>

    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
      <div class="flex justify-between items-center mb-4">
        <h4 class="text-[10px] font-black text-[#4a5d23] uppercase tracking-widest">Categorías</h4>
        <button v-if="selectedCategory" @click="selectedCategory = ''" class="text-[9px] font-black text-red-500 uppercase hover:underline">Quitar Filtro</button>
      </div>
      
      <div class="flex overflow-x-auto gap-4 pb-2 custom-scrollbar justify-between">
        <button 
          v-for="cat in categorias" 
          :key="cat.nombre" 
          @click="seleccionarCategoria(cat.nombre)"
          :class="['flex flex-col items-center justify-center min-w-[100px] p-3 rounded-xl transition-all border group', 
                   selectedCategory === cat.nombre ? 'bg-[#4a5d23]/10 border-[#4a5d23] scale-105' : 'border-transparent hover:bg-slate-50 hover:border-slate-100']"
        >
          <i :class="[cat.icono, selectedCategory === cat.nombre ? 'text-[#4a5d23]' : cat.colorClase, 'text-2xl mb-2']"></i>
          <span :class="['text-[11px] font-black whitespace-nowrap', selectedCategory === cat.nombre ? 'text-[#4a5d23]' : 'text-slate-700']">{{ cat.nombre }}</span>
          <span class="text-[9px] font-bold text-slate-400">Filtrar</span>
        </button>
      </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-[#4a5d23] text-white text-[10px] uppercase tracking-widest">
              <th class="p-4 font-black rounded-tl-lg">Producto</th>
              <th class="p-4 font-black">Categoría</th>
              <th class="p-4 font-black">SKU</th>
              <th class="p-4 font-black">Precio (Bs)</th>
              <th class="p-4 font-black text-center">Stock</th>
              <th class="p-4 font-black text-center">Estado</th>
              <th class="p-4 font-black text-center rounded-tr-lg">Acción</th>
            </tr>
          </thead>
          <tbody class="text-sm">
            
            <tr v-if="estaCargando">
              <td colspan="7" class="p-8 text-center text-slate-400 font-bold">
                <i class="fas fa-circle-notch fa-spin text-4xl mb-3 text-[#4a5d23]"></i>
                <p>Cargando inventario desde Laravel...</p>
              </td>
            </tr>

            <tr v-else-if="productosFiltrados.length === 0">
              <td colspan="7" class="p-8 text-center text-slate-400 font-bold">
                <i class="fas fa-box-open text-4xl mb-3 block opacity-50"></i>
                No hay productos que coincidan con estos filtros.
              </td>
            </tr>
            
            <tr v-else v-for="prod in productosFiltrados" :key="prod.id || prod.sku" class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
              <td class="p-4">
                <div class="flex items-center gap-4">
                  <div class="w-12 h-12 rounded-xl bg-white border border-slate-200 shadow-sm flex items-center justify-center overflow-hidden p-1 shrink-0">
                    <img :src="prod.imagen || prod.foto_persona || prod.image || 'https://cdn-icons-png.flaticon.com/512/3004/3004655.png'" class="w-full h-full object-cover">
                  </div>
                  <div>
                    <h5 class="font-black text-slate-800 text-[13px]">{{ prod.nombre || prod.name || 'Sin nombre' }}</h5>
                    <p class="text-[10px] font-bold text-[#b48a2d] mt-0.5">{{ parseFloat(prod.cc_value || 0).toFixed(3) }} CC</p>
                  </div>
                </div>
              </td>
              <td class="p-4 font-bold text-slate-600 text-xs">{{ prod.categoria || prod.category || 'General' }}</td>
              <td class="p-4 font-black text-slate-400 text-xs tracking-wider">{{ prod.sku || prod.codigo || prod.code || 'N/A' }}</td>
              
              <td class="p-4 font-black text-slate-800">{{ parseFloat(prod.price_bs || prod.precio || prod.price || 0).toFixed(2) }}</td>
              
              <td class="p-4 text-center">
                <span :class="['font-black', (prod.stock || prod.cantidad || prod.quantity || 0) > 15 ? 'text-[#4a5d23]' : (prod.stock || prod.cantidad || prod.quantity || 0) > 0 ? 'text-orange-500' : 'text-red-500']">
                  {{ prod.stock || prod.cantidad || prod.quantity || 0 }}
                </span>
              </td>
              <td class="p-4">
                <div class="flex justify-center">
                  <span v-if="(prod.stock || prod.cantidad || prod.quantity || 0) > 15" class="px-3 py-1 rounded-full text-[10px] font-black flex items-center gap-1.5 w-fit uppercase tracking-widest bg-green-50 text-[#4a5d23]">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#4a5d23]"></span> Disponible
                  </span>
                  <span v-else-if="(prod.stock || prod.cantidad || prod.quantity || 0) > 0" class="px-3 py-1 rounded-full text-[10px] font-black flex items-center gap-1.5 w-fit uppercase tracking-widest bg-orange-50 text-orange-600">
                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span> Stock Bajo
                  </span>
                  <span v-else class="px-3 py-1 rounded-full text-[10px] font-black flex items-center gap-1.5 w-fit uppercase tracking-widest bg-red-50 text-red-600">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Agotado
                  </span>
                </div>
              </td>
              <td class="p-4 text-center">
                <div class="flex justify-center items-center gap-3 text-slate-400">
                  <button @click="verProducto(prod)" class="hover:text-[#4a5d23] transition-colors"><i class="fas fa-eye"></i></button>
                  <button @click="editarProducto(prod)" class="hover:text-[#FFC600] transition-colors"><i class="fas fa-pen"></i></button>
                  <button @click="eliminarProducto(prod)" class="hover:text-red-500 transition-colors"><i class="fas fa-trash"></i></button>
                </div>
              </td>
            </tr>

          </tbody>
        </table>
      </div>
    </div>

    <div v-if="mostrarModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
      <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden animate-in zoom-in-95 duration-200">
        
        <div class="bg-[#4a5d23] px-6 py-4 flex justify-between items-center text-white">
          <h2 class="font-black tracking-widest uppercase text-sm">
            <i class="fas fa-box mr-2"></i> {{ modoEdicion ? 'Editar Producto' : 'Nuevo Producto' }}
          </h2>
          <button @click="cerrarModal" class="text-white/70 hover:text-white transition-colors">
            <i class="fas fa-times text-xl"></i>
          </button>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
          
          <div class="md:col-span-2 flex items-center gap-4 p-4 border border-slate-100 bg-slate-50 rounded-2xl">
            <div class="w-20 h-20 rounded-xl border-2 border-dashed border-slate-300 flex items-center justify-center bg-white overflow-hidden shrink-0">
              <img v-if="imagenPreview" :src="imagenPreview" class="w-full h-full object-cover">
              <i v-else class="fas fa-image text-slate-200 text-3xl"></i>
            </div>
            
            <div class="flex-1 w-full relative">
              <input type="file" id="subir-foto" accept="image/*" @change="manejarSubidaImagen" class="hidden" />
              <label for="subir-foto" class="w-full flex flex-col items-center justify-center py-4 border-2 border-dashed border-[#4a5d23]/30 rounded-xl cursor-pointer hover:bg-[#4a5d23]/5 transition-colors">
                <div class="flex items-center gap-2 text-[#4a5d23]">
                  <i class="fas fa-cloud-upload-alt text-lg"></i>
                  <span class="text-[11px] font-black uppercase tracking-widest">Subir Imagen</span>
                </div>
                <p class="text-[9px] font-bold text-slate-400 mt-1">Formatos: JPG, PNG, WEBP</p>
              </label>
            </div>
          </div>

          <div class="space-y-1 md:col-span-2">
            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Nombre del Producto</label>
            <input type="text" v-model="form.nombre" placeholder="Ej. Aloe Vera Gel" class="w-full p-3 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-700 outline-none focus:border-[#4a5d23]">
          </div>

          <div class="space-y-1">
            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest">SKU (Código)</label>
            <input type="text" v-model="form.sku" placeholder="Ej. FL1005" class="w-full p-3 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-700 outline-none focus:border-[#4a5d23]">
          </div>

          <div class="space-y-1">
            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Categoría</label>
            <select v-model="form.categoria" class="w-full p-3 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-700 outline-none focus:border-[#4a5d23]">
              <option v-for="cat in categorias" :key="cat.nombre" :value="cat.nombre">{{ cat.nombre }}</option>
            </select>
          </div>

          <div class="space-y-1">
            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Precio (Bs)</label>
            <input type="number" step="0.01" v-model="form.precio" placeholder="0.00" class="w-full p-3 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-700 outline-none focus:border-[#4a5d23]">
          </div>

          <div class="space-y-1">
            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Stock Inicial</label>
            <input type="number" v-model="form.stock" placeholder="0" class="w-full p-3 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-700 outline-none focus:border-[#4a5d23]">
          </div>
          
          <div class="space-y-1 md:col-span-2">
            <label class="text-[10px] font-black text-[#b48a2d] uppercase tracking-widest">Puntos CC (Case Credits)</label>
            <input type="number" step="0.001" v-model="form.cc_value" placeholder="Ej. 0.043" class="w-full p-3 bg-[#fdfaf3] border border-[#e6d5b0] rounded-xl text-sm font-bold text-[#b48a2d] outline-none focus:border-[#b48a2d]">
          </div>

        </div>

        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
          <button @click="cerrarModal" class="px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest text-slate-500 hover:bg-slate-200 transition-colors">
            Cancelar
          </button>
          <button @click="guardarProducto" class="px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest bg-[#4a5d23] text-white hover:bg-[#38471a] transition-colors shadow-lg shadow-[#4a5d23]/30">
            <i class="fas fa-save mr-1"></i> Guardar
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import Swal from 'sweetalert2';
import api from '../../api.js'; 

const searchQuery = ref('');
const selectedStatus = ref('Todos los estados');
const selectedCategory = ref('');

const mostrarModal = ref(false);
const modoEdicion = ref(false);
const imagenPreview = ref(null); 
const archivoFisico = ref(null); 

const form = ref({
  id: null, nombre: '', sku: '', categoria: 'Aloe Vera', precio: null, stock: null, cc_value: null
});

// ==========================================
// 📡 CONEXIÓN REAL CON LARAVEL
// ==========================================
const productos = ref([]); 
const estaCargando = ref(true);

const obtenerProductos = async () => {
  try {
    estaCargando.value = true;
    const respuesta = await api.get('/products'); 
    productos.value = respuesta.data.data || respuesta.data; 
  } catch (error) {
    console.error("Error al traer los productos:", error);
    Swal.fire({
      toast: true, position: 'top-end', icon: 'error',
      title: 'No se pudo conectar con la base de datos', showConfirmButton: false, timer: 3000
    });
  } finally {
    estaCargando.value = false;
  }
};

onMounted(() => {
  obtenerProductos();
});

const manejarSubidaImagen = (event) => {
  const file = event.target.files[0];
  if (file) {
    archivoFisico.value = file;
    imagenPreview.value = URL.createObjectURL(file); 
  }
};

const categorias = ref([
  { nombre: 'Aloe Vera', icono: 'fas fa-leaf', colorClase: 'text-[#4a5d23]' },
  { nombre: 'Nutrición', icono: 'fas fa-pills', colorClase: 'text-[#4a5d23]' },
  { nombre: 'Cuidado Personal', icono: 'fas fa-pump-soap', colorClase: 'text-[#4a5d23]' },
  { nombre: 'Cosmética', icono: 'fas fa-magic', colorClase: 'text-[#4a5d23]' },
  { nombre: 'Bebidas', icono: 'fas fa-glass-whiskey', colorClase: 'text-[#4a5d23]' },
  { nombre: 'Packs / Combos', icono: 'fas fa-boxes', colorClase: 'text-[#4a5d23]' }
]);

const abrirModalNuevo = () => {
  modoEdicion.value = false;
  form.value = { id: null, nombre: '', sku: '', categoria: 'Aloe Vera', precio: null, stock: null, cc_value: null };
  imagenPreview.value = null; 
  archivoFisico.value = null;
  mostrarModal.value = true;
};

const editarProducto = (prod) => {
  modoEdicion.value = true;
  form.value = { 
    id: prod.id, 
    nombre: prod.nombre || prod.name || '', 
    sku: prod.sku || prod.codigo || prod.code || '', 
    categoria: prod.categoria || prod.category || 'Aloe Vera', 
    precio: prod.price_bs || prod.precio || prod.price || null, 
    stock: prod.stock || prod.cantidad || prod.quantity || 0,
    cc_value: prod.cc_value || 0
  }; 
  imagenPreview.value = prod.imagen || prod.foto_persona || prod.image; 
  archivoFisico.value = null;
  mostrarModal.value = true;
};

const cerrarModal = () => mostrarModal.value = false;

const guardarProducto = async () => {
  if(!form.value.nombre || !form.value.sku) {
    return Swal.fire('Error', 'Completa el nombre y SKU principales', 'error');
  }

  Swal.fire({ title: 'Guardando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

  const formData = new FormData();
  
  formData.append('name', form.value.nombre);
  formData.append('code', form.value.sku);
  formData.append('sku', form.value.sku); 
  formData.append('category', form.value.categoria);
  formData.append('price', form.value.precio || 0);
  formData.append('price_bs', form.value.precio || 0); // Para tu DB
  formData.append('stock', form.value.stock || 0);
  formData.append('cc_value', form.value.cc_value || 0); // Para tu DB

  formData.append('nombre', form.value.nombre);
  formData.append('categoria', form.value.categoria);
  formData.append('precio', form.value.precio || 0);
  formData.append('cantidad', form.value.stock || 0);

  if (archivoFisico.value) {
    formData.append('image', archivoFisico.value);
    formData.append('imagen', archivoFisico.value);
  }

  try {
    if (modoEdicion.value) {
      formData.append('_method', 'PUT'); 
      await api.post(`/products/${form.value.id}`, formData); 
      Swal.fire('Actualizado', 'Producto modificado exitosamente.', 'success');
    } else {
      await api.post('/products', formData); 
      Swal.fire('Guardado', 'Nuevo producto creado.', 'success');
    }
    cerrarModal();
    obtenerProductos(); 
  } catch (error) {
    console.error("Error capturado:", error.response);
    let mensajeError = 'No se pudo guardar en la base de datos';
    if (error.response && error.response.data && error.response.data.message) {
        mensajeError = error.response.data.message;
    }
    Swal.fire('Error de Validación', mensajeError, 'error');
  }
};

const verProducto = (prod) => Swal.fire('Detalles', `Viendo: ${prod.nombre || prod.name || 'Producto sin nombre'}`, 'success');

const eliminarProducto = async (prod) => {
  const nombreProd = prod.nombre || prod.name || 'este producto';
  Swal.fire({
    title: '¿Mandar a la papelera?',
    text: `Vas a ocultar ${nombreProd}.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#8f949c',
    confirmButtonText: 'Sí, eliminar'
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await api.delete(`/products/${prod.id || prod.sku || prod.code}`); 
        Swal.fire('¡Eliminado!', 'El producto fue enviado a la papelera.', 'success');
        obtenerProductos(); 
      } catch (error) {
        Swal.fire('Error', 'No se pudo eliminar en el servidor', 'error');
      }
    }
  });
};

const seleccionarCategoria = (nombreCat) => {
  selectedCategory.value = selectedCategory.value === nombreCat ? '' : nombreCat;
};

// --- CEREBRO DE LOS FILTROS Y BÚSQUEDA ---
const productosFiltrados = computed(() => {
  return productos.value.filter(prod => {
    if (prod.activo === false || prod.status === 0 || prod.is_active === false) return false; 
    
    const nombreProd = (prod.nombre || prod.name || '').toLowerCase();
    const skuProd = (prod.sku || prod.codigo || prod.code || '').toLowerCase();
    const catProd = prod.categoria || prod.category || '';
    const stockProd = parseInt(prod.stock || prod.cantidad || prod.quantity || 0);
    
    let estadoActual = 'Agotado';
    if (stockProd > 15) estadoActual = 'Disponible';
    else if (stockProd > 0) estadoActual = 'Stock Bajo';

    const terminoBusqueda = searchQuery.value.toLowerCase();
    
    const coincideBusqueda = nombreProd.includes(terminoBusqueda) || skuProd.includes(terminoBusqueda);
    const coincideCategoria = selectedCategory.value === '' || catProd === selectedCategory.value;
    const coincideEstado = selectedStatus.value === 'Todos los estados' || estadoActual === selectedStatus.value;
    
    return coincideBusqueda && coincideCategoria && coincideEstado;
  });
});

// ==========================================
// 🧮 CALCULADORAS PARA LAS TARJETAS (KPIs)
// ==========================================
const totalInversion = computed(() => {
  return productosFiltrados.value.reduce((total, prod) => {
    const precio = parseFloat(prod.price_bs || prod.precio || prod.price || 0);
    const stock = parseInt(prod.stock || prod.cantidad || prod.quantity || 0);
    return total + (precio * stock);
  }, 0);
});

const totalCC = computed(() => {
  return productosFiltrados.value.reduce((total, prod) => {
    const cc = parseFloat(prod.cc_value || 0);
    const stock = parseInt(prod.stock || prod.cantidad || prod.quantity || 0);
    return total + (cc * stock);
  }, 0);
});

const stockCriticoCount = computed(() => {
  return productosFiltrados.value.filter(prod => {
    const stock = parseInt(prod.stock || prod.cantidad || prod.quantity || 0);
    return stock > 0 && stock <= 15;
  }).length;
});

</script>

<style scoped>
@reference "tailwindcss";

.custom-scrollbar::-webkit-scrollbar { height: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
.custom-scrollbar:hover::-webkit-scrollbar-thumb { background: #cbd5e1; }
</style>