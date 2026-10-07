<x-filament-panels::page>
    <style>
        .directivo-reports {
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

        .directivo-reports * {
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

        .layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 300px;
            gap: 22px;
            align-items: start;
        }

        .main {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .card {
            background: #FFFFFF;
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 22px;
            box-shadow: 0 1px 2px rgba(15, 24, 39, .03);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .card-title {
            font-size: 16px;
            font-weight: 850;
        }

        .card-description {
            margin-top: 4px;
            color: var(--muted);
            font-size: 11px;
            line-height: 1.5;
        }

        .report-types {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .report-type {
            padding: 17px;
            border: 1px solid var(--border);
            border-radius: 13px;
            background: #FBFCFE;
            cursor: pointer;
        }

        .report-type.active {
            border-color: #C4B5FD;
            background: #FAF7FF;
            box-shadow: 0 0 0 2px rgba(124, 58, 237, .06);
        }

        .report-icon {
            width: 38px;
            height: 38px;
            margin-bottom: 11px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-soft);
            color: var(--primary);
            font-size: 12px;
            font-weight: 900;
        }

        .report-name {
            color: #334155;
            font-size: 13px;
            font-weight: 850;
        }

        .report-description {
            margin-top: 5px;
            color: var(--muted);
            font-size: 10px;
            line-height: 1.5;
        }

        .selected-indicator {
            display: inline-flex;
            margin-top: 10px;
            padding: 4px 7px;
            border-radius: 999px;
            background: #EDE9FE;
            color: #6D28D9;
            font-size: 9px;
            font-weight: 850;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 15px;
        }

        .field.full {
            grid-column: 1 / -1;
        }

        .field label {
            display: block;
            margin-bottom: 7px;
            color: #475569;
            font-size: 11px;
            font-weight: 800;
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

        .check-group {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 9px;
        }

        .check-item {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 11px;
            border: 1px solid #E7ECF3;
            border-radius: 10px;
            background: #FBFCFE;
            color: #475569;
            font-size: 10px;
            font-weight: 700;
        }

        .check-item input {
            accent-color: var(--primary);
        }

        .format-options {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }

        .format-option {
            padding: 14px;
            border: 1px solid var(--border);
            border-radius: 11px;
            background: white;
            text-align: center;
            cursor: pointer;
        }

        .format-option.active {
            border-color: var(--primary);
            background: var(--primary-soft);
        }

        .format-icon {
            width: 38px;
            height: 38px;
            margin: 0 auto 8px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 900;
        }

        .pdf-icon {
            background: #FEE2E2;
            color: #B91C1C;
        }

        .excel-icon {
            background: #D1FAE5;
            color: #047857;
        }

        .csv-icon {
            background: #DBEAFE;
            color: #1D4ED8;
        }

        .format-name {
            color: #334155;
            font-size: 11px;
            font-weight: 850;
        }

        .preview {
            padding: 17px;
            border: 1px dashed #C4B5FD;
            border-radius: 12px;
            background: #FAF7FF;
        }

        .preview-title {
            color: var(--primary-dark);
            font-size: 12px;
            font-weight: 850;
        }

        .preview-text {
            margin-top: 5px;
            color: #7C3AED;
            font-size: 10px;
            line-height: 1.5;
        }

        .preview-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 9px;
            margin-top: 14px;
        }

        .preview-stat {
            padding: 11px;
            border-radius: 9px;
            background: white;
            border: 1px solid #EDE9FE;
        }

        .preview-stat span {
            display: block;
            color: #A78BFA;
            font-size: 8px;
            text-transform: uppercase;
        }

        .preview-stat strong {
            display: block;
            margin-top: 4px;
            color: #5B21B6;
            font-size: 13px;
        }

        .generate-box {
            display: flex;
            justify-content: space-between;
            gap: 18px;
            align-items: center;
            padding-top: 19px;
            margin-top: 19px;
            border-top: 1px solid var(--border);
        }

        .generate-help {
            max-width: 520px;
            color: var(--muted);
            font-size: 10px;
            line-height: 1.5;
        }

        .generate-btn {
            min-height: 43px;
            padding: 0 18px;
            border: 1px solid var(--primary);
            border-radius: 10px;
            background: var(--primary);
            color: white;
            font-size: 11px;
            font-weight: 850;
            cursor: pointer;
            white-space: nowrap;
        }

        .sidebar {
            position: sticky;
            top: 90px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .side-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 15px;
            padding: 19px;
        }

        .side-title {
            margin-bottom: 14px;
            font-size: 13px;
            font-weight: 850;
        }

        .history-item {
            padding: 12px 0;
            border-bottom: 1px solid #EEF2F7;
        }

        .history-item:first-of-type {
            padding-top: 0;
        }

        .history-item:last-child {
            padding-bottom: 0;
            border-bottom: 0;
        }

        .history-title {
            color: #334155;
            font-size: 10px;
            font-weight: 800;
            line-height: 1.4;
        }

        .history-meta {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            margin-top: 5px;
            color: #94A3B8;
            font-size: 8px;
        }

        .download {
            color: var(--primary);
            font-weight: 800;
            cursor: pointer;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 8px 0;
            border-bottom: 1px solid #EEF2F7;
            font-size: 10px;
        }

        .summary-row:last-child {
            border-bottom: 0;
        }

        .summary-row span {
            color: var(--muted);
        }

        .summary-row strong {
            color: #334155;
        }

        .info-box {
            padding: 13px;
            border-radius: 10px;
            background: #F5F3FF;
            border: 1px solid #DDD6FE;
            color: #5B21B6;
            font-size: 10px;
            line-height: 1.5;
        }

        @media (max-width: 1000px) {
            .layout {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: static;
            }

            .preview-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 650px) {
            .page-header,
            .generate-box {
                flex-direction: column;
                align-items: flex-start;
            }

            .report-types,
            .form-grid,
            .check-group,
            .format-options,
            .preview-grid {
                grid-template-columns: 1fr;
            }

            .field.full {
                grid-column: auto;
            }

            .generate-btn {
                width: 100%;
            }
        }
    
        .report-type,
        .format-option {
            width: 100%;
            font-family: inherit;
            text-align: left;
        }

        button.report-type,
        button.format-option {
            appearance: none;
        }

        .report-type:hover,
        .format-option:hover {
            border-color: #C4B5FD;
        }

        .report-type:focus-visible,
        .format-option:focus-visible,
        .generate-btn:focus-visible,
        .secondary-btn:focus-visible {
            outline: 3px solid rgba(124, 58, 237, .16);
            outline-offset: 2px;
        }

        .actions-row {
            display: flex;
            gap: 9px;
            flex-wrap: wrap;
            margin-top: 17px;
        }

        .secondary-btn {
            min-height: 43px;
            padding: 0 16px;
            border: 1px solid #CBD5E1;
            border-radius: 10px;
            background: white;
            color: #475569;
            font-size: 11px;
            font-weight: 850;
            cursor: pointer;
        }

        .generate-btn:disabled {
            opacity: .55;
            cursor: not-allowed;
        }

        .preview-table-wrap {
            margin-top: 17px;
            overflow-x: auto;
            border: 1px solid #E7ECF3;
            border-radius: 10px;
            background: white;
        }

        .preview-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 650px;
        }

        .preview-table th {
            padding: 9px 10px;
            background: #F8FAFC;
            border-bottom: 1px solid #E7ECF3;
            color: #64748B;
            font-size: 9px;
            text-align: left;
            white-space: nowrap;
        }

        .preview-table td {
            padding: 9px 10px;
            border-bottom: 1px solid #EEF2F7;
            color: #475569;
            font-size: 9px;
            vertical-align: top;
        }

        .preview-table tr:last-child td {
            border-bottom: 0;
        }

        .empty-state {
            padding: 18px;
            border: 1px solid #E7ECF3;
            border-radius: 10px;
            background: #F8FAFC;
            color: #94A3B8;
            font-size: 10px;
            line-height: 1.5;
        }

        .quick-link {
            display: block;
            padding: 11px 0;
            border-bottom: 1px solid #EEF2F7;
            color: #475569;
            text-decoration: none;
            font-size: 10px;
            font-weight: 800;
        }

        .quick-link:last-child {
            border-bottom: 0;
        }

        .quick-link:hover {
            color: var(--primary);
        }

        .filter-note {
            margin-top: 10px;
            color: #94A3B8;
            font-size: 9px;
        }

</style>

    <div class="directivo-reports">

        <div class="page-header">

            <div>

                <h1 class="page-title">
                    Reportes
                </h1>

                <div class="page-subtitle">
                    Genera reportes institucionales
                    utilizando la información real
                    almacenada en PostgreSQL.
                </div>

                <div style="margin-top:12px;">
                    <button
                        type="button"
                        class="secondary-btn"
                        wire:click="probarBoton"
                    >
                        Probar interacción
                    </button>
                </div>

            </div>

            <span class="role-badge">
                <span class="role-dot"></span>
                Directivo
            </span>

        </div>

        <div class="layout">

            <main class="main">

                <section class="card">

                    <div class="card-header">

                        <div>

                            <div class="card-title">
                                Tipo de reporte
                            </div>

                            <div class="card-description">
                                Selecciona la información
                                que deseas consultar y
                                exportar.
                            </div>

                        </div>

                    </div>

                    <div class="report-types">

                        <button
                            type="button"
                            class="
                                report-type
                                {{
                                    $tipoReporte
                                    === 'convocatorias'
                                        ? 'active'
                                        : ''
                                }}
                            "
                            wire:click="seleccionarTipo('convocatorias')"
                        >

                            <div class="report-icon">
                                C
                            </div>

                            <div class="report-name">
                                Convocatorias
                            </div>

                            <div class="report-description">
                                Convocatorias detectadas,
                                organismos, fechas,
                                montos y estado.
                            </div>

                            @if (
                                $tipoReporte
                                === 'convocatorias'
                            )

                                <span
                                    class="
                                        selected-indicator
                                    "
                                >
                                    Seleccionado
                                </span>

                            @endif

                        </button>

                        <button
                            type="button"
                            class="
                                report-type
                                {{
                                    $tipoReporte
                                    === 'revisiones'
                                        ? 'active'
                                        : ''
                                }}
                            "
                            wire:click="seleccionarTipo('revisiones')"
                        >

                            <div class="report-icon">
                                ✓
                            </div>

                            <div class="report-name">
                                Revisiones
                            </div>

                            <div class="report-description">
                                Historial real de
                                decisiones administrativas.
                            </div>

                            @if (
                                $tipoReporte
                                === 'revisiones'
                            )

                                <span
                                    class="
                                        selected-indicator
                                    "
                                >
                                    Seleccionado
                                </span>

                            @endif

                        </button>

                        <button
                            type="button"
                            class="
                                report-type
                                {{
                                    $tipoReporte
                                    === 'propuestas'
                                        ? 'active'
                                        : ''
                                }}
                            "
                            wire:click="seleccionarTipo('propuestas')"
                        >

                            <div class="report-icon">
                                P
                            </div>

                            <div class="report-name">
                                Propuestas
                            </div>

                            <div class="report-description">
                                Propuestas registradas y
                                convocatorias relacionadas.
                            </div>

                            @if (
                                $tipoReporte
                                === 'propuestas'
                            )

                                <span
                                    class="
                                        selected-indicator
                                    "
                                >
                                    Seleccionado
                                </span>

                            @endif

                        </button>

                        <button
                            type="button"
                            class="
                                report-type
                                {{
                                    $tipoReporte
                                    === 'financiamiento'
                                        ? 'active'
                                        : ''
                                }}
                            "
                            wire:click="seleccionarTipo('financiamiento')"
                        >

                            <div class="report-icon">
                                $
                            </div>

                            <div class="report-name">
                                Financiamiento
                            </div>

                            <div class="report-description">
                                Montos registrados en
                                las convocatorias.
                            </div>

                            @if (
                                $tipoReporte
                                === 'financiamiento'
                            )

                                <span
                                    class="
                                        selected-indicator
                                    "
                                >
                                    Seleccionado
                                </span>

                            @endif

                        </button>

                    </div>

                </section>

                <section class="card">

                    <div class="card-header">

                        <div>

                            <div class="card-title">
                                Configuración del reporte
                            </div>

                            <div class="card-description">
                                Define el periodo y los
                                filtros que se aplicarán.
                            </div>

                        </div>

                    </div>

                    <div class="form-grid">

                        <div class="field">

                            <label>
                                Fecha inicial
                            </label>

                            <input
                                class="input"
                                type="date"
                                wire:model.live="fechaInicio"
                            >

                        </div>

                        <div class="field">

                            <label>
                                Fecha final
                            </label>

                            <input
                                class="input"
                                type="date"
                                wire:model.live="fechaFinal"
                            >

                        </div>

                        <div class="field">

                            <label>
                                Categoría
                            </label>

                            <select
                                class="select"
                                wire:model.live="categoria"
                            >

                                <option value="">
                                    Todas las categorías
                                </option>

                                @foreach (
                                    $categorias
                                    as $categoriaItem
                                )

                                    <option
                                        value="{{
                                            $categoriaItem
                                                ->id
                                        }}"
                                    >
                                        {{
                                            trim(
                                                $categoriaItem
                                                    ->nombre
                                            )
                                        }}
                                    </option>

                                @endforeach

                            </select>

                        </div>

                        <div class="field">

                            <label>
                                Organismo
                            </label>

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
                                            $organismoItem
                                                ->id
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

                        <div class="field">

                            <label>
                                Estado / resultado
                            </label>

                            <select
                                class="select"
                                wire:model.live="estado"
                            >

                                <option value="">
                                    Todos
                                </option>

                                @foreach (
                                    $estadosDisponibles
                                    as $valorEstado
                                    => $textoEstado
                                )

                                    <option
                                        value="{{
                                            $valorEstado
                                        }}"
                                    >
                                        {{
                                            $textoEstado
                                        }}
                                    </option>

                                @endforeach

                            </select>

                        </div>

                        <div class="field">

                            <label>
                                Registros encontrados
                            </label>

                            <input
                                class="input"
                                type="text"
                                value="{{
                                    $reporte[
                                        'total'
                                    ]
                                }}"
                                readonly
                            >

                        </div>

                        <div class="field full">

                            <label>
                                Información a incluir
                            </label>

                            <div class="check-group">

                                <label class="check-item">

                                    <input
                                        type="checkbox"
                                        wire:model.live="incluirDatosGenerales"
                                    >

                                    Datos generales

                                </label>

                                <label class="check-item">

                                    <input
                                        type="checkbox"
                                        wire:model.live="incluirOrganismos"
                                    >

                                    Organismos y categorías

                                </label>

                                <label class="check-item">

                                    <input
                                        type="checkbox"
                                        wire:model.live="incluirFechas"
                                    >

                                    Fechas y vigencia

                                </label>

                                <label class="check-item">

                                    <input
                                        type="checkbox"
                                        wire:model.live="incluirMontos"
                                    >

                                    Montos

                                </label>

                                <label class="check-item">

                                    <input
                                        type="checkbox"
                                        wire:model.live="incluirRequisitos"
                                    >

                                    Requisitos

                                </label>

                                <label class="check-item">

                                    <input
                                        type="checkbox"
                                        wire:model.live="incluirDocumentos"
                                    >

                                    Documentos

                                </label>

                            </div>

                            <div class="actions-row">

                                <button
                                    type="button"
                                    class="secondary-btn"
                                    wire:click="limpiarFiltros"
                                >
                                    Limpiar filtros
                                </button>

                            </div>

                        </div>

                    </div>

                </section>

                <section class="card">

                    <div class="card-header">

                        <div>

                            <div class="card-title">
                                Formato de salida
                            </div>

                            <div class="card-description">
                                El reporte se descargará
                                en el formato seleccionado.
                            </div>

                        </div>

                    </div>

                    <div class="format-options">

                        <button
                            type="button"
                            class="
                                format-option
                                {{
                                    $formato
                                    === 'pdf'
                                        ? 'active'
                                        : ''
                                }}
                            "
                            wire:click="seleccionarFormato('pdf')"
                        >

                            <div
                                class="
                                    format-icon
                                    pdf-icon
                                "
                            >
                                PDF
                            </div>

                            <div class="format-name">
                                Documento PDF
                            </div>

                        </button>

                        <button
                            type="button"
                            class="
                                format-option
                                {{
                                    $formato
                                    === 'xlsx'
                                        ? 'active'
                                        : ''
                                }}
                            "
                            wire:click="seleccionarFormato('xlsx')"
                        >

                            <div
                                class="
                                    format-icon
                                    excel-icon
                                "
                            >
                                XLSX
                            </div>

                            <div class="format-name">
                                Excel
                            </div>

                        </button>

                        <button
                            type="button"
                            class="
                                format-option
                                {{
                                    $formato
                                    === 'csv'
                                        ? 'active'
                                        : ''
                                }}
                            "
                            wire:click="seleccionarFormato('csv')"
                        >

                            <div
                                class="
                                    format-icon
                                    csv-icon
                                "
                            >
                                CSV
                            </div>

                            <div class="format-name">
                                Archivo CSV
                            </div>

                        </button>

                    </div>

                </section>

                <section class="card">

                    <div class="card-header">

                        <div>

                            <div class="card-title">
                                Vista previa
                            </div>

                            <div class="card-description">
                                Los resultados cambian
                                automáticamente al modificar
                                los filtros.
                            </div>

                        </div>

                    </div>

                    <div class="preview">

                        <div class="preview-title">
                            {{
                                $reporte[
                                    'titulo'
                                ]
                            }}
                        </div>

                        <div class="preview-text">

                            {{
                                $reporte[
                                    'descripcion'
                                ]
                            }}

                            Periodo:
                            {{
                                $fechaInicio
                            }}
                            —
                            {{
                                $fechaFinal
                            }}.

                        </div>

                        <div class="preview-grid">

                            @foreach (
                                $reporte[
                                    'metricas'
                                ]
                                as $metrica
                            )

                                <div class="preview-stat">

                                    <span>
                                        {{
                                            $metrica[
                                                'label'
                                            ]
                                        }}
                                    </span>

                                    <strong>
                                        {{
                                            $metrica[
                                                'valor'
                                            ]
                                        }}
                                    </strong>

                                </div>

                            @endforeach

                        </div>

                    </div>

                    @if (
                        $reporte[
                            'total'
                        ] > 0
                    )

                        <div class="preview-table-wrap">

                            <table class="preview-table">

                                <thead>
                                    <tr>

                                        @foreach (
                                            $reporte[
                                                'columnas'
                                            ]
                                            as $columna
                                        )

                                            <th>
                                                {{
                                                    $columna[
                                                        'label'
                                                    ]
                                                }}
                                            </th>

                                        @endforeach

                                    </tr>
                                </thead>

                                <tbody>

                                    @foreach (
                                        $reporte[
                                            'filas'
                                        ]
                                            ->take(8)
                                        as $fila
                                    )

                                        <tr>

                                            @foreach (
                                                $reporte[
                                                    'columnas'
                                                ]
                                                as $columna
                                            )

                                                <td>
                                                    {{
                                                        \Illuminate\Support\Str::limit(
                                                            (string)
                                                            (
                                                                $fila[
                                                                    $columna[
                                                                        'key'
                                                                    ]
                                                                ]
                                                                ?? ''
                                                            ),
                                                            80
                                                        )
                                                    }}
                                                </td>

                                            @endforeach

                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                        @if (
                            $reporte[
                                'total'
                            ] > 8
                        )

                            <div class="filter-note">
                                Vista previa de los primeros
                                8 registros. La exportación
                                contendrá los
                                {{
                                    $reporte[
                                        'total'
                                    ]
                                }}
                                registros.
                            </div>

                        @endif

                    @else

                        <div
                            class="empty-state"
                            style="margin-top:17px;"
                        >
                            No existen registros que
                            coincidan con los filtros
                            seleccionados.
                        </div>

                    @endif

                    <div class="generate-box">

                        <div class="generate-help">
                            Se exportarán los datos reales
                            mostrados por los filtros
                            actuales. No se modificará
                            ningún registro de PostgreSQL.
                        </div>

                        <button
                            type="button"
                            class="generate-btn"
                            wire:click="generarReporte"
                            wire:loading.attr="disabled"
                            wire:target="generarReporte"
                            @disabled(
                                $reporte[
                                    'total'
                                ] === 0
                            )
                        >

                            <span
                                wire:loading.remove wire:target="generarReporte"
                            >
                                Generar
                                {{ $formatoLabel }}
                            </span>

                            <span
                                wire:loading wire:target="generarReporte"
                            >
                                Generando...
                            </span>

                        </button>

                    </div>

                </section>

            </main>

            <aside class="sidebar">

                <section class="side-card">

                    <div class="side-title">
                        Resumen actual
                    </div>

                    <div class="summary-row">
                        <span>Periodo</span>
                        <strong>
                            {{ $fechaInicio }}
                            —
                            {{ $fechaFinal }}
                        </strong>
                    </div>

                    <div class="summary-row">
                        <span>Tipo</span>
                        <strong>
                            {{ $tipoLabel }}
                        </strong>
                    </div>

                    <div class="summary-row">
                        <span>Formato</span>
                        <strong>
                            {{ $formatoLabel }}
                        </strong>
                    </div>

                    <div class="summary-row">
                        <span>Registros</span>
                        <strong>
                            {{
                                $reporte[
                                    'total'
                                ]
                            }}
                        </strong>
                    </div>

                </section>

                <section class="side-card">

                    <div class="side-title">
                        Accesos relacionados
                    </div>

                    <a
                        href="{{
                            route(
                                'filament.directivo.pages.estadisticas'
                            )
                        }}"
                        class="quick-link"
                    >
                        Estadísticas →
                    </a>

                    <a
                        href="{{
                            route(
                                'filament.directivo.pages.convocatorias-por-revisar'
                            )
                        }}"
                        class="quick-link"
                    >
                        Convocatorias por revisar →
                    </a>

                    <a
                        href="{{
                            route(
                                'filament.directivo.pages.buscar-convocatorias'
                            )
                        }}"
                        class="quick-link"
                    >
                        Buscar convocatorias →
                    </a>

                </section>

                <div class="info-box">
                    No se muestran reportes recientes
                    simulados. El historial de archivos
                    exportados requeriría persistir cada
                    generación en una tabla específica.
                </div>

            </aside>

        </div>

    </div>

</x-filament-panels::page>
