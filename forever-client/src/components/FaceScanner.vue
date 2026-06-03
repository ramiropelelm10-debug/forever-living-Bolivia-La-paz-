<template>
    <div class="mb-8 flex flex-col items-center justify-center">
        <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-3 animate-pulse">Escaneando Rostro...</p>
        <div class="relative w-32 h-32 rounded-full overflow-hidden border-4 border-emerald-500 shadow-[0_0_20px_rgba(16,185,129,0.4)]">
            <video id="login-video" autoplay muted playsinline class="w-full h-full object-cover bg-slate-800"></video>
            <canvas id="login-canvas" class="absolute top-0 left-0 w-full h-full z-10"></canvas>
        </div>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { loadFaceModels, startVideo } from '../composables/faceApiService.js';

onMounted(async () => {
    await loadFaceModels();
    const video = document.getElementById('login-video');
    if (video) startVideo(video);
});
</script>