<?php

namespace App\Filament\Resources\Diplomas\Pages;

use App\Filament\Resources\Diplomas\DiplomaResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateDiploma extends CreateRecord
{
    protected static string $resource = DiplomaResource::class;

    /**
     * Por ahora NO vamos a crear registros reales,
     * solo dejamos el wizard funcionando visualmente.
     */
    public function create(bool $another = false): void
    {
        $data = $this->form->getState();

        // Aquí más adelante:
        // - filtrar students seleccionados
        // - crear registros en diplomas
        // - generar PDFs, etc.

        Notification::make()
            ->title('Wizard completado')
            ->body('La lógica real de creación de diplomas la implementaremos en el siguiente paso.')
            ->success()
            ->send();

        // Reseteamos el formulario
        $this->form->fill();
    }
}
