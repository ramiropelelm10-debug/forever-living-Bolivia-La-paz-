<template>
  <button
    @click="$emit('click')"
    class="flex items-center gap-2 pb-4 text-[10px] font-black uppercase tracking-widest transition-all relative whitespace-nowrap border-b-4"
    :class="active ? 'border-[#4a5d23] text-[#4a5d23]' : 'border-transparent text-slate-400'"
  >
    <i v-if="icon" :class="icon" class="text-sm"></i>
    
    <span>{{ label }}</span>

    <span
      v-if="badge > 0"
      class="flex items-center justify-center w-4 h-4 text-[8px] font-black text-white rounded-full bg-[#b48a2d] shadow-sm ml-1"
    >
      {{ badge }}
    </span>
  </button>
</template>

<script setup>
defineProps({
  label: String,
  icon: String,   // Agregamos prop para el icono
  active: Boolean,
  badge: { type: Number, default: 0 }
})
defineEmits(['click'])
</script>