<x-filament-panels::page>

<style>
    .directivo-dashboard {
        --text: #0F1827;
        --muted: #64748B;
        --primary: #7C3AED;
        --primary-dark: #6D28D9;
        --primary-soft: #F3E8FF;
        --border: #DDE3EE;
        --success: #059669;
        --warning: #D97706;

        color: var(--text);
    }

    .directivo-dashboard * {
        box-sizing: border-box;
    }

    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 22px;
        margin-bottom: 22px;
    }

    .dashboard-title {
        margin: 0;
        font-size: 25px;
        font-weight: 850;
    }

    .dashboard-subtitle {
        margin-top: 6px;
        color: var(--muted);
        font-size: 12px;
    }

    .role-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        background: var(--primary-soft);
        color: var(--primary-dark);
        font-size: 10px;
        font-weight: 850;
    }

    .role-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--primary);
    }

    .stats-grid {
        display: grid;
        grid-template-columns:
            repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 20px;
    }

    .stat-card,
    .card {
        background: white;
        border: 1px solid var(--border);
        box-shadow:
            0 1px 2px
            rgba(15, 24, 39, .03);
    }

    .stat-card {
        padding: 17px;
        border-radius: 13px;
    }

    .stat-icon {
        width: 37px;
        height: 37px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 900;
    }

    .icon-purple {
        background: #F3E8FF;
        color: #7C3AED;
    }

    .icon-blue {
        background: #DBEAFE;
        color: #2563EB;
    }

    .icon-green {
        background: #D1FAE5;
        color: #047857;
    }

    .icon-yellow {
        background: #FEF3C7;
        color: #B45309;
    }

    .stat-value {
        margin-top: 13px;
        font-size: 25px;
        font-weight: 900;
    }

    .stat-label {
        margin-top: 3px;
        color: #475569;
        font-size: 10px;
        font-weight: 750;
    }

    .stat-help {
        margin-top: 4px;
        color: #94A3B8;
        font-size: 8px;
        line-height: 1.4;
    }

    .dashboard-layout {
        display: grid;
        grid-template-columns:
            minmax(0, 1.4fr)
            minmax(280px, .6fr);
        gap: 20px;
        align-items: start;
    }

    .column {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .card {
        overflow: hidden;
        border-radius: 14px;
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 17px 19px;
        border-bottom:
            1px solid var(--border);
    }

    .card-title {
        font-size: 13px;
        font-weight: 850;
    }

    .card-subtitle {
        margin-top: 3px;
        color: var(--muted);
        font-size: 9px;
    }

    .link {
        color: var(--primary);
        font-size: 9px;
        font-weight: 850;
        text-decoration: none;
    }

    .quick-actions {
        display: grid;
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
        gap: 10px;
        padding: 18px;
    }

    .quick-action {
        min-height: 100px;
        padding: 14px;
        border: 1px solid var(--border);
        border-radius: 11px;
        background: #FBFCFE;
        color: var(--text);
        text-decoration: none;
    }

    .quick-action:hover {
        border-color: #C4B5FD;
        background: #FCFAFF;
    }

    .quick-icon {
        width: 33px;
        height: 33px;
        margin-bottom: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 9px;
        background: var(--primary-soft);
        color: var(--primary);
        font-size: 11px;
        font-weight: 900;
    }

    .quick-title {
        font-size: 11px;
        font-weight: 850;
    }

    .quick-description {
        margin-top: 4px;
        color: var(--muted);
        font-size: 8px;
        line-height: 1.45;
    }

    .review-item {
        padding: 16px 19px;
        border-bottom: 1px solid #EEF2F7;
    }

    .review-item:last-child {
        border-bottom: 0;
    }

    .review-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
    }

    .review-title {
        color: #1E293B;
        font-size: 11px;
        font-weight: 850;
    }

    .review-organization {
        margin-top: 4px;
        color: var(--muted);
        font-size: 9px;
    }

    .review-badge {
        padding: 4px 8px;
        border-radius: 999px;
        background: #FEF3C7;
        color: #92400E;
        font-size: 8px;
        font-weight: 850;
        white-space: nowrap;
    }

    .review-info {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        margin-top: 11px;
    }

    .review-info-item {
        color: #94A3B8;
        font-size: 8px;
    }

    .review-info-item strong {
        color: #475569;
        font-weight: 800;
    }

    .review-actions {
        margin-top: 12px;
    }

    .small-btn {
        min-height: 31px;
        padding: 0 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--primary);
        border-radius: 7px;
        background: var(--primary);
        color: white;
        font-size: 8px;
        font-weight: 800;
        text-decoration: none;
    }

    .deadline {
        display: flex;
        gap: 10px;
        padding: 13px 18px;
        border-bottom: 1px solid #EEF2F7;
    }

    .deadline:last-child {
        border-bottom: 0;
    }

    .deadline-date {
        width: 42px;
        height: 46px;
        flex: 0 0 42px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border-radius: 9px;
        background: var(--primary-soft);
        color: var(--primary-dark);
    }

    .deadline-day {
        font-size: 14px;
        font-weight: 900;
        line-height: 1;
    }

    .deadline-month {
        margin-top: 3px;
        font-size: 7px;
        font-weight: 850;
        text-transform: uppercase;
    }

    .deadline-title {
        color: #334155;
        font-size: 10px;
        font-weight: 800;
    }

    .deadline-text {
        margin-top: 4px;
        color: var(--muted);
        font-size: 8px;
        line-height: 1.4;
    }

    .activity {
        display: flex;
        gap: 10px;
        padding: 12px 18px;
        border-bottom: 1px solid #EEF2F7;
    }

    .activity:last-child {
        border-bottom: 0;
    }

    .activity-dot {
        width: 8px;
        height: 8px;
        flex: 0 0 8px;
        margin-top: 4px;
        border-radius: 50%;
        background: var(--primary);
    }

    .activity-title {
        color: #334155;
        font-size: 9px;
        font-weight: 750;
    }

    .activity-time {
        margin-top: 4px;
        color: #94A3B8;
        font-size: 8px;
    }

    .empty {
        padding: 28px 18px;
        color: var(--muted);
        font-size: 9px;
        text-align: center;
    }

    @media (max-width: 1100px) {
        .stats-grid {
            grid-template-columns:
                repeat(2, 1fr);
        }

        .dashboard-layout {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 650px) {
        .dashboard-header {
            flex-direction: column;
        }

        .stats-grid,
        .quick-actions {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="directivo-dashboard">

    <div class="dashboard-header">

        <div>

            <h1 class="dashboard-title">
                Dashboard Directivo
            </h1>

            <div class="dashboard-subtitle">
                Bienvenido,
                {{ $usuario->name }}.
                Consulta convocatorias y supervisa
                la información registrada en la
                plataforma.
            </div>

        </div>

        <span class="role-badge">
            <span class="role-dot"></span>
            Directivo
        </span>

    </div>

    <div class="stats-grid">

        <div class="stat-card">

            <div class="stat-icon icon-purple">
                R
            </div>

            <div class="stat-value">
                {{ $porRevisar }}
            </div>

            <div class="stat-label">
                Por revisar
            </div>

            <div class="stat-help">
                Convocatorias en
                PENDIENTE_REVISION
            </div>

        </div>

        <div class="stat-card">

            <div class="stat-icon icon-blue">
                C
            </div>

            <div class="stat-value">
                {{ $totalConvocatorias }}
            </div>

            <div class="stat-label">
                Convocatorias
            </div>

            <div class="stat-help">
                Total registrado en PostgreSQL
            </div>

        </div>

        <div class="stat-card">

            <div class="stat-icon icon-green">
                ✓
            </div>

            <div class="stat-value">
                {{ $revisadas }}
            </div>

            <div class="stat-label">
                Revisadas
            </div>

            <div class="stat-help">
                Ya no están pendientes de revisión
            </div>

        </div>

        <div class="stat-card">

            <div class="stat-icon icon-yellow">
                P
            </div>

            <div class="stat-value">
                {{ $propuestasDocentes }}
            </div>

            <div class="stat-label">
                Propuestas
            </div>

            <div class="stat-help">
                Registradas por usuarios DOCENTE
            </div>

        </div>

    </div>

    <div class="dashboard-layout">

        <div class="column">

            <section class="card">

                <div class="card-header">

                    <div>

                        <div class="card-title">
                            Accesos rápidos
                        </div>

                        <div class="card-subtitle">
                            Funciones principales
                            del panel Directivo
                        </div>

                    </div>

                </div>

                <div class="quick-actions">

                    <a
                        href="{{
                            url(
                                '/directivo/buscar-convocatorias'
                            )
                        }}"
                        class="quick-action"
                    >

                        <div class="quick-icon">
                            ⌕
                        </div>

                        <div class="quick-title">
                            Buscar convocatorias
                        </div>

                        <div class="quick-description">
                            Consulta convocatorias
                            mediante filtros y
                            palabras clave.
                        </div>

                    </a>

                    <a
                        href="{{
                            url(
                                '/directivo/convocatorias-por-revisar'
                            )
                        }}"
                        class="quick-action"
                    >

                        <div class="quick-icon">
                            ✓
                        </div>

                        <div class="quick-title">
                            Convocatorias por revisar
                        </div>

                        <div class="quick-description">
                            Consulta los registros
                            pendientes de revisión.
                        </div>

                    </a>

                    <a
                        href="{{
                            url(
                                '/directivo/estadisticas'
                            )
                        }}"
                        class="quick-action"
                    >

                        <div class="quick-icon">
                            %
                        </div>

                        <div class="quick-title">
                            Estadísticas
                        </div>

                        <div class="quick-description">
                            Visualiza indicadores
                            generales del sistema.
                        </div>

                    </a>

                    <a
                        href="{{
                            url(
                                '/directivo/reportes'
                            )
                        }}"
                        class="quick-action"
                    >

                        <div class="quick-icon">
                            R
                        </div>

                        <div class="quick-title">
                            Reportes
                        </div>

                        <div class="quick-description">
                            Consulta los reportes
                            institucionales.
                        </div>

                    </a>

                </div>

            </section>

            <section class="card">

                <div class="card-header">

                    <div>

                        <div class="card-title">
                            Convocatorias pendientes
                            de revisión
                        </div>

                        <div class="card-subtitle">
                            Últimos registros que
                            requieren revisión
                        </div>

                    </div>

                    <a
                        href="{{
                            url(
                                '/directivo/convocatorias-por-revisar'
                            )
                        }}"
                        class="link"
                    >
                        Ver todas
                    </a>

                </div>

                @forelse (
                    $pendientes
                    as $convocatoria
                )

                    <div class="review-item">

                        <div class="review-top">

                            <div>

                                <div class="review-title">
                                    {{
                                        $convocatoria
                                            ->titulo
                                    }}
                                </div>

                                <div
                                    class="
                                        review-organization
                                    "
                                >
                                    {{
                                        $convocatoria
                                            ->organismo
                                            ?->nombre
                                        ?? 'Sin organismo'
                                    }}
                                </div>

                            </div>

                            <span class="review-badge">
                                Pendiente
                            </span>

                        </div>

                        <div class="review-info">

                            <div
                                class="
                                    review-info-item
                                "
                            >
                                Cierre:

                                <strong>
                                    {{
                                        $convocatoria
                                            ->fecha_cierre
                                            ?->format(
                                                'd/m/Y'
                                            )
                                        ?? 'Sin fecha'
                                    }}
                                </strong>
                            </div>

                            <div
                                class="
                                    review-info-item
                                "
                            >
                                Monto:

                                <strong>
                                    @if (
                                        $convocatoria
                                            ->monto_maximo
                                        !== null
                                    )

                                        $
                                        {{
                                            number_format(
                                                (float)
                                                $convocatoria
                                                    ->monto_maximo,
                                                2
                                            )
                                        }}

                                        {{
                                            $convocatoria
                                                ->moneda
                                            ?: 'MXN'
                                        }}

                                    @else

                                        No especificado

                                    @endif
                                </strong>
                            </div>

                            <div
                                class="
                                    review-info-item
                                "
                            >
                                Origen:

                                <strong>
                                    {{
                                        match (
                                            $convocatoria
                                                ->origen
                                        ) {
                                            'SCRAPING' =>
                                                'Automática',

                                            'MANUAL' =>
                                                'Manual',

                                            'ADMINISTRADOR' =>
                                                'Administrador',

                                            'API' =>
                                                'API',

                                            default =>
                                                $convocatoria
                                                    ->origen
                                                ?: 'Sin origen',
                                        }
                                    }}
                                </strong>
                            </div>

                        </div>

                        <div class="review-actions">

                            <a
                                href="{{
                                    url(
                                        '/directivo/convocatorias-por-revisar'
                                    )
                                }}"
                                class="small-btn"
                            >
                                Consultar pendientes
                            </a>

                        </div>

                    </div>

                @empty

                    <div class="empty">
                        No hay convocatorias
                        pendientes de revisión.
                    </div>

                @endforelse

            </section>

        </div>

        <div class="column">

            <section class="card">

                <div class="card-header">

                    <div>

                        <div class="card-title">
                            Próximos cierres
                        </div>

                        <div class="card-subtitle">
                            Fechas reales registradas
                            en convocatorias
                        </div>

                    </div>

                </div>

                @forelse (
                    $proximosCierres
                    as $convocatoria
                )

                    @php
                        $dias =
                            (int)
                            now()
                                ->startOfDay()
                                ->diffInDays(
                                    $convocatoria
                                        ->fecha_cierre
                                        ->copy()
                                        ->startOfDay()
                                );
                    @endphp

                    <div class="deadline">

                        <div class="deadline-date">

                            <span class="deadline-day">
                                {{
                                    $convocatoria
                                        ->fecha_cierre
                                        ->format('d')
                                }}
                            </span>

                            <span class="deadline-month">
                                {{
                                    $convocatoria
                                        ->fecha_cierre
                                        ->locale('es')
                                        ->translatedFormat(
                                            'M'
                                        )
                                }}
                            </span>

                        </div>

                        <div>

                            <div class="deadline-title">
                                {{
                                    $convocatoria
                                        ->titulo
                                }}
                            </div>

                            <div class="deadline-text">

                                @if ($dias === 0)

                                    Cierra hoy

                                @elseif ($dias === 1)

                                    Cierra en 1 día

                                @else

                                    Cierra en
                                    {{ $dias }}
                                    días

                                @endif

                                @if (
                                    $convocatoria
                                        ->organismo
                                )
                                    ·
                                    {{
                                        $convocatoria
                                            ->organismo
                                            ->nombre
                                    }}
                                @endif

                            </div>

                        </div>

                    </div>

                @empty

                    <div class="empty">
                        No existen cierres futuros
                        registrados.
                    </div>

                @endforelse

            </section>

            <section class="card">

                <div class="card-header">

                    <div>

                        <div class="card-title">
                            Registros recientes
                        </div>

                        <div class="card-subtitle">
                            Información real registrada
                            en la plataforma
                        </div>

                    </div>

                </div>

                @forelse (
                    $actividadReciente
                    as $actividad
                )

                    <div class="activity">

                        <span
                            class="activity-dot"
                        ></span>

                        <div>

                            <div class="activity-title">
                                {{
                                    $actividad[
                                        'titulo'
                                    ]
                                }}
                            </div>

                            <div class="activity-time">
                                {{
                                    $actividad[
                                        'fecha'
                                    ]
                                        ->locale('es')
                                        ->diffForHumans()
                                }}
                            </div>

                        </div>

                    </div>

                @empty

                    <div class="empty">
                        No hay registros recientes.
                    </div>

                @endforelse

            </section>

        </div>

    </div>

</div>

</x-filament-panels::page>
