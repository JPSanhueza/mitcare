<?php

namespace Database\Factories;

use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeacherFactory extends Factory
{
    protected $model = Teacher::class;

    public function definition(): array
    {
        $especialidades = [
            'Prevención de Riesgos',
            'Liderazgo',
            'Calidad de Servicio',
            'Gestión de Proyectos',
            'Computación Básica'
        ];

        return [
            'nombre' => $this->faker->firstName,
            'apellido' => $this->faker->lastName,
            'descripcion' => $this->faker->sentence(8),
            'foto' => null,
            'signature' => null,
            'especialidad' => $this->faker->randomElement($especialidades),
            'email' => $this->faker->unique()->safeEmail,
            'telefono' => $this->faker->numerify('+56 9 ########'),
            'organization' => $this->faker->company,
            'is_active' => true,
            'order' => $this->faker->numberBetween(1, 100),
        ];
    }
}
