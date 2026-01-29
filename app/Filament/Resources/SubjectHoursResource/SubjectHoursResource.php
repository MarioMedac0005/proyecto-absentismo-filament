<?php

namespace App\Filament\Resources\SubjectHoursResource;

use App\Models\Subject;
use Auth;
use Filament\Forms;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class SubjectHoursResource extends Resource
{
    protected static ?string $model = Subject::class;

    protected static ?string $slug = 'subject-hours';

    // Usa condicion depende del rol, la funcion se encuentra abajo
    // protected static ?string $navigationLabel = 'Horas por Asignatura';

    protected static ?string $pluralLabel = 'Horas por Asignatura';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-eye';

    // Usa condicion depende del rol, la funcion se encuentra abajo
    // protected static string|\UnitEnum|null $navigationGroup = 'Consultar';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('nombre')
                    ->disabled(),
                Forms\Components\TextInput::make('horas_semanales')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            /* ->headerActions([
                ActionGroup::make([
                    Action::make('exportar')
                        ->label('Exportar a Excel')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('primary')
                        ->action(fn () => dd('Implementar exportar a Excel')),
                ])
            ]) */
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('grado')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('course.nombre')
                    ->label('Curso')
                    ->sortable(),
                Tables\Columns\TextColumn::make('horas_semanales')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('hours_t1')
                    ->label('Horas 1º Tri')
                    ->state(fn (Subject $record) => $record->calcularHorasPorTrimestre(1)),
                Tables\Columns\TextColumn::make('hours_t2')
                    ->label('Horas 2º Tri')
                    ->state(fn (Subject $record) => $record->calcularHorasPorTrimestre(2)),
                Tables\Columns\TextColumn::make('hours_t3')
                    ->label('Horas 3º Tri')
                    ->state(fn (Subject $record) => $record->calcularHorasPorTrimestre(3)),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                // View action only?
            ])
            ->bulkActions([
                //
            ]);
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
            'index' => Pages\ListSubjectHours::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()->hasRole('profesor')) {
            $subjectIds = auth()->user()->subjects()->pluck('subjects.id'); // IMPORTANTE: especificar la tabla
            return $query->whereIn('id', $subjectIds);
        }

        return $query; // Admin ve todo
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->count();
    }

    public static function getNavigationGroup(): ?string
    {
        $user = Auth::user();

        if ($user->hasRole('profesor')) {
            return 'Profesorado';
        }

        return 'Consultar';
    }

    public static function getNavigationLabel(): string
    {
        $user = Auth::user();

        if ($user->hasRole('profesor')) {
            return 'Mis horas';
        }

        return 'Horas por Asignatura';
    }
}
