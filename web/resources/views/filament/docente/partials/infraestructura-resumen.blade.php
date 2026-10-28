{{--
    Diagnóstico y proyectos de una propuesta de infraestructura (FAM).
    Estilos en línea: se usa en la vista previa y en el PDF (dompdf).
--}}
@php
    $proyectosInfra = $propuesta->proyectosInfraestructura()->with(['itemsPresupuesto', 'laminas'])->get();
    $dineroInfra = fn ($monto) => '$' . number_format((float) $monto, 2);
    $laminasInfra = \App\Models\ProyectoInfraestructura::LAMINAS;
    $celda = 'border:1px solid #CBD5E1;padding:6px 8px;font-size:11px;vertical-align:top;';
    $cabecera = 'border:1px solid #1A4B8C;padding:6px 8px;font-size:11px;background:#1A4B8C;color:#fff;text-align:left;';
@endphp

<div style="margin-bottom:12px">
    <strong>Diagnóstico de la capacidad física instalada</strong>
    <div class="text rico">{!! \App\Support\TextoEnriquecido::mostrar($propuesta->diagnostico, 'Sin diagnóstico registrado.') !!}</div>
</div>

<strong>Resumen de los proyectos</strong>
<table style="width:100%;border-collapse:collapse;margin:6px 0 14px">
    <thead>
        <tr>
            <th style="{{ $cabecera }}">Prioridad</th>
            <th style="{{ $cabecera }}">Proyecto</th>
            <th style="{{ $cabecera }}">Tipo</th>
            <th style="{{ $cabecera }}text-align:right">FAM</th>
            <th style="{{ $cabecera }}text-align:right">Otros fondos</th>
            <th style="{{ $cabecera }}text-align:right">Total</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($proyectosInfra as $p)
            <tr>
                <td style="{{ $celda }}">{{ $p->prioridad }}</td>
                <td style="{{ $celda }}">{{ $p->nombre }}</td>
                <td style="{{ $celda }}">{{ $p->tipoTexto() }}</td>
                <td style="{{ $celda }}text-align:right">{{ $dineroInfra($p->montoFam()) }}</td>
                <td style="{{ $celda }}text-align:right">{{ $dineroInfra($p->monto_otros_fondos) }}</td>
                <td style="{{ $celda }}text-align:right">{{ $dineroInfra($p->montoTotal()) }}</td>
            </tr>
        @empty
            <tr><td colspan="6" style="{{ $celda }}">Sin proyectos registrados.</td></tr>
        @endforelse
    </tbody>
    @if ($proyectosInfra->isNotEmpty())
        <tfoot>
            <tr>
                <td colspan="3" style="{{ $celda }}font-weight:bold">Total</td>
                <td style="{{ $celda }}text-align:right;font-weight:bold">{{ $dineroInfra($proyectosInfra->sum(fn ($p) => $p->montoFam())) }}</td>
                <td style="{{ $celda }}text-align:right;font-weight:bold">{{ $dineroInfra($proyectosInfra->sum(fn ($p) => (float) $p->monto_otros_fondos)) }}</td>
                <td style="{{ $celda }}text-align:right;font-weight:bold">{{ $dineroInfra($proyectosInfra->sum(fn ($p) => $p->montoTotal())) }}</td>
            </tr>
        </tfoot>
    @endif
</table>

@foreach ($proyectosInfra as $p)
    <div style="border:1px solid #E2E8F0;border-radius:8px;padding:10px 12px;margin-bottom:10px;page-break-inside:avoid">
        <div style="font-weight:bold;font-size:13px">{{ $p->prioridad }}. {{ $p->nombre }}</div>
        <div style="font-size:11px;color:#475569;margin-bottom:6px">{{ $p->tipoTexto() }}</div>
        <table style="width:100%;border-collapse:collapse;margin-bottom:6px">
            <tr>
                <td style="{{ $celda }}width:33%"><strong>Ubicación:</strong> {{ collect([$p->campus, $p->municipio])->filter()->implode(', ') ?: '—' }}{{ $p->coordenadas ? ' (' . $p->coordenadas . ')' : '' }}</td>
                <td style="{{ $celda }}width:33%"><strong>Beneficiarios:</strong> {{ number_format($p->beneficiarios()) }} alumnos ({{ (int) $p->alumnos_mujeres }} M · {{ (int) $p->alumnos_hombres }} H) · {{ (int) $p->personal_academico }} académicos</td>
                <td style="{{ $celda }}"><strong>Superficie:</strong> {{ $p->superficie_m2 !== null ? number_format((float) $p->superficie_m2, 2) . ' m²' : '—' }} · <strong>Periodo:</strong> {{ $p->fecha_inicio?->format('d/m/Y') ?? '—' }} – {{ $p->fecha_fin?->format('d/m/Y') ?? '—' }}</td>
            </tr>
        </table>
        @foreach (['objetivo' => 'Objetivo', 'justificacion' => 'Justificación', 'descripcion' => 'Descripción y acciones', 'impacto' => 'Impacto', 'financiamiento_previo' => 'Financiamiento previo'] as $campoInfra => $etiquetaInfra)
            @if (filled($p->{$campoInfra}))
                <div style="font-size:11px;margin-bottom:4px"><strong>{{ $etiquetaInfra }}:</strong> {!! nl2br(e($p->{$campoInfra})) !!}</div>
            @endif
        @endforeach
        <div style="font-size:11px;margin-top:4px"><strong>Láminas:</strong>
            {{ $p->laminas->map(fn ($l) => $l->nombre . ' (' . ($laminasInfra[$l->tipo_lamina] ?? 'Lámina') . ')')->implode('; ') ?: 'Sin láminas.' }}
            @if ($faltantesInfra = $p->laminasFaltantes())
                <span style="color:#B45309"> · Faltan: {{ collect($faltantesInfra)->map(fn ($l) => $laminasInfra[$l])->implode(', ') }}</span>
            @endif
        </div>
    </div>
@endforeach
