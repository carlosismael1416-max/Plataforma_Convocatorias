<x-filament-panels::page>
    <style>
        .directivo-stats {
            --text: #0F1827;
            --muted: #64748B;
            --primary: #7C3AED;
            --primary-dark: #6D28D9;
            --primary-soft: #F3E8FF;
            --border: #DDE3EE;
            --success: #059669;
            --warning: #D97706;
            --danger: #DC2626;
            --blue: #2563EB;

            color: var(--text);
        }

        .directivo-stats * {
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
            line-height: 1.2;
        }

        .page-subtitle {
            margin-top: 7px;
            color: var(--muted);
            font-size: 14px;
        }

        .filters {
            display: flex;
            gap: 9px;
            flex-wrap: wrap;
        }

        .select {
            height: 40px;
            min-width: 150px;
            padding: 0 11px;
            border: 1px solid var(--border);
            border-radius: 9px;
            background: white;
            color: #475569;
            font-size: 11px;
            outline: none;
        }

        .select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, .10);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 22px;
        }

        .stat-card {
            display: block;
            background: white;
            border: 1px solid var(--border);
            border-radius: 15px;
            padding: 19px;
            box-shadow: 0 1px 2px rgba(15, 24, 39, .03);
            color: inherit;
            text-decoration: none;
            cursor: pointer;
            transition:
                transform .18s ease,
                box-shadow .18s ease,
                border-color .18s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            border-color: #C4B5FD;
            box-shadow:
                0 10px 25px rgba(15, 24, 39, .08);
        }

        .stat-card:focus-visible {
            outline: 3px solid rgba(124, 58, 237, .20);
            outline-offset: 3px;
        }

        .stat-action {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-top: 13px;
            padding-top: 11px;
            border-top: 1px solid #EEF2F7;
            color: var(--primary);
            font-size: 10px;
            font-weight: 850;
        }

        .stat-arrow {
            font-size: 14px;
            transition: transform .18s ease;
        }

        .stat-card:hover .stat-arrow {
            transform: translateX(3px);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }

        .stat-icon {
            width: 39px;
            height: 39px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 900;
        }

        .purple {
            background: #F3E8FF;
            color: #7C3AED;
        }

        .green {
            background: #D1FAE5;
            color: #047857;
        }

        .yellow {
            background: #FEF3C7;
            color: #B45309;
        }

        .blue {
            background: #DBEAFE;
            color: #1D4ED8;
        }

        .trend {
            padding: 4px 7px;
            border-radius: 999px;
            font-size: 9px;
            font-weight: 850;
        }

        .trend.up {
            background: #D1FAE5;
            color: #047857;
        }

        .trend.down {
            background: #FEE2E2;
            color: #B91C1C;
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
            padding: 21px;
            box-shadow: 0 1px 2px rgba(15, 24, 39, .03);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 20px;
        }

        .card-title {
            font-size: 15px;
            font-weight: 850;
        }

        .card-description {
            margin-top: 4px;
            color: var(--muted);
            font-size: 11px;
        }

        .chart-area {
            height: 255px;
            display: flex;
            align-items: flex-end;
            gap: 13px;
            padding-top: 20px;
            border-bottom: 1px solid #E8EDF4;
            position: relative;
        }

        .chart-grid {
            position: absolute;
            inset: 20px 0 0 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            pointer-events: none;
        }

        .grid-line {
            width: 100%;
            border-top: 1px dashed #E8EDF4;
        }

        .month {
            flex: 1;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            align-items: center;
            gap: 7px;
            z-index: 1;
        }

        .bars {
            width: 100%;
            height: 205px;
            display: flex;
            gap: 4px;
            justify-content: center;
            align-items: flex-end;
        }

        .bar {
            width: 13px;
            min-height: 4px;
            border-radius: 4px 4px 0 0;
        }

        .bar.detected {
            background: #C4B5FD;
        }

        .bar.reviewed {
            background: var(--primary);
        }

        .month-label {
            color: #94A3B8;
            font-size: 9px;
            font-weight: 700;
        }

        .legend {
            display: flex;
            gap: 18px;
            flex-wrap: wrap;
            margin-top: 17px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 7px;
            color: #64748B;
            font-size: 10px;
        }

        .legend-dot {
            width: 9px;
            height: 9px;
            border-radius: 3px;
        }

        .category-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .category-top {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 7px;
            font-size: 11px;
        }

        .category-name {
            color: #475569;
            font-weight: 750;
        }

        .category-value {
            color: #334155;
            font-weight: 850;
        }

        .progress {
            height: 8px;
            border-radius: 999px;
            background: #EEF2F7;
            overflow: hidden;
        }

        .progress-value {
            height: 100%;
            border-radius: 999px;
            background: var(--primary);
        }

        .organism-table {
            width: 100%;
            border-collapse: collapse;
        }

        .organism-table th {
            padding: 10px 11px;
            text-align: left;
            background: #F8FAFC;
            border-bottom: 1px solid var(--border);
            color: #64748B;
            font-size: 10px;
            font-weight: 850;
        }

        .organism-table td {
            padding: 12px 11px;
            border-bottom: 1px solid #EEF2F7;
            color: #475569;
            font-size: 11px;
        }

        .organism-table tr:last-child td {
            border-bottom: 0;
        }

        .organism-name {
            color: #334155;
            font-weight: 800;
        }

        .table-number {
            font-weight: 850;
            color: #334155;
        }

        .success-text {
            color: var(--success);
            font-weight: 850;
        }

        .review-summary {
            display: flex;
            flex-direction: column;
            gap: 13px;
        }

        .summary-row {
            display: grid;
            grid-template-columns: 37px minmax(0, 1fr) auto;
            gap: 10px;
            align-items: center;
        }

        .summary-icon {
            width: 37px;
            height: 37px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 900;
        }

        .summary-name {
            color: #475569;
            font-size: 11px;
            font-weight: 750;
        }

        .summary-help {
            margin-top: 2px;
            color: #94A3B8;
            font-size: 9px;
        }

        .summary-number {
            color: #334155;
            font-size: 16px;
            font-weight: 900;
        }

        .amount-box {
            padding: 16px;
            border-radius: 12px;
            background: #F5F3FF;
            border: 1px solid #DDD6FE;
        }

        .amount-label {
            color: #8B5CF6;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .amount-value {
            margin-top: 7px;
            color: #5B21B6;
            font-size: 25px;
            font-weight: 900;
        }

        .amount-help {
            margin-top: 5px;
            color: #7C3AED;
            font-size: 10px;
            line-height: 1.45;
        }

        .small-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-top: 12px;
        }

        .small-stat {
            padding: 12px;
            border: 1px solid #E7ECF3;
            border-radius: 10px;
            background: #FBFCFE;
        }

        .small-stat span {
            display: block;
            color: #94A3B8;
            font-size: 9px;
        }

        .small-stat strong {
            display: block;
            margin-top: 4px;
            color: #334155;
            font-size: 13px;
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

            .filters {
                width: 100%;
            }

            .select {
                flex: 1;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .chart-area {
                gap: 6px;
            }

            .bar {
                width: 8px;
            }
        }
    
        .empty-state {
            padding: 15px;
            border-radius: 10px;
            border: 1px solid #E7ECF3;
            background: #F8FAFC;
            color: #94A3B8;
            font-size: 10px;
            line-height: 1.5;
        }

        .period-info {
            margin-top: 5px;
            color: #94A3B8;
            font-size: 9px;
        }

        .proposal-state-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .proposal-state {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 11px 12px;
            border: 1px solid #E7ECF3;
            border-radius: 10px;
            background: #FBFCFE;
            font-size: 10px;
        }

        .proposal-state span {
            color: #64748B;
            font-weight: 750;
        }

        .proposal-state strong {
            color: #334155;
            font-size: 13px;
        }

        .finance-group + .finance-group {
            margin-top: 12px;
        }

        .bar-value {
            color: #64748B;
            font-size: 7px;
            font-weight: 800;
        }

</style>

    <div class="directivo-stats">

        <div class="page-header">

            <div>

                <h1 class="page-title">
                    Estadísticas
                </h1>

                <div class="page-subtitle">
                    Consulta indicadores reales sobre
                    convocatorias, revisiones y propuestas
                    registradas en la plataforma.
                </div>

                <div class="period-info">
                    Periodo:
                    {{ $inicio->format('d/m/Y') }}
                    —
                    {{ $fin->format('d/m/Y') }}
                </div>

            </div>

            <div class="filters">

                <select
                    class="select"
                    wire:model.live="anio"
                >

                    @foreach (
                        $anios
                        as $anioDisponible
                    )

                        <option
                            value="{{
                                $anioDisponible
                            }}"
                        >
                            {{ $anioDisponible }}
                        </option>

                    @endforeach

                </select>

                <select
                    class="select"
                    wire:model.live="periodo"
                >

                    <option value="todo">
                        Todo el año
                    </option>

                    <option value="ultimos6">
                        Últimos 6 meses
                    </option>

                    <option value="ultimos3">
                        Últimos 3 meses
                    </option>

                    <option value="mes">
                        Este mes
                    </option>

                </select>

            </div>

        </div>

        <div class="stats-grid">

            <a
                href="{{
                    route(
                        'filament.directivo.pages.buscar-convocatorias'
                    )
                }}"
                class="stat-card"
                title="Ver convocatorias detectadas"
            >

                <div class="stat-header">
                    <div class="stat-icon purple">
                        C
                    </div>
                </div>

                <div class="stat-value">
                    {{ $totalConvocatorias }}
                </div>

                <div class="stat-label">
                    Convocatorias detectadas
                </div>

                <div class="stat-help">
                    Durante el periodo seleccionado
                </div>

                <div class="stat-action">
                    <span>
                        Ver convocatorias
                    </span>

                    <span class="stat-arrow">
                        →
                    </span>
                </div>

            </a>

            <a
                href="{{
                    route(
                        'filament.directivo.pages.reportes',
                        [
                            'seccion' =>
                                'revisiones',
                        ]
                    )
                }}"
                class="stat-card"
                title="Consultar revisiones"
            >

                <div class="stat-header">
                    <div class="stat-icon green">
                        ✓
                    </div>
                </div>

                <div class="stat-value">
                    {{ $revisadas }}
                </div>

                <div class="stat-label">
                    Convocatorias revisadas
                </div>

                <div class="stat-help">
                    Con decisión administrativa registrada
                </div>

                <div class="stat-action">
                    <span>
                        Ver revisiones
                    </span>

                    <span class="stat-arrow">
                        →
                    </span>
                </div>

            </a>

            <a
                href="{{
                    route(
                        'filament.directivo.pages.convocatorias-por-revisar'
                    )
                }}"
                class="stat-card"
                title="Ver convocatorias pendientes"
            >

                <div class="stat-header">
                    <div class="stat-icon yellow">
                        !
                    </div>
                </div>

                <div class="stat-value">
                    {{ $pendientes }}
                </div>

                <div class="stat-label">
                    Pendientes de revisión
                </div>

                <div class="stat-help">
                    Dentro del periodo seleccionado
                </div>

                <div class="stat-action">
                    <span>
                        Revisar pendientes
                    </span>

                    <span class="stat-arrow">
                        →
                    </span>
                </div>

            </a>

            <a
                href="{{
                    route(
                        'filament.directivo.pages.reportes',
                        [
                            'seccion' =>
                                'propuestas',
                        ]
                    )
                }}"
                class="stat-card"
                title="Consultar propuestas"
            >

                <div class="stat-header">
                    <div class="stat-icon blue">
                        P
                    </div>
                </div>

                <div class="stat-value">
                    {{ $totalPropuestas }}
                </div>

                <div class="stat-label">
                    Propuestas registradas
                </div>

                <div class="stat-help">
                    Registradas en la plataforma
                </div>

                <div class="stat-action">
                    <span>
                        Ver propuestas
                    </span>

                    <span class="stat-arrow">
                        →
                    </span>
                </div>

            </a>

        </div>

        <div class="layout">

            <div class="column">

                <section class="card">

                    <div class="card-header">

                        <div>

                            <div class="card-title">
                                Convocatorias por mes
                            </div>

                            <div class="card-description">
                                Comparación entre convocatorias
                                detectadas y revisadas.
                            </div>

                        </div>

                    </div>

                    <div class="chart-area">

                        <div class="chart-grid">
                            <div class="grid-line"></div>
                            <div class="grid-line"></div>
                            <div class="grid-line"></div>
                            <div class="grid-line"></div>
                            <div class="grid-line"></div>
                        </div>

                        @foreach (
                            $meses
                            as $mes
                        )

                            <div class="month">

                                <div class="bars">

                                    <div
                                        class="
                                            bar
                                            detected
                                        "
                                        title="
                                            Detectadas:
                                            {{ $mes['detectadas'] }}
                                        "
                                        style="
                                            height:
                                            {{
                                                $mes[
                                                    'altura_detectadas'
                                                ]
                                            }}%;
                                        "
                                    ></div>

                                    <div
                                        class="
                                            bar
                                            reviewed
                                        "
                                        title="
                                            Revisadas:
                                            {{ $mes['revisadas'] }}
                                        "
                                        style="
                                            height:
                                            {{
                                                $mes[
                                                    'altura_revisadas'
                                                ]
                                            }}%;
                                        "
                                    ></div>

                                </div>

                                <span class="bar-value">
                                    {{
                                        $mes[
                                            'detectadas'
                                        ]
                                    }}
                                    /
                                    {{
                                        $mes[
                                            'revisadas'
                                        ]
                                    }}
                                </span>

                                <span class="month-label">
                                    {{
                                        $mes[
                                            'nombre'
                                        ]
                                    }}
                                </span>

                            </div>

                        @endforeach

                    </div>

                    <div class="legend">

                        <div class="legend-item">
                            <span
                                class="legend-dot"
                                style="
                                    background:#C4B5FD;
                                "
                            ></span>
                            Detectadas
                        </div>

                        <div class="legend-item">
                            <span
                                class="legend-dot"
                                style="
                                    background:#7C3AED;
                                "
                            ></span>
                            Revisadas
                        </div>

                    </div>

                </section>

                <section class="card">

                    <div class="card-header">

                        <div>

                            <div class="card-title">
                                Principales organismos
                            </div>

                            <div class="card-description">
                                Organismos con convocatorias
                                registradas durante el periodo.
                            </div>

                        </div>

                    </div>

                    @if (
                        $organismos->isNotEmpty()
                    )

                        <div
                            style="
                                overflow-x:auto;
                            "
                        >

                            <table class="organism-table">

                                <thead>
                                    <tr>
                                        <th>
                                            Organismo
                                        </th>
                                        <th>
                                            Detectadas
                                        </th>
                                        <th>
                                            Revisadas
                                        </th>
                                        <th>
                                            Propuestas
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>

                                    @foreach (
                                        $organismos
                                        as $organismo
                                    )

                                        <tr>

                                            <td
                                                class="
                                                    organism-name
                                                "
                                            >
                                                {{
                                                    $organismo
                                                        ->nombre
                                                }}
                                            </td>

                                            <td
                                                class="
                                                    table-number
                                                "
                                            >
                                                {{
                                                    $organismo
                                                        ->detectadas
                                                }}
                                            </td>

                                            <td
                                                class="
                                                    success-text
                                                "
                                            >
                                                {{
                                                    $organismo
                                                        ->revisadas
                                                }}
                                            </td>

                                            <td
                                                class="
                                                    table-number
                                                "
                                            >
                                                {{
                                                    $organismo
                                                        ->propuestas
                                                }}
                                            </td>

                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                    @else

                        <div class="empty-state">
                            No existen convocatorias para
                            el periodo seleccionado.
                        </div>

                    @endif

                </section>

            </div>

            <div class="column">

                <section class="card">

                    <div class="card-header">

                        <div>

                            <div class="card-title">
                                Convocatorias por área
                            </div>

                            <div class="card-description">
                                Distribución real por
                                categoría.
                            </div>

                        </div>

                    </div>

                    <div class="category-list">

                        @forelse (
                            $categorias
                            as $categoria
                        )

                            <div>

                                <div class="category-top">

                                    <span
                                        class="
                                            category-name
                                        "
                                    >
                                        {{
                                            $categoria
                                                ->nombre
                                        }}
                                    </span>

                                    <span
                                        class="
                                            category-value
                                        "
                                    >
                                        {{
                                            $categoria
                                                ->total
                                        }}
                                        ·
                                        {{
                                            number_format(
                                                $categoria
                                                    ->porcentaje,
                                                1
                                            )
                                        }}%
                                    </span>

                                </div>

                                <div class="progress">

                                    <div
                                        class="
                                            progress-value
                                        "
                                        style="
                                            width:
                                            {{
                                                min(
                                                    100,
                                                    $categoria
                                                        ->porcentaje
                                                )
                                            }}%;
                                        "
                                    ></div>

                                </div>

                            </div>

                        @empty

                            <div class="empty-state">
                                No hay categorías para
                                mostrar.
                            </div>

                        @endforelse

                    </div>

                </section>

                <section class="card">

                    <div class="card-header">

                        <div>

                            <div class="card-title">
                                Estado de las revisiones
                            </div>

                            <div class="card-description">
                                Estado actual de las
                                convocatorias del periodo.
                            </div>

                        </div>

                    </div>

                    <div class="review-summary">

                        <div class="summary-row">

                            <div
                                class="
                                    summary-icon
                                    green
                                "
                            >
                                ✓
                            </div>

                            <div>
                                <div class="summary-name">
                                    Aprobadas
                                </div>
                                <div class="summary-help">
                                    Publicadas, activas
                                    o cerradas
                                </div>
                            </div>

                            <div class="summary-number">
                                {{ $aprobadas }}
                            </div>

                        </div>

                        <div class="summary-row">

                            <div
                                class="
                                    summary-icon
                                    yellow
                                "
                            >
                                !
                            </div>

                            <div>
                                <div class="summary-name">
                                    Pendientes
                                </div>
                                <div class="summary-help">
                                    Esperando revisión
                                </div>
                            </div>

                            <div class="summary-number">
                                {{ $pendientes }}
                            </div>

                        </div>

                        <div class="summary-row">

                            <div
                                class="
                                    summary-icon
                                    purple
                                "
                            >
                                C
                            </div>

                            <div>
                                <div class="summary-name">
                                    Corrección
                                </div>
                                <div class="summary-help">
                                    Requieren ajustes
                                </div>
                            </div>

                            <div class="summary-number">
                                {{ $correcciones }}
                            </div>

                        </div>

                        <div class="summary-row">

                            <div
                                class="
                                    summary-icon
                                "
                                style="
                                    background:#FEE2E2;
                                    color:#B91C1C;
                                "
                            >
                                X
                            </div>

                            <div>
                                <div class="summary-name">
                                    Descartadas
                                </div>
                                <div class="summary-help">
                                    No continúan en el
                                    proceso
                                </div>
                            </div>

                            <div class="summary-number">
                                {{ $rechazadas }}
                            </div>

                        </div>

                    </div>

                </section>

                <section class="card">

                    <div class="card-header">

                        <div>

                            <div class="card-title">
                                Propuestas por estado
                            </div>

                            <div class="card-description">
                                Distribución de las
                                propuestas registradas.
                            </div>

                        </div>

                    </div>

                    <div class="proposal-state-list">

                        @forelse (
                            $propuestasPorEstado
                            as $estadoPropuesta
                        )

                            <div class="proposal-state">

                                <span>
                                    {{
                                        str_replace(
                                            '_',
                                            ' ',
                                            $estadoPropuesta
                                                ->estado
                                        )
                                    }}
                                </span>

                                <strong>
                                    {{
                                        $estadoPropuesta
                                            ->total
                                    }}
                                </strong>

                            </div>

                        @empty

                            <div class="empty-state">
                                No existen propuestas
                                registradas durante
                                este periodo.
                            </div>

                        @endforelse

                    </div>

                </section>

                <section class="card">

                    <div class="card-header">

                        <div>

                            <div class="card-title">
                                Financiamiento identificado
                            </div>

                            <div class="card-description">
                                Montos máximos registrados,
                                separados por moneda.
                            </div>

                        </div>

                    </div>

                    @forelse (
                        $financiamientos
                        as $financiamiento
                    )

                        <div class="finance-group">

                            <div class="amount-box">

                                <div class="amount-label">
                                    Monto potencial
                                    ·
                                    {{
                                        $financiamiento
                                            ->moneda
                                    }}
                                </div>

                                <div class="amount-value">
                                    $
                                    {{
                                        number_format(
                                            (float)
                                            $financiamiento
                                                ->total,
                                            2
                                        )
                                    }}
                                </div>

                                <div class="amount-help">
                                    Suma de los montos
                                    máximos disponibles
                                    en
                                    {{
                                        $financiamiento
                                            ->convocatorias
                                    }}
                                    convocatorias.
                                </div>

                            </div>

                            <div class="small-stats">

                                <div class="small-stat">
                                    <span>
                                        Promedio
                                    </span>
                                    <strong>
                                        $
                                        {{
                                            number_format(
                                                (float)
                                                $financiamiento
                                                    ->promedio,
                                                2
                                            )
                                        }}
                                    </strong>
                                </div>

                                <div class="small-stat">
                                    <span>
                                        Mayor monto
                                    </span>
                                    <strong>
                                        $
                                        {{
                                            number_format(
                                                (float)
                                                $financiamiento
                                                    ->maximo,
                                                2
                                            )
                                        }}
                                    </strong>
                                </div>

                            </div>

                        </div>

                    @empty

                        <div class="empty-state">
                            No hay montos máximos
                            registrados para las
                            convocatorias de este periodo.
                        </div>

                    @endforelse

                </section>

            </div>

        </div>

    </div>

</x-filament-panels::page>
