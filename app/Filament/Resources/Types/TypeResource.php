<?php

namespace App\Filament\Resources\Types;

use App\Filament\Resources\Types\Pages\ManageTypes;
use App\Models\Type;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ColorColumn;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TypeResource extends Resource
{
    protected static ?string $model = Type::class;

    protected static ?string $navigationLabel = 'Tipos de Días';

    protected static ?string $pluralLabel = 'Listado de Tipos de Días';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static ?string $recordTitleAttribute = 'nombre';

    protected static ?string $slug = 'tipos-de-dias';

    protected static string|\UnitEnum|null $navigationGroup = 'Administración';

    protected static ?int $navigationSort = 7;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombre')
                    ->label('Nombre')
                    ->placeholder('Nombre del tipo de día')
                    ->required()
                    ->minLength(3)
                    ->maxLength(100)
                    ->validationMessages([
                        'required' => 'El nombre es obligatorio',
                        'min' => 'El nombre debe tener al menos 3 caracteres',
                        'max' => 'El nombre debe tener como máximo 100 caracteres',
                    ]),
                ColorPicker::make('color')
                    ->label('Color')
                    ->required()
                    ->validationMessages([
                        'required' => 'El color es obligatorio',
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nombre')
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->badge()
                    ->color(fn ($record) => $record->color ? Color::hex($record->color) : null)
                    ->searchable(),
                ColorColumn::make('color')
                    ->label('Color'),
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
            ])
            ->recordActions([
                EditAction::make()
                    ->successNotificationTitle('Tipo de día editado correctamente'),
                DeleteAction::make()
                    ->successNotificationTitle('Tipo de día eliminado correctamente'),
                ForceDeleteAction::make()
                    ->successNotificationTitle('Tipo de día eliminado correctamente'),
                RestoreAction::make()
                    ->successNotificationTitle('Tipo de día restaurado correctamente'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->successNotificationTitle('Tipos de días eliminados correctamente'),
                    ForceDeleteBulkAction::make()
                        ->successNotificationTitle('Tipos de días eliminados correctamente'),
                    RestoreBulkAction::make()
                        ->successNotificationTitle('Tipos de días restaurados correctamente'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageTypes::route('/'),
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
        return 'Numero de tipos de días';
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole('admin');
    }
}
