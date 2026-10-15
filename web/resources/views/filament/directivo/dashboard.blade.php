<x-filament-panels::page>
    @php
        $svg = fn (string $nombre, int $tam = 18): string => \App\Filament\Apariencia\Iconos::svg($nombre, $tam);
    @endphp

    @include('filament.itsva.dashboard-estilos')

    <div class="dd dd-directivo">
        {{-- Banner --}}
        <section class="dd-hero">
            <span class="dd-hero-deco">{!! $svg('portapapeles', 120) !!}</span>

            <div>
                <p class="dd-hero-pre">Bienvenido de vuelta</p>
                <h1>{{ $nombre }}</h1>
                <p class="dd-hero-sub">
                    Directivo
                    @if ($departamento)
                        — {{ $departamento }}
                    @endif
                    — {{ $fecha }}
                </p>

                <div class="dd-hero-acciones">
                    <a class="dd-hero-btn dd-hero-btn-primario" href="{{ $urls['porRevisar'] }}">
                        {!! $svg('portapapeles', 15) !!} Revisar convocatorias
                    </a>
                    <a class="dd-hero-btn" href="{{ $urls['propuestas'] }}">
                        {!! $svg('aprobado', 15) !!} Revisar propuestas
                    </a>
                    <a class="dd-hero-btn" href="{{ $urls['subir'] }}">
                        {!! $svg('subir', 15) !!} Subir convocatoria
                    </a>
                    <a class="dd-hero-btn" href="{{ $urls['reportes'] }}">
                        {!! $svg('reporte', 15) !!} Reportes
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
            <div class="dd-columna">
                {{-- Convocatorias pendientes --}}
                <section>
                    <div class="dd-seccion">
                        <div>
                            <h2>Pendientes de revisión</h2>
                            <p>Convocatorias detectadas o subidas que requieren tu aprobación</p>
                        </div>
                        <a class="dd-enlace" href="{{ $urls['porRevisar'] }}">Ver todas →</a>
                    </div>

                    <div class="dd-card">
                        @if ($pendientes === [])
                            <div class="dd-vacio">No hay convocatorias pendientes de revisión.</div>
                        @else
                            <div class="dd-tabla-ctn">
                                <table class="dd-tabla">
                                    <thead>
                                        <tr>
                                            <th>Título</th>
                                            <th>Organismo</th>
                                            <th>Fuente</th>
                                            <th>Encontrada</th>
                                            <th>Cierre</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($pendientes as $p)
                                            <tr>
                                                <td>
                                                    <p class="dd-tabla-titulo">{{ $p['titulo'] }}</p>
                                                    @if ($p['categoria'])
                                                        <span class="dd-categoria">{{ $p['categoria'] }}</span>
                                                    @endif
                                                </td>
                                                <td class="dd-suave">{{ $p['organismo'] }}</td>
                                                <td class="{{ $p['manual'] ? 'dd-manual' : 'dd-auto' }}">{{ $p['manual'] ? 'Manual' : 'Automático' }}</td>
                                                <td class="dd-mono dd-suave">{{ $p['encontrada'] }}</td>
                                                <td class="dd-mono">{{ $p['cierre'] }}</td>
                                                <td>
                                                    <a class="dd-btn dd-btn-chico dd-btn-morado" href="{{ $p['url'] }}">
                                                        {!! $svg('ojo', 13) !!}&nbsp;Revisar
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </section>

                {{-- Propuestas pendientes --}}
                <section>
                    <div class="dd-seccion">
                        <div>
                            <h2>Propuestas por revisar</h2>
                            <p>Enviadas por docentes y directivos, incluidas las tuyas</p>
                        </div>
                        <a class="dd-enlace" href="{{ $urls['propuestas'] }}">Ver todas →</a>
                    </div>

                    <div class="dd-card">
                        @if ($propuestasPendientes === [])
                            <div class="dd-vacio">No hay propuestas pendientes de revisión.</div>
                        @else
                            <div class="dd-tabla-ctn">
                                <table class="dd-tabla">
                                    <thead>
                                        <tr>
                                            <th>Propuesta</th>
                                            <th>Autor</th>
                                            <th>Enviada</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($propuestasPendientes as $p)
                                            <tr>
                                                <td>
                                                    <p class="dd-tabla-titulo">{{ $p['titulo'] }}</p>
                                                    <p class="dd-tabla-sub">{{ \Illuminate\Support\Str::limit($p['convocatoria'], 60) }}</p>
                                                </td>
                                                <td>
                                                    {{ $p['autor'] }}
                                                    @if ($p['propia'])
                                                        <span class="dd-badge dd-badge-porcerrar">TUYA</span>
                                                    @endif
                                                </td>
                                                <td class="dd-mono dd-suave">{{ $p['enviada'] }}</td>
                                                <td>
                                                    <a class="dd-btn dd-btn-chico dd-btn-morado" href="{{ $p['url'] }}">
                                                        {!! $svg('ojo', 13) !!}&nbsp;Revisar
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </section>
            </div>

            <div class="dd-columna">
                {{-- Resumen del mes --}}
                <section class="dd-card dd-panel">
                    <h3>Resumen del mes</h3>
                    <div class="dd-barras">
                        @foreach ($resumen as $r)
                            <div>
                                <div class="dd-barra-fila">
                                    <span>{{ $r['etiqueta'] }}</span>
                                    <strong style="color: {{ $r['color'] }}">{{ $r['valor'] }}</strong>
                                </div>
                                <div class="dd-barra-gruesa">
                                    <div style="width: {{ min(100, round($r['valor'] / $r['max'] * 100)) }}%; background: {{ $r['color'] }}"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                {{-- Próximos cierres --}}
                <section>
                    <div class="dd-seccion">
                        <h2>Próximos cierres</h2>
                        <a class="dd-enlace" href="{{ $urls['calendario'] }}">Calendario →</a>
                    </div>

                    <div class="dd-card dd-lista">
                        @forelse ($proximosCierres as $c)
                            <a class="dd-venc" href="{{ $c['url'] }}">
                                <div class="dd-venc-dias">
                                    <div @class(['dd-venc-num', 'dd-venc-num-pronto' => $c['dias'] <= 14])>{{ $c['dias'] }}</div>
                                    <div class="dd-venc-txt">días</div>
                                </div>
                                <div class="dd-venc-info">
                                    <p class="dd-venc-titulo">{{ $c['titulo'] }}</p>
                                    <p class="dd-venc-fecha">{{ $c['cierre'] }}</p>
                                </div>
                            </a>
                        @empty
                            <div class="dd-vacio">Sin cierres próximos.</div>
                        @endforelse
                    </div>
                </section>

                {{-- Acceso rápido --}}
                <section class="dd-card dd-panel">
                    <h3>Acceso rápido</h3>
                    <div class="dd-accesos">
                        <a class="dd-acceso" style="background: #EDE9FE" href="{{ $urls['estadisticas'] }}">
                            <span style="color: #7C3AED">{!! $svg('grafica', 16) !!}</span> Ver estadísticas
                        </a>
                        <a class="dd-acceso" style="background: #D1FAE5" href="{{ $urls['reportes'] }}">
                            <span style="color: #059669">{!! $svg('reporte', 16) !!}</span> Generar reporte
                        </a>
                        <a class="dd-acceso" style="background: #FEF3C7" href="{{ $urls['buscar'] }}">
                            <span style="color: #D97706">{!! $svg('buscar', 16) !!}</span> Buscar convocatorias
                        </a>
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-filament-panels::page>
