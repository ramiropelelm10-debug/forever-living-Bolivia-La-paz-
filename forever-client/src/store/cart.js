import { ref, computed, watch } from 'vue';

// 1. CARGAMOS EL CARRITO DESDE EL DISCO DURO (Si existe)
const storedCart = localStorage.getItem('forever_cart');
export const cart = ref(storedCart ? JSON.parse(storedCart) : []);

// 2. MAGIA: CADA VEZ QUE 'cart' CAMBIA, SE GUARDA SOLO EN LOCALSTORAGE
watch(cart, (newCart) => {
  localStorage.setItem('forever_cart', JSON.stringify(newCart));
}, { deep: true });

// AGREGAR AL CARRITO
export const addToCart = (product, discount = 0) => {
  const existingItem = cart.value.find(item => item.id === product.id);
  // 🔥 Calculamos el precio con el descuento aplicado
  const precioFinal = product.price_bs - (product.price_bs * (discount / 100));

  if (existingItem) {
    existingItem.quantity++;
  } else {
    // Guardamos el producto con su precio original y su precio final
    cart.value.push({ 
      ...product, 
      quantity: 1, 
      precio_final: precioFinal 
    });
  }
};

// ACTUALIZAR CANTIDAD (Para el botón + y -)
export const updateQuantity = (productId, change) => {
  const item = cart.value.find(i => i.id === productId);
  if (item) {
    const newQty = item.quantity + change;
    if (newQty > 0) {
      item.quantity = newQty;
    }
  }
};

// QUITAR UN PRODUCTO ENTERO
export const removeFromCart = (productId) => {
  cart.value = cart.value.filter(item => item.id !== productId);
};

// VACIAR TODO EL CARRITO
export const clearCart = () => {
  cart.value = [];
};

// 🔥 SUMAS MÁGICAS (COMPUTADOS)

// Total en Bolivianos (Usando el precio final con descuento)
export const cartTotal = computed(() => {
  return cart.value.reduce((total, item) => total + (item.precio_final * item.quantity), 0);
});

// Total en CC (Puntos Forever)
export const cartTotalCC = computed(() => {
  return cart.value.reduce((total, item) => total + (parseFloat(item.cc_value) * item.quantity), 0);
});

// Contador de globitos para el Navbar
export const cartItemCount = computed(() => {
  return cart.value.reduce((count, item) => count + item.quantity, 0);
});