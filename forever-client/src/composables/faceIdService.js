import * as faceapi from 'face-api.js';
import api from '../api.js';

let modelosCargados = false;

// Carga los 3 modelos que existen en /public/models
export async function cargarModelos() {
    if (modelosCargados) return;
    await faceapi.nets.ssdMobilenetv1.loadFromUri('/models');
    await faceapi.nets.faceLandmark68Net.loadFromUri('/models');
    await faceapi.nets.faceRecognitionNet.loadFromUri('/models');
    modelosCargados = true;
}

export async function iniciarCamara(videoEl) {
    const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
    videoEl.srcObject = stream;
    // Esperar a que el video tenga imagen real
    await new Promise((resolve) => {
        if (videoEl.readyState >= 2) return resolve();
        videoEl.onloadeddata = () => resolve();
    });
    return stream;
}

export function detenerCamara(stream) {
    if (stream) stream.getTracks().forEach((t) => t.stop());
}

// Devuelve un array de 128 números con los rasgos del rostro, o null si no detecta rostro
export async function obtenerDescriptor(videoEl) {
    const det = await faceapi.detectSingleFace(videoEl).withFaceLandmarks().withFaceDescriptor();
    return det ? Array.from(det.descriptor) : null;
}

export const faceIdApi = {
    status: () => api.get('/face-id/status'),
    register: (descriptors, password) => api.post('/face-id/register', { descriptors, password }),
    remove: () => api.delete('/face-id'),
};