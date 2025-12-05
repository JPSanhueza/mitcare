<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\StudentResource;
use App\Imports\StudentsImport;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    /** Errores del último import realizado en esta sesión de página */
    public ?array $lastImportErrors = null;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            // 👉 Acción para subir el Excel e importar
            Actions\Action::make('importStudents')
                ->label('Importar estudiantes')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->form([
                    Forms\Components\FileUpload::make('file')
                        ->label('Archivo Excel')
                        ->disk('local') // guarda en storage/app/
                        ->directory('imports/students')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            'text/csv',
                        ])
                        ->required(),
                ])
                ->action(function (array $data): void {

                    $fullPath = Storage::disk('local')->path($data['file']);

                    if (!file_exists($fullPath)) {
                        Notification::make()
                            ->title('El archivo no existe en el servidor')
                            ->body("Ruta buscada: {$fullPath}")
                            ->danger()
                            ->persistent()
                            ->send();
                        return;
                    }

                    $import = new StudentsImport();
                    Excel::import($import, $fullPath);

                    $summary = $import->getSummary();
                    $errors = $import->getRowErrors();

                    // Guardamos los errores en el estado de la página
                    $this->lastImportErrors = $errors;

                    // 🟢 Resumen general
                    $bodySummary = implode("\n", [
                        "✔ Creados: {$summary['created']}",
                        "⛔ Duplicados omitidos: {$summary['skipped_existing']}",
                        "⚠ Faltan datos obligatorios: {$summary['skipped_missing_required']}",
                        "❌ RUT inválido: {$summary['skipped_invalid_rut']}",
                        "💥 Errores inesperados: {$summary['failed']}",
                    ]);

                    Notification::make()
                        ->title('Importación de estudiantes completada')
                        ->body(view('filament.students.import-summary', compact('summary'))->render())
                        ->success()
                        ->persistent()
                        ->send();

                    // Si hubo errores, abrimos el modal de detalle
                    if (!empty($errors)) {
                        $this->mountAction('showImportErrors');
                    }

                }),

            // 👉 Acción que muestra el modal con el detalle de errores
            Actions\Action::make('showImportErrors')
                ->label('Ver errores de último import')
                ->icon('heroicon-o-exclamation-triangle')
                ->color('danger')
                ->visible(fn(): bool => !empty($this->lastImportErrors))
                ->modalHeading('Detalle de errores en la importación')
                ->modalSubmitAction(false) // sin botón "Guardar"
                ->modalCancelActionLabel('Cerrar')
                ->modalContent(function () {
                    return view('filament.students.import-errors-modal', [
                        'errors' => $this->lastImportErrors ?? [],
                    ]);
                }),
        ];
    }
}
