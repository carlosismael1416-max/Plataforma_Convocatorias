<?php

namespace App\Filament\Directivo\Pages;

use App\Models\Propuesta;
use App\Models\User;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

/**
 * Propuestas enviadas a revisión por docentes y directivos (incluidas las del
 * propio directivo) y los últimos dictámenes.
 */
class PropuestasPorRevisar extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    protected static ?string $navigationLabel = 'Propuestas por revisar';

    protected static ?string $title = 'Propuestas por revisar';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.directivo.pages.propuestas-por-revisar';

    // El título ya aparece en el header del panel.
    protected ?string $heading = '';

    public static function canAccess(): bool
    {
        $usuario = auth()->user();

        return $usuario instanceof User
            && (bool) $usuario->estado
            && $usuario->tieneRol('DIRECTIVO');
    }

    public static function getNavigationBadge(): ?string
    {
        $pendientes = Propuesta::query()->where('estado', 'ENVIADA')->count();

        return $pendientes > 0 ? (string) $pendientes : null;
    }

    protected function getViewData(): array
    {
        $fila = fn (Propuesta $p): array => [
            'id' => $p->id,
            'titulo' => $p->titulo,
            'autor' => trim(($p->usuario?->name ?? '') . ' ' . ($p->usuario?->apellidos ?? '')),
            'rol' => $p->usuario?->role?->nombre === 'DIRECTIVO' ? 'Directivo' : 'Docente',
            'propia' => $p->user_id === auth()->id(),
            'convocatoria' => $p->convocatoria?->titulo,
            'estado' => $p->estado,
            'fecha' => ($p->fecha_envio ?? $p->updated_at)?->locale('es')->isoFormat('D MMM YYYY'),
            'url' => RevisarPropuesta::getUrl(['propuesta' => $p->id]),
        ];

        $base = Propuesta::query()->with(['usuario.role', 'convocatoria:id,titulo']);

        return [
            'pendientes' => (clone $base)
                ->where('estado', 'ENVIADA')
                ->orderBy('fecha_envio')
                ->get()
                ->map($fila)
                ->all(),

            'dictaminadas' => (clone $base)
                ->whereIn('estado', ['APROBADA', 'RECHAZADA'])
                ->latest('updated_at')
                ->limit(10)
                ->get()
                ->map($fila)
                ->all(),
        ];
    }
}
