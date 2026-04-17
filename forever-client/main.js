import Alpine from 'alpinejs';
import axios from 'axios';
import Swal from 'sweetalert2';
import * as XLSX from 'xlsx';
// --- IMPORTAMOS LAS FUNCIONES BIOMÉTRICAS ---
import * as api from './src/api.js';

// --- CONFIGURACIÓN GLOBAL DE AXIOS ---
// IMPORTANTE: Cambiado a localhost para compatibilidad total con WebAuthn/FaceID
const API_URL = 'http://localhost:8000/api'; 
axios.defaults.baseURL = API_URL;

// Interceptor profesional: Envía el token en CADA petición automáticamente
axios.interceptors.request.use(config => {
    const token = localStorage.getItem('auth_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

window.axios = axios;
// Hacemos que la API sea accesible globalmente para Alpine
window.api = api; 

document.addEventListener('alpine:init', () => {
    Alpine.data('productApp', () => ({
        // --- VARIABLES DE ESTADO ---
        isLoggedIn: false,
        isLoading: false,
        requiresOTP: false, 
        view: 'catalog', 
        showModal: false,
        editMode: false,
        search: '',
        loginData: { email: '', password: '', code: '' }, 
        productData: { id: null, name: '', sku: '', stock: 0, price_bs: 0, cc_value: 0, foto_persona: '' },
        
        // --- VARIABLES PARA FBO (DISTRIBUIDORES) ---
        fboData: { fbo_id: '', name: '', discount_rate: 0 },
        fbos: [], 

        products: [],
        trashProducts: [],
        sales: [], 
        stats: { total_items: 0, low_stock: 0, total_value: 0 },
        totalCC: 0,

        // --- INICIALIZACIÓN ---
        async init() {
            axios.defaults.headers.common['Accept'] = 'application/json';
            const token = localStorage.getItem('auth_token');
            if (token && token !== 'undefined') {
                this.isLoggedIn = true;
                await Promise.all([
                    this.fetchProducts(),
                    this.fetchFbos(),
                    this.fetchSales()
                ]);
            } else {
                localStorage.removeItem('auth_token');
            }
            this.iniciarCamara();
        },

        // --- FUNCIÓN MEJORADA: REGISTRO BIOMÉTRICO ---
        async registerBiometrics() {
            try {
                // 1. Validamos que el usuario esté logueado y tengamos su email
                const userEmail = localStorage.getItem('user_email');
                if (!userEmail) {
                    throw new Error("Sesión incompleta. Por favor, re-inicia sesión para vincular tu Face ID.");
                }

                this.isLoading = true;

                // 2. Llamamos a la API (que ahora ya envía el email internamente)
                await window.api.registerBiometrics();

                Swal.fire({
                    title: '¡Dispositivo Vinculado!',
                    text: 'Tu Face ID / Huella se ha registrado con éxito en el sistema de Forever Living Bolivia.',
                    icon: 'success',
                    confirmButtonColor: '#eab308' // Amarillo Forever
                });

            } catch (err) {
                console.error("Error en Biometría:", err);
                
                // Mensaje inteligente según el tipo de error
                let mensaje = 'No se pudo registrar la huella.';
                if (window.location.hostname !== 'localhost') {
                    mensaje = 'WebAuthn requiere HTTPS o localhost. Estás en: ' + window.location.hostname;
                }

                Swal.fire({
                    title: 'Error de Seguridad',
                    text: mensaje,
                    icon: 'error',
                    confirmButtonColor: '#d33'
                });
            } finally {
                this.isLoading = false;
            }
        },

        // --- AUTENTICACIÓN ---
        async login() {
            this.isLoading = true;
            try {
                const res = await axios.post(`/login`, {
                    email: this.loginData.email,
                    password: this.loginData.password
                });
                
                if (res.data.requires_otp) {
                    this.requiresOTP = true;
                    // Guardamos el email temporalmente para la verificación OTP y posterior biometría
                    localStorage.setItem('user_email', this.loginData.email);
                    Swal.fire('Código Requerido', 'Introduce el código de verificación enviado', 'info');
                    return;
                }

                const token = res.data.token || res.data.access_token;
                
                if (token) {
                    localStorage.setItem('auth_token', token);
                    // IMPORTANTE: Guardamos el email para que registerBiometrics() lo encuentre
                    localStorage.setItem('user_email', res.data.user.email); 
                    this.isLoggedIn = true;
                    await this.fetchProducts();
                    await this.fetchFbos();
                    Swal.fire('¡Bienvenido!', 'Sesión iniciada correctamente', 'success');
                }

            } catch (e) { 
                console.error("Error de login:", e);
                Swal.fire('Error', 'Credenciales inválidas o error de servidor', 'error'); 
            } finally {
                this.isLoading = false;
            }
        },

        // --- VERIFICACIÓN DE OTP ---
        async verifyOtp() {
            this.isLoading = true;
            try {
                const res = await axios.post('/verify-otp', {
                    email: this.loginData.email,
                    code: this.loginData.code
                });

                const token = res.data.token;
                if (token) {
                    localStorage.setItem('auth_token', token);
                    // Aseguramos que el email persista tras el OTP
                    localStorage.setItem('user_email', res.data.user.email);
                    this.isLoggedIn = true;
                    this.requiresOTP = false;
                    
                    await Promise.all([
                        this.fetchProducts(),
                        this.fetchFbos(),
                        this.fetchSales()
                    ]);
                    
                    Swal.fire('¡Acceso Concedido!', 'Bienvenido al sistema', 'success');
                }
            } catch (e) {
                Swal.fire('Error', 'Código incorrecto o expirado', 'error');
            } finally {
                this.isLoading = false;
            }
        },

        logout() { 
            localStorage.clear(); 
            location.reload(); 
        },

        // --- GESTIÓN DE PRODUCTOS ---
        async fetchProducts() {
            this.isLoading = true;
            const endpoint = this.view === 'catalog' ? '/products' : '/products/trash';
            try {
                const res = await axios.get(endpoint, { params: { search: this.search } });
                if (this.view === 'catalog') {
                    this.products = res.data;
                    this.updateStats();
                } else {
                    this.trashProducts = res.data;
                }
            } catch (e) {
                console.error("Error al cargar productos", e);
                if(e.response && e.response.status === 401) this.logout();
            } finally { 
                this.isLoading = false; 
            }
        },

        async saveProduct() {
            this.isLoading = true;
            try {
                const data = {
                    name: this.productData.name,
                    sku: this.productData.sku,
                    stock: parseInt(this.productData.stock),
                    price_bs: parseFloat(this.productData.price_bs),
                    cc_value: parseFloat(this.productData.cc_value),
                    foto_persona: this.productData.foto_persona
                };

                if (this.editMode) {
                    await axios.post(`/products/${this.productData.id}`, { ...data, _method: 'PUT' });
                } else {
                    await axios.post(`/products`, data);
                }
                
                this.showModal = false;
                await this.fetchProducts();
                this.resetProductForm();
                Swal.fire('Éxito', 'Guardado correctamente', 'success');
            } catch (e) { 
                Swal.fire('Error', 'No se pudo guardar el producto', 'error'); 
            } finally {
                this.isLoading = false;
            }
        },

        async deleteProduct(id) {
            const res = await Swal.fire({ 
                title: '¿Seguro?', 
                text: "Se moverá a la papelera", 
                icon: 'warning', 
                showCancelButton: true 
            });

            if (res.isConfirmed) {
                try {
                    await axios.delete(`/products/${id}`);
                    await this.fetchProducts();
                    Swal.fire('Eliminado', 'Producto movido a la papelera', 'success');
                } catch (e) {
                    Swal.fire('Error', 'No se pudo eliminar', 'error');
                }
            }
        },

        // --- GESTIÓN DE FBOs ---
        async fetchFbos() {
            try {
                const res = await axios.get(`/fbos`);
                this.fbos = res.data;
            } catch (e) {
                console.error("Error al cargar FBOs");
            }
        },

        async saveFbo() {
            if(!this.fboData.fbo_id || !this.fboData.name) {
                return Swal.fire('Atención', 'El ID y Nombre son obligatorios', 'warning');
            }
            this.isLoading = true;
            try {
                await axios.post(`/fbos`, this.fboData);
                this.fboData = { fbo_id: '', name: '', discount_rate: 0 }; 
                Swal.fire('Éxito', 'Distribuidor registrado en Bolivia', 'success');
                await this.fetchFbos();
            } catch (e) {
                Swal.fire('Error', 'No se pudo registrar el FBO', 'error');
            } finally {
                this.isLoading = false;
            }
        },

        // --- VENTAS Y REPORTES ---
        async fetchSales() {
            try {
                const res = await axios.get(`/sales`);
                this.sales = res.data;
            } catch (e) {
                console.error("Error al cargar el historial");
            }
        },

        async sellProduct(product) {
            const { value: cantidad } = await Swal.fire({
                title: `Vender ${product.name}`,
                text: `Stock disponible: ${product.stock}`,
                input: 'number',
                inputAttributes: { min: 1, step: 1 },
                showCancelButton: true,
                confirmButtonText: 'Confirmar Venta 💰',
                confirmButtonColor: '#10b981'
            });

            if (cantidad && cantidad > 0) {
                if (cantidad > product.stock) {
                    return Swal.fire('Error', 'No hay suficiente stock', 'error');
                }

                this.isLoading = true;
                try {
                    const res = await axios.post(`/sales`, {
                        product_id: product.id,
                        cantidad: parseInt(cantidad)
                    });

                    await this.fetchProducts(); 
                    
                    Swal.fire({
                        title: 'Venta Exitosa',
                        text: "¿Deseas imprimir el recibo de venta?",
                        icon: 'success',
                        showCancelButton: true,
                        confirmButtonText: 'Imprimir 🖨️',
                        cancelButtonText: 'Cerrar',
                        confirmButtonColor: '#10b981'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.printReceipt(res.data);
                        }
                    });

                } catch (e) {
                    Swal.fire('Error', 'No se pudo procesar la venta', 'error');
                } finally {
                    this.isLoading = false;
                }
            }
        },

        printReceipt(sale) {
            const ventanita = window.open('', '_blank', 'width=400,height=600');
            ventanita.document.write(`
                <html>
                <head>
                    <title>Recibo Forever</title>
                    <style>
                        body { font-family: 'Courier New', sans-serif; width: 280px; padding: 10px; }
                        .center { text-align: center; }
                        .bold { font-weight: bold; }
                        .hr { border-bottom: 1px dashed black; margin: 10px 0; }
                    </style>
                </head>
                <body>
                    <div class="center bold" style="font-size: 18px;">FOREVER LIVING</div>
                    <div class="center">La Paz - Bolivia</div>
                    <div class="hr"></div>
                    <div class="center bold">RECIBO DE VENTA</div>
                    <div class="center">${sale.nro_factura}</div>
                    <div class="hr"></div>
                    <div class="center italic">¡Gracias por su compra!</div>
                    <script>setTimeout(() => { window.print(); window.close(); }, 500);</script>
                </body>
                </html>
            `);
            ventanita.document.close();
        },

        // --- ESTADÍSTICAS Y EXCEL ---
        updateStats() {
            this.stats.total_items = this.products.length;
            this.stats.low_stock = this.products.filter(p => p.stock < 5).length;
            this.stats.total_value = this.products.reduce((acc, p) => acc + (p.price_bs * p.stock), 0).toFixed(2);
            this.totalCC = this.products.reduce((acc, p) => acc + (parseFloat(p.cc_value) * p.stock), 0).toFixed(3);
        },

        exportToExcel() {
            let data = [];
            let filename = "";

            if (this.view === 'sales') {
                filename = "Reporte_Ventas_Forever_Bolivia.xlsx";
                data = this.sales.map(s => ({
                    'Nro Factura': s.nro_factura,
                    'Fecha': new Date(s.created_at).toLocaleString(),
                    'Producto': s.product?.name || 'N/A',
                    'Total (Bs)': s.monto_total,
                    'Puntos CC': s.total_cc
                }));
            } else {
                filename = "Inventario_Forever_Bolivia.xlsx";
                const source = this.view === 'catalog' ? this.products : this.trashProducts;
                data = source.map(p => ({
                    'Nombre': p.name,
                    'SKU': p.sku,
                    'Stock': p.stock,
                    'Precio (Bs)': p.price_bs
                }));
            }

            const ws = XLSX.utils.json_to_sheet(data);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Datos");
            XLSX.writeFile(wb, filename);
            Swal.fire({ title: 'Excel Generado', icon: 'success', timer: 1500, showConfirmButton: false });
        },

        // --- HELPERS ---
        editProduct(product) { 
            this.productData = { ...product }; 
            this.editMode = true; 
            this.showModal = true; 
        },
        resetProductForm() { 
            this.productData = { id: null, name: '', sku: '', stock: 0, price_bs: 0, cc_value: 0, foto_persona: '' }; 
            this.editMode = false;
        },
        toggleTrash() { 
            this.view = (this.view === 'catalog') ? 'trash' : 'catalog'; 
            this.fetchProducts(); 
        },
        handleFileUpload(event) {
            const file = event.target.files[0];
            const reader = new FileReader();
            reader.onloadend = () => { 
                this.productData.foto_persona = reader.result; 
            };
            reader.readAsDataURL(file);
        },

        // --- LÓGICA DE CÁMARA ---
        async iniciarCamara() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                const video = document.getElementById('video');
                video.srcObject = stream;
            } catch (err) {
                Swal.fire('Error', 'No se pudo acceder a la cámara de la laptop', 'error');
            }
        },

        tomarFoto() {
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const context = canvas.getContext('2d');

            // Ajustamos el tamaño del canvas al del video
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            // "Dibujamos" el frame actual del video en el canvas
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Convertimos a Base64 y lo guardamos en nuestro objeto de datos
            const fotoBase64 = canvas.toDataURL('image/jpeg');
            this.productData.foto_persona = fotoBase64;

            Swal.fire({
                title: '¡Foto Capturada!',
                text: 'La imagen se ha preparado para la credencial',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            });
        }
    }));
});

Alpine.start();