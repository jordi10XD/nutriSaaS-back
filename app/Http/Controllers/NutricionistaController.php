<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class NutricionistaController extends Controller
{
    private function getEmpresaId(Request $request)
    {
        return $request->header('X-Empresa-ID');
    }

    public function index(Request $request)
    {
        $empresaId = $this->getEmpresaId($request);

        if (!$empresaId) {
            return response()->json(['message' => 'Falta encabezado X-Empresa-ID'], 400);
        }

        $nutris = User::where('rol', 'nutricionista')->get();

        return response()->json(['data' => $nutris], 200);
    }

    public function store(Request $request)
    {
        $empresaId = $this->getEmpresaId($request);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'usuario' => 'required|string|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6'
        ]);

        $nutri = User::create([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'usuario' => $request->usuario,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'rol' => 'nutricionista',
            'estado' => 1
        ]);

        return response()->json(['data' => $nutri, 'message' => 'Nutricionista creado'], 201);
    }

    public function update(Request $request, $id)
    {
        $empresaId = $this->getEmpresaId($request);

        $nutri = User::where('rol', 'nutricionista')->findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'usuario' => ['required', 'string', Rule::unique('users')->ignore($nutri->id)],
            'email' => ['required', 'email', Rule::unique('users')->ignore($nutri->id)],
            'password' => 'nullable|string|min:6'
        ]);

        $nutri->nombre = $request->nombre;
        $nutri->apellido = $request->apellido;
        $nutri->usuario = $request->usuario;
        $nutri->email = $request->email;

        if ($request->filled('password')) {
            $nutri->password = Hash::make($request->password);
        }

        $nutri->save();

        return response()->json(['data' => $nutri, 'message' => 'Nutricionista actualizado']);
    }

    public function destroy(Request $request, $id)
    {
        $empresaId = $this->getEmpresaId($request);

        $nutri = User::where('rol', 'nutricionista')->findOrFail($id);

        $nutri->delete();

        return response()->json(['message' => 'Nutricionista eliminado']);
    }
}
