<x-filament-panels::page>
    <style>
        .source-manager {
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

        .source-manager * {
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

        .header-card {
            padding: 24px;
            margin-bottom: 20px;
            border: 1px solid var(--border);
            border-radius: 16px;
            background: white;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            align-items: flex-start;
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

        .url {
            margin-top: 6px;
            color: var(--muted);
            font-size: 11px;
            word-break: break-all;
        }

        .status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 11px;
            border-radius: 999px;
            background: #D1FAE5;
            color: #047857;
            font-size: 10px;
            font-weight: 850;
            white-space: nowrap;
        }

        .status-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--primary);
        }

        .summary-grid {
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
            margin-bottom: 4px;
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
            padding: 21px;
            border: 1px solid var(--border);
            border-radius: 15px;
            background: white;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            align-items: flex-start;
            margin-bottom: 17px;
        }

        .card-title {
            font-size: 15px;
            font-weight: 850;
        }

        .card-description {
            margin-top: 4px;
            color: var(--muted);
            font-size: 10px;
            line-height: 1.5;
        }

        .edit-button {
            min-height: 34px;
            padding: 0 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: white;
            color: var(--primary);
            font-size: 9px;
            font-weight: 850;
            cursor: pointer;
        }

        .config-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 11px;
        }

        .config-item {
            padding: 12px;
            border: 1px solid #E7ECF3;
            border-radius: 10px;
            background: #FBFCFE;
        }

        .config-item.full {
            grid-column: 1 / -1;
        }

        .config-label {
            display: block;
            margin-bottom: 5px;
            color: #94A3B8;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .config-value {
            color: #334155;
            font-size: 11px;
            font-weight: 800;
        }

        .code-box {
            margin-top: 12px;
            padding: 14px;
            border-radius: 10px;
            background: #0F172A;
            color: #E2E8F0;
            font-family: monospace;
            font-size: 10px;
            line-height: 1.6;
            overflow-x: auto;
        }

        .execution {
            display: grid;
            grid-template-columns: 38px minmax(0, 1fr) auto;
            gap: 11px;
            align-items: center;
            padding: 13px 0;
            border-bottom: 1px solid #EEF2F7;
        }

        .execution:last-child {
            border-bottom: 0;
        }

        .execution-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-soft);
            color: var(--primary-dark);
            font-size: 9px;
            font-weight: 900;
        }

        .execution-title {
            color: #334155;
            font-size: 10px;
            font-weight: 800;
        }

        .execution-meta {
            margin-top: 3px;
            color: #94A3B8;
            font-size: 9px;
        }

        .badge {
            padding: 5px 8px;
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

        .error {
            display: flex;
            gap: 11px;
            padding: 13px 0;
            border-bottom: 1px solid #EEF2F7;
        }

        .error:last-child {
            border-bottom: 0;
        }

        .error-icon {
            width: 29px;
            height: 29px;
            flex: 0 0 29px;
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
            font-weight: 800;
        }

        .error-text {
            margin-top: 3px;
            color: #94A3B8;
            font-size: 9px;
            line-height: 1.45;
        }

        .state-box {
            padding: 15px;
            border-radius: 11px;
            background: #ECFDF5;
            border: 1px solid #A7F3D0;
        }

        .state-title {
            color: #047857;
            font-size: 11px;
            font-weight: 850;
        }

        .state-text {
            margin-top: 5px;
            color: #059669;
            font-size: 9px;
            line-height: 1.5;
        }

        .side-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 9px 0;
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

        .actions {
            display: flex;
            flex-direction: column;
            gap: 9px;
        }

        .action-button {
            width: 100%;
            min-height: 40px;
            border-radius: 9px;
            border: 1px solid var(--border);
            background: white;
            color: #475569;
            font-size: 10px;
            font-weight: 850;
            cursor: pointer;
        }

        .action-button.primary {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }

        .action-button.warning {
            background: #FFFBEB;
            border-color: #FDE68A;
            color: #92400E;
        }

        .visual-note {
            padding: 13px;
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

            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 650px) {
            .header-top {
                flex-direction: column;
            }

            .summary-grid,
            .config-grid {
                grid-template-columns: 1fr;
            }

            .config-item.full {
                grid-column: auto;
            }
        }
    
        .status.inactive {
            background: #F1F5F9;
            color: #64748B;
        }

        .status.inactive .status-dot {
            background: #94A3B8;
        }

        .state-box.inactive {
            background: #F8FAFC;
            border-color: #CBD5E1;
        }

        .state-box.inactive .state-title,
        .state-box.inactive .state-text {
            color: #64748B;
        }

        .execution-link,
        .error-link {
            color: inherit;
            text-decoration: none;
        }

        .execution-link:hover,
        .error-link:hover {
            background: #FAFFFC;
        }

        .button-link {
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .empty-state {
            padding: 26px 18px;
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

        .config-empty {
            padding: 16px;
            border: 1px dashed #CBD5E1;
            border-radius: 10px;
            background: #F8FAFC;
            color: #64748B;
            font-size: 10px;
            line-height: 1.6;
        }

        .badge-danger {
            background: #FEE2E2;
            color: #B91C1C;
        }

        .badge-neutral {
            background: #F1F5F9;
            color: #64748B;
        }

        .safe-url {
            color: inherit;
            text-decoration: none;
        }

        .safe-url:hover {
            color: var(--primary);
            text-decoration: underline;
        }

</style>

    <div class="source-manager">

        <a
            href="{{
                route(
                    'filament.admin.resources.fuentes.index'
                )
            }}"
            class="back-link"
        >
            ← Volver a Fuentes Web
        </a>


        <section class="header-card">

            <div class="header-top">

                <div>

                    <div class="eyebrow">
                        Gestión de fuente
                    </div>

                    <div class="title">
                        {{ $fuente->nombre }}
                    </div>

                    <div class="url">

                        @if ($urlSegura)

                            <a
                                href="{{ $urlSegura }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="safe-url"
                            >
                                {{ $fuente->url_base }}
                            </a>

                        @else

                            {{ $fuente->url_base }}

                        @endif

                    </div>

                </div>


                <div
                    class="
                        status
                        {{
                            $fuente->activa
                                ? ''
                                : 'inactive'
                        }}
                    "
                >
                    <span class="status-dot"></span>

                    {{
                        $fuente->activa
                            ? 'Fuente activa'
                            : 'Fuente inactiva'
                    }}
                </div>

            </div>


            <div class="summary-grid">

                <div class="summary-item">

                    <span class="summary-label">
                        Tipo
                    </span>

                    <span class="summary-value">
                        {{
                            match (
                                $fuente->tipo_fuente
                            ) {
                                'WEB' =>
                                    'Página web',

                                'API' =>
                                    'API',

                                'RSS' =>
                                    'RSS',

                                'OTRO' =>
                                    'Otro',

                                default =>
                                    $fuente->tipo_fuente
                                    ?? 'Sin definir',
                            }
                        }}
                    </span>

                </div>


                <div class="summary-item">

                    <span class="summary-label">
                        Frecuencia
                    </span>

                    <span class="summary-value">
                        @if (
                            $fuente
                                ->frecuencia_scraping
                            !== null
                        )

                            Cada
                            {{
                                number_format(
                                    $fuente
                                        ->frecuencia_scraping
                                )
                            }}
                            min

                        @else

                            Sin definir

                        @endif
                    </span>

                </div>


                <div class="summary-item">

                    <span class="summary-label">
                        JavaScript
                    </span>

                    <span class="summary-value">
                        {{
                            $fuente
                                ->requiere_javascript
                                ? 'Requerido'
                                : 'No requerido'
                        }}
                    </span>

                </div>


                <div class="summary-item">

                    <span class="summary-label">
                        Última ejecución
                    </span>

                    <span class="summary-value">

                        @if ($ultimaEjecucion)

                            {{
                                \Carbon\Carbon::parse(
                                    $ultimaEjecucion
                                )->format(
                                    'd/m/Y H:i'
                                )
                            }}

                        @else

                            Sin registro

                        @endif

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
                                Configuración de la fuente
                            </div>

                            <div class="card-description">
                                Parámetros utilizados por
                                el motor de extracción.
                            </div>

                        </div>

                        <a
                            href="{{
                                route(
                                    'filament.admin.resources.fuentes.edit',
                                    [
                                        'record' =>
                                            $fuente->id,
                                    ]
                                )
                            }}"
                            class="
                                edit-button
                                button-link
                            "
                        >
                            Editar configuración
                        </a>

                    </div>


                    <div class="config-grid">

                        <div class="config-item">

                            <span class="config-label">
                                Nombre
                            </span>

                            <span class="config-value">
                                {{ $fuente->nombre }}
                            </span>

                        </div>


                        <div class="config-item">

                            <span class="config-label">
                                Tipo de fuente
                            </span>

                            <span class="config-value">
                                {{
                                    $fuente->tipo_fuente
                                    ?? 'Sin definir'
                                }}
                            </span>

                        </div>


                        <div
                            class="
                                config-item
                                full
                            "
                        >

                            <span class="config-label">
                                Dirección web
                            </span>

                            <span class="config-value">
                                {{ $fuente->url_base }}
                            </span>

                        </div>


                        <div class="config-item">

                            <span class="config-label">
                                Frecuencia de consulta
                            </span>

                            <span class="config-value">
                                @if (
                                    $fuente
                                        ->frecuencia_scraping
                                    !== null
                                )

                                    {{
                                        number_format(
                                            $fuente
                                                ->frecuencia_scraping
                                        )
                                    }}
                                    minutos

                                @else

                                    Sin definir

                                @endif
                            </span>

                        </div>


                        <div class="config-item">

                            <span class="config-label">
                                Requiere JavaScript
                            </span>

                            <span class="config-value">
                                {{
                                    $fuente
                                        ->requiere_javascript
                                        ? 'Sí'
                                        : 'No'
                                }}
                            </span>

                        </div>

                    </div>

                </section>


                <section class="card">

                    <div class="card-header">

                        <div>

                            <div class="card-title">
                                Configuración del extractor
                            </div>

                            <div class="card-description">
                                Selectores y parámetros
                                registrados para esta fuente.
                            </div>

                        </div>

                    </div>


                    @if ($selectorJson)

                        <pre class="code-box">{{ $selectorJson }}</pre>

                    @else

                        <div class="config-empty">

                            <strong>
                                Sin configuración de selectores.
                            </strong>

                            <br>

                            Esta fuente todavía no tiene
                            información almacenada en
                            <code>selector_config</code>.

                        </div>

                    @endif

                </section>


                <section class="card">

                    <div class="card-header">

                        <div>

                            <div class="card-title">
                                Historial de ejecuciones
                            </div>

                            <div class="card-description">
                                Últimos intentos registrados
                                para esta fuente.
                            </div>

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

                            $badge =
                                match ($estado) {
                                    'COMPLETADO',
                                    'EXITOSA',
                                    'EXITOSO' =>
                                        'badge-success',

                                    'FALLIDO',
                                    'ERROR' =>
                                        'badge-danger',

                                    'INICIADO',
                                    'EJECUTANDO',
                                    'ADVERTENCIA',
                                    'COMPLETADO_CON_ERRORES' =>
                                        'badge-warning',

                                    default =>
                                        'badge-neutral',
                                };

                            $estadoTexto =
                                str_replace(
                                    '_',
                                    ' ',
                                    $estado !== ''
                                        ? $estado
                                        : 'SIN ESTADO'
                                );
                        @endphp


                        @if (
                            $ejecucion
                                ->ejecucion_scraping_id
                        )

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
                                                $ejecucion
                                                    ->ejecucion_scraping_id,
                                        ]
                                    )
                                }}"
                            >

                        @else

                            <div class="execution">

                        @endif


                            <div class="execution-icon">
                                BOT
                            </div>

                            <div>

                                <div class="execution-title">
                                    Ejecución
                                    #{{ $ejecucion->id }}
                                </div>

                                <div class="execution-meta">

                                    @if (
                                        $ejecucion->fecha_inicio
                                    )

                                        {{
                                            $ejecucion
                                                ->fecha_inicio
                                                ->format(
                                                    'd/m/Y H:i'
                                                )
                                        }}

                                    @else

                                        Sin fecha

                                    @endif

                                    ·
                                    {{
                                        $ejecucion
                                            ->registros_encontrados
                                        ?? 0
                                    }}
                                    encontrados

                                    ·
                                    {{
                                        $ejecucion
                                            ->registros_nuevos
                                        ?? 0
                                    }}
                                    nuevos

                                    ·
                                    {{
                                        $ejecucion->errores
                                        ?? 0
                                    }}
                                    errores

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


                        @if (
                            $ejecucion
                                ->ejecucion_scraping_id
                        )

                            </a>

                        @else

                            </div>

                        @endif


                    @empty

                        <div class="empty-state">

                            <div class="empty-title">
                                Sin ejecuciones registradas
                            </div>

                            Todavía no existen ejecuciones
                            del motor asociadas a esta fuente.

                        </div>

                    @endforelse

                </section>


                <section class="card">

                    <div class="card-header">

                        <div>

                            <div class="card-title">
                                Errores recientes
                            </div>

                            <div class="card-description">
                                Incidencias reales relacionadas
                                con esta fuente.
                            </div>

                        </div>

                    </div>


                    @forelse (
                        $errores
                        as $error
                    )

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
                            class="
                                error
                                error-link
                            "
                        >

                            <div class="error-icon">
                                !
                            </div>

                            <div>

                                <div class="error-title">
                                    {{
                                        $error->tipo_error
                                        ?? 'Error del sistema'
                                    }}
                                </div>

                                <div class="error-text">

                                    {{
                                        $error->mensaje
                                        ?: 'Sin descripción.'
                                    }}

                                    <br>

                                    {{
                                        $error->created_at
                                            ?->format(
                                                'd/m/Y H:i'
                                            )
                                        ?? 'Sin fecha'
                                    }}

                                    ·

                                    {{
                                        $error->resuelto
                                            ? 'Resuelto'
                                            : 'Pendiente'
                                    }}

                                </div>

                            </div>

                        </a>


                    @empty

                        <div class="empty-state">

                            <div class="empty-title">
                                Sin errores registrados
                            </div>

                            No existen incidencias asociadas
                            a esta fuente.

                        </div>

                    @endforelse

                </section>

            </main>


            <aside class="sidebar">


                <div
                    class="
                        state-box
                        {{
                            $fuente->activa
                                ? ''
                                : 'inactive'
                        }}
                    "
                >

                    <div class="state-title">

                        {{
                            $fuente->activa
                                ? 'Fuente disponible'
                                : 'Fuente desactivada'
                        }}

                    </div>

                    <div class="state-text">

                        @if ($fuente->activa)

                            La fuente está habilitada
                            para participar en los ciclos
                            automáticos del motor.

                        @else

                            La fuente no participará
                            en ciclos automáticos mientras
                            permanezca desactivada.

                        @endif

                    </div>

                </div>


                <section class="card">

                    <div
                        class="card-title"
                        style="margin-bottom:14px;"
                    >
                        Estado técnico
                    </div>


                    <div class="side-row">

                        <span>Estado</span>

                        <strong
                            style="
                                color:{{
                                    $fuente->activa
                                        ? '#059669'
                                        : '#64748B'
                                }};
                            "
                        >
                            {{
                                $fuente->activa
                                    ? 'Activa'
                                    : 'Inactiva'
                            }}
                        </strong>

                    </div>


                    <div class="side-row">
                        <span>Tipo</span>

                        <strong>
                            {{
                                $fuente->tipo_fuente
                                ?? '—'
                            }}
                        </strong>
                    </div>


                    <div class="side-row">
                        <span>JavaScript</span>

                        <strong>
                            {{
                                $fuente
                                    ->requiere_javascript
                                    ? 'Sí'
                                    : 'No'
                            }}
                        </strong>
                    </div>


                    <div class="side-row">

                        <span>Frecuencia</span>

                        <strong>
                            {{
                                $fuente
                                    ->frecuencia_scraping
                                !== null
                                    ? number_format(
                                        $fuente
                                            ->frecuencia_scraping
                                    ).' min'
                                    : '—'
                            }}
                        </strong>

                    </div>


                    <div class="side-row">

                        <span>Última consulta</span>

                        <strong>
                            {{
                                $ultimaEjecucion
                                    ? \Carbon\Carbon::parse(
                                        $ultimaEjecucion
                                    )->format(
                                        'd/m/Y H:i'
                                    )
                                    : 'Sin registro'
                            }}
                        </strong>

                    </div>


                    <div class="side-row">

                        <span>Ejecuciones</span>

                        <strong>
                            {{ $totalEjecuciones }}
                        </strong>

                    </div>


                    <div class="side-row">

                        <span>Errores totales</span>

                        <strong>
                            {{ $totalErrores }}
                        </strong>

                    </div>


                    <div class="side-row">

                        <span>Errores pendientes</span>

                        <strong>
                            {{ $erroresPendientes }}
                        </strong>

                    </div>

                </section>


                <section class="card">

                    <div
                        class="card-title"
                        style="margin-bottom:14px;"
                    >
                        Acciones
                    </div>


                    <div class="actions">

                        <a
                            href="{{
                                route(
                                    'filament.admin.pages.motor-extraccion',
                                    [
                                        'fuente' =>
                                            $fuente->id,
                                    ]
                                )
                            }}"
                            class="
                                action-button
                                primary
                                button-link
                            "
                        >
                            Ir al motor de extracción
                        </a>


                        <a
                            href="{{
                                route(
                                    'filament.admin.resources.fuentes.edit',
                                    [
                                        'record' =>
                                            $fuente->id,
                                    ]
                                )
                            }}"
                            class="
                                action-button
                                button-link
                            "
                        >
                            Editar fuente
                        </a>


                        @if ($urlSegura)

                            <a
                                href="{{ $urlSegura }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="
                                    action-button
                                    button-link
                                "
                            >
                                Abrir sitio web
                            </a>

                        @endif


                        <button
                            type="button"
                            class="
                                action-button
                                {{
                                    $fuente->activa
                                        ? 'warning'
                                        : 'primary'
                                }}
                            "
                            wire:click="alternarEstado"
                            wire:confirm="{{
                                $fuente->activa
                                    ? '¿Deseas desactivar esta fuente?'
                                    : '¿Deseas activar esta fuente?'
                            }}"
                        >
                            {{
                                $fuente->activa
                                    ? 'Desactivar fuente'
                                    : 'Activar fuente'
                            }}
                        </button>

                    </div>

                </section>


                <div class="visual-note">

                    Los datos de esta pantalla
                    provienen de PostgreSQL.

                    <br>

                    La ejecución del scraper se
                    administra desde el módulo
                    Motor de Extracción.

                </div>

            </aside>

        </div>

    </div>

</x-filament-panels::page>
