<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
     * Procesar login de estudiante (RUT + contraseña).
     */
    public function login(Request $request)
    {
        $request->validate([
            'rut' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Normalizar RUT usando el helper del modelo
        $rut = Student::normalizeRut($request->input('rut'));

        if (!Student::isValidRut($rut)) {
            throw ValidationException::withMessages([
                'rut' => 'El RUT ingresado no es válido.',
            ]);
        }

        /** @var Student|null $student */
        $student = Student::where('rut', $rut)->first();

        if (!$student || !Hash::check($request->input('password'), $student->password)) {
            throw ValidationException::withMessages([
                'rut' => 'RUT o contraseña incorrectos.',
            ]);
        }

        // Guardamos la sesión de estudiante
        $request->session()->put('student_id', $student->id);

        //  Si debe cambiar contraseña, lo mandamos a la vista de cambio obligatorio
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

    /**
     * Mostrar formulario de recuperación de contraseña basada en RUT.
     */
    public function showResetForm()
    {
        return view('student.auth.reset-password');
    }

    /**
     * Procesar actualización de contraseña del estudiante.
     */
    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'rut' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $rut = Student::normalizeRut($data['rut']);

        if (!Student::isValidRut($rut)) {
            throw ValidationException::withMessages([
                'rut' => 'El RUT ingresado no es válido.',
            ]);
        }

        /** @var Student|null $student */
        $student = Student::where('rut', $rut)->first();

        if (!$student) {
            throw ValidationException::withMessages([
                'rut' => 'No se encontró un estudiante con ese RUT.',
            ]);
        }

        // El mutator del modelo se encarga de hashear
        $student->password = $data['new_password'];
        $student->save();

        return redirect()
            ->route('student.login')
            ->with('success', 'Tu contraseña ha sido actualizada. Ahora puedes iniciar sesión.');
    }
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

        // Asignar nueva contraseña (mutator se encarga del hash)
        $student->password = $data['new_password'];
        $student->must_change_password = false;
        $student->save();

        return redirect()
            ->route('student.certificates')
            ->with('success', 'Tu contraseña ha sido actualizada correctamente.');
    }

}
