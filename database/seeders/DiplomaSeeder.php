<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Diploma;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DiplomaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpia diplomas previos (sin truncar por FKs)
        Diploma::query()->delete();

        // Buscar en el pivot las inscripciones aprobadas con diploma_issued = true
        $rows = DB::table('course_student')
            ->where('approved', true)
            ->where('diploma_issued', true)
            ->get();

        $count = 0;

        foreach ($rows as $row) {
            $code = 'DIP-' . strtoupper(Str::random(8));

            Diploma::create([
                'course_id' => $row->course_id,
                'student_id' => $row->student_id,
                'issued_at' => Carbon::now()->subDays(rand(1, 20)),
                'final_grade' => $row->final_grade,
                'file_path' => "diplomas/{$code}.pdf",
                'verification_code' => $code,
                'qr_path' => "qrs/{$code}.png",
                // 👇 OJO: ya no incluimos template_id
            ]);

            $count++;
        }

        $this->command?->info("✅ Se generaron {$count} diplomas de prueba.");
    }
}
