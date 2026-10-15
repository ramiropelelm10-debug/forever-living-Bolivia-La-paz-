<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FaceProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class FaceIdController extends Controller
{
    // Solo el personal del panel puede usar Face ID.
    private const ROLES_PERMITIDOS = ['admin', 'inventario'];

    /**
     * Distancia euclidiana entre dos descriptores faciales de 128 números.
     */
    private function distancia(array $a, array $b): float
    {
        $suma = 0.0;
        for ($i = 0; $i < 128; $i++) {
            $d = $a[$i] - $b[$i];
            $suma += $d * $d;
        }
        return sqrt($suma);
    }

    /**
     * ¿El usuario logueado ya registró su rostro?
     */
    public function status(Request $request)
    {
        return response()->json([
            'registrado' => FaceProfile::where('user_id', $request->user()->id)->exists(),
        ]);
    }

    /**
     * Registra el rostro del usuario logueado (3 a 5 capturas). Pide la contraseña para confirmar.
     */
    public function register(Request $request)
    {
        $user = $request->user();

        if (!in_array($user->role, self::ROLES_PERMITIDOS, true)) {
            return response()->json(['message' => 'Tu cuenta no puede usar Face ID.'], 403);
        }

        $request->validate([
            'password'        => 'required|string',
            'descriptors'     => 'required|array|min:3|max:5',
            'descriptors.*'   => 'required|array|size:128',
            'descriptors.*.*' => 'numeric',
        ]);

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Contraseña incorrecta.'], 422);
        }

        $descriptors = array_map(
            fn ($d) => array_map('floatval', array_values($d)),
            array_values($request->descriptors)
        );

        // Las capturas deben parecerse entre sí.
        $max = 0.0;
        for ($i = 0; $i < count($descriptors); $i++) {
            for ($j = $i + 1; $j < count($descriptors); $j++) {
                $max = max($max, $this->distancia($descriptors[$i], $descriptors[$j]));
            }
        }
        if ($max > config('faceid.max_distancia_registro')) {
            return response()->json([
                'message' => 'Las capturas no se parecen entre sí. Repite con buena luz y solo tu rostro frente a la cámara.',
            ], 422);
        }

        FaceProfile::updateOrCreate(
            ['user_id' => $user->id],
            ['descriptors' => $descriptors]
        );
        $user->update(['biometrics_enabled' => true]);

        return response()->json(['message' => 'Face ID registrado correctamente.']);
    }

    /**
     * Elimina el rostro registrado del usuario logueado.
     */
    public function destroy(Request $request)
    {
        $user = $request->user();

        FaceProfile::where('user_id', $user->id)->delete();
        $user->update(['biometrics_enabled' => false]);

        return response()->json(['message' => 'Face ID desactivado.']);
    }

    /**
     * Login con rostro: el frontend manda email + descriptor y AQUÍ se compara contra el guardado.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'        => 'required|email',
            'descriptor'   => 'required|array|size:128',
            'descriptor.*' => 'numeric',
        ]);

        // Mismo mensaje para cualquier fallo, para no revelar qué cuentas existen.
        $fallo = response()->json(['message' => 'No se pudo verificar tu identidad.'], 401);

        $user = User::where('email', trim($request->email))->first();

        if (
            !$user
            || $user->status !== 'activo'
            || !$user->biometrics_enabled
            || !in_array($user->role, self::ROLES_PERMITIDOS, true)
        ) {
            return $fallo;
        }

        $perfil = FaceProfile::where('user_id', $user->id)->first();
        if (!$perfil || empty($perfil->descriptors)) {
            return $fallo;
        }

        $enVivo = array_map('floatval', array_values($request->descriptor));

        $mejor = min(array_map(
            fn ($guardado) => $this->distancia($enVivo, $guardado),
            $perfil->descriptors
        ));

        if ($mejor > config('faceid.umbral')) {
            return $fallo;
        }

        $token = $user->createToken('forever_access_token')->plainTextToken;

        return response()->json([
            'message' => 'Login biométrico exitoso',
            'token'   => $token,
            'user'    => [
                'id'           => $user->id,
                'name'         => $user->name,
                'email'        => $user->email,
                'role'         => $user->role,
                'tipo_usuario' => $user->tipo_usuario,
            ],
        ]);
    }
}