<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // POST /api/login
    public function login(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email'    => ['required', 'email'],
                'password' => ['required', 'string'],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors()[array_key_first($e->errors())][0],
            ], 422);
        }

        $usuario = Usuario::where('email', $validated['email'])->first();

        if (!$usuario || !Hash::check($validated['password'], $usuario->password_hash)) {
            return response()->json([
                'success' => false,
                'message' => 'Correo o contraseña incorrectos.',
            ], 401);
        }

        $request->session()->regenerate();
        $request->session()->put('user_id', $usuario->id_usuario);

        return response()->json([
            'success' => true,
            'message' => 'Sesión iniciada correctamente.',
            'user'    => $this->userPayload($usuario),
        ]);
    }

    // POST /api/logout
    public function logout(Request $request): JsonResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['success' => true, 'message' => 'Sesión cerrada.']);
    }

    // GET /api/me
    public function me(Request $request): JsonResponse
    {
        $userId = $request->session()->get('user_id');

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'No autenticado.'], 401);
        }

        $usuario = Usuario::find($userId);

        if (!$usuario) {
            $request->session()->invalidate();
            return response()->json(['success' => false, 'message' => 'Usuario no encontrado.'], 401);
        }

        return response()->json([
            'success' => true,
            'user'    => $this->userPayload($usuario),
        ]);
    }

    private function userPayload(Usuario $u): array
    {
        return [
            'id'        => $u->id_usuario,
            'name'      => $u->nombre,
            'email'     => $u->email,
            'tipo'      => $u->tipo,
            'vendor_id' => $u->tipo === 'vendedor' ? $u->id_usuario : null,
        ];
    }
}
