<?php

namespace App\Filament\Resources\Diplomas\Pages;

use App\Filament\Resources\Diplomas\DiplomaResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;
use App\Jobs\GenerateDiplomaPdf;

class EditDiploma extends EditRecord
{
    protected static string $resource = DiplomaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadPdf')
                ->label('Descargar PDF')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('success')
                ->url(function () {
                    // Si no hay archivo, devolvemos null
                    if (blank($this->record->file_path)) {
                        return null;
                    }

                    // Asumiendo que usas disk('public')
                    return Storage::disk('public')->url($this->record->file_path);
                })
                ->openUrlInNewTab()
                // Ocultamos el botón si aún no hay PDF generado
                ->hidden(fn() => blank($this->record->file_path)),

                Action::make('regeneratePdf')
                ->label('Re-generar PDF')
                ->icon('heroicon-m-arrow-path')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Re-generar PDF del certificado')
                ->modalDescription('Se volverá a generar el archivo PDF de este certificado con la información actual.')
                ->action(function () {
                    // $this->record es el diploma que estás editando
                    GenerateDiplomaPdf::dispatch($this->record->id);
                })
                ->successNotificationTitle('El PDF del certificado se está regenerando.'),

            DeleteAction::make(),
        ];
    }

     protected function mutateFormDataBeforeSave(array $data): array
    {
        // Si viene el campo del select múltiple...
        if (isset($data['teacher_ids_display'])) {
            $teacherIds = $data['teacher_ids_display'] ?? [];

            // Forzamos máximo 3 por seguridad extra
            $teacherIds = array_slice($teacherIds, 0, 3);

            // Actualizamos el batch asociado al diploma
            if ($this->record->batch) {
                $this->record->batch->update([
                    'teacher_ids' => $teacherIds,
                ]);
            }

            // IMPORTANTÍSIMO: que Filament NO intente guardar esto en la tabla diplomas
            unset($data['teacher_ids_display']);
        }

        return $data;
    }
}
