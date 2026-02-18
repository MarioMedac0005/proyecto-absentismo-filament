<?php

namespace App\Filament\Resources\Courses;

use App\Filament\Resources\Courses\Pages\ManageCourses;
use App\Models\Course;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\SelectFilter;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationLabel = 'Cursos';

    protected static ?string $pluralLabel = 'Listado de Cursos';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?string $slug = 'cursos';

    protected static ?string $recordTitleAttribute = 'nombre';

    protected static string|\UnitEnum|null $navigationGroup = 'Administración';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Información General')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        TextInput::make('nombre')
                            ->placeholder('Nombre del curso')
                            ->rules(['required', 'string', 'min:3', 'max:255'])
                            ->validationMessages([
                                'required' => 'El nombre es obligatorio',
                                'string' => 'El nombre debe ser texto',
                                'min' => 'El nombre debe tener al menos 3 caracteres',
                                'max' => 'El nombre debe tener como maximo 255 caracteres',
                            ]),
                        Select::make('grado')
                            ->options(['primero' => 'Primero', 'segundo' => 'Segundo'])
                            ->rules(['required', 'string', 'in:primero,segundo'])
                            ->placeholder('Grado del curso')
                            ->validationMessages([
                                'required' => 'El grado es obligatorio',
                                'in' => 'El grado debe ser primero o segundo',
                            ]),
                    ])->columns(1),

                \Filament\Schemas\Components\Section::make('Fechas del Curso')
                    ->icon('heroicon-o-calendar')
                    ->schema([
                        TextInput::make('inicio_curso')
                            ->label('Año Inicio')
                            ->rules(['required', 'numeric'])
                            ->validationMessages([
                                'required' => 'El año de inicio es obligatorio',
                                'numeric' => 'El año de inicio debe ser un número',
                            ]),
                        TextInput::make('fin_curso')
                            ->label('Año Fin')
                            ->rules(['required', 'numeric'])
                            ->validationMessages([
                                'required' => 'El año de fin es obligatorio',
                                'numeric' => 'El año de fin debe ser un número',
                            ]),
                    ])->columns(1),

                \Filament\Schemas\Components\Tabs::make('Trimestres')
                    ->tabs([
                        \Filament\Schemas\Components\Tabs\Tab::make('1º Trimestre')
                            ->schema([
                                DatePicker::make('trimestre_1_inicio')
                                    ->label('Inicio')
                                    ->native(false)
                                    ->closeOnDateSelection()
                                    ->required()
                                    ->validationMessages([
                                        'required' => 'El inicio del trimestre es obligatorio',
                                    ]),
                                DatePicker::make('trimestre_1_fin')
                                    ->label('Fin')
                                    ->native(false)
                                    ->closeOnDateSelection()
                                    ->required()
                                    ->after('trimestre_1_inicio')
                                    ->validationMessages([
                                        'required' => 'El fin del trimestre es obligatorio',
                                        'after' => 'El fin del trimestre debe ser posterior al inicio del trimestre',
                                    ]),
                            ])->columns(2),
                        \Filament\Schemas\Components\Tabs\Tab::make('2º Trimestre')
                            ->schema([
                                DatePicker::make('trimestre_2_inicio')
                                    ->label('Inicio')
                                    ->native(false)
                                    ->closeOnDateSelection()
                                    ->required()
                                    ->after('trimestre_1_fin')
                                    ->validationMessages([
                                        'required' => 'El inicio del trimestre es obligatorio',
                                        'after' => 'El inicio del trimestre debe ser posterior al fin del trimestre anterior',
                                    ]),
                                DatePicker::make('trimestre_2_fin')
                                    ->label('Fin')
                                    ->native(false)
                                    ->closeOnDateSelection()
                                    ->required()
                                    ->after('trimestre_2_inicio')
                                    ->validationMessages([
                                        'required' => 'El fin del trimestre es obligatorio',
                                        'after' => 'El fin del trimestre debe ser posterior al inicio del trimestre',
                                    ]),
                            ])->columns(2),
                        \Filament\Schemas\Components\Tabs\Tab::make('3º Trimestre')
                            ->schema([
                                DatePicker::make('trimestre_3_inicio')
                                    ->label('Inicio')
                                    ->native(false)
                                    ->closeOnDateSelection()
                                    ->required()
                                    ->after('trimestre_2_fin')
                                    ->validationMessages([
                                        'required' => 'El inicio del trimestre es obligatorio',
                                        'after' => 'El inicio del trimestre debe ser posterior al fin del trimestre anterior',
                                    ]),
                                DatePicker::make('trimestre_3_fin')
                                    ->label('Fin')
                                    ->native(false)
                                    ->closeOnDateSelection()
                                    ->required()
                                    ->after('trimestre_3_inicio')
                                    ->validationMessages([
                                        'required' => 'El fin del trimestre es obligatorio',
                                        'after' => 'El fin del trimestre debe ser posterior al inicio del trimestre',
                                    ]),
                            ])->columns(2),
                    ])
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                    ActionGroup::make([
                        Action::make('downloadTemporalizationHeader')
                            ->label('Temporalización')
                            ->icon('heroicon-o-calendar')
                            ->modalHeading('Generar temporalización')
                            ->modalDescription('Selecciona el curso para generar el PDF')
                            ->modalSubmitActionLabel('Generar PDF')
                            ->color('primary')
                            ->form([
                                Select::make('course_id')
                                    ->label('Curso')
                                    ->options(Course::whereNotNull('nombre')->pluck('nombre', 'id'))
                                    ->required()
                                    ->validationMessages([
                                        'required' => 'El curso es obligatorio',
                                    ])
                                    ->searchable(),
                            ])
                            ->action(function (array $data, \App\Services\TemporalizationCalendar $service) {
                                try {
                                    $course = Course::findOrFail($data['course_id']);
                                    return $service->generatePdf($course);
                                } catch (\Exception $e) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Error al generar PDF')
                                        ->body($e->getMessage())
                                        ->danger()
                                        ->send();
                                }
                            }),
                    ])
                ])
            ->recordTitleAttribute('nombre')
            ->columns([
                TextColumn::make('nombre')
                    ->searchable(),
                TextColumn::make('curso_escolar_virtual')
                    ->label('Curso Escolar')
                    ->getStateUsing(fn (Course $record) => $record->inicio_curso && $record->fin_curso 
                        ? $record->inicio_curso . '/' . $record->fin_curso
                        : 'N/A')
                    ->badge(),
                TextColumn::make('grado')
                    ->badge(),
                TextColumn::make('trimestre_1_inicio')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('trimestre_1_fin')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('trimestre_2_inicio')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('trimestre_2_fin')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('trimestre_3_inicio')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('trimestre_3_fin')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('grado')
                    ->options([
                        'primero' => 'Primero',
                        'segundo' => 'Segundo',
                    ])
                    ->label('Grado'),
                SelectFilter::make('inicio_curso')
                    ->label('Año de inicio')
                    ->options(fn () => Course::whereNotNull('inicio_curso')->distinct()->pluck('inicio_curso', 'inicio_curso')->sort()->toArray()),
                Filter::make('recientes')
                    ->label('Últimos 7 días')
                    ->query(fn (Builder $query): Builder =>
                        $query->where('created_at', '>=', Carbon::now()->subDays(7))
                    ),
            ])
            ->recordActions([
                EditAction::make()
                    ->successNotificationTitle('Curso editado correctamente'),
                DeleteAction::make()
                    ->successNotificationTitle('Curso eliminado correctamente'),
                ForceDeleteAction::make()
                    ->successNotificationTitle('Curso eliminado correctamente'),
                RestoreAction::make()
                    ->successNotificationTitle('Curso restaurado correctamente'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->successNotificationTitle('Cursos eliminados correctamente'),
                    ForceDeleteBulkAction::make()
                        ->successNotificationTitle('Cursos eliminados correctamente'),
                    RestoreBulkAction::make()
                        ->successNotificationTitle('Cursos restaurados correctamente'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCourses::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Numero de cursos';
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole('admin');
    }
}
