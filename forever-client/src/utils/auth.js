// src/utils/auth.js

// Esta función obtiene el rol guardado en el navegador
export const getUserRole = () => localStorage.getItem('user_role');

// Funciones de comprobación rápidas
export const isAdmin = () => getUserRole() === 'admin';
export const isAlmacen = () => getUserRole() === 'almacen';
export const isVentas = () => getUserRole() === 'ventas';
export const isProveedor = () => getUserRole() === 'proveedor';

// Función para cerrar sesión y limpiar todo
export const logout = () => {
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user_role');
    localStorage.removeItem('user_name');
    window.location.href = '/login';
};