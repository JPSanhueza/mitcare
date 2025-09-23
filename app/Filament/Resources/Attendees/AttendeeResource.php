<?php

namespace App\Filament\Resources\Attendees;

use App\Filament\Resources\Attendees\Pages\CreateAttendee;
use App\Filament\Resources\Attendees\Pages\EditAttendee;
use App\Filament\Resources\Attendees\Pages\ListAttendees;
use App\Filament\Resources\Attendees\Pages\ViewAttendee;
use App\Filament\Resources\Attendees\Schemas\AttendeeForm;
use App\Filament\Resources\Attendees\Schemas\AttendeeInfolist;
use App\Filament\Resources\Attendees\Tables\AttendeesTable;
use App\Models\OrderItemAttendee as Attendee;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AttendeeResource extends Resource
{
    protected static ?string $model = Attendee::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Asistentes';

    protected static ?string $navigationLabel = 'Asistentes';

    protected static ?string $pluralLabel = 'Asistentes';

    protected static ?string $singularLabel = 'Asistente';

    protected static ?string $modelLabel = 'Asistente';

    protected static ?string $slug = 'Asistentes';

    protected static ?int $navigationSort = 20;

    protected static string|UnitEnum|null $navigationGroup = 'Administración';

    public static function form(Schema $schema): Schema
    {
        return AttendeeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AttendeeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AttendeesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAttendees::route('/'),
            // 'create' => CreateAttendee::route('/create'),
            'view' => ViewAttendee::route('/{record}'),
            'edit' => EditAttendee::route('/{record}/edit'),
        ];
    }
}
