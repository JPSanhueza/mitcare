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
}
