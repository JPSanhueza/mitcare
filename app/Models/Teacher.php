<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $fillable = [
        'nombre',
        'apellido',
        'descripcion',
        'foto',
        'signature',
        'especialidad',
        'email',
        'telefono',
        'organization',
        'signature',
        'is_active',
        'order'
    ];
}
