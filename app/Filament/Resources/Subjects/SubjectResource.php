<?php

namespace App\Filament\Resources\Subjects;

use App\Filament\Resources\Subjects\Pages\ManageSubjects;
use App\Models\Subject;
use BackedEnum;
use Carbon\Carbon;
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
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubjectResource extends Resource
{
    protected static ?string $model = Subject::class;

    protected static ?string $navigationLabel = 'Asignaturas';

    protected static ?string $pluralLabel = 'Listado de Asignaturas';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?string $slug = 'asignaturas';

    protected static ?string $recordTitleAttribute = 'nombre';

    protected static string|\UnitEnum|null $navigationGroup = 'Administración';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombre')
                    ->placeholder('Nombre de la asignatura')
                    ->required()
                    ->validationMessages([
                        'required' => 'El nombre de la asignatura es obligatorio',
                    ]),
                TextInput::make('horas_semanales')
                    ->placeholder('Horas semanales')
                    ->disabled()
                    ->numeric(),
                Select::make('grado')
                    ->placeholder('Grado')
                    ->options(['primero' => 'Primero', 'segundo' => 'Segundo'])
                    ->required()
                    ->validationMessages([
                        'required' => 'El grado es obligatorio',
                    ]),
                Select::make('course_id')
                    ->label('Curso')
                    ->placeholder('Curso')
                    ->relationship('course', 'nombre')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->validationMessages([
                        'required' => 'El curso es obligatorio',
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->recordTitleAttribute('nombre')
            ->columns([
                TextColumn::make('nombre')
                    ->searchable(),
                TextColumn::make('horas_semanales')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('grado')
                    ->badge(),
                TextColumn::make('course.nombre')
                    ->label('Curso')
                    ->sortable(),
                TextColumn::make('users.nombre')
                    ->label('Profesores')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                SelectFilter::make('course_id')
                    ->label('Curso')
                    ->options(\App\Models\Course::whereNotNull('nombre')->pluck('nombre', 'id')),
                SelectFilter::make('grado')
                    ->label('Grado')
                    ->options([
                        'primero' => 'Primero',
                        'segundo' => 'Segundo',
                    ]),
                Filter::make('recientes')
                    ->label('Últimos 7 días')
                    ->query(fn (Builder $query): Builder =>
                        $query->where('created_at', '>=', Carbon::now()->subDays(7))
                    ),
            ])
            ->recordActions([
                EditAction::make()
                    ->successNotificationTitle('Asignatura editada correctamente'),
                DeleteAction::make()
                    ->successNotificationTitle('Asignatura eliminada correctamente'),
                ForceDeleteAction::make()
                    ->successNotificationTitle('Asignatura eliminada correctamente'),
                RestoreAction::make()
                    ->successNotificationTitle('Asignatura restaurada correctamente'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->successNotificationTitle('Asignaturas eliminadas correctamente'),
                    ForceDeleteBulkAction::make()
                        ->successNotificationTitle('Asignaturas eliminadas correctamente'),
                    RestoreBulkAction::make()
                        ->successNotificationTitle('Asignaturas restauradas correctamente'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSubjects::route('/'),
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
        return 'Numero de asignaturas';
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole('admin');
    }
}
