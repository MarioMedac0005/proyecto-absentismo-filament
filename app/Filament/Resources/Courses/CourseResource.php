<?php

namespace App\Filament\Resources\Courses;

use App\Filament\Resources\Courses\Pages\ManageCourses;
use App\Models\Course;
use BackedEnum;
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
use Filament\Forms\Components\Section;;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
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
                TextInput::make('nombre')
                    ->placeholder('Nombre del curso')
                    ->required(),
                Select::make('grado')
                    ->options(['primero' => 'Primero', 'segundo' => 'Segundo'])
                    ->required()
                    ->placeholder('Grado del curso'),
                \Filament\Schemas\Components\Tabs::make('Trimestres')
                    ->tabs([
                        \Filament\Schemas\Components\Tabs\Tab::make('1º Trimestre')
                            ->schema([
                                DatePicker::make('trimestre_1_inicio')
                                    ->label('Inicio')
                                    ->native(false)
                                    ->closeOnDateSelection(),
                                DatePicker::make('trimestre_1_fin')
                                    ->label('Fin')
                                    ->native(false)
                                    ->closeOnDateSelection(),
                            ])->columns(2),
                        \Filament\Schemas\Components\Tabs\Tab::make('2º Trimestre')
                            ->schema([
                                DatePicker::make('trimestre_2_inicio')
                                    ->label('Inicio')
                                    ->native(false)
                                    ->closeOnDateSelection(),
                                DatePicker::make('trimestre_2_fin')
                                    ->label('Fin')
                                    ->native(false)
                                    ->closeOnDateSelection(),
                            ])->columns(2),
                        \Filament\Schemas\Components\Tabs\Tab::make('3º Trimestre')
                            ->schema([
                                DatePicker::make('trimestre_3_inicio')
                                    ->label('Inicio')
                                    ->native(false)
                                    ->closeOnDateSelection(),
                                DatePicker::make('trimestre_3_fin')
                                    ->label('Fin')
                                    ->native(false)
                                    ->closeOnDateSelection(),
                            ])->columns(2),
                    ])
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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
                                    ->options(Course::all()->pluck('nombre', 'id'))
                                    ->required()
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
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
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
        return 'The number of courses';
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole('admin');
    }
}
