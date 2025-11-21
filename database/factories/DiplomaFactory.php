<?php

namespace Database\Factories;

use App\Models\Diploma;
use App\Models\Course;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DiplomaFactory extends Factory
{
    protected $model = Diploma::class;

    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'student_id' => Student::factory(),
            'issued_at' => now()->subDays(rand(1, 20)),
            'final_grade' => rand(40, 70) / 10,
            'file_path' => 'diplomas/' . Str::random(10) . '.pdf',
            'verification_code' => 'DIP-' . strtoupper(Str::random(8)),
            'qr_path' => 'qrs/' . Str::random(8) . '.png',
            // ❌ sin template_id
        ];
    }
}
