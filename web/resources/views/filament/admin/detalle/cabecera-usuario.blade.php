@php
    $u = $getRecord();
    $nombre = trim($u->name . ' ' . ($u->apellidos ?? ''));
    $iniciales = mb_strtoupper(collect(preg_split('/\s+/', $nombre))->filter()->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode(''));
@endphp
@include('filament.admin.detalle.estilos')
<section class="det-cab">
    <div class="det-avatar" aria-hidden="true">{{ $iniciales ?: 'U' }}</div>
    <div class="det-info">
        <h2>{{ $nombre }}</h2>
        <p>{{ $u->email }}</p>
        <div class="det-chips">
            <span class="det-chip">🛡️ {{ ucfirst(mb_strtolower($u->role?->nombre ?? 'Sin rol')) }}</span>
            <span class="det-chip">🏢 {{ $u->departamento?->nombre ?? 'Sin departamento' }}</span>
            <span @class(['det-chip', 'det-chip-ok' => $u->estado, 'det-chip-no' => ! $u->estado])>{{ $u->estado ? '● Activo' : '● Inactivo' }}</span>
        </div>
    </div>
    <div class="det-cifras">
        <div class="det-cifra"><strong>{{ \App\Models\Propuesta::where('user_id', $u->id)->count() }}</strong><span>Propuestas</span></div>
        <div class="det-cifra"><strong>{{ \App\Models\UsuarioConvocatoria::where('user_id', $u->id)->count() }}</strong><span>Guardadas</span></div>
    </div>
</section>
