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

class SendStudentInvitationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var array<int, array{student_id:int, token:string}>
     */
    public array $invitations;

    /**
     * Tiempo máximo de ejecución del job (en segundos)
     */
    public $timeout = 300;

    /**
     * Create a new job instance.
     *
     * @param  array<int, array{student_id:int, token:string}>  $invitations
     */
    public function __construct(array $invitations)
    {
        $this->invitations = $invitations;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach ($this->invitations as $invite) {
            $studentId = $invite['student_id'] ?? null;
            $token = $invite['token'] ?? null;

            if (!$studentId || !$token) {
                continue;
            }

            $student = Student::find($studentId);

            if (!$student || !$student->email) {
                continue;
            }

            try {
                Mail::to($student->email)->send(
                    new StudentInvitationMail($student, $token)
                );

                // 💡 Pequeña pausa para no saturar Mailtrap / SMTP
                // Ajusta si quieres más o menos agresivo
                usleep(300000); // 0.3 segundos

            } catch (\Throwable $e) {
                Log::error('Error al enviar correo de invitación (job)', [
                    'student_id' => $student->id,
                    'email' => $student->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
