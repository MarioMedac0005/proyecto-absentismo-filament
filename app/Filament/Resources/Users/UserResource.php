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
                    ->rules(['required', 'string', 'min:3', 'max:255'])
                    ->validationMessages([
                        'required' => 'El nombre es obligatorio',
                        'string' => 'El nombre debe ser texto',
                        'min' => 'El nombre debe tener al menos 3 caracteres',
                        'max' => 'El nombre debe tener como maximo 255 caracteres',
                    ]),
                TextInput::make('email')
                    ->label('Email')
                    ->placeholder('Email del profesor')
                    ->required()
                    ->email()
                    ->maxLength(255)
                    ->unique(ignorable: fn ($record) => $record)
                    ->rules([
                        function ($attribute, $value, $fail) {
                            $user = \App\Models\User::withTrashed()->where('email', $value)->first();
                            if ($user && $user->trashed()) {
                                $fail('Este email pertenece a un usuario eliminado (papelera). Restaurelo si desea usarlo de nuevo.');
                            }
                        },
                    ])
                    ->validationMessages([
                        'required' => 'El email es obligatorio',
                        'email' => 'El email debe ser un correo electronico valido',
                        'max' => 'El email debe tener como maximo 255 caracteres',
                        'unique' => 'El email ya esta registrado',
                    ]),
                TextInput::make('password')
                    ->label('Contraseña')
                    ->placeholder('Contraseña del profesor')
                    ->password()
                    ->helperText(fn ($state) => filled($state) ? 'Dejar en blanco para no cambiar la contraseña': '')
                    ->required(fn (string $context): bool => $context === 'create') // Campo requerido unicamente en el crear
                    ->validationMessages([
                        'required' => 'La contraseña es obligatoria',
                    ])
                    ->visible(fn (string $context): bool => $context === 'create') // Campo visible unicamente en el crear
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null) // Si el campo esta vacio lo deja como null, si no lo hashea
                    ->dehydrated(fn ($state) => filled($state)), // Si el campo esta vacio no se guarda
                TextInput::make('phone')
                    ->label('Teléfono')
                    ->placeholder('+34 600 123 456')
                    ->tel()
                    ->rules(['regex:/^[0-9+\-\s]+$/', 'min:8', 'max:15'])
                    ->validationMessages([
                        'regex' => 'El telefono debe contener solo numeros, espacios y guiones',
                        'min' => 'El telefono debe tener al menos 8 caracteres',
                        'max' => 'El telefono debe tener como maximo 15 caracteres',
                    ])
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
                    ->required()
                    ->validationMessages([
                        'required' => 'El profesor debe tener al menos una asignatura',
                    ]),
                \Filament\Forms\Components\Select::make('roles')
                    ->label('Rol')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
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
                TextColumn::make('roles.name')
                    ->label('Rol')
                    ->badge()
                    ->toggleable(),
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
                    ->multiple()
                    ->label('Asignaturas')
                    ->options(\App\Models\Subject::whereNotNull('nombre')->pluck('nombre', 'id')),
                SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->label('Rol'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Editar')
                    ->successNotificationTitle('Profesor editado correctamente')
                    ->modalHeading('Editar profesor'),
                DeleteAction::make()
                    ->label('Eliminar')
                    ->modalHeading('Eliminar profesor')
                    ->modalDescription('¿Está seguro de que desea eliminar este profesor?')
                    ->modalSubmitActionLabel('Sí, eliminar')
                    ->successNotificationTitle('Profesor eliminado correctamente'),
                ForceDeleteAction::make()
                    ->label('Forzar eliminación')
                    ->modalHeading('Forzar eliminación de profesor')
                    ->modalDescription('¿Está seguro de que desea eliminar permanentemente este profesor? Esta acción no se puede deshacer.')
                    ->modalSubmitActionLabel('Sí, eliminar permanentemente')
                    ->successNotificationTitle('Profesor eliminado permanentemente'),
                RestoreAction::make()
                    ->label('Restaurar')
                    ->modalHeading('Restaurar profesor')
                    ->modalDescription('¿Está seguro de que desea restaurar este profesor?')
                    ->modalSubmitActionLabel('Sí, restaurar')
                    ->successNotificationTitle('Profesor restaurado correctamente'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Eliminar seleccionados')
                        ->modalHeading('Eliminar profesores seleccionados')
                        ->modalDescription('¿Está seguro de que desea eliminar los profesores seleccionados?')
                        ->modalSubmitActionLabel('Sí, eliminar')
                        ->successNotificationTitle('Profesores eliminados correctamente'),
                    ForceDeleteBulkAction::make()
                        ->label('Forzar eliminación de seleccionados')
                        ->modalHeading('Forzar eliminación de profesores seleccionados')
                        ->modalDescription('¿Está seguro de que desea eliminar permanentemente los profesores seleccionados? Esta acción no se puede deshacer.')
                        ->modalSubmitActionLabel('Sí, eliminar permanentemente')
                        ->successNotificationTitle('Profesores eliminados permanentemente'),
                    RestoreBulkAction::make()
                        ->label('Restaurar seleccionados')
                        ->modalHeading('Restaurar profesores seleccionados')
                        ->modalDescription('¿Está seguro de que desea restaurar los profesores seleccionados?')
                        ->modalSubmitActionLabel('Sí, restaurar')
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
