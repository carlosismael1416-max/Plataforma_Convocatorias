<x-filament-panels::page>
    <style>
        .review-page {
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

        .review-page * {
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
            margin-bottom: 22px;
        }

        .stat {
            padding: 18px;
            background: white;
            border: 1px solid var(--border);
            border-radius: 14px;
        }

        .stat-label {
            color: var(--muted);
            font-size: 11px;
        }

        .stat-value {
            margin-top: 7px;
            font-size: 25px;
            font-weight: 900;
        }

        .stat-help {
            margin-top: 4px;
            color: #94A3B8;
            font-size: 10px;
        }

        .toolbar {
            display: grid;
            grid-template-columns:
                minmax(0, 1fr)
                auto
                auto
                190px
                190px;
            gap: 12px;
            padding: 18px;
            margin-bottom: 18px;
            background: white;
            border: 1px solid var(--border);
            border-radius: 15px;
        }

        .input,
        .select {
            width: 100%;
            height: 43px;
            padding: 0 12px;
            border: 1px solid #CBD5E1;
            border-radius: 9px;
            background: white;
            color: #334155;
            font: inherit;
            font-size: 12px;
            outline: none;
        }

        .input:focus,
        .select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, .10);
        }

        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            margin-bottom: 13px;
        }

        .results-title {
            font-size: 15px;
            font-weight: 850;
        }

        .results-count {
            margin-top: 3px;
            color: var(--muted);
            font-size: 11px;
        }

        .review-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .review-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 1px 2px rgba(15, 24, 39, .03);
        }

        .review-card.urgent {
            border-left: 4px solid var(--warning);
        }

        .review-top {
            display: flex;
            justify-content: space-between;
            gap: 18px;
            align-items: flex-start;
        }

        .title {
            color: #1E293B;
            font-size: 15px;
            line-height: 1.4;
            font-weight: 850;
        }

        .organization {
            margin-top: 5px;
            color: var(--primary);
            font-size: 11px;
            font-weight: 750;
        }

        .status {
            padding: 6px 9px;
            border-radius: 999px;
            background: #FEF3C7;
            color: #92400E;
            font-size: 10px;
            font-weight: 850;
            white-space: nowrap;
        }

        .description {
            margin-top: 13px;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.6;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 10px;
            margin-top: 16px;
        }

        .meta {
            padding: 11px;
            border-radius: 10px;
            border: 1px solid #E8EDF4;
            background: #FBFCFE;
        }

        .meta-label {
            display: block;
            margin-bottom: 4px;
            color: #94A3B8;
            font-size: 9px;
            text-transform: uppercase;
        }

        .meta-value {
            color: #334155;
            font-size: 11px;
            font-weight: 800;
        }

        .review-footer {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            align-items: center;
            margin-top: 16px;
            padding-top: 15px;
            border-top: 1px solid #EEF2F7;
        }

        .origin {
            color: #94A3B8;
            font-size: 10px;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .btn {
            min-height: 36px;
            padding: 0 13px;
            border-radius: 9px;
            border: 1px solid var(--border);
            background: white;
            color: #475569;
            font-size: 10px;
            font-weight: 850;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .toolbar > .btn {
            height: 43px;
            min-height: 43px;
            padding: 0 18px;
            white-space: nowrap;
        }

        .info {
            display: flex;
            gap: 12px;
            padding: 14px;
            margin-bottom: 18px;
            border-radius: 11px;
            background: #F5F3FF;
            border: 1px solid #DDD6FE;
            color: #5B21B6;
            font-size: 11px;
            line-height: 1.5;
        }

        .info-icon {
            width: 24px;
            height: 24px;
            flex: 0 0 24px;
            border-radius: 50%;
            background: #EDE9FE;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
        }

        @media (max-width: 1000px) {
            .stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .toolbar {
                grid-template-columns: 1fr;
            }

            .meta-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 600px) {
            .page-header,
            .review-top,
            .review-footer {
                flex-direction: column;
                align-items: flex-start;
            }

            .stats,
            .meta-grid {
                grid-template-columns: 1fr;
            }

            .actions {
                width: 100%;
            }

            .actions .btn {
                flex: 1;
            }
        }
    
        .review-card.expired {
            border-left: 4px solid var(--danger);
        }

        .urgency {
            font-weight: 850;
        }

        .urgency-high {
            color: #D97706;
        }

        .urgency-expired {
            color: #DC2626;
        }

        .urgency-normal {
            color: #475569;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .empty-state {
            padding: 42px 20px;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: white;
            color: var(--muted);
            text-align: center;
            font-size: 11px;
        }

        .pagination {
            margin-top: 16px;
        }

</style>

    <div class="review-page">

        <div class="page-header">

            <div>

                <h1 class="page-title">
                    Convocatorias por Revisar
                </h1>

                <div class="page-subtitle">
                    Consulta las convocatorias pendientes
                    de revisión registradas en la plataforma.
                </div>

            </div>

            <div class="role-badge">
                <span class="role-dot"></span>
                Directivo
            </div>

        </div>

        <div class="stats">

            <div class="stat">

                <div class="stat-label">
                    Pendientes
                </div>

                <div class="stat-value">
                    {{ $pendientes }}
                </div>

                <div class="stat-help">
                    Esperando revisión
                </div>

            </div>

            <div class="stat">

                <div class="stat-label">
                    Cierre próximo
                </div>

                <div class="stat-value">
                    {{ $cierreProximo }}
                </div>

                <div class="stat-help">
                    Cierran en los próximos 30 días
                </div>

            </div>

            <div class="stat">

                <div class="stat-label">
                    Automáticas
                </div>

                <div class="stat-value">
                    {{ $automaticas }}
                </div>

                <div class="stat-help">
                    Detectadas por scraping
                </div>

            </div>

            <div class="stat">

                <div class="stat-label">
                    Manuales
                </div>

                <div class="stat-value">
                    {{ $manuales }}
                </div>

                <div class="stat-help">
                    Registradas manualmente
                </div>

            </div>

        </div>

        <div class="info">

            <div class="info-icon">
                i
            </div>

            <div>
                Esta sección muestra únicamente
                convocatorias con estado
                PENDIENTE_REVISION. La consulta general
                se mantiene en “Buscar Convocatorias”.
            </div>

        </div>

        <section class="toolbar">

            <input
                class="input"
                type="text"
                placeholder="
                    Buscar por título, organismo,
                    categoría o fuente...
                "
                wire:model="buscar"
                wire:keydown.enter="
                    aplicarBusqueda
                "
            >

            <button
                type="button"
                class="
                    btn
                    btn-primary
                "
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

            <button
                type="button"
                class="btn"
                wire:click="
                    limpiarFiltros
                "
                wire:loading.attr="disabled"
                wire:target="
                    limpiarFiltros
                "
            >
                <span
                    wire:loading.remove
                    wire:target="
                        limpiarFiltros
                    "
                >
                    Limpiar filtros
                </span>

                <span
                    wire:loading
                    wire:target="
                        limpiarFiltros
                    "
                >
                    Limpiando...
                </span>
            </button>

            <select
                class="select"
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
                            $organismoItem->nombre
                        }}
                    </option>

                @endforeach

            </select>

            <select
                class="select"
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

        </section>

        <div class="results-header">

            <div>

                <div class="results-title">
                    Pendientes de revisión
                </div>

                <div class="results-count">

                    {{
                        $convocatorias->total()
                    }}

                    {{
                        $convocatorias->total()
                        === 1
                            ? 'convocatoria encontrada'
                            : 'convocatorias encontradas'
                    }}

                </div>

            </div>

        </div>

        <div class="review-list">

            @forelse (
                $convocatorias
                as $convocatoria
            )

                @php
                    $detectada =
                        $convocatoria
                            ->fecha_extraccion
                        ?? $convocatoria
                            ->created_at;

                    $cierre =
                        $convocatoria
                            ->fecha_cierre;

                    $dias = null;

                    if (
                        $cierre
                        && ! $cierre->isPast()
                    ) {
                        $dias =
                            now()
                                ->startOfDay()
                                ->diffInDays(
                                    $cierre
                                        ->copy()
                                        ->startOfDay()
                                );
                    }

                    if (
                        $cierre
                        && $cierre->isPast()
                    ) {
                        $urgenciaTexto =
                            'Fecha vencida';

                        $urgenciaClase =
                            'urgency-expired';

                        $cardClase =
                            'expired';
                    } elseif (
                        $dias !== null
                        && $dias <= 30
                    ) {
                        $urgenciaTexto =
                            'Alta';

                        $urgenciaClase =
                            'urgency-high';

                        $cardClase =
                            'urgent';
                    } else {
                        $urgenciaTexto =
                            'Normal';

                        $urgenciaClase =
                            'urgency-normal';

                        $cardClase =
                            '';
                    }

                    $origenTexto =
                        match (
                            $convocatoria->origen
                        ) {
                            'SCRAPING' =>
                                'Detectada automáticamente por el motor de extracción',

                            'MANUAL' =>
                                'Registrada manualmente',

                            'API' =>
                                'Registrada mediante API',

                            default =>
                                $convocatoria->origen
                                ?: 'Origen no especificado',
                        };
                @endphp

                <article
                    class="
                        review-card
                        {{ $cardClase }}
                    "
                >

                    <div class="review-top">

                        <div>

                            <div class="title">
                                {{
                                    $convocatoria
                                        ->titulo
                                }}
                            </div>

                            <div class="organization">
                                {{
                                    $convocatoria
                                        ->organismo
                                        ?->nombre
                                    ?? 'Organismo no especificado'
                                }}
                            </div>

                        </div>

                        <span class="status">
                            Pendiente
                        </span>

                    </div>

                    <div class="description">

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

                    <div class="meta-grid">

                        <div class="meta">

                            <span class="meta-label">
                                Detectada
                            </span>

                            <span class="meta-value">
                                {{
                                    $detectada
                                        ?->format(
                                            'd/m/Y'
                                        )
                                    ?? 'Sin fecha'
                                }}
                            </span>

                        </div>

                        <div class="meta">

                            <span class="meta-label">
                                Fecha de cierre
                            </span>

                            <span class="meta-value">
                                {{
                                    $cierre
                                        ?->format(
                                            'd/m/Y'
                                        )
                                    ?? 'Sin fecha'
                                }}
                            </span>

                        </div>

                        <div class="meta">

                            <span class="meta-label">
                                Categoría
                            </span>

                            <span class="meta-value">
                                {{
                                    $convocatoria
                                        ->categoria
                                        ?->nombre
                                    ?? 'Sin categoría'
                                }}
                            </span>

                        </div>

                        <div class="meta">

                            <span class="meta-label">
                                Fuente
                            </span>

                            <span class="meta-value">
                                {{
                                    $convocatoria
                                        ->fuente
                                        ?->nombre
                                    ?? 'Sin fuente'
                                }}
                            </span>

                        </div>

                        <div class="meta">

                            <span class="meta-label">
                                Documentos
                            </span>

                            <span class="meta-value">

                                {{
                                    $convocatoria
                                        ->archivos_count
                                }}

                                {{
                                    $convocatoria
                                        ->archivos_count
                                    === 1
                                        ? 'archivo'
                                        : 'archivos'
                                }}

                            </span>

                        </div>

                    </div>

                    <div class="review-footer">

                        <div class="origin">

                            {{ $origenTexto }}

                            ·

                            <span
                                class="
                                    urgency
                                    {{ $urgenciaClase }}
                                "
                            >
                                Urgencia:
                                {{ $urgenciaTexto }}
                            </span>

                        </div>

                        <div class="actions">

                            <a
                                href="{{
                                    route(
                                        'filament.directivo.pages.detalle-convocatoria',
                                        [
                                            'record' =>
                                                $convocatoria->id,
                                        ]
                                    )
                                }}"
                                class="btn"
                            >
                                Ver detalle
                            </a>

                            <a
                                href="{{
                                    route(
                                        'filament.directivo.pages.revisar-convocatoria',
                                        [
                                            'record' =>
                                                $convocatoria->id,
                                        ]
                                    )
                                }}"
                                class="
                                    btn
                                    btn-primary
                                "
                            >
                                Revisar convocatoria
                            </a>

                        </div>

                    </div>

                </article>

            @empty

                <div class="empty-state">
                    No existen convocatorias pendientes
                    que coincidan con los filtros
                    seleccionados.
                </div>

            @endforelse

        </div>

        @if (
            $convocatorias->hasPages()
        )

            <div class="pagination">
                {{
                    $convocatorias
                        ->links()
                }}
            </div>

        @endif

    </div>

</x-filament-panels::page>
