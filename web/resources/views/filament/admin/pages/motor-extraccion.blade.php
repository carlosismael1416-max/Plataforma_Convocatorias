<x-filament-panels::page>
    <style>
        .extraction-engine {
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

        .extraction-engine * {
            box-sizing: border-box;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 22px;
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

        .engine-status {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 12px;
            border-radius: 999px;
            background: var(--primary-soft);
            color: var(--primary-dark);
            font-size: 11px;
            font-weight: 850;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--primary);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 20px;
        }

        .stat-card {
            padding: 18px;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: white;
        }

        .stat-icon {
            width: 38px;
            height: 38px;
            margin-bottom: 12px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
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

        .yellow {
            background: #FEF3C7;
            color: #B45309;
        }

        .red {
            background: #FEE2E2;
            color: #B91C1C;
        }

        .stat-value {
            font-size: 25px;
            font-weight: 900;
        }

        .stat-label {
            margin-top: 3px;
            color: #475569;
            font-size: 11px;
            font-weight: 800;
        }

        .stat-help {
            margin-top: 4px;
            color: #94A3B8;
            font-size: 9px;
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
            border: 1px solid var(--border);
            border-radius: 15px;
            background: white;
            overflow: hidden;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            padding: 18px 20px;
            border-bottom: 1px solid var(--border);
        }

        .card-title {
            font-size: 14px;
            font-weight: 850;
        }

        .card-description {
            margin-top: 3px;
            color: var(--muted);
            font-size: 10px;
        }

        .primary-btn {
            min-height: 38px;
            padding: 0 14px;
            border: 1px solid var(--primary);
            border-radius: 9px;
            background: var(--primary);
            color: white;
            font-size: 10px;
            font-weight: 850;
            cursor: pointer;
        }

        .execution {
            display: grid;
            grid-template-columns: 46px minmax(0, 1fr) auto;
            gap: 13px;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 1px solid #EEF2F7;
        }

        .execution:last-child {
            border-bottom: 0;
        }

        .execution-icon {
            width: 46px;
            height: 46px;
            border-radius: 12px;
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
            font-weight: 850;
        }

        .execution-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 11px;
            margin-top: 5px;
            color: #94A3B8;
            font-size: 9px;
        }

        .execution-summary {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
            margin-top: 8px;
        }

        .chip {
            padding: 4px 7px;
            border-radius: 999px;
            background: #F1F5F9;
            color: #64748B;
            font-size: 8px;
            font-weight: 800;
        }

        .chip.success {
            background: #D1FAE5;
            color: #047857;
        }

        .chip.danger {
            background: #FEE2E2;
            color: #B91C1C;
        }

        .badge {
            padding: 6px 9px;
            border-radius: 999px;
            font-size: 9px;
            font-weight: 850;
            white-space: nowrap;
        }

        .badge-success {
            background: #D1FAE5;
            color: #047857;
        }

        .badge-warning {
            background: #FEF3C7;
            color: #92400E;
        }

        .badge-running {
            background: #DBEAFE;
            color: #1D4ED8;
        }

        .source-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 90px 80px;
            gap: 10px;
            align-items: center;
            padding: 13px 19px;
            border-bottom: 1px solid #EEF2F7;
        }

        .source-row:last-child {
            border-bottom: 0;
        }

        .source-name {
            color: #334155;
            font-size: 10px;
            font-weight: 800;
        }

        .source-info {
            margin-top: 3px;
            color: #94A3B8;
            font-size: 8px;
        }

        .mini-status {
            font-size: 9px;
            font-weight: 850;
        }

        .success-text {
            color: #059669;
        }

        .warning-text {
            color: #D97706;
        }

        .error-text {
            color: #DC2626;
        }

        .count {
            color: #475569;
            font-size: 9px;
            text-align: right;
        }

        .side-content {
            padding: 18px;
        }

        .engine-box {
            padding: 15px;
            border: 1px solid #A7F3D0;
            border-radius: 11px;
            background: #ECFDF5;
        }

        .engine-box-title {
            color: #047857;
            font-size: 11px;
            font-weight: 850;
        }

        .engine-box-text {
            margin-top: 5px;
            color: #059669;
            font-size: 9px;
            line-height: 1.5;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 0;
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
            text-align: right;
        }

        .progress-wrap {
            margin-top: 16px;
        }

        .progress-header {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 7px;
            color: #64748B;
            font-size: 9px;
        }

        .progress {
            height: 8px;
            overflow: hidden;
            border-radius: 999px;
            background: #E2E8F0;
        }

        .progress-value {
            width: 75%;
            height: 100%;
            border-radius: inherit;
            background: var(--primary);
        }

        .alert {
            display: flex;
            gap: 10px;
            padding: 12px 0;
            border-bottom: 1px solid #EEF2F7;
        }

        .alert:last-child {
            border-bottom: 0;
        }

        .alert-icon {
            width: 28px;
            height: 28px;
            flex: 0 0 28px;
            border-radius: 8px;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #FEF3C7;
            color: #B45309;
            font-size: 9px;
            font-weight: 900;
        }

        .alert-title {
            color: #334155;
            font-size: 9px;
            font-weight: 800;
        }

        .alert-description {
            margin-top: 3px;
            color: #94A3B8;
            font-size: 8px;
            line-height: 1.4;
        }

        .visual-note {
            margin: 0 18px 18px;
            padding: 12px;
            border-radius: 10px;
            border: 1px solid #A7F3D0;
            background: #ECFDF5;
            color: #047857;
            font-size: 9px;
            line-height: 1.5;
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
            .page-header {
                flex-direction: column;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .execution {
                grid-template-columns: 42px minmax(0, 1fr);
            }

            .execution > .badge {
                grid-column: 2;
                width: fit-content;
            }

            .source-row {
                grid-template-columns: 1fr;
            }

            .count {
                text-align: left;
            }
        }
    
        .badge-danger {
            background: #FEE2E2;
            color: #B91C1C;
        }

        .badge-neutral {
            background: #F1F5F9;
            color: #64748B;
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

        .empty-state {
            padding: 30px 20px;
            text-align: center;
            color: #94A3B8;
            font-size: 10px;
            line-height: 1.6;
        }

        .empty-title {
            margin-bottom: 5px;
            color: #475569;
            font-size: 11px;
            font-weight: 850;
        }

        .context-box {
            margin-bottom: 18px;
            padding: 13px 16px;
            border: 1px solid #BFDBFE;
            border-radius: 11px;
            background: #EFF6FF;
            color: #1D4ED8;
            font-size: 10px;
        }

        .context-box a {
            color: inherit;
            font-weight: 850;
            text-decoration: none;
        }

        .context-box a:hover {
            text-decoration: underline;
        }

        .engine-box.neutral {
            border-color: #CBD5E1;
            background: #F8FAFC;
        }

        .engine-box.neutral .engine-box-title {
            color: #475569;
        }

        .engine-box.neutral .engine-box-text {
            color: #64748B;
        }

        .engine-box.running {
            border-color: #BFDBFE;
            background: #EFF6FF;
        }

        .engine-box.running .engine-box-title,
        .engine-box.running .engine-box-text {
            color: #1D4ED8;
        }

        .header-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .secondary-btn {
            min-height: 38px;
            padding: 0 14px;
            border: 1px solid var(--border);
            border-radius: 9px;
            background: white;
            color: #475569;
            font-size: 10px;
            font-weight: 850;
            cursor: pointer;
        }

        .primary-btn:disabled {
            opacity: .55;
            cursor: not-allowed;
        }

</style>

    <div class="extraction-engine">

        <div class="page-header">

            <div>

                <h1 class="page-title">
                    Motor de Extracción
                </h1>

                <div class="page-subtitle">
                    Supervisa las ejecuciones,
                    fuentes y errores registrados
                    por el motor.
                </div>

            </div>


            <div class="engine-status">

                <span class="status-dot"></span>

                Monitor conectado a PostgreSQL

            </div>

        </div>


        @if ($fuenteContexto)

            <div class="context-box">

                Mostrando actividad relacionada con:

                <strong>
                    {{ $fuenteContexto->nombre }}
                </strong>

                ·

                <a
                    href="{{
                        route(
                            'filament.admin.pages.gestionar-fuente',
                            [
                                'record' =>
                                    $fuenteContexto->id,
                            ]
                        )
                    }}"
                >
                    Gestionar fuente
                </a>

            </div>

        @endif


        <div class="stats-grid">

            <div class="stat-card">

                <div class="stat-icon green">
                    BOT
                </div>

                <div class="stat-value">
                    {{ $totalEjecuciones }}
                </div>

                <div class="stat-label">
                    Ejecuciones
                </div>

                <div class="stat-help">
                    Ciclos registrados
                    en PostgreSQL
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-icon blue">
                    F
                </div>

                <div class="stat-value">
                    {{ $totalFuentes }}
                </div>

                <div class="stat-label">
                    Fuentes configuradas
                </div>

                <div class="stat-help">
                    {{ $fuentesActivas }}
                    activas actualmente
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-icon yellow">
                    C
                </div>

                <div class="stat-value">
                    {{ $totalDetectadas }}
                </div>

                <div class="stat-label">
                    Convocatorias detectadas
                </div>

                <div class="stat-help">
                    Acumuladas en ejecuciones
                    del motor
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-icon red">
                    !
                </div>

                <div class="stat-value">
                    {{ $erroresPendientes }}
                </div>

                <div class="stat-label">
                    Errores pendientes
                </div>

                <div class="stat-help">
                    Incidencias sin resolver
                </div>

            </div>

        </div>


        <div class="layout">

            <div class="column">


                <section class="card">

                    <div class="card-header">

                        <div>

                            <div class="card-title">
                                Ejecuciones recientes
                            </div>

                            <div class="card-description">
                                Historial real de ciclos
                                registrados por el motor.
                            </div>

                        </div>


                        <div
                            class="header-actions"
                            @if ($ejecucionActiva)
                                wire:poll.5s="$refresh"
                            @endif
                        >

                            <button
                                type="button"
                                class="secondary-btn"
                                wire:click="$refresh"
                            >
                                Actualizar estado
                            </button>

                            <button
                                type="button"
                                class="primary-btn"
                                wire:click="ejecutarCiclo"
                                wire:loading.attr="disabled"
                                wire:target="ejecutarCiclo"
                                @disabled(
                                    $ejecucionActiva !== null
                                )
                                title="{{
                                    $ejecucionActiva
                                        ? 'Hay una ejecución activa.'
                                        : 'Iniciar un ciclo real de extracción.'
                                }}"
                            >
                                <span
                                    wire:loading.remove
                                    wire:target="ejecutarCiclo"
                                >
                                    Ejecutar ciclo
                                </span>

                                <span
                                    wire:loading
                                    wire:target="ejecutarCiclo"
                                >
                                    Iniciando...
                                </span>
                            </button>

                        </div>

                    </div>


                    @forelse (
                        $ejecuciones
                        as $ejecucion
                    )

                        @php
                            $estado =
                                strtoupper(
                                    (string)
                                    $ejecucion->estado
                                );

                            [$badge, $estadoTexto] =
                                match ($estado) {
                                    'COMPLETADO' => [
                                        'badge-success',
                                        'Completado',
                                    ],

                                    'COMPLETADO_CON_ERRORES' => [
                                        'badge-warning',
                                        'Con errores',
                                    ],

                                    'EJECUTANDO' => [
                                        'badge-running',
                                        'Ejecutando',
                                    ],

                                    'INICIADO' => [
                                        'badge-running',
                                        'Iniciado',
                                    ],

                                    'FALLIDO' => [
                                        'badge-danger',
                                        'Fallido',
                                    ],

                                    default => [
                                        'badge-neutral',
                                        $estado !== ''
                                            ? str_replace(
                                                '_',
                                                ' ',
                                                $estado
                                            )
                                            : 'Sin estado',
                                    ],
                                };
                        @endphp


                        <a
                            class="
                                execution
                                execution-link
                            "
                            href="{{
                                route(
                                    'filament.admin.pages.detalle-ejecucion',
                                    [
                                        'record' =>
                                            $ejecucion->id,
                                    ]
                                )
                            }}"
                        >

                            <div class="execution-icon">
                                #{{ $ejecucion->id }}
                            </div>


                            <div>

                                <div class="execution-title">
                                    Ciclo de extracción
                                </div>

                                <div class="execution-meta">

                                    <span>
                                        {{
                                            $ejecucion
                                                ->fecha_inicio
                                                ?->format(
                                                    'd/m/Y H:i'
                                                )
                                            ?? 'Sin fecha'
                                        }}
                                    </span>

                                    <span>
                                        Duración:

                                        {{
                                            $ejecucion
                                                ->duracion_segundos
                                            !== null
                                                ? number_format(
                                                    (float)
                                                    $ejecucion
                                                        ->duracion_segundos,
                                                    1
                                                ).' s'
                                                : '—'
                                        }}
                                    </span>

                                </div>


                                <div class="execution-summary">

                                    <span class="chip">
                                        {{
                                            $ejecucion
                                                ->total_fuentes
                                            ?? 0
                                        }}
                                        fuentes
                                    </span>

                                    <span
                                        class="
                                            chip
                                            success
                                        "
                                    >
                                        {{
                                            $ejecucion
                                                ->fuentes_exitosas
                                            ?? 0
                                        }}
                                        exitosas
                                    </span>

                                    <span
                                        class="
                                            chip
                                            danger
                                        "
                                    >
                                        {{
                                            $ejecucion
                                                ->fuentes_fallidas
                                            ?? 0
                                        }}
                                        fallidas
                                    </span>

                                    <span class="chip">
                                        {{
                                            $ejecucion
                                                ->total_encontradas
                                            ?? 0
                                        }}
                                        encontradas
                                    </span>

                                </div>

                            </div>


                            <span
                                class="
                                    badge
                                    {{ $badge }}
                                "
                            >
                                {{ $estadoTexto }}
                            </span>

                        </a>


                    @empty

                        <div class="empty-state">

                            <div class="empty-title">
                                Sin ejecuciones registradas
                            </div>

                            El motor todavía no ha
                            registrado ningún ciclo
                            de extracción.

                        </div>

                    @endforelse

                </section>


                <section class="card">

                    <div class="card-header">

                        <div>

                            <div class="card-title">
                                Estado de fuentes
                                en la última ejecución
                            </div>

                            <div class="card-description">
                                Resultado individual
                                de cada fuente procesada.
                            </div>

                        </div>

                    </div>


                    @if (
                        $ultimaEjecucion
                        && $ultimaEjecucion
                            ->ejecucionesFuentes
                            ->isNotEmpty()
                    )

                        @foreach (
                            $ultimaEjecucion
                                ->ejecucionesFuentes
                            as $detalle
                        )

                            @php
                                $estadoFuente =
                                    strtoupper(
                                        (string)
                                        $detalle->estado
                                    );

                                $claseFuente =
                                    match (
                                        $estadoFuente
                                    ) {
                                        'COMPLETADO' =>
                                            'success-text',

                                        'ERROR',
                                        'FALLIDO' =>
                                            'error-text',

                                        default =>
                                            'warning-text',
                                    };
                            @endphp


                            <a
                                class="
                                    source-row
                                    source-link
                                "
                                href="{{
                                    route(
                                        'filament.admin.pages.gestionar-fuente',
                                        [
                                            'record' =>
                                                $detalle
                                                    ->fuente_id,
                                        ]
                                    )
                                }}"
                            >

                                <div>

                                    <div class="source-name">
                                        {{
                                            $detalle
                                                ->fuente
                                                ?->nombre
                                            ?? 'Fuente #'.
                                                $detalle
                                                    ->fuente_id
                                        }}
                                    </div>

                                    <div class="source-info">

                                        {{
                                            $detalle
                                                ->registros_encontrados
                                            ?? 0
                                        }}

                                        registros identificados

                                    </div>

                                </div>


                                <div
                                    class="
                                        mini-status
                                        {{ $claseFuente }}
                                    "
                                >
                                    {{
                                        str_replace(
                                            '_',
                                            ' ',
                                            $estadoFuente
                                        )
                                    }}
                                </div>


                                <div class="count">

                                    {{
                                        $detalle
                                            ->duracion_segundos
                                        !== null
                                            ? number_format(
                                                (float)
                                                $detalle
                                                    ->duracion_segundos,
                                                1
                                            ).' s'
                                            : '—'
                                    }}

                                </div>

                            </a>

                        @endforeach


                    @else

                        <div class="empty-state">

                            <div class="empty-title">
                                Sin resultados por fuente
                            </div>

                            Aún no existe una ejecución
                            registrada que pueda mostrar
                            resultados individuales.

                        </div>

                    @endif

                </section>

            </div>


            <div class="column">


                <section class="card">

                    <div class="card-header">

                        <div class="card-title">
                            Estado actual
                        </div>

                    </div>


                    <div class="side-content">

                        @if ($ejecucionActiva)

                            <div
                                class="
                                    engine-box
                                    running
                                "
                            >

                                <div class="engine-box-title">
                                    Ejecución en curso
                                </div>

                                <div class="engine-box-text">
                                    Existe una ejecución
                                    registrada con estado
                                    {{
                                        $ejecucionActiva
                                            ->estado
                                    }}.
                                </div>

                            </div>

                        @elseif ($ultimaEjecucion)

                            <div class="engine-box">

                                <div class="engine-box-title">
                                    Sin ejecución activa
                                </div>

                                <div class="engine-box-text">
                                    El último ciclo registrado
                                    terminó con estado
                                    {{
                                        str_replace(
                                            '_',
                                            ' ',
                                            $ultimaEjecucion
                                                ->estado
                                        )
                                    }}.
                                </div>

                            </div>

                        @else

                            <div
                                class="
                                    engine-box
                                    neutral
                                "
                            >

                                <div class="engine-box-title">
                                    Sin ejecuciones
                                </div>

                                <div class="engine-box-text">
                                    PostgreSQL no contiene
                                    todavía ciclos registrados
                                    por el extractor.
                                </div>

                            </div>

                        @endif


                        <div style="margin-top:15px;">

                            <div class="info-row">

                                <span>
                                    Último ciclo
                                </span>

                                <strong>
                                    {{
                                        $ultimaEjecucion
                                            ?->fecha_inicio
                                            ?->format(
                                                'd/m/Y H:i'
                                            )
                                        ?? 'Sin registro'
                                    }}
                                </strong>

                            </div>


                            <div class="info-row">

                                <span>
                                    Total fuentes
                                </span>

                                <strong>
                                    {{
                                        $ultimaEjecucion
                                            ?->total_fuentes
                                        ?? 0
                                    }}
                                </strong>

                            </div>


                            <div class="info-row">

                                <span>
                                    Exitosas
                                </span>

                                <strong
                                    style="color:#059669;"
                                >
                                    {{
                                        $ultimaEjecucion
                                            ?->fuentes_exitosas
                                        ?? 0
                                    }}
                                </strong>

                            </div>


                            <div class="info-row">

                                <span>
                                    Fallidas
                                </span>

                                <strong
                                    style="color:#DC2626;"
                                >
                                    {{
                                        $ultimaEjecucion
                                            ?->fuentes_fallidas
                                        ?? 0
                                    }}
                                </strong>

                            </div>


                            <div class="info-row">

                                <span>
                                    Errores
                                </span>

                                <strong>
                                    {{
                                        $ultimaEjecucion
                                            ?->total_errores
                                        ?? 0
                                    }}
                                </strong>

                            </div>

                        </div>


                        @if ($ultimaEjecucion)

                            <div class="progress-wrap">

                                <div class="progress-header">

                                    <span>
                                        Fuentes procesadas
                                        correctamente
                                    </span>

                                    <strong>
                                        {{
                                            number_format(
                                                $porcentajeExito,
                                                1
                                            )
                                        }}%
                                    </strong>

                                </div>

                                <div class="progress">

                                    <div
                                        class="progress-value"
                                        style="
                                            width:{{
                                                $porcentajeExito
                                            }}%;
                                        "
                                    ></div>

                                </div>

                            </div>

                        @endif

                    </div>

                </section>


                <section class="card">

                    <div class="card-header">

                        <div>

                            <div class="card-title">
                                Alertas recientes
                            </div>

                            <div class="card-description">
                                Errores pendientes
                                registrados por el sistema.
                            </div>

                        </div>

                    </div>


                    <div
                        class="side-content"
                        style="padding-top:5px;"
                    >

                        @forelse (
                            $alertas
                            as $alerta
                        )

                            <a
                                class="
                                    alert
                                    alert-link
                                "
                                href="{{
                                    route(
                                        'filament.admin.pages.detalle-error',
                                        [
                                            'record' =>
                                                $alerta->id,
                                        ]
                                    )
                                }}"
                            >

                                <div class="alert-icon">
                                    !
                                </div>

                                <div>

                                    <div class="alert-title">
                                        {{
                                            $alerta
                                                ->tipo_error
                                            ?? 'Error'
                                        }}
                                    </div>

                                    <div
                                        class="
                                            alert-description
                                        "
                                    >
                                        {{
                                            $alerta->mensaje
                                            ?: 'Sin descripción.'
                                        }}

                                        @if (
                                            $alerta->fuente
                                        )

                                            <br>

                                            Fuente:
                                            {{
                                                $alerta
                                                    ->fuente
                                                    ->nombre
                                            }}

                                        @endif
                                    </div>

                                </div>

                            </a>


                        @empty

                            <div class="empty-state">

                                <div class="empty-title">
                                    Sin alertas pendientes
                                </div>

                                No existen errores
                                sin resolver.

                            </div>

                        @endforelse

                    </div>

                </section>


                <section class="card">

                    <div class="card-header">

                        <div>

                            <div class="card-title">
                                Integración
                            </div>

                            <div class="card-description">
                                Estado de comunicación
                                entre la plataforma
                                y el extractor.
                            </div>

                        </div>

                    </div>


                    <div class="side-content">

                        <div class="info-row">

                            <span>
                                Base de datos
                            </span>

                            <strong
                                style="color:#059669;"
                            >
                                Conectada
                            </strong>

                        </div>


                        <div class="info-row">

                            <span>
                                Fuentes activas
                            </span>

                            <strong>
                                {{ $fuentesActivas }}
                            </strong>

                        </div>


                        <div class="info-row">

                            <span>
                                Monitor
                            </span>

                            <strong
                                style="color:#059669;"
                            >
                                PostgreSQL
                            </strong>

                        </div>


                        <div class="info-row">

                            <span>
                                Laravel → Python
                            </span>

                            <strong
                                style="color:#D97706;"
                            >
                                Pendiente
                            </strong>

                        </div>

                    </div>


                    <div class="visual-note">
                        El extractor Python existe,
                        pero la ejecución remota desde
                        Laravel se conectará durante
                        la Fase 3. Esta pantalla no
                        crea ejecuciones ficticias.
                    </div>

                </section>

            </div>

        </div>

    </div>

</x-filament-panels::page>
