<?php

namespace App\Filament\Resources\Attendees\Pages;

use App\Filament\Resources\Attendees\AttendeeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAttendee extends EditRecord
{
    protected static string $resource = AttendeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
