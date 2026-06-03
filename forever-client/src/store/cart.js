import { ref, computed } from 'vue';

export const cart = ref([]);

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

export const removeFromCart = (productId) => {
  cart.value = cart.value.filter(item => item.id !== productId);
};

export const clearCart = () => {
  cart.value = [];
};

// 🔥 Ahora sumamos usando el 'precio_final'
export const cartTotal = computed(() => {
  return cart.value.reduce((total, item) => total + (item.precio_final * item.quantity), 0);
});

export const cartTotalCC = computed(() => {
  return cart.value.reduce((total, item) => total + (item.cc_value * item.quantity), 0);
});

export const cartItemCount = computed(() => {
  return cart.value.reduce((count, item) => count + item.quantity, 0);
});