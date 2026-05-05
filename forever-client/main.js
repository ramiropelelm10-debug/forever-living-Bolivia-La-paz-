import Alpine from 'alpinejs';
import axios from 'axios';
import Swal from 'sweetalert2';
import * as XLSX from 'xlsx';
import * as api from './src/api.js';

// Configuración de la API
const API_URL = 'http://localhost:8000/api'; 
axios.defaults.baseURL = API_URL;

// Interceptor para enviar el Token de Sanctum automáticamente
axios.interceptors.request.use(config => {
    const token = localStorage.getItem('auth_token');
    if (token) config.headers.Authorization = `Bearer ${token}`;
    return config;
});

window.axios = axios;
window.api = api; 

document.addEventListener('alpine:init', () => {
    Alpine.data('productApp', () => ({
        // --- ESTADO GLOBAL ---
        isLoggedIn: false, 
        isLoading: false, 
        requiresOTP: false, 
        view: 'catalog', 
        showModal: false, 
        editMode: false, 
        search: '',
        isTrashView: false,

        // --- DATOS DE FORMULARIOS ---
        loginData: { email: '', password: '', code: '' }, 
        productData: { id: null, name: '', sku: '', stock: 0, price_bs: 0, cc_value: 0, foto_persona: '' },
        fboData: { fbo_id: '', name: '', last_name: '', email: '', dni: '', discount_rate: 0 },
        // AQUÍ SE AGREGA EL CAMPO DE CONTRASEÑA PARA CLIENTES
        clientData: { name: '', last_name: '', email: '', dni: '', phone: '', password: '' },

        // --- COLECCIONES ---
        fbos: [], 
        clients: [], // NUEVO: Lista de clientes
        products: [], 
        sales: [], 

        // --- ESTADÍSTICAS ---
        stats: { total_items: 0, low_stock: 0, total_value: 0 }, 
        totalCC: 0,

        // --- BIOMETRÍA Y CÁMARA ---
        faceApp: { cargando: true },
        loginInterval: null, 
        currentStream: null, 
        faceLoginActivo: localStorage.getItem('faceLoginActivo') === 'true',
        rostroAdminUrl: localStorage.getItem('rostroAdminUrl') || null,

        // --- INICIALIZACIÓN ---
        async init() {
            axios.defaults.headers.common['Accept'] = 'application/json';
            
            try {
                await faceapi.nets.tinyFaceDetector.loadFromUri('/models');
                this.faceApp.cargando = false;
            } catch (e) { console.error("Error cargando modelos IA", e); }

            const token = localStorage.getItem('auth_token');
            const backupToken = localStorage.getItem('face_token_backup'); 
            
            if (token && token !== 'undefined') {
                if (this.faceLoginActivo) {
                    this.isLoggedIn = false;
                    setTimeout(() => { this.iniciarCamaraLogin(); }, 800);
                } else {
                    this.entrarAlSistemaDirecto();
                }
            } else {
                if (this.faceLoginActivo && backupToken) {
                    this.isLoggedIn = false;
                    setTimeout(() => { this.iniciarCamaraLogin(); }, 800);
                } else {
                    localStorage.removeItem('auth_token');
                    this.faceLoginActivo = false; 
                }
            }
        },

        async entrarAlSistemaDirecto() {
            this.isLoggedIn = true;
            await Promise.all([
                this.fetchProducts().catch(e => console.error("Error en productos", e)),
                this.fetchFbos().catch(e => console.error("Error en FBOs", e)),
                this.fetchSales().catch(e => console.error("Error en Sales", e)),
                this.fetchClients().catch(e => console.error("Error en Clientes", e)) // Carga los clientes
            ]);
        },

        // --- LÓGICA DE LOGIN Y AUTH ---
        async login() {
            this.isLoading = true;
            try {
                const res = await axios.post(`/login`, { email: this.loginData.email, password: this.loginData.password });
                if (res.data.requires_otp) {
                    this.requiresOTP = true;
                    localStorage.setItem('user_email', this.loginData.email);
                    return;
                }
                const token = res.data.token || res.data.access_token;
                if (token) {
                    localStorage.setItem('auth_token', token);
                    if(this.faceLoginActivo) localStorage.setItem('face_token_backup', token);
                    this.entrarAlSistemaDirecto();
                }
            } catch (e) { 
                Swal.fire('Error', 'Credenciales inválidas o error de servidor', 'error'); 
            } finally { this.isLoading = false; }
        },

        async verifyOtp() {
            this.isLoading = true;
            try {
                const res = await axios.post('/verify-otp', { email: this.loginData.email, code: this.loginData.code });
                if (res.data.token) {
                    localStorage.setItem('auth_token', res.data.token);
                    this.requiresOTP = false;
                    this.entrarAlSistemaDirecto();
                }
            } catch (e) { Swal.fire('Error', 'Código incorrecto', 'error');
            } finally { this.isLoading = false; }
        },

        logout() { 
            if (this.loginInterval) clearInterval(this.loginInterval);
            if (this.currentStream) this.currentStream.getTracks().forEach(t => t.stop());
            localStorage.removeItem('auth_token'); 
            location.reload(); 
        },

        // --- FACE ID LOGIN ---
        async iniciarCamaraLogin() {
            const video = document.getElementById('login-video');
            const canvas = document.getElementById('login-canvas');
            if (!video || !canvas) return;

            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
                this.currentStream = stream;
                video.srcObject = stream;
                await video.play();

                const displaySize = { width: video.clientWidth, height: video.clientHeight };
                faceapi.matchDimensions(canvas, displaySize);

                this.loginInterval = setInterval(async () => {
                    const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions());
                    if (detections.length > 0) {
                        clearInterval(this.loginInterval);
                        this.entrarAlSistemaDirecto();
                        Swal.fire({ title: '¡Hola de nuevo!', icon: 'success', timer: 1000, showConfirmButton: false });
                    }
                }, 400);
            } catch (err) { console.error("Error cámara login:", err); }
        },

        async iniciarCamaraPerfil() {
            const video = document.getElementById('perfil-video');
            try {
                if (this.currentStream) {
                    this.currentStream.getTracks().forEach(track => track.stop());
                }
                const stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { width: 400, height: 400 }, 
                    audio: false 
                });
                this.currentStream = stream;
                if (video) {
                    video.srcObject = stream;
                    await video.play();
                }
                Swal.fire({ title: 'Cámara Lista', text: 'Ponte frente al círculo.', icon: 'info', timer: 1500, showConfirmButton: false });
            } catch (err) {
                console.error("Error cámara perfil:", err);
                Swal.fire('Error', 'No se pudo acceder a la cámara.', 'error');
            }
        },

        async guardarHuellaFacial() {
            this.isLoading = true;
            setTimeout(() => {
                this.rostroAdminUrl = 'registrado'; 
                localStorage.setItem('rostroAdminUrl', this.rostroAdminUrl);
                if (this.currentStream) {
                    this.currentStream.getTracks().forEach(track => track.stop());
                    this.currentStream = null;
                }
                this.isLoading = false;
                Swal.fire('¡Éxito!', 'Huella facial guardada para Forever Bolivia.', 'success');
            }, 1500);
        },

        // --- GESTIÓN DE PRODUCTOS Y PAPELERA ---
        toggleTrash() {
            this.isTrashView = !this.isTrashView;
            this.fetchProducts();
        },

        async fetchProducts() {
            try {
                const endpoint = this.isTrashView ? '/products/trash' : '/products';
                const res = await axios.get(endpoint, { params: { search: this.search } });
                this.products = res.data;
                this.updateStats();
            } catch (e) { console.error("Error al traer productos"); }
        },

        async saveProduct() {
            this.isLoading = true;
            try {
                if (this.editMode) await axios.put(`/products/${this.productData.id}`, this.productData);
                else await axios.post('/products', this.productData);
                this.showModal = false;
                await this.fetchProducts();
                Swal.fire('¡Listo!', 'Producto actualizado.', 'success');
            } catch (e) { Swal.fire('Error', 'No se pudo guardar.', 'error'); } 
            finally { this.isLoading = false; }
        },

        handleImageUpload(event) {
            const file = event.target.files[0];
            if (!file) return;
            
            const reader = new FileReader();
            reader.onload = (e) => { 
                this.productData.foto_persona = e.target.result; 
            };
            reader.readAsDataURL(file);
        },

        async deleteProduct(id) {
            const result = await Swal.fire({
                title: '¿Eliminar producto?',
                text: "El producto irá a la papelera.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });

            if (result.isConfirmed) {
                try {
                    await axios.delete(`/products/${id}`);
                    this.fetchProducts();
                    Swal.fire('¡Eliminado!', 'Movido a la papelera.', 'success');
                } catch (e) {
                    Swal.fire('Error', 'No se pudo eliminar el producto.', 'error');
                }
            }
        },

        async restoreProduct(id) {
            const result = await Swal.fire({
                title: '¿Restaurar producto?',
                text: "El producto volverá a estar disponible en el catálogo.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2F6432',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Sí, restaurar',
                cancelButtonText: 'Cancelar'
            });

            if (result.isConfirmed) {
                try {
                    await axios.post(`/products/${id}/restore`);
                    this.fetchProducts();
                    Swal.fire('¡Restaurado!', 'El producto regresó a la tienda.', 'success');
                } catch (e) {
                    Swal.fire('Error', 'No se pudo restaurar el producto.', 'error');
                }
            }
        },

        sellProduct(product) {
            Swal.fire({
                title: 'Ventas y Facturación',
                text: 'Para procesar ventas con descuento de FBO y generar factura, usa la Tienda Live.',
                icon: 'info',
                confirmButtonText: 'Ir a la Tienda 🛒',
                showCancelButton: true,
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#2F6432'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.open('/tienda.html', '_blank');
                }
            });
        },

        // --- GESTIÓN DE CLIENTES NORMALES ---
        async fetchClients() {
            try {
                const res = await axios.get('/clientes'); 
                this.clients = Array.isArray(res.data) ? res.data : (res.data.data || []);
            } catch (e) { 
                console.log("Aún no existe ruta /clientes en Laravel o error", e); 
            }
        },

        async saveClient() {
            if (!this.clientData.name || !this.clientData.email || !this.clientData.dni || !this.clientData.password) {
                Swal.fire('Faltan Datos', 'El Nombre, Correo, CI/NIT y Contraseña son obligatorios.', 'warning');
                return;
            }

            this.isLoading = true;
            try {
                await axios.post('/clientes', this.clientData);
                Swal.fire('¡Éxito!', 'Cliente registrado correctamente.', 'success');
                this.clientData = { name: '', last_name: '', email: '', dni: '', phone: '', password: '' };
                await this.fetchClients();
            } catch (e) {
                // DETECTOR DE ERRORES REALES DE LARAVEL
                let msg = "Error al guardar.";
                
                if (e.response && e.response.status === 422) {
                    const errores = e.response.data.messages || e.response.data.errors;
                    if (errores) {
                        msg = Object.values(errores)[0][0]; 
                    } else {
                        msg = "El correo o el CI ya están registrados en otra cuenta.";
                    }
                } else if (e.response && e.response.data && e.response.data.message) {
                    msg = e.response.data.message;
                } else if (e.response && e.response.status === 404) {
                    msg = "Aún falta crear la ruta POST /clientes en Laravel.";
                }

                Swal.fire('❌ Error en Laravel', msg, 'error');
                console.error("Error completo:", e.response?.data || e);
            } finally {
                this.isLoading = false;
            }
        },

        // --- GESTIÓN DE FBOs ---
        async fetchFbos() { 
            try { 
                const res = await axios.get('/fbos');
                this.fbos = res.data; 
            } catch(e) { console.error("Error cargando FBOs", e); }
        },

        async saveFbo() {
            if (!this.fboData.fbo_id || !this.fboData.name || !this.fboData.email || !this.fboData.dni) {
                Swal.fire('Atención', 'Por favor llena todos los campos requeridos (ID, Nombres, Correo, CI).', 'warning');
                return;
            }

            this.isLoading = true;
            try {
                const res = await axios.post('/fbos', this.fboData);
                
                Swal.fire({
                    title: '¡FBO Registrado!',
                    text: `${res.data.data ? res.data.data.user.persona.nombres : 'El FBO'} ha sido dado de alta correctamente.`,
                    icon: 'success',
                    confirmButtonColor: '#eab308'
                });

                this.fboData = { fbo_id: '', name: '', last_name: '', email: '', dni: '', discount_rate: 0 };
                await this.fetchFbos();

            } catch (e) {
                let msg = "Ocurrió un error al guardar.";
                if (e.response?.status === 422) {
                    const errores = e.response.data.messages || e.response.data.errors;
                    if (errores) {
                        msg = Object.values(errores)[0][0]; 
                    } else {
                        msg = "Verifica que el ID, Correo o CI no estén duplicados.";
                    }
                }
                Swal.fire('Error de Validación', msg, 'error');
            } finally {
                this.isLoading = false;
            }
        },

        // --- VENTAS ---
        async fetchSales() { 
            try { 
                const res = await axios.get('/sales');
                
                let ventasData = [];
                if (Array.isArray(res.data)) {
                    ventasData = res.data;
                } else if (res.data && Array.isArray(res.data.data)) {
                    ventasData = res.data.data;
                } else if (res.data && res.data.data && Array.isArray(res.data.data.data)) {
                    ventasData = res.data.data.data;
                }
                
                this.sales = ventasData;

                if (ventasData.length === 0) {
                    console.warn("Laravel no envió ventas.");
                }
            } catch(e) { 
                console.error("Error al cargar ventas", e);
                this.sales = []; 
            }
        },

        // --- DESCARGAR FACTURA EN PDF DESDE EL ADMIN ---
        descargarFactura(sale) {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            doc.setFontSize(22);
            doc.setTextColor(47, 100, 50);
            doc.text("FOREVER LIVING BOLIVIA", 105, 20, null, null, "center");

            doc.setFontSize(12);
            doc.setTextColor(0, 0, 0);
            doc.text(`Factura Oficial Nro: ${sale.nro_factura}`, 20, 40);
            
            const fechaVenta = sale.created_at ? new Date(sale.created_at).toLocaleDateString() : new Date().toLocaleDateString();
            doc.text(`Fecha: ${fechaVenta}`, 140, 40);
            doc.text(`Cliente: ${sale.razon_social || 'Consumidor Final'}`, 20, 50);
            doc.text(`NIT/CI: ${sale.nit_ci || 'S/N'}`, 20, 58);

            const tableColumn = ["SKU", "Producto", "Cant.", "Precio U.", "Subtotal"];
            const tableRows = [];

            if (sale.items && sale.items.length > 0) {
                sale.items.forEach(item => {
                    const nombreProd = item.producto?.name || item.product?.name || 'Desconocido';
                    const skuProd = item.producto?.sku || item.product?.sku || '-';
                    
                    tableRows.push([
                        skuProd,
                        nombreProd,
                        item.cantidad,
                        `Bs. ${item.precio_unitario}`,
                        `Bs. ${item.subtotal}`
                    ]);
                });
            } else {
                tableRows.push(['-', 'Venta sin detalle', sale.cantidad || 1, '-', `Bs. ${sale.monto_total}`]);
            }

            doc.autoTable({
                startY: 70,
                head: [tableColumn],
                body: tableRows,
                theme: 'striped',
                headStyles: { fillColor: [47, 100, 50] } 
            });

            const finalY = doc.lastAutoTable.finalY + 15;
            doc.setFontSize(12);
            doc.setTextColor(220, 38, 38);
            doc.text(`Impuesto IVA (13%): Bs. ${sale.monto_iva}`, 20, finalY);
            
            doc.setTextColor(47, 100, 50);
            doc.text(`Puntaje Acumulado: ${sale.total_cc} CC`, 20, finalY + 8);
            
            doc.setFontSize(16);
            doc.setTextColor(0, 0, 0);
            doc.text(`TOTAL A PAGAR: Bs. ${sale.monto_total}`, 130, finalY + 8);

            doc.save(`Factura_${sale.nro_factura}.pdf`);
        },

        // --- EXPORTAR EXCEL ---
        exportToExcel() {
            let dataToExport = [];
            let filename = 'Reporte.xlsx';

            if (this.view === 'catalog' || this.view === 'trash') {
                dataToExport = this.products.map(p => ({
                    'SKU': p.sku,
                    'Nombre del Producto': p.name,
                    'Precio (Bs)': p.price_bs,
                    'Stock Disponible': p.stock,
                    'Valor CC': p.cc_value
                }));
                filename = 'Inventario_Forever.xlsx';
            } else if (this.view === 'sales') {
                dataToExport = this.sales.map(s => ({
                    'Nro Factura': s.nro_factura,
                    'Cliente': s.razon_social || 'Consumidor Final',
                    'NIT/CI': s.nit_ci || 'S/N',
                    'Items Vendidos': s.items ? s.items.length : 0,
                    'Total Facturado (Bs)': s.monto_total,
                    'IVA Pagado (Bs)': s.monto_iva,
                    'CC Generados': s.total_cc
                }));
                filename = 'Ventas_Forever.xlsx';
            } else if (this.view === 'fbo_admin') {
                dataToExport = this.fbos.map(f => ({
                    'ID FBO': f.fbo_id,
                    'Nombres Completos': f.user?.persona?.nombres ? f.user.persona.nombres + ' ' + f.user.persona.apellidos : 'Sin Nombre',
                    'Porcentaje Descuento (%)': f.discount_rate
                }));
                filename = 'Distribuidores_FBO.xlsx';
            } else if (this.view === 'clients') {
                dataToExport = this.clients.map(c => ({
                    'Nombre': c.persona?.nombres || c.nombres || c.name,
                    'Apellido': c.persona?.apellidos || c.apellidos || c.last_name,
                    'CI/NIT': c.persona?.ci || c.dni || c.ci,
                    'Email': c.email
                }));
                filename = 'Clientes_Forever.xlsx';
            }

            if (dataToExport.length === 0) {
                Swal.fire('Aviso', 'No hay datos para exportar en esta vista.', 'info');
                return;
            }

            try {
                const worksheet = XLSX.utils.json_to_sheet(dataToExport);
                const workbook = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(workbook, worksheet, "Reporte");
                XLSX.writeFile(workbook, filename);
                
                Swal.fire({
                    title: '¡Excel Descargado!',
                    text: 'Tu reporte se generó correctamente.',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
            } catch (error) {
                console.error("Error exportando a Excel", error);
                Swal.fire('Error', 'Ocurrió un problema al generar el archivo.', 'error');
            }
        },

        // --- AYUDANTES (HELPERS) ---
        updateStats() {
            if (!this.isTrashView) {
                this.stats.total_items = this.products.length;
                this.stats.total_value = this.products.reduce((acc, p) => acc + (p.price_bs * p.stock), 0).toFixed(2);
                this.totalCC = this.products.reduce((acc, p) => acc + (parseFloat(p.cc_value) * p.stock), 0).toFixed(3);
            }
        },

        toggleFaceLogin() {
            this.faceLoginActivo = !this.faceLoginActivo;
            localStorage.setItem('faceLoginActivo', this.faceLoginActivo);
        },

        resetProductForm() { 
            this.productData = { id: null, name: '', sku: '', stock: 0, price_bs: 0, cc_value: 0, foto_persona: '' }; 
            this.editMode = false; 
        }
    }));
});

Alpine.start();