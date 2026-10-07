<x-filament-panels::page>

<style>
    .itsva-requisitos {
        --text: #0F1827;
        --muted: #64748B;
        --primary: #1A4B8C;
        --border: #DDE3EE;
        --success: #059669;
        --warning: #D97706;
        --danger: #DC2626;

        color: var(--text);
    }

    .itsva-requisitos * {
        box-sizing: border-box;
    }

    .page-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
        margin-bottom: 22px;
    }

    .page-title {
        margin: 0;
        font-size: 25px;
        font-weight: 800;
    }

    .page-subtitle {
        margin-top: 7px;
        color: var(--muted);
        font-size: 13px;
    }

    .top-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .btn {
        min-height: 40px;
        padding: 0 14px;
        border: 1px solid var(--border);
        border-radius: 9px;
        background: white;
        color: var(--text);
        cursor: pointer;
        font-size: 11px;
        font-weight: 700;
        text-decoration: none;
    }

    .btn-primary {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    .btn:disabled {
        opacity: .5;
        cursor: not-allowed;
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
        padding: 22px;
        background: white;
        border: 1px solid var(--border);
        border-radius: 14px;
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 15px;
        margin-bottom: 18px;
    }

    .card-title {
        margin: 0;
        font-size: 16px;
        font-weight: 800;
    }

    .card-description {
        margin-top: 5px;
        color: var(--muted);
        font-size: 11px;
    }

    .section-badge {
        padding: 5px 9px;
        border-radius: 999px;
        background: #E8EEF8;
        color: var(--primary);
        font-size: 10px;
        font-weight: 800;
    }

    .info-box {
        padding: 14px;
        border: 1px solid #DBEAFE;
        border-radius: 11px;
        background: #EFF6FF;
        color: #1E40AF;
        font-size: 11px;
        line-height: 1.55;
    }

    .requirement {
        padding: 17px;
        margin-bottom: 13px;
        border: 1px solid var(--border);
        border-radius: 12px;
        background: #FBFCFE;
    }

    .requirement:last-child {
        margin-bottom: 0;
    }

    .requirement-top {
        display: grid;
        grid-template-columns:
            auto minmax(0, 1fr) auto;
        gap: 12px;
        align-items: flex-start;
    }

    .check {
        width: 21px;
        height: 21px;
        margin-top: 2px;
        accent-color: var(--primary);
    }

    .requirement-title {
        font-size: 13px;
        font-weight: 800;
        color: #1E293B;
    }

    .requirement-description {
        margin-top: 4px;
        color: var(--muted);
        font-size: 11px;
        line-height: 1.5;
    }

    .requirement-type {
        margin-top: 7px;
        color: #64748B;
        font-size: 9px;
        text-transform: uppercase;
    }

    .tag {
        padding: 5px 8px;
        border-radius: 999px;
        font-size: 9px;
        font-weight: 800;
        white-space: nowrap;
    }

    .tag-required {
        background: #FEE2E2;
        color: #991B1B;
    }

    .tag-optional {
        background: #E0F2FE;
        color: #075985;
    }

    .observation {
        margin-top: 14px;
    }

    .observation label,
    .evidence-title {
        display: block;
        margin-bottom: 6px;
        color: #475569;
        font-size: 10px;
        font-weight: 800;
    }

    .textarea,
    .input {
        width: 100%;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        background: white;
        color: var(--text);
        font: inherit;
        font-size: 11px;
        outline: none;
    }

    .textarea {
        min-height: 78px;
        padding: 10px;
        resize: vertical;
    }

    .input {
        min-height: 40px;
        padding: 0 10px;
    }

    .textarea:focus,
    .input:focus {
        border-color: #2563EB;
        box-shadow:
            0 0 0 3px
            rgba(37,99,235,.08);
    }

    .textarea:disabled {
        background: #F8FAFC;
    }

    .evidence-area {
        margin-top: 14px;
        padding-top: 13px;
        border-top: 1px solid #E2E8F0;
    }

    .evidence-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 9px 10px;
        margin-top: 7px;
        border-radius: 8px;
        background: white;
        border: 1px solid #E2E8F0;
    }

    .evidence-name {
        font-size: 10px;
        font-weight: 700;
        color: #334155;
    }

    .evidence-description {
        margin-top: 2px;
        color: #94A3B8;
        font-size: 9px;
    }

    .evidence-actions {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
    }

    .mini-btn {
        min-height: 29px;
        padding: 0 8px;
        border: 1px solid #DDE3EE;
        border-radius: 6px;
        background: white;
        color: #1A4B8C;
        cursor: pointer;
        font-size: 9px;
        font-weight: 700;
        text-decoration: none;
    }

    .mini-danger {
        color: #DC2626;
        border-color: #FECACA;
        background: #FEF2F2;
    }

    .add-evidence {
        margin-top: 9px;
    }

    .completion-box {
        margin-bottom: 16px;
        padding: 15px;
        border: 1px solid var(--border);
        border-radius: 11px;
        background: #F8FAFC;
    }

    .completion-top {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 8px;
        font-size: 11px;
        font-weight: 800;
    }

    .progress-track {
        height: 8px;
        overflow: hidden;
        border-radius: 999px;
        background: #E8EEF8;
    }

    .progress-value {
        height: 100%;
        border-radius: 999px;
        background: var(--primary);
    }

    .bottom-actions {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        margin-top: 19px;
        padding-top: 18px;
        border-top: 1px solid var(--border);
    }

    .empty {
        padding: 35px 20px;
        text-align: center;
        color: var(--muted);
        font-size: 12px;
    }

    .readonly {
        padding: 12px;
        border: 1px solid #FDE68A;
        border-radius: 9px;
        background: #FFFBEB;
        color: #92400E;
        font-size: 10px;
    }

    .sidebar {
        position: sticky;
        top: 90px;
        padding: 18px;
        background: white;
        border: 1px solid var(--border);
        border-radius: 14px;
    }

    .sidebar-row {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 10px;
        font-size: 10px;
    }

    .sidebar-row span {
        color: var(--muted);
    }

    .sidebar-row strong {
        color: #334155;
        text-align: right;
    }

    .sidebar-progress {
        margin: 17px 0;
        padding: 15px 0;
        border-top: 1px solid #EEF2F7;
        border-bottom: 1px solid #EEF2F7;
    }

    .steps {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .step {
        display: flex;
        gap: 8px;
        align-items: center;
        padding: 8px;
        border-radius: 8px;
        text-decoration: none;
        color: #475569;
        font-size: 10px;
        font-weight: 700;
    }

    .step.active {
        background: #E8EEF8;
        color: var(--primary);
    }

    .step-number {
        width: 24px;
        height: 24px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #CBD5E1;
        border-radius: 50%;
        font-size: 9px;
    }

    .step.complete .step-number {
        border-color: #A7F3D0;
        background: #ECFDF5;
        color: var(--success);
    }

    .step.active .step-number {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
    }

    .modal-backdrop {
        position: fixed;
        inset: 0;
        z-index: 100;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(15, 24, 39, .45);
    }

    .modal-card {
        width: min(560px, 100%);
        padding: 22px;
        border-radius: 14px;
        background: white;
        box-shadow: 0 20px 50px rgba(0,0,0,.2);
    }

    .modal-card h3 {
        margin: 0 0 5px;
        font-size: 16px;
    }

    .modal-card p {
        margin: 0 0 17px;
        color: var(--muted);
        font-size: 10px;
    }

    .modal-field {
        margin-bottom: 13px;
    }

    .modal-field label {
        display: block;
        margin-bottom: 5px;
        color: #475569;
        font-size: 10px;
        font-weight: 700;
    }

    .field-error {
        margin-top: 4px;
        color: #DC2626;
        font-size: 9px;
    }

    .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        margin-top: 17px;
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
        .page-top,
        .bottom-actions {
            flex-direction: column;
        }

        .requirement-top {
            grid-template-columns:
                auto minmax(0, 1fr);
        }

        .requirement-top .tag {
            grid-column: 2;
        }

        .evidence-row {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<div class="itsva-requisitos">

    <div class="page-top">

        <div>
            <h1 class="page-title">
                Requisitos de la Propuesta
            </h1>

            <div class="page-subtitle">
                Revisa los requisitos de la convocatoria,
                registra observaciones y agrega evidencias.
            </div>
        </div>

        <div class="top-actions">

            @if ($editable)
                <button
                    type="button"
                    class="btn"
                    wire:click="guardarBorrador"
                    wire:loading.attr="disabled"
                    wire:target="guardarBorrador"
                >
                    <span
                        wire:loading.remove
                        wire:target="guardarBorrador"
                    >
                        Guardar borrador
                    </span>

                    <span
                        wire:loading
                        wire:target="guardarBorrador"
                    >
                        Guardando...
                    </span>
                </button>
            @endif

            <button
                type="button"
                class="btn btn-primary"
                wire:click="vistaPrevia"
            >
                Vista previa
            </button>

        </div>

    </div>

    @if (! $editable)
        <div class="readonly">
            Esta propuesta está en estado
            <strong>
                {{ str_replace('_', ' ', $propuesta->estado) }}
            </strong>
            y sus requisitos se muestran en modo de solo lectura.
        </div>
    @endif

    <div class="layout">

        <main class="main">

            <div class="info-box">
                Los requisitos mostrados provienen directamente
                de la convocatoria seleccionada. Marca los que
                ya se encuentran cubiertos y agrega la evidencia
                correspondiente cuando sea necesario.
            </div>

            <section class="card">

                <div class="card-header">

                    <div>
                        <h2 class="card-title">
                            Requisitos de la convocatoria
                        </h2>

                        <div class="card-description">
                            Sección 3 del proceso de elaboración
                            de la propuesta.
                        </div>
                    </div>

                    <span class="section-badge">
                        Sección 3 de 7
                    </span>

                </div>

                <div class="completion-box">

                    <div class="completion-top">
                        <span>
                            Requisitos cumplidos:
                            {{ $cumplidosCount }}
                            de
                            {{ $totalRequisitos }}
                        </span>

                        <span>
                            {{ $porcentajeRequisitos }}%
                        </span>
                    </div>

                    <div class="progress-track">
                        <div
                            class="progress-value"
                            style="
                                width:
                                {{ $porcentajeRequisitos }}%;
                            "
                        ></div>
                    </div>

                </div>

                @forelse (
                    $requisitos
                    as $requisitoPropuesta
                )

                    @php
                        $origen =
                            $requisitoPropuesta
                                ->requisitoConvocatoria;

                        $evidencias =
                            $evidenciasPorRequisito
                                ->get(
                                    $requisitoPropuesta->id,
                                    collect()
                                );
                    @endphp

                    <div class="requirement">

                        <div class="requirement-top">

                            <input
                                class="check"
                                type="checkbox"
                                wire:model.live="
                                    cumplidos.{{
                                        $requisitoPropuesta->id
                                    }}
                                "
                                @disabled(! $editable)
                            >

                            <div>

                                <div class="requirement-title">
                                    {{
                                        $origen?->titulo
                                        ?? 'Requisito'
                                    }}
                                </div>

                                @if (
                                    filled(
                                        $origen?->descripcion
                                    )
                                )
                                    <div
                                        class="
                                            requirement-description
                                        "
                                    >
                                        {{
                                            $origen->descripcion
                                        }}
                                    </div>
                                @endif

                                @if (
                                    filled(
                                        $origen?->tipo_requisito
                                    )
                                )
                                    <div
                                        class="
                                            requirement-type
                                        "
                                    >
                                        {{
                                            str_replace(
                                                '_',
                                                ' ',
                                                $origen
                                                    ->tipo_requisito
                                            )
                                        }}
                                    </div>
                                @endif

                            </div>

                            <span
                                class="
                                    tag
                                    {{
                                        $origen?->obligatorio
                                            ? 'tag-required'
                                            : 'tag-optional'
                                    }}
                                "
                            >
                                {{
                                    $origen?->obligatorio
                                        ? 'Obligatorio'
                                        : 'Según aplique'
                                }}
                            </span>

                        </div>

                        <div class="observation">

                            <label>
                                Observaciones
                            </label>

                            <textarea
                                class="textarea"
                                maxlength="5000"
                                wire:model="
                                    observaciones.{{
                                        $requisitoPropuesta->id
                                    }}
                                "
                                placeholder="
                                    Añade comentarios sobre
                                    el cumplimiento del requisito...
                                "
                                @disabled(! $editable)
                            ></textarea>

                            @error(
                                'observaciones.'
                                . $requisitoPropuesta->id
                            )
                                <div class="field-error">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        <div class="evidence-area">

                            <span class="evidence-title">
                                Evidencias
                            </span>

                            @forelse (
                                $evidencias
                                as $evidencia
                            )

                                <div class="evidence-row">

                                    <div>
                                        <div class="evidence-name">
                                            {{ $evidencia->nombre }}
                                        </div>

                                        @if (
                                            filled(
                                                $evidencia
                                                    ->descripcion
                                            )
                                        )
                                            <div
                                                class="
                                                    evidence-description
                                                "
                                            >
                                                {{
                                                    $evidencia
                                                        ->descripcion
                                                }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="evidence-actions">

                                        @if (
                                            filled(
                                                $evidencia
                                                    ->ruta_archivo
                                            )
                                        )
                                            <button
                                                type="button"
                                                class="mini-btn"
                                                wire:click="
                                                    descargarEvidencia(
                                                        {{
                                                            $evidencia->id
                                                        }}
                                                    )
                                                "
                                            >
                                                Descargar
                                            </button>
                                        @endif

                                        @if (
                                            filled(
                                                $evidencia
                                                    ->url_archivo
                                            )
                                        )
                                            <a
                                                href="{{
                                                    $evidencia
                                                        ->url_archivo
                                                }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="mini-btn"
                                            >
                                                Abrir enlace
                                            </a>
                                        @endif

                                        @if ($editable)
                                            <button
                                                type="button"
                                                class="
                                                    mini-btn
                                                    mini-danger
                                                "
                                                wire:click="
                                                    eliminarEvidencia(
                                                        {{
                                                            $evidencia->id
                                                        }}
                                                    )
                                                "
                                                wire:confirm="
                                                    ¿Eliminar esta evidencia?
                                                "
                                            >
                                                Eliminar
                                            </button>
                                        @endif

                                    </div>

                                </div>

                            @empty

                                <div
                                    class="
                                        evidence-description
                                    "
                                >
                                    Sin evidencias registradas.
                                </div>

                            @endforelse

                            @if ($editable)
                                <button
                                    type="button"
                                    class="
                                        mini-btn
                                        add-evidence
                                    "
                                    wire:click="
                                        abrirEvidencia(
                                            {{
                                                $requisitoPropuesta
                                                    ->id
                                            }}
                                        )
                                    "
                                >
                                    + Agregar evidencia
                                </button>
                            @endif

                        </div>

                    </div>

                @empty

                    <div class="empty">
                        Esta convocatoria no tiene requisitos
                        registrados.
                    </div>

                @endforelse

                <div class="bottom-actions">

                    <button
                        type="button"
                        class="btn"
                        wire:click="volverAObjetivos"
                    >
                        ← Volver a Objetivos
                    </button>

                    <button
                        type="button"
                        class="btn btn-primary"
                        wire:click="
                            continuarAPresupuesto
                        "
                        wire:loading.attr="disabled"
                        wire:target="
                            continuarAPresupuesto
                        "
                    >
                        Continuar a Presupuesto →
                    </button>

                </div>

            </section>

        </main>

        <aside class="sidebar">

            <div class="sidebar-row">
                <span>Estado</span>

                <strong>
                    {{
                        ucfirst(
                            strtolower(
                                str_replace(
                                    '_',
                                    ' ',
                                    $propuesta->estado
                                )
                            )
                        )
                    }}
                </strong>
            </div>

            <div class="sidebar-row">
                <span>Cumplidos</span>
                <strong>{{ $cumplidosCount }}</strong>
            </div>

            <div class="sidebar-row">
                <span>Pendientes</span>
                <strong>{{ $pendientesCount }}</strong>
            </div>

            <div class="sidebar-progress">

                <div class="completion-top">
                    <span>Progreso general</span>
                    <span>{{ $progreso }}%</span>
                </div>

                <div class="progress-track">
                    <div
                        class="progress-value"
                        style="
                            width:
                            {{ $progreso }}%;
                        "
                    ></div>
                </div>

            </div>

            <div class="steps">

                <a
                    class="
                        step
                        {{
                            $pasos['datos']
                                ? 'complete'
                                : ''
                        }}
                    "
                    href="{{ route(
                        'filament.docente.pages.editor-propuesta',
                        ['propuesta' => $propuesta->id]
                    ) }}"
                >
                    <span class="step-number">
                        {{
                            $pasos['datos']
                                ? '✓'
                                : '1'
                        }}
                    </span>
                    Datos de propuesta
                </a>

                <a
                    class="
                        step
                        {{
                            $pasos['objetivos']
                                ? 'complete'
                                : ''
                        }}
                    "
                    href="{{ route(
                        'filament.docente.pages.objetivos-propuesta',
                        ['propuesta' => $propuesta->id]
                    ) }}"
                >
                    <span class="step-number">
                        {{
                            $pasos['objetivos']
                                ? '✓'
                                : '2'
                        }}
                    </span>
                    Objetivos
                </a>

                <a
                    class="
                        step
                        active
                        {{
                            $pasos['requisitos']
                                ? 'complete'
                                : ''
                        }}
                    "
                    href="{{ route(
                        'filament.docente.pages.requisitos-propuesta',
                        ['propuesta' => $propuesta->id]
                    ) }}"
                >
                    <span class="step-number">
                        3
                    </span>
                    Requisitos
                </a>

                <a
                    class="
                        step
                        {{
                            $pasos['presupuesto']
                                ? 'complete'
                                : ''
                        }}
                    "
                    href="{{ route(
                        'filament.docente.pages.presupuesto-propuesta',
                        ['propuesta' => $propuesta->id]
                    ) }}"
                >
                    <span class="step-number">
                        {{
                            $pasos['presupuesto']
                                ? '✓'
                                : '4'
                        }}
                    </span>
                    Presupuesto
                </a>

                <a
                    class="
                        step
                        {{
                            $pasos['cotizaciones']
                                ? 'complete'
                                : ''
                        }}
                    "
                    href="{{ route(
                        'filament.docente.pages.cotizaciones-propuesta',
                        ['propuesta' => $propuesta->id]
                    ) }}"
                >
                    <span class="step-number">
                        {{
                            $pasos['cotizaciones']
                                ? '✓'
                                : '5'
                        }}
                    </span>
                    Cotizaciones
                </a>

                <a
                    class="
                        step
                        {{
                            $pasos['cronograma']
                                ? 'complete'
                                : ''
                        }}
                    "
                    href="{{ route(
                        'filament.docente.pages.cronograma-propuesta',
                        ['propuesta' => $propuesta->id]
                    ) }}"
                >
                    <span class="step-number">
                        {{
                            $pasos['cronograma']
                                ? '✓'
                                : '6'
                        }}
                    </span>
                    Cronograma
                </a>

                <a
                    class="
                        step
                        {{
                            $pasos['entregables']
                                ? 'complete'
                                : ''
                        }}
                    "
                    href="{{ route(
                        'filament.docente.pages.entregables-propuesta',
                        ['propuesta' => $propuesta->id]
                    ) }}"
                >
                    <span class="step-number">
                        {{
                            $pasos['entregables']
                                ? '✓'
                                : '7'
                        }}
                    </span>
                    Entregables
                </a>

            </div>

            <div
                style="
                    margin-top:18px;
                    padding-top:16px;
                    border-top:1px solid #EEF2F7;
                "
            >

                <div class="sidebar-row">
                    <span>Propuesta</span>
                    <strong>
                        {{ $propuesta->titulo }}
                    </strong>
                </div>

                <div class="sidebar-row">
                    <span>Convocatoria</span>
                    <strong>
                        {{
                            $convocatoria?->titulo
                            ?? 'No disponible'
                        }}
                    </strong>
                </div>

            </div>

        </aside>

    </div>

    @if ($mostrarEvidencia)

        <div
            class="modal-backdrop"
            wire:click.self="
                cerrarEvidencia
            "
        >

            <div class="modal-card">

                <h3>
                    Agregar evidencia
                </h3>

                <p>
                    Adjunta un archivo privado,
                    registra una URL o ambas opciones.
                </p>

                <div class="modal-field">

                    <label>
                        Nombre *
                    </label>

                    <input
                        type="text"
                        class="input"
                        maxlength="255"
                        wire:model="
                            evidenciaNombre
                        "
                    >

                    @error('evidenciaNombre')
                        <div class="field-error">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

                <div class="modal-field">

                    <label>
                        Descripción
                    </label>

                    <textarea
                        class="textarea"
                        maxlength="5000"
                        wire:model="
                            evidenciaDescripcion
                        "
                    ></textarea>

                    @error('evidenciaDescripcion')
                        <div class="field-error">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

                <div class="modal-field">

                    <label>
                        Archivo
                    </label>

                    <input
                        type="file"
                        class="input"
                        wire:model="
                            archivoEvidencia
                        "
                        accept="
                            .pdf,.doc,.docx,
                            .xls,.xlsx,
                            .jpg,.jpeg,.png
                        "
                    >

                    <div
                        wire:loading
                        wire:target="
                            archivoEvidencia
                        "
                        style="
                            margin-top:5px;
                            font-size:9px;
                            color:#64748B;
                        "
                    >
                        Cargando archivo...
                    </div>

                    @error('archivoEvidencia')
                        <div class="field-error">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

                <div class="modal-field">

                    <label>
                        URL externa
                    </label>

                    <input
                        type="url"
                        class="input"
                        wire:model="
                            evidenciaUrl
                        "
                        placeholder="
                            https://...
                        "
                    >

                    @error('evidenciaUrl')
                        <div class="field-error">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

                <div class="modal-actions">

                    <button
                        type="button"
                        class="btn"
                        wire:click="
                            cerrarEvidencia
                        "
                    >
                        Cancelar
                    </button>

                    <button
                        type="button"
                        class="
                            btn
                            btn-primary
                        "
                        wire:click="
                            guardarEvidencia
                        "
                        wire:loading.attr="
                            disabled
                        "
                        wire:target="
                            guardarEvidencia
                        "
                    >
                        Guardar evidencia
                    </button>

                </div>

            </div>

        </div>

    @endif

</div>

</x-filament-panels::page>
