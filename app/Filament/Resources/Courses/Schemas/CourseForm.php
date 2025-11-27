<?php

namespace App\Filament\Resources\Courses\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            /* ---------------------------------
             * DATOS DEL CURSO
             * --------------------------------- */
            Section::make('Datos del curso')
                ->columns(2)
                ->schema([
                    TextInput::make('nombre')
                        ->label('Título')
                        ->hint('Título que se usa en la página principal')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Set $set, ?string $state) {
                            if (blank($state)) {
                                return;
                            }
                            $set('slug', Str::slug($state));
                        }),

                    TextInput::make('nombre_diploma')
                        ->label('Nombre')
                        ->hint('Nombre que se usa en el diploma')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Set $set, ?string $state) {
                            if (blank($state)) {
                                return;
                            }
                            $set('slug', Str::slug($state));
                        }),

                    TextInput::make('subtitulo')
                        ->label('Subtítulo')
                        ->maxLength(255)
                        ->nullable()
                        ->columnSpanFull(),

                    RichEditor::make('descripcion')
                        ->label('Descripción')
                        ->toolbarButtons([
                            ['bold', 'italic', 'underline', 'strike', 'subscript', 'superscript', 'link'],
                            ['undo', 'redo'],
                        ])
                        ->columnSpanFull(),
                ]),

            /* ---------------------------------
             * EJECUCIÓN DEL CURSO
             * --------------------------------- */
            Section::make('Ejecución')
                ->columns(3)
                ->schema([
                    Select::make('modality')
                        ->label('Modalidad')
                        ->options([
                            'online'     => 'Online',
                            'presencial' => 'Presencial',
                            'mixto'      => 'Mixto',
                        ])
                        ->default('online')
                        ->required(),

                    DatePicker::make('start_at')
                        ->label('Inicio')
                        ->live(),

                    DatePicker::make('end_at')
                        ->label('Término')
                        ->rules(fn(Get $get) => $get('start_at') ? ['after_or_equal:start_at'] : [])
                        ->validationMessages([
                            'after_or_equal' => 'El término debe ser posterior o igual al inicio.',
                        ])
                        ->minDate(fn(Get $get) => $get('start_at') ?: null),

                    TextInput::make('total_hours')
                        ->label('Horas totales')
                        ->numeric()
                        ->minValue(0)
                        ->required(),

                    TextInput::make('hours_description')
                        ->label('Descripción de las horas')
                        ->helperText('Ej: 23 horas teóricas asincrónicas y 7 horas prácticas')
                        ->columnSpan(2),

                    TextInput::make('location')
                        ->label('Ubicación')
                        ->maxLength(255)
                        ->visible(fn(callable $get) => in_array($get('modality'), ['presencial', 'mixto']))
                        ->columnSpanFull(),
                ]),

            /* ---------------------------------
             * VENTA Y PUBLICACIÓN
             * --------------------------------- */
            Section::make('Venta y publicación')
                ->columns(2)
                ->schema([
                    TextInput::make('price')
                        ->label('Precio')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->prefix('CLP'),

                    TextInput::make('capacity')
                        ->label('Cupos')
                        ->numeric()
                        ->minValue(1)
                        ->helperText('Déjalo vacío si no hay límite')
                        ->nullable(),
                    DateTimePicker::make('published_at')
                        ->label('Publicado desde')
                        ->seconds(false)
                        ->maxDate(fn(Get $get) => $get('start_at') ?: null)
                        ->rules(fn(Get $get) => $get('start_at') ? ['before_or_equal:start_at'] : [])
                        ->validationMessages([
                            'before_or_equal' => 'La fecha de publicación debe ser anterior o igual a la fecha de inicio del curso.',
                        ])
                        ->columnSpan(1),

                    Toggle::make('is_active')
                        ->label('Activo')
                        ->helperText('Activa o desactiva la visibilidad en la pagina principal')
                        ->default(true),

                ]),

            /* ---------------------------------
             * PORTADA Y ORDEN
             * --------------------------------- */
            Section::make('Portada y orden')
                ->columns(2)
                ->schema([
                    FileUpload::make('image')
                        ->label('Imagen (portada)')
                        ->image()
                        ->disk('public')
                        ->directory('courses')
                        ->imageEditor()
                        ->rules([
                            'image',
                            'mimes:jpg,jpeg,png,webp',
                        ])
                        ->validationMessages([
                            'image' => 'El archivo debe ser una imagen válida.',
                            'mimes' => 'El formato de la imagen debe ser jpg, jpeg, png o webp.',
                            'max'   => 'La imagen no puede exceder los 1024 KB (1 MB).',
                        ])
                        ->maxSize(1024)
                        ->columnSpan(1),

                    TextInput::make('order')
                        ->label('Orden en pantalla')
                        ->required()
                        ->numeric()
                        ->default('0'),
                ]),
        ]);
    }
}
