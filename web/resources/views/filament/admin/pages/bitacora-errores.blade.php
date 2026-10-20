<x-filament-panels::page>
@php
    $colores = [
        'red' => ['#FEE2E2', '#B91C1C'], 'yellow' => ['#FEF3C7', '#B45309'],
        'green' => ['#D1FAE5', '#047857'], 'blue' => ['#DBEAFE', '#1D4ED8'],
    ];
    $origenes = ['motor' => ['Motor', '#EDE9FE', '#6D28D9'], 'web' => ['Web', '#DBEAFE', '#1D4ED8'], 'api' => ['API', '#CFFAFE', '#0E7490']];
    $hayFiltros = trim($buscar) !== '' || $tipo !== '' || $fuente !== '' || $estado !== '' || $ejecucion !== null;
@endphp

<style>
    .bit { color: #0F1827; }
    .bit-cab h2 { margin: 0; font: 800 22px 'DM Sans', sans-serif; }
    .bit-cab p { margin: 6px 0 18px; color: #64748B; font-size: 14px; max-width: 860px; line-height: 1.5; }
    .bit-tarjetas { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-bottom: 16px; }
    .bit-tarjeta { display: flex; gap: 12px; align-items: center; width: 100%; padding: 16px; border: 1px solid #DDE3EE; border-radius: 14px; background: #fff; text-align: left; font: inherit; cursor: pointer; transition: box-shadow .15s, transform .15s, border-color .15s; }
    .bit-tarjeta:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(15, 24, 39, .08); }
    .bit-tarjeta.activa { border-color: #059669; box-shadow: 0 0 0 2px rgba(5, 150, 105, .18); }
    .bit-icono { width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .bit-valor { font: 800 24px 'DM Sans', sans-serif; line-height: 1.1; }
    .bit-etq { font-size: 13px; font-weight: 700; }
    .bit-ayuda { color: #94A3B8; font-size: 11px; }

    .bit-filtros { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; margin-bottom: 16px; padding: 12px; border: 1px solid #DDE3EE; border-radius: 14px; background: #fff; }
    .bit-filtros input, .bit-filtros select { min-height: 40px; max-width: 100%; border: 1px solid #DDE3EE; border-radius: 9px; padding: 0 12px; font-size: 13px; background: #F8FAFC; color: #0F1827; }
    .bit-filtros input { flex: 1 1 280px; }
    .bit-limpiar { border: 0; background: transparent; color: #047857; font-size: 13px; font-weight: 700; cursor: pointer; }
    .bit-contexto { display: flex; gap: 10px; align-items: center; margin-bottom: 14px; padding: 10px 14px; border-radius: 12px; background: #EFF6FF; color: #1E3A8A; font-size: 13px; }

    .bit-lista { overflow: hidden; border: 1px solid #DDE3EE; border-radius: 16px; background: #fff; }
    .bit-fila { display: grid; grid-template-columns: 40px minmax(0, 1fr) auto; gap: 14px; align-items: start; padding: 14px 18px; border-top: 1px solid #F1F5F9; }
    .bit-fila:first-child { border-top: 0; }
    .bit-fila-icono { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; background: #F1F5F9; font-size: 17px; }
    .bit-chips { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; margin-bottom: 4px; }
    .bit-chip { padding: 2px 9px; border-radius: 999px; font-size: 11px; font-weight: 700; }
    .bit-mensaje { font-size: 14px; font-weight: 600; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; word-break: break-word; }
    .bit-sub { margin-top: 3px; color: #64748B; font-size: 12px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .bit-fecha { text-align: right; font-size: 12px; font-weight: 600; white-space: nowrap; }
    .bit-fecha span { display: block; color: #94A3B8; font-weight: 400; }
    .bit-vacio { padding: 48px 20px; text-align: center; color: #64748B; font-size: 14px; }
    .bit-vacio strong { display: block; margin-bottom: 4px; color: #0F1827; font-size: 15px; }
    @media (max-width: 1000px) { .bit-tarjetas { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 640px) { .bit-fila { grid-template-columns: 1fr; } .bit-fila-icono { display: none; } .bit-fecha { text-align: left; } }
</style>

<div class="bit">
    <div class="bit-cab">
        <h2>Bitácora de errores</h2>
        <p>Incidencias de todo el sistema: el motor (buscador, fuentes oficiales, publicación, seguimiento y revalidación), su API y la página web. Si un error se repite, se agrupa en la misma fila. Toda la fila abre el detalle, donde se puede marcar como resuelto.</p>
    </div>

    <div class="bit-tarjetas">
        @foreach ($tarjetas as [$clave, $etiqueta, $valor, $ayuda, $icono, $color])
            <button type="button" wire:click="$set('estado', '{{ $clave }}')" @class(['bit-tarjeta', 'activa' => $estado === $clave]) aria-pressed="{{ $estado === $clave ? 'true' : 'false' }}">
                <span class="bit-icono" style="background: {{ $colores[$color][0] }}; color: {{ $colores[$color][1] }}">{!! \App\Filament\Apariencia\Iconos::svg($icono, 18) !!}</span>
                <span>
                    <span class="bit-valor" style="display: block">{{ $valor }}</span>
                    <span class="bit-etq" style="display: block">{{ $etiqueta }}</span>
                    <span class="bit-ayuda">{{ $ayuda }}</span>
                </span>
            </button>
        @endforeach
    </div>

    <div class="bit-filtros">
        <input type="search" wire:model.live.debounce.400ms="buscar" placeholder="Buscar en mensajes, enlaces o detalles…" aria-label="Buscar en la bitácora">
        <select wire:model.live="tipo" aria-label="Origen">
            <option value="">Todos los orígenes</option>
            @foreach ($tiposUsados as $t)
                @php [$texto, $icono] = \App\Filament\Admin\Pages\BitacoraErrores::infoTipo($t); @endphp
                <option value="{{ $t }}">{{ $icono }} {{ $texto }}</option>
            @endforeach
        </select>
        <select wire:model.live="fuente" aria-label="Fuente">
            <option value="">Todas las fuentes</option>
            @foreach ($fuentes as $f)
                <option value="{{ $f->id }}">{{ $f->nombre }}</option>
            @endforeach
        </select>
        <select wire:model.live="estado" aria-label="Estado">
            <option value="">Cualquier estado</option>
            <option value="pendiente">Pendientes</option>
            <option value="resuelto">Resueltas</option>
            <option value="hoy">De hoy</option>
        </select>
        @if ($hayFiltros)
            <button type="button" class="bit-limpiar" wire:click="limpiarFiltros">Limpiar filtros</button>
        @endif
    </div>

    @if ($ejecucionContexto)
        <div class="bit-contexto">
            Mostrando solo las incidencias de la ejecución #{{ $ejecucionContexto->id }}.
            <button type="button" class="bit-limpiar" wire:click="quitarFiltroEjecucion">Ver todas</button>
        </div>
    @endif

    <div class="bit-lista">
        @forelse ($errores as $error)
            @php
                [$textoTipo, $iconoTipo, $origen] = \App\Filament\Admin\Pages\BitacoraErrores::infoTipo($error->tipo_error);
                [$textoOrigen, $fondoOrigen, $colorOrigen] = $origenes[$origen];
                $repeticiones = substr_count((string) $error->detalle, 'Se repitió');
                $url = \App\Filament\Admin\Pages\DetalleError::getUrl(['record' => $error->id]);
            @endphp
            <div class="bit-fila" data-href="{{ $url }}" tabindex="0" wire:key="error-{{ $error->id }}">
                <span class="bit-fila-icono" aria-hidden="true">{{ $iconoTipo }}</span>
                <div style="min-width: 0">
                    <div class="bit-chips">
                        <span class="bit-chip" style="background: {{ $fondoOrigen }}; color: {{ $colorOrigen }}">{{ $textoOrigen }} · {{ $textoTipo }}</span>
                        @if ($error->resuelto)
                            <span class="bit-chip" style="background: #D1FAE5; color: #047857">Resuelta</span>
                        @else
                            <span class="bit-chip" style="background: #FEE2E2; color: #B91C1C">Pendiente</span>
                        @endif
                        @if ($repeticiones > 0)
                            <span class="bit-chip" style="background: #FEF3C7; color: #B45309">Se repitió {{ $repeticiones }} {{ $repeticiones === 1 ? 'vez' : 'veces' }}</span>
                        @endif
                    </div>
                    <div class="bit-mensaje" title="{{ $error->mensaje }}">{{ $error->mensaje }}</div>
                    @if ($error->fuente || $error->url)
                        <div class="bit-sub" title="{{ $error->url }}">
                            {{ $error->fuente?->nombre }}{{ $error->fuente && $error->url ? ' · ' : '' }}{{ $error->url }}
                        </div>
                    @endif
                </div>
                <div class="bit-fecha" title="{{ $error->updated_at?->format('d/m/Y H:i') }}">
                    {{ ($error->updated_at ?? $error->created_at)?->locale('es')->diffForHumans() }}
                    <span>Desde {{ $error->created_at?->format('d/m/Y') }}</span>
                </div>
            </div>
        @empty
            <div class="bit-vacio">
                @if ($hayFiltros)
                    <strong>Sin incidencias con esos filtros</strong>
                    <button type="button" class="bit-limpiar" wire:click="limpiarFiltros">Limpiar filtros</button>
                @else
                    <strong>Todo en orden</strong>
                    No hay incidencias registradas. Cuando el motor, su API o la web tengan un error, aparecerá aquí.
                @endif
            </div>
        @endforelse
    </div>

    <div style="margin-top: 16px">
        {{ $errores->links('pagination.itsva') }}
    </div>
</div>
</x-filament-panels::page>
