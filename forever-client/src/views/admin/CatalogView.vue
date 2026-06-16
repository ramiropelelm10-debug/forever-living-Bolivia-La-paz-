<template>
  <div class="space-y-8 animate-in fade-in duration-500 relative max-w-7xl mx-auto">
    
    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
      <div class="relative w-full md:w-96 shadow-sm">
        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
        <input type="text" v-model="searchQuery" placeholder="Buscar por nombre o SKU..." class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-600 focus:outline-none focus:border-[#4a5d23]">
      </div>
      
      <div class="flex items-center gap-4 w-full md:w-auto">
        <select v-model="selectedStatus" class="px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-600 focus:outline-none cursor-pointer hidden md:block shadow-sm">
          <option value="Todos los estados">Todos los estados</option>
          <option value="Disponible">Disponibles</option>
          <option value="Stock Bajo">Stock Bajo</option>
          <option value="Agotado">Agotados</option>
        </select>

        <button @click="abrirModalNuevo" class="px-6 py-3 bg-[#D4AF37] text-white rounded-xl font-black text-[11px] uppercase tracking-widest hover:bg-[#C5A028] transition-all shadow-lg shadow-[#D4AF37]/30 whitespace-nowrap">
          <i class="fas fa-plus mr-1"></i> Nuevo Producto
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex justify-between items-center group relative overflow-hidden">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-green-50/50 rounded-full blur-xl group-hover:scale-110 transition-transform"></div>
        <div class="relative z-10">
          <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Productos Activos</p>
          <h3 class="text-3xl font-black text-[#0A2617]">
            <span v-if="estaCargando"><i class="fas fa-spinner fa-spin text-xl"></i></span>
            <span v-else>{{ productosFiltrados.length }}</span>
          </h3>
          <p class="text-[9px] font-bold text-green-600 uppercase mt-1"><i class="fas fa-arrow-up mr-1"></i>Total en inventario</p>
        </div>
        <div class="w-12 h-12 rounded-full bg-green-50 border border-green-100 flex items-center justify-center text-[#4a5d23] text-lg relative z-10">
          <i class="fas fa-briefcase-medical"></i>
        </div>
      </div>
      
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex justify-between items-center group relative overflow-hidden">
        <div class="relative z-10">
          <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Stock Crítico</p>
          <h3 class="text-3xl font-black text-[#0A2617]">
            <span v-if="estaCargando"><i class="fas fa-spinner fa-spin text-xl"></i></span>
            <span v-else>{{ stockCriticoCount }}</span>
          </h3>
          <p class="text-[9px] font-bold text-orange-500 uppercase mt-1"><i class="fas fa-exclamation mr-1"></i>Productos (1 a 15 u.)</p>
        </div>
        <div class="w-12 h-12 rounded-full bg-orange-50 border border-orange-100 flex items-center justify-center text-orange-500 text-lg relative z-10">
          <i class="fas fa-exclamation-triangle"></i>
        </div>
      </div>

      <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex justify-between items-center group relative overflow-hidden">
        <div class="relative z-10">
          <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Valor Inventario (Bs)</p>
          <h3 class="text-3xl font-black text-[#0A2617]">
            <span v-if="estaCargando"><i class="fas fa-spinner fa-spin text-xl"></i></span>
            <span v-else>{{ totalInversion.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) }}</span>
          </h3>
          <p class="text-[9px] font-bold text-green-600 uppercase mt-1"><i class="fas fa-chart-line mr-1"></i>Total Inversión</p>
        </div>
        <div class="w-12 h-12 rounded-full bg-green-50 border border-green-100 flex items-center justify-center text-[#4a5d23] text-lg relative z-10">
          <i class="fas fa-money-bill-wave"></i>
        </div>
      </div>

      <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex justify-between items-center group relative overflow-hidden">
        <div class="relative z-10">
          <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Total CC Acumulados</p>
          <h3 class="text-3xl font-black text-[#0A2617]">
            <span v-if="estaCargando"><i class="fas fa-spinner fa-spin text-xl"></i></span>
            <span v-else>{{ totalCC.toFixed(3) }}</span>
          </h3>
          <p class="text-[9px] font-bold text-[#b48a2d] uppercase mt-1"><i class="fas fa-star mr-1"></i>Crédito de clientes</p>
        </div>
        <div class="w-12 h-12 rounded-full bg-[#fdfaf3] border border-[#e6d5b0] flex items-center justify-center text-[#D4AF37] text-lg relative z-10">
          <i class="fas fa-shopping-bag"></i>
        </div>
      </div>
    </div>

    <div class="mb-10">
      <div class="flex justify-between items-end mb-12">
        <h4 class="text-xs font-black text-slate-800 uppercase tracking-widest">Categorías</h4>
        <button v-if="selectedCategory" @click="selectedCategory = ''" class="text-[10px] font-black text-red-500 uppercase hover:underline">Quitar Filtro</button>
        <span v-else class="text-[10px] font-bold text-slate-400 uppercase">Ver todas las categorías ></span>
      </div>
      
      <div class="grid grid-cols-2 md:grid-cols-6 gap-5">
        <div v-for="cat in categorias" :key="cat.nombre" 
             @click="seleccionarCategoria(cat.nombre)"
             :class="['bg-white rounded-2xl p-4 shadow-sm border relative flex flex-col items-center justify-end h-28 hover:shadow-md transition-all cursor-pointer group',
                       selectedCategory === cat.nombre ? 'border-[#005A36] bg-green-50/20' : 'border-slate-50']">
          
          <img v-if="cat.img" :src="cat.img" class="absolute -top-12 h-24 object-contain drop-shadow-xl group-hover:-translate-y-2 transition-transform duration-300" :alt="cat.nombre">
          
          <div v-else class="absolute -top-8 w-16 h-16 bg-slate-50 rounded-full border-4 border-white shadow-sm flex items-center justify-center text-2xl text-slate-300 group-hover:-translate-y-2 transition-transform duration-300">
            <i :class="cat.icono"></i>
          </div>

          <h3 class="font-black text-[#0A2617] text-xs text-center mt-auto w-full">{{ cat.nombre }}</h3>
          <p class="text-[9px] text-slate-400 mb-2">Filtrar productos</p>
          <span class="absolute bottom-4 right-4 bg-[#0A2617] text-white text-[9px] font-black px-2 py-0.5 rounded-full">{{ cat.count || 0 }}</span>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
      <div class="p-5 border-b border-slate-50 flex justify-between items-center">
         <h4 class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Inventario de Productos</h4>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="text-[10px] uppercase tracking-widest text-slate-400 border-b border-slate-100">
              <th class="p-5 font-black">Producto</th>
              <th class="p-5 font-black text-center">SKU</th>
              <th class="p-5 font-black text-center">Categoría</th>
              <th class="p-5 font-black text-right">Precio (Bs)</th>
              <th class="p-5 font-black text-center">Stock</th>
              <th class="p-5 font-black text-center">CC</th>
              <th class="p-5 font-black text-center">Estado</th>
              <th class="p-5 font-black text-center">Acción</th>
            </tr>
          </thead>
          <tbody class="text-sm">
            
            <tr v-if="estaCargando">
              <td colspan="8" class="p-10 text-center text-slate-400 font-bold">
                <i class="fas fa-circle-notch fa-spin text-4xl mb-3 text-[#005A36]"></i>
                <p>Cargando inventario...</p>
              </td>
            </tr>

            <tr v-else-if="productosFiltrados.length === 0">
              <td colspan="8" class="p-10 text-center text-slate-400 font-bold">
                <i class="fas fa-box-open text-4xl mb-3 block opacity-30"></i>
                No hay productos en esta categoría.
              </td>
            </tr>
            
            <tr v-else v-for="prod in productosFiltrados" :key="prod.id || prod.sku" class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
              <td class="p-4">
                <div class="flex items-center gap-4">
                  <div class="w-10 h-10 rounded-lg bg-white border border-slate-200 shadow-sm flex items-center justify-center overflow-hidden p-1 shrink-0">
                    <img :src="prod.imagen || prod.foto_persona || prod.image || 'https://cdn-icons-png.flaticon.com/512/3004/3004655.png'" class="w-full h-full object-contain drop-shadow-sm">
                  </div>
                  <div>
                    <h5 class="font-black text-[#0A2617] text-sm">{{ prod.nombre || prod.name || 'Sin nombre' }}</h5>
                    <p class="text-[9px] font-bold text-slate-400 mt-0.5">Código: {{ prod.id }}</p>
                  </div>
                </div>
              </td>
              <td class="p-4 font-mono text-slate-500 text-xs text-center">{{ prod.sku || prod.codigo || prod.code || 'N/A' }}</td>
              <td class="p-4 text-center">
                 <span class="text-[10px] font-bold text-[#005A36]">
                    {{ prod.categoria || prod.category || 'General' }}
                 </span>
              </td>
              <td class="p-4 font-black text-[#0A2617] text-right">{{ parseFloat(prod.price_bs || prod.precio || prod.price || 0).toFixed(2) }}</td>
              <td class="p-4 text-center font-bold text-slate-600">{{ prod.stock || prod.cantidad || prod.quantity || 0 }}</td>
              <td class="p-4 text-center font-black text-[#b48a2d] text-xs">{{ parseFloat(prod.cc_value || 0).toFixed(3) }}</td>
              
              <td class="p-4">
                <div class="flex justify-center">
                  <span v-if="(prod.stock || prod.cantidad || prod.quantity || 0) > 15" class="px-2 py-1 rounded-md text-[9px] font-black flex items-center gap-1.5 uppercase tracking-widest text-green-600">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Disponible
                  </span>
                  <span v-else-if="(prod.stock || prod.cantidad || prod.quantity || 0) > 0" class="px-2 py-1 rounded-md text-[9px] font-black flex items-center gap-1.5 uppercase tracking-widest text-orange-500">
                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span> Stock Bajo
                  </span>
                  <span v-else class="px-2 py-1 rounded-md text-[9px] font-black flex items-center gap-1.5 uppercase tracking-widest text-red-600">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Agotado
                  </span>
                </div>
              </td>
              <td class="p-4 text-center">
                <div class="flex justify-center items-center gap-3 text-slate-300">
                  <button @click="verProducto(prod)" class="hover:text-[#005A36] transition-colors"><i class="fas fa-eye"></i></button>
                  <button @click="editarProducto(prod)" class="hover:text-blue-500 transition-colors"><i class="fas fa-pen"></i></button>
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
        
        <div class="bg-[#092615] px-6 py-4 flex justify-between items-center text-white">
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
              <label for="subir-foto" class="w-full flex flex-col items-center justify-center py-4 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:bg-white transition-colors">
                <div class="flex items-center gap-2 text-slate-500">
                  <i class="fas fa-cloud-upload-alt text-lg"></i>
                  <span class="text-[11px] font-black uppercase tracking-widest">Subir Imagen</span>
                </div>
              </label>
            </div>
          </div>

          <div class="space-y-1 md:col-span-2">
            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Nombre del Producto</label>
            <input type="text" v-model="form.nombre" placeholder="Ej. Aloe Vera Gel" class="w-full p-3 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-700 outline-none focus:border-[#005A36]">
          </div>

          <div class="space-y-1">
            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest">SKU (Código)</label>
            <input type="text" v-model="form.sku" placeholder="Ej. FL1005" class="w-full p-3 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-700 outline-none focus:border-[#005A36]">
          </div>

          <div class="space-y-1">
            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Categoría</label>
            <select v-model="form.categoria" class="w-full p-3 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-700 outline-none focus:border-[#005A36]">
              <option v-for="cat in categorias" :key="cat.nombre" :value="cat.nombre">{{ cat.nombre }}</option>
            </select>
          </div>

          <div class="space-y-1">
            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Precio (Bs)</label>
            <input type="number" step="0.01" v-model="form.precio" placeholder="0.00" class="w-full p-3 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-700 outline-none focus:border-[#005A36]">
          </div>

          <div class="space-y-1">
            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Stock Inicial</label>
            <input type="number" v-model="form.stock" placeholder="0" class="w-full p-3 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-700 outline-none focus:border-[#005A36]">
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
          <button @click="guardarProducto" class="px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest bg-[#D4AF37] text-white hover:bg-[#C5A028] transition-colors shadow-lg shadow-[#D4AF37]/30">
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
const form = ref({ id: null, nombre: '', sku: '', categoria: 'Aloe Vera', precio: null, stock: null, cc_value: null });
const productos = ref([]); 
const estaCargando = ref(true);

