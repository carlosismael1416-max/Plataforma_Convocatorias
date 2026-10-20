<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Departamento;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class UserForm
{
    public static function configure(
        Schema $schema
    ): Schema {
        // El administrador no puede quitarse su propio rol ni desactivarse.
        $esUnoMismo = fn (?User $record): bool => $record !== null
            && (int) $record->id === (int) auth()->id();

        return $schema
            ->columns(['default' => 1, 'xl' => 3])
            ->components([
                Section::make('Datos personales')
                    ->icon(Heroicon::OutlinedUser)
                    ->columns(2)
                    ->columnSpan(['default' => 1, 'xl' => 2])
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->autocomplete('off'),

                        TextInput::make('apellidos')
                            ->label('Apellidos')
                            ->maxLength(150)
                            ->autocomplete('off'),

                        // «off» evita que el navegador ponga el correo del
                        // administrador que inició sesión.
                        TextInput::make('email')
                            ->label('Correo institucional')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->autocomplete('off')
                            ->placeholder('nombre@itsva.edu.mx')
                            ->prefixIcon(Heroicon::OutlinedEnvelope)
                            ->columnSpanFull(),
                    ]),

                Section::make('Rol y departamento')
                    ->icon(Heroicon::OutlinedIdentification)
                    ->columnSpan(['default' => 1, 'xl' => 1])
                    ->schema([
                        Select::make('role_id')
                            ->label('Rol')
                            ->relationship('role', 'nombre', modifyQueryUsing: fn ($query) => $query->where('estado', true))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->disabled($esUnoMismo)
                            ->helperText(fn (?User $record): ?string => $esUnoMismo($record) ? 'Tu propio rol está protegido.' : null),

                        Select::make('departamento_id')
                            ->label('Departamento')
                            ->relationship('departamento', 'nombre', modifyQueryUsing: fn ($query) => $query->where('estado', true))
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->native(false)
                            ->placeholder('Sin departamento')
                            ->helperText('¿No aparece? Créalo con el botón «+».')
                            ->createOptionForm([
                                TextInput::make('nombre')
                                    ->label('Nombre del departamento')
                                    ->required()
                                    ->maxLength(150)
                                    ->unique(Departamento::class, 'nombre'),
                                Textarea::make('descripcion')
                                    ->label('Descripción')
                                    ->rows(2),
                            ])
                            ->createOptionUsing(fn (array $data): int => Departamento::create($data + ['estado' => true])->getKey())
                            ->createOptionModalHeading('Nuevo departamento'),

                        Toggle::make('estado')
                            ->label('Usuario activo')
                            ->default(true)
                            ->disabled($esUnoMismo)
                            ->helperText(fn (?User $record): ?string => $esUnoMismo($record) ? 'No puedes desactivar tu propia cuenta desde este módulo.' : 'Un usuario inactivo no puede entrar.'),
                    ]),

                Section::make('Acceso')
                    ->icon(Heroicon::OutlinedKey)
                    ->description(fn (string $operation): string => $operation === 'create'
                        ? 'Contraseña inicial (mínimo 8 caracteres). El usuario podrá cambiarla desde su perfil.'
                        : 'Déjalas vacías para conservar la contraseña actual.')
                    ->columns(2)
                    ->columnSpan(['default' => 1, 'xl' => 2])
                    ->schema([
                        TextInput::make('password')
                            ->label('Contraseña')
                            ->password()
                            ->revealable()
                            ->minLength(8)
                            ->maxLength(255)
                            ->autocomplete('new-password')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->same('password_confirmation')
                            ->dehydrated(fn (?string $state): bool => filled($state)),

                        TextInput::make('password_confirmation')
                            ->label('Confirmar contraseña')
                            ->password()
                            ->revealable()
                            ->minLength(8)
                            ->maxLength(255)
                            ->autocomplete('new-password')
                            ->dehydrated(false),
                    ]),
            ]);
    }
}
