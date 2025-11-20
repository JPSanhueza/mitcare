<?php

namespace App\Filament\Resources\Diplomas\Pages;

use App\Filament\Resources\Diplomas\DiplomaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDiplomas extends ListRecords
{
    protected static string $resource = DiplomaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
