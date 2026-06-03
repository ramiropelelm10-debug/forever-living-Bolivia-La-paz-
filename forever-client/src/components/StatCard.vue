<template>
  <div class="bg-white rounded-2xl border p-5 flex items-start gap-4 shadow-sm transition hover:shadow-md" style="border-color:#e8eedf">
    <div class="p-3 rounded-xl flex-shrink-0" :style="`background:${warn ? '#fff7ed' : '#f0f4ea'}`">
      <i :class="icon" class="text-xl" :style="`color:${color}`"></i>
    </div>
    <div>
      <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">{{ label }}</p>
      <h3 class="text-2xl font-black mt-0.5 leading-none" :style="`color:${color}`">{{ value }}</h3>
      <p class="text-[9px] text-slate-400 font-semibold mt-1">{{ sub }}</p>
    </div>
  </div>
</template>

<script setup>
defineProps({ icon: String, label: String, value: [String, Number], sub: String, color: String, warn: Boolean })
</script>