<x-filament-panels::page>

<style>
    .directivo-search {
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

    .directivo-search * {
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
        font-size: 25px;
        line-height: 1.2;
        font-weight: 850;
    }

    .page-subtitle {
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

    .search-card {
        padding: 20px;
        margin-bottom: 20px;
        background: white;
        border: 1px solid var(--border);
        border-radius: 14px;
    }

    .search-row {
        display: grid;
        grid-template-columns:
            minmax(0, 1fr)
            auto;
        gap: 10px;
    }

    .search-input {
        width: 100%;
        height: 45px;
        padding: 0 13px;
        border: 1px solid #CBD5E1;
        border-radius: 9px;
        background: white;
        color: var(--text);
        font: inherit;
        font-size: 12px;
        outline: none;
    }

    .search-input:focus,
    .filter-select:focus,
    .date-input:focus,
    .sort-select:focus {
        border-color: var(--primary);
        box-shadow:
            0 0 0 3px
            rgba(124, 58, 237, .10);
    }

    .search-btn {
        height: 45px;
        padding: 0 20px;
        border: 1px solid var(--primary);
        border-radius: 9px;
        background: var(--primary);
        color: white;
        font-size: 11px;
        font-weight: 850;
        cursor: pointer;
    }

    .filters {
        display: grid;
        grid-template-columns:
            repeat(
                4,
                minmax(0, 1fr)
            );
        gap: 10px;
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px solid #EEF2F7;
    }

    .filter-field label {
        display: block;
        margin-bottom: 5px;
        color: #64748B;
        font-size: 9px;
        font-weight: 800;
    }

    .filter-select,
    .date-input {
        width: 100%;
        height: 39px;
        padding: 0 10px;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        background: white;
        color: #334155;
        font: inherit;
        font-size: 10px;
        outline: none;
    }

    .filter-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        margin-top: 13px;
    }

    .active-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .filter-chip {
        padding: 5px 8px;
        border-radius: 999px;
        background: var(--primary-soft);
        color: var(--primary-dark);
        font-size: 8px;
        font-weight: 800;
    }

    .clear-btn {
        padding: 0;
        border: 0;
        background: transparent;
        color: var(--primary);
        cursor: pointer;
        font-size: 9px;
        font-weight: 800;
    }

    .content-layout {
        display: grid;
        grid-template-columns:
            minmax(0, 1fr)
            255px;
        gap: 20px;
        align-items: start;
    }

    .results-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 13px;
    }

    .results-title {
        font-size: 13px;
        font-weight: 850;
    }

    .results-count {
        margin-top: 3px;
        color: var(--muted);
        font-size: 9px;
    }

    .sort-select {
        height: 36px;
        padding: 0 9px;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: white;
        color: #475569;
        font-size: 9px;
        outline: none;
    }

    .results-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .result-card {
        padding: 18px;
        background: white;
        border: 1px solid var(--border);
        border-radius: 13px;
        box-shadow:
            0 1px 2px
            rgba(15, 24, 39, .03);
    }

    .result-card:hover {
        border-color: #C4B5FD;
    }

    .result-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 15px;
    }

    .result-title {
        color: #1E293B;
        font-size: 13px;
        line-height: 1.4;
        font-weight: 850;
    }

    .result-organization {
        margin-top: 4px;
        color: var(--primary);
        font-size: 9px;
        font-weight: 750;
    }

    .status {
        padding: 5px 8px;
        border-radius: 999px;
        white-space: nowrap;
        font-size: 8px;
        font-weight: 850;
    }

    .status-open {
        background: #D1FAE5;
        color: #065F46;
    }

    .status-review {
        background: #FEF3C7;
        color: #92400E;
    }

    .status-correction {
        background: #F3E8FF;
        color: #6D28D9;
    }

    .status-closed {
        background: #F1F5F9;
        color: #475569;
    }

    .status-danger {
        background: #FEE2E2;
        color: #991B1B;
    }

    .result-description {
        margin-top: 11px;
        color: #64748B;
        font-size: 10px;
        line-height: 1.55;
    }

    .result-meta {
        display: grid;
        grid-template-columns:
            repeat(
                4,
                minmax(0, 1fr)
            );
        gap: 8px;
        margin-top: 14px;
    }

    .meta-box {
        padding: 9px;
        border: 1px solid #E8EDF4;
        border-radius: 9px;
        background: #FBFCFE;
    }

    .meta-label {
        display: block;
        margin-bottom: 3px;
        color: #94A3B8;
        font-size: 7px;
        text-transform: uppercase;
    }

    .meta-value {
        display: block;
        color: #334155;
        font-size: 9px;
        font-weight: 800;
        word-break: break-word;
    }

    .tags {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-top: 12px;
    }

    .tag {
        padding: 4px 7px;
        border-radius: 999px;
        background: #F1F5F9;
        color: #475569;
        font-size: 7px;
        font-weight: 750;
    }

    .result-bottom {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-top: 14px;
        padding-top: 13px;
        border-top: 1px solid #EEF2F7;
    }

    .source {
        color: #94A3B8;
        font-size: 8px;
    }

    .result-actions {
        display: flex;
        gap: 7px;
    }

    .btn {
        min-height: 32px;
        padding: 0 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: white;
        color: #475569;
        font-size: 8px;
        font-weight: 850;
        text-decoration: none;
    }

    .btn-primary {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
    }

    .sidebar {
        position: sticky;
        top: 90px;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .side-card {
        padding: 16px;
        background: white;
        border: 1px solid var(--border);
        border-radius: 13px;
    }

    .side-title {
        margin-bottom: 12px;
        font-size: 11px;
        font-weight: 850;
    }

    .summary-item {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        padding: 8px 0;
        border-bottom: 1px solid #EEF2F7;
        font-size: 9px;
    }

    .summary-item:last-child {
        border-bottom: 0;
    }

    .summary-item span {
        color: var(--muted);
    }

    .summary-item strong {
        color: #334155;
    }

    .category-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        padding: 7px 0;
        font-size: 9px;
    }

    .category-name {
        color: #475569;
    }

    .category-count {
        min-width: 23px;
        height: 20px;
        padding: 0 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: var(--primary-soft);
        color: var(--primary-dark);
        font-size: 8px;
        font-weight: 850;
    }

    .info-box {
        padding: 11px;
        border: 1px solid #DDD6FE;
        border-radius: 9px;
        background: #F5F3FF;
        color: #5B21B6;
        font-size: 8px;
        line-height: 1.5;
    }

    .empty-state {
        padding: 45px 20px;
        background: white;
        border: 1px solid var(--border);
        border-radius: 13px;
        text-align: center;
    }

    .empty-title {
        color: #334155;
        font-size: 12px;
        font-weight: 850;
    }

    .empty-text {
        max-width: 430px;
        margin: 6px auto 0;
        color: var(--muted);
        font-size: 9px;
        line-height: 1.5;
    }

    .pagination {
        margin-top: 15px;
    }

    @media (max-width: 1050px) {
        .content-layout {
            grid-template-columns: 1fr;
        }

        .sidebar {
            position: static;
        }

        .filters,
        .result-meta {
            grid-template-columns:
                repeat(2, 1fr);
        }
    }

    @media (max-width: 650px) {
        .page-header,
        .result-bottom {
            flex-direction: column;
            align-items: flex-start;
        }

        .search-row,
        .filters,
        .result-meta {
            grid-template-columns: 1fr;
        }

        .search-btn {
            width: 100%;
        }

        .result-top {
            flex-direction: column;
        }

        .result-actions {
            width: 100%;
        }

        .result-actions .btn {
            flex: 1;
        }
    }
