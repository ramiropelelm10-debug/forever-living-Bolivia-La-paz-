<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Persona;
use Exception;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * LOGIN: Genera OTP o salta si el dispositivo es de confianza
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_token' => 'nullable|string' 
        ]);

        $user = User::where('email', trim($request->email))->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Credenciales incorrectas'], 401);
        }

        if ($user->status !== 'activo') {
            return response()->json(['message' => 'Cuenta pendiente de aprobación o desactivada.'], 403);
        }

        // 🕵️ VERIFICAR DISPOSITIVO DE CONFIANZA
        if ($request->device_token && 
            $user->trusted_device_token === $request->device_token && 
            $user->trusted_until && 
            Carbon::parse($user->trusted_until)->isFuture()) {
            
            $token = $user->createToken('forever_access_token')->plainTextToken;
            return response()->json([
                'token' => $token,
                'user'  => [
                    'id'           => $user->id,
                    'name'         => $user->name,
                    'email'        => $user->email,
                    'role'         => $user->role,          // 🔥 ENVIADO: Para control de vistas en Vue (admin / inventario)
                    'tipo_usuario' => $user->tipo_usuario,
                ],
                'requires_otp' => false,
                'message' => 'Acceso directo concedido.'
            ]);
        }

        // 🎲 GENERAR OTP Y ACTUALIZAR
        $otp = (string) rand(100000, 999999);
        
        DB::table('users')->where('id', $user->id)->update([
            'otp_code' => $otp,
            'otp_expires_at' => Carbon::now('America/La_Paz')->addMinutes(15),
            'updated_at' => now()
        ]);

        return response()->json([
            'message' => 'Código enviado correctamente',
            'requires_otp' => true,
            'email' => $user->email,
            'role' => $user->role,                           // 🔥 ENVIADO: Por si el front necesita saber el rol antes de validar el OTP
            'code_debug' => $otp 
        ]);
    }

    /**
     * VERIFICAR OTP: Valida el código y puede crear la confianza
     */
    public function verifyOtp(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'code'  => 'required|string',
                'remember_device' => 'boolean'
            ]);

            $now = Carbon::now('America/La_Paz');

            $user = User::where('email', trim($request->email))
                        ->where('otp_code', trim($request->code))
                        ->where('otp_expires_at', '>', $now)
                        ->first();

            if (!$user) {
                return response()->json(['message' => 'Código incorrecto o expirado'], 401);
            }

            $token = $user->createToken('forever_access_token')->plainTextToken;
            
            // Construimos la respuesta limpia inyectando el rol del usuario
            $responsePayload = [
                'token' => $token,
                'user'  => [
                    'id'           => $user->id,
                    'name'         => $user->name,
                    'email'        => $user->email,
                    'role'         => $user->role,          // 🔥 VITAL: El rol definitivo que leerá Vue al entrar exitosamente
                    'tipo_usuario' => $user->tipo_usuario,
                ],
                'message' => '¡Verificación exitosa!'
            ];

            // 🔒 GUARDAR CONFIANZA SI MARCÓ EL CHECK
            if ($request->remember_device) {
                $deviceToken = Str::random(60); 
                $user->update([
                    'otp_code' => null,
                    'trusted_device_token' => $deviceToken,
                    'trusted_until' => $now->copy()->addDays(30)
                ]);
                $responsePayload['trusted_device_token'] = $deviceToken;
            } else {
                $user->update(['otp_code' => null]);
            }

            return response()->json($responsePayload);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error en el servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * LOGOUT: Cierra sesión de Sanctum
     */
    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }
        return response()->json(['message' => 'Sesión cerrada']);
    }

    /**
     * REGISTRO: Solicitud de nuevo usuario
     */
    public function registerRequest(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $persona = Persona::create([
                    'nombres'   => $request->name,
                    'apellidos' => $request->last_name,
                    'correo'    => trim($request->email),
                ]);

                User::create([
                    'persona_id'   => $persona->id,
                    'name'         => $request->name, 
                    'email'        => trim($request->email),
                    'password'     => $request->password, 
                    'status'       => 'pendiente', 
                    'tipo_usuario' => $request->isFboRequest ? 'fbo' : 'cliente',
                    'role'         => 'cliente',             // Por defecto nacen con rol base de cliente
                ]);

                return response()->json(['message' => 'Solicitud enviada.'], 201);
            });
        } catch (Exception $e) {
            return response()->json(['message' => 'Error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * LOGIN FACE ID: Genera token directo si la IA biométrica aprueba el rostro en el frontend
     */
    public function loginFaceId(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', trim($request->email))->first();

        // Validaciones de seguridad
        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado.'], 404);
        }

        if ($user->status !== 'activo') {
            return response()->json(['message' => 'Cuenta pendiente o desactivada.'], 403);
        }

        // ¡Generamos el TOKEN REAL de Sanctum!
        $token = $user->createToken('forever_access_token')->plainTextToken;

        return response()->json([
            'message' => 'Login biométrico exitoso',
            'token' => $token,
            'user'  => [
                'id'           => $user->id,
                'name'         => $user->name,
                'email'        => $user->email,
                'role'         => $user->role,
                'tipo_usuario' => $user->tipo_usuario,
            ]
        ], 200);
    }
}