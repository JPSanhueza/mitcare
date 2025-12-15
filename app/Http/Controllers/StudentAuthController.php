<?php

namespace App\Http\Controllers;

use App\Mail\StudentResetPasswordMail;
use App\Models\Student;
use App\Models\StudentPasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StudentAuthController extends Controller
{
    /**
     * Mostrar formulario de login de estudiante.
     */
    public function showLoginForm()
    {
        // Si ya está logueado, lo mandamos directo a sus certificados
        if (session()->has('student_id')) {
            return redirect()->route('student.certificates');
        }

        return view('student.auth.login');
    }

    /**
     * Procesar login de estudiante (EMAIL + contraseña).
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Debes ingresar tu correo electrónico.',
            'email.email' => 'Debes ingresar un correo válido.',
            'password.required' => 'Debes ingresar tu contraseña.',
        ]);

        // Normalizar email
        $email = strtolower(trim($data['email']));

        /** @var Student|null $student */
        $student = Student::where('email', $email)->first();

        if (!$student || !Hash::check($data['password'], $student->password)) {
            throw ValidationException::withMessages([
                'email' => 'Correo o contraseña incorrectos.',
            ]);
        }

        // Guardamos la sesión de estudiante
        $request->session()->put('student_id', $student->id);

        // Si debe cambiar contraseña, lo mandamos a la vista de cambio obligatorio
        if ($student->must_change_password) {
            return redirect()->route('student.password.force');
        }

        // Si no, va normal a sus certificados
        return redirect()->route('student.certificates');
    }

    /**
     * Cerrar sesión de estudiante.
     */
    public function logout(Request $request)
    {
        $request->session()->forget('student_id');

        return redirect()
            ->route('student.login')
            ->with('success', 'Has cerrado sesión correctamente.');
    }

    /* ============================================================
     *   OLVIDÉ MI CONTRASEÑA (flujo por token)
     * ============================================================ */

    /**
     * Mostrar formulario "Olvidé mi contraseña" (solo email).
     */
    public function showForgotForm()
    {
        return view('student.auth.forgot-password');
    }

    /**
     * Procesar envío de link de reset de contraseña.
     */
    public function sendResetLink(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Debes ingresar tu correo electrónico.',
            'email.email' => 'Debes ingresar un correo válido.',
        ]);

        $email = strtolower(trim($data['email']));

        /** @var Student|null $student */
        $student = Student::where('email', $email)->first();

        // Por seguridad, no revelamos si el correo existe o no
        if (!$student) {
            return back()->with('status', 'Si el correo existe en el sistema, se ha enviado un enlace para restablecer la contraseña.');
        }

        // Crear token
        $token = Str::random(64);

        StudentPasswordReset::create([
            'student_id' => $student->id,
            'token' => $token,
            'type' => 'reset',
            'expires_at' => now()->addHours(2),
        ]);

        // Enviar mail con link de reset
        Mail::to($student->email)->send(
            new StudentResetPasswordMail($student, $token)
        );

        return back()->with('status', 'Si el correo existe en el sistema, se ha enviado un enlace para restablecer la contraseña.');
    }

    /**
     * Mostrar formulario para definir nueva contraseña desde token.
     * GET /certificados/definir-clave?token=XXX&email=alumno@example.com
     */
    public function showSetPasswordForm(Request $request)
    {
        $token = $request->query('token');
        $email = strtolower(trim($request->query('email', '')));

        if (!$token || !$email) {
            abort(404);
        }

        /** @var Student|null $student */
        $student = Student::where('email', $email)->first();

        if (!$student) {
            abort(404);
        }

        /** @var StudentPasswordReset|null $record */
        $record = StudentPasswordReset::where('token', $token)
            ->where('student_id', $student->id)
            ->whereNull('used_at')
            ->first();

        if (!$record || ($record->expires_at && $record->expires_at->isPast())) {
            return view('student.auth.token-expired');
        }

        return view('student.auth.set-password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    /**
     * Guardar la nueva contraseña enviada desde el formulario con token.
     */
    public function setPassword(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'email.required' => 'Debes ingresar tu correo electrónico.',
            'email.email' => 'Debes ingresar un correo válido.',
            'new_password.required' => 'Debes ingresar una nueva contraseña.',
            'new_password.min' => 'La nueva contraseña debe tener al menos :min caracteres.',
            'new_password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ]);

        $email = strtolower(trim($data['email']));
        $token = $data['token'];

        /** @var Student|null $student */
        $student = Student::where('email', $email)->first();

        if (!$student) {
            throw ValidationException::withMessages([
                'email' => 'El enlace no es válido o ha expirado.',
            ]);
        }

        /** @var StudentPasswordReset|null $record */
        $record = StudentPasswordReset::where('token', $token)
            ->where('student_id', $student->id)
            ->whereNull('used_at')
            ->first();

        if (!$record || ($record->expires_at && $record->expires_at->isPast())) {
            throw ValidationException::withMessages([
                'email' => 'El enlace no es válido o ha expirado.',
            ]);
        }

        // Actualizar contraseña
        $student->password = $data['new_password'];
        $student->must_change_password = false;
        $student->save();

        // Marcar token como usado
        $record->used_at = now();
        $record->save();

        // Loguear automáticamente al estudiante
        $request->session()->put('student_id', $student->id);

        return redirect()
            ->route('student.certificates')
            ->with('success', 'Tu contraseña ha sido definida correctamente.');
    }

    /* ============================================================
     *   CAMBIO FORZADO DE CONTRASEÑA (must_change_password)
     * ============================================================ */

    public function showForceChangeForm(Request $request)
    {
        $studentId = $request->session()->get('student_id');

        if (!$studentId) {
            return redirect()->route('student.login');
        }

        $student = Student::findOrFail($studentId);

        return view('student.auth.force-change-password', [
            'student' => $student,
        ]);
    }

    public function forceChangePassword(Request $request)
    {
        $studentId = $request->session()->get('student_id');

        if (!$studentId) {
            return redirect()->route('student.login');
        }

        /** @var Student $student */
        $student = Student::findOrFail($studentId);

        $data = $request->validate([
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'new_password.required' => 'Debes ingresar una nueva contraseña.',
            'new_password.min' => 'La nueva contraseña debe tener al menos :min caracteres.',
            'new_password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ]);

        $student->password = $data['new_password'];
        $student->must_change_password = false;
        $student->save();

        return redirect()
            ->route('student.certificates')
            ->with('success', 'Tu contraseña ha sido actualizada correctamente.');
    }
}
