<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'nombre',
        'logo_path',
        'nombre_bd',
        'estado'
    ];
}
