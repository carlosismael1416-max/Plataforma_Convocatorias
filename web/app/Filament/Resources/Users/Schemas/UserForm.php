<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(
        Schema $schema
    ): Schema {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),

                TextInput::make('apellidos')
                    ->label('Apellidos')
                    ->maxLength(150),

                TextInput::make('email')
                    ->label(
                        'Correo institucional'
                    )
                    ->email()
                    ->required()
                    ->unique(
                        ignoreRecord: true
                    )
                    ->maxLength(255)
                    ->columnSpanFull(),

                TextInput::make('password')
                    ->label('Contraseña')
                    ->password()
                    ->revealable()
                    ->minLength(8)
                    ->maxLength(255)
                    ->required(
                        fn (
                            string $operation
                        ): bool =>
                            $operation
                            === 'create'
                    )
                    ->same(
                        'password_confirmation'
                    )
                    ->dehydrated(
                        fn (
                            ?string $state
                        ): bool =>
                            filled($state)
                    )
                    ->helperText(
                        'Mínimo 8 caracteres. '
                        . 'En edición, déjala vacía '
                        . 'para conservar la contraseña actual.'
                    ),

                TextInput::make(
                    'password_confirmation'
                )
                    ->label(
                        'Confirmar contraseña'
                    )
                    ->password()
                    ->revealable()
                    ->minLength(8)
                    ->maxLength(255)
                    ->dehydrated(false),

                Select::make('role_id')
                    ->label('Rol')
                    ->relationship(
                        'role',
                        'nombre',
                        modifyQueryUsing:
                            fn ($query) =>
                                $query->where(
                                    'estado',
                                    true
                                )
                    )
                    ->searchable()
                    ->preload()
                    ->required()
                    ->disabled(
                        fn (
                            ?User $record
                        ): bool =>
                            $record !== null
                            && (int)
                                $record->id
                                === (int)
                                auth()->id()
                    )
                    ->helperText(
                        fn (
                            ?User $record
                        ): ?string =>
                            $record !== null
                            && (int)
                                $record->id
                                === (int)
                                auth()->id()
                                    ? 'Tu propio rol está protegido.'
                                    : null
                    ),

                Select::make(
                    'departamento_id'
                )
                    ->label('Departamento')
                    ->relationship(
                        'departamento',
                        'nombre',
                        modifyQueryUsing:
                            fn ($query) =>
                                $query->where(
                                    'estado',
                                    true
                                )
                    )
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->placeholder(
                        'Sin departamento'
                    ),

                Toggle::make('estado')
                    ->label('Usuario activo')
                    ->default(true)
                    ->disabled(
                        fn (
                            ?User $record
                        ): bool =>
                            $record !== null
                            && (int)
                                $record->id
                                === (int)
                                auth()->id()
                    )
                    ->helperText(
                        fn (
                            ?User $record
                        ): ?string =>
                            $record !== null
                            && (int)
                                $record->id
                                === (int)
                                auth()->id()
                                    ? 'No puedes desactivar tu propia cuenta desde este módulo.'
                                    : null
                    ),
            ]);
    }
}
