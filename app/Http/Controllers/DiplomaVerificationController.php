<?php

namespace App\Http\Controllers;

use App\Models\Diploma;
use Illuminate\Http\Request;

class DiplomaVerificationController extends Controller
{
    /**
     * Muestra la verificación pública de un diploma
     * a partir de su verification_code (escaneado desde el QR).
     */
    public function show(string $code)
    {
        // Por si el QR viene con minúsculas, normalizamos a mayúsculas
        $normalizedCode = strtoupper($code);

        $diploma = Diploma::with(['student', 'course'])
            ->where('verification_code', $normalizedCode)
            ->first();

        if (!$diploma) {
            // Vista para códigos inválidos o no encontrados
            return view('diplomas.verify-invalid', [
                'code' => $normalizedCode,
            ]);
        }

        // Si en el futuro agregas estados (ej: revoked), aquí se valida:
        // if ($diploma->status === 'revoked') { ... }

        return view('diplomas.verify', [
            'diploma' => $diploma,
        ]);
    }
}
