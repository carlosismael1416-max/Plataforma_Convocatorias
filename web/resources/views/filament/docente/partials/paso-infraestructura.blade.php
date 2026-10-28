{{-- Paso extra de las propuestas de infraestructura (FAM), después de «Datos». --}}
@if ($propuesta->esInfraestructura())
    @php
        $completo = filled(\App\Support\TextoEnriquecido::texto($propuesta->diagnostico))
            && $propuesta->proyectosInfraestructura()->exists();
    @endphp
    <a class="step {{ request()->routeIs('filament.*.pages.infraestructura-propuesta') ? 'active' : '' }} {{ $completo ? 'done' : '' }}" href="{{ route('filament.' . filament()->getId() . '.pages.infraestructura-propuesta', ['propuesta' => $propuesta->id]) }}">
        <span class="step-number">🏗</span>
        Infraestructura (FAM)
    </a>
@endif
