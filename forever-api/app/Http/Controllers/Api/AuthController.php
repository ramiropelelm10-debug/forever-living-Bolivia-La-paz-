<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * LOGIN TRADICIONAL
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Credenciales incorrectas'], 401);
        }

        $otp = rand(100000, 999999);
        $user->update(['otp_code' => $otp]);

        return response()->json([
            'message' => 'Código enviado',
            'require_2fa' => true,
            'requires_otp' => true,
            'biometrics_enabled' => (bool)$user->biometrics_enabled, 
            'code_debug' => $otp 
        ]);
    }

    /**
     * VERIFICACIÓN DE OTP
     */
    public function verifyOtp(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|email',
            'code'  => 'required|string'
        ]);

        $user = User::where('email', $fields['email'])
                    ->where('otp_code', trim($fields['code'])) 
                    ->first();

        if (!$user) {
            return response()->json(['message' => 'Código incorrecto'], 401);
        }

        $user->otp_code = null;
        $user->save();

        $token = $user->createToken('forever_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $user,
            'message' => '¡Acceso concedido!'
        ]);
    }

    /**
     * REGISTRO DE BIOMETRÍA
     */
    public function register(Request $request) 
    {
        $request->validate([
            'email'    => 'required|email|exists:users,email',
            'name'     => 'required|string',
            'id'       => 'required',
            'response' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();
        $user->update(['biometrics_enabled' => true]);

        return response()->json([
            'message' => '¡Biometría vinculada con éxito!',
            'biometrics_enabled' => true,
            'user' => $user
        ]);
    }

    /**
     * INTERRUPTOR TIPO IPHONE
     */
    public function toggleBiometrics(Request $request)
    {
        $request->validate(['enabled' => 'required|boolean']);
        
        $user = $request->user(); 
        $user->biometrics_enabled = $request->enabled;
        $user->save();

        return response()->json([
            'message' => $user->biometrics_enabled ? 'FaceID Activado' : 'FaceID Desactivado',
            'biometrics_enabled' => (bool)$user->biometrics_enabled
        ]);
    }

    /**
     * NUEVO: ACTUALIZAR FOTO DE CREDENCIAL DEL ADMIN
     * Esta es la función que captura la foto de la laptop y la guarda en el usuario.
     */
    public function updateProfile(Request $request) 
    {
        $request->validate([
            'foto_persona' => 'required' // El Base64 que viene de la cámara
        ]);

        $user = $request->user(); // Obtenemos al admin logueado
        $user->update([
            'foto_persona' => $request->foto_persona
        ]);

        return response()->json([
            'message' => 'Foto de credencial actualizada',
            'user' => $user
        ]);
    }

    /**
     * CERRAR SESIÓN
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada']);
    }
}