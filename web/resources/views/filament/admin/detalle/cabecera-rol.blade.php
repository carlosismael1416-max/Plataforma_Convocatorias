@php
    $r = $getRecord();
@endphp
@include('filament.admin.detalle.estilos')
<section class="det-cab">
    <div class="det-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($r->nombre, 0, 2)) }}</div>
    <div class="det-info">
        <h2>{{ ucfirst(mb_strtolower($r->nombre)) }}</h2>
        <p>{{ $r->descripcion ?: 'Sin descripción' }}</p>
        <div class="det-chips">
            <span @class(['det-chip', 'det-chip-ok' => $r->estado, 'det-chip-no' => ! $r->estado])>{{ $r->estado ? '● Rol activo' : '● Rol inactivo' }}</span>
        </div>
    </div>
    <div class="det-cifras">
        <div class="det-cifra"><strong>{{ $r->users()->count() }}</strong><span>Usuarios</span></div>
        <div class="det-cifra"><strong>{{ $r->permisos()->count() }}</strong><span>Permisos</span></div>
    </div>
</section>
