import Alpine from 'alpinejs';
import axios from 'axios';
import Swal from 'sweetalert2';
import * as XLSX from 'xlsx';
import * as api from './src/api.js';

const API_URL = 'http://localhost:8000/api'; 
axios.defaults.baseURL = API_URL;

axios.interceptors.request.use(config => {
    const token = localStorage.getItem('auth_token');
    if (token) config.headers.Authorization = `Bearer ${token}`;
    return config;
});

window.axios = axios;
window.api = api; 

document.addEventListener('alpine:init', () => {
    Alpine.data('productApp', () => ({
        isLoggedIn: false, isLoading: false, requiresOTP: false, 
        view: 'catalog', showModal: false, editMode: false, search: '',
        loginData: { email: '', password: '', code: '' }, 
        productData: { id: null, name: '', sku: '', stock: 0, price_bs: 0, cc_value: 0, foto_persona: '' },
        fboData: { fbo_id: '', name: '', discount_rate: 0 },
        fbos: [], products: [], trashProducts: [], sales: [], 
        stats: { total_items: 0, low_stock: 0, total_value: 0 }, totalCC: 0,
        faceApp: { cargando: true }, // Simplificado: ya no necesitamos rostroDetectado para vigilancia
        loginInterval: null, 
        currentStream: null, 
        faceLoginActivo: localStorage.getItem('faceLoginActivo') === 'true',
        rostroAdminUrl: localStorage.getItem('rostroAdminUrl') || null,

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
            // Cargamos datos, pero YA NO encendemos la cámara de vigilancia
            await Promise.all([
                this.fetchProducts().catch(e => console.error("Error en productos", e)),
                this.fetchFbos().catch(e => console.error("Error en FBOs", e)),
                this.fetchSales().catch(e => console.error("Error en Sales", e))
            ]);
        },

        // --- CÁMARA DE LOGIN (SE MANTIENE) ---
        async iniciarCamaraLogin() {
            const video = document.getElementById('login-video');
            const canvas = document.getElementById('login-canvas');
            
            if (!video || !canvas) {
                setTimeout(() => this.iniciarCamaraLogin(), 500);
                return;
            }

            if (this.currentStream) {
                this.currentStream.getTracks().forEach(track => track.stop());
            }

            try {
                const stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { facingMode: "user", width: { ideal: 640 }, height: { ideal: 480 } }, 
                    audio: false 
                });
                
                this.currentStream = stream;
                video.srcObject = stream;
                video.muted = true;
                video.setAttribute('playsinline', '');
                
                await video.play().catch(e => console.warn("Play interrumpido"));

                const displaySize = { width: video.clientWidth, height: video.clientHeight };
                faceapi.matchDimensions(canvas, displaySize);

                if (this.loginInterval) clearInterval(this.loginInterval);
                this.loginInterval = setInterval(async () => {
                    if(video.paused || video.ended) return;
                    
                    const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions());
                    
                    if (detections.length > 0) {
                        clearInterval(this.loginInterval); 
                        stream.getTracks().forEach(track => track.stop());
                        this.currentStream = null;
                        
                        const backup = localStorage.getItem('face_token_backup');
                        if(backup) localStorage.setItem('auth_token', backup);
                        
                        Swal.fire({
                            title: '¡Rostro Identificado!', 
                            text: 'Bienvenido a Forever Living', 
                            icon: 'success', 
                            timer: 1500, 
                            showConfirmButton: false
                        });
                        
                        setTimeout(() => { this.entrarAlSistemaDirecto(); }, 1000);
                    }
                    
                    const res = faceapi.resizeResults(detections, displaySize);
                    canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
                    faceapi.draw.drawDetections(canvas, res);
                }, 400);

            } catch (err) { console.error("Error de cámara login:", err); }
        },

        lockScreen() { 
            if(!this.faceLoginActivo) {
                Swal.fire('Atención', 'Activa la Seguridad Facial en el Perfil Admin primero.', 'warning');
                return;
            }
            location.reload(); 
        },
        
        logout() { 
            if (this.loginInterval) clearInterval(this.loginInterval);
            if (this.currentStream) this.currentStream.getTracks().forEach(t => t.stop());
            
            localStorage.removeItem('auth_token'); 
            location.reload(); 
        },

        // --- GESTIÓN DE PRODUCTOS ---
        handleImageUpload(event) {
            const file = event.target.files[0];
            const reader = new FileReader();
            reader.onload = (e) => { this.productData.foto_persona = e.target.result; };
            if (file) reader.readAsDataURL(file);
        },

        async saveProduct() {
            this.isLoading = true;
            try {
                const payload = {
                    ...this.productData,
                    stock: parseInt(this.productData.stock) || 0,
                    price_bs: parseFloat(this.productData.price_bs) || 0,
                    cc_value: parseFloat(this.productData.cc_value) || 0
                };
                if (this.editMode) await axios.post(`/products/${this.productData.id}`, { ...payload, _method: 'PUT' });
                else await axios.post('/products', payload);
                this.showModal = false;
                await this.fetchProducts();
                this.resetProductForm();
                Swal.fire('¡Éxito!', 'Producto guardado.', 'success');
            } catch (e) { Swal.fire('Error', 'No se pudo guardar.', 'error'); } 
            finally { this.isLoading = false; }
        },

        async fetchProducts() {
            try {
                const res = await axios.get('/products', { params: { search: this.search } });
                this.products = res.data;
                this.updateStats();
            } catch (e) { if(e.response?.status === 401) this.logout(); }
        },

        // --- AUTENTICACIÓN MANUAL ---
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
                    localStorage.setItem('user_email', res.data.user.email); 
                    if(this.faceLoginActivo) localStorage.setItem('face_token_backup', token);
                    this.entrarAlSistemaDirecto();
                }
            } catch (e) { Swal.fire('Error', 'Credenciales inválidas', 'error'); 
            } finally { this.isLoading = false; }
        },

        async verifyOtp() {
            this.isLoading = true;
            try {
                const res = await axios.post('/verify-otp', { email: this.loginData.email, code: this.loginData.code });
                if (res.data.token) {
                    localStorage.setItem('auth_token', res.data.token);
                    localStorage.setItem('user_email', res.data.user.email);
                    this.requiresOTP = false;
                    if(this.faceLoginActivo) localStorage.setItem('face_token_backup', res.data.token);
                    this.entrarAlSistemaDirecto();
                }
            } catch (e) { Swal.fire('Error', 'Código incorrecto', 'error');
            } finally { this.isLoading = false; }
        },

        toggleFaceLogin() {
            this.faceLoginActivo = !this.faceLoginActivo;
            localStorage.setItem('faceLoginActivo', this.faceLoginActivo);
            
            if(this.faceLoginActivo) {
                localStorage.setItem('face_token_backup', localStorage.getItem('auth_token'));
            } else {
                localStorage.removeItem('face_token_backup');
            }
        },

        resetProductForm() { this.productData = { id: null, name: '', sku: '', stock: 0, price_bs: 0, cc_value: 0, foto_persona: '' }; this.editMode = false; },
        editProduct(product) { this.productData = { ...product }; this.editMode = true; this.showModal = true; },
        async deleteProduct(id) {
            if ((await Swal.fire({ title: '¿Eliminar?', icon: 'warning', showCancelButton: true })).isConfirmed) {
                await axios.delete(`/products/${id}`);
                this.fetchProducts();
            }
        },

        async fetchFbos() { 
            try { this.fbos = (await axios.get('/fbos')).data; } 
            catch(e) { console.error("FBO Error"); }
        },

        async fetchSales() { 
            try { this.sales = (await axios.get('/sales')).data; } 
            catch(e) { this.sales = []; }
        },

        updateStats() {
            this.stats.total_items = this.products.length;
            this.stats.total_value = this.products.reduce((acc, p) => acc + (p.price_bs * p.stock), 0).toFixed(2);
            this.totalCC = this.products.reduce((acc, p) => acc + (parseFloat(p.cc_value) * p.stock), 0).toFixed(3);
        }
    }));
});
Alpine.start();