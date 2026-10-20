<x-filament-panels::page>
    @include('filament.itsva.pestanas-usuarios', ['activa' => 'departamentos'])

    <div style="margin-bottom: 14px; color: #64748B; font-size: 14px; max-width: 760px; line-height: 1.5">
        Áreas a las que pertenecen los usuarios (por ejemplo, Ingeniería en Sistemas o Investigación y Posgrado).
        También puedes crear uno al registrar o editar un usuario, con el botón «+» del campo Departamento.
    </div>

    {{ $this->table }}
</x-filament-panels::page>
