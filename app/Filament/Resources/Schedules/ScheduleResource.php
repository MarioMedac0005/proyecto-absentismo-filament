<?php

namespace App\Filament\Resources\Schedules;

use App\Filament\Resources\Schedules\Pages\ManageSchedules;
use App\Models\Schedule;
use App\Models\Subject;
use Auth;
use BackedEnum;
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
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static ?string $navigationLabel = 'Horarios';

    protected static ?string $pluralLabel = 'Listado de Horarios';

    protected static ?string $slug = 'horarios';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?string $recordTitleAttribute = 'dia_semana';

    // Usa condicion depende del rol, la funcion se encuentra abajo
    // protected static string|\UnitEnum|null $navigationGroup = 'Administración';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('dia_semana')
                    ->label('Día de la semana')
                    ->options([
                        'lunes' => 'Lunes',
                        'martes' => 'Martes',
                        'miercoles' => 'Miercoles',
                        'jueves' => 'Jueves',
                        'viernes' => 'Viernes',
                    ])
                    ->placeholder('Día de la semana')
                    ->required()
                    ->validationMessages([
                        'required' => 'El día de la semana es obligatorio',
                    ]),
                TextInput::make('horas')
                    ->label('Horas')
                    ->placeholder('Horas')
                    ->required()
                    ->numeric()
                    ->validationMessages([
                        'required' => 'Las horas son obligatorias',
                        'numeric' => 'Las horas deben ser un número',
                    ]),
                Select::make('subject_id')
                    ->label('Asignatura')
                    ->placeholder('Asignatura')
                    ->required()
                    ->validationMessages([
                        'required' => 'La asignatura es obligatoria',
                    ])
                    ->searchable()
                    ->preload()
                    ->options(function () {
                        if (auth()->user()->hasRole('profesor')) {
                            return auth()->user()->subjects()
                                        ->whereNotNull('nombre')
                                        ->pluck('nombre', 'id')
                                        ->toArray();
                        }
                        return Subject::whereNotNull('nombre')->pluck('nombre', 'id')->toArray();
                    })

                    /* ->options(function () {
                        if (auth()->user()->hasRole('profesor')) {
                            return auth()->user()->subjects()->pluck('subjects.nombre', 'subjects.id');
                        }
                        return Subject::pluck('nombre', 'id');
                    }), */
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('dia_semana')
            ->columns([
                TextColumn::make('dia_semana')
                    ->label('Día de la semana')
                    ->badge(),
                TextColumn::make('horas')
                    ->label('Horas')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('subject.nombre')
                    ->label('Asignatura')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Fecha de creación')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Fecha de actualización')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label('Fecha de eliminación')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('dia_semana')
                    ->label('Día de la semana')
                    ->options([
                        'lunes' => 'Lunes',
                        'martes' => 'Martes',
                        'miercoles' => 'Miercoles',
                        'jueves' => 'Jueves',
                        'viernes' => 'Viernes',
                    ]),
                SelectFilter::make('subject_id')
                    ->label('Asignatura')
                    ->options(\App\Models\Subject::whereNotNull('nombre')->pluck('nombre', 'id')),
            ])
            ->recordActions([
                EditAction::make()
                    ->successNotificationTitle('Horario editado correctamente'),
                DeleteAction::make()
                    ->successNotificationTitle('Horario eliminado correctamente'),
                ForceDeleteAction::make()
                    ->successNotificationTitle('Horario eliminado correctamente'),
                RestoreAction::make()
                    ->successNotificationTitle('Horario restaurado correctamente'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->successNotificationTitle('Horarios eliminados correctamente'),
                    ForceDeleteBulkAction::make()
                        ->successNotificationTitle('Horarios eliminados correctamente'),
                    RestoreBulkAction::make()
                        ->successNotificationTitle('Horarios restaurados correctamente'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSchedules::route('/'),
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
        if (auth()->user()->hasRole('profesor')) {
            $subjectIds = auth()->user()->subjects()->pluck('subjects.id');
            return Schedule::whereIn('subject_id', $subjectIds)->count();
        }

        return static::getModel()::count();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Numero de horarios';
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()->hasRole('profesor')) {
            $asignaturasId = auth()->user()->subjects()->pluck('subjects.id');

            return $query->whereIn('subject_id', $asignaturasId);
        }

        return $query;
    }

    public static function getNavigationGroup(): ?string
    {
        $user = Auth::user();

        if ($user->hasRole('profesor')) {
            return 'Profesorado';
        }

        return 'Administración';
    }
}
