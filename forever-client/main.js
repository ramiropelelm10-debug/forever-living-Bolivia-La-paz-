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
        isLoggedIn: false,
        isLoading: false,
        requiresOTP: false, 
        view: 'catalog', 
        showModal: false,
        editMode: false,
        search: '',
        loginData: { email: '', password: '', code: '' }, 
        productData: { id: null, name: '', sku: '', stock: 0, price_bs: 0, cc_value: 0, foto_persona: '' },
        fboData: { fbo_id: '', name: '', discount_rate: 0 },
        fbos: [], products: [], trashProducts: [], sales: [], 
        stats: { total_items: 0, low_stock: 0, total_value: 0 }, totalCC: 0,

        faceApp: { cargando: true, rostroDetectado: false },
        faceInterval: null,
        loginInterval: null, 

        faceLoginActivo: localStorage.getItem('faceLoginActivo') === 'true',
        rostroAdminUrl: localStorage.getItem('rostroAdminUrl') || null,

        async init() {
            axios.defaults.headers.common['Accept'] = 'application/json';
            const token = localStorage.getItem('auth_token');
            
            if (token && token !== 'undefined') {
                if (this.faceLoginActivo) {
                    // MODO BLOQUEO: Hay sesión pero pasamos por el filtro facial primero
                    this.isLoggedIn = false;
                    setTimeout(() => { this.iniciarCamaraLogin(); }, 500);
                } else {
                    this.entrarAlSistemaDirecto();
                }
            } else {
                localStorage.removeItem('auth_token');
            }
        },

        async entrarAlSistemaDirecto() {
            this.isLoggedIn = true;
            await Promise.all([this.fetchProducts(), this.fetchFbos(), this.fetchSales()]);
            setTimeout(() => { this.inicializarFaceDetection(); }, 500);
        },

        iniciarCamaraLogin() {
            faceapi.nets.tinyFaceDetector.loadFromUri('/models').then(() => {
                const video = document.getElementById('login-video');
                const canvas = document.getElementById('login-canvas');
                if (!video || !canvas) return;

                navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" }, audio: false })
                    .then(stream => {
                        video.srcObject = stream;
                        video.onplaying = () => {
                            const displaySize = { width: video.clientWidth, height: video.clientHeight };
                            faceapi.matchDimensions(canvas, displaySize);

                            this.loginInterval = setInterval(async () => {
                                if(video.paused || video.ended) return;
                                const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions());
                                
                                if (detections.length > 0) {
                                    clearInterval(this.loginInterval); 
                                    video.srcObject.getTracks().forEach(track => track.stop()); 
                                    
                                    Swal.fire({
                                        title: '¡Rostro Identificado!', text: 'Accediendo al sistema...', icon: 'success', 
                                        timer: 1000, showConfirmButton: false
                                    });
                                    
                                    setTimeout(() => { this.entrarAlSistemaDirecto(); }, 1000);
                                }
                                const resizedDetections = faceapi.resizeResults(detections, displaySize);
                                canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
                                faceapi.draw.drawDetections(canvas, resizedDetections);
                            }, 400);
                        };
                    });
            });
        },

        lockScreen() { 
            if(!this.faceLoginActivo) {
                Swal.fire('Atención', 'Para usar el bloqueo rápido, primero debes activar la Seguridad Facial en el Perfil Admin.', 'warning');
                return;
            }
            location.reload(); 
        },

        logout() { 
            // Limpiamos los intervalos y las cámaras
            if (this.faceInterval) clearInterval(this.faceInterval);
            if (this.loginInterval) clearInterval(this.loginInterval);
            
            const video1 = document.getElementById('security-video');
            if (video1 && video1.srcObject) video1.srcObject.getTracks().forEach(t => t.stop());
            
            const video2 = document.getElementById('login-video');
            if (video2 && video2.srcObject) video2.srcObject.getTracks().forEach(t => t.stop());

            // BORRAMOS EL TOKEN DE ACCESO
            localStorage.removeItem('auth_token'); 
            location.reload(); 
        },

        toggleFaceLogin() {
            if (!this.rostroAdminUrl) {
                Swal.fire('Atención', 'Primero escanea tu rostro en el botón de abajo.', 'warning');
                return;
            }
            this.faceLoginActivo = !this.faceLoginActivo;
            localStorage.setItem('faceLoginActivo', this.faceLoginActivo);
            Swal.fire('Configuración', this.faceLoginActivo ? 'Bloqueo facial activado.' : 'Bloqueo facial desactivado.', 'success');
        },

        escanearRostroAdmin() {
            if(!this.faceApp.rostroDetectado) return Swal.fire('Error', 'Colócate frente a la cámara de vigilancia.', 'error');
            const video = document.getElementById('security-video');
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth; canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
            this.rostroAdminUrl = canvas.toDataURL('image/jpeg');
            localStorage.setItem('rostroAdminUrl', this.rostroAdminUrl);
            Swal.fire('Éxito', 'Rostro guardado correctamente.', 'success');
        },

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
                    this.entrarAlSistemaDirecto();
                }
            } catch (e) { Swal.fire('Error', 'Código incorrecto', 'error');
            } finally { this.isLoading = false; }
        },

        async registerBiometrics() {
            try {
                this.isLoading = true;
                await window.api.registerBiometrics();
                Swal.fire('¡Listo!', 'Llavero biométrico vinculado.', 'success');
            } catch (err) { Swal.fire('Error', 'No se pudo vincular.', 'error'); } 
            finally { this.isLoading = false; }
        },

        async fetchProducts() {
            const endpoint = this.view === 'catalog' ? '/products' : '/products/trash';
            try {
                const res = await axios.get(endpoint, { params: { search: this.search } });
                if (this.view === 'catalog') { this.products = res.data; this.updateStats(); } 
                else { this.trashProducts = res.data; }
            } catch (e) { if(e.response && e.response.status === 401) this.logout(); }
        },

        async fetchFbos() { try { const res = await axios.get(`/fbos`); this.fbos = res.data; } catch (e) {} },
        async fetchSales() { try { const res = await axios.get(`/sales`); this.sales = res.data; } catch (e) {} },
        
        updateStats() {
            this.stats.total_items = this.products.length;
            this.stats.total_value = this.products.reduce((acc, p) => acc + (p.price_bs * p.stock), 0).toFixed(2);
        },

        async inicializarFaceDetection() {
            try {
                await faceapi.nets.tinyFaceDetector.loadFromUri('/models');
                this.faceApp.cargando = false;
                this.iniciarCamaraSeguridad();
            } catch (error) { console.error("Error IA:", error); }
        },

        iniciarCamaraSeguridad() {
            setTimeout(() => {
                const video = document.getElementById('security-video');
                const canvas = document.getElementById('security-canvas');
                if (!video || !canvas) return;
                navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" }, audio: false })
                    .then(stream => {
                        video.srcObject = stream;
                        video.onplaying = () => {
                            const displaySize = { width: video.clientWidth, height: video.clientHeight };
                            faceapi.matchDimensions(canvas, displaySize);
                            this.faceInterval = setInterval(async () => {
                                if(video.paused || video.ended) return;
                                const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions());
                                this.faceApp.rostroDetectado = detections.length > 0;
                                const resizedDetections = faceapi.resizeResults(detections, displaySize);
                                canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
                                faceapi.draw.drawDetections(canvas, resizedDetections);
                            }, 300); 
                        };
                    });
            }, 500); 
        }
    }));
});
Alpine.start();