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
     * Recupera el listado general de usuarios que poseen el rol de cliente, 
     * incluyendo los detalles de su información personal asociada.
     */
    public function index()
    {
        $clientes = User::where('tipo_usuario', 'cliente')
            ->with('persona')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($clientes);
    }

    /**
     * Gestiona la creación de un nuevo cliente mediante una transacción de base de datos,
     * registrando primero el perfil personal y luego asociándolo a una nueva cuenta de acceso activa.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'dni'       => 'required|unique:personas,ci',
            'password'  => 'required|min:6',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                
                $persona = Persona::create([
                    'nombres'   => $request->name,
                    'apellidos' => $request->last_name,
                    'ci'        => $request->dni,
                    'telefono'  => $request->phone ?? null,
                    'correo'    => $request->email,
                ]);

                $user = User::create([
                    'persona_id'   => $persona->id,
                    'name'         => $request->name . ' ' . $request->last_name,
                    'email'        => $request->email,
                    'password'     => $request->password, 
                    'tipo_usuario' => 'cliente',
                    'status'       => 'activo',
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