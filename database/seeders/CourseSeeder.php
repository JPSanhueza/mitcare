<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 10 cursos con fechas coherentes, horas, etc.
        Course::factory()->count(10)->create();
    }
}
