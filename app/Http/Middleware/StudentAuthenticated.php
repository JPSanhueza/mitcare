<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StudentAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->has('student_id')) {
            return redirect()
                ->route('student.login')
                ->with('error', 'Debes iniciar sesión para acceder a tus certificados.');
        }

        return $next($request);
    }
}
