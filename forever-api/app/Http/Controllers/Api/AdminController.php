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
     * Obtiene y retorna una lista de las solicitudes de registro que se encuentran pendientes de aprobación,
     * incluyendo la información personal asociada a cada usuario.
     */
    public function pendingRequests()
    {
        try {
            $requests = User::where('status', 'pendiente')
                ->with('persona')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($requests, 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error al obtener solicitudes'], 500);
        }
    }

    /**
     * Procesa la respuesta de un administrador a una solicitud de registro, actualizando 
     * el estado del usuario a activo o rechazado según corresponda.
     */
    public function respond(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:aprobado,rechazado'
        ]);

        try {
            $user = User::findOrFail($id);
            
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
     * Recupera la lista de todos los usuarios registrados en el sistema, excluyendo al administrador 
     * que realiza la consulta y a las cuentas que aún están pendientes de aprobación.
     */
    public function allUsers()
    {
        try {
            $users = User::where('id', '!=', Auth::id())
                ->where('status', '!=', 'pendiente')
                ->with('persona')
                ->orderBy('id', 'desc')
                ->get();

            return response()->json($users, 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error al listar usuarios'], 500);
        }
    }

    /**
     * Alterna el estado de acceso de un usuario específico, permitiendo suspender temporalmente 
     * una cuenta activa o reactivar una cuenta previamente suspendida.
     */
    public function toggleStatus($id)
    {
        try {
            $user = User::findOrFail($id);

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
     * Modifica el rol de un usuario con perfil de cliente para otorgarle el rango de FBO, 
     * asegurando que su cuenta permanezca activa durante el proceso.
     */
    public function promote($id)
    {
        try {
            $user = User::findOrFail($id);
            
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