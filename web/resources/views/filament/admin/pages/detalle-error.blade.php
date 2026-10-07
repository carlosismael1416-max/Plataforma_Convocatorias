<x-filament-panels::page>
    <style>
        .error-detail {
            --text: #0F1827;
            --muted: #64748B;
            --primary: #059669;
            --primary-dark: #047857;
            --primary-soft: #D1FAE5;
            --border: #DDE3EE;
            --danger: #DC2626;
            --warning: #D97706;

            color: var(--text);
        }

        .error-detail * {
            box-sizing: border-box;
        }

        .back-link {
            display: inline-flex;
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
            color: var(--danger);
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

        .message {
            margin-top: 7px;
            color: var(--muted);
            font-size: 12px;
        }

        .status {
            padding: 7px 11px;
            border-radius: 999px;
            background: #FEE2E2;
            color: #B91C1C;
            font-size: 10px;
            font-weight: 850;
            white-space: nowrap;
        }

        .summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
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
            margin-bottom: 5px;
            color: #94A3B8;
            font-size: 9px;
            text-transform: uppercase;
        }

        .summary-value {
            color: #334155;
            font-size: 11px;
            font-weight: 850;
        }

        .layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 310px;
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
            padding: 20px;
            border: 1px solid var(--border);
            border-radius: 15px;
            background: white;
        }

        .card-title {
            font-size: 14px;
            font-weight: 850;
        }

        .card-description {
            margin-top: 4px;
            margin-bottom: 17px;
            color: var(--muted);
            font-size: 10px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 11px;
        }

        .info-item {
            padding: 12px;
            border: 1px solid #E7ECF3;
            border-radius: 10px;
            background: #FBFCFE;
        }

        .info-item.full {
            grid-column: 1 / -1;
        }

        .info-label {
            display: block;
            margin-bottom: 5px;
            color: #94A3B8;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .info-value {
            color: #334155;
            font-size: 10px;
            font-weight: 800;
            word-break: break-word;
        }

        .code {
            margin-top: 12px;
            padding: 15px;
            border-radius: 10px;
            background: #0F172A;
            color: #E2E8F0;
            font-family: monospace;
            font-size: 10px;
            line-height: 1.6;
            white-space: pre-wrap;
            overflow-x: auto;
        }

        .event {
            display: grid;
            grid-template-columns: 30px minmax(0, 1fr);
            gap: 11px;
            padding: 13px 0;
            border-bottom: 1px solid #EEF2F7;
        }

        .event:last-child {
            border-bottom: 0;
        }

        .event-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #FEE2E2;
            color: #B91C1C;
            font-size: 9px;
            font-weight: 900;
        }

        .event-title {
            color: #334155;
            font-size: 10px;
            font-weight: 850;
        }

        .event-text {
            margin-top: 3px;
            color: #94A3B8;
            font-size: 9px;
            line-height: 1.45;
        }

        .side-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid #EEF2F7;
            font-size: 10px;
        }

        .side-row:last-child {
            border-bottom: 0;
        }

        .side-row span {
            color: var(--muted);
        }

        .side-row strong {
            color: #334155;
            text-align: right;
        }

        .warning-box {
            padding: 14px;
            border: 1px solid #FECACA;
            border-radius: 11px;
            background: #FEF2F2;
        }

        .warning-title {
            color: #B91C1C;
            font-size: 11px;
            font-weight: 850;
        }

        .warning-text {
            margin-top: 5px;
            color: #DC2626;
            font-size: 9px;
            line-height: 1.5;
        }

        .actions {
            display: flex;
            flex-direction: column;
            gap: 9px;
        }

        .action-btn {
            width: 100%;
            min-height: 40px;
            border: 1px solid var(--border);
            border-radius: 9px;
            background: white;
            color: #475569;
            font-size: 10px;
            font-weight: 850;
            cursor: pointer;
        }

        .action-btn.primary {
            border-color: var(--primary);
            background: var(--primary);
            color: white;
        }

        .action-btn.warning {
            border-color: #FDE68A;
            background: #FFFBEB;
            color: #92400E;
        }

        .visual-note {
            padding: 12px;
            border: 1px solid #A7F3D0;
            border-radius: 10px;
            background: #ECFDF5;
            color: #047857;
            font-size: 9px;
            line-height: 1.5;
            text-align: center;
        }

        @media (max-width: 1000px) {
            .layout {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: static;
            }

            .summary {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 650px) {
            .hero-top {
                flex-direction: column;
            }

            .summary,
            .info-grid {
                grid-template-columns: 1fr;
            }

            .info-item.full {
                grid-column: auto;
            }
        }
    
        .status.resolved {
            background: #D1FAE5;
            color: #047857;
        }

        .resolved-box {
            padding: 14px;
            border: 1px solid #A7F3D0;
            border-radius: 11px;
            background: #ECFDF5;
        }

        .resolved-title {
            color: #047857;
            font-size: 11px;
            font-weight: 850;
        }

        .resolved-text {
            margin-top: 5px;
            color: #059669;
            font-size: 9px;
            line-height: 1.5;
        }

        .action-link {
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .action-btn:disabled {
            opacity: .55;
            cursor: not-allowed;
        }

        .url-link {
            color: var(--primary);
            text-decoration: none;
        }

        .url-link:hover {
            text-decoration: underline;
        }

        .empty-box {
            padding: 17px;
            border: 1px dashed #CBD5E1;
            border-radius: 10px;
            background: #F8FAFC;
            color: #64748B;
            font-size: 10px;
            line-height: 1.6;
        }

        .event-icon.success {
            background: #D1FAE5;
            color: #047857;
        }

        .event-icon.neutral {
            background: #DBEAFE;
            color: #1D4ED8;
        }

</style>

    <div class="error-detail">

        <a
            href="{{
                route(
                    'filament.admin.pages.bitacora-errores',
                    $error->ejecucion_scraping_id
                        ? [
                            'ejecucion' =>
                                $error
                                    ->ejecucion_scraping_id,
                        ]
                        : []
                )
            }}"
            class="back-link"
        >
            ← Volver a Bitácora de Errores
        </a>


        <section class="hero">

            <div class="hero-top">

                <div>

                    <div class="eyebrow">
                        Error del sistema
                    </div>

                    <div class="title">

                        {{
                            $error->tipo_error
                            ?: 'Incidencia registrada'
                        }}

                    </div>

                    <div class="message">

                        {{ $error->mensaje }}

                    </div>

                </div>


                <div
                    class="
                        status
                        {{
                            $error->resuelto
                                ? 'resolved'
                                : ''
                        }}
                    "
                >
                    {{
                        $error->resuelto
                            ? 'Resuelto'
                            : 'Pendiente'
                    }}
                </div>

            </div>


            <div class="summary">

                <div class="summary-item">

                    <span class="summary-label">
                        Código
                    </span>

                    <span class="summary-value">
                        {{
                            $error->codigo_error
                            ?: 'Sin código'
                        }}
                    </span>

                </div>


                <div class="summary-item">

                    <span class="summary-label">
                        Tipo
                    </span>

                    <span class="summary-value">
                        {{
                            $error->tipo_error
                            ?: 'Sin clasificar'
                        }}
                    </span>

                </div>


                <div class="summary-item">

                    <span class="summary-label">
                        Fuente
                    </span>

                    <span class="summary-value">
                        {{
                            $error
                                ->fuente
                                ?->nombre
                            ?? 'Sin fuente'
                        }}
                    </span>

                </div>


                <div class="summary-item">

                    <span class="summary-label">
                        Ejecución
                    </span>

                    <span class="summary-value">

                        @if (
                            $error
                                ->ejecucion_scraping_id
                        )

                            #{{
                                $error
                                    ->ejecucion_scraping_id
                            }}

                        @else

                            Sin ejecución

                        @endif

                    </span>

                </div>

            </div>

        </section>


        <div class="layout">

            <main class="main">


                <section class="card">

                    <div class="card-title">
                        Información del error
                    </div>

                    <div class="card-description">
                        Datos almacenados por la bitácora
                        durante la incidencia.
                    </div>


                    <div class="info-grid">

                        <div class="info-item">

                            <span class="info-label">
                                Tipo de error
                            </span>

                            <span class="info-value">
                                {{
                                    $error->tipo_error
                                    ?: 'Sin clasificar'
                                }}
                            </span>

                        </div>


                        <div class="info-item">

                            <span class="info-label">
                                Código
                            </span>

                            <span class="info-value">
                                {{
                                    $error->codigo_error
                                    ?: 'Sin código'
                                }}
                            </span>

                        </div>


                        <div
                            class="
                                info-item
                                full
                            "
                        >

                            <span class="info-label">
                                Mensaje
                            </span>

                            <span class="info-value">
                                {{ $error->mensaje }}
                            </span>

                        </div>


                        <div
                            class="
                                info-item
                                full
                            "
                        >

                            <span class="info-label">
                                Detalle
                            </span>

                            <span class="info-value">
                                {{
                                    $error->detalle
                                    ?: 'Sin detalle adicional.'
                                }}
                            </span>

                        </div>


                        <div
                            class="
                                info-item
                                full
                            "
                        >

                            <span class="info-label">
                                URL afectada
                            </span>

                            <span class="info-value">

                                @if ($urlSegura)

                                    <a
                                        href="{{ $urlSegura }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="url-link"
                                    >
                                        {{ $error->url }}
                                    </a>

                                @else

                                    {{
                                        $error->url
                                        ?: 'Sin URL registrada'
                                    }}

                                @endif

                            </span>

                        </div>

                    </div>

                </section>


                <section class="card">

                    <div class="card-title">
                        Información técnica
                    </div>

                    <div class="card-description">
                        Contexto técnico disponible
                        para facilitar el diagnóstico.
                    </div>

                    <div class="info-grid">

                        <div class="info-item">

                            <span class="info-label">
                                HTTP status
                            </span>

                            <span class="info-value">
                                {{
                                    $error
                                        ->ejecucionFuente
                                        ?->http_status
                                    ?? 'No registrado'
                                }}
                            </span>

                        </div>


                        <div class="info-item">

                            <span class="info-label">
                                Ejecución por fuente
                            </span>

                            <span class="info-value">

                                @if (
                                    $error
                                        ->ejecucion_fuente_id
                                )

                                    #{{
                                        $error
                                            ->ejecucion_fuente_id
                                    }}

                                @else

                                    No registrada

                                @endif

                            </span>

                        </div>


                        <div class="info-item">

                            <span class="info-label">
                                Estado de fuente
                            </span>

                            <span class="info-value">
                                {{
                                    $error
                                        ->ejecucionFuente
                                        ?->estado
                                    ?? 'No registrado'
                                }}
                            </span>

                        </div>


                        <div class="info-item">

                            <span class="info-label">
                                Duración
                            </span>

                            <span class="info-value">

                                {{
                                    $error
                                        ->ejecucionFuente
                                        ?->duracion_segundos
                                    !== null
                                        ? number_format(
                                            (float)
                                            $error
                                                ->ejecucionFuente
                                                ->duracion_segundos,
                                            2
                                        ).' s'
                                        : 'No registrada'
                                }}

                            </span>

                        </div>

                    </div>

                </section>


                <section class="card">

                    <div class="card-title">
                        Stack trace
                    </div>

                    <div class="card-description">
                        Información técnica capturada
                        durante el fallo.
                    </div>


                    @if ($error->stack_trace)

                        <div class="code">{{ $error->stack_trace }}</div>

                    @else

                        <div class="empty-box">
                            Este error no tiene un
                            stack trace almacenado.
                        </div>

                    @endif

                </section>


                <section class="card">

                    <div class="card-title">
                        Contexto de la incidencia
                    </div>

                    <div class="card-description">
                        Eventos derivados de los
                        registros disponibles.
                    </div>


                    @forelse (
                        $eventos
                        as $indice => $evento
                    )

                        <div class="event">

                            <div
                                class="
                                    event-icon
                                    {{
                                        $evento['tipo']
                                        === 'resuelto'
                                            ? 'success'
                                            : (
                                                $evento['tipo']
                                                === 'inicio'
                                                    ? 'neutral'
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
                                            === 'resuelto'
                                                ? '✓'
                                                : $indice + 1
                                        )
                                }}
                            </div>


                            <div>

                                <div class="event-title">
                                    {{
                                        $evento[
                                            'titulo'
                                        ]
                                    }}
                                </div>

                                <div class="event-text">

                                    {{
                                        $evento[
                                            'texto'
                                        ]
                                    }}

                                    <br>

                                    {{
                                        $evento[
                                            'fecha'
                                        ]->format(
                                            'd/m/Y H:i:s'
                                        )
                                    }}

                                </div>

                            </div>

                        </div>


                    @empty

                        <div class="empty-box">
                            No existe contexto temporal
                            adicional para esta incidencia.
                        </div>

                    @endforelse

                </section>

            </main>


            <aside class="sidebar">


                @if ($error->resuelto)

                    <div class="resolved-box">

                        <div class="resolved-title">
                            Error resuelto
                        </div>

                        <div class="resolved-text">

                            Esta incidencia fue marcada
                            como atendida.

                            @if (
                                $error
                                    ->fecha_resolucion
                            )

                                <br><br>

                                {{
                                    $error
                                        ->fecha_resolucion
                                        ->format(
                                            'd/m/Y H:i'
                                        )
                                }}

                            @endif

                            @if (
                                $error
                                    ->usuarioResolucion
                            )

                                <br>

                                Por:
                                {{
                                    $error
                                        ->usuarioResolucion
                                        ->name
                                }}

                            @endif

                        </div>

                    </div>


                @else

                    <div class="warning-box">

                        <div class="warning-title">
                            Error pendiente
                        </div>

                        <div class="warning-text">
                            Esta incidencia todavía
                            requiere atención del
                            administrador.
                        </div>

                    </div>

                @endif


                <section class="card">

                    <div
                        class="card-title"
                        style="margin-bottom:14px;"
                    >
                        Registro
                    </div>


                    <div class="side-row">

                        <span>ID error</span>

                        <strong>
                            #{{ $error->id }}
                        </strong>

                    </div>


                    <div class="side-row">

                        <span>Fecha</span>

                        <strong>
                            {{
                                $error
                                    ->created_at
                                    ?->format(
                                        'd/m/Y H:i'
                                    )
                                ?? '—'
                            }}
                        </strong>

                    </div>


                    <div class="side-row">

                        <span>Ejecución</span>

                        <strong>
                            {{
                                $error
                                    ->ejecucion_scraping_id
                                    ? '#'.
                                        $error
                                            ->ejecucion_scraping_id
                                    : '—'
                            }}
                        </strong>

                    </div>


                    <div class="side-row">

                        <span>Fuente</span>

                        <strong>
                            {{
                                $error
                                    ->fuente
                                    ?->nombre
                                ?? '—'
                            }}
                        </strong>

                    </div>


                    <div class="side-row">

                        <span>Estado</span>

                        <strong
                            style="
                                color:{{
                                    $error->resuelto
                                        ? '#059669'
                                        : '#DC2626'
                                }};
                            "
                        >
                            {{
                                $error->resuelto
                                    ? 'Resuelto'
                                    : 'Pendiente'
                            }}
                        </strong>

                    </div>


                    @if ($error->resuelto)

                        <div class="side-row">

                            <span>
                                Resuelto por
                            </span>

                            <strong>
                                {{
                                    $error
                                        ->usuarioResolucion
                                        ?->name
                                    ?? '—'
                                }}
                            </strong>

                        </div>


                        <div class="side-row">

                            <span>
                                Fecha resolución
                            </span>

                            <strong>
                                {{
                                    $error
                                        ->fecha_resolucion
                                        ?->format(
                                            'd/m/Y H:i'
                                        )
                                    ?? '—'
                                }}
                            </strong>

                        </div>

                    @endif

                </section>


                <section class="card">

                    <div
                        class="card-title"
                        style="margin-bottom:14px;"
                    >
                        Acciones
                    </div>


                    <div class="actions">


                        @if ($error->resuelto)

                            <button
                                type="button"
                                class="
                                    action-btn
                                    warning
                                "
                                wire:click="
                                    reabrirIncidencia
                                "
                                wire:confirm="
                                    ¿Deseas reabrir esta incidencia?
                                "
                            >
                                Reabrir incidencia
                            </button>

                        @else

                            <button
                                type="button"
                                class="
                                    action-btn
                                    primary
                                "
                                wire:click="
                                    marcarComoResuelto
                                "
                                wire:confirm="
                                    ¿Deseas marcar esta incidencia como resuelta?
                                "
                            >
                                Marcar como resuelto
                            </button>

                        @endif


                        @if (
                            $error
                                ->ejecucion_scraping_id
                        )

                            <a
                                href="{{
                                    route(
                                        'filament.admin.pages.detalle-ejecucion',
                                        [
                                            'record' =>
                                                $error
                                                    ->ejecucion_scraping_id,
                                        ]
                                    )
                                }}"
                                class="
                                    action-btn
                                    action-link
                                "
                            >
                                Ver ejecución relacionada
                            </a>

                        @endif


                        @if ($error->fuente_id)

                            <a
                                href="{{
                                    route(
                                        'filament.admin.pages.gestionar-fuente',
                                        [
                                            'record' =>
                                                $error
                                                    ->fuente_id,
                                        ]
                                    )
                                }}"
                                class="
                                    action-btn
                                    action-link
                                "
                            >
                                Ver fuente
                            </a>

                        @endif


                        @if ($urlSegura)

                            <a
                                href="{{ $urlSegura }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="
                                    action-btn
                                    action-link
                                "
                            >
                                Abrir URL afectada
                            </a>

                        @endif


                        <button
                            type="button"
                            class="
                                action-btn
                                warning
                            "
                            disabled
                            title="
                                Disponible cuando Laravel
                                se conecte al extractor
                                en la Fase 3
                            "
                        >
                            Reintentar procesamiento
                        </button>

                    </div>

                </section>


                <div class="visual-note">
                    Resolver o reabrir una incidencia
                    actualiza directamente PostgreSQL.
                    El reintento del extractor se
                    conectará en la Fase 3.
                </div>

            </aside>

        </div>

    </div>

</x-filament-panels::page>
