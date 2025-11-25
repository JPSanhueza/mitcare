<?php

namespace Database\Factories;

use App\Models\Student;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        // Creamos una instancia de Faker directamente
        $faker = FakerFactory::create('es_CL'); // o el locale que quieras

        $nombre = $faker->firstName();
        $apellido = $faker->lastName();
        $rut = $this->generateValidRut();

        return [
            'nombre' => $nombre,
            'apellido' => $apellido,
            'email' => $faker->unique()->safeEmail(),
            'rut' => $rut,
            'password' => Hash::make('Password123!'),
            'telefono' => $faker->numerify('+56 9 ########'),
            'direccion' => $faker->address(),
        ];
    }

    private function generateValidRut(): string
    {
        $number = rand(10000000, 26000000);
        return $this->computeDv($number);
    }

    private function computeDv(int $num): string
    {
        $cuerpo = (string) $num;
        $suma = 0;
        $factor = 2;

        for ($i = strlen($cuerpo) - 1; $i >= 0; $i--) {
            $suma += intval($cuerpo[$i]) * $factor;
            $factor = ($factor === 7) ? 2 : $factor + 1;
        }

        $dv = 11 - ($suma % 11);

        if ($dv == 11) {
            $dv = '0';
        } elseif ($dv == 10) {
            $dv = 'K';
        }

        return $cuerpo . $dv;
    }
}
