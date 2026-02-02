<?php

namespace App\Filament\Resources\Calendars;

use App\Filament\Resources\Calendars\Pages\ManageCalendars;
use App\Models\Calendar;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Group;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CalendarResource extends Resource
{
    protected static ?string $model = Calendar::class;

    protected static ?string $navigationLabel = 'Calendarios';

    protected static ?string $pluralLabel = 'Listado de Calendarios';

    protected static ?string $slug = 'calendarios';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $recordTitleAttribute = 'fecha';

    protected static string|\UnitEnum|null $navigationGroup = 'Administración';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make([
                    ToggleButtons::make('is_range')
                        ->label('Modo de creación')
                        ->options([
                            0 => 'Un solo día',
                            1 => 'Rango de fechas',
                        ])
                        ->icons([
                            0 => 'heroicon-m-calendar',
                            1 => 'heroicon-m-calendar-days',
                        ])
                        ->colors([
                            0 => 'primary',
                            1 => 'warning',
                        ])
                        ->grouped()
                        ->default(0)
                        ->reactive()
                        ->visible(fn ($operation) => $operation === 'create'),
                ])
                ->columnSpanFull()
                ->extraAttributes(['class' => 'flex justify-center w-full']),

                DatePicker::make('fecha')
                    ->label('Fecha')
                    ->placeholder('Selecciona una fecha')
                    ->required(fn ($get, $operation) => $operation === 'create' && ! $get('is_range'))
                    ->visible(fn ($get, $operation) => $operation === 'edit' || ! $get('is_range'))
                    ->native(false)
                    ->closeOnDateSelection(),
                
                DatePicker::make('start_date')
                    ->label('Fecha Inicio')
                    ->placeholder('Selecciona una fecha')
                    ->required(fn ($get, $operation) => $operation === 'create' && $get('is_range'))
                    ->visible(fn ($get, $operation) => $operation === 'create' && $get('is_range'))
                    ->native(false)
                    ->closeOnDateSelection(),

                DatePicker::make('end_date')
                    ->label('Fecha Fin')
                    ->placeholder('Selecciona una fecha')
                    ->required(fn ($get, $operation) => $operation === 'create' && $get('is_range'))
                    ->visible(fn ($get, $operation) => $operation === 'create' && $get('is_range'))
                    ->afterOrEqual('start_date')
                    ->native(false)
                    ->closeOnDateSelection(),

                TextInput::make('descripcion')
                    ->label('Descripción')
                    ->placeholder('Descripción del día')
                    ->default(null),

                Select::make('type_id')
                    /* ->relationship('type', 'nombre') */
                    ->relationship('type', 'nombre', fn($query) => $query->whereNotNull('nombre'))
                    ->required()
                    ->label('Tipo de día')
                    ->placeholder('Selecciona un tipo')
                    ->searchable()
                    ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('fecha')
            ->columns([
                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),
                TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->searchable(),
                TextColumn::make('type.nombre')
                    ->label('Tipo')
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
                SelectFilter::make('tipo')
                    ->relationship('type', 'nombre')
                    ->label('Tipo')
                    ->multiple()
            ])
            ->recordActions([
                EditAction::make()
                    ->successNotificationTitle('Calendario actualizado correctamente'),
                DeleteAction::make()
                    ->successNotificationTitle('Calendario eliminado correctamente'),
                ForceDeleteAction::make()
                    ->successNotificationTitle('Calendario eliminado definitivamente'),
                RestoreAction::make()
                    ->successNotificationTitle('Calendario restaurado correctamente'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->successNotificationTitle('Calendarios eliminados correctamente'),
                    ForceDeleteBulkAction::make()
                        ->successNotificationTitle('Calendarios eliminados definitivamente'),
                    RestoreBulkAction::make()
                        ->successNotificationTitle('Calendarios restaurados correctamente'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCalendars::route('/'),
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
        return 'Numero de calendarios';
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole('admin');
    }
}
