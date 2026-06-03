import api from '../api.js';
import { create, get } from '@github/webauthn-json';

/**
 * LOGIN INICIAL
 * Envía email, password y el device_token (si existe)
 */
export const login = async (credentials) => {
    // credentials = { email, password, device_token }
    const response = await api.post('/login', credentials);
    
    // Si entramos directo (sin OTP), guardamos la sesión
    if (response.data.token) {
        saveSession(response.data);
    }
    
    return response.data;
};

/**
 * VERIFICACIÓN DE OTP
 * Aquí es donde recibimos el 'trusted_device_token' por primera vez
 */
export const verifyOtp = async (data) => {
    // data = { email, code, remember_device }
    const response = await api.post('/verify-otp', data);
    
    if (response.data.token) {
        saveSession(response.data);
        
        // 🔥 PERSISTENCIA: Si el servidor mandó una llave de confianza, la guardamos
        // Esta NO se borra en el logout.
        if (response.data.trusted_device_token) {
            localStorage.setItem('forever_device_token', response.data.trusted_device_token);
        }
    }
    
    return response.data;
};

/**
 * LOGIN CON BIOMETRÍA (FaceID / Huella)
 */
export const loginWithBiometrics = async (email) => {
    const config = { withCredentials: true };
    
    // Aseguramos cookie CSRF para Sanctum
    await api.get('http://localhost:8000/sanctum/csrf-cookie', config);
    
    const { data: options } = await api.post('/webauthn/auth/options', { email }, config);
    const credential = await get(options);
    
    await api.post('/webauthn/auth', credential, config);
    const { data } = await api.post('/webauthn/get-token', {}, config);
    
    if (data.token) {
        saveSession(data);
    }
    return data;
};

/**
 * REGISTRO DE NUEVA LLAVE BIOMÉTRICA
 */
export const registerBiometrics = async () => {
    const config = { withCredentials: true };
    const userEmail = localStorage.getItem('user_email');
    
    if (!userEmail) throw new Error("No se encontró el email del usuario.");

    const { data: options } = await api.post('/webauthn/keys/options', {}, config);
    const credential = await create(options);

    const response = await api.post('/webauthn/keys', {
        ...credential, 
        email: userEmail,
        name: "Dispositivo Autorizado - Forever"
    }, config);
    
    return response.data;
};

/**
 * CERRAR SESIÓN (CORREGIDO okok)
 * Borra la sesión pero MANTIENE la confianza del dispositivo
 */
export const logout = () => {
    // ❌ NO USAR localStorage.clear() porque borra el 'forever_device_token'
    
    // Eliminamos solo los datos de la sesión actual
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user_email');
    localStorage.removeItem('user_role');
    localStorage.removeItem('user_name');
    
    // ✅ El 'forever_device_token' se queda en el disco para que
    // la próxima vez el Login sepa que esta computadora es de confianza.

    window.location.reload();
};

/**
 * FUNCIÓN INTERNA: Guardar datos de usuario en el navegador
 */
const saveSession = (data) => {
    localStorage.setItem('auth_token', data.token);
    localStorage.setItem('user_email', data.user.email);
    localStorage.setItem('user_name', data.user.name);
    
    // Si tu API devuelve el rol, guárdalo también
    if (data.user.tipo_usuario) {
        localStorage.setItem('user_role', data.user.tipo_usuario);
    }
};