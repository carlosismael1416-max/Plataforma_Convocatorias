<x-filament-panels::page>
    @php
        $iconos = [
            'libro' => '<path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/>',
            'marcador' => '<path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2Z"/>',
            'documento' => '<path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/>',
            'reloj' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
            'buscar' => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
            'subir' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/>',
            'tendencia' => '<polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/>',
        ];
        $svg = fn (string $nombre, int $tam = 18): string => '<svg width="' . $tam . '" height="' . $tam . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $iconos[$nombre] . '</svg>';
        $estadoPropuesta = [
            'BORRADOR' => 'borrador', 'GENERADA' => 'revisando', 'EDITANDO' => 'revisando',
            'LISTA' => 'aprobada', 'ENVIADA' => 'enviada', 'APROBADA' => 'aprobada', 'RECHAZADA' => 'rechazada',
        ];
    @endphp

    <style>
        .dd { color: #1A2C4E; font-family: 'Inter', sans-serif; }
        .dd h1, .dd h2, .dd h3, .dd .display { font-family: 'DM Sans', sans-serif; }

        .dd-hero {
            position: relative;
            overflow: hidden;
            margin-bottom: 32px;
            padding: 28px 32px;
            border-radius: 16px;
            background: linear-gradient(135deg, #0A1E42 0%, #0F2A57 45%, #1A4B8C 100%);
        }

        .dd-hero::before {
            content: "";
            position: absolute;
            inset: 0;
            opacity: .1;
            background-image: radial-gradient(circle, #fff 1px, transparent 0);
            background-size: 28px 28px;
        }

        .dd-hero-deco {
            position: absolute;
            top: 50%;
            right: 32px;
            transform: translateY(-50%);
            color: #fff;
            opacity: .08;
            pointer-events: none;
        }

        .dd-hero > div { position: relative; }

        .dd-hero-pre {
            margin: 0 0 8px;
            color: #60A5FA;
            font: 600 12px 'DM Sans', sans-serif;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .dd-hero h1 {
            margin: 0 0 4px;
            color: #fff;
            font-size: 30px;
            font-weight: 700;
        }

        .dd-hero-sub {
            margin: 0 0 24px;
            color: #93B4D8;
            font-size: 14px;
        }

        .dd-hero-acciones { display: flex; flex-wrap: wrap; gap: 12px; }

        .dd-hero-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border: 1px solid rgba(255, 255, 255, .2);
            border-radius: 12px;
            background: rgba(255, 255, 255, .1);
            color: #fff;
            font: 600 14px 'DM Sans', sans-serif;
            text-decoration: none;
        }

        .dd-hero-btn:hover { background: rgba(255, 255, 255, .2); }

        .dd-hero-btn-primario { border-color: #2563EB; background: #2563EB; }
        .dd-hero-btn-primario:hover { background: #1D4ED8; }

        .dd-stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 32px;
        }

        @media (min-width: 1280px) {
            .dd-stats { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        }

        .dd-card {
            border: 1px solid #DDE3EE;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
        }

        .dd-stat {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            padding: 20px;
            color: inherit;
            text-decoration: none;
            transition: border-color .15s, box-shadow .15s, transform .15s;
        }

        .dd-stat:hover { border-color: #1A4B8C; box-shadow: 0 4px 12px rgba(26, 75, 140, .12); transform: translateY(-1px); }
        .dd-stat:focus-visible { outline: 2px solid #2563EB; outline-offset: 2px; }

        .dd-stat-icono {
            display: flex;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border-radius: 12px;
        }

        .dd-azul { background: #E8EEF8; color: #1A4B8C; }
        .dd-morado { background: #EDE9FE; color: #7C3AED; }
        .dd-verde { background: #D1FAE5; color: #059669; }
        .dd-ambar { background: #FEF3C7; color: #D97706; }

        .dd-stat-etiqueta { margin: 0 0 4px; color: #64748B; font: 400 14px 'DM Sans', sans-serif; }
        .dd-stat-valor { margin: 0; font: 700 24px 'DM Sans', sans-serif; }
        .dd-stat-sub { margin: 2px 0 0; color: #94A3B8; font-size: 12px; }

        .dd-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 24px; }

        @media (min-width: 1280px) {
            .dd-grid { grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); }
        }

        .dd-columna { display: flex; flex-direction: column; gap: 24px; }

        .dd-seccion {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .dd-seccion h2 { margin: 0; font-size: 18px; font-weight: 700; }
        .dd-seccion p { margin: 2px 0 0; color: #64748B; font-size: 14px; }

        .dd-enlace {
            color: #2563EB;
            font: 600 14px 'DM Sans', sans-serif;
            text-decoration: none;
            white-space: nowrap;
        }

        .dd-tarjetas { display: grid; grid-template-columns: minmax(0, 1fr); gap: 16px; }

        @media (min-width: 640px) {
            .dd-tarjetas { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        .dd-conv { padding: 20px; transition: box-shadow .15s; }
        .dd-conv:hover { box-shadow: 0 10px 15px -3px rgba(15, 23, 42, .08); }

        .dd-conv-cabecera { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 12px; }
        .dd-conv-etiquetas { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-bottom: 4px; }

        .dd-badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 6px;
            font: 600 10px 'DM Sans', sans-serif;
            letter-spacing: .05em;
        }

        .dd-badge-publicada { background: #DBEAFE; color: #1A4B8C; }
        .dd-badge-porcerrar { background: #FEF3C7; color: #D97706; }
        .dd-badge-borrador { background: #F1F5F9; color: #64748B; }
        .dd-badge-revisando { background: #DBEAFE; color: #2563EB; }
        .dd-badge-aprobada { background: #D1FAE5; color: #059669; }
        .dd-badge-enviada { background: #E0E7FF; color: #4338CA; }
        .dd-badge-rechazada { background: #FEE2E2; color: #DC2626; }

        .dd-categoria {
            padding: 2px 8px;
            border-radius: 999px;
            background: #F0F4FB;
            color: #64748B;
            font: 400 12px 'DM Sans', sans-serif;
        }

        .dd-conv h3 {
            display: -webkit-box;
            overflow: hidden;
            margin: 0;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.35;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .dd-conv-organismo { margin: 4px 0 0; color: #64748B; font-size: 12px; }

        .dd-fav {
            flex-shrink: 0;
            padding: 0;
            border: 0;
            background: none;
            color: #CBD5E1;
            cursor: pointer;
        }

        .dd-fav-activo { color: #E11D48; }

        .dd-datos { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 16px; }
        .dd-dato { padding: 8px; border-radius: 8px; background: #F8FAFC; }
        .dd-dato-alerta { background: #FEF3C7; }
        .dd-dato-titulo { margin: 0; color: #94A3B8; font: 400 12px 'DM Sans', sans-serif; }
        .dd-dato-valor { margin: 0; font: 700 14px 'DM Sans', sans-serif; }
        .dd-dato-monto { color: #1A4B8C; }
        .dd-dato-alerta .dd-dato-valor { color: #D97706; }

        .dd-botones { display: flex; gap: 8px; }

        .dd-btn {
            display: inline-flex;
            flex: 1;
            align-items: center;
            justify-content: center;
            padding: 5px 12px;
            border: 0;
            border-radius: 8px;
            font: 600 12px 'DM Sans', sans-serif;
            text-decoration: none;
            cursor: pointer;
        }

        .dd-btn:hover { opacity: .85; }
        .dd-btn-primario { background: #1A4B8C; color: #fff; }
        .dd-btn-secundario { background: #E8EEF8; color: #1A4B8C; }
        .dd-btn-secundario[disabled] { cursor: default; opacity: .6; }

        .dd-lista > * + * { border-top: 1px solid #F0F4FB; }

        .dd-venc {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: inherit;
            text-decoration: none;
        }

        .dd-venc:hover, .dd-prop:hover { background: rgba(249, 250, 251, .7); }
        .dd-venc-dias { width: 40px; text-align: center; }
        .dd-venc-num { font: 700 18px/1 'DM Sans', sans-serif; color: #1A4B8C; }
        .dd-venc-num-pronto { color: #D97706; }
        .dd-venc-txt { color: #94A3B8; font-size: 12px; }
        .dd-venc-info { flex: 1; min-width: 0; }

        .dd-venc-titulo {
            overflow: hidden;
            margin: 0;
            font: 600 12px 'DM Sans', sans-serif;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .dd-venc-fecha { margin: 0; color: #94A3B8; font-size: 12px; }
        .dd-punto { flex-shrink: 0; width: 8px; height: 8px; border-radius: 999px; }
        .dd-punto-alta { background: #DC2626; }
        .dd-punto-media { background: #D97706; }
        .dd-punto-baja { background: #059669; }

        .dd-prop { display: block; padding: 16px; color: inherit; text-decoration: none; }
        .dd-prop-cabecera { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; margin-bottom: 8px; }
        .dd-prop-titulo { margin: 0; font: 600 12px/1.35 'DM Sans', sans-serif; }
        .dd-prop-conv { margin: 0 0 8px; color: #94A3B8; font-size: 12px; }
        .dd-progreso { display: flex; align-items: center; gap: 8px; }
        .dd-barra { flex: 1; height: 6px; overflow: hidden; border-radius: 999px; background: #E8EEF8; }
        .dd-barra > div { height: 100%; border-radius: 999px; background: #2563EB; }
        .dd-barra-completa > div { background: #059669; }
        .dd-pct { color: #94A3B8; font: 400 12px 'JetBrains Mono', ui-monospace, monospace; }

        .dd-actividad { padding: 16px; }
        .dd-actividad-titulo { display: flex; align-items: center; gap: 8px; margin-bottom: 12px; color: #059669; }
        .dd-actividad-titulo span { color: #1A2C4E; font: 700 14px 'DM Sans', sans-serif; }
        .dd-actividad-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
        .dd-actividad-item { padding: 8px; border-radius: 8px; background: #F8FAFC; text-align: center; }
        .dd-actividad-valor { font: 700 20px 'DM Sans', sans-serif; }
        .dd-actividad-etiqueta { color: #94A3B8; font-size: 12px; }

        .dd-vacio { padding: 32px 16px; color: #64748B; font-size: 14px; text-align: center; }
        .dd-vacio a { color: #2563EB; font-weight: 600; text-decoration: none; }
    </style>

    <div class="dd">
        {{-- Banner --}}
        <section class="dd-hero">
            <span class="dd-hero-deco">{!! $svg('libro', 120) !!}</span>

            <div>
                <p class="dd-hero-pre">Bienvenido de vuelta</p>
                <h1>{{ $nombre }}</h1>
                <p class="dd-hero-sub">
                    @if ($departamento)
                        {{ $departamento }} —
                    @endif
                    {{ $fecha }}
                </p>

                <div class="dd-hero-acciones">
                    <a class="dd-hero-btn dd-hero-btn-primario" href="{{ route('filament.docente.pages.buscar-convocatorias') }}">
                        {!! $svg('buscar', 15) !!} Buscar convocatoria
                    </a>
                    <a class="dd-hero-btn" href="{{ route('filament.docente.resources.convocatorias.create') }}">
                        {!! $svg('subir', 15) !!} Subir convocatoria
                    </a>
                    <a class="dd-hero-btn" href="{{ route('filament.docente.pages.propuestas') }}">
                        {!! $svg('documento', 15) !!} Mis propuestas
                    </a>
                </div>
            </div>
        </section>

        {{-- Estadísticas --}}
        <section class="dd-stats">
            @foreach ($estadisticas as $estadistica)
                <a href="{{ $estadistica['url'] }}" class="dd-card dd-stat">
                    <div class="dd-stat-icono dd-{{ $estadistica['color'] }}">{!! $svg($estadistica['icono'], 20) !!}</div>
                    <div>
                        <p class="dd-stat-etiqueta">{{ $estadistica['etiqueta'] }}</p>
                        <p class="dd-stat-valor">{{ $estadistica['valor'] }}</p>
                        <p class="dd-stat-sub">{{ $estadistica['sub'] }}</p>
                    </div>
                </a>
            @endforeach
        </section>

        <div class="dd-grid">
            {{-- Convocatorias recomendadas --}}
            <section>
                <div class="dd-seccion">
                    <div>
                        <h2>Convocatorias recomendadas</h2>
                        <p>Publicadas y vigentes, ordenadas por fecha de cierre</p>
                    </div>
                    <a class="dd-enlace" href="{{ route('filament.docente.pages.buscar-convocatorias') }}">Ver todas →</a>
                </div>

                @if ($recomendadas === [])
                    <div class="dd-card dd-vacio">
                        No hay convocatorias abiertas por ahora. El sistema revisa las fuentes oficiales cada 6 horas.
                    </div>
                @else
                    <div class="dd-tarjetas">
                        @foreach ($recomendadas as $c)
                            <article class="dd-card dd-conv" wire:key="conv-{{ $c['id'] }}">
                                <div class="dd-conv-cabecera">
                                    <div style="min-width: 0">
                                        <div class="dd-conv-etiquetas">
                                            @if ($c['porCerrar'])
                                                <span class="dd-badge dd-badge-porcerrar">POR CERRAR</span>
                                            @else
                                                <span class="dd-badge dd-badge-publicada">PUBLICADA</span>
                                            @endif
                                            @if ($c['categoria'])
                                                <span class="dd-categoria">{{ $c['categoria'] }}</span>
                                            @endif
                                        </div>
                                        <h3>{{ $c['titulo'] }}</h3>
                                        <p class="dd-conv-organismo">{{ $c['organismo'] }}</p>
                                    </div>

                                    <button
                                        type="button"
                                        wire:click="alternarFavorita({{ $c['id'] }})"
                                        @class(['dd-fav', 'dd-fav-activo' => $c['favorita']])
                                        aria-label="{{ $c['favorita'] ? 'Quitar de favoritas' : 'Marcar como favorita' }}"
                                        aria-pressed="{{ $c['favorita'] ? 'true' : 'false' }}"
                                    >
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="{{ $c['favorita'] ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                                    </button>
                                </div>

                                <div class="dd-datos">
                                    <div class="dd-dato">
                                        <p class="dd-dato-titulo">Monto</p>
                                        <p class="dd-dato-valor dd-dato-monto">{{ $c['monto'] }}</p>
                                    </div>
                                    <div @class(['dd-dato', 'dd-dato-alerta' => $c['porCerrar']])>
                                        <p class="dd-dato-titulo">Cierre</p>
                                        <p class="dd-dato-valor">
                                            @if ($c['dias'] !== null)
                                                {{ $c['dias'] }}d · {{ $c['cierre'] }}
                                            @else
                                                Sin fecha
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <div class="dd-botones">
                                    <a class="dd-btn dd-btn-primario" href="{{ route('filament.docente.pages.detalle-convocatoria', ['record' => $c['id']]) }}">Ver detalle</a>
                                    @if ($c['guardada'])
                                        <button type="button" class="dd-btn dd-btn-secundario" disabled>Guardada</button>
                                    @else
                                        <button type="button" class="dd-btn dd-btn-secundario" wire:click="guardar({{ $c['id'] }})">Guardar</button>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            <div class="dd-columna">
                {{-- Próximos vencimientos --}}
                <section>
                    <div class="dd-seccion">
                        <h2>Próximos vencimientos</h2>
                        <a class="dd-enlace" href="{{ route('filament.docente.pages.calendario') }}">Calendario →</a>
                    </div>

                    <div class="dd-card dd-lista">
                        @forelse ($vencimientos as $v)
                            <a class="dd-venc" href="{{ route('filament.docente.pages.detalle-convocatoria', ['record' => $v['id']]) }}">
                                <div class="dd-venc-dias">
                                    <div @class(['dd-venc-num', 'dd-venc-num-pronto' => $v['dias'] <= 14])>{{ $v['dias'] }}</div>
                                    <div class="dd-venc-txt">días</div>
                                </div>
                                <div class="dd-venc-info">
                                    <p class="dd-venc-titulo">{{ $v['titulo'] }}</p>
                                    <p class="dd-venc-fecha">{{ $v['cierre'] }}</p>
                                </div>
                                <span class="dd-punto dd-punto-{{ $v['prioridad'] }}" title="Prioridad {{ $v['prioridad'] }}"></span>
                            </a>
                        @empty
                            <div class="dd-vacio">Sin cierres próximos.</div>
                        @endforelse
                    </div>
                </section>

                {{-- Propuestas recientes --}}
                <section>
                    <div class="dd-seccion">
                        <h2>Mis propuestas recientes</h2>
                        <a class="dd-enlace" href="{{ route('filament.docente.pages.propuestas') }}">Ver todas →</a>
                    </div>

                    <div class="dd-card dd-lista">
                        @forelse ($propuestasRecientes as $p)
                            <a class="dd-prop" href="{{ route('filament.docente.pages.editor-propuesta', ['propuesta' => $p['id']]) }}">
                                <div class="dd-prop-cabecera">
                                    <p class="dd-prop-titulo">{{ $p['titulo'] }}</p>
                                    <span class="dd-badge dd-badge-{{ $estadoPropuesta[$p['estado']] ?? 'borrador' }}">{{ $p['estado'] }}</span>
                                </div>
                                @if ($p['convocatoria'])
                                    <p class="dd-prop-conv">{{ \Illuminate\Support\Str::limit($p['convocatoria'], 60) }}</p>
                                @endif
                                <div class="dd-progreso">
                                    <div @class(['dd-barra', 'dd-barra-completa' => $p['avance'] === 100])>
                                        <div style="width: {{ $p['avance'] }}%"></div>
                                    </div>
                                    <span class="dd-pct">{{ $p['avance'] }}%</span>
                                </div>
                            </a>
                        @empty
                            <div class="dd-vacio">
                                Aún no tienes propuestas.
                                <a href="{{ route('filament.docente.pages.buscar-convocatorias') }}">Busca una convocatoria</a>
                                para empezar.
                            </div>
                        @endforelse
                    </div>
                </section>

                {{-- Actividad --}}
                <section class="dd-card dd-actividad">
                    <div class="dd-actividad-titulo">
                        {!! $svg('tendencia', 16) !!}
                        <span>Tu actividad</span>
                    </div>
                    <div class="dd-actividad-grid">
                        @foreach ($actividad as $a)
                            <div class="dd-actividad-item">
                                <div class="dd-actividad-valor" style="color: {{ $a['color'] }}">{{ $a['valor'] }}</div>
                                <div class="dd-actividad-etiqueta">{{ $a['etiqueta'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-filament-panels::page>
