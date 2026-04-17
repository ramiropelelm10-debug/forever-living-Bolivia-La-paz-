import axios from 'axios';
import { create, get } from '@github/webauthn-json';

/**
 * CONFIGURACIÓN BASE
 */
const api = axios.create({
    baseURL: 'http://localhost:8000/api', 
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    }
});

// Interceptor para inyectar el Token Sanctum en cada petición
api.interceptors.request.use(config => {
    const token = localStorage.getItem('auth_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

/**
 * 1. LOGIN TRADICIONAL (Email y Password)
 */
export const login = async (credentials) => {
    try {
        const response = await api.post('/login', credentials);
        
        if (response.data.token) {
            localStorage.setItem('auth_token', response.data.token);
            localStorage.setItem('user_email', response.data.user.email); // Guardamos el email para registrar biometría después
            localStorage.setItem('user_role', response.data.user.role);
            localStorage.setItem('user_name', response.data.user.name);
        }
        
        return response.data;
    } catch (error) {
        throw error;
    }
};

/**
 * 2. LOGIN CON BIOMETRÍA (FaceID / Huella / PIN)
 */
export const loginWithBiometrics = async (email) => {
    try {
        const config = { withCredentials: true };
        
        // Sincronizar cookie CSRF (Requerido por Sanctum)
        await axios.get('http://localhost:8000/sanctum/csrf-cookie', { withCredentials: true });
        
        // Obtener opciones de autenticación
        const { data: options } = await api.post('/webauthn/auth/options', { email }, config);
        
        // Pedir al navegador que use el sensor biométrico
        const credential = await get(options);
        
        // Verificar la credencial en el servidor
        await api.post('/webauthn/auth', credential, config);
        
        // Obtener el token final
        const { data } = await api.post('/webauthn/get-token', {}, config);
        
        if (data.token) {
            localStorage.setItem('auth_token', data.token);
            localStorage.setItem('user_name', data.user.name);
            localStorage.setItem('user_email', data.user.email);
        }
        
        return data;
    } catch (error) {
        console.error("Error en login biométrico:", error);
        throw error;
    }
};

/**
 * 3. REGISTRAR NUEVA BIOMETRÍA (BOTÓN AZUL)
 * Aquí es donde corregimos el error 422 agregando el email.
 */
export const registerBiometrics = async () => {
    try {
        const config = { withCredentials: true };
        
        // Recuperamos el email del usuario logueado desde el localStorage
        const userEmail = localStorage.getItem('user_email');

        if (!userEmail) {
            throw new Error("No se encontró el email del usuario. Inicia sesión nuevamente.");
        }

        // 1. Obtener opciones para CREAR una nueva llave
        const { data: options } = await api.post('/webauthn/keys/options', {}, config);
        
        // 2. El navegador activa el FaceID / Huella
        const credential = await create(options);

        // 3. Enviamos la credencial + el email que el backend está pidiendo (Error 422 arreglado)
        const response = await api.post('/webauthn/keys', {
            ...credential, 
            email: userEmail, // <--- Este campo soluciona la validación de Laravel
            name: "Lenovo LOQ Ramiro"
        }, config);
        
        return response.data;
    } catch (error) {
        console.error("Error al registrar biometría:", error.response?.data || error);
        throw error;
    }
};

export default api;