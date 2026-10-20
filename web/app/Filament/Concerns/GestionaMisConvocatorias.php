<?php

namespace App\Filament\Concerns;

use App\Models\Convocatoria;
use App\Models\UsuarioConvocatoria;
use App\Services\RegistroActividad;
use Filament\Notifications\Notification;

/**
 * Guardar, quitar y marcar como favorita una convocatoria desde cualquier
 * página (buscador, detalle) de docente o directivo. Solo se pueden guardar
 * o marcar las vigentes (`visibles()`); quitar siempre se puede.
 * Cada acción revalida el acceso de la página en el servidor.
 */
trait GestionaMisConvocatorias
{
    public function guardarEnMis(int $convocatoriaId): void
    {
        if ($seguimiento = $this->seguimientoNuevo($convocatoriaId, 'Agregada a Mis convocatorias')) {
            RegistroActividad::registrar('MIS_CONVOCATORIAS', 'Guardó en Mis convocatorias «' . mb_strimwidth((string) $seguimiento->convocatoria?->titulo, 0, 160, '…') . '»', $seguimiento->convocatoria);
        }

        $this->despuesDeCambiarSeguimiento();

        Notification::make()
            ->title('Convocatoria guardada')
            ->body('Se agregó a Mis convocatorias.')
            ->success()
            ->send();
    }

    public function quitarDeMis(int $convocatoriaId): void
    {
        abort_unless(static::canAccess(), 403);

        UsuarioConvocatoria::query()
            ->where('user_id', auth()->id())
            ->where('convocatoria_id', $convocatoriaId)
            ->delete();

        $convocatoria = Convocatoria::find($convocatoriaId);
        RegistroActividad::registrar('MIS_CONVOCATORIAS', 'Quitó de Mis convocatorias «' . mb_strimwidth((string) $convocatoria?->titulo, 0, 160, '…') . '»', $convocatoria);

        $this->despuesDeCambiarSeguimiento();

        Notification::make()
            ->title('Convocatoria quitada')
            ->body('Se quitó de Mis convocatorias y de favoritas.')
            ->success()
            ->send();
    }

    /** Marca o desmarca como favorita; si no estaba guardada, la guarda. */
    public function alternarFavorita(int $convocatoriaId): void
    {
        abort_unless(static::canAccess(), 403);

        $seguimiento = UsuarioConvocatoria::query()
            ->where('user_id', auth()->id())
            ->where('convocatoria_id', $convocatoriaId)
            ->first();

        if ($seguimiento === null) {
            $seguimiento = $this->seguimientoNuevo($convocatoriaId, 'Marcada como favorita');

            if ($seguimiento === null) {
                return;
            }

            $seguimiento->es_favorita = false;
        }

        $seguimiento->es_favorita = ! $seguimiento->es_favorita;
        $seguimiento->ultima_accion = $seguimiento->es_favorita ? 'Marcada como favorita' : 'Quitada de favoritas';
        $seguimiento->fecha_ultima_accion = now();
        $seguimiento->save();

        RegistroActividad::registrar('MIS_CONVOCATORIAS', ($seguimiento->es_favorita ? 'Marcó como favorita' : 'Quitó de favoritas') . ' «' . mb_strimwidth((string) $seguimiento->convocatoria?->titulo, 0, 160, '…') . '»', $seguimiento->convocatoria);

        $this->despuesDeCambiarSeguimiento();

        Notification::make()
            ->title($seguimiento->es_favorita ? 'Marcada como favorita' : 'Quitada de favoritas')
            ->success()
            ->send();
    }

    /** Ids de convocatorias guardadas y favoritas del usuario, para pintar botones. */
    protected function idsDeMisConvocatorias(): array
    {
        $filas = UsuarioConvocatoria::query()
            ->where('user_id', auth()->id())
            ->get(['convocatoria_id', 'es_favorita']);

        return [
            'guardadas' => $filas->pluck('convocatoria_id')->map(fn ($id): int => (int) $id)->all(),
            'favoritas' => $filas->where('es_favorita', true)->pluck('convocatoria_id')->map(fn ($id): int => (int) $id)->all(),
        ];
    }

    /** Hook para que la página refresque su estado tras un cambio. */
    protected function despuesDeCambiarSeguimiento(): void {}

    private function seguimientoNuevo(int $convocatoriaId, string $accion): ?UsuarioConvocatoria
    {
        abort_unless(static::canAccess(), 403);

        $convocatoria = Convocatoria::query()->visibles()->whereKey($convocatoriaId)->first();

        if ($convocatoria === null) {
            Notification::make()
                ->title('Convocatoria no disponible')
                ->body('Solo se pueden guardar convocatorias publicadas y vigentes.')
                ->warning()
                ->send();

            return null;
        }

        $seguimiento = UsuarioConvocatoria::query()->firstOrNew([
            'user_id' => auth()->id(),
            'convocatoria_id' => $convocatoria->id,
        ]);

        if (! $seguimiento->exists) {
            $seguimiento->estado_seguimiento = 'GUARDADA';
            $seguimiento->prioridad = 'MEDIA';
            $seguimiento->es_favorita = false;
        }

        $seguimiento->ultima_accion = $accion;
        $seguimiento->fecha_ultima_accion = now();
        $seguimiento->save();

        return $seguimiento;
    }
}
