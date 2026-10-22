<x-filament-panels::page>
    @include('filament.itsva.pestanas-admin-convocatorias', ['activa' => 'organismos'])

    <div style="margin-bottom: 14px; color: #64748B; font-size: 14px; max-width: 820px; line-height: 1.5">
        Dependencias e instituciones que publican convocatorias. El motor reconoce el organismo de una convocatoria por el dominio de su <strong>sitio web</strong>, así que conviene registrarlo (por ejemplo, https://secihti.mx).
    </div>

    {{ $this->table }}
</x-filament-panels::page>
