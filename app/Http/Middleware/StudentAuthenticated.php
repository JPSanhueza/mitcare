<?php

namespace App\Http\Middleware;

use App\Models\Student;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StudentAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $studentId = $request->session()->get('student_id');

        // Si no hay sesión, redirigir al login
        if (!$studentId) {
            return redirect()
                ->route('student.login')
                ->with('error', 'Debes iniciar sesión para acceder a tus certificados.');
        }

        /** @var Student|null $student */
        $student = Student::find($studentId);

        // Si el student ya no existe (borrado, etc.), limpiar sesión y mandar a login
        if (!$student) {
            $request->session()->forget('student_id');

            return redirect()
                ->route('student.login')
                ->with('error', 'Tu sesión ha expirado. Por favor, inicia sesión nuevamente.');
        }

        // 🔴 Si debe cambiar contraseña...
        if ($student->must_change_password) {

            // ...solo permitimos estas rutas:
            if (
                $request->routeIs(
                    'student.password.force',
                    'student.password.force.submit',
                    'student.logout',
                )
            ) {
                // Permitir acceder a la vista de cambio obligatorio / POST de cambio / logout
                return $next($request);
            }

            // Para cualquier otra ruta protegida:
            // cerramos sesión y mandamos al login
            $request->session()->forget('student_id');

            return redirect()
                ->route('student.login')
                ->with('error', 'Debes iniciar sesión y cambiar tu contraseña antes de acceder a tus certificados.');
        }

        // Si no debe cambiar la contraseña, flujo normal
        return $next($request);
    }
}
