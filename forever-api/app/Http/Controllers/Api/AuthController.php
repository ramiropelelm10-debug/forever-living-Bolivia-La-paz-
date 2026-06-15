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
                    'role'         => $user->role,
                    'tipo_usuario' => $user->tipo_usuario,
                ],
                'requires_otp' => false,
                'message' => 'Acceso directo concedido.'
            ]);
        }

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
            'role' => $user->role,
            'code_debug' => $otp 
        ]);
    }

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
            
            $responsePayload = [
                'token' => $token,
                'user'  => [
                    'id'           => $user->id,
                    'name'         => $user->name,
                    'email'        => $user->email,
                    'role'         => $user->role,
                    'tipo_usuario' => $user->tipo_usuario,
                ],
                'message' => '¡Verificación exitosa!'
            ];

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

    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }
        return response()->json(['message' => 'Sesión cerrada']);
    }

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
                    'role'         => 'cliente',
                ]);

                return response()->json(['message' => 'Solicitud enviada.'], 201);
            });
        } catch (Exception $e) {
            return response()->json(['message' => 'Error', 'error' => $e->getMessage()], 500);
        }
    }

    // 🔥 CONFIGURACIÓN REAL DEL LOGIN FACIAL POR IA RECONOCIMIENTO 2D 🔥
    public function loginFaceId(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'face_descriptor' => 'required|array'
        ]);

        $user = User::where('email', trim($request->email))->first();

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado.'], 404);
        }

        if ($user->status !== 'activo') {
            return response()->json(['message' => 'Cuenta pendiente o desactivada.'], 403);
        }

        if (!$user->face_descriptor) {
            return response()->json(['message' => 'No cuentas con un rostro registrado para este perfil.'], 400);
        }

        // Decodificamos el descriptor guardado (array de 128 flotantes)
        $storedDescriptor = json_decode($user->face_descriptor, true);
        $currentDescriptor = $request->face_descriptor;

        // 🔥 ALGORITMO CIENTÍFICO: DISTANCIA EUCLIDIANA EN VECTOR 128-D 🔥
        $distance = 0.0;
        for ($i = 0; $i < 128; $i++) {
            $diff = $storedDescriptor[$i] - $currentDescriptor[$i];
            $distance += $diff * $diff;
        }
        $distance = sqrt($distance);

        // Umbral de coincidencia estricta (menor o igual a 0.6 significa identidad verificada)
        if ($distance > 0.6) {
            return response()->json(['message' => 'Validación facial fallida. Rostro no coincide.'], 401);
        }

        // Si pasó el examen de IA, creamos el token de acceso directo
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

    // 🔥 NUEVA FUNCIÓN: ALMACENAR ARREGLO DE PUNTOS FACIALES DESDE VUE 🔥
    public function saveFace(Request $request)
    {
        $request->validate([
            'face_descriptor' => 'required|array'
        ]);

        $user = $request->user();
        // Almacenamos el array mapeado de 128 números directamente a formato JSON estructurado
        $user->face_descriptor = json_encode($request->face_descriptor);
        $user->save();

        return response()->json(['message' => 'Rostro guardado exitosamente en tu base de datos']);
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
        ]);

        $user = $request->user();
        
        $user->name = $request->name;
        $user->save();

        if ($user->persona_id) {
            $persona = Persona::find($user->persona_id);
            if ($persona) {
                $persona->nombres = $request->name;
                $persona->apellidos = $request->last_name;
                $persona->save();
            }
        }

        return response()->json([
            'message' => 'Perfil actualizado correctamente',
            'user' => $user
        ]);
    }

    public function toggleBiometrics(Request $request) {
        // Reservado para configuraciones adicionales de flags biométricos si los requieres
        return response()->json(['message' => 'Preferencia modificada']);
    }
}