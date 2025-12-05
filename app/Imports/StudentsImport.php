<?php

namespace App\Imports;

use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentsImport implements ToCollection, WithHeadingRow
{
    // Contadores para el resumen
    protected int $created = 0;
    protected int $skippedExisting = 0;
    protected int $skippedMissingRequired = 0;
    protected int $skippedInvalidRut = 0;
    protected int $failed = 0;

    // Errores por fila / globales
    protected array $rowErrors = [];

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

        // 🔍 Validar que existan las columnas mínimas
        $firstRow = $rows->first();
        $headers = array_keys($firstRow->toArray());
        $requiredColumns = ['nombre', 'apellido', 'rut'];

        $missingColumns = array_diff($requiredColumns, $headers);

        if (!empty($missingColumns)) {
            $this->addRowError(
                type: 'missing_columns',
                row: null,
                message: 'Faltan columnas obligatorias en el Excel: ' . implode(', ', $missingColumns)
            );

            // Puedes decidir si abortar el import aquí:
            // return;
        }

        foreach ($rows as $index => $row) {
            $fila = $index + 2; // fila 1 = encabezados

            try {
                $nombre = trim((string) ($row['nombre'] ?? ''));
                $apellido = trim((string) ($row['apellido'] ?? ''));
                $rutRaw = trim((string) ($row['rut'] ?? ''));

                $email = $row['email'] ?? null;
                $telefono = $row['telefono'] ?? null;
                $direccion = $row['direccion'] ?? null;

                // 1) Validación mínima
                if ($nombre === '' || $apellido === '' || $rutRaw === '') {
                    $this->skippedMissingRequired++;
                    $this->addRowError(
                        type: 'missing_required',
                        row: $fila,
                        message: 'Faltan datos obligatorios (nombre, apellido o RUT).'
                    );
                    Log::warning('Fila omitida (faltan campos obligatorios)', [
                        'fila' => $fila,
                        'row' => $row->toArray(),
                    ]);
                    continue;
                }

                // 2) Normalizar / validar RUT
                $rutNormalizado = Student::normalizeRut($rutRaw);

                if (!Student::isValidRut($rutNormalizado)) {
                    $this->skippedInvalidRut++;
                    $this->addRowError(
                        type: 'invalid_rut',
                        row: $fila,
                        message: "RUT inválido para Chile: {$rutRaw}"
                    );
                    Log::warning('RUT inválido en import', [
                        'fila' => $fila,
                        'rut' => $rutRaw,
                    ]);
                    continue;
                }

                // 3) Si ya existe, lo omitimos (no actualizamos)
                if (Student::where('rut', $rutNormalizado)->exists()) {
                    $this->skippedExisting++;
                    $this->addRowError(
                        type: 'duplicate',
                        row: $fila,
                        message: "Estudiante duplicado, ya existe el RUT {$rutNormalizado}."
                    );
                    Log::info('Estudiante ya existe, se omite', [
                        'fila' => $fila,
                        'rut' => $rutNormalizado,
                    ]);
                    continue;
                }

                // 4) Crear estudiante nuevo
                Student::create([
                    'nombre' => $nombre,
                    'apellido' => $apellido,
                    'rut' => $rutNormalizado,
                    'email' => $email ?: null,
                    'telefono' => $telefono ?: null,
                    'direccion' => $direccion ?: null,
                ]);

                $this->created++;

            } catch (ValidationException $e) {
                $this->failed++;
                $this->addRowError(
                    type: 'validation',
                    row: $fila,
                    message: 'Error de validación al crear el estudiante.',
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
    }

    /* ---------- Helpers de resumen ---------- */

    public function getSummary(): array
    {
        return [
            'created' => $this->created,
            'skipped_existing' => $this->skippedExisting,
            'skipped_missing_required' => $this->skippedMissingRequired,
            'skipped_invalid_rut' => $this->skippedInvalidRut,
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
