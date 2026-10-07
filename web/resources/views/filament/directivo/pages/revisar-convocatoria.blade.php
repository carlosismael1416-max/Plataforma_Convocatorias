<x-filament-panels::page>
    <style>
        .review-detail {
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

        .review-detail * {
            box-sizing: border-box;
        }

        .back-link {
            display: inline-flex;
            gap: 7px;
            align-items: center;
            margin-bottom: 18px;
            color: var(--primary);
            font-size: 12px;
            font-weight: 800;
            text-decoration: none;
        }

        .header-card {
            padding: 24px;
            margin-bottom: 20px;
            border: 1px solid var(--border);
            border-radius: 16px;
            background: #FFFFFF;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            align-items: flex-start;
        }

        .eyebrow {
            color: var(--primary);
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .title {
            margin-top: 7px;
            max-width: 850px;
            color: var(--text);
            font-size: 24px;
            line-height: 1.35;
            font-weight: 900;
        }

        .pending-badge {
            display: inline-flex;
            gap: 6px;
            align-items: center;
            padding: 7px 11px;
            border-radius: 999px;
            background: #FEF3C7;
            color: #92400E;
            font-size: 11px;
            font-weight: 850;
            white-space: nowrap;
        }

        .pending-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--warning);
        }

        .header-description {
            max-width: 900px;
            margin-top: 14px;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.65;
        }

        .header-meta {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 10px;
            margin-top: 20px;
        }

        .meta {
            padding: 11px;
            border: 1px solid #E7ECF3;
            border-radius: 10px;
            background: #F8FAFC;
        }

        .meta span {
            display: block;
            margin-bottom: 4px;
            color: #94A3B8;
            font-size: 9px;
            text-transform: uppercase;
        }

        .meta strong {
            color: #334155;
            font-size: 11px;
        }

        .layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 310px;
            gap: 22px;
            align-items: start;
        }

        .main {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .card {
            padding: 22px;
            border: 1px solid var(--border);
            border-radius: 16px;
            background: #FFFFFF;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            gap: 16px;
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
            font-size: 11px;
            line-height: 1.45;
        }

        .section-status {
            padding: 5px 9px;
            border-radius: 999px;
            background: #D1FAE5;
            color: #065F46;
            font-size: 10px;
            font-weight: 850;
            white-space: nowrap;
        }

        .section-status.warning {
            background: #FEF3C7;
            color: #92400E;
        }

        .text {
            color: #64748B;
            font-size: 12px;
            line-height: 1.7;
        }

        .detected-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .detected-field {
            padding: 13px;
            border: 1px solid #E7ECF3;
            border-radius: 11px;
            background: #FBFCFE;
        }

        .field-label {
            display: block;
            margin-bottom: 5px;
            color: #94A3B8;
            font-size: 9px;
            text-transform: uppercase;
            font-weight: 800;
        }

        .field-value {
            color: #334155;
            font-size: 12px;
            font-weight: 800;
        }

        .field-value.highlight {
            color: var(--primary-dark);
        }

        .requirements {
            display: flex;
            flex-direction: column;
            gap: 9px;
        }

        .requirement {
            display: grid;
            grid-template-columns: 27px minmax(0, 1fr) auto;
            gap: 11px;
            align-items: flex-start;
            padding: 12px;
            border: 1px solid #E7ECF3;
            border-radius: 10px;
            background: #FBFCFE;
        }

        .requirement-icon {
            width: 27px;
            height: 27px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-soft);
            color: var(--primary);
            font-size: 10px;
            font-weight: 900;
        }

        .requirement-title {
            color: #334155;
            font-size: 11px;
            font-weight: 800;
        }

        .requirement-text {
            margin-top: 3px;
            color: #94A3B8;
            font-size: 10px;
            line-height: 1.45;
        }

        .required-badge {
            padding: 4px 7px;
            border-radius: 999px;
            background: #FEE2E2;
            color: #991B1B;
            font-size: 9px;
            font-weight: 850;
            white-space: nowrap;
        }

        .documents {
            display: flex;
            flex-direction: column;
            gap: 9px;
        }

        .document {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            align-items: center;
            padding: 12px;
            border: 1px solid #E7ECF3;
            border-radius: 10px;
        }

        .document-left {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .pdf {
            width: 36px;
            height: 36px;
            border-radius: 9px;
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
            font-size: 11px;
            font-weight: 800;
        }

        .document-help {
            margin-top: 3px;
            color: #94A3B8;
            font-size: 9px;
        }

        .document-button {
            min-height: 32px;
            padding: 0 11px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: white;
            color: var(--primary);
            font-size: 9px;
            font-weight: 850;
            cursor: pointer;
        }

        .warning-box {
            display: flex;
            gap: 11px;
            padding: 13px;
            border: 1px solid #FDE68A;
            border-radius: 10px;
            background: #FFFBEB;
            color: #92400E;
            font-size: 11px;
            line-height: 1.55;
        }

        .warning-icon {
            width: 24px;
            height: 24px;
            flex: 0 0 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #FEF3C7;
            font-weight: 900;
        }

        .sidebar {
            position: sticky;
            top: 90px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .side-card {
            padding: 19px;
            border: 1px solid var(--border);
            border-radius: 15px;
            background: white;
        }

        .side-title {
            margin-bottom: 14px;
            font-size: 13px;
            font-weight: 850;
        }

        .check-item {
            display: flex;
            gap: 9px;
            align-items: center;
            padding: 9px 0;
            border-bottom: 1px solid #EEF2F7;
            font-size: 10px;
        }

        .check-item:last-child {
            border-bottom: 0;
        }

        .check-icon {
            width: 21px;
            height: 21px;
            flex: 0 0 21px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #D1FAE5;
            color: #047857;
            font-size: 9px;
            font-weight: 900;
        }

        .check-icon.pending {
            background: #FEF3C7;
            color: #B45309;
        }

        .check-text {
            color: #475569;
            font-weight: 700;
        }

        .textarea {
            width: 100%;
            min-height: 105px;
            padding: 11px;
            border: 1px solid #CBD5E1;
            border-radius: 10px;
            background: white;
            color: #334155;
            font: inherit;
            font-size: 11px;
            line-height: 1.5;
            resize: vertical;
            outline: none;
        }

        .textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, .10);
        }

        .decision-actions {
            display: flex;
            flex-direction: column;
            gap: 9px;
            margin-top: 13px;
        }

        .decision-btn {
            width: 100%;
            min-height: 42px;
            border-radius: 9px;
            font-size: 11px;
            font-weight: 850;
            cursor: pointer;
        }

        .approve {
            border: 1px solid var(--success);
            background: var(--success);
            color: white;
        }

        .correction {
            border: 1px solid #F59E0B;
            background: #FFFBEB;
            color: #92400E;
        }

        .reject {
            border: 1px solid #FECACA;
            background: #FEF2F2;
            color: #B91C1C;
        }

        .visual-note {
            margin-top: 13px;
            color: #94A3B8;
            font-size: 9px;
            line-height: 1.45;
            text-align: center;
        }

        @media (max-width: 1050px) {
            .layout {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: static;
            }

            .header-meta {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 650px) {
            .header-top {
                flex-direction: column;
            }

            .header-meta,
            .detected-grid {
                grid-template-columns: 1fr;
            }

            .document {
                flex-direction: column;
                align-items: flex-start;
            }

            .document-button {
                width: 100%;
            }

            .requirement {
                grid-template-columns: 27px minmax(0, 1fr);
            }

            .required-badge {
                grid-column: 2;
                width: fit-content;
            }
        }
    
        .empty-state {
            padding: 14px;
            border: 1px solid #E7ECF3;
            border-radius: 10px;
            background: #F8FAFC;
            color: #94A3B8;
            font-size: 10px;
            line-height: 1.5;
        }

        .change-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .change-item {
            padding: 13px;
            border: 1px solid #E7ECF3;
            border-radius: 11px;
            background: #FBFCFE;
        }

        .change-title {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            align-items: center;
            margin-bottom: 10px;
            color: #334155;
            font-size: 11px;
            font-weight: 850;
        }

        .change-values {
            display: grid;
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .change-value {
            padding: 10px;
            border-radius: 9px;
            background: white;
            border: 1px solid #E8EDF4;
        }

        .change-label {
            display: block;
            margin-bottom: 4px;
            color: #94A3B8;
            font-size: 8px;
            text-transform: uppercase;
        }

        .change-text {
            color: #334155;
            font-size: 10px;
            font-weight: 800;
        }

        .change-meta {
            margin-top: 9px;
            color: #64748B;
            font-size: 9px;
            line-height: 1.5;
        }

        .source-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 800;
        }

        .check-icon.missing {
            background: #F1F5F9;
            color: #94A3B8;
        }

        .decision-btn:disabled {
            opacity: .55;
            cursor: not-allowed;
        }

        .textarea:disabled {
            cursor: not-allowed;
            background: #F8FAFC;
            color: #94A3B8;
        }

        @media (max-width: 650px) {
            .change-values {
                grid-template-columns: 1fr;
            }
        }

</style>

    <div class="review-detail">

        <a
            href="{{
                route(
                    'filament.directivo.pages.convocatorias-por-revisar'
                )
            }}"
            class="back-link"
        >
            ← Volver a Convocatorias por Revisar
        </a>

        <section class="header-card">

            <div class="header-top">

                <div>

                    <div class="eyebrow">
                        Revisión de convocatoria
                    </div>

                    <div class="title">
                        {{
                            $convocatoria
                                ->titulo
                        }}
                    </div>

                </div>

                <div class="pending-badge">
                    <span class="pending-dot"></span>
                    Pendiente de revisión
                </div>

            </div>

            <div class="header-description">
                Revisa la información registrada,
                documentos y cambios detectados
                antes de realizar una decisión
                administrativa.
            </div>

            <div class="header-meta">

                <div class="meta">

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

                <div class="meta">

                    <span>
                        Detectada
                    </span>

                    <strong>
                        {{
                            $fechaDetectada
                                ?->format(
                                    'd/m/Y'
                                )
                            ?? 'Sin fecha'
                        }}
                    </strong>

                </div>

                <div class="meta">

                    <span>
                        Cierre
                    </span>

                    <strong>
                        {{
                            $convocatoria
                                ->fecha_cierre
                                ?->format(
                                    'd/m/Y'
                                )
                            ?? 'Sin fecha'
                        }}
                    </strong>

                </div>

                <div class="meta">

                    <span>
                        Monto máximo
                    </span>

                    <strong>

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

                    </strong>

                </div>

                <div class="meta">

                    <span>
                        Origen
                    </span>

                    <strong>
                        {{ $origenTexto }}
                    </strong>

                </div>

            </div>

        </section>

        <div class="layout">

            <main class="main">

                <section class="card">

                    <div class="card-header">

                        <div>

                            <div class="card-title">
                                Información registrada
                            </div>

                            <div class="card-description">
                                Datos actualmente almacenados
                                en PostgreSQL.
                            </div>

                        </div>

                        <span class="section-status">
                            Información encontrada
                        </span>

                    </div>

                    <div class="detected-grid">

                        <div class="detected-field">

                            <span class="field-label">
                                Título
                            </span>

                            <div class="field-value">
                                {{
                                    $convocatoria
                                        ->titulo
                                }}
                            </div>

                        </div>

                        <div class="detected-field">

                            <span class="field-label">
                                Organismo
                            </span>

                            <div class="field-value">
                                {{
                                    $convocatoria
                                        ->organismo
                                        ?->nombre
                                    ?? 'No especificado'
                                }}
                            </div>

                        </div>

                        <div class="detected-field">

                            <span class="field-label">
                                Fecha de apertura
                            </span>

                            <div class="field-value">
                                {{
                                    $fechaApertura
                                        ?->format(
                                            'd/m/Y'
                                        )
                                    ?? 'No especificada'
                                }}
                            </div>

                        </div>

                        <div class="detected-field">

                            <span class="field-label">
                                Fecha de cierre
                            </span>

                            <div
                                class="
                                    field-value
                                    highlight
                                "
                            >
                                {{
                                    $convocatoria
                                        ->fecha_cierre
                                        ?->format(
                                            'd/m/Y'
                                        )
                                    ?? 'No especificada'
                                }}
                            </div>

                        </div>

                        <div class="detected-field">

                            <span class="field-label">
                                Monto máximo
                            </span>

                            <div class="field-value">

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

                            </div>

                        </div>

                        <div class="detected-field">

                            <span class="field-label">
                                Área temática
                            </span>

                            <div class="field-value">
                                {{
                                    $convocatoria
                                        ->categoria
                                        ?->nombre
                                    ?? 'No especificada'
                                }}
                            </div>

                        </div>

                        <div class="detected-field">

                            <span class="field-label">
                                Cobertura
                            </span>

                            <div class="field-value">
                                {{
                                    $convocatoria
                                        ->ubicacion
                                    ?: 'No especificada'
                                }}
                            </div>

                        </div>

                        <div class="detected-field">

                            <span class="field-label">
                                Modalidad
                            </span>

                            <div class="field-value">
                                {{
                                    $convocatoria
                                        ->modalidad
                                    ?: 'No especificada'
                                }}
                            </div>

                        </div>

                    </div>

                </section>

                <section class="card">

                    <div class="card-header">

                        <div>

                            <div class="card-title">
                                Descripción
                            </div>

                            <div class="card-description">
                                Descripción general registrada
                                para la convocatoria.
                            </div>

                        </div>

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

                        <div class="empty-state">
                            No existe una descripción
                            registrada.
                        </div>

                    @endif

                </section>

                <section class="card">

                    <div class="card-header">

                        <div>

                            <div class="card-title">
                                Objetivo
                            </div>

                            <div class="card-description">
                                Objetivo registrado para
                                esta convocatoria.
                            </div>

                        </div>

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

                        <div class="empty-state">
                            No existe un objetivo
                            registrado.
                        </div>

                    @endif

                </section>

                <section class="card">

                    <div class="card-header">

                        <div>

                            <div class="card-title">
                                Requisitos identificados
                            </div>

                            <div class="card-description">
                                Requisitos asociados a
                                esta convocatoria.
                            </div>

                        </div>

                        <span
                            class="
                                section-status
                                warning
                            "
                        >
                            {{
                                $convocatoria
                                    ->requisitos
                                    ->count()
                            }}

                            requisitos
                        </span>

                    </div>

                    <div class="requirements">

                        @forelse (
                            $convocatoria
                                ->requisitos
                            as $requisito
                        )

                            <div class="requirement">

                                <div
                                    class="
                                        requirement-icon
                                    "
                                >
                                    {{
                                        $loop
                                            ->iteration
                                    }}
                                </div>

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
                                                requirement-text
                                            "
                                        >
                                            {{
                                                $requisito
                                                    ->descripcion
                                            }}
                                        </div>

                                    @endif

                                </div>

                                <span
                                    class="
                                        required-badge
                                    "
                                >
                                    {{
                                        $requisito
                                            ->obligatorio
                                            ? 'Obligatorio'
                                            : 'Opcional'
                                    }}
                                </span>

                            </div>

                        @empty

                            <div class="empty-state">
                                No hay requisitos
                                registrados para esta
                                convocatoria.
                            </div>

                        @endforelse

                    </div>

                </section>

                <section class="card">

                    <div class="card-header">

                        <div>

                            <div class="card-title">
                                Documentos encontrados
                            </div>

                            <div class="card-description">
                                Archivos asociados a
                                la convocatoria.
                            </div>

                        </div>

                        <span class="section-status">

                            {{
                                $documentos
                                    ->count()
                            }}

                            documentos

                        </span>

                    </div>

                    <div class="documents">

                        @forelse (
                            $documentos
                            as $documento
                        )

                            <div class="document">

                                <div class="document-left">

                                    <div class="pdf">
                                        PDF
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
                                                document-help
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
                                            document-button
                                        "
                                    >
                                        Revisar
                                    </a>

                                @else

                                    <span
                                        class="
                                            document-help
                                        "
                                    >
                                        Sin enlace
                                    </span>

                                @endif

                            </div>

                        @empty

                            <div class="empty-state">
                                No hay documentos
                                registrados.
                            </div>

                        @endforelse

                    </div>

                </section>

                <section class="card">

                    <div class="card-header">

                        <div>

                            <div class="card-title">
                                Cambios detectados
                            </div>

                            <div class="card-description">
                                Historial de cambios
                                registrados por el sistema.
                            </div>

                        </div>

                        <span class="section-status">
                            {{
                                $cambios
                                    ->count()
                            }}
                        </span>

                    </div>

                    <div class="change-list">

                        @forelse (
                            $cambios
                            as $cambio
                        )

                            <div class="change-item">

                                <div class="change-title">

                                    <span>
                                        {{
                                            $cambio[
                                                'campo'
                                            ]
                                        }}
                                    </span>

                                    <span
                                        class="
                                            section-status
                                            {{
                                                $cambio[
                                                    'requiere_verificacion'
                                                ]
                                                    ? 'warning'
                                                    : ''
                                            }}
                                        "
                                    >
                                        {{
                                            $cambio[
                                                'requiere_verificacion'
                                            ]
                                                ? 'Requiere verificación'
                                                : 'Verificado'
                                        }}
                                    </span>

                                </div>

                                <div class="change-values">

                                    <div class="change-value">

                                        <span
                                            class="
                                                change-label
                                            "
                                        >
                                            Valor anterior
                                        </span>

                                        <div
                                            class="
                                                change-text
                                            "
                                        >
                                            {{
                                                $cambio[
                                                    'anterior'
                                                ]
                                            }}
                                        </div>

                                    </div>

                                    <div class="change-value">

                                        <span
                                            class="
                                                change-label
                                            "
                                        >
                                            Valor nuevo
                                        </span>

                                        <div
                                            class="
                                                change-text
                                            "
                                        >
                                            {{
                                                $cambio[
                                                    'nuevo'
                                                ]
                                            }}
                                        </div>

                                    </div>

                                </div>

                                <div class="change-meta">

                                    Fuente:
                                    {{
                                        $cambio[
                                            'fuente'
                                        ]
                                    }}

                                    @if (
                                        $cambio[
                                            'fuente_url'
                                        ]
                                    )

                                        ·

                                        <a
                                            href="{{
                                                $cambio[
                                                    'fuente_url'
                                                ]
                                            }}"
                                            target="_blank"
                                            rel="
                                                noopener
                                                noreferrer
                                            "
                                            class="
                                                source-link
                                            "
                                        >
                                            Abrir fuente
                                        </a>

                                    @endif

                                    @if (
                                        $cambio[
                                            'fecha_revision'
                                        ]
                                    )

                                        · Registrado:
                                        {{
                                            $cambio[
                                                'fecha_revision'
                                            ]->format(
                                                'd/m/Y H:i'
                                            )
                                        }}

                                    @endif

                                    @if (
                                        trim(
                                            (string)
                                            $cambio[
                                                'observaciones'
                                            ]
                                        ) !== ''
                                    )

                                        <br>

                                        {{
                                            $cambio[
                                                'observaciones'
                                            ]
                                        }}

                                    @endif

                                </div>

                            </div>

                        @empty

                            <div class="empty-state">
                                No existen cambios
                                registrados para esta
                                convocatoria.
                            </div>

                        @endforelse

                    </div>

                </section>

                <div class="warning-box">

                    <div class="warning-icon">
                        !
                    </div>

                    <div>
                        Antes de tomar una decisión,
                        debe verificarse que los datos
                        coincidan con los documentos y
                        la fuente oficial.
                    </div>

                </div>

            </main>

            <aside class="sidebar">

                <section class="side-card">

                    <div class="side-title">
                        Verificación
                    </div>

                    @foreach (
                        $verificaciones
                        as $verificacion
                    )

                        <div class="check-item">

                            <div
                                class="
                                    check-icon
                                    {{
                                        $verificacion[
                                            'ok'
                                        ]
                                            ? ''
                                            : 'missing'
                                    }}
                                "
                            >
                                {{
                                    $verificacion[
                                        'ok'
                                    ]
                                        ? '✓'
                                        : '—'
                                }}
                            </div>

                            <div class="check-text">
                                {{
                                    $verificacion[
                                        'texto'
                                    ]
                                }}
                            </div>

                        </div>

                    @endforeach

                    <div class="check-item">

                        <div
                            class="
                                check-icon
                                pending
                            "
                        >
                            !
                        </div>

                        <div class="check-text">
                            Revisión humana pendiente
                        </div>

                    </div>

                </section>

                @if ($urlOficial)

                    <section class="side-card">

                        <div class="side-title">
                            Fuente oficial
                        </div>

                        <a
                            href="{{ $urlOficial }}"
                            target="_blank"
                            rel="
                                noopener
                                noreferrer
                            "
                            class="
                                document-button
                            "
                        >
                            Abrir fuente oficial
                        </a>

                    </section>

                @endif

                <section class="side-card">

                    <div class="side-title">
                        Observaciones
                    </div>

                    <textarea
                        class="textarea"
                        placeholder="
                            Se habilitará durante
                            el flujo de decisión.
                        "
                        disabled
                    ></textarea>

                </section>

                <section class="side-card">

                    <div class="side-title">
                        Decisión
                    </div>

                    <div class="decision-actions">

                        <button
                            class="
                                decision-btn
                                approve
                            "
                            type="button"
                            disabled
                        >
                            ✓ Aprobar convocatoria
                        </button>

                        <button
                            class="
                                decision-btn
                                correction
                            "
                            type="button"
                            disabled
                        >
                            Solicitar corrección
                        </button>

                        <button
                            class="
                                decision-btn
                                reject
                            "
                            type="button"
                            disabled
                        >
                            Rechazar convocatoria
                        </button>

                    </div>

                    <div class="visual-note">
                        Las decisiones y cambios
                        de estado se habilitarán
                        durante la FASE 4.
                    </div>

                </section>

            </aside>

        </div>

    </div>

</x-filament-panels::page>
