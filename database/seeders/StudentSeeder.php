<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 80 estudiantes con RUT válido, password fija de test, etc.
        Student::factory()->count(80)->create();
    }
}
