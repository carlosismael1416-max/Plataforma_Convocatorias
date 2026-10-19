<x-filament-panels::page>
    @php
        $svg = fn (string $nombre, int $tam = 18): string => \App\Filament\Apariencia\Iconos::svg($nombre, $tam);
        $estadoPropuesta = [
            'BORRADOR' => 'borrador', 'GENERADA' => 'revisando', 'EDITANDO' => 'revisando',
            'LISTA' => 'aprobada', 'ENVIADA' => 'enviada', 'APROBADA' => 'aprobada', 'RECHAZADA' => 'rechazada',
        ];
    @endphp

    @include('filament.itsva.dashboard-estilos')

    <div class="dd dd-docente">
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
                    <a class="dd-hero-btn dd-hero-btn-primario" href="{{ route('filament.' . filament()->getId() . '.pages.buscar-convocatorias') }}">
                        {!! $svg('buscar', 15) !!} Buscar convocatoria
                    </a>
                    <a class="dd-hero-btn" href="{{ route('filament.' . filament()->getId() . '.resources.convocatorias.create') }}">
                        {!! $svg('subir', 15) !!} Subir convocatoria
                    </a>
                    <a class="dd-hero-btn" href="{{ route('filament.' . filament()->getId() . '.pages.propuestas') }}">
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
                    <a class="dd-enlace" href="{{ route('filament.' . filament()->getId() . '.pages.buscar-convocatorias') }}">Ver todas →</a>
                </div>

                @if ($recomendadas === [])
                    <div class="dd-card dd-vacio">
                        No hay convocatorias abiertas por ahora. El sistema revisa las fuentes oficiales cada 6 horas.
                    </div>
                @else
                    <div class="dd-tarjetas">
                        @foreach ($recomendadas as $c)
                            <article class="dd-card dd-conv" wire:key="conv-{{ $c['id'] }}" data-href="{{ route('filament.' . filament()->getId() . '.pages.detalle-convocatoria', ['record' => $c['id']]) }}">
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
                                    <a class="dd-btn dd-btn-primario" href="{{ route('filament.' . filament()->getId() . '.pages.detalle-convocatoria', ['record' => $c['id']]) }}">Ver detalle</a>
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
                        <a class="dd-enlace" href="{{ route('filament.' . filament()->getId() . '.pages.calendario') }}">Calendario →</a>
                    </div>

                    <div class="dd-card dd-lista">
                        @forelse ($vencimientos as $v)
                            <a class="dd-venc" href="{{ route('filament.' . filament()->getId() . '.pages.detalle-convocatoria', ['record' => $v['id']]) }}">
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
                        <a class="dd-enlace" href="{{ route('filament.' . filament()->getId() . '.pages.propuestas') }}">Ver todas →</a>
                    </div>

                    <div class="dd-card dd-lista">
                        @forelse ($propuestasRecientes as $p)
                            <a class="dd-prop" href="{{ route('filament.' . filament()->getId() . '.pages.editor-propuesta', ['propuesta' => $p['id']]) }}">
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
                                <a href="{{ route('filament.' . filament()->getId() . '.pages.buscar-convocatorias') }}">Busca una convocatoria</a>
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
