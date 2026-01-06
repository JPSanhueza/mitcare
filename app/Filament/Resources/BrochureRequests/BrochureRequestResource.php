<?php

namespace App\Filament\Resources\BrochureRequests;

use App\Filament\Resources\BrochureRequests\Pages\CreateBrochureRequest;
use App\Filament\Resources\BrochureRequests\Pages\EditBrochureRequest;
use App\Filament\Resources\BrochureRequests\Pages\ListBrochureRequests;
use App\Filament\Resources\BrochureRequests\Pages\ViewBrochureRequest;
use App\Filament\Resources\BrochureRequests\Schemas\BrochureRequestForm;
use App\Filament\Resources\BrochureRequests\Tables\BrochureRequestsTable;
use App\Models\BrochureRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class BrochureRequestResource extends Resource
{
    protected static ?string $model = BrochureRequest::class;

    protected static ?string $navigationLabel = 'Solicitudes de ficha';
    protected static ?string $modelLabel = 'Solicitud de ficha';
    protected static ?string $pluralModelLabel = 'Solicitudes de ficha';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentArrowDown;

    protected static ?int $navigationSort = 10;
    protected static string|UnitEnum|null $navigationGroup = 'Fichas';

    protected static ?string $recordTitleAttribute = 'full_name';

    public static function form(Schema $schema): Schema
    {
        return BrochureRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BrochureRequestsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListBrochureRequests::route('/'),
            'create' => CreateBrochureRequest::route('/create'),
            'edit'   => EditBrochureRequest::route('/{record}/edit'),
        ];
    }
}
