<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        $title = $this->faker->randomElement([
            'Prevención de Riesgos Laborales',
            'Gestión de Bodegas',
            'Atención al Cliente',
            'Trabajo en Altura',
            'Computación Básica',
            'Manipulación de Alimentos',
        ]);

        // Fechas coherentes
        $start = Carbon::now()->subDays(rand(10, 60));
        $end   = (clone $start)->addDays(rand(3, 10));
        $hours = rand(20, 50);

        // Siempre <= start_at
        $publishedAt = (clone $start)->subDays(rand(0, 15));

        return [
            'nombre'            => $title,
            'nombre_diploma'    => $title . ' (Diploma)',   // 👈 NUEVO
            'slug'              => Str::slug($title) . '-' . Str::random(5),
            'subtitulo'         => $this->faker->sentence(6),
            'descripcion'       => $this->faker->paragraph(4),

            // Venta/publicación
            'price'             => $this->faker->randomElement([0, 25000, 35000, 50000]),
            'is_active'         => true,
            'order'             => rand(1, 100),
            'published_at'      => $publishedAt,
            'capacity'          => rand(15, 30),

            // Ejecución
            'modality'          => $this->faker->randomElement(['online', 'presencial', 'mixto']),
            'start_at'          => $start,
            'end_at'            => $end,

            // Datos adicionales
            'total_hours'       => $hours,
            'hours_description' => $hours . ' horas cronológicas',

            'location'          => $this->faker->address,
            'image'             => null,
            'external_url'      => null,
            'moodle_course_id'  => null,
        ];
    }
}
