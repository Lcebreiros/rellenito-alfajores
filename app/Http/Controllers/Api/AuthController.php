<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login del usuario y generación de token
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'string|nullable',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        // Verificar que el usuario esté activo
        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Esta cuenta está desactivada.'],
            ]);
        }

        // Generar token
        $deviceName = $request->device_name ?? 'android-app';
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login exitoso',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'hierarchy_level' => $user->hierarchy_level,
                    'subscription_level' => $user->subscription_level,
                    'is_active' => $user->is_active,
                ],
                'token' => $token,
            ],
        ], 200);
    }

    /**
     * Logout del usuario (revoca el token actual)
     */
    public function logout(Request $request)
    {
        // Revoca el token actual del usuario
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout exitoso',
        ], 200);
    }

    /**
     * Revoca todos los tokens del usuario
     */
    public function logoutAll(Request $request)
    {
        // Revoca todos los tokens del usuario
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Todos los dispositivos han sido desconectados',
        ], 200);
    }

    /**
     * Obtiene la información del usuario autenticado
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'hierarchy_level' => $user->hierarchy_level,
                'subscription_level' => $user->subscription_level,
                'is_active' => $user->is_active,
                'organization_context' => $user->organization_context,
                'created_at' => $user->created_at,
            ],
        ], 200);
    }

    /**
     * Registra un nuevo usuario (opcional, depende si quieres permitir registro desde la app)
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'device_name' => 'string|nullable',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'hierarchy_level' => User::HIERARCHY_COMPANY,
            'is_active' => true,
        ]);

        $deviceName = $request->device_name ?? 'android-app';
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Usuario registrado exitosamente',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'hierarchy_level' => $user->hierarchy_level,
                ],
                'token' => $token,
            ],
        ], 201);
    }
}