// Añadimos el token a la cabecera en cada petición
const getAuthHeaders = () => {
  return { headers: { 'Authorization': 'Bearer ' + localStorage.getItem('auth_token') } };
};

const obtenerProductos = async () => {
  try {
    estaCargando.value = true;
    const respuesta = await api.get('/products', getAuthHeaders()); 
    productos.value = respuesta.data.data || respuesta.data; 
    categorias.value.forEach(cat => {
      cat.count = productos.value.filter(p => (p.categoria || p.category) === cat.nombre).length;
    });
  } catch (error) {
    Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'Error de conexión', showConfirmButton: false, timer: 3000 });
  } finally {
    estaCargando.value = false;
  }
};

onMounted(() => { obtenerProductos(); });

const manejarSubidaImagen = (event) => {
  const file = event.target.files[0];
  if (file) { archivoFisico.value = file; imagenPreview.value = URL.createObjectURL(file); }
};

const categorias = ref([
  { nombre: 'Aloe Vera', img: '/images/cat-aloe.png', icono: 'fas fa-leaf', count: 0 },
  { nombre: 'Nutrición', img: '/images/cat-nutricion.png', icono: 'fas fa-pills', count: 0 },
  { nombre: 'Bebidas', img: '/images/cat-bebidas.png', icono: 'fas fa-glass-whiskey', count: 0 },
  { nombre: 'Cuidado Personal', img: '/images/cat-cuidado-personal.png', icono: 'fas fa-pump-soap', count: 0 },
  { nombre: 'Cosmética', img: null, icono: 'fas fa-magic', count: 0 },
  { nombre: 'Packs / Combos', img: null, icono: 'fas fa-boxes', count: 0 }
]);

