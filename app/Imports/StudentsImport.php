<?php

namespace App\Imports;

use App\Jobs\SendStudentInvitationsJob;
use App\Models\Student;
use App\Models\StudentPasswordReset;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;

class StudentsImport implements ToCollection, WithHeadingRow
{
    protected int $created = 0;
    protected int $skippedExisting = 0;
    protected int $skippedMissingRequired = 0;
    protected int $invalidRut = 0;
    protected int $failed = 0;

    protected array $rowErrors = [];

    /** @var array<int, array{student_id:int, token:string}> */
    protected array $invitations = [];

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            $this->addRowError(
                type: 'empty_file',
                row: null,
                message: 'El archivo no tiene filas de datos.'
            );
            return;
        }

        // Tomamos los headers tal como los da WithHeadingRow
        $firstRow = $rows->first();
        $headers = array_keys($firstRow->toArray());

        // Tus nombres reales de columnas en el Excel (normalizados):
        // Nombre              -> nombre
        // Apellido(s)         -> apellidos
        // Dirección de correo -> direccion_de_correo
        $missing = [];

        if (!in_array('nombre', $headers, true)) {
            $missing[] = 'Nombre';
        }

        if (!in_array('apellidos', $headers, true)) {
            $missing[] = 'Apellido(s)';
        }

        if (!in_array('direccion_de_correo', $headers, true)) {
            $missing[] = 'Dirección de correo';
        }

        if (!empty($missing)) {
            $this->addRowError(
                type: 'missing_columns',
                row: null,
                message: 'Faltan columnas obligatorias en el Excel: ' . implode(', ', $missing)
            );
            // Si quieres abortar completamente:
            // return;
        }

        foreach ($rows as $index => $row) {
            $fila = $index + 2; // fila 1 = encabezados

            try {
                // Leer usando EXACTAMENTE los nombres que muestra tu log
                $nombre = trim((string) ($row['nombre'] ?? ''));
                $apellido = trim((string) ($row['apellidos'] ?? ''));
                $emailRaw = trim((string) ($row['direccion_de_correo'] ?? ''));

                // Opcionales
                $rutRaw = trim((string) ($row['rut'] ?? ''));
                $telefono = $row['telefono'] ?? null;
                $direccion = $row['direccion'] ?? null;

                // Validación mínima
                if ($nombre === '' || $apellido === '' || $emailRaw === '') {
                    $this->skippedMissingRequired++;
                    $this->addRowError(
                        type: 'missing_required',
                        row: $fila,
                        message: 'Faltan datos obligatorios (Nombre, Apellido(s) o Dirección de correo).'
                    );
                    Log::warning('Fila omitida (faltan campos obligatorios)', [
                        'fila' => $fila,
                        'row' => $row->toArray(),
                    ]);
                    continue;
                }

                // Normalizar/validar email
                $email = strtolower($emailRaw);
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $this->skippedMissingRequired++;
                    $this->addRowError(
                        type: 'invalid_email',
                        row: $fila,
                        message: "El correo '{$emailRaw}' no es válido."
                    );
                    Log::warning('Email inválido en import', [
                        'fila' => $fila,
                        'email' => $emailRaw,
                    ]);
                    continue;
                }

                // RUT opcional
                $rutNormalizado = null;
                if ($rutRaw !== '') {
                    $rutNormalizado = Student::normalizeRut($rutRaw);

                    if (!Student::isValidRut($rutNormalizado)) {
                        $this->invalidRut++;
                        $this->addRowError(
                            type: 'invalid_rut',
                            row: $fila,
                            message: "RUT inválido para Chile: {$rutRaw}. Se guarda el estudiante sin RUT."
                        );
                        Log::warning('RUT inválido en import', [
                            'fila' => $fila,
                            'rut' => $rutRaw,
                        ]);
                        $rutNormalizado = null;
                    } else {
                        if (Student::where('rut', $rutNormalizado)->exists()) {
                            $this->addRowError(
                                type: 'RUT duplicado',
                                row: $fila,
                                message: "Ya existe un estudiante con RUT {$rutNormalizado}. Se usará solo el email."
                            );
                            Log::info('RUT duplicado, se ignora y se continúa con email', [
                                'fila' => $fila,
                                'rut' => $rutNormalizado,
                            ]);
                            $rutNormalizado = null;
                        }
                    }
                }

                // Email debe ser único
                if (Student::where('email', $email)->exists()) {
                    $this->skippedExisting++;
                    $this->addRowError(
                        type: 'Email Duplicado',
                        row: $fila,
                        message: "Estudiante duplicado, ya existe el email {$email}."
                    );
                    Log::info('Estudiante ya existe, se omite (email duplicado)', [
                        'fila' => $fila,
                        'email' => $email,
                    ]);
                    continue;
                }

                // Crear estudiante
                $student = Student::create([
                    'nombre' => $nombre,
                    'apellido' => $apellido,
                    'rut' => $rutNormalizado, // puede ser null
                    'email' => $email,
                    'telefono' => $telefono ?: null,
                    'direccion' => $direccion ?: null,
                ]);

                // Token de invitación
                $token = Str::random(64);

                StudentPasswordReset::create([
                    'student_id' => $student->id,
                    'token' => $token,
                    'type' => 'invite',
                    'expires_at' => now()->addDays(3),
                ]);

                // 👉 NO enviamos el correo aquí
                // Solo guardamos la info para que el Job se encargue después
                $this->invitations[] = [
                    'student_id' => $student->id,
                    'token' => $token,
                ];

                $this->created++;

            } catch (ValidationException $e) {
                $this->failed++;
                $this->addRowError(
                    type: 'validation',
                    row: $fila,
                    message: 'Error de validación al crear el estudiante.',
                    context: $e->errors(),
                );
                Log::error('Error de validación al importar estudiante', [
                    'fila' => $fila,
                    'errores' => $e->errors(),
                ]);

            } catch (\Throwable $e) {
                $this->failed++;
                $this->addRowError(
                    type: 'exception',
                    row: $fila,
                    message: 'Error inesperado: ' . $e->getMessage(),
                );
                Log::error('Error inesperado al importar estudiante', [
                    'fila' => $fila,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // 🚀 Al terminar de procesar TODAS las filas, disparamos el Job
        if (!empty($this->invitations)) {
            SendStudentInvitationsJob::dispatch($this->invitations);
        }
    }

    public function getSummary(): array
    {
        return [
            'created' => $this->created,
            'skipped_existing' => $this->skippedExisting,
            'skipped_missing_required' => $this->skippedMissingRequired,

            // Compatibilidad con tu ListStudents
            'skipped_invalid_rut' => $this->invalidRut,

            'invalid_rut' => $this->invalidRut,
            'failed' => $this->failed,
        ];
    }

    public function getRowErrors(): array
    {
        return $this->rowErrors;
    }

    protected function addRowError(string $type, ?int $row, string $message, array $context = []): void
    {
        $this->rowErrors[] = [
            'type' => $type,
            'row' => $row,      // null = error global (ej: columnas)
            'message' => $message,
            'context' => $context,
        ];
    }
}
