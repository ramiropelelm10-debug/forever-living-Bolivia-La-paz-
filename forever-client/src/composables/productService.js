import api from '../api.js';

export const ProductoService = {
    // En Laravel usaste apiResource('products'), por eso es /products
    fetch: (q = '', trash = false) => {
        const url = trash ? '/products/trash' : '/products';
        return api.get(url, { params: { search: q } });
    },
    save: (data) => data.id ? api.put(`/products/${data.id}`, data) : api.post('/products', data),
    delete: (id) => api.delete(`/products/${id}`),
    restore: (id) => api.post(`/products/${id}/restore`),
};

export const VentasService = {
    // Laravel: apiResource('sales')
    fetch: () => api.get('/sales'),
    sell: (data) => api.post('/sales', data),
    
    // 🔥 FUNCIÓN PARA PDF (Normalizada para Laravel)
    descargarPdf: (id) => api.get(`/sales/${id}/pdf`, { responseType: 'blob' }),
    
    // 🔥 NUEVA FUNCIÓN PARA EXCEL (Pide el archivo binario al servidor)
    exportarExcel: () => api.get('/sales/export/excel', { responseType: 'blob' })
};

export const UsuariosService = {
    // Laravel: Route::get('/clientes')
    fetchClients: () => api.get('/clientes'),
    saveClient: (data) => api.post('/clientes', data),
    
    // Gestión Administrativa (Requests)
    fetchRequests: () => api.get('/admin/requests'),
    approve: (id) => api.post(`/admin/users/${id}/respond`, { status: 'aprobado' }),
    reject: (id) => api.post(`/admin/users/${id}/respond`, { status: 'rechazado' }),
    
    // Gestión de Usuarios General
    fetchAll: () => api.get('/admin/users'),
    toggleStatus: (id) => api.post(`/admin/users/${id}/toggle-status`),
    promoteFBO: (id) => api.post(`/admin/users/${id}/promote`),
    
    // Gestión de Perfiles FBO
    fetchFbos: () => api.get('/fbos'),
    saveFbo: (data) => api.post('/fbos', data),
};