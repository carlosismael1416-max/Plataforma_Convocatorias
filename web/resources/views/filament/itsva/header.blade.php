@php
    $usuario = filament()->auth()->user();

    // Título: el elemento activo del menú lateral; si la página no está en
    // el menú (detalle, perfil, editor...), su propia etiqueta.
    $pagina = request()->route()?->getControllerClass();

    $titulo = collect(filament()->getNavigation())
        ->flatMap(fn ($grupo) => $grupo->getItems())
        ->first(fn ($item) => $item->isActive())
        ?->getLabel()
        ?? (is_string($pagina) && is_subclass_of($pagina, \Filament\Pages\Page::class) ? $pagina::getNavigationLabel() : null)
        ?? 'Convocatorias ITSVA';

    $paginas = collect(filament()->getPages());
    $buscador = $paginas->first(fn (string $p): bool => class_basename($p) === 'BuscarConvocatorias');
    $notificaciones = $paginas->first(fn (string $p): bool => class_basename($p) === 'Notificaciones');

    $urlBusqueda = null;
    $campoBusqueda = 'q';

    if ($buscador) {
        $urlBusqueda = $buscador::getUrl();
    } elseif (filament()->getId() === 'admin') {
        $urlBusqueda = \App\Filament\Resources\Convocatorias\ConvocatoriaResource::getUrl('index');
        $campoBusqueda = 'tableSearch';
    }

    $nombre = trim(($usuario?->name ?? '') . ' ' . ($usuario?->apellidos ?? ''));
    $rolNombre = $usuario?->role?->nombre;
    $rolEtiqueta = ['DOCENTE' => 'Docente', 'DIRECTIVO' => 'Directivo', 'ADMINISTRADOR' => 'Administrador'][$rolNombre] ?? '';
    $rolClase = ['DOCENTE' => 'docente', 'DIRECTIVO' => 'directivo', 'ADMINISTRADOR' => 'admin'][$rolNombre] ?? 'docente';
    $perfil = $paginas->first(fn (string $p): bool => class_basename($p) === 'Perfil');

    $sinLeer = $usuario
        ? \App\Models\Notificacion::query()
            ->where('user_id', $usuario->getKey())
            ->where('leida', false)
            ->count()
        : 0;
@endphp

<header class="itsva-header">
    <h1 class="itsva-header-titulo">{{ $titulo }}</h1>

    <div class="itsva-header-acciones">
        @if ($urlBusqueda)
            <form method="GET" action="{{ $urlBusqueda }}" class="itsva-header-busqueda" role="search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                <input
                    type="search"
                    name="{{ $campoBusqueda }}"
                    placeholder="Buscar convocatorias..."
                    aria-label="Buscar convocatorias"
                >
            </form>
        @endif

        @if ($notificaciones)
            <a
                href="{{ $notificaciones::getUrl() }}"
                class="itsva-header-campana"
                aria-label="Notificaciones{{ $sinLeer ? " ({$sinLeer} sin leer)" : '' }}"
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 8a6 6 0 1 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                @if ($sinLeer)
                    <span class="itsva-header-contador">{{ $sinLeer > 9 ? '9+' : $sinLeer }}</span>
                @endif
            </a>
        @endif

        @if ($usuario)
            <div class="itsva-menu" x-data="{ abierto: false }" x-on:click.outside="abierto = false" x-on:keydown.escape.window="abierto = false">
                <button type="button" class="itsva-menu-boton" x-on:click="abierto = ! abierto" x-bind:aria-expanded="abierto" aria-haspopup="true">
                    <img class="itsva-menu-avatar" src="{{ filament()->getUserAvatarUrl($usuario) }}" alt="">
                    <span class="itsva-menu-texto">
                        <span class="itsva-menu-nombre">{{ $nombre }}</span>
                        <span class="itsva-menu-rol itsva-menu-rol-{{ $rolClase }}">{{ $rolEtiqueta }}</span>
                    </span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                </button>

                <div class="itsva-menu-panel" x-show="abierto" x-cloak x-transition.opacity>
                    <div class="itsva-menu-cabecera">
                        <p class="itsva-menu-nombre">{{ $nombre }}</p>
                        <p class="itsva-menu-correo">{{ $usuario->email }}</p>
                    </div>

                    @if ($perfil)
                        <a class="itsva-menu-opcion" href="{{ $perfil::getUrl() }}">Mi perfil</a>
                    @endif

                    <form method="POST" action="{{ filament()->getLogoutUrl() }}">
                        @csrf
                        <button type="submit" class="itsva-menu-opcion itsva-menu-salir">Cerrar sesión</button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</header>
