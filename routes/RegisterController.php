<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    public function store(Request $request)
    {
        // Validación de datos según los límites de la tabla SQL
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:usuarios,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        // Inserción en la tabla 'usuarios' usando los nombres de columna del .sql
        DB::table('usuarios')->insert([
            'id_usuario' => (string) Str::uuid(),
            'tipo' => 'comprador', // Valor por defecto según requerimiento
            'email' => $request->email,
            'password_hash' => Hash::make($request->password),
            'nombre' => $request->name,
        ]);

        return response()->json(['success' => true]);
    }
}