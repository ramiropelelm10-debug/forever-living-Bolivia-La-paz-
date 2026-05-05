<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Exception;

class ClienteController extends Controller
{
    // Muestra la lista de clientes
    public function index()
    {
        // Traemos usuarios que NO son FBO (rol = 'cliente' o como lo tengas definido)
        // Aquí asumimos que los FBO tienen un registro en la tabla 'fbos', los clientes no.
        $clientes = User::doesntHave('fbo')->with('persona')->orderBy('id', 'desc')->get();
        return response()->json($clientes);
    }

    // Guarda el nuevo cliente
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'dni'      => 'required|string|unique:personas,ci',
        ]);

        try {
            DB::beginTransaction();

            // 1. Creamos al usuario con su contraseña
            $user = User::create([
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                // 'role' => 'cliente' // Descomenta si usas roles en tu sistema
            ]);

            // 2. Le creamos su perfil de Persona
            Persona::create([
                'user_id'   => $user->id,
                'nombres'   => $request->name,
                'apellidos' => $request->last_name,
                'ci'        => $request->dni,
                'telefono'  => $request->phone,
            ]);

            DB::commit();

            return response()->json(['message' => 'Cliente registrado con éxito'], 201);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al registrar', 'detail' => $e->getMessage()], 500);
        }
    }
}