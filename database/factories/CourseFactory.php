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
            'Manipulación de Alimentos'
        ]);

        $start = Carbon::now()->subDays(rand(10, 60));
        $end = (clone $start)->addDays(rand(3, 10));
        $hours = rand(20, 50);

        return [
            'nombre' => $title,
            'slug' => Str::slug($title) . '-' . Str::random(5),
            'subtitulo' => $this->faker->sentence(6),
            'descripcion' => $this->faker->paragraph(4),
            'price' => $this->faker->randomElement([0, 25000, 35000, 50000]),
            'total_hours' => $hours,
            'hours_description' => $hours . ' horas cronológicas',
            'is_active' => true,
            'order' => rand(1, 100),
            'published_at' => now()->subDays(rand(1, 20)),
            'capacity' => rand(15, 30),
            'modality' => $this->faker->randomElement(['online', 'presencial']),
            'start_at' => $start,
            'end_at' => $end,
            'location' => $this->faker->address,
            'image' => null,
            'external_url' => null,
            'moodle_course_id' => null,
        ];
    }
}
