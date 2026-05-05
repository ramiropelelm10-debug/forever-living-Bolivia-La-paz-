<?php

namespace App\Http\Controllers\Api;

use App\Models\Fbo;
use App\Models\User;
use App\Models\Persona;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Exception;

class FboController extends Controller
{
    public function index() 
    {
        // Cargamos también la relación con el usuario y la persona para ver los nombres en el listado
        return response()->json(Fbo::with('user.persona')->orderBy('created_at', 'desc')->get());
    }

    public function store(Request $request)
    {
        // 1. VALIDACIÓN INTEGRAL
        $validator = Validator::make($request->all(), [
            'fbo_id'        => 'required|unique:fbos,fbo_id',
            'email'         => 'required|email|unique:users,email',
            'name'          => 'required|string|max:255',
            'last_name'     => 'required|string|max:255',
            'dni'           => 'required|string|unique:personas,ci',
            'discount_rate' => 'numeric|min:0|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validación fallida',
                'messages' => $validator->errors()
            ], 422);
        }

        try {
            // 2. TRANSACCIÓN DE BASE DE DATOS
            return DB::transaction(function () use ($request) {
                
                // A. Creamos los datos físicos (Persona)
                $persona = Persona::create([
                    'nombres'   => $request->name,
                    'apellidos' => $request->last_name,
                    'ci'        => $request->dni,
                ]);

                // B. Creamos la cuenta de acceso (User) vinculada a la persona
                $user = User::create([
                    'persona_id' => $persona->id,
                    'name'       => $request->name . ' ' . $request->last_name,
                    'email'      => $request->email,
                    'password'   => Hash::make('Forever123'), // Contraseña por defecto
                    'role'       => 'fbo',
                ]);

                // C. Creamos el perfil de distribuidor (FBO) vinculado al usuario
                $fbo = Fbo::create([
                    'user_id'       => $user->id,
                    'fbo_id'        => $request->fbo_id,
                    'discount_rate' => $request->discount_rate ?? 0,
                ]);

                return response()->json([
                    'message' => 'FBO y Usuario registrados correctamente',
                    'data' => $fbo->load('user.persona')
                ], 201);
            });

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Error crítico al procesar el registro',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}