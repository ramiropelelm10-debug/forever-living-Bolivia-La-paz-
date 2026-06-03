import * as faceapi from 'face-api.js';

export async function loadFaceModels() {
    try {
        // Asegúrate que la carpeta /public/models existe con los archivos de face-api
        await faceapi.nets.tinyFaceDetector.loadFromUri('/models');
        console.log("✅ Modelos cargados");
    } catch (error) {
        console.error("❌ Error modelos:", error);
    }
}

export async function startVideo(videoElement) {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
        if (videoElement) videoElement.srcObject = stream;
        return stream;
    } catch (err) { // <--- Aquí quitamos la "a" que estaba suelta
        console.error("❌ Error cámara:", err);
        throw err;
    }
}

export function stopVideo(stream) {
    if (stream) stream.getTracks().forEach(track => track.stop());
}