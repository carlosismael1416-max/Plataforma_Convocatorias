<x-filament-panels::page>

    <style>
        .itsva-search-page {
            color: #1A2C4E;
        }

        .search-intro {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 20px;
        }

        .search-intro h2 {
            margin: 0;
            color: #1A2C4E;
            font-size: 22px;
            font-weight: 700;
        }

        .search-intro p {
            margin: 6px 0 0;
            color: #64748B;
            font-size: 13px;
            line-height: 1.5;
        }

        .results-count {
            flex: 0 0 auto;
            padding: 8px 12px;
            border-radius: 999px;
            background: #DBEAFE;
            color: #1D4ED8;
            font-size: 11px;
            font-weight: 700;
        }

        .search-panel {
            background: white;
            border: 1px solid #DDE3EE;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .search-main {
            display: grid;
            grid-template-columns:
                minmax(260px, 2fr)
                minmax(180px, 1fr)
                minmax(180px, 1fr);
            gap: 12px;
            margin-bottom: 12px;
        }

        .search-secondary {
            display: grid;
            grid-template-columns:
                repeat(3, minmax(0, 1fr))
                auto;
            gap: 12px;
            align-items: end;
        }

        .filter-group label {
            display: block;
            margin-bottom: 6px;
            color: #64748B;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .03em;
        }

        .itsva-input,
        .itsva-select {
            width: 100%;
            min-height: 42px;
            padding: 0 13px;
            border: 1px solid #DDE3EE;
            border-radius: 8px;
            background: white;
            color: #1A2C4E;
            outline: none;
            font-size: 12px;
        }

        .itsva-input:focus,
        .itsva-select:focus {
            border-color: #2563EB;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .08);
        }

        .btn-clear {
            min-height: 42px;
            padding: 0 15px;
            border: 1px solid #DDE3EE;
            border-radius: 8px;
            background: white;
            color: #475569;
            cursor: pointer;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        .btn-clear:hover {
            background: #F8FAFC;
        }

        .search-status {
            display: flex;
            align-items: center;
            gap: 7px;
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px solid #EEF2F7;
            color: #64748B;
            font-size: 11px;
        }

        .search-status strong {
            color: #1A2C4E;
        }

        .results {
            display: grid;
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .conv-card {
            display: flex;
            flex-direction: column;
            background: white;
            border: 1px solid #DDE3EE;
            border-radius: 12px;
            padding: 20px;
            transition:
                transform .16s ease,
                box-shadow .16s ease,
                border-color .16s ease;
        }

        .conv-card:hover {
            transform: translateY(-2px);
            border-color: #C6D4E8;
            box-shadow: 0 8px 22px rgba(15, 24, 39, .06);
        }

        .conv-title {
            margin-bottom: 7px;
            color: #1A2C4E;
            font-size: 16px;
            font-weight: 700;
            line-height: 1.4;
        }

        .conv-title a {
            color: inherit;
            text-decoration: none;
        }

        .conv-title a:hover {
            color: #2563EB;
        }

        .conv-org {
            margin-bottom: 12px;
            color: #64748B;
            font-size: 13px;
        }

        .conv-description {
            margin-bottom: 14px;
            color: #64748B;
            font-size: 11px;
            line-height: 1.6;
        }

        .conv-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 16px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 5px 9px;
            border-radius: 20px;
            background: #F0F4FB;
            color: #1A4B8C;
            font-size: 10px;
            font-weight: 650;
        }

        .badge-success {
            background: #D1FAE5;
            color: #059669;
        }

        .badge-blue {
            background: #DBEAFE;
            color: #2563EB;
        }

        .badge-money {
            background: #EDE9FE;
            color: #6D28D9;
        }

        .conv-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 9px;
            margin-top: auto;
            padding-top: 15px;
            border-top: 1px solid #EEF2F7;
        }

        .btn-detail,
        .btn-official,
        .btn-save {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 36px;
            padding: 0 12px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-detail {
            border: 1px solid #BFDBFE;
            background: #EFF6FF;
            color: #2563EB;
        }

        .btn-detail:hover {
            background: #DBEAFE;
        }

        .btn-official {
            border: 1px solid #DDE3EE;
            background: white;
            color: #475569;
        }

        .btn-save {
            margin-left: auto;
            border: 1px solid #1A4B8C;
            background: #1A4B8C;
            color: white;
        }

        .btn-save:hover:not(:disabled) {
            background: #153F77;
        }

        .btn-save.saved {
            border-color: #A7F3D0;
            background: #ECFDF5;
            color: #047857;
            cursor: default;
        }

        .btn-save:disabled {
            opacity: 1;
        }

        .empty-state {
            grid-column: 1 / -1;
            padding: 60px 20px;
            border: 1px solid #DDE3EE;
            border-radius: 12px;
            background: white;
            text-align: center;
        }

        .empty-icon {
            margin-bottom: 12px;
            font-size: 34px;
        }

        .empty-state h3 {
            margin: 0 0 7px;
            color: #1A2C4E;
            font-size: 16px;
            font-weight: 700;
        }

        .empty-state p {
            max-width: 520px;
            margin: 0 auto;
            color: #64748B;
            font-size: 12px;
            line-height: 1.6;
        }

        .empty-state button {
            margin-top: 17px;
        }

        .pagination-wrapper {
            margin-top: 22px;
        }

        .loading-bar {
            position: relative;
        }

        .loading-message {
            display: none;
            margin-bottom: 12px;
            color: #2563EB;
            font-size: 11px;
            font-weight: 650;
        }

        [wire\:loading] .loading-message {
            display: block;
        }

        @media (max-width: 1100px) {
            .search-main,
            .search-secondary {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 900px) {
            .results {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 650px) {
            .search-intro {
                flex-direction: column;
            }

            .search-main,
            .search-secondary {
                grid-template-columns: 1fr;
            }

            .btn-save {
                margin-left: 0;
            }
        }
    </style>

    <div class="itsva-search-page">

        <div class="search-intro">

            <div>
                <h2>
                    Buscar Convocatorias
                </h2>

                <p>
                    Consulta las oportunidades que ya fueron
                    publicadas o activadas para los usuarios.
                </p>
            </div>

            <div class="results-count">
                {{ $totalResultados }}
                {{ $totalResultados === 1
                    ? 'resultado'
                    : 'resultados' }}
            </div>

        </div>

        <section class="search-panel">

            <div class="search-main">

                <div class="filter-group">

                    <label>
                        Buscar
                    </label>

                    <input
                        type="search"
                        class="itsva-input"
                        wire:model.live.debounce.400ms="buscar"
                        placeholder="Título, descripción, organismo, categoría..."
                        autocomplete="off"
                    >

                </div>

                <div class="filter-group">

                    <label>
                        Categoría
                    </label>

                    <select
                        class="itsva-select"
                        wire:model.live="categoria"
                    >
                        <option value="">
                            Todas las categorías
                        </option>

                        @foreach ($categorias as $categoriaItem)

                            <option
                                value="{{ $categoriaItem->id }}"
                            >
                                {{ $categoriaItem->nombre }}
                            </option>

                        @endforeach

                    </select>

                </div>

                <div class="filter-group">

                    <label>
                        Organismo
                    </label>

                    <select
                        class="itsva-select"
                        wire:model.live="organismo"
                    >
                        <option value="">
                            Todos los organismos
                        </option>

                        @foreach ($organismos as $organismoItem)

                            <option
                                value="{{ $organismoItem->id }}"
                            >
                                {{ $organismoItem->nombre }}
                            </option>

                        @endforeach

                    </select>

                </div>

            </div>

            <div class="search-secondary">

                <div class="filter-group">

                    <label>
                        Estado
                    </label>

                    <select
                        class="itsva-select"
                        wire:model.live="estado"
                    >
                        <option value="">
                            Publicadas y activas
                        </option>

                        <option value="PUBLICADA">
                            Publicadas
                        </option>

                        <option value="ACTIVA">
                            Activas
                        </option>

                    </select>

                </div>

                <div class="filter-group">

                    <label>
                        Fecha de cierre
                    </label>

                    <select
                        class="itsva-select"
                        wire:model.live="cierre"
                    >
                        <option value="">
                            Cualquier fecha
                        </option>

                        <option value="7">
                            Próximos 7 días
                        </option>

                        <option value="30">
                            Próximos 30 días
                        </option>

                        <option value="90">
                            Próximos 90 días
                        </option>

                        <option value="sin_fecha">
                            Sin fecha de cierre
                        </option>

                    </select>

                </div>

                <div class="filter-group">

                    <label>
                        Ordenar
                    </label>

                    <select
                        class="itsva-select"
                        wire:model.live="orden"
                    >
                        <option value="cierre">
                            Cierre más próximo
                        </option>

                        <option value="recientes">
                            Más recientes
                        </option>

                        <option value="monto">
                            Mayor financiamiento
                        </option>

                        <option value="titulo">
                            Título A-Z
                        </option>

                    </select>

                </div>

                <button
                    type="button"
                    class="btn-clear"
                    wire:click="limpiarFiltros"
                >
                    Limpiar filtros
                </button>

            </div>

            <div class="search-status">

                <span>
                    🔎
                </span>

                <span>
                    Mostrando únicamente convocatorias
                    <strong>PUBLICADAS</strong>
                    o
                    <strong>ACTIVAS</strong>.
                </span>

            </div>

        </section>

        <div
            wire:loading.delay
            wire:target="
                buscar,
                categoria,
                organismo,
                estado,
                cierre,
                orden,
                limpiarFiltros,
                guardarConvocatoria
            "
            style="
                margin-bottom:12px;
                color:#2563EB;
                font-size:11px;
                font-weight:650;
            "
        >
            Actualizando resultados...
        </div>

        <div class="results">

            @forelse ($convocatorias as $convocatoria)

                @php
                    $guardada = in_array(
                        (int) $convocatoria->id,
                        $guardadasIds,
                        true
                    );
                @endphp

                <article
                    class="conv-card"
                    wire:key="convocatoria-{{ $convocatoria->id }}"
                >

                    <div class="conv-title">

                        <a
                            href="{{ route(
                                'filament.docente.pages.detalle-convocatoria',
                                ['record' => $convocatoria->id]
                            ) }}"
                        >
                            {{ $convocatoria->titulo }}
                        </a>

                    </div>

                    <div class="conv-org">

                        {{ $convocatoria->organismo?->nombre
                            ?? 'Sin organismo' }}

                    </div>

                    @if ($convocatoria->descripcion)

                        <div class="conv-description">

                            {{ \Illuminate\Support\Str::limit(
                                $convocatoria->descripcion,
                                170
                            ) }}

                        </div>

                    @endif

                    <div class="conv-meta">

                        @if ($convocatoria->categoria)

                            <span class="badge">

                                {{ $convocatoria
                                    ->categoria
                                    ->nombre }}

                            </span>

                        @endif

                        @if (
                            $convocatoria->estado
                            === 'ACTIVA'
                        )

                            <span
                                class="
                                    badge
                                    badge-success
                                "
                            >
                                Activa
                            </span>

                        @else

                            <span
                                class="
                                    badge
                                    badge-blue
                                "
                            >
                                Publicada
                            </span>

                        @endif

                        @if ($convocatoria->fecha_cierre)

                            <span class="badge">

                                Cierra:

                                {{ $convocatoria
                                    ->fecha_cierre
                                    ->format('d/m/Y') }}

                            </span>

                        @else

                            <span class="badge">
                                Sin fecha de cierre
                            </span>

                        @endif

                        @if (
                            $convocatoria->monto_maximo
                            !== null
                        )

                            <span
                                class="
                                    badge
                                    badge-money
                                "
                            >

                                $
                                {{ number_format(
                                    (float)
                                    $convocatoria
                                        ->monto_maximo,
                                    2,
                                    '.',
                                    ','
                                ) }}

                                {{ $convocatoria->moneda
                                    ?? 'MXN' }}

                            </span>

                        @endif

                    </div>

                    <div class="conv-actions">

                        <a
                            href="{{ route(
                                'filament.docente.pages.detalle-convocatoria',
                                ['record' => $convocatoria->id]
                            ) }}"
                            class="btn-detail"
                        >
                            Ver detalle
                        </a>

                        @if ($convocatoria->url_original)

                            <a
                                href="{{ $convocatoria->url_original }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="btn-official"
                            >
                                Sitio oficial ↗
                            </a>

                        @endif

                        @if ($guardada)

                            <button
                                type="button"
                                class="btn-save saved"
                                disabled
                            >
                                ✓ Guardada
                            </button>

                        @else

                            <button
                                type="button"
                                class="btn-save"
                                wire:click="
                                    guardarConvocatoria(
                                        {{ $convocatoria->id }}
                                    )
                                "
                                wire:loading.attr="disabled"
                                wire:target="
                                    guardarConvocatoria(
                                        {{ $convocatoria->id }}
                                    )
                                "
                            >
                                + Mis Convocatorias
                            </button>

                        @endif

                    </div>

                </article>

            @empty

                <div class="empty-state">

                    <div class="empty-icon">
                        🔎
                    </div>

                    @if (
                        trim($this->buscar) !== ''
                        || $this->categoria !== ''
                        || $this->organismo !== ''
                        || $this->estado !== ''
                        || $this->cierre !== ''
                    )

                        <h3>
                            No encontramos coincidencias
                        </h3>

                        <p>
                            No existen convocatorias publicadas o
                            activas que coincidan con los filtros
                            seleccionados.
                        </p>

                        <button
                            type="button"
                            class="btn-clear"
                            wire:click="limpiarFiltros"
                        >
                            Limpiar filtros
                        </button>

                    @else

                        <h3>
                            No hay convocatorias disponibles
                        </h3>

                        <p>
                            Actualmente no existen convocatorias
                            publicadas o activas. Cuando una
                            convocatoria termine su proceso de
                            revisión aparecerá automáticamente
                            en esta sección.
                        </p>

                    @endif

                </div>

            @endforelse

        </div>

        @if ($convocatorias->hasPages())

            <div class="pagination-wrapper">

                {{ $convocatorias->links() }}

            </div>

        @endif

    </div>

</x-filament-panels::page>
