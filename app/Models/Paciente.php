<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    protected $fillable = [
        'nombre',
        'apellido',
        'nombre_completo',
        'cedula',
        'sexo',
        'edad',
        'peso',
        'altura',
        'ocupacion',
        'tipoConsulta'
    ];
}
