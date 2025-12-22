<?php

namespace App\Filament\Resources\Courses\Schemas;

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
                ->columns(1)
                ->schema([
                    RichEditor::make('nombre')
                        ->label('Título')
                        ->helperText('Título que se usa en la página principal (poner en negrita las palabras a destacar en color)')
                        ->required()
                        ->toolbarButtons([
                            ['bold'],
                        ])
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
                        ->helperText('Nombre que se usa en el diploma')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Set $set, ?string $state) {
                            if (blank($state)) {
                                return;
                            }
                            $set('slug', Str::slug($state));
                        }),

                    // TextInput::make('subtitulo')
                    //     ->label('Subtítulo')
                    //     ->maxLength(255)
                    //     ->nullable()
                    //     ->columnSpanFull(),

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
                ->columns(2)
                ->schema([
                    DateTimePicker::make('start_at')
                        ->label('Inicio')
                        ->live(),

                    DateTimePicker::make('end_at')
                        ->label('Término')
                        ->rules(fn (Get $get) => $get('start_at') ? ['after_or_equal:start_at'] : [])
                        ->validationMessages([
                            'after_or_equal' => 'El término debe ser posterior o igual al inicio.',
                        ])
                        ->minDate(fn (Get $get) => $get('start_at') ?: null),

                    Select::make('modality')
                        ->label('Modalidad')
                        ->options([
                            'online' => 'Asincrónica',
                            'presencial' => 'Presencial',
                            'mixto' => 'Mixto',
                        ])
                        ->default('online')
                        ->required(),

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
                        ->visible(fn (callable $get) => in_array($get('modality'), ['presencial', 'mixto']))
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

                    FileUpload::make('ficha')
                        ->label('Ficha (PDF)')
                        ->disk('public')
                        ->directory('courses/fichas')
                        ->acceptedFileTypes(['application/pdf'])
                        ->maxSize(20480) // 20 MB
                        ->preserveFilenames()
                        ->downloadable()
                        ->openable()
                        ->columnSpanFull()
                        ->validationMessages([
                            'required' => 'Debes subir un archivo PDF.',
                            'file' => 'El archivo subido no es válido.',
                            'uploaded' => 'El archivo no pudo subirse. Revisa el tamaño, tipo o permisos del servidor.',
                            'mimes' => 'El archivo debe ser un PDF.',
                            'mimetypes' => 'El archivo debe ser un PDF válido (application/pdf).',
                            'max' => 'El archivo supera el tamaño máximo permitido (20 MB).',
                            'min' => 'El archivo es demasiado pequeño o está corrupto.',
                        ]),

                    DateTimePicker::make('published_at')
                        ->label('Publicado desde')
                        ->seconds(false)
                        ->maxDate(fn (Get $get) => $get('start_at') ?: null)
                        ->rule(function (Get $get) {
                            return function (string $attribute, $value, \Closure $fail) use ($get) {
                                $startAt = $get('start_at');

                                if (! $startAt || ! $value) {
                                    return;
                                }

                                $start = \Illuminate\Support\Carbon::parse($startAt);
                                $published = \Illuminate\Support\Carbon::parse($value);

                                // Si la publicación es después del inicio => error
                                if ($published->greaterThan($start)) {
                                    $fail('La fecha de publicación debe ser anterior a la fecha de inicio del curso.');
                                }
                            };
                        })
                        ->validationMessages([
                            'before_or_equal' => 'La fecha de publicación debe ser anterior a la fecha de inicio del curso.',
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
                        ->directory('courses/front')
                        ->imageEditor()
                        ->rules([
                            'image',
                            'mimes:jpg,jpeg,png,webp',
                        ])
                        ->validationMessages([
                            'image' => 'El archivo debe ser una imagen válida.',
                            'mimes' => 'El formato de la imagen debe ser jpg, jpeg, png o webp.',
                            'max' => 'La imagen no puede exceder los 1536 KB (1.5 MB).',
                        ])
                        ->maxSize(1536)
                        ->columnspanFull(),

                    TextInput::make('order')
                        ->label('Orden en pantalla')
                        ->required()
                        ->numeric()
                        ->default('0'),

                    Select::make('teachers_type')
                        ->label('Tipo de docente')
                        ->options([
                            'nacional' => 'Nacional',
                            'internacional' => 'Internacional',
                        ])
                        ->required(),
                ]),
        ]);
    }
}
