<?php

namespace App\Filament\Resources\Diplomas\Pages;

use App\Filament\Resources\Diplomas\DiplomaResource;
use App\Jobs\GenerateDiplomaPdf;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditDiploma extends EditRecord
{
    protected static string $resource = DiplomaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadPdf')
                ->label(fn () => blank($this->record->file_path) ? 'PDF en proceso' : 'Descargar PDF')
                ->icon('heroicon-m-arrow-down-tray')
                ->color(fn () => blank($this->record->file_path) ? 'gray' : 'success')
                ->extraAttributes([
                    'wire:poll.3s' => 'refreshDownloadState',
                ])
                ->url(function () {
                    if (blank($this->record->file_path)) {
                        return null;
                    }

                    return Storage::disk('public')->url($this->record->file_path);
                })
                ->openUrlInNewTab()
                ->disabled(fn () => blank($this->record->file_path))
                ->tooltip(fn () => blank($this->record->file_path)
                    ? 'El PDF se esta generando. Se habilitara al finalizar.'
                    : 'Descargar certificado en PDF'),

            Action::make('regeneratePdf')
                ->label('Re-generar PDF')
                ->icon('heroicon-m-arrow-path')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Re-generar PDF del certificado')
                ->modalDescription('Se volvera a generar el archivo PDF de este certificado con la informacion actual.')
                ->action(function () {
                    // Deja el diploma sin archivo mientras corre la regeneracion.
                    $this->record->update([
                        'file_path' => null,
                    ]);

                    $this->record->refresh();

                    GenerateDiplomaPdf::dispatch($this->record->id);
                })
                ->successNotificationTitle('El PDF del certificado se esta regenerando.'),

            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['teacher_ids_display'])) {
            $teacherIds = $data['teacher_ids_display'] ?? [];
            $teacherIds = array_slice($teacherIds, 0, 3);

            if ($this->record->batch) {
                $this->record->batch->update([
                    'teacher_ids' => $teacherIds,
                ]);
            }

            unset($data['teacher_ids_display']);
        }

        return $data;
    }

    public function refreshDownloadState(): void
    {
        $this->record->refresh();
    }
}
