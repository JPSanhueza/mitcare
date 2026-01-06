<?php

namespace App\Filament\Resources\BrochureRequests\Pages;

use App\Filament\Resources\BrochureRequests\BrochureRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBrochureRequests extends ListRecords
{
    protected static string $resource = BrochureRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make()
            //     ->label('Agregar solicitud'),
        ];
    }
}
