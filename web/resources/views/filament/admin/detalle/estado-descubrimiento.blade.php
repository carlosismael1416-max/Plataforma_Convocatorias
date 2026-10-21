@php
    $d = $getRecord();
    $convocatoria = $d->convocatoria_id ? \App\Models\Convocatoria::query()->find($d->convocatoria_id) : null;
    [$fondo, $borde, $color, $icono, $titulo, $texto] = match (true) {
        $d->estado === 'EN_PROCESO' => ['#EFF6FF', '#BFDBFE', '#1E3A8A', '⏳', 'Extrayendo…', 'El motor está descargando la página y sus PDF, extrayendo los datos y validándolos. Esta página se actualiza sola.'],
        $convocatoria?->estado === 'PUBLICADA' => ['#ECFDF5', '#A7F3D0', '#065F46', '✅', 'Publicada', 'Se extrajo y el validador la aprobó: ya la ven docentes y directivos.'],
        $convocatoria && in_array($convocatoria->estado, ['PENDIENTE_REVISION', 'DESCARTADA'], true) => ['#FFFBEB', '#FDE68A', '#92400E', '🟡', $convocatoria->estado === 'DESCARTADA' ? 'Convocatoria descartada' : 'Extraída, sin publicar', 'Se creó la convocatoria #' . $convocatoria->id . ', pero el validador no la publicó. Usa «Revisar y decidir publicación» para ver por qué y decidir.'],
        $convocatoria !== null => ['#F8FAFC', '#E2E8F0', '#334155', '📄', 'Con convocatoria', 'Este descubrimiento ya generó la convocatoria #' . $convocatoria->id . '.'],
        default => ['#F8FAFC', '#E2E8F0', '#334155', '🔎', 'Sin extraer', in_array($d->estado, ['APROBADO', 'ERROR_TEMPORAL'], true)
            ? 'El ciclo automático lo procesará en su turno. Si no quieres esperar, usa «Extraer ahora».'
            : 'El ciclo automático no lo procesará (estado ' . $d->estado . '). Si crees que sí es una convocatoria, usa «Extraer ahora»: se extrae y valida igual que siempre.'],
    };
@endphp
<div @if ($d->estado === 'EN_PROCESO') wire:poll.5s="refrescarEstado" @endif style="display: flex; gap: 12px; align-items: flex-start; padding: 14px 16px; border: 1px solid {{ $borde }}; border-radius: 14px; background: {{ $fondo }}; color: {{ $color }}">
    <span style="font-size: 20px; line-height: 1" aria-hidden="true">{{ $icono }}</span>
    <div>
        <strong style="display: block; font-size: 14px">{{ $titulo }}</strong>
        <span style="font-size: 13px; line-height: 1.5">{{ $texto }}</span>
    </div>
</div>
