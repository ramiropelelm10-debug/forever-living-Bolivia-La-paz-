<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class ClienteController extends Controller
{
    /**
     * Muestra la lista de clientes vinculados a su información personal
     */
    public function index()
    {
        // Traemos usuarios que son específicamente de tipo 'cliente' con su data de persona
        $clientes = User::where('tipo_usuario', 'cliente')
            ->with('persona')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($clientes);
    }

    /**
     * Guarda un nuevo cliente siguiendo la lógica: Persona -> User
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'dni'       => 'required|unique:personas,ci', // CI debe ser único en la tabla personas
            'password'  => 'required|min:6',
        ]);

        try {
            // Usamos una transacción para asegurar que no se cree uno sin el otro
            return DB::transaction(function () use ($request) {
                
                // 1. Primero creamos el perfil humano (Persona)
                $persona = Persona::create([
                    'nombres'   => $request->name,
                    'apellidos' => $request->last_name,
                    'ci'        => $request->dni,
                    'telefono'  => $request->phone ?? null,
                    'correo'    => $request->email,
                ]);

                // 2. Creamos la cuenta de acceso vinculada (User)
                // NOTA: Pasamos el password plano porque el modelo User ya tiene el cast 'hashed'
                $user = User::create([
                    'persona_id'   => $persona->id,
                    'name'         => $request->name . ' ' . $request->last_name,
                    'email'        => $request->email,
                    'password'     => $request->password, 
                    'tipo_usuario' => 'cliente',
                    'status'       => 'activo', // Nace activo porque lo crea el Admin
                    'nit_ci'       => $request->dni,
                ]);

                return response()->json([
                    'message' => '¡Cliente registrado con éxito en Forever Bolivia!',
                    'user'    => $user->load('persona')
                ], 201);
            });

        } catch (Exception $e) {
            return response()->json([
                'error'  => 'No se pudo completar el registro',
                'detail' => $e->getMessage()
            ], 500);
        }
    }
}