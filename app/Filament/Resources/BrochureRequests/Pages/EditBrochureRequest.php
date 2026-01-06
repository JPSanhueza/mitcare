<?php

namespace App\Filament\Resources\BrochureRequests\Pages;

use App\Filament\Resources\BrochureRequests\BrochureRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBrochureRequest extends EditRecord
{
    protected static string $resource = BrochureRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
