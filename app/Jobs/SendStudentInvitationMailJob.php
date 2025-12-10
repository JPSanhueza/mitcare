<?php

namespace App\Jobs;

use App\Mail\StudentInvitationMail;
use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendStudentInvitationMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $studentId;
    public string $token;

    /**
     * Tiempo máximo de ejecución del job (en segundos)
     */
    public $timeout = 120;

    public function __construct(int $studentId, string $token)
    {
        $this->studentId = $studentId;
        $this->token = $token;
    }

    public function handle(): void
    {
        $student = Student::find($this->studentId);

        if (!$student || !$student->email) {
            return;
        }

        try {
            Mail::to($student->email)->send(
                new StudentInvitationMail($student, $this->token)
            );
        } catch (\Throwable $e) {
            Log::error('Error al enviar correo de invitación (job por estudiante)', [
                'student_id' => $this->studentId,
                'email' => $student->email ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
