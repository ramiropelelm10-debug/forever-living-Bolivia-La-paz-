<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class AdminController extends Controller
{
    /**
     * Listar solicitudes de registro que están esperando aprobación
     * (Alimenta la tabla de "Solicitudes")
     */
    public function pendingRequests()
    {
        try {
            $requests = User::where('status', 'pendiente')
                ->with('persona') // Si tienes relación con la tabla de datos personales
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($requests, 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error al obtener solicitudes'], 500);
        }
    }

    /**
     * Aprobar o Rechazar una solicitud inicial de registro
     */
    public function respond(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:aprobado,rechazado'
        ]);

        try {
            $user = User::findOrFail($id);
            
            // Si aprobamos, el estado pasa a 'activo' para que pueda loguearse
            if ($request->status === 'aprobado') {
                $user->status = 'activo';
            } else {
                $user->status = 'rechazado';
            }

            $user->save();

            return response()->json([
                'message' => 'Solicitud procesada: El usuario ahora está ' . $user->status,
                'status' => $user->status
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'No se pudo procesar la respuesta'], 500);
        }
    }

    /**
     * Listar todos los usuarios del sistema (Alimenta la tabla de "Usuarios")
     * 🔥 EXCLUYE AL ADMIN ACTUAL Y A LAS SOLICITUDES PENDIENTES 🔥
     */
    public function allUsers()
    {
        try {
            $users = User::where('id', '!=', Auth::id())
                ->where('status', '!=', 'pendiente') // Filtro mágico aplicado
                ->with('persona')
                ->orderBy('id', 'desc')
                ->get();

            return response()->json($users, 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error al listar usuarios'], 500);
        }
    }

    /**
     * Alternar estado: Activa o Desactiva una cuenta (Toggle)
     */
    public function toggleStatus($id)
    {
        try {
            $user = User::findOrFail($id);

            // Alternamos entre activo e inactivo (Ideal para castigar o suspender cuentas)
            if ($user->status === 'activo') {
                $user->status = 'inactivo';
            } else {
                $user->status = 'activo';
            }

            $user->save();

            return response()->json([
                'message' => 'Estado cambiado a ' . $user->status,
                'nuevo_estado' => $user->status
            ], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'No se pudo cambiar el estado'], 500);
        }
    }

    /**
     * Ascender un Cliente a rango FBO (👑)
     */
    public function promote($id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Cambiamos el tipo de usuario y aseguramos que esté activo
            $user->tipo_usuario = 'fbo'; 
            $user->status = 'activo'; 
            
            $user->save();

            return response()->json([
                'message' => '¡Ascenso exitoso! El usuario ahora es FBO',
                'tipo_usuario' => $user->tipo_usuario
            ], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error al procesar el ascenso'], 500);
        }
    }
}