import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useAppStore = defineStore('app', () => {
    // Equivalente a tu "view: 'catalog'" de Alpine
    const currentView = ref('catalog')
    
    // Equivalente a tu configuración de biometría global
    const faceLoginActivo = ref(localStorage.getItem('faceLoginActivo') === 'true')

    const logout = () => {
        localStorage.removeItem('auth_token')
        window.location.reload()
    }

    return {
        currentView,
        faceLoginActivo,
        logout
    }
})