const abrirModalNuevo = () => {
  modoEdicion.value = false;
  form.value = { id: null, nombre: '', sku: '', categoria: 'Aloe Vera', precio: null, stock: null, cc_value: null };
  imagenPreview.value = null; archivoFisico.value = null; mostrarModal.value = true;
};

const editarProducto = (prod) => {
  modoEdicion.value = true;
  form.value = { 
    id: prod.id, nombre: prod.nombre || prod.name || '', sku: prod.sku || prod.codigo || prod.code || '', 
    categoria: prod.categoria || prod.category || 'Aloe Vera', precio: prod.price_bs || prod.precio || prod.price || null, 
    stock: prod.stock || prod.cantidad || prod.quantity || 0, cc_value: prod.cc_value || 0
  }; 
  imagenPreview.value = prod.imagen || prod.foto_persona || prod.image; 
  archivoFisico.value = null; mostrarModal.value = true;
};

const cerrarModal = () => mostrarModal.value = false;

const guardarProducto = async () => {
  if(!form.value.nombre || !form.value.sku) return Swal.fire('Error', 'Completa el nombre y SKU principales', 'error');
  Swal.fire({ title: 'Guardando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

  const formData = new FormData();
  formData.append('name', form.value.nombre); formData.append('code', form.value.sku); formData.append('sku', form.value.sku); 
  formData.append('category', form.value.categoria); formData.append('price', form.value.precio || 0);
  formData.append('price_bs', form.value.precio || 0); formData.append('stock', form.value.stock || 0);
  formData.append('cc_value', form.value.cc_value || 0); formData.append('nombre', form.value.nombre);
  formData.append('categoria', form.value.categoria); formData.append('precio', form.value.precio || 0);
  formData.append('cantidad', form.value.stock || 0);

  if (archivoFisico.value) { formData.append('image', archivoFisico.value); formData.append('imagen', archivoFisico.value); }

  try {
    if (modoEdicion.value) {
      formData.append('_method', 'PUT'); 
      await api.post(`/products/${form.value.id}`, formData, getAuthHeaders()); 
      Swal.fire('Actualizado', 'Producto modificado.', 'success');
    } else {
      await api.post('/products', formData, getAuthHeaders()); 
      Swal.fire('Guardado', 'Nuevo producto creado.', 'success');
    }
    cerrarModal(); obtenerProductos(); 
  } catch (error) {
    Swal.fire('Error', 'No se pudo guardar', 'error');
  }
};

const verProducto = (prod) => Swal.fire('Detalles', `Viendo: ${prod.nombre || prod.name || 'Producto sin nombre'}`, 'success');

const eliminarProducto = async (prod) => {
  Swal.fire({ title: '¿Mandar a la papelera?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Sí, eliminar'
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await api.delete(`/products/${prod.id || prod.sku || prod.code}`, getAuthHeaders()); 
        Swal.fire('¡Eliminado!', 'Producto a papelera.', 'success');
        obtenerProductos(); 
      } catch (error) { Swal.fire('Error', 'No se pudo eliminar', 'error'); }
    }
  });
};

