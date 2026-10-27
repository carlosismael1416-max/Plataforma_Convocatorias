<?php

namespace App\Filament\Admin\Pages;

use App\Models\CategoriaGasto;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

/**
 * «Gestión de propuestas» (administrador y directivo): por ahora, las
 * categorías de gasto que el docente elige al armar el presupuesto.
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
            ->reorderable('orden')
            ->paginated(false)
            ->columns([
                TextColumn::make('nombre')
                    ->label('Categoría')
                    ->description(fn (CategoriaGasto $record): ?string => $record->descripcion)
                    ->weight('bold')
                    ->searchable(),
                TextColumn::make('usos')
                    ->label('Conceptos que la usan')
                    ->state(fn (CategoriaGasto $record): int => $record->usos())
                    ->alignCenter(),
                IconColumn::make('activa')
                    ->label('Se ofrece')
                    ->boolean(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Nueva categoría')
                    ->modalHeading('Nueva categoría de gasto')
                    ->schema($this->campos())
                    ->authorize(fn (): bool => static::canAccess()),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Editar')
                    ->iconButton()
                    ->tooltip('Editar')
                    ->modalHeading('Editar categoría de gasto')
                    ->schema($this->campos())
                    ->authorize(fn (): bool => static::canAccess()),
                // Si ya la usa algún concepto, no se borra: se desactiva.
                DeleteAction::make()
                    ->label('Eliminar')
                    ->iconButton()
                    ->tooltip('Eliminar')
                    ->visible(fn (CategoriaGasto $record): bool => $record->usos() === 0)
                    ->authorize(fn (): bool => static::canAccess()),
                Action::make('desactivar')
                    ->label(fn (CategoriaGasto $record): string => $record->activa ? 'Dejar de ofrecer' : 'Volver a ofrecer')
                    ->icon(fn (CategoriaGasto $record) => $record->activa ? Heroicon::OutlinedEyeSlash : Heroicon::OutlinedEye)
                    ->iconButton()
                    ->tooltip(fn (CategoriaGasto $record): string => $record->activa ? 'Dejar de ofrecer (los conceptos que la usan se conservan)' : 'Volver a ofrecer')
                    ->color('gray')
                    ->visible(fn (CategoriaGasto $record): bool => $record->usos() > 0)
                    ->authorize(fn (): bool => static::canAccess())
                    ->action(function (CategoriaGasto $record): void {
                        $record->update(['activa' => ! $record->activa]);
                        Notification::make()->title($record->activa ? 'Se vuelve a ofrecer' : 'Ya no se ofrece')->success()->send();
                    }),
            ])
            ->emptyStateHeading('Sin categorías de gasto');
    }

    private function campos(): array
    {
        return [
            TextInput::make('nombre')
                ->label('Nombre')
                ->placeholder('Ejemplo: Software y licencias')
                ->required()
                ->maxLength(100),
            TextInput::make('descripcion')
                ->label('Descripción (opcional)')
                ->placeholder('Qué gastos entran en esta categoría')
                ->maxLength(500),
            Toggle::make('activa')
                ->label('Se ofrece al docente')
                ->default(true),
        ];
    }
}
