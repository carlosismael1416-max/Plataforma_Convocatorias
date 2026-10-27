<?php

namespace App\Filament\Admin\Pages;

use App\Models\CategoriaGasto;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

/**
 * «Gestión de propuestas» (administrador y directivo): por ahora, el
 * Clasificador por Objeto del Gasto (CONAC) del que el docente elige la
 * partida de cada concepto del presupuesto.
 */
class GestionPropuestas extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.admin.pages.gestion-propuestas';

    protected ?string $heading = '';

    protected static ?string $navigationLabel = 'Gestión de propuestas';

    protected static ?string $title = 'Gestión de propuestas';

    protected static ?string $slug = 'gestion-propuestas';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static ?int $navigationSort = 9;

    public static function canAccess(): bool
    {
        $usuario = auth()->user();

        return $usuario instanceof User
            && (bool) $usuario->estado
            && ($usuario->tieneRol('ADMINISTRADOR') || $usuario->tieneRol('DIRECTIVO'));
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(CategoriaGasto::query())
            ->defaultSort('orden')
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->searchPlaceholder('Buscar por número o nombre')
            ->columns([
                TextColumn::make('clave')
                    ->label('Clave')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(query: fn ($query, string $direction) => $query->orderBy('orden', $direction)),
                TextColumn::make('nivel')
                    ->label('Nivel')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'CAPITULO' => 'primary',
                        'CONCEPTO' => 'info',
                        'PARTIDA_GENERICA' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => CategoriaGasto::NIVELES[$state] ?? $state),
                TextColumn::make('nombre')
                    ->label('Nombre y definición')
                    ->description(fn (CategoriaGasto $record): ?string => $record->descripcion ? Str::limit($record->descripcion, 160) : null)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('tipo_gasto')
                    ->label('Tipo de gasto')
                    ->formatStateUsing(fn (string $state): string => CategoriaGasto::TIPOS_GASTO[$state] ?? $state),
                TextColumn::make('usos')
                    ->label('Usos')
                    ->state(fn (CategoriaGasto $record): int => $record->usos())
                    ->alignCenter(),
                IconColumn::make('activa')
                    ->label('Se ofrece')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('capitulo')
                    ->label('Capítulo')
                    ->options(fn (): array => CategoriaGasto::query()->where('nivel', 'CAPITULO')->orderBy('orden')->get()
                        ->mapWithKeys(fn (CategoriaGasto $c) => [$c->clave[0] => $c->clave . ' · ' . $c->nombre])->all())
                    ->query(fn ($query, array $data) => filled($data['value'] ?? null) ? $query->where('clave', 'like', $data['value'] . '%') : $query),
                SelectFilter::make('nivel')
                    ->label('Nivel')
                    ->options(CategoriaGasto::NIVELES),
                SelectFilter::make('tipo_gasto')
                    ->label('Tipo de gasto')
                    ->options(CategoriaGasto::TIPOS_GASTO),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Nueva partida')
                    ->modalHeading('Nueva partida del clasificador')
                    ->schema($this->campos(nueva: true))
                    ->authorize(fn (): bool => static::canAccess()),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Editar')
                    ->iconButton()
                    ->tooltip('Editar')
                    ->modalHeading(fn (CategoriaGasto $record): string => 'Editar ' . $record->clave)
                    ->schema($this->campos(nueva: false))
                    ->authorize(fn (): bool => static::canAccess()),
                // Si la usa algún concepto o tiene partidas dentro, no se borra: deja de ofrecerse.
                DeleteAction::make()
                    ->label('Eliminar')
                    ->iconButton()
                    ->tooltip('Eliminar')
                    ->visible(fn (CategoriaGasto $record): bool => $record->usos() === 0 && ! $record->tieneHijas())
                    ->authorize(fn (): bool => static::canAccess()),
                Action::make('desactivar')
                    ->label(fn (CategoriaGasto $record): string => $record->activa ? 'Dejar de ofrecer' : 'Volver a ofrecer')
                    ->icon(fn (CategoriaGasto $record) => $record->activa ? Heroicon::OutlinedEyeSlash : Heroicon::OutlinedEye)
                    ->iconButton()
                    ->tooltip(fn (CategoriaGasto $record): string => $record->activa
                        ? 'Dejar de ofrecer' . ($record->nivel === 'PARTIDA_ESPECIFICA' ? '' : ' (también todo lo que contiene)') . '; los conceptos que la usan se conservan'
                        : 'Volver a ofrecer')
                    ->color('gray')
                    ->authorize(fn (): bool => static::canAccess())
                    ->action(function (CategoriaGasto $record): void {
                        $record->update(['activa' => ! $record->activa]);
                        Notification::make()->title($record->activa ? 'Se vuelve a ofrecer' : 'Ya no se ofrece')->success()->send();
                    }),
            ])
            ->emptyStateHeading('Sin partidas');
    }

    private function campos(bool $nueva): array
    {
        return [
            TextInput::make('clave')
                ->label('Clave')
                ->helperText('Capítulo 2000, concepto 2100, partida genérica 211 o específica 2111. Debe existir el nivel de arriba.')
                ->required()
                ->disabled(! $nueva)
                ->dehydrated($nueva)
                ->rules($nueva ? [
                    'regex:/^[1-9](000|[1-9]00|[1-9][1-9]|[1-9][1-9][1-9])$/',
                    'unique:categorias_gasto,clave',
                    fn () => function (string $attribute, mixed $value, \Closure $fail): void {
                        $padre = CategoriaGasto::padreDeClave((string) $value);
                        if ($padre && ! CategoriaGasto::query()->where('clave', $padre)->exists()) {
                            $fail("Primero debe existir {$padre}.");
                        }
                    },
                ] : [])
                ->validationMessages(['regex' => 'Usa el formato del clasificador: 2000, 2100, 211 o 2111.', 'unique' => 'Esa clave ya existe.']),
            TextInput::make('nombre')
                ->label('Nombre')
                ->placeholder('Ejemplo: Software')
                ->required()
                ->maxLength(255),
            Textarea::make('descripcion')
                ->label('Definición')
                ->rows(4)
                ->maxLength(3000),
            Select::make('tipo_gasto')
                ->label('Tipo de gasto')
                ->options(CategoriaGasto::TIPOS_GASTO)
                ->default('CORRIENTE')
                ->required(),
            Toggle::make('activa')
                ->label('Se ofrece al docente')
                ->default(true),
        ];
    }
}
