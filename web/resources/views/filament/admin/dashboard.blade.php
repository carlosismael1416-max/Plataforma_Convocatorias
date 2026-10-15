<x-filament-panels::page>
    @php
        $svg = fn (string $nombre, int $tam = 18): string => \App\Filament\Apariencia\Iconos::svg($nombre, $tam);
        $estadoEjecucion = [
            'COMPLETADO' => ['aprobada', 'EXITOSA'],
            'FALLIDO' => ['rechazada', 'ERROR'],
            'INICIADO' => ['revisando', 'EN CURSO'],
            'EJECUTANDO' => ['revisando', 'EN CURSO'],
        ];
    @endphp

    @include('filament.itsva.dashboard-estilos')

    <div class="dd dd-admin">
        {{-- Banner --}}
        <section class="dd-hero">
            <span class="dd-hero-deco">{!! $svg('cpu', 120) !!}</span>

            <div>
                <p class="dd-hero-pre">Bienvenido de vuelta</p>
                <h1>{{ $nombre }}</h1>
                <p class="dd-hero-sub">Administración del sistema — {{ $fecha }}</p>

                <div class="dd-hero-acciones">
                    <a class="dd-hero-btn dd-hero-btn-primario" href="{{ $urls['motor'] }}">
                        {!! $svg('play', 15) !!} Motor de extracción
                    </a>
                    <a class="dd-hero-btn" href="{{ $urls['usuarios'] }}">
                        {!! $svg('usuarios', 15) !!} Gestionar usuarios
                    </a>
                    <a class="dd-hero-btn" href="{{ $urls['convocatorias'] }}">
                        {!! $svg('libro', 15) !!} Convocatorias
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

        @include('filament.itsva.interruptor-motor')

        {{-- Motor: último ciclo automático --}}
        <section class="dd-card dd-motor">
            <div @class(['dd-motor-cab', 'dd-motor-caido' => ! $apiDisponible || $ciclo === null])>
                <div class="dd-motor-info">
                    <div class="dd-motor-icono">{!! $svg('cpu', 22) !!}</div>
                    <div>
                        <h3>
                            Motor de extracción
                            <span class="dd-pastilla">{{ $apiDisponible ? 'EN LÍNEA' : 'SIN RESPUESTA' }}</span>
                        </h3>
                        <p class="dd-motor-sub">
                            @if ($ciclo)
                                Último ciclo automático:
                                {{ $ciclo['inicio']->locale('es')->isoFormat('D MMM YYYY, HH:mm') }}
                                @if ($ciclo['duracion'])
                                    · {{ $ciclo['duracion'] }} s
                                @endif
                                · {{ ucfirst(mb_strtolower($ciclo['estado'])) }}
                                @if ($ciclo['atrasado'])
                                    · atrasado (el ciclo corre cada 6 h)
                                @endif
                            @else
                                No hay reporte del ciclo automático.
                            @endif
                        </p>
                    </div>
                </div>

                <div class="dd-motor-btns">
                    <a class="dd-hero-btn" href="{{ $urls['motor'] }}">Ver detalles</a>
                </div>
            </div>

            @if ($ciclo)
                <div class="dd-motor-cifras">
                    @foreach ($ciclo['cifras'] as $cifra)
                        <div>
                            <div class="dd-motor-cifra" style="color: {{ $cifra['color'] }}">{{ $cifra['valor'] }}</div>
                            <div class="dd-motor-etq">{{ $cifra['etiqueta'] }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <div class="dd-grid">
            {{-- Historial de ejecuciones --}}
            <section>
                <div class="dd-seccion">
                    <div>
                        <h2>Historial de ejecuciones</h2>
                        <p>Ejecuciones del extractor registradas por fuente</p>
                    </div>
                    <a class="dd-enlace" href="{{ $urls['motor'] }}">Ver todo →</a>
                </div>

                <div class="dd-card">
                    @if ($ejecuciones === [])
                        <div class="dd-vacio">El motor todavía no tiene ejecuciones registradas.</div>
                    @else
                        <div class="dd-tabla-ctn">
                            <table class="dd-tabla">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Inicio</th>
                                        <th>Estado</th>
                                        <th>Nuevas</th>
                                        <th>Actualizadas</th>
                                        <th>Errores</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($ejecuciones as $e)
                                        @php
                                            [$claseEstado, $textoEstado] = $estadoEjecucion[$e['estado']] ?? ['borrador', $e['estado']];
                                        @endphp
                                        <tr>
                                            <td class="dd-mono" style="color: #1A4B8C; font-weight: 600">#{{ $e['id'] }}</td>
                                            <td>{{ $e['inicio'] }}</td>
                                            <td><span class="dd-badge dd-badge-{{ $claseEstado }}">{{ $textoEstado }}</span></td>
                                            <td style="color: #059669; font-weight: 700">{{ $e['nuevas'] }}</td>
                                            <td style="color: #2563EB; font-weight: 700">{{ $e['actualizadas'] }}</td>
                                            <td style="color: {{ $e['errores'] > 0 ? '#DC2626' : '#94A3B8' }}; font-weight: 700">{{ $e['errores'] }}</td>
                                            <td><a class="dd-enlace-texto" href="{{ $e['url'] }}">Ver detalle</a></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </section>

            <div class="dd-columna">
                {{-- Gestión rápida --}}
                <section class="dd-card dd-panel">
                    <h3>Gestión rápida</h3>
                    <div class="dd-accesos">
                        <a class="dd-acceso" style="background: #E8EEF8" href="{{ $urls['usuarios'] }}">
                            <span style="color: #1A4B8C">{!! $svg('usuarios', 16) !!}</span> Gestionar usuarios
                        </a>
                        <a class="dd-acceso" style="background: #DBEAFE" href="{{ $urls['fuentes'] }}">
                            <span style="color: #2563EB">{!! $svg('globo', 16) !!}</span> Fuentes web
                        </a>
                        <a class="dd-acceso" style="background: #EDE9FE" href="{{ $urls['motor'] }}">
                            <span style="color: #7C3AED">{!! $svg('cpu', 16) !!}</span> Motor de extracción
                        </a>
                        <a class="dd-acceso" style="background: #FEE2E2" href="{{ $urls['bitacora'] }}">
                            <span style="color: #DC2626">{!! $svg('alerta', 16) !!}</span> Bitácora de errores
                        </a>
                    </div>
                </section>

                {{-- Estado del sistema --}}
                <section class="dd-card dd-panel">
                    <h3>Estado del sistema</h3>
                    <div class="dd-estado">
                        @foreach ($sistema as $s)
                            <div class="dd-estado-fila">
                                <span class="dd-punto" style="background: {{ ['ok' => '#10B981', 'aviso' => '#F59E0B', 'mal' => '#EF4444'][$s['estado']] }}"></span>
                                <span>{{ $s['etiqueta'] }}</span>
                                <strong class="dd-{{ $s['estado'] }}">{{ $s['texto'] }}</strong>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-filament-panels::page>