const seleccionarCategoria = (nombreCat) => { selectedCategory.value = selectedCategory.value === nombreCat ? '' : nombreCat; };

const productosFiltrados = computed(() => {
  return productos.value.filter(prod => {
    if (prod.activo === false || prod.status === 0 || prod.is_active === false) return false; 
    const nombreProd = (prod.nombre || prod.name || '').toLowerCase();
    const skuProd = (prod.sku || prod.codigo || prod.code || '').toLowerCase();
    const catProd = prod.categoria || prod.category || '';
    const stockProd = parseInt(prod.stock || prod.cantidad || prod.quantity || 0);
    
    let estadoActual = 'Agotado';
    if (stockProd > 15) estadoActual = 'Disponible'; else if (stockProd > 0) estadoActual = 'Stock Bajo';
    
    const coincideBusqueda = nombreProd.includes(searchQuery.value.toLowerCase()) || skuProd.includes(searchQuery.value.toLowerCase());
    const coincideCategoria = selectedCategory.value === '' || catProd === selectedCategory.value;
    const coincideEstado = selectedStatus.value === 'Todos los estados' || estadoActual === selectedStatus.value;
    return coincideBusqueda && coincideCategoria && coincideEstado;
  });
});

const totalInversion = computed(() => {
  return productosFiltrados.value.reduce((t, p) => t + (parseFloat(p.price_bs || p.precio || p.price || 0) * parseInt(p.stock || p.cantidad || p.quantity || 0)), 0);
});

const totalCC = computed(() => {
  return productosFiltrados.value.reduce((t, p) => t + (parseFloat(p.cc_value || 0) * parseInt(p.stock || p.cantidad || p.quantity || 0)), 0);
});

const stockCriticoCount = computed(() => {
  return productosFiltrados.value.filter(p => { const s = parseInt(p.stock || p.cantidad || p.quantity || 0); return s > 0 && s <= 15; }).length;
});
</script>

<style scoped>
@reference "tailwindcss";
</style>