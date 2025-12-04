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
        'organization_name',
        'signature',
        'is_active',
        'order',
    ];
    public function courses()
    {
        return $this->belongsToMany(Course::class)
            ->using(CourseTeacher::class)
            ->withPivot(['role'])
            ->withTimestamps();
    }

}
