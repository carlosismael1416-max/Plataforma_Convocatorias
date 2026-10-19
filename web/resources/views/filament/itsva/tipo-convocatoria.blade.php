{{-- Marcas de tipo y de a quién va dirigida una convocatoria. Uso:
     @include('filament.itsva.tipo-convocatoria', ['convocatoria' => $c]) --}}
@php
    [$tipoTexto, $tipoIcono, $tipoFondo, $tipoColor] = $convocatoria->infoTipo();
    $audiencias = $convocatoria->etiquetasDirigidoA();
@endphp
<span class="itsva-marcas" style="display:inline-flex; flex-wrap:wrap; gap:6px; align-items:center;">
    <span title="Tipo de convocatoria" style="display:inline-flex; align-items:center; gap:4px; padding:3px 9px; border-radius:999px; background:{{ $tipoFondo }}; color:{{ $tipoColor }}; font-size:11px; font-weight:700;">
        <span aria-hidden="true">{{ $tipoIcono }}</span>{{ $tipoTexto }}
    </span>
    @foreach ($audiencias as $audiencia)
        <span title="Dirigida a" style="display:inline-flex; align-items:center; gap:4px; padding:3px 9px; border-radius:999px; background:#F1F5F9; color:#334155; font-size:11px; font-weight:600; border:1px solid #E2E8F0;">
            <span aria-hidden="true">👤</span>{{ $audiencia }}
        </span>
    @endforeach
</span>
