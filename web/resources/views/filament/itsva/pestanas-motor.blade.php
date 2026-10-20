{{-- Pestañas «Motor y ejecuciones / Descubrimientos» del admin. Uso:
     @include('filament.itsva.pestanas-motor', ['activa' => 'motor'|'descubrimientos']) --}}
@php
    $enEspera = \App\Models\DescubrimientoWeb::query()
        ->whereIn('estado', \App\Filament\Resources\DescubrimientoWebs\Pages\ListDescubrimientoWebs::EN_ESPERA)
        ->count();
@endphp
<nav class="itsva-pestanas" aria-label="Secciones del motor">
    <style>
        .itsva-pestanas { display: flex; gap: 6px; width: fit-content; max-width: 100%; margin-bottom: 16px; padding: 5px; background: #EEF2F7; border-radius: 12px; overflow-x: auto; }
        .itsva-pestana { display: inline-flex; align-items: center; gap: 8px; padding: 9px 16px; border-radius: 9px; color: #475569; font-size: 13px; font-weight: 700; text-decoration: none; white-space: nowrap; }
        .itsva-pestana:hover { color: #047857; }
        .itsva-pestana.activa { background: #fff; color: #047857; box-shadow: 0 1px 3px rgba(15, 23, 42, .12); }
        .itsva-pestana-n { padding: 1px 8px; border-radius: 999px; background: #E2E8F0; font-size: 11px; }
        .itsva-pestana.activa .itsva-pestana-n { background: #D1FAE5; }
    </style>
    <a href="{{ \App\Filament\Admin\Pages\MotorExtraccion::getUrl() }}" @class(['itsva-pestana', 'activa' => $activa === 'motor']) @if ($activa === 'motor') aria-current="page" @endif>
        ⚙️ Motor y ejecuciones
    </a>
    <a href="{{ \App\Filament\Resources\DescubrimientoWebs\DescubrimientoWebResource::getUrl('index') }}" @class(['itsva-pestana', 'activa' => $activa === 'descubrimientos']) @if ($activa === 'descubrimientos') aria-current="page" @endif>
        🔍 Descubrimientos
        @if ($enEspera)
            <span class="itsva-pestana-n" title="En espera de decisión">{{ $enEspera }} en espera</span>
        @endif
    </a>
</nav>
