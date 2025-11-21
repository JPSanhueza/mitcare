<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Teacher extends Model
{
    use HasFactory;
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
