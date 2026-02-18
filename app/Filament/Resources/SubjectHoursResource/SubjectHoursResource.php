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

/**
 * SubjectHoursResource — Recurso de Filament para visualizar las horas lectivas por asignatura.
 *
 * Este recurso es de solo lectura (no permite crear ni editar registros directamente).
 * Su función es mostrar una tabla con las horas calculadas por trimestre para cada asignatura,
 * adaptando la vista según el rol del usuario autenticado:
 *
 * - Administrador: ve todas las asignaturas de todos los profesores.
 * - Profesor: ve únicamente las asignaturas que él imparte.
 *
 * Nota: Las horas NO se almacenan en la base de datos. Se calculan en tiempo real
 * llamando a Subject::calcularHorasPorTrimestre() para cada fila de la tabla.
 */
class SubjectHoursResource extends Resource
{
    // Modelo Eloquent sobre el que trabaja este recurso
    protected static ?string $model = Subject::class;

    // Slug de la URL: /admin/subject-hours
    protected static ?string $slug = 'subject-hours';

    // Etiqueta plural que aparece en el panel (se sobreescribe dinámicamente abajo)
    protected static ?string $pluralLabel = 'Horas por Asignatura';

    // Icono del menú de navegación lateral
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-eye';

    // Orden de aparición en el menú de navegación
    protected static ?int $navigationSort = 6;

    /**
     * Formulario del recurso (actualmente deshabilitado para edición).
     * Los campos están en modo 'disabled' porque este recurso es de solo consulta.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('nombre')
                    ->disabled(), // Solo lectura
                Forms\Components\TextInput::make('horas_semanales')
                    ->disabled(), // Solo lectura
            ]);
    }

    /**
     * Define la tabla principal del recurso con sus columnas.
     *
     * Las columnas 'hours_t1', 'hours_t2' y 'hours_t3' son columnas virtuales:
     * no existen en la base de datos, sino que se calculan en tiempo real
     * usando el método calcularHorasPorTrimestre() del modelo Subject.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Nombre de la asignatura — con búsqueda y ordenación
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable()
                    ->sortable(),

                // Grado del ciclo (primero/segundo) — mostrado como badge visual
                Tables\Columns\TextColumn::make('grado')
                    ->badge()
                    ->sortable(),

                // Nombre del curso al que pertenece la asignatura (relación)
                Tables\Columns\TextColumn::make('course.nombre')
                    ->label('Curso')
                    ->sortable(),

                // Horas semanales — oculto por defecto, se puede activar con el toggle
                Tables\Columns\TextColumn::make('horas_semanales')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Horas del 1º trimestre — calculadas en tiempo real
                Tables\Columns\TextColumn::make('hours_t1')
                    ->label('Horas 1º Tri')
                    ->state(function (Subject $record) {
                        // Si es profesor, calcular solo sus horas; si es admin, calcular el total
                        $userId = auth()->user()->hasRole('profesor') ? auth()->id() : null;
                        return $record->calcularHorasPorTrimestre(1, $userId);
                    }),

                // Horas del 2º trimestre — calculadas en tiempo real
                Tables\Columns\TextColumn::make('hours_t2')
                    ->label('Horas 2º Tri')
                    ->state(function (Subject $record) {
                        $userId = auth()->user()->hasRole('profesor') ? auth()->id() : null;
                        return $record->calcularHorasPorTrimestre(2, $userId);
                    }),

                // Horas del 3º trimestre — calculadas en tiempo real
                Tables\Columns\TextColumn::make('hours_t3')
                    ->label('Horas 3º Tri')
                    ->state(function (Subject $record) {
                        $userId = auth()->user()->hasRole('profesor') ? auth()->id() : null;
                        return $record->calcularHorasPorTrimestre(3, $userId);
                    }),

                // Fechas de auditoría — ocultas por defecto
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
                // Sin filtros adicionales por ahora
            ])
            ->actions([
                // Sin acciones de fila (solo consulta)
            ])
            ->bulkActions([
                // Sin acciones masivas
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubjectHours::route('/'),
        ];
    }

    /**
     * Personaliza la consulta Eloquent base del recurso.
     *
     * - Carga las relaciones 'course' y 'schedules' de forma eager (evita N+1 queries).
     * - Si el usuario es profesor, filtra para mostrar solo sus asignaturas asignadas.
     * - Si el usuario es admin, devuelve todas las asignaturas sin filtrar.
     */
    public static function getEloquentQuery(): Builder
    {
        // Cargar relaciones necesarias para el cálculo de horas
        $query = parent::getEloquentQuery()->with(['course', 'schedules']);

        if (auth()->user()->hasRole('profesor')) {
            // Obtener los IDs de las asignaturas asignadas al profesor actual
            // IMPORTANTE: especificar 'subjects.id' para evitar ambigüedad en el JOIN
            $subjectIds = auth()->user()->subjects()->pluck('subjects.id');
            return $query->whereIn('id', $subjectIds);
        }

        return $query; // Admin ve todo sin restricciones
    }

    /**
     * Badge numérico que aparece junto al enlace en el menú de navegación.
     * Muestra el número de asignaturas visibles para el usuario actual.
     */
    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->count();
    }

    /**
     * Tooltip que aparece al pasar el ratón sobre el badge del menú.
     */
    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Numero de horas por asignatura';
    }

    /**
     * Grupo de navegación dinámico según el rol del usuario.
     * - Profesor → aparece en el grupo "Profesorado"
     * - Admin    → aparece en el grupo "Consultar"
     */
    public static function getNavigationGroup(): ?string
    {
        $user = Auth::user();

        if ($user->hasRole('profesor')) {
            return 'Profesorado';
        }

        return 'Consultar';
    }

    /**
     * Etiqueta del enlace de navegación, también dinámica según el rol.
     * - Profesor → "Mis horas" (más personal y directo)
     * - Admin    → "Horas por Asignatura" (más descriptivo)
     */
    public static function getNavigationLabel(): string
    {
        $user = Auth::user();

        if ($user->hasRole('profesor')) {
            return 'Mis horas';
        }

        return 'Horas por Asignatura';
    }
}
