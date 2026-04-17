<?php

namespace App\Http\Controllers\Api;

use App\Models\Fbo;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;

class FboController extends Controller
{
    /**
     * Listar todos los FBOs
     */
    public function index() 
    {
        return response()->json(Fbo::all());
    }

    /**
     * Guardar un nuevo FBO
     */
    public function store(Request $request)
    {
        try {
            // Forzamos el guardado directo para Forever Bolivia
            $fbo = Fbo::create([
                'fbo_id'        => $request->fbo_id,
                'name'          => $request->name,
                'discount_rate' => $request->discount_rate ?? 0,
            ]);

            return response()->json($fbo, 201);

        } catch (Exception $e) {
            // Enviamos el mensaje de error para que se vea en el frontend
            return response()->json([
                'error' => 'Error al guardar FBO',
                'message' => $e->getMessage()
            ], 500);
        }
    }       
}