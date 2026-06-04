import { createI18n } from 'vue-i18n';

const messages = {
  es: {
    nav: {
      tienda: 'Tienda',
      acerca: 'Acerca De',
      blog: 'Desde el Blog',
      contacto: 'Contacta con Nosotros',
      iniciar_sesion: 'Iniciar Sesión',
      registrarse: 'Registrarse',
      panel: 'Panel Admin',
      perfil: 'Mi Perfil',
      salir: 'Salir'
    },
    tienda: {
      titulo: 'Catálogo de Productos',
      subtitulo: 'Descubre nuestra exclusiva línea de productos naturales a base de Aloe Vera. Salud, nutrición y belleza para ti y tu familia.',
      btn_carrito: 'Añadir al Carrito',
      sin_categoria: 'Sin Categoría',
      filtros: {
        todos: 'Todos',
        bebidas: 'Bebidas',
        nutricion: 'Nutrición',
        cuidado: 'Cuidado Personal',
        colmena: 'Colmena',
        combos: 'Combos'
      }
    },
    perfil: {
      titulo: 'Mi Panel',
      cliente: 'Cliente',
      datos_personales: 'Datos Personales',
      nombres: 'Nombres',
      apellidos: 'Apellidos',
      correo: 'Correo Electrónico',
      historial: 'Historial de Compras',
      tabla_fecha: 'Fecha',
      tabla_orden: 'Nro. Orden',
      tabla_total: 'Total',
      tabla_docs: 'Documentos',
      sin_compras: 'Aún no has realizado ninguna compra en el sistema.'
    }
  },
  en: {
    nav: {
      tienda: 'Shop',
      acerca: 'About Us',
      blog: 'From the Blog',
      contacto: 'Contact Us',
      iniciar_sesion: 'Login',
      registrarse: 'Sign Up',
      panel: 'Admin Panel',
      perfil: 'My Profile',
      salir: 'Logout'
    },
    tienda: {
      titulo: 'Product Catalog',
      subtitulo: 'Discover our exclusive line of natural Aloe Vera products. Health, nutrition, and beauty for you and your family.',
      btn_carrito: 'Add to Cart',
      sin_categoria: 'Uncategorized',
      filtros: {
        todos: 'All',
        bebidas: 'Drinks',
        nutricion: 'Nutrition',
        cuidado: 'Personal Care',
        colmena: 'Beehive',
        combos: 'Packs'
      }
    },
    perfil: {
      titulo: 'My Dashboard',
      cliente: 'Customer',
      datos_personales: 'Personal Data',
      nombres: 'First Name',
      apellidos: 'Last Name',
      correo: 'Email Address',
      historial: 'Purchase History',
      tabla_fecha: 'Date',
      tabla_orden: 'Order No.',
      tabla_total: 'Total',
      tabla_docs: 'Documents',
      sin_compras: 'You have not made any purchases yet.'
    }
  }
};

const i18n = createI18n({
  locale: 'es', 
  fallbackLocale: 'es',
  messages,
});

export default i18n;