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
        'especialidad',
        'email',
        'telefono',
        'organization_id',
        'signature',
        'is_active',
        'order',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
