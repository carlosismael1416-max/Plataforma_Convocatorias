@php
    $c = $getRecord();
    [$tipoTexto, $tipoIcono] = $c->tipo === 'NO_APLICA' ? ['No aplica (no se publica)', '🚫'] : $c->infoTipo();
    $vencida = in_array($c->estado, ['PUBLICADA', 'ACTIVA'], true) && $c->fecha_cierre?->lt(today());
    $estado = $vencida ? 'Vencida' : ([
        'PENDIENTE_REVISION' => 'No publicada', 'PUBLICADA' => 'Publicada', 'ACTIVA' => 'Activa',
        'CERRADA' => 'Cerrada', 'DESCARTADA' => 'Descartada', 'ARCHIVADA' => 'Archivada',
    ][$c->estado] ?? $c->estado);
    $visible = ! $vencida && in_array($c->estado, ['PUBLICADA', 'ACTIVA'], true);
@endphp
@include('filament.admin.detalle.estilos')
<section class="det-cab">
    <div class="det-avatar" aria-hidden="true" style="font-size: 30px">{{ $tipoIcono }}</div>
    <div class="det-info">
        <h2>{{ $c->titulo }}</h2>
        <p>{{ $c->organismo?->nombre ?? 'Sin organismo' }}</p>
        <div class="det-chips">
            <span class="det-chip">{{ $tipoTexto }}</span>
            <span @class(['det-chip', 'det-chip-ok' => $visible, 'det-chip-no' => ! $visible])>● {{ $estado }}{{ $visible ? ' · la ven docentes y directivos' : '' }}</span>
            <span class="det-chip">{{ match ($c->origen) { 'MANUAL' => '✍ Subida a mano', 'PROGRAMA' => '📅 Programa anual', default => '🤖 Motor' } }}</span>
            @if ($c->url_original)
                <a class="det-chip" href="{{ $c->url_original }}" target="_blank" rel="noopener noreferrer" style="color: #fff; text-decoration: none">Sitio oficial ↗</a>
            @endif
        </div>
    </div>
    <div class="det-cifras">
        <div class="det-cifra">
            <strong style="font-size: 16px">{{ $c->fecha_cierre?->format('d/m/Y') ?? '—' }}</strong>
            <span>{{ $c->fecha_cierre ? ($c->fecha_cierre->isToday() ? 'Cierra hoy' : $c->fecha_cierre->locale('es')->diffForHumans(today(), ['syntax' => \Carbon\CarbonInterface::DIFF_RELATIVE_TO_NOW])) : 'Sin fecha de cierre' }}</span>
        </div>
        <div class="det-cifra">
            <strong style="font-size: 16px">{{ $c->monto_maximo !== null ? '$' . number_format((float) $c->monto_maximo, 0) : '—' }}</strong>
            <span>{{ $c->monto_maximo !== null ? 'Monto máximo ' . ($c->moneda ?: 'MXN') : 'Sin monto' }}</span>
        </div>
    </div>
</section>
