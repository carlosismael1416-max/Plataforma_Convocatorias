{{-- Pestañas «Vigentes / Historial» de Buscar convocatorias. Uso:
     @include('filament.itsva.pestanas-convocatorias', ['activa' => 'vigentes'|'historial']) --}}
@php
    $panel = filament()->getId();
    $urlVigentes = route('filament.' . $panel . '.pages.buscar-convocatorias');
    $urlHistorial = route('filament.' . $panel . '.pages.historial-convocatorias');
    $vigentes = \App\Models\Convocatoria::query()->visibles()->count();
    $cerradas = \App\Models\Convocatoria::query()->historial()->count();
@endphp
<nav class="itsva-pestanas" aria-label="Secciones de convocatorias">
    <style>
        .itsva-pestanas { display: flex; gap: 6px; width: fit-content; max-width: 100%; margin-bottom: 16px; padding: 5px; background: #EEF2F7; border-radius: 12px; overflow-x: auto; }
        .itsva-pestana { display: inline-flex; align-items: center; gap: 8px; padding: 9px 16px; border-radius: 9px; color: #475569; font-size: 13px; font-weight: 700; text-decoration: none; white-space: nowrap; }
        .itsva-pestana:hover { color: #1A4B8C; }
        .itsva-pestana.activa { background: #fff; color: #1A4B8C; box-shadow: 0 1px 3px rgba(15, 23, 42, .12); }
        .itsva-pestana-n { padding: 1px 8px; border-radius: 999px; background: #E2E8F0; font-size: 11px; }
        .itsva-pestana.activa .itsva-pestana-n { background: #E8EEF8; }
    </style>
    <a href="{{ $urlVigentes }}" @class(['itsva-pestana', 'activa' => $activa === 'vigentes']) @if ($activa === 'vigentes') aria-current="page" @endif>
        🔎 Vigentes <span class="itsva-pestana-n">{{ $vigentes }}</span>
    </a>
    <a href="{{ $urlHistorial }}" @class(['itsva-pestana', 'activa' => $activa === 'historial']) @if ($activa === 'historial') aria-current="page" @endif>
        🗂️ Historial (cerradas) <span class="itsva-pestana-n">{{ $cerradas }}</span>
    </a>
</nav>
