<x-filament-panels::page>
    <style>
        .ppr { display: flex; flex-direction: column; gap: 24px; color: #1A2C4E; }
        .ppr-cabecera h2 { margin: 0; font: 700 22px 'DM Sans', sans-serif; }
        .ppr-cabecera p { margin: 4px 0 0; color: #64748B; font-size: 14px; }
        .ppr-card { overflow: hidden; border: 1px solid #DDE3EE; border-radius: 12px; background: #fff; }
        .ppr-card-titulo { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-bottom: 1px solid #F0F4FB; }
        .ppr-card-titulo h3 { margin: 0; font: 700 15px 'DM Sans', sans-serif; }
        .ppr-contador { padding: 2px 10px; border-radius: 999px; background: #EDE9FE; color: #7C3AED; font: 700 12px 'DM Sans', sans-serif; }
        .ppr-tabla { width: 100%; border-collapse: collapse; font-size: 13px; }
        .ppr-tabla th { padding: 10px 20px; background: #F8FAFC; color: #64748B; font: 600 11px 'DM Sans', sans-serif; letter-spacing: .05em; text-align: left; text-transform: uppercase; }
        .ppr-tabla td { padding: 14px 20px; border-top: 1px solid #F0F4FB; vertical-align: middle; }
        .ppr-titulo { font-weight: 600; }
        .ppr-sub { margin-top: 2px; color: #94A3B8; font-size: 12px; }
        .ppr-chip { display: inline-block; padding: 2px 8px; border-radius: 999px; font: 600 11px 'DM Sans', sans-serif; }
        .ppr-docente { background: #DBEAFE; color: #1D4ED8; }
        .ppr-directivo { background: #EDE9FE; color: #7C3AED; }
        .ppr-propia { background: #FEF3C7; color: #B45309; }
        .ppr-aprobada { background: #D1FAE5; color: #047857; }
        .ppr-rechazada { background: #FEE2E2; color: #B91C1C; }
        .ppr-btn { display: inline-flex; padding: 7px 14px; border-radius: 8px; background: #1A4B8C; color: #fff; font: 600 13px 'DM Sans', sans-serif; text-decoration: none; white-space: nowrap; }
        .ppr-btn:hover { background: #163F78; }
        .ppr-btn-sec { background: #E8EEF8; color: #1A4B8C; }
        .ppr-btn-sec:hover { background: #DCE5F3; }
        .ppr-vacio { padding: 40px 20px; color: #64748B; font-size: 14px; text-align: center; }
        .ppr-pestanas { display: flex; gap: 6px; width: fit-content; max-width: 100%; padding: 5px; background: #EEF2F7; border-radius: 12px; overflow-x: auto; }
        .ppr-pestana { display: inline-flex; align-items: center; gap: 8px; padding: 9px 16px; border: 0; border-radius: 9px; background: transparent; color: #475569; font: 700 13px 'DM Sans', sans-serif; cursor: pointer; white-space: nowrap; }
        .ppr-pestana.activa { background: #fff; color: #6D28D9; box-shadow: 0 1px 3px rgba(15, 23, 42, .12); }
        .ppr-resumen { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
        .ppr-resumen-item { display: flex; flex-direction: column; gap: 2px; padding: 14px 16px; border: 1px solid #DDE3EE; border-left-width: 4px; border-radius: 12px; background: #fff; text-align: left; font: inherit; cursor: pointer; }
        .ppr-resumen-item strong { font: 800 22px 'DM Sans', sans-serif; color: #1A2C4E; }
        .ppr-resumen-item span { color: #64748B; font-size: 12px; }
        .ppr-resumen-item.activa { box-shadow: 0 0 0 2px rgba(109, 40, 217, .2); }
        .ppr-resumen-item.ppr-mov-enviada { border-left-color: #2563EB; }
        .ppr-resumen-item.ppr-mov-aprobada { border-left-color: #059669; }
        .ppr-resumen-item.ppr-mov-devuelta { border-left-color: #D97706; }
        .ppr-resumen-item.ppr-mov-rechazada { border-left-color: #DC2626; }
        .ppr-filtros { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; padding: 12px; border: 1px solid #DDE3EE; border-radius: 12px; background: #fff; }
        .ppr-filtros input, .ppr-filtros select { min-height: 40px; border: 1px solid #DDE3EE; border-radius: 9px; padding: 0 12px; font-size: 13px; background: #F8FAFC; color: #1A2C4E; max-width: 100%; }
        .ppr-filtros input { flex: 1 1 260px; }
        .ppr-limpiar { border: 0; background: transparent; color: #6D28D9; font-weight: 700; font-size: 13px; cursor: pointer; }
        .ppr-mov { display: flex; gap: 14px; align-items: flex-start; padding: 14px 20px; border-top: 1px solid #F0F4FB; }
        .ppr-mov:hover { background: #FAFAFF; }
        .ppr-mov-punto { width: 10px; height: 10px; margin-top: 6px; border-radius: 50%; flex-shrink: 0; }
        .ppr-mov-cuerpo { flex: 1; min-width: 0; }
        .ppr-mov-linea { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
        .ppr-mov-obs { margin-top: 6px; padding: 8px 10px; border-radius: 8px; background: #F8FAFC; color: #475569; font-size: 12px; font-style: italic; }
        .ppr-mov-fecha { flex-shrink: 0; text-align: right; color: #1A2C4E; font-size: 12px; font-weight: 600; }
        .ppr-mov-fecha span { display: block; color: #94A3B8; font-weight: 400; }
        .ppr-mov-punto.ppr-mov-enviada, .ppr-chip.ppr-mov-enviada { background: #DBEAFE; color: #1D4ED8; }
        .ppr-mov-punto.ppr-mov-enviada { background: #2563EB; }
        .ppr-mov-punto.ppr-mov-aprobada { background: #059669; }
        .ppr-chip.ppr-mov-aprobada { background: #D1FAE5; color: #047857; }
        .ppr-mov-punto.ppr-mov-devuelta { background: #D97706; }
        .ppr-chip.ppr-mov-devuelta { background: #FEF3C7; color: #B45309; }
        .ppr-mov-punto.ppr-mov-rechazada { background: #DC2626; }
        .ppr-chip.ppr-mov-rechazada { background: #FEE2E2; color: #B91C1C; }
        @media (max-width: 900px) { .ppr-ocultar { display: none; } .ppr-resumen { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    </style>

    <div class="ppr">
        <div class="ppr-cabecera">
            <h2>Propuestas por revisar</h2>
            <p>Propuestas enviadas por docentes y directivos, incluidas las tuyas. Aprueba, devuelve con observaciones o rechaza.</p>
        </div>

        <div class="ppr-pestanas" role="tablist">
            <button type="button" role="tab" aria-selected="{{ $seccion === 'pendientes' ? 'true' : 'false' }}"
                @class(['ppr-pestana', 'activa' => $seccion === 'pendientes']) wire:click="cambiarSeccion('pendientes')">
                📥 Por revisar <span class="ppr-contador">{{ count($pendientes) }}</span>
            </button>
            <button type="button" role="tab" aria-selected="{{ $seccion === 'historial' ? 'true' : 'false' }}"
                @class(['ppr-pestana', 'activa' => $seccion === 'historial']) wire:click="cambiarSeccion('historial')">
                🕓 Historial de movimientos <span class="ppr-contador">{{ $conteoAcciones->sum() }}</span>
            </button>
        </div>

        @if ($seccion === 'pendientes')

        <section class="ppr-card">
            <div class="ppr-card-titulo">
                <h3>Pendientes</h3>
                <span class="ppr-contador">{{ count($pendientes) }}</span>
            </div>

            @if ($pendientes === [])
                <div class="ppr-vacio">No hay propuestas pendientes de revisión.</div>
            @else
                <table class="ppr-tabla">
                    <thead>
                        <tr>
                            <th>Propuesta</th>
                            <th>Autor</th>
                            <th class="ppr-ocultar">Enviada</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pendientes as $p)
                            <tr data-href="{{ $p['url'] }}" tabindex="0">
                                <td>
                                    <div class="ppr-titulo">{{ $p['titulo'] }}</div>
                                    <div class="ppr-sub">{{ $p['convocatoria'] }}</div>
                                </td>
                                <td>
                                    {{ $p['autor'] }}
                                    <div style="margin-top: 4px;">
                                        <span class="ppr-chip {{ $p['rol'] === 'Directivo' ? 'ppr-directivo' : 'ppr-docente' }}">{{ $p['rol'] }}</span>
                                        @if ($p['propia'])
                                            <span class="ppr-chip ppr-propia">Tuya</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="ppr-ocultar">{{ $p['fecha'] }}</td>
                                <td style="text-align: right;">
                                    <a class="ppr-btn" href="{{ $p['url'] }}">Revisar</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>

        <section class="ppr-card">
            <div class="ppr-card-titulo">
                <h3>Dictámenes recientes</h3>
            </div>

            @if ($dictaminadas === [])
                <div class="ppr-vacio">Todavía no hay propuestas dictaminadas.</div>
            @else
                <table class="ppr-tabla">
                    <thead>
                        <tr>
                            <th>Propuesta</th>
                            <th>Autor</th>
                            <th>Dictamen</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dictaminadas as $p)
                            <tr data-href="{{ $p['url'] }}" tabindex="0">
                                <td>
                                    <div class="ppr-titulo">{{ $p['titulo'] }}</div>
                                    <div class="ppr-sub">{{ $p['convocatoria'] }}</div>
                                </td>
                                <td>{{ $p['autor'] }}</td>
                                <td>
                                    <span class="ppr-chip {{ $p['estado'] === 'APROBADA' ? 'ppr-aprobada' : 'ppr-rechazada' }}">
                                        {{ $p['estado'] === 'APROBADA' ? 'Aprobada' : 'Rechazada' }}
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <a class="ppr-btn ppr-btn-sec" href="{{ $p['url'] }}">Ver</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>
        @else
            <div class="ppr-resumen">
                @foreach (\App\Filament\Directivo\Pages\PropuestasPorRevisar::ACCIONES as $clave => [$texto, $clase])
                    <button type="button" wire:click="$set('accion', '{{ $accion === $clave ? '' : $clave }}')"
                        @class(['ppr-resumen-item', 'ppr-mov-' . $clase, 'activa' => $accion === $clave])>
                        <strong>{{ $conteoAcciones[$clave] ?? 0 }}</strong>
                        <span>{{ $texto }}</span>
                    </button>
                @endforeach
            </div>

            <div class="ppr-filtros">
                <input type="search" wire:model.live.debounce.400ms="buscar" placeholder="Buscar por propuesta, autor u observaciones…" aria-label="Buscar en el historial">
                <select wire:model.live="convocatoria" aria-label="Convocatoria">
                    <option value="">Todas las convocatorias</option>
                    @foreach ($convocatoriasConMovimientos as $c)
                        <option value="{{ $c->id }}">{{ \Illuminate\Support\Str::limit($c->titulo, 70) }}</option>
                    @endforeach
                </select>
                <select wire:model.live="accion" aria-label="Movimiento">
                    <option value="">Todos los movimientos</option>
                    @foreach (\App\Filament\Directivo\Pages\PropuestasPorRevisar::ACCIONES as $clave => [$texto])
                        <option value="{{ $clave }}">{{ $texto }}</option>
                    @endforeach
                </select>
                @if ($convocatoria !== '' || $accion !== '' || trim($buscar) !== '')
                    <button type="button" class="ppr-limpiar" wire:click="limpiarFiltros">Limpiar filtros</button>
                @endif
            </div>

            <section class="ppr-card">
                <div class="ppr-card-titulo">
                    <h3>Movimientos</h3>
                    <span class="ppr-sub">Cada envío y dictamen queda registrado con quién lo hizo y cuándo.</span>
                </div>

                @forelse ($movimientos as $m)
                    @php
                        [$textoAccion, $claseAccion] = \App\Filament\Directivo\Pages\PropuestasPorRevisar::ACCIONES[$m->accion] ?? [$m->accion, 'enviada'];
                        $url = \App\Filament\Directivo\Pages\RevisarPropuesta::getUrl(['propuesta' => $m->propuesta_id]);
                    @endphp
                    <div class="ppr-mov" data-href="{{ $url }}" tabindex="0">
                        <span class="ppr-mov-punto ppr-mov-{{ $claseAccion }}" aria-hidden="true"></span>
                        <div class="ppr-mov-cuerpo">
                            <div class="ppr-mov-linea">
                                <span class="ppr-chip ppr-mov-{{ $claseAccion }}">{{ $textoAccion }}</span>
                                <span class="ppr-titulo">{{ $m->propuesta->titulo }}</span>
                            </div>
                            <div class="ppr-sub">
                                {{ \Illuminate\Support\Str::limit($m->propuesta->convocatoria?->titulo ?? 'Sin convocatoria', 90) }}
                            </div>
                            <div class="ppr-sub">
                                Autor: {{ $nombre($m->propuesta->usuario) }}
                                · {{ $m->accion === 'ENVIAR' ? 'Envió' : 'Dictaminó' }}: {{ $nombre($m->usuario) }}
                            </div>
                            @if (filled($m->observaciones))
                                <div class="ppr-mov-obs">“{{ \Illuminate\Support\Str::limit($m->observaciones, 220) }}”</div>
                            @endif
                        </div>
                        <div class="ppr-mov-fecha" title="{{ $m->created_at?->format('d/m/Y H:i') }}">
                            {{ $m->created_at?->locale('es')->isoFormat('D MMM YYYY') }}
                            <span>{{ $m->created_at?->locale('es')->diffForHumans() }}</span>
                        </div>
                    </div>
                @empty
                    <div class="ppr-vacio">No hay movimientos con esos filtros.</div>
                @endforelse

                @if ($movimientos->hasPages())
                    <div style="padding: 12px 20px">{{ $movimientos->links('pagination.itsva') }}</div>
                @endif
            </section>
        @endif
    </div>
</x-filament-panels::page>
