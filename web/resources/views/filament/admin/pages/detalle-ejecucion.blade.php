<x-filament-panels::page>
    <style>
        .execution-detail {
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

        .execution-detail * {
            box-sizing: border-box;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 18px;
            color: var(--primary);
            text-decoration: none;
            font-size: 12px;
            font-weight: 850;
        }

        .hero {
            padding: 24px;
            margin-bottom: 20px;
            border: 1px solid var(--border);
            border-radius: 16px;
            background: white;
        }

        .hero-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
        }

        .eyebrow {
            color: var(--primary);
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .title {
            margin-top: 7px;
            font-size: 24px;
            font-weight: 900;
        }

        .subtitle {
            margin-top: 6px;
            color: var(--muted);
            font-size: 11px;
        }

        .status {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 11px;
            border-radius: 999px;
            background: #FEF3C7;
            color: #92400E;
            font-size: 10px;
            font-weight: 850;
            white-space: nowrap;
        }

        .status-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--warning);
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 11px;
            margin-top: 20px;
        }

        .summary-item {
            padding: 12px;
            border: 1px solid #E7ECF3;
            border-radius: 10px;
            background: #F8FAFC;
        }

        .summary-label {
            display: block;
            margin-bottom: 4px;
            color: #94A3B8;
            font-size: 9px;
            text-transform: uppercase;
        }

        .summary-value {
            color: #334155;
            font-size: 12px;
            font-weight: 850;
        }

        .layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 300px;
            gap: 22px;
            align-items: start;
        }

        .main,
        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .sidebar {
            position: sticky;
            top: 90px;
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
            align-items: flex-start;
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
            line-height: 1.5;
        }

        .count-badge {
            padding: 5px 8px;
            border-radius: 999px;
            background: var(--primary-soft);
            color: var(--primary-dark);
            font-size: 9px;
            font-weight: 850;
            white-space: nowrap;
        }

        .timeline {
            padding: 19px 20px;
        }

        .timeline-item {
            display: grid;
            grid-template-columns: 32px minmax(0, 1fr) auto;
            gap: 12px;
            position: relative;
            padding-bottom: 22px;
        }

        .timeline-item:last-child {
            padding-bottom: 0;
        }

        .timeline-item:not(:last-child)::after {
            content: "";
            position: absolute;
            left: 15px;
            top: 32px;
            bottom: 0;
            width: 2px;
            background: #E2E8F0;
        }

        .timeline-icon {
            width: 32px;
            height: 32px;
            z-index: 1;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-soft);
            color: var(--primary-dark);
            font-size: 9px;
            font-weight: 900;
        }

        .timeline-icon.warning {
            background: #FEF3C7;
            color: #B45309;
        }

        .timeline-title {
            color: #334155;
            font-size: 10px;
            font-weight: 850;
        }

        .timeline-text {
            margin-top: 3px;
            color: #94A3B8;
            font-size: 9px;
            line-height: 1.45;
        }

        .timeline-time {
            color: #94A3B8;
            font-size: 8px;
            white-space: nowrap;
        }

        .source-table {
            width: 100%;
            border-collapse: collapse;
        }

        .source-table th {
            padding: 11px 13px;
            border-bottom: 1px solid var(--border);
            background: #F8FAFC;
            color: #64748B;
            text-align: left;
            font-size: 9px;
            font-weight: 850;
        }

        .source-table td {
            padding: 13px;
            border-bottom: 1px solid #EEF2F7;
            color: #475569;
            font-size: 9px;
        }

        .source-table tr:last-child td {
            border-bottom: 0;
        }

        .source-name {
            color: #334155;
            font-weight: 850;
        }

        .result-badge {
            display: inline-flex;
            padding: 5px 8px;
            border-radius: 999px;
            font-size: 8px;
            font-weight: 850;
        }

        .result-success {
            background: #D1FAE5;
            color: #047857;
        }

        .result-error {
            background: #FEE2E2;
            color: #B91C1C;
        }

        .documents {
            padding: 5px 20px;
        }

        .document {
            display: grid;
            grid-template-columns: 38px minmax(0, 1fr) auto;
            gap: 11px;
            align-items: center;
            padding: 13px 0;
            border-bottom: 1px solid #EEF2F7;
        }

        .document:last-child {
            border-bottom: 0;
        }

        .document-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #FEE2E2;
            color: #B91C1C;
            font-size: 9px;
            font-weight: 900;
        }

        .document-name {
            color: #334155;
            font-size: 10px;
            font-weight: 800;
        }

        .document-meta {
            margin-top: 3px;
            color: #94A3B8;
            font-size: 8px;
        }

        .document-state {
            color: var(--primary);
            font-size: 9px;
            font-weight: 850;
        }

        .document-state.error {
            color: var(--danger);
        }

        .error-list {
            padding: 5px 20px;
        }

        .error {
            display: flex;
            gap: 11px;
            padding: 14px 0;
            border-bottom: 1px solid #EEF2F7;
        }

        .error:last-child {
            border-bottom: 0;
        }

        .error-icon {
            width: 30px;
            height: 30px;
            flex: 0 0 30px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #FEE2E2;
            color: #B91C1C;
            font-size: 10px;
            font-weight: 900;
        }

        .error-title {
            color: #334155;
            font-size: 10px;
            font-weight: 850;
        }

        .error-description {
            margin-top: 3px;
            color: #94A3B8;
            font-size: 9px;
            line-height: 1.5;
        }

        .error-code {
            display: inline-flex;
            margin-top: 7px;
            padding: 4px 7px;
            border-radius: 6px;
            background: #F1F5F9;
            color: #475569;
            font-family: monospace;
            font-size: 8px;
        }

        .side-content {
            padding: 18px;
        }

        .state-box {
            padding: 15px;
            border: 1px solid #FDE68A;
            border-radius: 11px;
            background: #FFFBEB;
        }

        .state-title {
            color: #92400E;
            font-size: 11px;
            font-weight: 850;
        }

        .state-text {
            margin-top: 5px;
            color: #B45309;
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

        .progress-section {
            margin-top: 14px;
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
            border-radius: 999px;
            overflow: hidden;
            background: #E2E8F0;
        }

        .progress-value {
            height: 100%;
            border-radius: inherit;
            background: var(--primary);
        }

        .action-list {
            display: flex;
            flex-direction: column;
            gap: 9px;
        }

        .action-btn {
            width: 100%;
            min-height: 39px;
            border: 1px solid var(--border);
            border-radius: 9px;
            background: white;
            color: #475569;
            font-size: 10px;
            font-weight: 850;
            cursor: pointer;
        }

        .action-btn.primary {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }

        .visual-note {
            padding: 12px;
            border: 1px solid #A7F3D0;
            border-radius: 10px;
            background: #ECFDF5;
            color: #047857;
            text-align: center;
            font-size: 9px;
            line-height: 1.5;
        }

        @media (max-width: 1050px) {
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
            .hero-top {
                flex-direction: column;
            }

            .summary-grid {
                grid-template-columns: 1fr;
            }

            .timeline-item {
                grid-template-columns: 32px minmax(0, 1fr);
            }

            .timeline-time {
                grid-column: 2;
            }

            .document {
                grid-template-columns: 38px minmax(0, 1fr);
            }

            .document-state {
                grid-column: 2;
            }
        }
    
        .status.success {
            background: #D1FAE5;
            color: #047857;
        }

        .status.running {
            background: #DBEAFE;
            color: #1D4ED8;
        }

        .status.error {
            background: #FEE2E2;
            color: #B91C1C;
        }

        .status.neutral {
            background: #F1F5F9;
            color: #64748B;
        }

        .state-box.success {
            border-color: #A7F3D0;
            background: #ECFDF5;
        }

        .state-box.success .state-title,
        .state-box.success .state-text {
            color: #047857;
        }

        .state-box.running {
            border-color: #BFDBFE;
            background: #EFF6FF;
        }

        .state-box.running .state-title,
        .state-box.running .state-text {
            color: #1D4ED8;
        }

        .state-box.error {
            border-color: #FECACA;
            background: #FEF2F2;
        }

        .state-box.error .state-title,
        .state-box.error .state-text {
            color: #B91C1C;
        }

        .source-link,
        .error-link {
            color: inherit;
            text-decoration: none;
        }

        .source-link:hover {
            color: var(--primary);
            text-decoration: underline;
        }

        .error-link {
            display: flex;
            gap: 11px;
            padding: 13px 0;
            border-bottom: 1px solid #EEF2F7;
        }

        .error-link:last-child {
            border-bottom: 0;
        }

        .action-link {
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .empty-state {
            padding: 28px 18px;
            color: #94A3B8;
            text-align: center;
            font-size: 10px;
            line-height: 1.6;
        }

        .empty-title {
            margin-bottom: 5px;
            color: #475569;
            font-size: 11px;
            font-weight: 850;
        }

        .api-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 680px;
        }

        .api-table th,
        .api-table td {
            padding: 11px 13px;
            border-bottom: 1px solid #EEF2F7;
            text-align: left;
            font-size: 9px;
        }

        .api-table th {
            color: #64748B;
            font-weight: 850;
            background: #F8FAFC;
        }

        .api-table td {
            color: #475569;
        }

        .api-endpoint {
            max-width: 260px;
            overflow-wrap: anywhere;
        }

        .timeline-icon.api {
            background: #DBEAFE;
            color: #1D4ED8;
        }

        .timeline-icon.error {
            background: #FEE2E2;
            color: #B91C1C;
        }

</style>

    <div class="execution-detail">

        @php
            $estado =
                strtoupper(
                    (string)
                    $ejecucion->estado
                );

            [$estadoClase, $estadoTexto] =
                match ($estado) {
                    'COMPLETADO' => [
                        'success',
                        'Completada',
                    ],

                    'COMPLETADO_CON_ERRORES' => [
                        '',
                        'Completada con errores',
                    ],

                    'INICIADO' => [
                        'running',
                        'Iniciada',
                    ],

                    'EJECUTANDO' => [
                        'running',
                        'En ejecución',
                    ],

                    'FALLIDO' => [
                        'error',
                        'Fallida',
                    ],

                    default => [
                        'neutral',
                        $estado !== ''
                            ? str_replace(
                                '_',
                                ' ',
                                $estado
                            )
                            : 'Sin estado',
                    ],
                };

            $estadoCaja =
                match ($estado) {
                    'COMPLETADO' =>
                        'success',

                    'INICIADO',
                    'EJECUTANDO' =>
                        'running',

                    'FALLIDO' =>
                        'error',

                    default =>
                        '',
                };
        @endphp


        <a
            href="{{
                route(
                    'filament.admin.pages.motor-extraccion'
                )
            }}"
            class="back-link"
        >
            ← Volver al Motor de Extracción
        </a>


        <section class="hero">

            <div class="hero-top">

                <div>

                    <div class="eyebrow">
                        Detalle de ejecución
                    </div>

                    <div class="title">
                        Ejecución #{{ $ejecucion->id }}
                    </div>

                    <div class="subtitle">

                        Ciclo de extracción

                        @if ($ejecucion->fecha_inicio)

                            ·

                            {{
                                $ejecucion
                                    ->fecha_inicio
                                    ->format(
                                        'd/m/Y H:i'
                                    )
                            }}

                        @endif

                    </div>

                </div>


                <div
                    class="
                        status
                        {{ $estadoClase }}
                    "
                >
                    <span class="status-dot"></span>

                    {{ $estadoTexto }}
                </div>

            </div>


            <div class="summary-grid">

                <div class="summary-item">

                    <span class="summary-label">
                        Fuentes
                    </span>

                    <span class="summary-value">
                        {{
                            $ejecucion
                                ->total_fuentes
                            ?? 0
                        }}
                    </span>

                </div>


                <div class="summary-item">

                    <span class="summary-label">
                        Exitosas
                    </span>

                    <span
                        class="summary-value"
                        style="color:#059669;"
                    >
                        {{
                            $ejecucion
                                ->fuentes_exitosas
                            ?? 0
                        }}
                    </span>

                </div>


                <div class="summary-item">

                    <span class="summary-label">
                        Fallidas
                    </span>

                    <span
                        class="summary-value"
                        style="color:#DC2626;"
                    >
                        {{
                            $ejecucion
                                ->fuentes_fallidas
                            ?? 0
                        }}
                    </span>

                </div>


                <div class="summary-item">

                    <span class="summary-label">
                        Convocatorias
                    </span>

                    <span class="summary-value">
                        {{
                            $ejecucion
                                ->total_encontradas
                            ?? 0
                        }}
                    </span>

                </div>


                <div class="summary-item">

                    <span class="summary-label">
                        Errores
                    </span>

                    <span class="summary-value">
                        {{
                            $ejecucion
                                ->total_errores
                            ?? 0
                        }}
                    </span>

                </div>

            </div>

        </section>


        <div class="layout">

            <main class="main">


                <section class="card">

                    <div class="card-header">

                        <div>

                            <div class="card-title">
                                Línea de ejecución
                            </div>

                            <div class="card-description">
                                Eventos realmente registrados
                                durante el ciclo.
                            </div>

                        </div>

                    </div>


                    @if (count($lineaTiempo))

                        <div class="timeline">

                            @foreach (
                                $lineaTiempo
                                as $evento
                            )

                                <div class="timeline-item">

                                    <div
                                        class="
                                            timeline-icon
                                            {{
                                                $evento['tipo']
                                                === 'error'
                                                    ? 'error'
                                                    : (
                                                        $evento['tipo']
                                                        === 'api'
                                                            ? 'api'
                                                            : ''
                                                    )
                                            }}
                                        "
                                    >
                                        {{
                                            $evento['tipo']
                                            === 'error'
                                                ? '!'
                                                : (
                                                    $evento['tipo']
                                                    === 'api'
                                                        ? 'API'
                                                        : '✓'
                                                )
                                        }}
                                    </div>

                                    <div>

                                        <div class="timeline-title">
                                            {{
                                                $evento[
                                                    'titulo'
                                                ]
                                            }}
                                        </div>

                                        <div class="timeline-text">
                                            {{
                                                $evento[
                                                    'descripcion'
                                                ]
                                            }}
                                        </div>

                                    </div>

                                    <div class="timeline-time">

                                        {{
                                            $evento['fecha']
                                                ->format(
                                                    'H:i:s'
                                                )
                                        }}

                                    </div>

                                </div>

                            @endforeach

                        </div>

                    @else

                        <div class="empty-state">

                            <div class="empty-title">
                                Sin eventos registrados
                            </div>

                            La ejecución no contiene
                            marcas temporales suficientes.

                        </div>

                    @endif

                </section>


                <section class="card">

                    <div class="card-header">

                        <div>

                            <div class="card-title">
                                Fuentes procesadas
                            </div>

                            <div class="card-description">
                                Resultado individual
                                registrado para cada fuente.
                            </div>

                        </div>

                        <span class="count-badge">
                            {{ $detalleFuentes->count() }}
                            fuentes
                        </span>

                    </div>


                    @if ($detalleFuentes->isNotEmpty())

                        <div style="overflow-x:auto;">

                            <table class="source-table">

                                <thead>

                                    <tr>
                                        <th>Fuente</th>
                                        <th>Estado</th>
                                        <th>Detectadas</th>
                                        <th>Nuevas</th>
                                        <th>Actualizadas</th>
                                        <th>Duplicados</th>
                                        <th>Errores</th>
                                        <th>HTTP</th>
                                        <th>Duración</th>
                                    </tr>

                                </thead>

                                <tbody>

                                    @foreach (
                                        $detalleFuentes
                                        as $detalle
                                    )

                                        @php
                                            $estadoFuente =
                                                strtoupper(
                                                    (string)
                                                    $detalle
                                                        ->estado
                                                );

                                            $clase =
                                                match (
                                                    $estadoFuente
                                                ) {
                                                    'COMPLETADO' =>
                                                        'result-success',

                                                    'ERROR',
                                                    'FALLIDO' =>
                                                        'result-error',

                                                    default =>
                                                        '',
                                                };
                                        @endphp

                                        <tr>

                                            <td
                                                class="
                                                    source-name
                                                "
                                            >

                                                @if (
                                                    $detalle
                                                        ->fuente
                                                )

                                                    <a
                                                        class="
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
                                                        {{
                                                            $detalle
                                                                ->fuente
                                                                ->nombre
                                                        }}
                                                    </a>

                                                @else

                                                    Fuente
                                                    #{{
                                                        $detalle
                                                            ->fuente_id
                                                    }}

                                                @endif

                                            </td>

                                            <td>

                                                <span
                                                    class="
                                                        result-badge
                                                        {{ $clase }}
                                                    "
                                                >
                                                    {{
                                                        str_replace(
                                                            '_',
                                                            ' ',
                                                            $estadoFuente
                                                        )
                                                    }}
                                                </span>

                                            </td>

                                            <td>
                                                {{
                                                    $detalle
                                                        ->registros_encontrados
                                                    ?? 0
                                                }}
                                            </td>

                                            <td>
                                                {{
                                                    $detalle
                                                        ->registros_nuevos
                                                    ?? 0
                                                }}
                                            </td>

                                            <td>
                                                {{
                                                    $detalle
                                                        ->registros_actualizados
                                                    ?? 0
                                                }}
                                            </td>

                                            <td>
                                                {{
                                                    $detalle
                                                        ->duplicados
                                                    ?? 0
                                                }}
                                            </td>

                                            <td>
                                                {{
                                                    $detalle
                                                        ->errores
                                                    ?? 0
                                                }}
                                            </td>

                                            <td>
                                                {{
                                                    $detalle
                                                        ->http_status
                                                    ?? '—'
                                                }}
                                            </td>

                                            <td>

                                                {{
                                                    $detalle
                                                        ->duracion_segundos
                                                    !== null
                                                        ? number_format(
                                                            (float)
                                                            $detalle
                                                                ->duracion_segundos,
                                                            2
                                                        ).' s'
                                                        : '—'
                                                }}

                                            </td>

                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                    @else

                        <div class="empty-state">

                            <div class="empty-title">
                                Sin resultados por fuente
                            </div>

                            No existen registros en
                            ejecución_fuentes para este ciclo.

                        </div>

                    @endif

                </section>


                <section class="card">

                    <div class="card-header">

                        <div>

                            <div class="card-title">
                                Documentos procesados
                            </div>

                            <div class="card-description">
                                Procesamientos de documentos
                                asociados a las fuentes del ciclo.
                            </div>

                        </div>

                        <span class="count-badge">
                            {{ $documentos->count() }}
                            documentos
                        </span>

                    </div>


                    @forelse (
                        $documentos
                        as $documento
                    )

                        <div class="document">

                            <div class="document-icon">
                                PDF
                            </div>

                            <div>

                                <div class="document-name">

                                    Documento
                                    #{{
                                        $documento
                                            ->convocatoria_archivo_id
                                        ?? $documento->id
                                    }}

                                </div>

                                <div class="document-meta">

                                    {{
                                        $documento
                                            ->ejecucionFuente
                                            ?->fuente
                                            ?->nombre
                                        ?? 'Fuente sin identificar'
                                    }}

                                    ·

                                    {{
                                        $documento
                                            ->motor_extraccion
                                        ?? 'Motor no registrado'
                                    }}

                                    @if (
                                        $documento->paginas
                                        !== null
                                    )

                                        ·
                                        {{
                                            $documento
                                                ->paginas
                                        }}
                                        pág.

                                    @endif

                                    @if (
                                        $documento
                                            ->requiere_ocr
                                    )

                                        · OCR

                                    @endif

                                </div>

                            </div>

                            <div
                                class="
                                    document-state
                                    {{
                                        strtoupper(
                                            (string)
                                            $documento->estado
                                        )
                                        === 'ERROR'
                                            ? 'error'
                                            : ''
                                    }}
                                "
                            >
                                {{
                                    $documento->estado
                                    ?? 'Sin estado'
                                }}
                            </div>

                        </div>


                    @empty

                        <div class="empty-state">

                            <div class="empty-title">
                                Sin documentos procesados
                            </div>

                            No hay registros de procesamiento
                            documental para esta ejecución.

                        </div>

                    @endforelse

                </section>


                <section class="card">

                    <div class="card-header">

                        <div>

                            <div class="card-title">
                                Eventos API
                            </div>

                            <div class="card-description">
                                Comunicaciones registradas
                                durante esta ejecución.
                            </div>

                        </div>

                        <span class="count-badge">
                            {{ $eventosApi->count() }}
                            eventos
                        </span>

                    </div>


                    @if ($eventosApi->isNotEmpty())

                        <div style="overflow-x:auto;">

                            <table class="api-table">

                                <thead>
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Método</th>
                                        <th>Endpoint</th>
                                        <th>Respuesta</th>
                                        <th>Estado</th>
                                        <th>Intentos</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    @foreach (
                                        $eventosApi
                                        as $eventoApi
                                    )

                                        <tr>

                                            <td>
                                                {{
                                                    $eventoApi
                                                        ->tipo_evento
                                                }}
                                            </td>

                                            <td>
                                                {{
                                                    $eventoApi
                                                        ->metodo_http
                                                }}
                                            </td>

                                            <td
                                                class="
                                                    api-endpoint
                                                "
                                            >
                                                {{
                                                    $eventoApi
                                                        ->endpoint
                                                }}
                                            </td>

                                            <td>
                                                {{
                                                    $eventoApi
                                                        ->codigo_respuesta
                                                    ?? '—'
                                                }}
                                            </td>

                                            <td>
                                                {{
                                                    $eventoApi
                                                        ->estado
                                                }}
                                            </td>

                                            <td>
                                                {{
                                                    $eventoApi
                                                        ->intentos
                                                    ?? 0
                                                }}
                                            </td>

                                            <td>
                                                {{
                                                    $eventoApi
                                                        ->fecha_envio
                                                        ?->format(
                                                            'd/m/Y H:i:s'
                                                        )
                                                    ?? '—'
                                                }}
                                            </td>

                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                    @else

                        <div class="empty-state">

                            <div class="empty-title">
                                Sin eventos API
                            </div>

                            No hay comunicaciones API
                            asociadas a esta ejecución.

                        </div>

                    @endif

                </section>


                <section class="card">

                    <div class="card-header">

                        <div>

                            <div class="card-title">
                                Errores registrados
                            </div>

                            <div class="card-description">
                                Incidencias asociadas
                                directamente a esta ejecución.
                            </div>

                        </div>

                        <span
                            class="count-badge"
                            style="
                                background:#FEE2E2;
                                color:#B91C1C;
                            "
                        >
                            {{ $errores->count() }}
                            errores
                        </span>

                    </div>


                    <div class="error-list">

                        @forelse (
                            $errores
                            as $error
                        )

                            <a
                                class="error-link"
                                href="{{
                                    route(
                                        'filament.admin.pages.detalle-error',
                                        [
                                            'record' =>
                                                $error->id,
                                        ]
                                    )
                                }}"
                            >

                                <div class="error-icon">
                                    !
                                </div>

                                <div>

                                    <div class="error-title">

                                        {{
                                            $error->tipo_error
                                            ?: 'Error registrado'
                                        }}

                                    </div>

                                    <div
                                        class="
                                            error-description
                                        "
                                    >
                                        {{
                                            $error->mensaje
                                        }}

                                        @if (
                                            $error->fuente
                                        )

                                            <br>

                                            Fuente:
                                            {{
                                                $error
                                                    ->fuente
                                                    ->nombre
                                            }}

                                        @endif
                                    </div>

                                    @if (
                                        $error
                                            ->codigo_error
                                    )

                                        <span
                                            class="
                                                error-code
                                            "
                                        >
                                            {{
                                                $error
                                                    ->codigo_error
                                            }}
                                        </span>

                                    @endif

                                </div>

                            </a>


                        @empty

                            <div class="empty-state">

                                <div class="empty-title">
                                    Sin errores registrados
                                </div>

                                Esta ejecución no tiene
                                incidencias asociadas.

                            </div>

                        @endforelse

                    </div>

                </section>

            </main>


            <aside class="sidebar">


                <div
                    class="
                        state-box
                        {{ $estadoCaja }}
                    "
                >

                    <div class="state-title">
                        {{ $estadoTexto }}
                    </div>

                    <div class="state-text">

                        @switch($estado)

                            @case('COMPLETADO')

                                La ejecución finalizó
                                correctamente.

                                @break

                            @case('COMPLETADO_CON_ERRORES')

                                El ciclo terminó, pero
                                registró una o más incidencias.

                                @break

                            @case('FALLIDO')

                                El ciclo no pudo finalizar
                                correctamente.

                                @break

                            @case('INICIADO')
                            @case('EJECUTANDO')

                                El sistema mantiene esta
                                ejecución como activa.

                                @break

                            @default

                                Estado registrado:
                                {{ $ejecucion->estado }}.

                        @endswitch

                    </div>

                </div>


                <section class="card">

                    <div class="card-header">

                        <div class="card-title">
                            Información de ejecución
                        </div>

                    </div>


                    <div class="side-content">

                        <div class="info-row">

                            <span>ID</span>

                            <strong>
                                #{{ $ejecucion->id }}
                            </strong>

                        </div>


                        <div class="info-row">

                            <span>Inicio</span>

                            <strong>
                                {{
                                    $ejecucion
                                        ->fecha_inicio
                                        ?->format(
                                            'd/m/Y H:i:s'
                                        )
                                    ?? '—'
                                }}
                            </strong>

                        </div>


                        <div class="info-row">

                            <span>Fin</span>

                            <strong>
                                {{
                                    $ejecucion
                                        ->fecha_fin
                                        ?->format(
                                            'd/m/Y H:i:s'
                                        )
                                    ?? 'En curso'
                                }}
                            </strong>

                        </div>


                        <div class="info-row">

                            <span>Duración</span>

                            <strong>
                                {{
                                    $ejecucion
                                        ->duracion_segundos
                                    !== null
                                        ? number_format(
                                            (float)
                                            $ejecucion
                                                ->duracion_segundos,
                                            2
                                        ).' s'
                                        : '—'
                                }}
                            </strong>

                        </div>


                        <div class="info-row">

                            <span>Estado</span>

                            <strong>
                                {{ $estadoTexto }}
                            </strong>

                        </div>


                        <div class="progress-section">

                            <div class="progress-header">

                                <span>
                                    Fuentes exitosas
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

                    </div>

                </section>


                <section class="card">

                    <div class="card-header">

                        <div class="card-title">
                            Resumen
                        </div>

                    </div>


                    <div class="side-content">

                        <div class="info-row">
                            <span>Fuentes</span>
                            <strong>
                                {{
                                    $ejecucion
                                        ->total_fuentes
                                    ?? 0
                                }}
                            </strong>
                        </div>

                        <div class="info-row">
                            <span>Exitosas</span>
                            <strong
                                style="color:#059669;"
                            >
                                {{
                                    $ejecucion
                                        ->fuentes_exitosas
                                    ?? 0
                                }}
                            </strong>
                        </div>

                        <div class="info-row">
                            <span>Fallidas</span>
                            <strong
                                style="color:#DC2626;"
                            >
                                {{
                                    $ejecucion
                                        ->fuentes_fallidas
                                    ?? 0
                                }}
                            </strong>
                        </div>

                        <div class="info-row">
                            <span>Encontradas</span>
                            <strong>
                                {{
                                    $ejecucion
                                        ->total_encontradas
                                    ?? 0
                                }}
                            </strong>
                        </div>

                        <div class="info-row">
                            <span>Nuevas</span>
                            <strong>
                                {{
                                    $ejecucion
                                        ->nuevas_convocatorias
                                    ?? 0
                                }}
                            </strong>
                        </div>

                        <div class="info-row">
                            <span>Actualizadas</span>
                            <strong>
                                {{
                                    $ejecucion
                                        ->convocatorias_actualizadas
                                    ?? 0
                                }}
                            </strong>
                        </div>

                        <div class="info-row">
                            <span>Duplicados</span>
                            <strong>
                                {{
                                    $ejecucion
                                        ->duplicados_detectados
                                    ?? 0
                                }}
                            </strong>
                        </div>

                        <div class="info-row">
                            <span>Documentos</span>
                            <strong>
                                {{ $documentos->count() }}
                            </strong>
                        </div>

                        <div class="info-row">
                            <span>Errores</span>
                            <strong>
                                {{
                                    $ejecucion
                                        ->total_errores
                                    ?? 0
                                }}
                            </strong>
                        </div>

                    </div>

                </section>


                <section class="card">

                    <div class="card-header">

                        <div class="card-title">
                            Acciones
                        </div>

                    </div>


                    <div class="side-content">

                        <div class="action-list">

                            <a
                                href="{{
                                    route(
                                        'filament.admin.pages.bitacora-errores',
                                        [
                                            'ejecucion' =>
                                                $ejecucion->id,
                                        ]
                                    )
                                }}"
                                class="
                                    action-btn
                                    primary
                                    action-link
                                "
                            >
                                Ver bitácora relacionada
                            </a>


                            <button
                                type="button"
                                class="action-btn"
                                wire:click="
                                    descargarResumen
                                "
                            >
                                Descargar resumen JSON
                            </button>


                            <a
                                href="{{
                                    route(
                                        'filament.admin.pages.motor-extraccion'
                                    )
                                }}"
                                class="
                                    action-btn
                                    action-link
                                "
                            >
                                Volver al motor
                            </a>

                        </div>

                    </div>

                </section>


                <div class="visual-note">
                    Todos los valores mostrados
                    corresponden a registros reales
                    de PostgreSQL.
                </div>

            </aside>

        </div>

    </div>

</x-filament-panels::page>
