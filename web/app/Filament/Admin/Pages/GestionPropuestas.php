<?php

namespace App\Filament\Admin\Pages;

use App\Models\CategoriaGasto;
use App\Models\PresupuestoItem;
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
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;

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

    // Búsqueda sin acentos («viaticos» encuentra «Viáticos»).
    private const SIN_ACENTOS_NOMBRE = "translate(lower(nombre), 'áéíóúüñ', 'aeiouun')";

    private const SIN_ACENTOS_DESCRIPCION = "translate(lower(coalesce(descripcion, '')), 'áéíóúüñ', 'aeiouun')";

    /** Tarjeta de capítulo seleccionada arriba de la tabla ('' = todos). */
    #[Url(as: 'capitulo')]
    public string $capitulo = '';

    public function verCapitulo(string $capitulo): void
    {
        $this->capitulo = preg_match('/^[1-9]$/', $capitulo) ? $capitulo : '';
        $this->resetPage();
    }

    /** Partidas que elige el docente: específicas, o genéricas sin específicas. */
    private static function soloElegibles($query)
    {
        return $query->where(fn ($q) => $q->where('nivel', 'PARTIDA_ESPECIFICA')
            ->orWhere(fn ($g) => $g->where('nivel', 'PARTIDA_GENERICA')
                ->whereNotExists(fn ($h) => $h->selectRaw('1')->from('categorias_gasto as h')->whereColumn('h.padre_clave', 'categorias_gasto.clave'))));
    }

    protected function getViewData(): array
    {
        $capitulos = CategoriaGasto::query()->where('nivel', 'CAPITULO')->orderBy('orden')->get();
        $elegibles = self::soloElegibles(CategoriaGasto::query())->pluck('clave');

        return [
            'capitulos' => $capitulos->map(fn (CategoriaGasto $c) => [
                'digito' => $c->clave[0],
                'clave' => $c->clave,
                'nombre' => $c->nombre,
                'tipo' => $c->tipoGastoTexto(),
                'elegibles' => $elegibles->filter(fn ($clave) => str_starts_with((string) $clave, $c->clave[0]))->count(),
                'conceptos' => CategoriaGasto::query()->where('padre_clave', $c->clave)->count(),
                'activa' => $c->activa,
            ]),
            'totalElegibles' => $elegibles->count(),
            'totalPartidas' => CategoriaGasto::count(),
            'enUso' => PresupuestoItem::query()->whereNotNull('categoria_gasto')->distinct('categoria_gasto')->count('categoria_gasto'),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(CategoriaGasto::query())
            ->modifyQueryUsing(fn ($query) => $query
                ->addSelect(['usos_count' => PresupuestoItem::query()->selectRaw('count(*)')->whereColumn('presupuesto_items.categoria_gasto', 'categorias_gasto.clave')])
                ->addSelect(['hijas_count' => CategoriaGasto::query()->from('categorias_gasto as h')->selectRaw('count(*)')->whereColumn('h.padre_clave', 'categorias_gasto.clave')])
                ->when($this->capitulo !== '', fn ($q) => $q->where('clave', 'like', $this->capitulo . '%')))
            ->defaultSort('orden')
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->searchPlaceholder('Buscar por número o nombre')
            ->columns([
                TextColumn::make('clave')
                    ->label('Clave')
                    ->weight('bold')
                    ->color(fn (CategoriaGasto $record): ?string => in_array($record->nivel, ['CAPITULO', 'CONCEPTO'], true) ? 'primary' : null)
                    ->searchable(query: fn ($query, string $search) => $query->where('clave', 'like', trim($search) . '%'))
                    ->sortable(query: fn ($query, string $direction) => $query->orderBy('orden', $direction)),
                // Nombre con la definición debajo (recortada; completa en el tooltip).
                TextColumn::make('nombre')
                    ->label('Partida')
                    ->weight(fn (CategoriaGasto $record): string => in_array($record->nivel, ['CAPITULO', 'CONCEPTO'], true) ? 'bold' : 'semibold')
                    ->description(fn (CategoriaGasto $record): ?string => $record->descripcion ? Str::limit($record->descripcion, 150) : 'Sin definición')
                    ->tooltip(fn (CategoriaGasto $record): ?string => mb_strlen((string) $record->descripcion) > 150 ? $record->descripcion : null)
                    ->searchable(query: fn ($query, string $search) => $query
                        ->orWhereRaw(self::SIN_ACENTOS_NOMBRE . ' like ?', ['%' . Str::lower(Str::ascii(trim($search))) . '%'])
                        ->orWhereRaw(self::SIN_ACENTOS_DESCRIPCION . ' like ?', ['%' . Str::lower(Str::ascii(trim($search))) . '%']))
                    ->wrap()
                    ->grow(),
                TextColumn::make('nivel')
                    ->label('Nivel')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'CAPITULO' => 'primary',
                        'CONCEPTO' => 'info',
                        'PARTIDA_GENERICA' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => CategoriaGasto::NIVELES[$state] ?? $state)
                    ->description(fn (CategoriaGasto $record): ?string => match (true) {
                        in_array($record->nivel, ['CAPITULO', 'CONCEPTO'], true) => (int) $record->hijas_count . ' dentro',
                        $record->nivel === 'PARTIDA_GENERICA' && (int) $record->hijas_count > 0 => (int) $record->hijas_count . ((int) $record->hijas_count === 1 ? ' específica' : ' específicas'),
                        default => '✓ La elige el docente',
                    }),
                TextColumn::make('tipo_gasto')
                    ->label('Tipo de gasto')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'CAPITAL' => 'warning',
                        'PENSIONES' => 'gray',
                        default => 'info',
                    })
                    ->formatStateUsing(fn (string $state): string => CategoriaGasto::TIPOS_GASTO[$state] ?? $state)
                    ->toggleable(),
                TextColumn::make('usos_count')
                    ->label('Usos')
                    ->alignCenter()
                    ->badge()
                    ->color(fn ($state): string => (int) $state > 0 ? 'success' : 'gray')
                    ->formatStateUsing(fn ($state): string => (int) $state > 0 ? (string) $state : '—')
                    ->tooltip('Conceptos de presupuesto que la usan')
                    ->toggleable(),
                IconColumn::make('activa')
                    ->label('Se ofrece')
                    ->state(fn (CategoriaGasto $record): bool => $record->ofrecida())
                    ->boolean()
                    ->tooltip(fn (CategoriaGasto $record): string => $record->ofrecida()
                        ? 'Disponible para el docente'
                        : ($record->activa ? 'Oculta porque un nivel de arriba no se ofrece' : 'No se ofrece')),
                TextColumn::make('updated_at')
                    ->label('Modificada')
                    ->since()
                    ->dateTimeTooltip('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('nivel')
                    ->label('Nivel')
                    ->options(CategoriaGasto::NIVELES),
                SelectFilter::make('tipo_gasto')
                    ->label('Tipo de gasto')
                    ->options(CategoriaGasto::TIPOS_GASTO),
                TernaryFilter::make('elegibles')
                    ->label('La elige el docente')
                    ->placeholder('Todas')
                    ->trueLabel('Solo las que elige el docente')
                    ->falseLabel('Solo capítulos, conceptos y genéricas con específicas')
                    ->queries(
                        true: fn ($query) => self::soloElegibles($query),
                        false: fn ($query) => $query->whereNotIn('clave', self::soloElegibles(CategoriaGasto::query())->select('clave')),
                    ),
                TernaryFilter::make('activa')
                    ->label('Se ofrece')
                    ->placeholder('Todas')
                    ->trueLabel('Se ofrecen')
                    ->falseLabel('No se ofrecen'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Nueva partida')
                    ->icon(Heroicon::OutlinedPlus)
                    ->modalHeading('Nueva partida del clasificador')
                    ->schema($this->campos(nueva: true))
                    ->successNotificationTitle('Partida agregada')
                    ->authorize(fn (): bool => static::canAccess()),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Editar')
                    ->iconButton()
                    ->tooltip('Editar')
                    ->modalHeading(fn (CategoriaGasto $record): string => 'Editar ' . $record->clave . ' · ' . $record->nombre)
                    ->schema($this->campos(nueva: false))
                    ->successNotificationTitle('Partida actualizada')
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
                // Si la usa algún concepto o tiene partidas dentro, no se borra: deja de ofrecerse.
                DeleteAction::make()
                    ->label('Eliminar')
                    ->iconButton()
                    ->tooltip('Eliminar')
                    ->modalHeading(fn (CategoriaGasto $record): string => 'Eliminar ' . $record->clave . ' · ' . $record->nombre)
                    ->modalDescription('Nadie la usa y no contiene partidas. Esta acción no se puede deshacer.')
                    ->visible(fn (CategoriaGasto $record): bool => (int) $record->usos_count === 0 && (int) $record->hijas_count === 0)
                    ->authorize(fn (): bool => static::canAccess()),
            ])
            ->emptyStateIcon(Heroicon::OutlinedMagnifyingGlass)
            ->emptyStateHeading('No hay partidas con esos criterios')
            ->emptyStateDescription('Prueba con otro número o nombre, quita los filtros o elige «Todos» en las tarjetas de arriba.');
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
