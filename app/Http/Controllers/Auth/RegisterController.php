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
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:usuarios,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
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
            'nombre'        => $request->input('name'),
            'email'         => $request->input('email'),
            'password_hash' => Hash::make($request->input('password')),
            'tipo'          => 'comprador',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Usuario creado correctamente. Ya puedes iniciar sesión.',
            'user'    => [
                'id'     => $usuario->id_usuario,
                'nombre' => $usuario->nombre,
                'email'  => $usuario->email,
                'tipo'   => $usuario->tipo,
            ],
        ]);
    }
}
