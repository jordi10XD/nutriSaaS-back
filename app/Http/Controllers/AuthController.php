<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'usuario' => 'required|string',
            'password' => 'required|string',
            'id_empresa' => 'nullable|integer'
        ]);

        $connection = 'master';
        if ($request->filled('id_empresa')) {
            $empresa = \App\Models\Company::on('master')->find($request->id_empresa);
            if (!$empresa) {
                return response()->json(['message' => 'Empresa no encontrada'], 404);
            }
            \config(['database.connections.tenant.database' => $empresa->nombre_bd]);
            $connection = 'tenant';
        }

        $user = User::on($connection)->where('usuario', $request->usuario)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        /** @var \App\Models\User $user */
        if (!$user->estado) {
            return response()->json([
                'message' => 'Cuenta inactiva'
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'nombre' => $user->nombre,
                'apellido' => $user->apellido,
                'usuario' => $user->usuario,
                'rol' => $user->rol
            ]
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada correctamente']);
    }
}
