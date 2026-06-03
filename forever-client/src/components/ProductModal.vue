<template>
  <div class="space-y-6">
    
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6" v-show="!isTrashView">
      <div class="stat-card">
        <p class="stat-label text-[#4a5d23]">Productos</p>
        <h3 class="stat-value">{{ stats.total_items }}</h3>
        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-1">Total Registrados</p>
      </div>
      <div class="stat-card border-l-4 border-l-[#b48a2d]">
        <p class="stat-label text-[#b48a2d]">Stock Crítico</p>
        <h3 class="stat-value text-[#b48a2d]">{{ stats.low_stock }}</h3>
        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-1">Productos</p>
      </div>
      <div class="stat-card">
        <p class="stat-label text-[#4a5d23]">Inversión (BS)</p>
        <h3 class="stat-value text-[#4a5d23]">{{ stats.total_value }}</h3>
        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-1">Total Inversión</p>
      </div>
      <div class="stat-card">
        <p class="stat-label text-[#4a5d23]">Total CC</p>
        <h3 class="stat-value text-blue-600">{{ stats.totalCC }}</h3>
        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-1">Crédito Clientes</p>
      </div>
    </div>

    <div class="flex flex-col md:flex-row gap-4 mb-6">
      <div class="flex-1 relative">
        <input type="text" v-model="search" placeholder="Buscar producto o SKU..." 
          class="w-full pl-12 pr-4 py-4 bg-white border-2 border-slate-100 rounded-2xl outline-none focus:border-[#4a5d23] font-bold shadow-sm transition-all">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">🔍</div>
      </div>
      <button v-show="!isTrashView" @click="openCreateModal" class="bg-[#4a5d23] text-white px-8 py-4 rounded-2xl font-black uppercase text-xs shadow-lg hover:opacity-90 active:scale-95 transition">Nuevo Producto</button>
      <button @click="toggleTrash" class="bg-slate-800 text-white px-6 py-4 rounded-2xl font-black uppercase text-xs shadow-lg hover:bg-slate-900 active:scale-95 transition">
        {{ !isTrashView ? '📦 Ver Papelera' : '🔙 Volver al Inventario' }}
      </button>
    </div>

    <div class="bg-white rounded-[2.5rem] shadow-xl overflow-hidden border border-slate-100">
      <table class="w-full text-left">
        <thead class="bg-[#4a5d23] text-white text-[10px] uppercase tracking-[0.2em]">
          <tr>
            <th class="px-8 py-6 font-black italic">📦 DETALLE DEL PRODUCTO</th>
            <th class="px-6 py-6 text-center font-black">SKU</th>
            <th class="px-6 py-6 text-center font-black">Precio (Bs)</th>
            <th class="px-6 py-6 text-center font-black">Stock</th>
            <th class="px-8 py-6 text-right font-black">Acciones</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          
          <tr v-for="product in filteredProducts" :key="product.id" class="hover:bg-slate-50/50 transition-colors group">
            <td class="px-8 py-5 flex items-center gap-4">
              <img :src="product.foto_persona || 'https://via.placeholder.com/60'" class="h-12 w-12 rounded-2xl object-cover shadow-sm border-2 border-white transition group-hover:scale-110">
              <div>
                <p class="font-black text-[#4a5d23] uppercase text-sm leading-tight">{{ product.name }}</p>
                <span class="text-[10px] text-[#b48a2d] font-black italic uppercase tracking-tighter">{{ product.cc_value }} CC</span>
              </div>
            </td>
            <td class="px-6 py-5 text-center font-mono text-xs text-slate-400 font-bold">{{ product.sku }}</td>
            <td class="px-6 py-5 text-center font-black text-slate-800 text-lg">{{ product.price_bs }}</td>
            <td class="px-6 py-5 text-center">
              <span :class="(product.stock || 0) < 10 ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-600'" class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase shadow-sm">
                {{ product.stock || 0 }} U.
              </span>
            </td>
            <td class="px-8 py-5 text-right">
              <div class="flex justify-end gap-2" v-show="!isTrashView">
                <button type="button" @click="venderRapido(product)" class="action-btn bg-[#4a5d23]" title="Registrar Venta Directa">💰</button>
                <button type="button" @click="openEditModal(product)" class="action-btn bg-slate-100 text-slate-600" title="Editar Producto">✏️</button>
                <button type="button" @click="deleteProduct(product.id)" class="action-btn bg-red-50 text-red-500" title="Enviar a Papelera">🗑️</button>
              </div>
              <div class="flex justify-end" v-show="isTrashView">
                <button type="button" @click="restoreProduct(product.id)" class="bg-blue-600 text-white px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-wider shadow-md hover:bg-blue-700 transition">
                  🔄 Restaurar
                </button>
              </div>
            </td>
          </tr>

          <tr v-if="loading">
            <td colspan="5" class="px-8 py-10 text-center text-[#4a5d23] font-black uppercase tracking-widest text-xs animate-pulse">
              🔄 Sincronizando catálogo con el almacén central de Bolivia...
            </td>
          </tr>
          <tr v-if="!loading && filteredProducts.length === 0">
            <td colspan="5" class="px-8 py-10 text-center text-slate-400 font-bold uppercase tracking-widest text-xs">
              No se encontraron productos.
            </td>
          </tr>

        </tbody>
      </table>
    </div>

    <div v-if="showModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-[200] p-4">
      <div class="bg-white rounded-[2.5rem] shadow-2xl max-w-lg w-full overflow-hidden border border-slate-100 p-8">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-xl font-black uppercase tracking-tight text-[#4a5d23]">
            {{ form.id ? '✏️ Editar Producto' : '✨ Nuevo Producto' }}
          </h2>
          <button type="button" @click="showModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-black">✕</button>
        </div>

        <form @submit.prevent="saveProduct" class="space-y-4">
          <div>
            <label class="block text-[10px] font-black uppercase text-slate-400 tracking-wider mb-1">Nombre</label>
            <input type="text" v-model="form.name" required class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-100 rounded-xl font-bold text-slate-800 focus:border-[#4a5d23] outline-none">
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-[10px] font-black uppercase text-slate-400 tracking-wider mb-1">SKU</label>
              <input type="text" v-model="form.sku" required :disabled="!!form.id" class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-100 rounded-xl font-bold text-slate-800 focus:border-[#4a5d23] outline-none disabled:opacity-60">
            </div>
            <div>
              <label class="block text-[10px] font-black uppercase text-slate-400 tracking-wider mb-1">Puntos (CC)</label>
              <input type="number" step="0.001" v-model="form.cc_value" required class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-100 rounded-xl font-bold text-slate-800 focus:border-[#4a5d23] outline-none">
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-[10px] font-black uppercase text-slate-400 tracking-wider mb-1">Precio (Bs)</label>
              <input type="number" step="0.01" v-model="form.price_bs" required class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-100 rounded-xl font-bold text-slate-800 focus:border-[#4a5d23] outline-none">
            </div>
            <div>
              <label class="block text-[10px] font-black uppercase text-slate-400 tracking-wider mb-1">Stock</label>
              <input type="number" v-model="form.stock" required class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-100 rounded-xl font-bold text-slate-800 focus:border-[#4a5d23] outline-none">
            </div>
          </div>
          <div class="pt-4 flex justify-end gap-3">
            <button type="button" @click="showModal = false" class="px-6 py-3 border border-slate-200 text-slate-500 rounded-xl text-xs font-black uppercase hover:bg-slate-50">Cancelar</button>
            <button type="submit" class="px-8 py-3 bg-[#4a5d23] text-white rounded-xl text-xs font-black uppercase hover:opacity-90 shadow-md">Guardar</button>
          </div>
        </form>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

const isTrashView = ref(false)
const loading     = ref(true)
const search      = ref('')
const showModal   = ref(false)
const products    = ref([])

const stats = ref({ total_items: 0, low_stock: 0, total_value: 0, totalCC: 0 })

const form = ref({ id: null, sku: '', name: '', cc_value: 0, price_bs: 0, stock: 0 })

const getAuthHeaders = () => {
  let token = localStorage.getItem('auth_token')
  if (token && token.startsWith('"') && token.endsWith('"')) { token = token.slice(1, -1) }
  return {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'Authorization': `Bearer ${token}`
  }
}

const loadProductsFromBackend = async () => {
  try {
    loading.value = true
    const endpoint = isTrashView.value ? 'http://localhost:8000/api/products/trash' : 'http://localhost:8000/api/products'
    const response = await fetch(endpoint, { method: 'GET', headers: getAuthHeaders() })
    if (!response.ok) throw new Error('Error API')
    
    const data = await response.json()
    products.value = Array.isArray(data) ? data : (data.data || [])

    if (!isTrashView.value) {
      stats.value = {
        total_items: products.value.length,
        low_stock: products.value.filter(p => (p.stock || 0) < 10).length,
        total_value: products.value.reduce((sum, p) => sum + parseFloat(p.price_bs || 0), 0).toLocaleString('es-BO', { minimumFractionDigits: 2 }),
        totalCC: products.value.reduce((sum, p) => sum + parseFloat(p.cc_value || 0), 0).toFixed(3)
      }
    }
  } catch (error) {
    console.error(error)
  } finally {
    loading.value = false
  }
}

const filteredProducts = computed(() => {
  if (!search.value) return products.value
  const q = search.value.toLowerCase()
  return products.value.filter(p => p.name.toLowerCase().includes(q) || p.sku.toLowerCase().includes(q))
})

const toggleTrash = () => {
  isTrashView.value = !isTrashView.value
  loadProductsFromBackend()
}

// 🔥 FUNCIONES DE LOS BOTONES
const openCreateModal = () => {
  console.log('Abriendo modal para CREAR') // LOG PARA VERIFICAR QUE FUNCIONA
  form.value = { id: null, sku: '', name: '', cc_value: 0, price_bs: 0, stock: 0 }
  showModal.value = true
}

const openEditModal = (product) => {
  console.log('Abriendo modal para EDITAR:', product.name) // LOG PARA VERIFICAR QUE FUNCIONA
  form.value = { ...product }
  showModal.value = true
}

const venderRapido = (product) => {
  console.log('Click en venta rápida')
  alert(`🛒 Registrando venta de: ${product.name}`)
}

const deleteProduct = async (id) => {
  if (!confirm('¿Seguro de enviar a la papelera?')) return
  try {
    await fetch(`http://localhost:8000/api/products/${id}`, { method: 'DELETE', headers: getAuthHeaders() })
    loadProductsFromBackend()
  } catch (error) { alert(error.message) }
}

const restoreProduct = async (id) => {
  try {
    await fetch(`http://localhost:8000/api/products/${id}/restore`, { method: 'POST', headers: getAuthHeaders() })
    loadProductsFromBackend()
  } catch (error) { alert(error.message) }
}

const saveProduct = async () => {
  try {
    const isEdit = !!form.value.id
    const url = isEdit ? `http://localhost:8000/api/products/${form.value.id}` : 'http://localhost:8000/api/products'
    await fetch(url, { method: isEdit ? 'PUT' : 'POST', headers: getAuthHeaders(), body: JSON.stringify(form.value) })
    showModal.value = false
    loadProductsFromBackend()
    alert('Guardado con éxito')
  } catch (error) { alert(error.message) }
}

onMounted(() => { loadProductsFromBackend() })
</script>

<style scoped>
@reference "tailwindcss"; 
.stat-card { @apply bg-white p-7 rounded-[2.5rem] shadow-sm border border-slate-100 flex flex-col; }
.stat-label { @apply text-[10px] font-black uppercase tracking-widest mb-1; }
.stat-value { @apply text-3xl font-black text-slate-800 tracking-tighter; }
.action-btn { @apply p-2.5 rounded-xl shadow-md cursor-pointer hover:scale-110 active:scale-95 transition; }
</style>