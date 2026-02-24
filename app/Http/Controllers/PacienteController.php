<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Paciente;

class PacienteController extends Controller
{
    public function index(Request $request)
    {
        $pacientes = Paciente::all();
        return response()->json($pacientes, 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
        ]);

        $paciente = new Paciente();
        $paciente->fill($request->all());
        // Auto-generate full name
        $paciente->nombre_completo = $request->nombre . ' ' . $request->apellido;
        $paciente->save();

        return response()->json($paciente, 201);
    }
}
