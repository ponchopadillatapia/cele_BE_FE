<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Registrar nuevo usuario y enviar email de verificación.
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => 'resident',
            'department_id' => $request->department_id,
        ]);

        event(new Registered($user));

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Usuario registrado. Revisa tu correo para verificar tu cuenta.',
            'user' => $user->only('id', 'name', 'email', 'role'),
            'token' => $token,
        ], 201);
    }

    /**
     * Iniciar sesión y generar token.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Credenciales incorrectas.',
            ], 401);
        }

        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Debes verificar tu correo electrónico antes de iniciar sesión.',
                'email_verified' => false,
            ], 403);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Sesión iniciada.',
            'user' => $user->only('id', 'name', 'email', 'role', 'department_id'),
            'token' => $token,
        ]);
    }

    /**
     * Cerrar sesión (revocar token actual).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada.']);
    }

    /**
     * Obtener usuario autenticado.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user()->only('id', 'name', 'email', 'role', 'department_id'));
    }

    /**
     * Reenviar email de verificación.
     */
    public function resendVerification(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado.'], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'El correo ya está verificado.']);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'Email de verificación reenviado.']);
    }

    /**
     * Verificar email con ID y hash.
     */
    public function verifyEmail(Request $request, int $id, string $hash): JsonResponse
    {
        $user = User::findOrFail($id);

        if (!hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            return response()->json(['message' => 'Link de verificación inválido.'], 403);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'El correo ya está verificado.']);
        }

        $user->markEmailAsVerified();

        return response()->json(['message' => 'Correo verificado exitosamente.']);
    }
}
