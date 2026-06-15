import axios from 'axios';

const api = axios.create({
    // 🔥 AQUÍ ESTÁ LA MAGIA: Apuntamos tu frontend directamente a la nube 🔥
    baseURL: 'https://forever-api-e5zr.onrender.com/api',
    headers: {
        'Accept': 'application/json'
    }
});

api.interceptors.request.use(config => {
    let token = localStorage.getItem('auth_token');
    if (token) {
        token = token.replace(/^"(.*)"$/, '$1'); 
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

api.interceptors.response.use(response => {
    return response;
}, error => {
    if (error.response && error.response.status === 401) {
        // 🔥 APAGAMOS EL REDIRECT AUTOMÁTICO TEMPORALMENTE
        console.error("🚨 LARAVEL RECHAZÓ EL TOKEN. Revisa el LocalStorage.");
        // localStorage.removeItem('auth_token');
        // localStorage.removeItem('userType');
        // window.location.href = '/admin-login'; 
    }
    return Promise.reject(error);
});

export default api;