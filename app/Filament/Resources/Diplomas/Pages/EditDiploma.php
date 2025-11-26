<?php

namespace App\Filament\Resources\Diplomas\Pages;

use App\Filament\Resources\Diplomas\DiplomaResource;
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

            DeleteAction::make(),
        ];
    }
}
