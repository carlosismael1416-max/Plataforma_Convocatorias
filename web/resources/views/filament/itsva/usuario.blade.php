@php
    $usuario = filament()->auth()->user();
    $nombre = trim(($usuario?->name ?? '') . ' ' . ($usuario?->apellidos ?? ''));
    $iniciales = collect(preg_split('/\s+/', $nombre ?: 'U'))
        ->filter()
        ->take(2)
        ->map(fn (string $parte): string => mb_strtoupper(mb_substr($parte, 0, 1)))
        ->implode('');
    $perfil = collect(filament()->getPages())
        ->first(fn (string $pagina): bool => class_basename($pagina) === 'Perfil');
@endphp

@if ($usuario)
    <a
        href="{{ $perfil ? $perfil::getUrl() : '#' }}"
        class="itsva-usuario"
    >
        <span class="itsva-avatar itsva-avatar-{{ $rol ?? 'docente' }}">{{ $iniciales }}</span>
        <span class="itsva-usuario-texto">
            <span class="itsva-usuario-nombre">{{ $nombre }}</span>
            <span class="itsva-usuario-correo">{{ $usuario->email }}</span>
        </span>
    </a>
@endif
