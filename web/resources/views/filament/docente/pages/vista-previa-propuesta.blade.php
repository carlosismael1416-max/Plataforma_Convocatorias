<x-filament-panels::page>

<style>
    .proposal-preview {
        --text: #0F1827;
        --muted: #64748B;
        --primary: #1A4B8C;
        --border: #DDE3EE;
        --success: #059669;
        --warning: #D97706;
        --danger: #DC2626;

        color: var(--text);
    }

    .proposal-preview * {
        box-sizing: border-box;
    }

    .preview-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
        margin-bottom: 22px;
    }

    .preview-title {
        margin: 0;
        font-size: 25px;
        font-weight: 800;
    }

    .preview-subtitle {
        margin-top: 7px;
        color: var(--muted);
        font-size: 13px;
    }

    .top-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .btn,
    .edit-link,
    .file-btn {
        border: 1px solid var(--border);
        border-radius: 8px;
        background: white;
        color: var(--text);
        cursor: pointer;
        text-decoration: none;
        font-weight: 700;
    }

    .btn {
        min-height: 40px;
        padding: 0 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
    }

    .btn-primary {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
    }

    .edit-link,
    .file-btn {
        min-height: 29px;
        padding: 0 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 9px;
    }

    .layout {
        display: grid;
        grid-template-columns:
            minmax(0, 1fr)
            290px;
        gap: 20px;
        align-items: start;
    }

    .document {
        overflow: hidden;
        background: white;
        border: 1px solid var(--border);
        border-radius: 14px;
    }

    .document-header {
        padding: 26px;
        border-bottom: 1px solid var(--border);
        background: #F8FAFC;
    }

    .institution {
        color: var(--primary);
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
    }

    .proposal-name {
        max-width: 820px;
        margin-top: 10px;
        font-size: 22px;
        font-weight: 850;
        line-height: 1.3;
    }

    .proposal-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 15px;
        color: var(--muted);
        font-size: 10px;
    }

    .status-badge {
        padding: 5px 9px;
        border-radius: 999px;
        background: #FEF3C7;
        color: #92400E;
        font-size: 9px;
        font-weight: 800;
    }

    .document-body {
        padding: 26px;
    }

    .section {
        padding-bottom: 24px;
        margin-bottom: 24px;
        border-bottom: 1px solid #E8EDF4;
    }

    .section:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: 0;
    }

    .section-heading {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 14px;
    }

    .section-title {
        display: flex;
        align-items: center;
        gap: 9px;
        margin: 0;
        font-size: 15px;
        font-weight: 800;
    }

    .section-number {
        width: 28px;
        height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: #E8EEF8;
        color: var(--primary);
        font-size: 10px;
        font-weight: 900;
    }

    .text {
        color: #475569;
        font-size: 11px;
        line-height: 1.7;
        white-space: pre-line;
    }

    .data-grid {
        display: grid;
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .data-box {
        padding: 12px;
        border: 1px solid #E7ECF3;
        border-radius: 9px;
        background: #F8FAFC;
    }

    .data-label {
        display: block;
        margin-bottom: 4px;
        color: #94A3B8;
        font-size: 9px;
    }

    .data-value {
        color: #334155;
        font-size: 10px;
        font-weight: 800;
    }

    .list {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .list-item {
        display: flex;
        gap: 9px;
        align-items: flex-start;
        padding: 11px;
        border: 1px solid #E7ECF3;
        border-radius: 9px;
        background: #FBFCFE;
    }

    .bullet {
        width: 22px;
        height: 22px;
        flex: 0 0 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #E8EEF8;
        color: var(--primary);
        font-size: 8px;
        font-weight: 900;
    }

    .bullet.ok {
        background: #ECFDF5;
        color: var(--success);
    }

    .bullet.pending {
        background: #FEF3C7;
        color: #92400E;
    }

    .item-title {
        color: #334155;
        font-size: 10px;
        font-weight: 800;
    }

    .item-sub {
        margin-top: 3px;
        color: #64748B;
        font-size: 9px;
        line-height: 1.5;
    }

    .table-wrap {
        overflow-x: auto;
        border: 1px solid var(--border);
        border-radius: 10px;
    }

    .preview-table {
        width: 100%;
        min-width: 660px;
        border-collapse: collapse;
    }

    .preview-table th {
        padding: 10px 11px;
        background: #F8FAFC;
        border-bottom: 1px solid var(--border);
        color: #64748B;
        font-size: 9px;
        text-align: left;
    }

    .preview-table td {
        padding: 10px 11px;
        border-bottom: 1px solid #EEF2F7;
        color: #334155;
        font-size: 9px;
        vertical-align: top;
    }

    .preview-table tr:last-child td {
        border-bottom: 0;
    }

    .totals {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 6px;
        margin-top: 12px;
    }

    .total-box {
        width: min(100%, 330px);
        padding: 11px 13px;
        display: flex;
        justify-content: space-between;
        gap: 15px;
        border: 1px solid #DBEAFE;
        border-radius: 9px;
        background: #EFF6FF;
        color: #1E3A8A;
        font-size: 10px;
        font-weight: 800;
    }

    .timeline {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .timeline-item {
        display: grid;
        grid-template-columns:
            34px minmax(0, 1fr);
        gap: 10px;
    }

    .timeline-number {
        width: 34px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 9px;
        background: #E8EEF8;
        color: var(--primary);
        font-size: 9px;
        font-weight: 900;
    }

    .timeline-content {
        padding: 10px 12px;
        border: 1px solid #E7ECF3;
        border-radius: 9px;
        background: #FBFCFE;
    }

    .timeline-title {
        font-size: 10px;
        font-weight: 800;
    }

    .timeline-date {
        margin-top: 3px;
        color: var(--muted);
        font-size: 9px;
    }

    .files {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 7px;
    }

    .empty {
        padding: 18px;
        border: 1px dashed #CBD5E1;
        border-radius: 9px;
        color: var(--muted);
        font-size: 10px;
        text-align: center;
    }

    .sidebar {
        position: sticky;
        top: 90px;
        padding: 18px;
        background: white;
        border: 1px solid var(--border);
        border-radius: 14px;
    }

    .sidebar-title {
        margin-bottom: 14px;
        font-size: 12px;
        font-weight: 800;
    }

    .progress-header {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 7px;
        font-size: 10px;
        font-weight: 800;
    }

    .progress-track {
        height: 7px;
        overflow: hidden;
        border-radius: 999px;
        background: #E9EEF6;
    }

    .progress-bar {
        height: 100%;
        border-radius: 999px;
        background: var(--success);
    }

    .review-box {
        margin-top: 14px;
        padding: 11px;
        border-radius: 9px;
        font-size: 9px;
        line-height: 1.5;
    }

    .review-ok {
        border: 1px solid #A7F3D0;
        background: #ECFDF5;
        color: #065F46;
    }

    .review-pending {
        border: 1px solid #FDE68A;
        background: #FFFBEB;
        color: #92400E;
    }

    .summary {
        margin-top: 17px;
        padding-top: 15px;
        border-top: 1px solid var(--border);
    }

    .summary-title {
        margin-bottom: 10px;
        color: #64748B;
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 8px;
        font-size: 9px;
    }

    .summary-row span {
        color: var(--muted);
    }

    .summary-row strong {
        color: #334155;
    }

    .side-actions {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-top: 16px;
    }

    .side-actions .btn {
        width: 100%;
    }

    @media print {
        .fi-sidebar,
        .fi-topbar,
        .preview-top,
        .sidebar,
        .edit-link,
        .file-btn {
            display: none !important;
        }

        .layout {
            display: block;
        }

        .document {
            border: 0;
        }

        .proposal-preview {
            color: #000;
        }
    }

    @media (max-width: 1000px) {
        .layout {
            grid-template-columns: 1fr;
        }

        .sidebar {
            position: static;
        }
    }

    @media (max-width: 650px) {
        .preview-top {
            flex-direction: column;
        }

        .data-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="proposal-preview">

    <div class="preview-top">

        <div>

            <h1 class="preview-title">
                Vista Previa de la Propuesta
            </h1>

            <div class="preview-subtitle">
                Revisa toda la información registrada
                antes de continuar con el proceso.
            </div>

        </div>

        <div class="top-actions">

            <a
                class="btn"
                href="{{ route(
                    'filament.docente.pages.editor-propuesta',
                    [
                        'propuesta' =>
                            $propuesta->id,
                    ]
                ) }}"
            >
                ← Volver a editar
            </a>

            <button
                type="button"
                class="btn btn-primary"
                wire:click="exportarPdf"
                wire:loading.attr="disabled"
                wire:target="exportarPdf"
            >
                Exportar PDF
            </button>

            <button
                type="button"
                class="btn"
                wire:click="exportarExcel"
                wire:loading.attr="disabled"
                wire:target="exportarExcel"
            >
                Exportar Excel
            </button>

        </div>

    </div>

    <div class="layout">

        <main class="document">

            <div class="document-header">

                <div class="institution">
                    Instituto Tecnológico Superior
                    de Valladolid
                </div>

                <div class="proposal-name">
                    {{ $propuesta->titulo }}
                </div>

                <div class="proposal-meta">

                    <span>
                        Convocatoria:
                        {{
                            $propuesta
                                ->convocatoria
                                ?->titulo
                            ?? 'Sin convocatoria'
                        }}
                    </span>

                    <span>
                        Responsable:
                        {{ $responsable }}
                    </span>

                    <span>
                        Versión:
                        {{ $propuesta->version }}
                    </span>

                    <span class="status-badge">
                        {{
                            str_replace(
                                '_',
                                ' ',
                                $propuesta->estado
                            )
                        }}
                    </span>

                </div>

            </div>

            <div class="document-body">

                {{-- DATOS GENERALES --}}
                <section class="section">

                    <div class="section-heading">

                        <h2 class="section-title">
                            <span class="section-number">
                                1
                            </span>
                            Datos generales
                        </h2>

                        <a
                            class="edit-link"
                            href="{{ route(
                                'filament.docente.pages.editor-propuesta',
                                [
                                    'propuesta' =>
                                        $propuesta->id,
                                ]
                            ) }}"
                        >
                            Editar
                        </a>

                    </div>

                    <div class="data-grid">

                        <div class="data-box">
                            <span class="data-label">
                                Área temática
                            </span>

                            <span class="data-value">
                                {{
                                    $propuesta
                                        ->convocatoria
                                        ?->categoria
                                        ?->nombre
                                    ?? 'No especificada'
                                }}
                            </span>
                        </div>

                        <div class="data-box">
                            <span class="data-label">
                                Organismo
                            </span>

                            <span class="data-value">
                                {{
                                    $propuesta
                                        ->convocatoria
                                        ?->organismo
                                        ?->nombre
                                    ?? 'No especificado'
                                }}
                            </span>
                        </div>

                        <div class="data-box">
                            <span class="data-label">
                                Inicio estimado
                            </span>

                            <span class="data-value">
                                {{
                                    $inicioProyecto
                                        ?->format('d/m/Y')
                                    ?? 'Sin definir'
                                }}
                            </span>
                        </div>

                        <div class="data-box">
                            <span class="data-label">
                                Fin estimado
                            </span>

                            <span class="data-value">
                                {{
                                    $finProyecto
                                        ?->format('d/m/Y')
                                    ?? 'Sin definir'
                                }}
                            </span>
                        </div>

                        <div class="data-box">
                            <span class="data-label">
                                Duración
                            </span>

                            <span class="data-value">
                                {{
                                    $duracionDias !== null
                                        ? $duracionDias
                                            . ' día(s)'
                                        : 'Sin definir'
                                }}
                            </span>
                        </div>

                        <div class="data-box">
                            <span class="data-label">
                                Estado
                            </span>

                            <span class="data-value">
                                {{
                                    str_replace(
                                        '_',
                                        ' ',
                                        $propuesta->estado
                                    )
                                }}
                            </span>
                        </div>

                    </div>

                </section>

                {{-- RESUMEN --}}
                <section class="section">

                    <div class="section-heading">

                        <h2 class="section-title">
                            <span class="section-number">
                                2
                            </span>
                            Resumen ejecutivo
                        </h2>

                        <a
                            class="edit-link"
                            href="{{ route(
                                'filament.docente.pages.editor-propuesta',
                                [
                                    'propuesta' =>
                                        $propuesta->id,
                                ]
                            ) }}"
                        >
                            Editar
                        </a>

                    </div>

                    <div class="text">
                        {{
                            $propuesta->resumen
                            ?: 'Sin información registrada.'
                        }}
                    </div>

                </section>

                {{-- JUSTIFICACIÓN --}}
                <section class="section">

                    <div class="section-heading">

                        <h2 class="section-title">
                            <span class="section-number">
                                3
                            </span>
                            Justificación
                        </h2>

                        <a
                            class="edit-link"
                            href="{{ route(
                                'filament.docente.pages.editor-propuesta',
                                [
                                    'propuesta' =>
                                        $propuesta->id,
                                ]
                            ) }}"
                        >
                            Editar
                        </a>

                    </div>

                    <div class="text">
                        {{
                            $propuesta->justificacion
                            ?: 'Sin información registrada.'
                        }}
                    </div>

                </section>

                {{-- METODOLOGÍA --}}
                <section class="section">

                    <div class="section-heading">

                        <h2 class="section-title">
                            <span class="section-number">
                                4
                            </span>
                            Metodología
                        </h2>

                        <a
                            class="edit-link"
                            href="{{ route(
                                'filament.docente.pages.editor-propuesta',
                                [
                                    'propuesta' =>
                                        $propuesta->id,
                                ]
                            ) }}"
                        >
                            Editar
                        </a>

                    </div>

                    <div class="text">
                        {{
                            $propuesta->metodologia
                            ?: 'Sin información registrada.'
                        }}
                    </div>

                </section>

                {{-- IMPACTO --}}
                <section class="section">

                    <div class="section-heading">

                        <h2 class="section-title">
                            <span class="section-number">
                                5
                            </span>
                            Impacto esperado
                        </h2>

                        <a
                            class="edit-link"
                            href="{{ route(
                                'filament.docente.pages.editor-propuesta',
                                [
                                    'propuesta' =>
                                        $propuesta->id,
                                ]
                            ) }}"
                        >
                            Editar
                        </a>

                    </div>

                    <div class="text">
                        {{
                            $propuesta
                                ->impacto_esperado
                            ?: 'Sin información registrada.'
                        }}
                    </div>

                </section>

                {{-- OBJETIVOS --}}
                <section class="section">

                    <div class="section-heading">

                        <h2 class="section-title">
                            <span class="section-number">
                                6
                            </span>
                            Objetivos
                        </h2>

                        <a
                            class="edit-link"
                            href="{{ route(
                                'filament.docente.pages.objetivos-propuesta',
                                [
                                    'propuesta' =>
                                        $propuesta->id,
                                ]
                            ) }}"
                        >
                            Editar
                        </a>

                    </div>

                    <div
                        class="text"
                        style="margin-bottom:12px;"
                    >
                        <strong>
                            Objetivo general
                        </strong>

                        <br>

                        {{
                            $objetivoGeneral
                                ?->descripcion
                            ?? 'No registrado.'
                        }}
                    </div>

                    <div class="list">

                        @forelse (
                            $objetivosEspecificos
                            as $indice => $objetivo
                        )

                            <div class="list-item">

                                <span class="bullet">
                                    {{ $indice + 1 }}
                                </span>

                                <div class="text">
                                    {{
                                        $objetivo
                                            ->descripcion
                                    }}
                                </div>

                            </div>

                        @empty

                            <div class="empty">
                                No hay objetivos específicos.
                            </div>

                        @endforelse

                    </div>

                </section>

                {{-- REQUISITOS --}}
                <section class="section">

                    <div class="section-heading">

                        <h2 class="section-title">
                            <span class="section-number">
                                7
                            </span>
                            Requisitos
                        </h2>

                        <a
                            class="edit-link"
                            href="{{ route(
                                'filament.docente.pages.requisitos-propuesta',
                                [
                                    'propuesta' =>
                                        $propuesta->id,
                                ]
                            ) }}"
                        >
                            Editar
                        </a>

                    </div>

                    <div class="list">

                        @forelse (
                            $requisitos
                            as $requisito
                        )

                            @php
                                $archivosRequisito =
                                    collect(
                                        $evidenciasPorRequisito
                                            ->get(
                                                $requisito->id,
                                                []
                                            )
                                    );
                            @endphp

                            <div class="list-item">

                                <span
                                    class="
                                        bullet
                                        {{
                                            $requisito
                                                ->cumplido
                                                ? 'ok'
                                                : 'pending'
                                        }}
                                    "
                                >
                                    {{
                                        $requisito
                                            ->cumplido
                                            ? '✓'
                                            : '!'
                                    }}
                                </span>

                                <div>

                                    <div class="item-title">
                                        {{
                                            $requisito
                                                ->requisitoConvocatoria
                                                ?->titulo
                                            ?? 'Requisito'
                                        }}
                                    </div>

                                    @if (
                                        $requisito
                                            ->observaciones
                                    )

                                        <div class="item-sub">
                                            {{
                                                $requisito
                                                    ->observaciones
                                            }}
                                        </div>

                                    @endif

                                    @if (
                                        $archivosRequisito
                                            ->isNotEmpty()
                                    )

                                        <div class="files">

                                            @foreach (
                                                $archivosRequisito
                                                as $evidencia
                                            )

                                                @if (
                                                    $evidencia
                                                        ->ruta_archivo
                                                )

                                                    <button
                                                        type="button"
                                                        class="file-btn"
                                                        wire:click="
                                                            descargarEvidencia(
                                                                {{
                                                                    $evidencia
                                                                        ->id
                                                                }}
                                                            )
                                                        "
                                                    >
                                                        📎
                                                        {{
                                                            $evidencia
                                                                ->nombre
                                                        }}
                                                    </button>

                                                @endif

                                            @endforeach

                                        </div>

                                    @endif

                                </div>

                            </div>

                        @empty

                            <div class="empty">
                                La propuesta no tiene
                                requisitos registrados.
                            </div>

                        @endforelse

                    </div>

                </section>

                {{-- PRESUPUESTO --}}
                <section class="section">

                    <div class="section-heading">

                        <h2 class="section-title">
                            <span class="section-number">
                                8
                            </span>
                            Presupuesto
                        </h2>

                        <a
                            class="edit-link"
                            href="{{ route(
                                'filament.docente.pages.presupuesto-propuesta',
                                [
                                    'propuesta' =>
                                        $propuesta->id,
                                ]
                            ) }}"
                        >
                            Editar
                        </a>

                    </div>

                    @if ($presupuesto->isNotEmpty())

                        <div class="table-wrap">

                            <table class="preview-table">

                                <thead>
                                    <tr>
                                        <th>Concepto</th>
                                        <th>Categoría</th>
                                        <th>Cantidad</th>
                                        <th>Precio unitario</th>
                                        <th>Subtotal</th>
                                        <th>Moneda</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    @foreach (
                                        $presupuesto
                                        as $item
                                    )

                                        <tr>

                                            <td>
                                                {{ $item->concepto }}
                                            </td>

                                            <td>
                                                {{
                                                    str_replace(
                                                        '_',
                                                        ' ',
                                                        $item
                                                            ->categoria_gasto
                                                        ?? '—'
                                                    )
                                                }}
                                            </td>

                                            <td>
                                                {{
                                                    number_format(
                                                        (float)
                                                        $item->cantidad,
                                                        2
                                                    )
                                                }}
                                            </td>

                                            <td>
                                                $
                                                {{
                                                    number_format(
                                                        (float)
                                                        $item
                                                            ->precio_unitario,
                                                        2
                                                    )
                                                }}
                                            </td>

                                            <td>
                                                $
                                                {{
                                                    number_format(
                                                        (float)
                                                        $item->cantidad
                                                        *
                                                        (float)
                                                        $item
                                                            ->precio_unitario,
                                                        2
                                                    )
                                                }}
                                            </td>

                                            <td>
                                                {{
                                                    $item->moneda
                                                    ?: 'MXN'
                                                }}
                                            </td>

                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                        <div class="totals">

                            @foreach (
                                $totalesPorMoneda
                                as $moneda => $total
                            )

                                <div class="total-box">

                                    <span>
                                        Total solicitado
                                    </span>

                                    <span>
                                        $
                                        {{
                                            number_format(
                                                $total,
                                                2
                                            )
                                        }}
                                        {{ $moneda }}
                                    </span>

                                </div>

                            @endforeach

                        </div>

                    @else

                        <div class="empty">
                            No hay conceptos de presupuesto.
                        </div>

                    @endif

                </section>

                {{-- COTIZACIONES --}}
                <section class="section">

                    <div class="section-heading">

                        <h2 class="section-title">
                            <span class="section-number">
                                9
                            </span>
                            Cotizaciones
                        </h2>

                        <a
                            class="edit-link"
                            href="{{ route(
                                'filament.docente.pages.cotizaciones-propuesta',
                                [
                                    'propuesta' =>
                                        $propuesta->id,
                                ]
                            ) }}"
                        >
                            Editar
                        </a>

                    </div>

                    @if ($cotizaciones->isNotEmpty())

                        <div class="table-wrap">

                            <table class="preview-table">

                                <thead>
                                    <tr>
                                        <th>Concepto</th>
                                        <th>Proveedor</th>
                                        <th>Monto</th>
                                        <th>Fecha</th>
                                        <th>Documento</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    @foreach (
                                        $cotizaciones
                                        as $cotizacion
                                    )

                                        @php
                                            $archivoExterno =
                                                filled(
                                                    $cotizacion
                                                        ->archivo_url
                                                )
                                                &&
                                                (
                                                    str_starts_with(
                                                        strtolower(
                                                            $cotizacion
                                                                ->archivo_url
                                                        ),
                                                        'http://'
                                                    )
                                                    ||
                                                    str_starts_with(
                                                        strtolower(
                                                            $cotizacion
                                                                ->archivo_url
                                                        ),
                                                        'https://'
                                                    )
                                                );
                                        @endphp

                                        <tr>

                                            <td>
                                                {{
                                                    $cotizacion
                                                        ->concepto
                                                    ?: '—'
                                                }}
                                            </td>

                                            <td>
                                                {{
                                                    $cotizacion
                                                        ->proveedor
                                                    ?: '—'
                                                }}
                                            </td>

                                            <td>
                                                $
                                                {{
                                                    number_format(
                                                        (float)
                                                        $cotizacion
                                                            ->monto,
                                                        2
                                                    )
                                                }}
                                                {{
                                                    $cotizacion
                                                        ->moneda
                                                }}
                                            </td>

                                            <td>
                                                {{
                                                    $cotizacion
                                                        ->fecha_cotizacion
                                                        ?->format(
                                                            'd/m/Y'
                                                        )
                                                    ?? '—'
                                                }}
                                            </td>

                                            <td>

                                                @if (
                                                    blank(
                                                        $cotizacion
                                                            ->archivo_url
                                                    )
                                                )

                                                    Pendiente

                                                @elseif (
                                                    $archivoExterno
                                                )

                                                    <a
                                                        class="file-btn"
                                                        href="{{
                                                            $cotizacion
                                                                ->archivo_url
                                                        }}"
                                                        target="_blank"
                                                        rel="
                                                            noopener
                                                            noreferrer
                                                        "
                                                    >
                                                        Abrir
                                                    </a>

                                                @else

                                                    <button
                                                        type="button"
                                                        class="file-btn"
                                                        wire:click="
                                                            descargarCotizacion(
                                                                {{
                                                                    $cotizacion
                                                                        ->id
                                                                }}
                                                            )
                                                        "
                                                    >
                                                        Descargar
                                                    </button>

                                                @endif

                                            </td>

                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                    @else

                        <div class="empty">
                            No se registraron cotizaciones.
                            Esta sección puede no aplicar
                            a todos los proyectos.
                        </div>

                    @endif

                </section>

                {{-- CRONOGRAMA --}}
                <section class="section">

                    <div class="section-heading">

                        <h2 class="section-title">
                            <span class="section-number">
                                10
                            </span>
                            Cronograma
                        </h2>

                        <a
                            class="edit-link"
                            href="{{ route(
                                'filament.docente.pages.cronograma-propuesta',
                                [
                                    'propuesta' =>
                                        $propuesta->id,
                                ]
                            ) }}"
                        >
                            Editar
                        </a>

                    </div>

                    <div class="timeline">

                        @forelse (
                            $actividades
                            as $indice => $actividad
                        )

                            <div class="timeline-item">

                                <div class="timeline-number">
                                    {{
                                        str_pad(
                                            (string)
                                            ($indice + 1),
                                            2,
                                            '0',
                                            STR_PAD_LEFT
                                        )
                                    }}
                                </div>

                                <div class="timeline-content">

                                    <div class="timeline-title">
                                        {{
                                            $actividad
                                                ->actividad
                                        }}
                                    </div>

                                    <div class="timeline-date">
                                        {{
                                            $actividad
                                                ->fecha_inicio
                                                ?->format(
                                                    'd/m/Y'
                                                )
                                        }}
                                        —
                                        {{
                                            $actividad
                                                ->fecha_fin
                                                ?->format(
                                                    'd/m/Y'
                                                )
                                        }}
                                        ·
                                        {{
                                            $actividad
                                                ->responsable
                                            ?: 'Sin responsable'
                                        }}
                                        ·
                                        {{
                                            str_replace(
                                                '_',
                                                ' ',
                                                $actividad
                                                    ->estado
                                            )
                                        }}
                                        ·
                                        {{
                                            number_format(
                                                (float)
                                                $actividad
                                                    ->porcentaje_avance,
                                                0
                                            )
                                        }}%
                                    </div>

                                    @if (
                                        $actividad
                                            ->descripcion
                                    )

                                        <div class="item-sub">
                                            {{
                                                $actividad
                                                    ->descripcion
                                            }}
                                        </div>

                                    @endif

                                </div>

                            </div>

                        @empty

                            <div class="empty">
                                No hay actividades
                                registradas.
                            </div>

                        @endforelse

                    </div>

                </section>

                {{-- ENTREGABLES --}}
                <section class="section">

                    <div class="section-heading">

                        <h2 class="section-title">
                            <span class="section-number">
                                11
                            </span>
                            Entregables
                        </h2>

                        <a
                            class="edit-link"
                            href="{{ route(
                                'filament.docente.pages.entregables-propuesta',
                                [
                                    'propuesta' =>
                                        $propuesta->id,
                                ]
                            ) }}"
                        >
                            Editar
                        </a>

                    </div>

                    <div class="list">

                        @forelse (
                            $entregables
                            as $entregable
                        )

                            @php
                                $archivosEntregable =
                                    collect(
                                        $evidenciasPorEntregable
                                            ->get(
                                                $entregable
                                                    ->id,
                                                []
                                            )
                                    );
                            @endphp

                            <div class="list-item">

                                <span
                                    class="
                                        bullet
                                        {{
                                            in_array(
                                                $entregable
                                                    ->estado,
                                                [
                                                    'ENTREGADO',
                                                    'APROBADO',
                                                ],
                                                true
                                            )
                                                ? 'ok'
                                                : 'pending'
                                        }}
                                    "
                                >
                                    {{
                                        in_array(
                                            $entregable
                                                ->estado,
                                            [
                                                'ENTREGADO',
                                                'APROBADO',
                                            ],
                                            true
                                        )
                                            ? '✓'
                                            : '!'
                                    }}
                                </span>

                                <div>

                                    <div class="item-title">
                                        {{
                                            $entregable
                                                ->nombre
                                        }}
                                    </div>

                                    <div class="item-sub">
                                        Estado:
                                        {{
                                            str_replace(
                                                '_',
                                                ' ',
                                                $entregable
                                                    ->estado
                                            )
                                        }}

                                        · Fecha límite:
                                        {{
                                            $entregable
                                                ->fecha_limite
                                                ?->format(
                                                    'd/m/Y'
                                                )
                                            ?? 'Sin definir'
                                        }}

                                        @if (
                                            $entregable
                                                ->actividad
                                        )
                                            · Actividad:
                                            {{
                                                $entregable
                                                    ->actividad
                                                    ->actividad
                                            }}
                                        @endif
                                    </div>

                                    @if (
                                        $entregable
                                            ->descripcion
                                    )

                                        <div class="item-sub">
                                            {{
                                                $entregable
                                                    ->descripcion
                                            }}
                                        </div>

                                    @endif

                                    @if (
                                        $archivosEntregable
                                            ->isNotEmpty()
                                    )

                                        <div class="files">

                                            @foreach (
                                                $archivosEntregable
                                                as $evidencia
                                            )

                                                @if (
                                                    $evidencia
                                                        ->ruta_archivo
                                                )

                                                    <button
                                                        type="button"
                                                        class="file-btn"
                                                        wire:click="
                                                            descargarEvidencia(
                                                                {{
                                                                    $evidencia
                                                                        ->id
                                                                }}
                                                            )
                                                        "
                                                    >
                                                        📎
                                                        {{
                                                            $evidencia
                                                                ->nombre
                                                        }}
                                                    </button>

                                                @elseif (
                                                    $evidencia
                                                        ->url_archivo
                                                )

                                                    <a
                                                        class="file-btn"
                                                        href="{{
                                                            $evidencia
                                                                ->url_archivo
                                                        }}"
                                                        target="_blank"
                                                        rel="
                                                            noopener
                                                            noreferrer
                                                        "
                                                    >
                                                        {{
                                                            $evidencia
                                                                ->nombre
                                                        }}
                                                    </a>

                                                @endif

                                            @endforeach

                                        </div>

                                    @endif

                                </div>

                            </div>

                        @empty

                            <div class="empty">
                                No hay entregables registrados.
                            </div>

                        @endforelse

                    </div>

                </section>

            </div>

        </main>

        <aside class="sidebar">

            <div class="sidebar-title">
                Estado de la propuesta
            </div>

            <div class="progress-header">

                <span>
                    Completitud principal
                </span>

                <span>
                    {{ $progreso }}%
                </span>

            </div>

            <div class="progress-track">

                <div
                    class="progress-bar"
                    style="
                        width:
                        {{ $progreso }}%;
                    "
                ></div>

            </div>

            <div
                class="
                    review-box
                    {{
                        $listaParaRevision
                            ? 'review-ok'
                            : 'review-pending'
                    }}
                "
            >

                @if ($listaParaRevision)

                    Las secciones principales están
                    completas y la propuesta puede
                    revisarse antes del futuro flujo
                    de envío.

                @else

                    Aún existen secciones principales
                    incompletas. Puedes regresar a
                    editarlas desde esta vista.

                @endif

            </div>

            <div class="summary">

                <div class="summary-title">
                    Resumen
                </div>

                <div class="summary-row">
                    <span>Objetivos</span>

                    <strong>
                        {{
                            $objetivosEspecificos
                                ->count()
                            + (
                                $objetivoGeneral
                                    ? 1
                                    : 0
                            )
                        }}
                    </strong>
                </div>

                <div class="summary-row">
                    <span>Requisitos</span>

                    <strong>
                        {{ $requisitos->count() }}
                    </strong>
                </div>

                <div class="summary-row">
                    <span>Conceptos</span>

                    <strong>
                        {{ $presupuesto->count() }}
                    </strong>
                </div>

                <div class="summary-row">
                    <span>Cotizaciones</span>

                    <strong>
                        {{ $cotizaciones->count() }}
                    </strong>
                </div>

                <div class="summary-row">
                    <span>Actividades</span>

                    <strong>
                        {{ $actividades->count() }}
                    </strong>
                </div>

                <div class="summary-row">
                    <span>Entregables</span>

                    <strong>
                        {{ $entregables->count() }}
                    </strong>
                </div>

                <div class="summary-row">
                    <span>Evidencias</span>

                    <strong>
                        {{ $evidencias->count() }}
                    </strong>
                </div>

            </div>

            <div class="side-actions">

                <a
                    class="btn"
                    href="{{ route(
                        'filament.docente.pages.editor-propuesta',
                        [
                            'propuesta' =>
                                $propuesta->id,
                        ]
                    ) }}"
                >
                    Editar propuesta
                </a>

                <button
                type="button"
                class="btn btn-primary"
                wire:click="exportarPdf"
                wire:loading.attr="disabled"
                wire:target="exportarPdf"
            >
                Exportar PDF
            </button>

            <button
                type="button"
                class="btn"
                wire:click="exportarExcel"
                wire:loading.attr="disabled"
                wire:target="exportarExcel"
            >
                Exportar Excel
            </button>

            </div>

        </aside>

    </div>

</div>

</x-filament-panels::page>
