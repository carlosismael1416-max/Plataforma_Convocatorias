<x-filament-panels::page>
    <style>
        .error-log {
            --text: #0F1827;
            --muted: #64748B;
            --primary: #059669;
            --primary-dark: #047857;
            --primary-soft: #D1FAE5;
            --border: #DDE3EE;
            --warning: #D97706;
            --danger: #DC2626;

            color: var(--text);
        }

        .error-log * {
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

        .stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 20px;
        }

        .stat {
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

        .red {
            background: #FEE2E2;
            color: #B91C1C;
        }

        .yellow {
            background: #FEF3C7;
            color: #B45309;
        }

        .green {
            background: #D1FAE5;
            color: #047857;
        }

        .blue {
            background: #DBEAFE;
            color: #1D4ED8;
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

        .filters-card,
        .table-card {
            margin-bottom: 20px;
            padding: 20px;
            border: 1px solid var(--border);
            border-radius: 15px;
            background: white;
        }

        .section-title {
            font-size: 14px;
            font-weight: 850;
        }

        .section-description {
            margin-top: 4px;
            color: var(--muted);
            font-size: 10px;
        }

        .filters {
            display: grid;
            grid-template-columns: 1.4fr repeat(3, 1fr);
            gap: 11px;
            margin-top: 16px;
        }

        .field {
            min-height: 41px;
            padding: 0 12px;
            border: 1px solid var(--border);
            border-radius: 9px;
            background: #FBFCFE;
            color: #64748B;
            display: flex;
            align-items: center;
            font-size: 10px;
        }

        .errors-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
        }

        .errors-table th {
            padding: 11px 12px;
            background: #F8FAFC;
            border-bottom: 1px solid var(--border);
            color: #64748B;
            font-size: 9px;
            font-weight: 850;
            text-align: left;
        }

        .errors-table td {
            padding: 14px 12px;
            border-bottom: 1px solid #EEF2F7;
            color: #475569;
            font-size: 9px;
            vertical-align: middle;
        }

        .errors-table tr:last-child td {
            border-bottom: 0;
        }

        .error-title {
            color: #334155;
            font-size: 10px;
            font-weight: 850;
        }

        .error-code {
            display: inline-flex;
            margin-top: 4px;
            padding: 3px 6px;
            border-radius: 5px;
            background: #F1F5F9;
            color: #475569;
            font-family: monospace;
            font-size: 8px;
        }

        .badge {
            display: inline-flex;
            padding: 5px 8px;
            border-radius: 999px;
            font-size: 8px;
            font-weight: 850;
            white-space: nowrap;
        }

        .badge-error {
            background: #FEE2E2;
            color: #B91C1C;
        }

        .badge-warning {
            background: #FEF3C7;
            color: #92400E;
        }

        .badge-open {
            background: #FEE2E2;
            color: #B91C1C;
        }

        .badge-resolved {
            background: #D1FAE5;
            color: #047857;
        }

        .action {
            color: var(--primary);
            font-size: 9px;
            font-weight: 850;
            text-decoration: none;
        }

        .source-summary {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }

        .source-card {
            padding: 16px;
            border: 1px solid var(--border);
            border-radius: 13px;
            background: white;
        }

        .source-name {
            color: #334155;
            font-size: 11px;
            font-weight: 850;
        }

        .source-count {
            margin-top: 9px;
            font-size: 22px;
            font-weight: 900;
        }

        .source-meta {
            margin-top: 3px;
            color: #94A3B8;
            font-size: 9px;
        }

        .notice {
            padding: 13px;
            border: 1px solid #A7F3D0;
            border-radius: 10px;
            background: #ECFDF5;
            color: #047857;
            font-size: 9px;
            line-height: 1.5;
        }

        @media (max-width: 1000px) {
            .stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .filters {
                grid-template-columns: repeat(2, 1fr);
            }

            .source-summary {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 650px) {
            .page-header {
                flex-direction: column;
            }

            .stats,
            .filters {
                grid-template-columns: 1fr;
            }
        }
    
        .filter-control {
            width: 100%;
            min-height: 41px;
            padding: 0 12px;
            border: 1px solid var(--border);
            border-radius: 9px;
            outline: none;
            background: #FBFCFE;
            color: #475569;
            font-size: 10px;
        }

        .filter-control:focus {
            border-color: var(--primary);
            background: white;
        }

        .filters-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 12px;
        }

        .clear-button {
            min-height: 35px;
            padding: 0 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: white;
            color: #64748B;
            font-size: 9px;
            font-weight: 850;
            cursor: pointer;
        }

        .clear-button:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .context-filter {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            margin-bottom: 20px;
            padding: 13px 15px;
            border: 1px solid #BFDBFE;
            border-radius: 10px;
            background: #EFF6FF;
            color: #1D4ED8;
            font-size: 10px;
        }

        .context-filter strong {
            font-weight: 900;
        }

        .context-remove {
            border: 0;
            background: transparent;
            color: #1D4ED8;
            font-size: 9px;
            font-weight: 850;
            cursor: pointer;
        }

        .clickable-row {
            cursor: pointer;
            transition: background .12s ease;
        }

        .clickable-row:hover td {
            background: #FAFFFC;
        }

        .empty-row td {
            padding: 34px 18px;
            text-align: center;
        }

        .empty-title {
            margin-bottom: 5px;
            color: #475569;
            font-size: 11px;
            font-weight: 850;
        }

        .empty-text {
            color: #94A3B8;
            font-size: 10px;
            line-height: 1.6;
        }

        .pagination-wrap {
            margin-top: 18px;
        }

        .source-empty {
            margin-bottom: 20px;
            padding: 18px;
            border: 1px dashed #CBD5E1;
            border-radius: 12px;
            background: #F8FAFC;
            color: #64748B;
            text-align: center;
            font-size: 10px;
        }


        .filters-actions {
            gap: 9px;
        }

        .search-button {
            min-width: 100px;
            border-color: var(--primary);
            background: var(--primary);
            color: white;
        }

        .search-button:hover {
            border-color: var(--primary-dark);
            background: var(--primary-dark);
            color: white;
        }
\n</style>

    <div class="error-log">

        <div class="page-header">

            <div>

                <h1 class="page-title">
                    Bitácora de Errores
                </h1>

                <div class="page-subtitle">
                    Consulta las incidencias reales
                    registradas durante la extracción
                    y procesamiento de información.
                </div>

            </div>

            <div class="role-badge">
                <span class="role-dot"></span>
                Administrador
            </div>

        </div>


        @if ($ejecucion !== null)

            <div class="context-filter">

                <div>

                    Mostrando errores de

                    <strong>
                        Ejecución #{{ $ejecucion }}
                    </strong>

                    @if ($ejecucionContexto)

                        ·
                        {{
                            $ejecucionContexto
                                ->fecha_inicio
                                ?->format(
                                    'd/m/Y H:i'
                                )
                        }}

                    @endif

                </div>

                <button
                    type="button"
                    class="context-remove"
                    wire:click="
                        quitarFiltroEjecucion
                    "
                >
                    Quitar filtro
                </button>

            </div>

        @endif


        <div class="stats">

            <div class="stat">

                <div class="stat-icon red">
                    !
                </div>

                <div class="stat-value">
                    {{ $totalErrores }}
                </div>

                <div class="stat-label">
                    Errores registrados
                </div>

                <div class="stat-help">
                    Total almacenado
                    en la bitácora
                </div>

            </div>


            <div class="stat">

                <div class="stat-icon yellow">
                    P
                </div>

                <div class="stat-value">
                    {{ $pendientes }}
                </div>

                <div class="stat-label">
                    Pendientes
                </div>

                <div class="stat-help">
                    Requieren atención
                </div>

            </div>


            <div class="stat">

                <div class="stat-icon green">
                    ✓
                </div>

                <div class="stat-value">
                    {{ $resueltos }}
                </div>

                <div class="stat-label">
                    Resueltos
                </div>

                <div class="stat-help">
                    Incidencias atendidas
                </div>

            </div>


            <div class="stat">

                <div class="stat-icon blue">
                    F
                </div>

                <div class="stat-value">
                    {{ $fuentesRegistradas }}
                </div>

                <div class="stat-label">
                    Fuentes registradas
                </div>

                <div class="stat-help">
                    Configuradas
                    en el sistema
                </div>

            </div>

        </div>


        @if ($topFuentes->isNotEmpty())

            <div class="source-summary">

                @foreach (
                    $topFuentes
                    as $item
                )

                    <div class="source-card">

                        <div class="source-name">

                            {{
                                $item
                                    ->fuente
                                    ?->nombre
                                ?? 'Fuente eliminada'
                            }}

                        </div>

                        <div class="source-count">

                            {{ $item->total }}

                        </div>

                        <div class="source-meta">
                            Errores registrados
                        </div>

                    </div>

                @endforeach

            </div>

        @else

            <div class="source-empty">
                No existen fuentes con
                incidencias registradas.
            </div>

        @endif


        <section class="filters-card">

            <div class="section-title">
                Buscar y filtrar
            </div>

            <div class="section-description">
                Localiza incidencias por mensaje,
                código, tipo, fuente o estado.
            </div>


            <div class="filters">

                <input
                    type="search"
                    class="filter-control"
                    placeholder="Buscar error..."
                    wire:model="buscar"
                    wire:keydown.enter="
                        aplicarFiltros
                    "
                >


                <select
                    class="filter-control"
                    wire:model.live="tipo"
                >
                    <option value="">
                        Todos los tipos
                    </option>

                    @foreach (
                        $tiposError
                        as $valor => $etiqueta
                    )

                        <option
                            value="{{ $valor }}"
                        >
                            {{ $etiqueta }}
                        </option>

                    @endforeach
                </select>


                <select
                    class="filter-control"
                    wire:model.live="fuente"
                >
                    <option value="">
                        Todas las fuentes
                    </option>

                    @foreach (
                        $fuentes
                        as $fuenteFiltro
                    )

                        <option
                            value="{{
                                $fuenteFiltro->id
                            }}"
                        >
                            {{
                                $fuenteFiltro
                                    ->nombre
                            }}
                        </option>

                    @endforeach
                </select>


                <select
                    class="filter-control"
                    wire:model.live="estado"
                >
                    <option value="">
                        Todos los estados
                    </option>

                    <option value="pendiente">
                        Pendiente
                    </option>

                    <option value="resuelto">
                        Resuelto
                    </option>
                </select>

            </div>


            <div class="filters-actions">

                <button
                    type="button"
                    class="
                        clear-button
                        search-button
                    "
                    wire:click="
                        aplicarFiltros
                    "
                >
                    Buscar
                </button>

                <button
                    type="button"
                    class="clear-button"
                    wire:click="
                        limpiarFiltros
                    "
                >
                    Limpiar filtros
                </button>

            </div>

        </section>


        <section class="table-card">

            <div class="section-title">
                Incidencias registradas
            </div>

            <div class="section-description">
                Historial almacenado en
                bitacora_errores.
            </div>


            <div style="overflow-x:auto;">

                <table class="errors-table">

                    <thead>

                        <tr>
                            <th>Error</th>
                            <th>Tipo</th>
                            <th>Fuente</th>
                            <th>Ejecución</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>

                    </thead>


                    <tbody>

                        @forelse (
                            $errores
                            as $error
                        )

                            <tr
                                class="clickable-row"
                                onclick="
                                    window.location.href=
                                    '{{
                                        route(
                                            'filament.admin.pages.detalle-error',
                                            [
                                                'record' =>
                                                    $error->id,
                                            ]
                                        )
                                    }}'
                                "
                            >

                                <td>

                                    <div class="error-title">
                                        {{
                                            $error->mensaje
                                        }}
                                    </div>

                                    @if (
                                        $error
                                            ->codigo_error
                                    )

                                        <span class="error-code">
                                            {{
                                                $error
                                                    ->codigo_error
                                            }}
                                        </span>

                                    @endif

                                </td>


                                <td>

                                    @if ($error->tipo_error)

                                        <span
                                            class="
                                                badge
                                                badge-warning
                                            "
                                        >
                                            {{
                                                $error
                                                    ->tipo_error
                                            }}
                                        </span>

                                    @else

                                        —

                                    @endif

                                </td>


                                <td>

                                    {{
                                        $error
                                            ->fuente
                                            ?->nombre
                                        ?? 'Sin fuente'
                                    }}

                                </td>


                                <td>

                                    @if (
                                        $error
                                            ->ejecucion_scraping_id
                                    )

                                        #{{
                                            $error
                                                ->ejecucion_scraping_id
                                        }}

                                    @else

                                        —

                                    @endif

                                </td>


                                <td>

                                    {{
                                        $error
                                            ->created_at
                                            ?->format(
                                                'd/m/Y H:i'
                                            )
                                        ?? '—'
                                    }}

                                </td>


                                <td>

                                    <span
                                        class="
                                            badge
                                            {{
                                                $error
                                                    ->resuelto
                                                    ? 'badge-resolved'
                                                    : 'badge-open'
                                            }}
                                        "
                                    >
                                        {{
                                            $error
                                                ->resuelto
                                                ? 'Resuelto'
                                                : 'Pendiente'
                                        }}
                                    </span>

                                </td>


                                <td>

                                    <a
                                        href="{{
                                            route(
                                                'filament.admin.pages.detalle-error',
                                                [
                                                    'record' =>
                                                        $error->id,
                                                ]
                                            )
                                        }}"
                                        class="action"
                                        onclick="
                                            event.stopPropagation()
                                        "
                                    >
                                        Ver detalle
                                    </a>

                                </td>

                            </tr>


                        @empty

                            <tr class="empty-row">

                                <td colspan="7">

                                    <div class="empty-title">
                                        Sin errores registrados
                                    </div>

                                    <div class="empty-text">

                                        @if (
                                            $buscar !== ''
                                            || $tipo !== ''
                                            || $fuente !== ''
                                            || $estado !== ''
                                            || $ejecucion !== null
                                        )

                                            No existen incidencias
                                            que coincidan con los
                                            filtros seleccionados.

                                        @else

                                            El sistema todavía no
                                            ha registrado incidencias
                                            en la bitácora.

                                        @endif

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            @if ($errores->hasPages())

                <div class="pagination-wrap">
                    {{ $errores->links() }}
                </div>

            @endif

        </section>


        <div class="notice">

            La información mostrada proviene
            directamente de

            <strong>
                bitacora_errores
            </strong>.

            No se generan incidencias ficticias
            para completar la interfaz.

        </div>

    </div>

</x-filament-panels::page>
