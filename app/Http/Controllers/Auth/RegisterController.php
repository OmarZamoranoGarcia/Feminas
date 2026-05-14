<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    /**
     * POST /register
     *
     * Every new user is both a buyer and a seller from day one.
     * The optional `razon_social` and `rfc` fields let them set up
     * their seller profile at registration time.
     * Admin accounts are created via seeder/artisan only.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'         => ['required', 'string', 'max:100'],
            'email'        => ['required', 'string', 'email', 'max:255', 'unique:usuarios,email'],
            'password'     => ['required', 'string', 'min:8', 'confirmed'],
            'razon_social' => ['nullable', 'string', 'max:200'],
            'rfc'          => ['nullable', 'string', 'max:13', 'unique:usuarios,rfc'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $usuario = Usuario::create([
            'id_usuario'    => Str::uuid()->toString(),
            'is_admin'      => false,
            'email'         => $request->input('email'),
            'password_hash' => Hash::make($request->input('password')),
            'nombre'        => $request->input('name'),
            'razon_social'  => $request->input('razon_social') ?? null,
            'rfc'           => $request->input('rfc') ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Usuario creado correctamente. Ya puedes iniciar sesión.',
            'user'    => [
                'id'           => $usuario->id_usuario,
                'nombre'       => $usuario->nombre,
                'email'        => $usuario->email,
                'is_admin'     => false,
                'razon_social' => $usuario->razon_social,
            ],
        ]);
    }
}
