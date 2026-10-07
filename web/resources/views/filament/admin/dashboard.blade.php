<x-filament-panels::page>
    <style>
        .admin-dashboard {
            --text: #0F1827;
            --muted: #64748B;
            --primary: #059669;
            --primary-dark: #047857;
            --primary-soft: #D1FAE5;
            --border: #DDE3EE;
            --warning: #D97706;
            --danger: #DC2626;
            --blue: #2563EB;

            color: var(--text);
        }

        .admin-dashboard * {
            box-sizing: border-box;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 22px;
            margin-bottom: 24px;
        }

        .dashboard-title {
            margin: 0;
            font-size: 27px;
            font-weight: 850;
        }

        .dashboard-subtitle {
            margin-top: 7px;
            color: var(--muted);
            font-size: 14px;
        }

        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 11px;
            border-radius: 999px;
            background: var(--primary-soft);
            color: var(--primary-dark);
            font-size: 12px;
            font-weight: 850;
        }

        .role-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--primary);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 22px;
        }

        .stat-card {
            padding: 19px;
            background: white;
            border: 1px solid var(--border);
            border-radius: 15px;
            box-shadow: 0 1px 2px rgba(15, 24, 39, .03);
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 900;
        }

        .green {
            background: #D1FAE5;
            color: #047857;
        }

        .blue {
            background: #DBEAFE;
            color: #1D4ED8;
        }

        .purple {
            background: #F3E8FF;
            color: #7C3AED;
        }

        .yellow {
            background: #FEF3C7;
            color: #B45309;
        }

        .stat-value {
            margin-top: 15px;
            font-size: 27px;
            font-weight: 900;
        }

        .stat-label {
            margin-top: 3px;
            color: #475569;
            font-size: 12px;
            font-weight: 800;
        }

        .stat-help {
            margin-top: 5px;
            color: #94A3B8;
            font-size: 10px;
        }

        .layout {
            display: grid;
            grid-template-columns: minmax(0, 1.35fr) minmax(300px, .65fr);
            gap: 20px;
            align-items: start;
        }

        .column {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 1px 2px rgba(15, 24, 39, .03);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            padding: 19px 21px;
            border-bottom: 1px solid var(--border);
        }

        .card-title {
            font-size: 15px;
            font-weight: 850;
        }

        .card-description {
            margin-top: 3px;
            color: var(--muted);
            font-size: 11px;
        }

        .quick-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            padding: 20px;
        }

        .quick-item {
            min-height: 112px;
            padding: 16px;
            border: 1px solid var(--border);
            border-radius: 13px;
            background: #FBFCFE;
            text-decoration: none;
            color: var(--text);
            transition: .15s ease;
        }

        .quick-item:hover {
            border-color: #A7F3D0;
            background: #F8FFFC;
        }

        .quick-icon {
            width: 36px;
            height: 36px;
            margin-bottom: 11px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-soft);
            color: var(--primary-dark);
            font-size: 11px;
            font-weight: 900;
        }

        .quick-title {
            font-size: 12px;
            font-weight: 850;
        }

        .quick-text {
            margin-top: 4px;
            color: var(--muted);
            font-size: 10px;
            line-height: 1.45;
        }

        .execution {
            display: grid;
            grid-template-columns: 42px minmax(0, 1fr) auto;
            gap: 12px;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #EEF2F7;
        }

        .execution:last-child {
            border-bottom: 0;
        }

        .execution-icon {
            width: 42px;
            height: 42px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-soft);
            color: var(--primary-dark);
            font-size: 10px;
            font-weight: 900;
        }

        .execution-title {
            color: #334155;
            font-size: 11px;
            font-weight: 800;
        }

        .execution-meta {
            margin-top: 4px;
            color: #94A3B8;
            font-size: 9px;
        }

        .status {
            padding: 5px 8px;
            border-radius: 999px;
            font-size: 9px;
            font-weight: 850;
            white-space: nowrap;
        }

        .status-success {
            background: #D1FAE5;
            color: #047857;
        }

        .status-warning {
            background: #FEF3C7;
            color: #92400E;
        }

        .source-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 13px;
            padding: 13px 19px;
            border-bottom: 1px solid #EEF2F7;
        }

        .source-row:last-child {
            border-bottom: 0;
        }

        .source-name {
            color: #334155;
            font-size: 11px;
            font-weight: 800;
        }

        .source-url {
            margin-top: 3px;
            color: #94A3B8;
            font-size: 9px;
        }

        .source-state {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #047857;
            font-size: 9px;
            font-weight: 850;
        }

        .source-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--primary);
        }

        .alert {
            display: flex;
            gap: 11px;
            padding: 14px 19px;
            border-bottom: 1px solid #EEF2F7;
        }

        .alert:last-child {
            border-bottom: 0;
        }

        .alert-icon {
            width: 29px;
            height: 29px;
            flex: 0 0 29px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 900;
        }

        .alert-warning {
            background: #FEF3C7;
            color: #B45309;
        }

        .alert-danger {
            background: #FEE2E2;
            color: #B91C1C;
        }

        .alert-title {
            color: #334155;
            font-size: 10px;
            font-weight: 800;
        }

        .alert-text {
            margin-top: 3px;
            color: #94A3B8;
            font-size: 9px;
            line-height: 1.4;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            padding: 11px 19px;
            border-bottom: 1px solid #EEF2F7;
            font-size: 10px;
        }

        .info-row:last-child {
            border-bottom: 0;
        }

        .info-row span {
            color: var(--muted);
        }

        .info-row strong {
            color: #334155;
        }

        @media (max-width: 1050px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .layout {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 650px) {
            .dashboard-header {
                flex-direction: column;
            }

            .stats-grid,
            .quick-grid {
                grid-template-columns: 1fr;
            }
        }
    
        .stat-link {
            display: block;
            color: inherit;
            text-decoration: none;
            transition:
                transform .15s ease,
                box-shadow .15s ease,
                border-color .15s ease;
        }

        .stat-link:hover {
            transform: translateY(-2px);
            border-color: #A7F3D0;
            box-shadow: 0 8px 22px rgba(15,24,39,.06);
        }

        .section-link {
            color: var(--primary-dark);
            font-size: 10px;
            font-weight: 850;
            text-decoration: none;
            white-space: nowrap;
        }

        .execution-link,
        .source-link,
        .alert-link {
            color: inherit;
            text-decoration: none;
        }

        .execution-link:hover,
        .source-link:hover,
        .alert-link:hover {
            background: #FAFFFC;
        }

        .status-danger {
            background: #FEE2E2;
            color: #B91C1C;
        }

        .status-neutral {
            background: #F1F5F9;
            color: #64748B;
        }

        .source-state.inactive {
            color: #94A3B8;
        }

        .source-state.inactive .source-dot {
            background: #CBD5E1;
        }

        .empty-state {
            padding: 28px 20px;
            text-align: center;
            color: #94A3B8;
            font-size: 10px;
            line-height: 1.6;
        }

        .empty-title {
            margin-bottom: 4px;
            color: #475569;
            font-size: 11px;
            font-weight: 850;
        }

        .system-ok {
            color: #059669;
        }

        .system-muted {
            color: #64748B;
        }

</style>

    <div class="admin-dashboard">

        <div class="dashboard-header">

            <div>

                <h1 class="dashboard-title">
                    Dashboard Administrador
                </h1>

                <div class="dashboard-subtitle">
                    Supervisa usuarios, fuentes web,
                    convocatorias y el funcionamiento
                    general del sistema.
                </div>

            </div>

            <span class="role-badge">
                <span class="role-dot"></span>
                Administrador
            </span>

        </div>

        <div class="stats-grid">

            <a
                class="stat-card stat-link"
                href="{{
                    route(
                        'filament.admin.resources.users.index'
                    )
                }}"
            >

                <div class="stat-icon green">
                    U
                </div>

                <div class="stat-value">
                    {{ $usuarios }}
                </div>

                <div class="stat-label">
                    Usuarios
                </div>

                <div class="stat-help">
                    {{
                        $usuariosPorRol[
                            'ADMINISTRADOR'
                        ] ?? 0
                    }}
                    admin ·

                    {{
                        $usuariosPorRol[
                            'DIRECTIVO'
                        ] ?? 0
                    }}
                    directivo ·

                    {{
                        $usuariosPorRol[
                            'DOCENTE'
                        ] ?? 0
                    }}
                    docente
                </div>

            </a>

            <a
                class="stat-card stat-link"
                href="{{
                    route(
                        'filament.admin.resources.convocatorias.index'
                    )
                }}"
            >

                <div class="stat-icon blue">
                    C
                </div>

                <div class="stat-value">
                    {{ $convocatorias }}
                </div>

                <div class="stat-label">
                    Convocatorias
                </div>

                <div class="stat-help">
                    {{ $pendientesRevision }}
                    pendientes de revisión
                </div>

            </a>

            <a
                class="stat-card stat-link"
                href="{{
                    route(
                        'filament.admin.resources.fuentes.index'
                    )
                }}"
            >

                <div class="stat-icon purple">
                    F
                </div>

                <div class="stat-value">
                    {{ $fuentes }}
                </div>

                <div class="stat-label">
                    Fuentes web
                </div>

                <div class="stat-help">
                    {{ $fuentesActivas }}
                    activas ·
                    {{
                        max(
                            0,
                            $fuentes
                            - $fuentesActivas
                        )
                    }}
                    inactivas
                </div>

            </a>

            <a
                class="stat-card stat-link"
                href="{{
                    route(
                        'filament.admin.pages.bitacora-errores'
                    )
                }}"
            >

                <div class="stat-icon yellow">
                    !
                </div>

                <div class="stat-value">
                    {{ $alertas }}
                </div>

                <div class="stat-label">
                    Alertas
                </div>

                <div class="stat-help">
                    Errores pendientes de resolver
                </div>

            </a>

        </div>

        <div class="layout">

            <div class="column">

                <section class="card">

                    <div class="card-header">

                        <div>

                            <div class="card-title">
                                Accesos rápidos
                            </div>

                            <div
                                class="
                                    card-description
                                "
                            >
                                Herramientas principales
                                de administración
                            </div>

                        </div>

                    </div>

                    <div class="quick-grid">

                        <a
                            class="quick-item"
                            href="{{
                                route(
                                    'filament.admin.resources.users.index'
                                )
                            }}"
                        >

                            <div class="quick-icon">
                                U
                            </div>

                            <div class="quick-title">
                                Usuarios
                            </div>

                            <div class="quick-text">
                                Administra cuentas,
                                roles, departamentos
                                y estado de acceso.
                            </div>

                        </a>

                        <a
                            class="quick-item"
                            href="{{
                                route(
                                    'filament.admin.resources.roles.index'
                                )
                            }}"
                        >

                            <div class="quick-icon">
                                R
                            </div>

                            <div class="quick-title">
                                Roles y permisos
                            </div>

                            <div class="quick-text">
                                Administra los perfiles
                                de acceso del sistema.
                            </div>

                        </a>

                        <a
                            class="quick-item"
                            href="{{
                                route(
                                    'filament.admin.resources.fuentes.index'
                                )
                            }}"
                        >

                            <div class="quick-icon">
                                F
                            </div>

                            <div class="quick-title">
                                Fuentes web
                            </div>

                            <div class="quick-text">
                                Supervisa las fuentes
                                utilizadas por el motor
                                de extracción.
                            </div>

                        </a>

                        <a
                            class="quick-item"
                            href="{{
                                route(
                                    'filament.admin.pages.motor-extraccion'
                                )
                            }}"
                        >

                            <div class="quick-icon">
                                B
                            </div>

                            <div class="quick-title">
                                Motor de extracción
                            </div>

                            <div class="quick-text">
                                Consulta ejecuciones,
                                resultados y estado
                                del bot.
                            </div>

                        </a>

                        <a
                            class="quick-item"
                            href="{{
                                route(
                                    'filament.admin.pages.bitacora-errores'
                                )
                            }}"
                        >

                            <div class="quick-icon">
                                !
                            </div>

                            <div class="quick-title">
                                Bitácora de errores
                            </div>

                            <div class="quick-text">
                                Revisa errores reales
                                de los procesos
                                automáticos.
                            </div>

                        </a>

                        <a
                            class="quick-item"
                            href="{{
                                route(
                                    'filament.admin.resources.convocatorias.index'
                                )
                            }}"
                        >

                            <div class="quick-icon">
                                C
                            </div>

                            <div class="quick-title">
                                Gestión de convocatorias
                            </div>

                            <div class="quick-text">
                                Consulta y administra
                                los registros de
                                convocatorias.
                            </div>

                        </a>

                    </div>

                </section>

                <section class="card">

                    <div class="card-header">

                        <div>

                            <div class="card-title">
                                Ejecuciones recientes
                            </div>

                            <div
                                class="
                                    card-description
                                "
                            >
                                Actividad real del
                                motor de extracción
                            </div>

                        </div>

                        <a
                            class="section-link"
                            href="{{
                                route(
                                    'filament.admin.pages.motor-extraccion'
                                )
                            }}"
                        >
                            Ver motor →
                        </a>

                    </div>

                    @forelse (
                        $ejecuciones
                        as $ejecucion
                    )

                        <a
                            href="{{
                                $ejecucion[
                                    'url'
                                ]
                            }}"
                            class="
                                execution
                                execution-link
                            "
                        >

                            <div
                                class="
                                    execution-icon
                                "
                            >
                                BOT
                            </div>

                            <div>

                                <div
                                    class="
                                        execution-title
                                    "
                                >
                                    {{
                                        $ejecucion[
                                            'titulo'
                                        ]
                                    }}
                                </div>

                                <div
                                    class="
                                        execution-meta
                                    "
                                >
                                    {{
                                        $ejecucion[
                                            'detalle'
                                        ]
                                    }}
                                    ·
                                    {{
                                        $ejecucion[
                                            'fecha'
                                        ]
                                    }}
                                </div>

                            </div>

                            <span
                                class="
                                    status
                                    {{
                                        $ejecucion[
                                            'clase'
                                        ]
                                    }}
                                "
                            >
                                {{
                                    $ejecucion[
                                        'estado'
                                    ]
                                }}
                            </span>

                        </a>

                    @empty

                        <div class="empty-state">

                            <div class="empty-title">
                                Sin ejecuciones
                            </div>

                            El motor todavía no tiene
                            ciclos registrados en
                            PostgreSQL.

                        </div>

                    @endforelse

                </section>

            </div>

            <div class="column">

                <section class="card">

                    <div class="card-header">

                        <div>

                            <div class="card-title">
                                Estado de fuentes
                            </div>

                            <div
                                class="
                                    card-description
                                "
                            >
                                Fuentes configuradas
                                en PostgreSQL
                            </div>

                        </div>

                        <a
                            class="section-link"
                            href="{{
                                route(
                                    'filament.admin.resources.fuentes.index'
                                )
                            }}"
                        >
                            Ver todas →
                        </a>

                    </div>

                    @forelse (
                        $fuentesListado
                        as $fuente
                    )

                        <a
                            href="{{
                                route(
                                    'filament.admin.pages.gestionar-fuente',
                                    [
                                        'record' =>
                                            $fuente->id,
                                    ]
                                )
                            }}"
                            class="
                                source-row
                                source-link
                            "
                        >

                            <div>

                                <div class="source-name">
                                    {{ $fuente->nombre }}
                                </div>

                                <div class="source-url">
                                    {{
                                        $fuente
                                            ->tipo_fuente
                                        ?? 'Sin tipo'
                                    }}
                                    ·
                                    {{
                                        \Illuminate\Support\Str::limit(
                                            $fuente->url_base,
                                            38
                                        )
                                    }}
                                </div>

                            </div>

                            <div
                                class="
                                    source-state
                                    {{
                                        $fuente->activa
                                            ? ''
                                            : 'inactive'
                                    }}
                                "
                            >
                                <span
                                    class="
                                        source-dot
                                    "
                                ></span>

                                {{
                                    $fuente->activa
                                        ? 'Activa'
                                        : 'Inactiva'
                                }}
                            </div>

                        </a>

                    @empty

                        <div class="empty-state">

                            <div class="empty-title">
                                Sin fuentes
                            </div>

                            Todavía no existen fuentes
                            configuradas.

                        </div>

                    @endforelse

                </section>

                <section class="card">

                    <div class="card-header">

                        <div>

                            <div class="card-title">
                                Alertas del sistema
                            </div>

                            <div
                                class="
                                    card-description
                                "
                            >
                                Errores pendientes
                                de revisión
                            </div>

                        </div>

                        <a
                            class="section-link"
                            href="{{
                                route(
                                    'filament.admin.pages.bitacora-errores'
                                )
                            }}"
                        >
                            Ver bitácora →
                        </a>

                    </div>

                    @forelse (
                        $errores
                        as $error
                    )

                        <a
                            class="
                                alert
                                alert-link
                            "
                            href="{{
                                $error[
                                    'url'
                                ]
                            }}"
                        >

                            <div
                                class="
                                    alert-icon
                                    alert-danger
                                "
                            >
                                !
                            </div>

                            <div>

                                <div
                                    class="
                                        alert-title
                                    "
                                >
                                    {{
                                        $error[
                                            'titulo'
                                        ]
                                    }}
                                </div>

                                <div
                                    class="
                                        alert-text
                                    "
                                >
                                    {{
                                        $error[
                                            'mensaje'
                                        ]
                                    }}

                                    <br>

                                    {{
                                        $error[
                                            'fecha'
                                        ]
                                    }}
                                </div>

                            </div>

                        </a>

                    @empty

                        <div class="empty-state">

                            <div class="empty-title">
                                Sin alertas pendientes
                            </div>

                            La bitácora no contiene
                            errores sin resolver.

                        </div>

                    @endforelse

                </section>

                <section class="card">

                    <div class="card-header">

                        <div class="card-title">
                            Estado general
                        </div>

                    </div>

                    <div class="info-row">

                        <span>
                            Base de datos
                        </span>

                        <strong
                            class="system-ok"
                        >
                            Operativa
                        </strong>

                    </div>

                    <div class="info-row">

                        <span>
                            Motor de extracción
                        </span>

                        <strong
                            class="{{
                                $ultimaEjecucion
                                    ? 'system-ok'
                                    : 'system-muted'
                            }}"
                        >
                            @if ($ultimaEjecucion)

                                {{
                                    match (
                                        $ultimaEjecucion
                                            ->estado
                                    ) {
                                        'COMPLETADO' =>
                                            'Último ciclo completado',

                                        'EJECUTANDO' =>
                                            'Ejecutando',

                                        'INICIADO' =>
                                            'Iniciado',

                                        'COMPLETADO_CON_ERRORES' =>
                                            'Completado con errores',

                                        'FALLIDO' =>
                                            'Último ciclo fallido',

                                        default =>
                                            $ultimaEjecucion
                                                ->estado
                                                ?? 'Sin estado',
                                    }
                                }}

                            @else

                                Sin ejecuciones

                            @endif
                        </strong>

                    </div>

                    <div class="info-row">

                        <span>
                            Fuentes activas
                        </span>

                        <strong>
                            {{ $fuentesActivas }}
                            de
                            {{ $fuentes }}
                        </strong>

                    </div>

                    <div class="info-row">

                        <span>
                            Último ciclo
                        </span>

                        <strong>
                            @if (
                                $ultimaEjecucion
                                &&
                                $ultimaEjecucion
                                    ->fecha_inicio
                            )

                                {{
                                    \Carbon\Carbon::parse(
                                        $ultimaEjecucion
                                            ->fecha_inicio
                                    )->format(
                                        'd/m/Y H:i'
                                    )
                                }}

                            @else

                                Sin registro

                            @endif
                        </strong>

                    </div>

                </section>

            </div>

        </div>

    </div>

</x-filament-panels::page>
