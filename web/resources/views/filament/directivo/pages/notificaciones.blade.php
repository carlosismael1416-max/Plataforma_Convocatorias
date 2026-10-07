<x-filament-panels::page>
    <style>
        .directivo-notifications {
            --text: #0F1827;
            --muted: #64748B;
            --primary: #7C3AED;
            --primary-dark: #6D28D9;
            --primary-soft: #F3E8FF;
            --border: #DDE3EE;
            --success: #059669;
            --warning: #D97706;
            --danger: #DC2626;

            color: var(--text);
        }

        .directivo-notifications * {
            box-sizing: border-box;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 24px;
        }

        .page-title {
            margin: 0;
            font-size: 26px;
            font-weight: 850;
        }

        .page-subtitle {
            margin-top: 7px;
            color: var(--muted);
            font-size: 14px;
        }

        .header-actions {
            display: flex;
            gap: 9px;
            flex-wrap: wrap;
        }

        .btn {
            min-height: 40px;
            padding: 0 15px;
            border: 1px solid var(--border);
            border-radius: 9px;
            background: white;
            color: #475569;
            font-size: 11px;
            font-weight: 850;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 22px;
        }

        .summary-card {
            padding: 18px;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: white;
        }

        .summary-label {
            color: var(--muted);
            font-size: 11px;
        }

        .summary-value {
            margin-top: 7px;
            font-size: 25px;
            font-weight: 900;
        }

        .summary-help {
            margin-top: 4px;
            color: #94A3B8;
            font-size: 10px;
        }

        .layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 285px;
            gap: 22px;
            align-items: start;
        }

        .panel {
            border: 1px solid var(--border);
            border-radius: 16px;
            background: white;
            overflow: hidden;
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            padding: 19px 21px;
            border-bottom: 1px solid var(--border);
        }

        .panel-title {
            font-size: 15px;
            font-weight: 850;
        }

        .counter {
            padding: 5px 9px;
            border-radius: 999px;
            background: var(--primary-soft);
            color: var(--primary-dark);
            font-size: 10px;
            font-weight: 850;
        }

        .filters {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            padding: 15px 21px;
            border-bottom: 1px solid var(--border);
            background: #FBFCFE;
        }

        .filter {
            min-height: 33px;
            padding: 0 12px;
            border: 1px solid var(--border);
            border-radius: 999px;
            background: white;
            color: #475569;
            font-size: 10px;
            font-weight: 800;
            cursor: pointer;
        }

        .filter.active {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }

        .notification {
            display: grid;
            grid-template-columns: 45px minmax(0, 1fr) auto;
            gap: 13px;
            padding: 18px 21px;
            border-bottom: 1px solid #EEF2F7;
            position: relative;
        }

        .notification:last-child {
            border-bottom: 0;
        }

        .notification.unread {
            background: #FCFAFF;
        }

        .notification.unread::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: var(--primary);
        }

        .icon {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 900;
        }

        .purple {
            background: #F3E8FF;
            color: #7C3AED;
        }

        .yellow {
            background: #FEF3C7;
            color: #B45309;
        }

        .green {
            background: #D1FAE5;
            color: #047857;
        }

        .red {
            background: #FEE2E2;
            color: #B91C1C;
        }

        .notification-title {
            color: #334155;
            font-size: 12px;
            font-weight: 850;
        }

        .new {
            display: inline-flex;
            margin-left: 6px;
            padding: 3px 6px;
            border-radius: 999px;
            background: #EDE9FE;
            color: #6D28D9;
            font-size: 8px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .notification-text {
            margin-top: 5px;
            color: var(--muted);
            font-size: 10px;
            line-height: 1.55;
        }

        .notification-time {
            margin-top: 7px;
            color: #94A3B8;
            font-size: 9px;
        }

        .menu-btn {
            border: 0;
            background: transparent;
            color: #94A3B8;
            font-size: 18px;
            cursor: pointer;
        }

        .sidebar {
            position: sticky;
            top: 90px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .side-card {
            padding: 19px;
            border: 1px solid var(--border);
            border-radius: 15px;
            background: white;
        }

        .side-title {
            margin-bottom: 14px;
            font-size: 13px;
            font-weight: 850;
        }

        .preference {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: flex-start;
            padding: 12px 0;
            border-bottom: 1px solid #EEF2F7;
        }

        .preference:last-child {
            border-bottom: 0;
        }

        .preference-name {
            color: #334155;
            font-size: 10px;
            font-weight: 800;
        }

        .preference-help {
            margin-top: 3px;
            color: #94A3B8;
            font-size: 9px;
            line-height: 1.4;
        }

        .switch {
            width: 38px;
            height: 22px;
            flex: 0 0 38px;
            padding: 3px;
            border-radius: 999px;
            background: #CBD5E1;
        }

        .switch.active {
            background: var(--primary);
        }

        .switch-dot {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,.15);
        }

        .switch.active .switch-dot {
            margin-left: 16px;
        }

        .info-box {
            padding: 13px;
            border: 1px solid #DDD6FE;
            border-radius: 10px;
            background: #F5F3FF;
            color: #5B21B6;
            font-size: 10px;
            line-height: 1.5;
        }

        @media (max-width: 1000px) {
            .layout {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: static;
            }

            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 650px) {
            .page-header {
                flex-direction: column;
            }

            .header-actions {
                width: 100%;
            }

            .header-actions .btn {
                flex: 1;
            }

            .summary-grid {
                grid-template-columns: 1fr;
            }

            .notification {
                grid-template-columns: 42px minmax(0, 1fr);
                padding: 16px;
            }

            .notification > :last-child {
                display: none;
            }
        }
    
        .summary-card-action {
            display: block;
            width: 100%;
            text-align: left;
            color: inherit;
            text-decoration: none;
            cursor: pointer;
            transition:
                transform .18s ease,
                border-color .18s ease,
                box-shadow .18s ease;
        }

        button.summary-card-action {
            font-family: inherit;
        }

        .summary-card-action:hover {
            transform: translateY(-2px);
            border-color: #C4B5FD;
            box-shadow: 0 8px 20px rgba(15,24,39,.06);
        }

        .notification-actions {
            display: flex;
            flex-direction: column;
            gap: 6px;
            align-items: flex-end;
        }

        .notification-btn {
            min-height: 29px;
            padding: 0 9px;
            border: 1px solid #DDE3EE;
            border-radius: 7px;
            background: white;
            color: #64748B;
            font-size: 8px;
            font-weight: 850;
            cursor: pointer;
            white-space: nowrap;
        }

        .notification-btn.primary {
            border-color: #C4B5FD;
            color: #6D28D9;
            background: #FAF7FF;
        }

        .empty-state {
            padding: 40px 22px;
            text-align: center;
            color: #94A3B8;
        }

        .empty-title {
            color: #475569;
            font-size: 13px;
            font-weight: 850;
        }

        .empty-help {
            margin-top: 6px;
            font-size: 10px;
        }

        button.switch {
            border: 0;
            cursor: pointer;
        }

        .config-help {
            margin-bottom: 12px;
            padding: 10px;
            border-radius: 9px;
            background: #F8FAFC;
            color: #64748B;
            font-size: 9px;
            line-height: 1.5;
        }

        .pagination-box {
            padding: 15px 20px;
            border-top: 1px solid #EEF2F7;
        }

        .btn:disabled {
            opacity: .5;
            cursor: not-allowed;
        }

</style>

    <div class="directivo-notifications">

        <div class="page-header">

            <div>

                <h1 class="page-title">
                    Notificaciones
                </h1>

                <div class="page-subtitle">
                    Consulta avisos relacionados con
                    convocatorias, revisiones y actividad
                    institucional.
                </div>

            </div>

            <div class="header-actions">

                <button
                    class="btn"
                    type="button"
                    wire:click="alternarConfiguracion"
                >
                    {{
                        $mostrarConfiguracion
                            ? 'Cerrar configuración'
                            : 'Configurar'
                    }}
                </button>

                <button
                    class="btn btn-primary"
                    type="button"
                    wire:click="marcarTodasComoLeidas"
                    wire:loading.attr="disabled"
                    wire:target="marcarTodasComoLeidas"
                    @disabled($sinLeer === 0)
                >
                    Marcar todo como leído
                </button>

            </div>

        </div>

        <div class="summary-grid">

            <button
                type="button"
                class="
                    summary-card
                    summary-card-action
                "
                wire:click="
                    establecerFiltro('sin_leer')
                "
            >
                <div class="summary-label">
                    Sin leer
                </div>

                <div class="summary-value">
                    {{ $sinLeer }}
                </div>

                <div class="summary-help">
                    Avisos pendientes
                </div>
            </button>

            <a
                href="{{
                    route(
                        'filament.directivo.pages.convocatorias-por-revisar'
                    )
                }}"
                class="
                    summary-card
                    summary-card-action
                "
            >
                <div class="summary-label">
                    Por revisar
                </div>

                <div class="summary-value">
                    {{ $porRevisar }}
                </div>

                <div class="summary-help">
                    Abrir convocatorias pendientes →
                </div>
            </a>

            <a
                href="{{
                    route(
                        'filament.directivo.pages.convocatorias-por-revisar'
                    )
                }}"
                class="
                    summary-card
                    summary-card-action
                "
            >
                <div class="summary-label">
                    Próximos cierres
                </div>

                <div class="summary-value">
                    {{ $proximosCierres }}
                </div>

                <div class="summary-help">
                    Durante los próximos 30 días →
                </div>
            </a>

            <button
                type="button"
                class="
                    summary-card
                    summary-card-action
                "
                wire:click="
                    establecerFiltro('hoy')
                "
            >
                <div class="summary-label">
                    Actividad hoy
                </div>

                <div class="summary-value">
                    {{ $actividadHoy }}
                </div>

                <div class="summary-help">
                    Notificaciones registradas hoy
                </div>
            </button>

        </div>

        <div class="layout">

            <main class="panel">

                <div class="panel-header">

                    <div class="panel-title">
                        Notificaciones recientes
                    </div>

                    <span class="counter">
                        {{ $total }}
                        {{
                            $total === 1
                                ? 'notificación'
                                : 'notificaciones'
                        }}
                    </span>

                </div>

                <div class="filters">

                    @foreach (
                        [
                            'todas' => 'Todas',
                            'sin_leer' => 'Sin leer',
                            'revisiones' => 'Revisiones',
                            'convocatorias' => 'Convocatorias',
                            'sistema' => 'Sistema',
                        ]
                        as $valor => $texto
                    )

                        <button
                            class="
                                filter
                                {{
                                    $filtro === $valor
                                        ? 'active'
                                        : ''
                                }}
                            "
                            type="button"
                            wire:click="
                                establecerFiltro(
                                    '{{ $valor }}'
                                )
                            "
                        >
                            {{ $texto }}
                        </button>

                    @endforeach

                </div>

                @forelse (
                    $notificaciones
                    as $notificacion
                )

                    <div
                        class="
                            notification
                            {{
                                ! $notificacion[
                                    'leida'
                                ]
                                    ? 'unread'
                                    : ''
                            }}
                        "
                    >

                        <div
                            class="
                                icon
                                {{
                                    $notificacion[
                                        'color'
                                    ]
                                }}
                            "
                        >
                            {{
                                $notificacion[
                                    'icono'
                                ]
                            }}
                        </div>

                        <div>

                            <div class="notification-title">

                                {{
                                    $notificacion[
                                        'titulo'
                                    ]
                                }}

                                @if (
                                    ! $notificacion[
                                        'leida'
                                    ]
                                )

                                    <span class="new">
                                        Nueva
                                    </span>

                                @endif

                            </div>

                            <div class="notification-text">
                                {{
                                    $notificacion[
                                        'mensaje'
                                    ]
                                }}
                            </div>

                            <div class="notification-time">
                                {{
                                    $notificacion[
                                        'fecha'
                                    ]
                                }}
                            </div>

                        </div>

                        <div class="notification-actions">

                            @if (
                                ! $notificacion[
                                    'leida'
                                ]
                            )

                                <button
                                    class="
                                        notification-btn
                                    "
                                    type="button"
                                    wire:click="
                                        marcarComoLeida(
                                            {{
                                                $notificacion[
                                                    'id'
                                                ]
                                            }}
                                        )
                                    "
                                >
                                    Marcar leída
                                </button>

                            @endif

                            @if (
                                $notificacion[
                                    'tiene_destino'
                                ]
                            )

                                <button
                                    class="
                                        notification-btn
                                        primary
                                    "
                                    type="button"
                                    wire:click="
                                        abrirNotificacion(
                                            {{
                                                $notificacion[
                                                    'id'
                                                ]
                                            }}
                                        )
                                    "
                                >
                                    Abrir →
                                </button>

                            @endif

                        </div>

                    </div>

                @empty

                    <div class="empty-state">

                        <div class="empty-title">
                            No hay notificaciones
                        </div>

                        <div class="empty-help">
                            No existen avisos que
                            coincidan con el filtro
                            seleccionado.
                        </div>

                    </div>

                @endforelse

                @if (
                    $notificaciones
                        ->hasPages()
                )

                    <div class="pagination-box">
                        {{
                            $notificaciones
                                ->links()
                        }}
                    </div>

                @endif

            </main>

            <aside class="sidebar">

                @if ($mostrarConfiguracion)

                    <section class="side-card">

                        <div class="side-title">
                            Preferencias
                        </div>

                        <div class="config-help">
                            Estos cambios se guardan
                            para tu usuario en
                            PostgreSQL.
                        </div>

                        @php
                            $opciones = [
                                [
                                    'campo' =>
                                        'convocatorias_revision',
                                    'nombre' =>
                                        'Convocatorias por revisar',
                                    'ayuda' =>
                                        'Avisar sobre nuevas convocatorias pendientes.',
                                ],
                                [
                                    'campo' =>
                                        'fechas_proximas',
                                    'nombre' =>
                                        'Fechas próximas',
                                    'ayuda' =>
                                        'Avisos sobre próximas fechas de cierre.',
                                ],
                                [
                                    'campo' =>
                                        'nuevas_propuestas',
                                    'nombre' =>
                                        'Nuevas propuestas',
                                    'ayuda' =>
                                        'Notificar cuando se registren propuestas.',
                                ],
                                [
                                    'campo' =>
                                        'avisos_sistema',
                                    'nombre' =>
                                        'Avisos del sistema',
                                    'ayuda' =>
                                        'Información de procesos internos y extracción.',
                                ],
                            ];
                        @endphp

                        @foreach (
                            $opciones
                            as $opcion
                        )

                            <div class="preference">

                                <div>

                                    <div
                                        class="
                                            preference-name
                                        "
                                    >
                                        {{
                                            $opcion[
                                                'nombre'
                                            ]
                                        }}
                                    </div>

                                    <div
                                        class="
                                            preference-help
                                        "
                                    >
                                        {{
                                            $opcion[
                                                'ayuda'
                                            ]
                                        }}
                                    </div>

                                </div>

                                <button
                                    type="button"
                                    class="
                                        switch
                                        {{
                                            $preferencias
                                                ->getAttribute(
                                                    $opcion[
                                                        'campo'
                                                    ]
                                                )
                                                ? 'active'
                                                : ''
                                        }}
                                    "
                                    wire:click="
                                        alternarPreferencia(
                                            '{{
                                                $opcion[
                                                    'campo'
                                                ]
                                            }}'
                                        )
                                    "
                                    aria-label="
                                        Cambiar preferencia
                                    "
                                >
                                    <div
                                        class="
                                            switch-dot
                                        "
                                    ></div>
                                </button>

                            </div>

                        @endforeach

                    </section>

                @else

                    <section class="side-card">

                        <div class="side-title">
                            Preferencias
                        </div>

                        <div class="info-box">
                            Usa el botón
                            <strong>Configurar</strong>
                            para modificar qué tipos de
                            avisos deseas recibir.
                        </div>

                    </section>

                @endif

                <section class="side-card">

                    <div class="side-title">
                        Accesos relacionados
                    </div>

                    <a
                        href="{{
                            route(
                                'filament.directivo.pages.convocatorias-por-revisar'
                            )
                        }}"
                        style="
                            display:block;
                            margin-bottom:10px;
                            color:#7C3AED;
                            text-decoration:none;
                            font-size:10px;
                            font-weight:850;
                        "
                    >
                        Convocatorias por revisar →
                    </a>

                    <a
                        href="{{
                            route(
                                'filament.directivo.pages.estadisticas'
                            )
                        }}"
                        style="
                            display:block;
                            margin-bottom:10px;
                            color:#7C3AED;
                            text-decoration:none;
                            font-size:10px;
                            font-weight:850;
                        "
                    >
                        Estadísticas →
                    </a>

                    <a
                        href="{{
                            route(
                                'filament.directivo.pages.reportes'
                            )
                        }}"
                        style="
                            display:block;
                            color:#7C3AED;
                            text-decoration:none;
                            font-size:10px;
                            font-weight:850;
                        "
                    >
                        Reportes →
                    </a>

                </section>

            </aside>

        </div>

    </div>

</x-filament-panels::page>
