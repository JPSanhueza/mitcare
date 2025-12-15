<?php

namespace App\Mail;

use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StudentInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Student $student;
    public string $token;

    public function __construct(Student $student, string $token)
    {
        $this->student = $student;
        $this->token = $token;
    }

    public function build()
    {
        $url = route('student.password.set', [
            'token' => $this->token,
            'email' => $this->student->email,
        ]);

        return $this->subject('Crea tu contraseña para acceder a tus certificados')
            ->view('mail.student.invitation', [  
                'student' => $this->student,
                'url' => $url,
            ]);
    }
}
