<x-filament-panels::page>

<style>
    .directivo-detail {
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

    .directivo-detail * {
        box-sizing: border-box;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 18px;
        color: var(--primary);
        font-size: 11px;
        font-weight: 800;
        text-decoration: none;
    }

    .hero {
        padding: 24px;
        margin-bottom: 20px;
        background: white;
        border: 1px solid var(--border);
        border-radius: 15px;
        box-shadow:
            0 1px 2px
            rgba(15, 24, 39, .03);
    }

    .hero-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
    }

    .organization {
        color: var(--primary);
        font-size: 10px;
        font-weight: 850;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .title {
        max-width: 850px;
        margin-top: 7px;
        color: var(--text);
        font-size: 22px;
        line-height: 1.35;
        font-weight: 900;
    }

    .status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 9px;
        border-radius: 999px;
        font-size: 9px;
        font-weight: 850;
        white-space: nowrap;
    }

    .status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
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

    .status-danger {
        background: #FEE2E2;
        color: #991B1B;
    }

    .status-neutral {
        background: #F1F5F9;
        color: #475569;
    }

    .hero-description {
        max-width: 950px;
        margin-top: 14px;
        color: var(--muted);
        font-size: 11px;
        line-height: 1.65;
    }

    .hero-meta {
        display: grid;
        grid-template-columns:
            repeat(4, minmax(0, 1fr));
        gap: 10px;
        margin-top: 19px;
    }

    .meta-box {
        padding: 11px;
        border: 1px solid #E7ECF3;
        border-radius: 9px;
        background: #F8FAFC;
    }

    .meta-label {
        display: block;
        margin-bottom: 4px;
        color: #94A3B8;
        font-size: 8px;
        text-transform: uppercase;
    }

    .meta-value {
        color: #334155;
        font-size: 10px;
        font-weight: 850;
    }

    .layout {
        display: grid;
        grid-template-columns:
            minmax(0, 1fr)
            290px;
        gap: 20px;
        align-items: start;
    }

    .main {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .card {
        padding: 20px;
        background: white;
        border: 1px solid var(--border);
        border-radius: 14px;
        box-shadow:
            0 1px 2px
            rgba(15, 24, 39, .03);
    }

    .card-title {
        margin-bottom: 13px;
        font-size: 13px;
        font-weight: 850;
    }

    .text {
        color: #64748B;
        font-size: 11px;
        line-height: 1.7;
        white-space: pre-line;
    }

    .muted-empty {
        padding: 12px;
        border-radius: 9px;
        background: #F8FAFC;
        color: #94A3B8;
        font-size: 9px;
    }

    .tags {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 13px;
    }

    .tag {
        padding: 5px 8px;
        border-radius: 999px;
        background: var(--primary-soft);
        color: var(--primary-dark);
        font-size: 8px;
        font-weight: 800;
    }

    .requirements {
        display: flex;
        flex-direction: column;
        gap: 9px;
    }

    .requirement {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        padding: 12px;
        border: 1px solid #E7ECF3;
        border-radius: 9px;
        background: #FBFCFE;
    }

    .requirement-icon {
        width: 24px;
        height: 24px;
        flex: 0 0 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: var(--primary-soft);
        color: var(--primary);
        font-size: 8px;
        font-weight: 900;
    }

    .requirement-title {
        color: #334155;
        font-size: 10px;
        font-weight: 800;
    }

    .requirement-description {
        margin-top: 3px;
        color: #64748B;
        font-size: 9px;
        line-height: 1.5;
    }

    .requirement-meta {
        margin-top: 5px;
        color: #94A3B8;
        font-size: 7px;
    }

    .documents {
        display: flex;
        flex-direction: column;
        gap: 9px;
    }

    .document {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 11px;
        border: 1px solid #E7ECF3;
        border-radius: 9px;
    }

    .document-info {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }

    .document-icon {
        width: 36px;
        height: 36px;
        flex: 0 0 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 9px;
        background: #FEE2E2;
        color: #B91C1C;
        font-size: 8px;
        font-weight: 900;
    }

    .document-name {
        color: #334155;
        font-size: 10px;
        font-weight: 800;
        overflow-wrap: anywhere;
    }

    .document-meta {
        margin-top: 3px;
        color: #94A3B8;
        font-size: 8px;
    }

    .document-btn {
        min-height: 32px;
        padding: 0 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--border);
        border-radius: 7px;
        background: white;
        color: var(--primary);
        font-size: 8px;
        font-weight: 850;
        text-decoration: none;
    }

    .sidebar {
        position: sticky;
        top: 90px;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .side-card {
        padding: 17px;
        background: white;
        border: 1px solid var(--border);
        border-radius: 13px;
    }

    .side-title {
        margin-bottom: 12px;
        font-size: 11px;
        font-weight: 850;
    }

    .side-row {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        padding: 8px 0;
        border-bottom: 1px solid #EEF2F7;
        font-size: 9px;
    }

    .side-row:last-child {
        border-bottom: 0;
    }

    .side-row span {
        color: var(--muted);
    }

    .side-row strong {
        max-width: 155px;
        color: #334155;
        text-align: right;
        overflow-wrap: anywhere;
    }

    .deadline-box {
        padding: 13px;
        border: 1px solid #FDE68A;
        border-radius: 9px;
        background: #FEF3C7;
        color: #92400E;
    }

    .deadline-label {
        font-size: 8px;
        font-weight: 800;
    }

    .deadline-date {
        margin-top: 4px;
        font-size: 15px;
        font-weight: 900;
    }

    .deadline-help {
        margin-top: 4px;
        font-size: 8px;
        line-height: 1.4;
    }

    .actions {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .btn {
        width: 100%;
        min-height: 38px;
        padding: 0 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: white;
        color: #475569;
        font-size: 9px;
        font-weight: 850;
        text-decoration: none;
    }

    .btn-primary {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
    }

    .source-box {
        padding: 13px;
        border: 1px solid #DDD6FE;
        border-radius: 9px;
        background: #F5F3FF;
    }

    .source-label {
        color: #8B5CF6;
        font-size: 7px;
        text-transform: uppercase;
        font-weight: 850;
    }

    .source-name {
        margin-top: 5px;
        color: #5B21B6;
        font-size: 10px;
        font-weight: 850;
    }

    .source-text {
        margin-top: 5px;
        color: #7C3AED;
        font-size: 8px;
        line-height: 1.45;
    }

    @media (max-width: 1000px) {
        .layout {
            grid-template-columns: 1fr;
        }

        .sidebar {
            position: static;
        }

        .hero-meta {
            grid-template-columns:
                repeat(2, 1fr);
        }
    }

    @media (max-width: 650px) {
        .hero-top {
            flex-direction: column;
        }

        .hero-meta {
            grid-template-columns: 1fr;
        }

        .document {
            flex-direction: column;
            align-items: flex-start;
        }

        .document-btn {
            width: 100%;
        }
    }
</style>

<div class="directivo-detail">

    <a
        href="{{
            route(
                'filament.directivo.pages.buscar-convocatorias'
            )
        }}"
        class="back-link"
    >
        ← Volver a resultados
    </a>

    <section class="hero">

        <div class="hero-top">

            <div>

                <div class="organization">
                    {{
                        $convocatoria
                            ->organismo
                            ?->nombre
                        ?? $convocatoria
                            ->fuente
                            ?->nombre
                        ?? 'Organismo no especificado'
                    }}
                </div>

                <div class="title">
                    {{
                        $convocatoria
                            ->titulo
                    }}
                </div>

            </div>

            <span
                class="
                    status
                    {{ $estadoClase }}
                "
            >
                <span class="status-dot"></span>

                {{ $estadoTexto }}
            </span>

        </div>

        <div class="hero-description">

            @if ($descripcionCorta !== '')

                {{ $descripcionCorta }}

            @else

                No existe una descripción
                registrada para esta convocatoria.

            @endif

        </div>

        <div class="hero-meta">

            <div class="meta-box">

                <span class="meta-label">
                    Fecha de apertura
                </span>

                <span class="meta-value">
                    {{
                        $fechaApertura
                            ?->format('d/m/Y')
                        ?? 'No especificada'
                    }}
                </span>

            </div>

            <div class="meta-box">

                <span class="meta-label">
                    Fecha de cierre
                </span>

                <span class="meta-value">
                    {{
                        $fechaCierre
                            ?->format('d/m/Y')
                        ?? 'No especificada'
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

    </section>

    <div class="layout">

        <main class="main">

            <section class="card">

                <div class="card-title">
                    Descripción de la convocatoria
                </div>

                @if (
                    trim(
                        (string)
                        $convocatoria
                            ->descripcion
                    ) !== ''
                )

                    <div class="text">
                        {{
                            $convocatoria
                                ->descripcion
                        }}
                    </div>

                @else

                    <div class="muted-empty">
                        No hay una descripción
                        registrada.
                    </div>

                @endif

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

            </section>

            <section class="card">

                <div class="card-title">
                    Objetivo
                </div>

                @if (
                    trim(
                        (string)
                        $convocatoria
                            ->objetivo
                    ) !== ''
                )

                    <div class="text">
                        {{
                            $convocatoria
                                ->objetivo
                        }}
                    </div>

                @else

                    <div class="muted-empty">
                        No hay un objetivo
                        registrado.
                    </div>

                @endif

            </section>

            <section class="card">

                <div class="card-title">
                    Requisitos principales
                </div>

                <div class="requirements">

                    @forelse (
                        $convocatoria
                            ->requisitos
                        as $requisito
                    )

                        <div class="requirement">

                            <span
                                class="
                                    requirement-icon
                                "
                            >
                                {{
                                    $loop
                                        ->iteration
                                }}
                            </span>

                            <div>

                                <div
                                    class="
                                        requirement-title
                                    "
                                >
                                    {{
                                        $requisito
                                            ->titulo
                                        ?: 'Requisito'
                                    }}
                                </div>

                                @if (
                                    trim(
                                        (string)
                                        $requisito
                                            ->descripcion
                                    ) !== ''
                                )

                                    <div
                                        class="
                                            requirement-description
                                        "
                                    >
                                        {{
                                            $requisito
                                                ->descripcion
                                        }}
                                    </div>

                                @endif

                                <div
                                    class="
                                        requirement-meta
                                    "
                                >
                                    {{
                                        $requisito
                                            ->obligatorio
                                            ? 'Obligatorio'
                                            : 'No obligatorio'
                                    }}

                                    @if (
                                        $requisito
                                            ->tipo_requisito
                                    )
                                        ·
                                        {{
                                            str_replace(
                                                '_',
                                                ' ',
                                                $requisito
                                                    ->tipo_requisito
                                            )
                                        }}
                                    @endif
                                </div>

                            </div>

                        </div>

                    @empty

                        <div class="muted-empty">
                            No hay requisitos
                            registrados para esta
                            convocatoria.
                        </div>

                    @endforelse

                </div>

            </section>

            <section class="card">

                <div class="card-title">
                    Documentos
                </div>

                <div class="documents">

                    @forelse (
                        $documentos
                        as $documento
                    )

                        <div class="document">

                            <div class="document-info">

                                <div
                                    class="
                                        document-icon
                                    "
                                >
                                    DOC
                                </div>

                                <div>

                                    <div
                                        class="
                                            document-name
                                        "
                                    >
                                        {{
                                            $documento[
                                                'nombre'
                                            ]
                                        }}
                                    </div>

                                    <div
                                        class="
                                            document-meta
                                        "
                                    >
                                        {{
                                            $documento[
                                                'tipo'
                                            ]
                                        }}
                                    </div>

                                </div>

                            </div>

                            @if (
                                $documento[
                                    'url'
                                ]
                            )

                                <a
                                    href="{{
                                        $documento[
                                            'url'
                                        ]
                                    }}"
                                    target="_blank"
                                    rel="
                                        noopener
                                        noreferrer
                                    "
                                    class="
                                        document-btn
                                    "
                                >
                                    Ver documento
                                </a>

                            @else

                                <span
                                    class="
                                        document-meta
                                    "
                                >
                                    Sin enlace público
                                </span>

                            @endif

                        </div>

                    @empty

                        <div class="muted-empty">
                            No hay documentos
                            registrados para esta
                            convocatoria.
                        </div>

                    @endforelse

                </div>

            </section>

        </main>

        <aside class="sidebar">

            <section class="side-card">

                <div class="side-title">
                    Información general
                </div>

                <div class="side-row">

                    <span>
                        Organismo
                    </span>

                    <strong>
                        {{
                            $convocatoria
                                ->organismo
                                ?->nombre
                            ?? 'No especificado'
                        }}
                    </strong>

                </div>

                <div class="side-row">

                    <span>
                        Área
                    </span>

                    <strong>
                        {{
                            $convocatoria
                                ->categoria
                                ?->nombre
                            ?? 'No especificada'
                        }}
                    </strong>

                </div>

                <div class="side-row">

                    <span>
                        Modalidad
                    </span>

                    <strong>
                        {{
                            $convocatoria
                                ->modalidad
                            ?: 'No especificada'
                        }}
                    </strong>

                </div>

                <div class="side-row">

                    <span>
                        Cobertura
                    </span>

                    <strong>
                        {{
                            $convocatoria
                                ->ubicacion
                            ?: 'No especificada'
                        }}
                    </strong>

                </div>

                <div class="side-row">

                    <span>
                        Moneda
                    </span>

                    <strong>
                        {{
                            $convocatoria
                                ->moneda
                            ?: 'No especificada'
                        }}
                    </strong>

                </div>

                <div class="side-row">

                    <span>
                        Origen
                    </span>

                    <strong>
                        {{
                            $convocatoria
                                ->origen
                            ?: 'No especificado'
                        }}
                    </strong>

                </div>

            </section>

            <div class="deadline-box">

                <div class="deadline-label">
                    Fecha límite
                </div>

                <div class="deadline-date">
                    {{
                        $fechaCierre
                            ?->format(
                                'd/m/Y'
                            )
                        ?? 'Sin fecha'
                    }}
                </div>

                <div class="deadline-help">

                    @if (
                        $fechaCierre
                        && $fechaCierre
                            ->isPast()
                    )

                        La fecha registrada ya
                        ha vencido.

                    @elseif ($fechaCierre)

                        Verifica la fecha en la
                        fuente oficial antes de
                        participar.

                    @else

                        Esta convocatoria no tiene
                        fecha de cierre registrada.

                    @endif

                </div>

            </div>

            <section class="side-card">

                <div class="side-title">
                    Acciones
                </div>

                <div class="actions">

                    @if ($urlOficial)

                        <a
                            href="{{ $urlOficial }}"
                            target="_blank"
                            rel="
                                noopener
                                noreferrer
                            "
                            class="
                                btn
                                btn-primary
                            "
                        >
                            Abrir fuente oficial
                        </a>

                    @endif

                    <a
                        href="{{
                            route(
                                'filament.directivo.pages.buscar-convocatorias'
                            )
                        }}"
                        class="btn"
                    >
                        Volver a búsqueda
                    </a>

                </div>

            </section>

            <div class="source-box">

                <div class="source-label">
                    Fuente de información
                </div>

                <div class="source-name">
                    {{
                        $convocatoria
                            ->fuente
                            ?->nombre
                        ?? 'Sin fuente registrada'
                    }}
                </div>

                <div class="source-text">
                    Información almacenada
                    actualmente en PostgreSQL para
                    esta convocatoria.
                </div>

            </div>

        </aside>

    </div>

</div>

</x-filament-panels::page>
