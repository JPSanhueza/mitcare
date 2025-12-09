<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CourseRelationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = Course::all();
        $students = Student::all();
        $teachers = Teacher::all();

        if ($courses->isEmpty() ||  $teachers->isEmpty() || $students->isEmpty()) {
            $this->command?->warn('⚠️ Faltan cursos, estudiantes o docentes para relacionar.');
            return;
        }

        // Limpia pivots sin romper FKs (delete en vez de truncate)
        DB::table('course_student')->delete();
        DB::table('course_teacher')->delete();

        /* ============================
         *  ESTUDIANTES POR CURSO
         * ============================ */
        foreach ($courses as $course) {
            // Entre 10 y 40 estudiantes por curso
            $numStudents = rand(10, 40);
            $studentsForCourse = $students->random($numStudents);

            foreach ($studentsForCourse as $student) {
                $startAt = $course->start_at
                    ? Carbon::parse($course->start_at)
                    : now()->subDays(rand(10, 60));

                $enrolledAt = (clone $startAt)->subDays(rand(1, 15));

                $finalGrade = rand(35, 70) / 10;  // 3.5 a 7.0
                $approved = $finalGrade >= 4.0;
                $attendance = rand(50, 100);

                // Solo se emite diploma si aprobó y tiene buena asistencia
                $diplomaIssued = $approved && $attendance >= 75 && rand(0, 100) < 80;

                DB::table('course_student')->insert([
                    'course_id' => $course->id,
                    'student_id' => $student->id,
                    'enrolled_at' => $enrolledAt,
                    'final_grade' => $finalGrade,
                    'approved' => $approved,
                    'attendance' => $attendance,
                    'diploma_issued' => $diplomaIssued,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        /* ============================
         *  DOCENTES POR CURSO
         * ============================ */
        $roles = [
            'Relator/a principal',
            'Co-relator/a',
            'Docente invitado/a',
        ];

        foreach ($courses as $course) {
            $numTeachers = rand(1, min(3, $teachers->count()));
            $teachersForCourse = $teachers->random($numTeachers);

            foreach ($teachersForCourse as $index => $teacher) {
                DB::table('course_teacher')->insert([
                    'course_id' => $course->id,
                    'teacher_id' => $teacher->id,
                    'role' => $roles[min($index, count($roles) - 1)],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command?->info('✅ Relaciones curso-estudiante y curso-docente creadas.');
    }
}
