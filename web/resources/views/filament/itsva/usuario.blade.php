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

    // Cuentas con las que se inició sesión en este navegador (Cambiar de cuenta).
    $idsRecordados = (array) session(\App\Http\Controllers\AccesoUnificadoController::CUENTAS_SESION, []);
    $otrasCuentas = \App\Models\User::query()
        ->with('role:id,nombre')
        ->whereIn('id', array_diff($idsRecordados, [$usuario?->id]))
        ->where('estado', true)
        ->orderBy('name')
        ->get(['id', 'name', 'apellidos', 'email', 'role_id', 'foto']);
    $rolesTexto = ['ADMINISTRADOR' => 'Administrador', 'DIRECTIVO' => 'Directivo', 'DOCENTE' => 'Docente'];
@endphp

@if ($usuario)
    <div class="itsva-usuario-zona" x-data="{ abierto: false }" @keydown.escape.window="abierto = false" @click.outside="abierto = false">
        <a href="{{ $perfil ? $perfil::getUrl() : '#' }}" class="itsva-usuario">
            @if ($foto = $usuario->fotoUrl())
                <img class="itsva-avatar itsva-avatar-foto" src="{{ $foto }}" alt="">
            @else
                <span class="itsva-avatar itsva-avatar-{{ $rol ?? 'docente' }}">{{ $iniciales }}</span>
            @endif
            <span class="itsva-usuario-texto">
                <span class="itsva-usuario-nombre">{{ $nombre }}</span>
                <span class="itsva-usuario-correo">{{ $usuario->email }}</span>
            </span>
        </a>

        <button type="button" class="itsva-engrane" @click="abierto = ! abierto" :aria-expanded="abierto" aria-haspopup="menu" aria-label="Configuración de la cuenta" title="Configuración">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
        </button>

        <div class="itsva-menu-cuenta" role="menu" x-show="abierto" x-transition.opacity x-cloak>
            <div class="itsva-menu-titulo">Cuenta actual</div>
            <div class="itsva-menu-actual">
                <strong>{{ $nombre }}</strong>
                <span>{{ $rolesTexto[$usuario->role?->nombre] ?? $usuario->role?->nombre }} · {{ $usuario->email }}</span>
            </div>

            @if ($otrasCuentas->isNotEmpty())
                <div class="itsva-menu-titulo">Cambiar a</div>
                @foreach ($otrasCuentas as $cuenta)
                    <form method="POST" action="{{ route('cuentas.cambiar', $cuenta) }}">
                        @csrf
                        <button type="submit" class="itsva-menu-opcion" role="menuitem">
                            <span class="itsva-menu-cuenta-nombre">{{ trim($cuenta->name . ' ' . $cuenta->apellidos) }}</span>
                            <span class="itsva-menu-cuenta-rol">{{ $rolesTexto[$cuenta->role?->nombre] ?? $cuenta->role?->nombre }} · {{ $cuenta->email }}</span>
                        </button>
                    </form>
                @endforeach
            @endif

            <a href="{{ route('login', ['agregar' => 1]) }}" class="itsva-menu-opcion" role="menuitem">＋ Agregar otra cuenta</a>
            @if ($perfil)
                <a href="{{ $perfil::getUrl() }}" class="itsva-menu-opcion" role="menuitem">👤 Mi perfil</a>
            @endif

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="itsva-menu-opcion itsva-menu-salir" role="menuitem">⏻ Cerrar sesión</button>
            </form>
            @if ($otrasCuentas->isNotEmpty())
                <div class="itsva-menu-nota">Cerrar sesión cierra todas las cuentas de este navegador.</div>
            @endif
        </div>
    </div>
@endif