</style>

<div class="directivo-search">

    <div class="page-header">

        <div>

            <h1 class="page-title">
                Buscar Convocatorias
            </h1>

            <div class="page-subtitle">
                Consulta las convocatorias
                registradas en la plataforma.
            </div>

        </div>

        <div class="role-badge">
            <span class="role-dot"></span>
            Directivo
        </div>

    </div>

    <section class="search-card">

        <div class="search-row">

            <input
                type="text"
                class="search-input"
                placeholder="
                    Buscar por título, organismo,
                    palabra clave, modalidad o área...
                "
                wire:model="buscar"
                wire:keydown.enter="
                    aplicarBusqueda
                "
            >

            <button
                type="button"
                class="search-btn"
                wire:click="
                    aplicarBusqueda
                "
                wire:loading.attr="disabled"
                wire:target="
                    aplicarBusqueda
                "
            >
                <span
                    wire:loading.remove
                    wire:target="
                        aplicarBusqueda
                    "
                >
                    Buscar
                </span>

                <span
                    wire:loading
                    wire:target="
                        aplicarBusqueda
                    "
                >
                    Buscando...
                </span>
            </button>

        </div>

        <div class="filters">

            <div class="filter-field">

                <label>
                    Área temática
                </label>

                <select
                    class="filter-select"
                    wire:model.live="categoria"
                >
                    <option value="">
                        Todas las áreas
                    </option>

                    @foreach (
                        $categorias
                        as $categoriaItem
                    )

                        <option
                            value="{{
                                $categoriaItem->id
                            }}"
                        >
                            {{
                                $categoriaItem
                                    ->nombre
                            }}
                        </option>

                    @endforeach

                </select>

            </div>

            <div class="filter-field">

                <label>
                    Organismo
                </label>

                <select
                    class="filter-select"
                    wire:model.live="organismo"
                >
                    <option value="">
                        Todos los organismos
                    </option>

                    @foreach (
                        $organismos
                        as $organismoItem
                    )

                        <option
                            value="{{
                                $organismoItem->id
                            }}"
                        >
                            {{
                                $organismoItem
                                    ->nombre
                            }}
                        </option>

                    @endforeach

                </select>

            </div>

            <div class="filter-field">

                <label>
                    Estado
                </label>

                <select
                    class="filter-select"
                    wire:model.live="estado"
                >
                    <option value="">
                        Todos
                    </option>

                    <option value="BORRADOR">
                        Borrador
                    </option>

                    <option
                        value="PENDIENTE_REVISION"
                    >
                        Pendiente de revisión
                    </option>

                    <option
                        value="REQUIERE_CORRECCIONES"
                    >
                        Requiere correcciones
                    </option>

                    <option value="PUBLICADA">
                        Publicada
                    </option>

                    <option value="ACTIVA">
                        Activa
                    </option>

                    <option value="CERRADA">
                        Cerrada
                    </option>

                    <option value="DESCARTADA">
                        Descartada
                    </option>

                    <option value="ARCHIVADA">
                        Archivada
                    </option>

                </select>

            </div>

            <div class="filter-field">

                <label>
                    Fecha de cierre
                </label>

                <input
                    class="date-input"
                    type="date"
                    wire:model.live="
                        fechaCierre
                    "
                >

            </div>

        </div>

        <div class="filter-actions">

            <div class="active-filters">

                @forelse (
                    $filtrosActivos
                    as $filtro
                )

                    <span class="filter-chip">
                        {{ $filtro }}
                    </span>

                @empty

                    <span class="filter-chip">
                        Sin filtros
                    </span>

                @endforelse

            </div>

            <button
                type="button"
                class="clear-btn"
                wire:click="limpiarFiltros"
            >
                Limpiar filtros
            </button>

        </div>

    </section>

    <div class="content-layout">

        <main>

            <div class="results-header">

                <div>

                    <div class="results-title">
                        Resultados
                    </div>

                    <div class="results-count">

                        Se encontraron

                        {{
                            $convocatorias
                                ->total()
                        }}

                        {{
                            $convocatorias
                                ->total()
                            === 1
                                ? 'convocatoria'
                                : 'convocatorias'
                        }}

                    </div>

                </div>

                <select
                    class="sort-select"
                    wire:model.live="orden"
                >
                    <option value="recientes">
                        Más recientes
                    </option>

                    <option value="cierre">
                        Próximas a cerrar
                    </option>

                    <option value="monto">
                        Mayor monto
                    </option>
                </select>

            </div>

            <div class="results-list">

                @forelse (
                    $convocatorias
                    as $convocatoria
                )

                    @php
                        $estadoTexto =
                            match (
                                $convocatoria
                                    ->estado
                            ) {
                                'BORRADOR' =>
                                    'Borrador',

                                'PENDIENTE_REVISION' =>
                                    'Pendiente de revisión',

                                'REQUIERE_CORRECCIONES' =>
                                    'Requiere correcciones',

                                'PUBLICADA' =>
                                    'Publicada',

                                'ACTIVA' =>
                                    'Activa',

                                'CERRADA' =>
                                    'Cerrada',

                                'DESCARTADA' =>
                                    'Descartada',

                                'ARCHIVADA' =>
                                    'Archivada',

                                default =>
                                    $convocatoria
                                        ->estado,
                            };

                        $estadoClase =
                            match (
                                $convocatoria
                                    ->estado
                            ) {
                                'PUBLICADA',
                                'ACTIVA' =>
                                    'status-open',

                                'PENDIENTE_REVISION' =>
                                    'status-review',

                                'REQUIERE_CORRECCIONES' =>
                                    'status-correction',

                                'DESCARTADA' =>
                                    'status-danger',

                                default =>
                                    'status-closed',
                            };
                    @endphp

                    <article class="result-card">

                        <div class="result-top">

                            <div>

                                <div class="result-title">
                                    {{
                                        $convocatoria
                                            ->titulo
                                    }}
                                </div>

                                <div
                                    class="
                                        result-organization
                                    "
                                >
                                    {{
                                        $convocatoria
                                            ->organismo
                                            ?->nombre
                                        ?? 'Organismo no especificado'
                                    }}
                                </div>

                            </div>

                            <span
                                class="
                                    status
                                    {{ $estadoClase }}
                                "
                            >
                                {{ $estadoTexto }}
                            </span>

                        </div>

                        <div
                            class="
                                result-description
                            "
                        >
                            {{
                                $convocatoria
                                    ->descripcion
                                ?: (
                                    $convocatoria
                                        ->objetivo
                                    ?: 'Sin descripción registrada.'
                                )
                            }}
                        </div>

                        <div class="result-meta">

                            <div class="meta-box">

                                <span class="meta-label">
                                    Cierre
                                </span>

                                <span class="meta-value">
                                    {{
                                        $convocatoria
                                            ->fecha_cierre
                                            ?->format(
                                                'd/m/Y'
                                            )
                                        ?? 'Sin fecha'
                                    }}
                                </span>

                            </div>

                            <div class="meta-box">

                                <span class="meta-label">
                                    Monto máximo
                                </span>

                                <span class="meta-value">

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

                                </span>

                            </div>

                            <div class="meta-box">

                                <span class="meta-label">
                                    Modalidad
                                </span>

                                <span class="meta-value">
                                    {{
                                        $convocatoria
                                            ->modalidad
                                        ?: 'No especificada'
                                    }}
                                </span>

                            </div>

                            <div class="meta-box">

                                <span class="meta-label">
                                    Cobertura
                                </span>

                                <span class="meta-value">
                                    {{
                                        $convocatoria
                                            ->ubicacion
                                        ?: 'No especificada'
                                    }}
                                </span>

                            </div>

                        </div>

                        <div class="tags">

                            @if (
                                $convocatoria
                                    ->categoria
                            )

                                <span class="tag">
                                    {{
                                        $convocatoria
                                            ->categoria
                                            ->nombre
                                    }}
                                </span>

                            @endif

                            @if (
                                $convocatoria
                                    ->modalidad
                            )

                                <span class="tag">
                                    {{
                                        $convocatoria
                                            ->modalidad
                                    }}
                                </span>

                            @endif

                            @if (
                                $convocatoria
                                    ->origen
                            )

                                <span class="tag">
                                    Origen:
                                    {{
                                        $convocatoria
                                            ->origen
                                    }}
                                </span>

                            @endif

                        </div>

                        <div class="result-bottom">

                            <div class="source">

                                Fuente:

                                {{
                                    $convocatoria
                                        ->fuente
                                        ?->nombre
                                    ?? 'Sin fuente registrada'
                                }}

                            </div>

                            <div class="result-actions">

                                @if (
                                    $convocatoria
                                        ->url_original
                                )

                                    <a
                                        href="{{
                                            $convocatoria
                                                ->url_original
                                        }}"
                                        target="_blank"
                                        rel="
                                            noopener
                                            noreferrer
                                        "
                                        class="btn"
                                    >
                                        Abrir fuente
                                    </a>

                                @endif

                                <a
                                    href="{{
                                        route(
                                            'filament.directivo.pages.detalle-convocatoria',
                                            [
                                                'record' =>
                                                    $convocatoria
                                                        ->id,
                                            ]
                                        )
                                    }}"
                                    class="
                                        btn
                                        btn-primary
                                    "
                                >
                                    Ver detalle
                                </a>

                            </div>

                        </div>

                    </article>

                @empty

                    <div class="empty-state">

                        <div class="empty-title">
                            No se encontraron
                            convocatorias
                        </div>

                        <div class="empty-text">
                            No existen registros que
                            coincidan con los filtros
                            seleccionados.
                        </div>

                    </div>

                @endforelse

            </div>

            @if (
                $convocatorias
                    ->hasPages()
            )

                <div class="pagination">
                    {{
                        $convocatorias
                            ->links()
                    }}
                </div>

            @endif

        </main>

        <aside class="sidebar">

            <section class="side-card">

                <div class="side-title">
                    Resumen
                </div>

                <div class="summary-item">
                    <span>Total</span>

                    <strong>
                        {{ $total }}
                    </strong>
                </div>

                <div class="summary-item">
                    <span>
                        Publicadas / activas
                    </span>

                    <strong
                        style="
                            color:#059669;
                        "
                    >
                        {{ $abiertas }}
                    </strong>
                </div>

                <div class="summary-item">
                    <span>
                        Pendientes de revisión
                    </span>

                    <strong
                        style="
                            color:#D97706;
                        "
                    >
                        {{ $enRevision }}
                    </strong>
                </div>

                <div class="summary-item">
                    <span>
                        Cierran en 30 días
                    </span>

                    <strong>
                        {{ $proximasCerrar }}
                    </strong>
                </div>

            </section>

            <section class="side-card">

                <div class="side-title">
                    Áreas con convocatorias
                </div>

                @forelse (
                    $categoriasResumen
                    as $categoriaResumen
                )

                    <div class="category-item">

                        <span class="category-name">
                            {{
                                $categoriaResumen
                                    ->nombre
                            }}
                        </span>

                        <span class="category-count">
                            {{
                                $categoriaResumen
                                    ->total
                            }}
                        </span>

                    </div>

                @empty

                    <div class="source">
                        No hay convocatorias con
                        categoría asignada.
                    </div>

                @endforelse

            </section>

            <div class="info-box">
                Esta pantalla es de consulta.
                La revisión y cambio de estado se
                gestionará desde
                “Convocatorias por revisar”.
            </div>

        </aside>

    </div>

</div>

</x-filament-panels::page>
