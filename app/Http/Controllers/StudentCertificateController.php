<?php

namespace App\Http\Controllers;

use App\Models\Diploma;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentCertificateController extends Controller
{
    /**
     * Panel principal: lista de diplomas del estudiante logueado.
     */
    public function index(Request $request)
    {
        $studentId = $request->session()->get('student_id');

        /** @var Student $student */
        $student = Student::with(['diplomas.course'])->findOrFail($studentId);

        $diplomas = $student->diplomas()
            ->with('course')
            ->orderByDesc('issued_at')
            ->get();

        return view('student.certificates.index', [
            'student' => $student,
            'diplomas' => $diplomas,
        ]);
    }

    /**
     * Descargar un diploma específico del estudiante.
     */
    public function download(Request $request, Diploma $diploma)
    {
        $studentId = $request->session()->get('student_id');

        // Evitar que un estudiante descargue diplomas de otro
        if ($diploma->student_id !== $studentId) {
            abort(403, 'No tienes permiso para descargar este diploma.');
        }

        // Asumimos que file_path es la ruta en el disco 'public'
        if (! Storage::disk('public')->exists($diploma->file_path)) {
            abort(404, 'El archivo del diploma no se encuentra disponible.');
        }

        $courseName = $diploma->course->nombre ?? 'curso';
        $date = optional($diploma->issued_at)->format('Ymd') ?? 'sin-fecha';

        $filename = 'diploma-' . str_replace(' ', '-', strtolower($courseName)) . '-' . $date . '.pdf';

        return Storage::disk('public')->download($diploma->file_path, $filename);
    }
}
