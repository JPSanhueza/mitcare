<?php

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->company(),
            'logo'   => null, // o alguna ruta fake si quieres
        ];
    }
}
