<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\MultiSelect;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Hash;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'Profesores';

    protected static ?string $pluralLabel = 'Listado de Profesores';

    protected static ?string $slug = 'profesores';

    protected static string|\UnitEnum|null $navigationGroup = 'Administración';

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->placeholder('Nombre del profesor')
                    ->required(),
                TextInput::make('email')
                    ->label('Email')
                    ->placeholder('Email del profesor')
                    ->email()
                    ->required(),
                TextInput::make('password')
                    ->label('Contraseña')
                    ->placeholder('Contraseña del profesor')
                    ->password()
                    ->helperText(fn ($state) => filled($state) ? 'Dejar en blanco para no cambiar la contraseña': '')
                    ->required(fn (string $context): bool => $context === 'create') // Campo requerido unicamente en el crear
                    ->visible(fn (string $context): bool => $context === 'create') // Campo visible unicamente en el crear
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null) // Si el campo esta vacio lo deja como null, si no lo hashea
                    ->dehydrated(fn ($state) => filled($state)), // Si el campo esta vacio no se guarda
                TextInput::make('phone')
                    ->label('Teléfono')
                    ->placeholder('+34 600 123 456')
                    ->tel()
                    ->regex('/^[0-9+\-\s]+$/')
                    ->minLength(8)
                    ->maxLength(15)
                    ->nullable()
                    ->default(null),
                MultiSelect::make('subjects')
                    ->label('Asignaturas')
                    ->placeholder('Asignaturas')
                    ->relationship('subjects', 'nombre', fn (Builder $query) => $query->with('course'))
                    /* ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->nombre} ({$record->course->nombre})") */
                    ->getOptionLabelFromRecordUsing(fn ($record) => 
                        ($record->nombre ?? 'N/A') . ' (' . ($record->course->nombre ?? 'N/A') . ')'
                    )
                    ->preload()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('subjects.nombre')
                    ->label('Asignaturas')
                    ->badge(),
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
                    ->label('Fecha de eliminacion')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('asignaturas')
                    ->relationship('subjects', 'nombre')
                    ->multiple()
                    ->label('Asignaturas'),
                SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->label('Rol'),
            ])
            ->recordActions([
                EditAction::make()
                    ->successNotificationTitle('Profesor editado correctamente')
                    ->modalHeading('Editar profesor'),
                DeleteAction::make()
                    ->successNotificationTitle('Profesor eliminado correctamente'),
                ForceDeleteAction::make()
                    ->successNotificationTitle('Profesor eliminado correctamente'),
                RestoreAction::make()
                    ->successNotificationTitle('Profesor restaurado correctamente'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->successNotificationTitle('Profesores eliminados correctamente'),
                    ForceDeleteBulkAction::make()
                        ->successNotificationTitle('Profesores eliminados correctamente'),
                    RestoreBulkAction::make()
                        ->successNotificationTitle('Profesores restaurados correctamente'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageUsers::route('/'),
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
        return 'Numero de profesores';
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole('admin');
    }
}
