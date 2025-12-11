<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\StudentResource;
use App\Jobs\SendStudentInvitationMailJob;
use App\Models\Student;
use App\Models\StudentPasswordReset;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    /**
     * Después de crear el registro desde el formulario de Filament,
     * generamos el token de invitación y encolamos el job
     * igual que en el import.
     */
    protected function afterCreate(): void
    {
        /** @var Student $student */
        $student = $this->record;

        // Si no tiene correo, no podemos enviar invitación
        if (blank($student->email)) {
            return;
        }

        // Generar token de invitación
        $token = Str::random(64);

        StudentPasswordReset::create([
            'student_id' => $student->id,
            'token'      => $token,
            'type'       => 'invite',
            'expires_at' => now()->addDays(3),
        ]);

        // Encolar el job para enviar el correo
        SendStudentInvitationMailJob::dispatch($student->id, $token)
            ->delay(now()->addSeconds(1)); // pequeño delay por cariño al SMTP :)
    }
}
