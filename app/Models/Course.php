<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre', 'nombre_diploma', 'slug', 'descripcion', 'subtitulo',
        'price', 'pre_sale', 'total_hours', 'hours_description',
        'is_active', 'order', 'published_at', 'ficha',
        'capacity', 'modality', 'start_at', 'end_at', 'teachers_type',
        'location', 'image', 'external_url', 'moodle_course_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'pre_sale' => 'boolean',
        'published_at' => 'datetime',
        'start_at' => 'date',
        'end_at' => 'date',
    ];

    /** Generar slug automáticamente */
    protected static function boot()
    {
        parent::boot();

        // Crear slug al crear el registro
        static::creating(function ($course) {
            if (empty($course->slug)) {
                $course->slug = static::generateUniqueSlug(strip_tags($course->nombre));
            }
        });

        // Actualizar slug si cambia el nombre
        static::updating(function ($course) {
            // Solo regenerar si el nombre cambió y el usuario NO editó el slug manualmente
            if ($course->isDirty('nombre') && $course->isDirty('slug') === false) {
                $course->slug = static::generateUniqueSlug(strip_tags($course->nombre), $course->id);
            }
        });
    }

    // Función auxiliar para generar slugs únicos
    public static function generateUniqueSlug($name, $ignoreId = null)
    {
        $slug = Str::slug($name);
        $original = $slug;
        $counter = 1;

        while (
            static::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $original.'-'.$counter++;
        }

        return $slug;
    }

    /** Scopes útiles */
    public function scopePublicado($q)
    {
        return $q->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    /** Propiedad calculada para saber si tiene cupos */
    public function getTieneCuposAttribute(): bool
    {
        return is_null($this->capacity) || $this->capacity > 0;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function students()
    {
        return $this->belongsToMany(Student::class)
            ->using(CourseStudent::class)
            ->withPivot([
                'enrolled_at',
                'final_grade',
                'approved',
                'attendance',
                'diploma_issued',
            ])
            ->withTimestamps();
    }

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class)
            ->using(CourseTeacher::class)
            ->withPivot(['role'])
            ->withTimestamps();
    }
}
