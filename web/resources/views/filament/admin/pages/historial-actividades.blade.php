<x-filament-panels::page>
@php
    $colores = [
        'yellow' => ['#FEF3C7', '#B45309'], 'blue' => ['#DBEAFE', '#1D4ED8'],
        'green' => ['#D1FAE5', '#047857'], 'violet' => ['#EDE9FE', '#6D28D9'],
    ];
    $hayFiltros = trim($buscar) !== '' || $usuario !== '' || $accion !== '' || $periodo !== '';
@endphp

<style>
    .hac { color: #0F1827; }
    .hac-cab h2 { margin: 0; font: 800 22px 'DM Sans', sans-serif; }
    .hac-cab p { margin: 6px 0 18px; color: #64748B; font-size: 14px; max-width: 860px; line-height: 1.5; }
    .hac-tarjetas { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-bottom: 16px; }
    .hac-tarjeta { display: flex; gap: 12px; align-items: center; width: 100%; padding: 16px; border: 1px solid #DDE3EE; border-radius: 14px; background: #fff; text-align: left; font: inherit; cursor: pointer; transition: box-shadow .15s, transform .15s, border-color .15s; }
    .hac-tarjeta:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(15, 24, 39, .08); }
    .hac-tarjeta.activa { border-color: #059669; box-shadow: 0 0 0 2px rgba(5, 150, 105, .18); }
    .hac-icono { width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .hac-valor { display: block; font: 800 24px 'DM Sans', sans-serif; line-height: 1.1; }
    .hac-etq { display: block; font-size: 13px; font-weight: 700; }
    .hac-ayuda { color: #94A3B8; font-size: 11px; }

    .hac-filtros { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; margin-bottom: 16px; padding: 12px; border: 1px solid #DDE3EE; border-radius: 14px; background: #fff; }
    .hac-filtros input, .hac-filtros select { min-height: 40px; max-width: 100%; border: 1px solid #DDE3EE; border-radius: 9px; padding: 0 12px; font-size: 13px; background: #F8FAFC; color: #0F1827; }
    .hac-filtros input { flex: 1 1 260px; }
    .hac-limpiar { border: 0; background: transparent; color: #047857; font-size: 13px; font-weight: 700; cursor: pointer; }

    .hac-lista { overflow: hidden; border: 1px solid #DDE3EE; border-radius: 16px; background: #fff; }
    .hac-fila { display: grid; grid-template-columns: 40px minmax(0, 1fr) auto; gap: 14px; align-items: center; padding: 13px 18px; border-top: 1px solid #F1F5F9; }
    .hac-fila:first-child { border-top: 0; }
    .hac-fila-icono { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; background: #F1F5F9; font-size: 17px; }
    .hac-chips { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; margin-bottom: 3px; }
    .hac-chip { padding: 2px 9px; border-radius: 999px; font-size: 11px; font-weight: 700; background: #ECFDF5; color: #047857; }
    .hac-quien { color: #475569; font-size: 12px; font-weight: 600; }
    .hac-desc { font-size: 14px; font-weight: 600; line-height: 1.4; word-break: break-word; }
    .hac-fecha { text-align: right; font-size: 12px; font-weight: 600; white-space: nowrap; }
    .hac-fecha span { display: block; color: #94A3B8; font-weight: 400; }
    .hac-vacio { padding: 48px 20px; text-align: center; color: #64748B; font-size: 14px; }
    .hac-vacio strong { display: block; margin-bottom: 4px; color: #0F1827; font-size: 15px; }
    @media (max-width: 1000px) { .hac-tarjetas { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 640px) { .hac-fila { grid-template-columns: 1fr; } .hac-fila-icono { display: none; } .hac-fecha { text-align: left; } }
</style>

<div class="hac">
    <div class="hac-cab">
        <h2>Historial de actividades</h2>
        <p>Lo que hace cada usuario en la plataforma: inicios y cierres de sesión, cambios de perfil y contraseña, propuestas y dictámenes, convocatorias, usuarios, roles, fuentes, motor y reportes. Las ediciones seguidas de un mismo registro se agrupan en una sola fila.</p>
    </div>

    <div class="hac-tarjetas">
        @foreach ($tarjetas as [$clave, $etiqueta, $valor, $ayuda, $icono, $color])
            <button type="button" wire:click="$set('periodo', '{{ $clave }}')" @class(['hac-tarjeta', 'activa' => $periodo === $clave]) aria-pressed="{{ $periodo === $clave ? 'true' : 'false' }}">
                <span class="hac-icono" style="background: {{ $colores[$color][0] }}; color: {{ $colores[$color][1] }}">{!! \App\Filament\Apariencia\Iconos::svg($icono, 18) !!}</span>
                <span>
                    <span class="hac-valor">{{ $valor }}</span>
                    <span class="hac-etq">{{ $etiqueta }}</span>
                    <span class="hac-ayuda">{{ $ayuda }}</span>
                </span>
            </button>
        @endforeach
    </div>

    <div class="hac-filtros">
        <input type="search" wire:model.live.debounce.400ms="buscar" placeholder="Buscar por descripción, nombre o correo…" aria-label="Buscar en el historial">
        <select wire:model.live="usuario" aria-label="Usuario">
            <option value="">Todos los usuarios</option>
            @foreach ($usuarios as $u)
                <option value="{{ $u->id }}">{{ trim($u->name . ' ' . $u->apellidos) }}</option>
            @endforeach
        </select>
        <select wire:model.live="accion" aria-label="Acción">
            <option value="">Todas las acciones</option>
            @foreach ($accionesUsadas as $a)
                @php [$texto, $icono] = \App\Models\Actividad::ACCIONES[$a] ?? [$a, '•']; @endphp
                <option value="{{ $a }}">{{ $icono }} {{ $texto }}</option>
            @endforeach
        </select>
        <select wire:model.live="periodo" aria-label="Periodo">
            <option value="">Cualquier fecha</option>
            <option value="hoy">Hoy</option>
            <option value="semana">Últimos 7 días</option>
            <option value="mes">Últimos 30 días</option>
        </select>
        @if ($hayFiltros)
            <button type="button" class="hac-limpiar" wire:click="limpiarFiltros">Limpiar filtros</button>
        @endif
    </div>

    <div class="hac-lista">
        @forelse ($actividades as $actividad)
            @php
                [$etiqueta, $icono] = $actividad->infoAccion();
                $momento = $actividad->updated_at ?? $actividad->created_at;
                $quien = $actividad->usuario ? trim($actividad->usuario->name . ' ' . $actividad->usuario->apellidos) : 'Usuario eliminado';
            @endphp
            <div class="hac-fila" wire:key="actividad-{{ $actividad->id }}">
                <span class="hac-fila-icono" aria-hidden="true">{{ $icono }}</span>
                <div style="min-width: 0">
                    <div class="hac-chips">
                        <span class="hac-chip">{{ $etiqueta }}</span>
                        <span class="hac-quien">{{ $quien }}</span>
                    </div>
                    <div class="hac-desc">{{ $actividad->descripcion }}</div>
                </div>
                <div class="hac-fecha" title="{{ $actividad->ip ? 'IP ' . $actividad->ip : '' }}">
                    {{ $momento?->locale('es')->diffForHumans() }}
                    <span>{{ $momento?->timezone('America/Merida')->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        @empty
            <div class="hac-vacio">
                @if ($hayFiltros)
                    <strong>Sin actividades con esos filtros</strong>
                    <button type="button" class="hac-limpiar" wire:click="limpiarFiltros">Limpiar filtros</button>
                @else
                    <strong>Aún no hay actividades</strong>
                    Cuando los usuarios inicien sesión o hagan cambios, aparecerán aquí.
                @endif
            </div>
        @endforelse
    </div>

    <div style="margin-top: 16px">
        {{ $actividades->links('pagination.itsva') }}
    </div>
</div>
</x-filament-panels::page>
