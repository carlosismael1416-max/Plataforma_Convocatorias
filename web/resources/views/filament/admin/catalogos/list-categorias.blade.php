<x-filament-panels::page>
    @include('filament.itsva.pestanas-admin-convocatorias', ['activa' => 'categorias'])

    <div style="margin-bottom: 14px; color: #64748B; font-size: 14px; max-width: 820px; line-height: 1.5">
        Áreas temáticas con las que se clasifican las convocatorias (por ejemplo, investigación).
    </div>

    {{ $this->table }}
</x-filament-panels::page>
