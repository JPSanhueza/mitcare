<?php

namespace App\Filament\Resources\Diplomas;

use App\Filament\Resources\Diplomas\Pages\CreateDiploma;
use App\Filament\Resources\Diplomas\Pages\EditDiploma;
use App\Filament\Resources\Diplomas\Pages\ListDiplomas;
use App\Filament\Resources\Diplomas\Tables\DiplomasTable;
use App\Models\Diploma;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DiplomaResource extends Resource
{
    protected static ?string $model = Diploma::class;

    protected static ?string $recordTitleAttribute = 'Diplomas';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::AcademicCap;
    protected static ?string $navigationLabel = 'Certificados';
    protected static ?string $modelLabel = 'Certificado';
    protected static ?string $pluralModelLabel = 'Certificados';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        // 👇 ESTE form lo usa EditDiploma (NO el wizard)
        return $schema->components([
            Section::make('Datos del certificado')
                ->columns(2)
                ->schema([
                    Select::make('course_id')
                        ->label('Curso')
                        ->relationship('course', 'nombre')
                        ->disabled(),

                    Select::make('student_id')
                        ->label('Estudiante')
                        // ajusta si tienes nombre/apellido separados
                        ->relationship('student', 'nombre')
                        ->disabled(),

                    DatePicker::make('issued_at')
                        ->label('Fecha de emisión')
                        ->required(),

                    TextInput::make('final_grade')
                        ->label('Nota final')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(7)
                        ->nullable(),

                    TextInput::make('verification_code')
                        ->label('Código de verificación')
                        ->disabled(),

                    TextInput::make('file_path')
                        ->label('Ruta PDF')
                        ->disabled()
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return DiplomasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListDiplomas::route('/'),
            'create' => CreateDiploma::route('/create'),      // wizard
            'edit'   => EditDiploma::route('/{record}/edit'), // form simple
        ];
    }
}
