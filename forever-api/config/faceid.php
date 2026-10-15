<?php

return [

    // Distancia máxima (face-api.js) para aceptar un rostro. Menor = más estricto.
    // 0.4 - 0.5 recomendado. Mismo rostro suele dar 0.2 - 0.45; rostros distintos > 0.6.
    'umbral' => env('FACEID_UMBRAL', 0.5),

    // Al registrar, las capturas deben parecerse entre sí (evita registrar basura o personas distintas).
    'max_distancia_registro' => 0.55,
];