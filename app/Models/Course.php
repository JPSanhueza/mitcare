<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre', 'slug', 'descripcion', 'subtitulo',
        'price', 'is_active', 'order', 'published_at',
        'capacity', 'modality', 'start_at', 'end_at',
        'location', 'image', 'external_url', 'moodle_course_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'published_at' => 'datetime',
        'start_at' => 'date',
        'end_at' => 'date',
    ];

    /** Generar slug automáticamente */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($course) {
            if (empty($course->slug)) {
                $course->slug = Str::slug($course->nombre);

                // Evitar duplicados simples
                $original = $course->slug;
                $counter = 1;
                while (static::where('slug', $course->slug)->exists()) {
                    $course->slug = $original.'-'.$counter++;
                }
            }
        });
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
}
