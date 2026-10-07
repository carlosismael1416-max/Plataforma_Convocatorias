<x-filament-panels::page>

<style>
    .itsva-objectives {
        --text: #0F1827;
        --muted: #64748B;
        --primary: #1A4B8C;
        --primary-light: #2563EB;
        --border: #DDE3EE;
        --soft: #F0F4FB;
        --success: #059669;
        --warning: #D97706;

        color: var(--text);
    }

    .itsva-objectives * {
        box-sizing: border-box;
    }

    .page-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 24px;
        margin-bottom: 24px;
    }

    .page-title {
        margin: 0;
        font-size: 26px;
        line-height: 1.2;
        font-weight: 800;
    }

    .page-subtitle {
        margin-top: 8px;
        color: var(--muted);
        font-size: 14px;
    }

    .top-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .btn-itsva {
        min-height: 42px;
        padding: 0 18px;
        border-radius: 10px;
        border: 1px solid var(--border);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
    }

    .btn-secondary {
        background: #FFFFFF;
        color: var(--text);
    }

    .btn-primary {
        background: var(--primary);
        color: #FFFFFF;
        border-color: var(--primary);
    }

    .btn-itsva:disabled {
        opacity: .55;
        cursor: not-allowed;
    }

    .layout {
        display: grid;
        grid-template-columns:
            minmax(0, 1fr)
            300px;
        gap: 24px;
        align-items: start;
    }

    .main-content {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .card {
        background: #FFFFFF;
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 24px;
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 20px;
    }

    .card-title {
        margin: 0;
        font-size: 17px;
        font-weight: 800;
    }

    .card-description {
        margin-top: 5px;
        font-size: 12px;
        color: var(--muted);
        line-height: 1.5;
    }

    .section-badge {
        padding: 6px 10px;
        border-radius: 999px;
        background: #E8EEF8;
        color: var(--primary);
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
    }

    .field label {
        display: block;
        margin-bottom: 8px;
        color: #334155;
        font-size: 12px;
        font-weight: 700;
    }

    .required {
        color: #DC2626;
    }

    .textarea {
        width: 100%;
        min-height: 125px;
        padding: 13px 14px;
        border: 1px solid #CBD5E1;
        border-radius: 10px;
        background: #FFFFFF;
        color: var(--text);
        font: inherit;
        font-size: 13px;
        line-height: 1.55;
        resize: vertical;
        outline: none;
    }

    .textarea:focus {
        border-color: var(--primary-light);
        box-shadow:
            0 0 0 3px
            rgba(37, 99, 235, .10);
    }

    .textarea:disabled {
        background: #F8FAFC;
        color: #64748B;
    }

    .counter {
        margin-top: 6px;
        text-align: right;
        color: #94A3B8;
        font-size: 10px;
    }

    .field-error {
        margin-top: 6px;
        color: #DC2626;
        font-size: 10px;
    }

    .objective-item {
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 18px;
        margin-top: 14px;
        background: #FBFCFE;
    }

    .objective-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
        margin-bottom: 14px;
    }

    .objective-number {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 12px;
        font-weight: 800;
    }

    .number-circle {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #E8EEF8;
        color: var(--primary);
        font-size: 11px;
        font-weight: 800;
    }

    .objective-actions {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .order-btn,
    .remove-btn {
        min-height: 30px;
        padding: 0 9px;
        border-radius: 7px;
        cursor: pointer;
        font-size: 10px;
        font-weight: 700;
    }

    .order-btn {
        border: 1px solid #DDE3EE;
        background: white;
        color: #475569;
    }

    .remove-btn {
        border: 1px solid #FECACA;
        background: #FEF2F2;
        color: #DC2626;
    }

    .order-btn:disabled {
        cursor: not-allowed;
        opacity: .4;
    }

    .add-objective {
        width: 100%;
        margin-top: 16px;
        min-height: 46px;
        border: 1px dashed #AFC0D8;
        border-radius: 12px;
        background: #F8FAFD;
        color: var(--primary);
        font-size: 11px;
        font-weight: 800;
        cursor: pointer;
    }

    .info-box {
        display: flex;
        gap: 12px;
        padding: 15px;
        border-radius: 12px;
        background: #EFF6FF;
        border: 1px solid #DBEAFE;
        color: #1E40AF;
        font-size: 12px;
        line-height: 1.5;
    }

    .info-icon {
        width: 24px;
        height: 24px;
        flex: 0 0 24px;
        border-radius: 50%;
        background: #DBEAFE;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
    }

    .bottom-actions {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding-top: 20px;
        border-top: 1px solid var(--border);
        margin-top: 22px;
    }

    .readonly-warning {
        padding: 13px;
        border-radius: 10px;
        background: #FFFBEB;
        border: 1px solid #FDE68A;
        color: #92400E;
        font-size: 11px;
    }

    .sidebar {
        position: sticky;
        top: 90px;
        background: #FFFFFF;
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 20px;
    }

    .status-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--border);
    }

    .status-label {
        color: var(--muted);
        font-size: 12px;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 6px 10px;
        background: #FEF3C7;
        color: #92400E;
        font-size: 10px;
        font-weight: 800;
    }

    .status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--warning);
    }

    .progress {
        margin-top: 20px;
    }

    .progress-info {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        font-size: 11px;
        font-weight: 800;
    }

    .progress-info span:last-child {
        color: var(--primary);
    }

    .progress-track {
        height: 8px;
        border-radius: 999px;
        background: #E9EEF6;
        overflow: hidden;
    }

    .progress-value {
        height: 100%;
        background: var(--primary);
        border-radius: 999px;
        transition: width .2s ease;
    }

    .steps {
        display: flex;
        flex-direction: column;
        gap: 7px;
        margin-top: 20px;
    }

    .step {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 10px;
        border-radius: 10px;
        color: #475569;
        text-decoration: none;
        font-size: 11px;
        font-weight: 650;
    }

    .step-number {
        width: 27px;
        height: 27px;
        flex: 0 0 27px;
        border-radius: 50%;
        border: 1px solid #CBD5E1;
        background: #FFFFFF;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        font-weight: 800;
    }

    .step.complete .step-number {
        border-color: #A7F3D0;
        background: #ECFDF5;
        color: var(--success);
    }

    .step.active {
        background: #E8EEF8;
        color: var(--primary);
    }

    .step.active .step-number {
        border-color: var(--primary);
        background: var(--primary);
        color: #FFFFFF;
    }

    .summary {
        margin-top: 20px;
        padding-top: 18px;
        border-top: 1px solid var(--border);
    }

    .summary-title {
        margin-bottom: 12px;
        color: #64748B;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .summary-item {
        margin-bottom: 12px;
    }

    .summary-item span {
        display: block;
        margin-bottom: 2px;
        color: #94A3B8;
        font-size: 10px;
    }

    .summary-item strong {
        display: block;
        color: #334155;
        font-size: 11px;
        line-height: 1.4;
    }

    @media (max-width: 1024px) {
        .layout {
            grid-template-columns: 1fr;
        }

        .sidebar {
            position: static;
        }
    }

    @media (max-width: 700px) {
        .page-top,
        .bottom-actions {
            flex-direction: column;
        }

        .top-actions {
            width: 100%;
        }

        .btn-itsva {
            flex: 1;
        }

        .card {
            padding: 18px;
        }
    }
</style>

<div class="itsva-objectives">

    <div class="page-top">

        <div>

            <h1 class="page-title">
                Objetivos de la Propuesta
            </h1>

            <div class="page-subtitle">
                Define el objetivo general y los
                objetivos específicos del proyecto.
            </div>

        </div>

        <div class="top-actions">

            @if ($editable)

                <button
                    type="button"
                    class="
                        btn-itsva
                        btn-secondary
                    "
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
                class="
                    btn-itsva
                    btn-primary
                "
                wire:click="vistaPrevia"
            >
                Vista previa
            </button>

        </div>

    </div>

    @if (! $editable)

        <div class="readonly-warning">

            La propuesta está en estado

            <strong>
                {{
                    str_replace(
                        '_',
                        ' ',
                        $propuesta->estado
                    )
                }}
            </strong>

            y los objetivos se muestran en modo
            de solo lectura.

        </div>

    @endif

    <div class="layout">

        <main class="main-content">

            <div class="info-box">

                <div class="info-icon">
                    i
                </div>

                <div>
                    Los objetivos deben expresar de
                    forma clara qué se pretende
                    alcanzar con el proyecto. Procura
                    utilizar verbos en infinitivo y
                    resultados verificables.
                </div>

            </div>

            <section class="card">

                <div class="card-header">

                    <div>

                        <h2 class="card-title">
                            Objetivo general
                        </h2>

                        <div class="card-description">
                            Describe el propósito
                            principal que se pretende
                            alcanzar con el proyecto.
                        </div>

                    </div>

                    <span class="section-badge">
                        Sección 2 de 7
                    </span>

                </div>

                <div class="field">

                    <label>
                        Objetivo general
                        <span class="required">
                            *
                        </span>
                    </label>

                    <textarea
                        class="textarea"
                        maxlength="800"
                        wire:model.live.debounce.700ms="
                            objetivoGeneral
                        "
                        @disabled(! $editable)
                    ></textarea>

                    <div class="counter">
                        {{
                            mb_strlen(
                                $objetivoGeneral
                            )
                        }}
                        / 800 caracteres
                    </div>

                    @error('objetivoGeneral')
                        <div class="field-error">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

            </section>

            <section class="card">

                <div class="card-header">

                    <div>

                        <h2 class="card-title">
                            Objetivos específicos
                        </h2>

                        <div class="card-description">
                            Divide el objetivo general
                            en resultados concretos y
                            medibles.
                        </div>

                    </div>

                </div>

                @forelse (
                    $objetivosEspecificos
                    as $indice => $objetivo
                )

                    <div
                        class="objective-item"
                        wire:key="
                            objetivo-especifico-{{
                                $objetivo['id']
                                ?? 'nuevo-'.$indice
                            }}
                        "
                    >

                        <div class="objective-top">

                            <div class="objective-number">

                                <span class="number-circle">
                                    {{ $indice + 1 }}
                                </span>

                                Objetivo específico
                                {{ $indice + 1 }}

                            </div>

                            @if ($editable)

                                <div class="objective-actions">

                                    <button
                                        type="button"
                                        class="order-btn"
                                        wire:click="
                                            subirObjetivo(
                                                {{ $indice }}
                                            )
                                        "
                                        @disabled(
                                            $indice === 0
                                        )
                                        title="
                                            Subir posición
                                        "
                                    >
                                        ↑
                                    </button>

                                    <button
                                        type="button"
                                        class="order-btn"
                                        wire:click="
                                            bajarObjetivo(
                                                {{ $indice }}
                                            )
                                        "
                                        @disabled(
                                            $indice ===
                                            count(
                                                $objetivosEspecificos
                                            ) - 1
                                        )
                                        title="
                                            Bajar posición
                                        "
                                    >
                                        ↓
                                    </button>

                                    <button
                                        type="button"
                                        class="remove-btn"
                                        wire:click="
                                            eliminarObjetivoEspecifico(
                                                {{ $indice }}
                                            )
                                        "
                                        wire:confirm="
                                            ¿Eliminar este objetivo específico?
                                        "
                                    >
                                        Eliminar
                                    </button>

                                </div>

                            @endif

                        </div>

                        <div class="field">

                            <textarea
                                class="textarea"
                                maxlength="600"
                                wire:model.live.debounce.700ms="
                                    objetivosEspecificos.{{
                                        $indice
                                    }}.descripcion
                                "
                                @disabled(! $editable)
                            ></textarea>

                            <div class="counter">
                                {{
                                    mb_strlen(
                                        (string) (
                                            $objetivo[
                                                'descripcion'
                                            ] ?? ''
                                        )
                                    )
                                }}
                                / 600 caracteres
                            </div>

                            @error(
                                'objetivosEspecificos.'
                                . $indice
                                . '.descripcion'
                            )
                                <div class="field-error">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                    </div>

                @empty

                    <div class="info-box">
                        Esta propuesta todavía no
                        tiene objetivos específicos.
                    </div>

                @endforelse

                @error('objetivosEspecificos')
                    <div class="field-error">
                        {{ $message }}
                    </div>
                @enderror

                @if ($editable)

                    <button
                        type="button"
                        class="add-objective"
                        wire:click="
                            agregarObjetivoEspecifico
                        "
                    >
                        + Agregar objetivo específico
                    </button>

                @endif

                <div class="bottom-actions">

                    <button
                        type="button"
                        class="
                            btn-itsva
                            btn-secondary
                        "
                        wire:click="volverAlEditor"
                    >
                        ← Volver al Editor
                    </button>

                    <button
                        type="button"
                        class="
                            btn-itsva
                            btn-primary
                        "
                        wire:click="
                            continuarARequisitos
                        "
                        wire:loading.attr="
                            disabled
                        "
                        wire:target="
                            continuarARequisitos
                        "
                    >
                        Continuar a Requisitos →
                    </button>

                </div>

            </section>

        </main>

        <aside class="sidebar">

            <div class="status-row">

                <span class="status-label">
                    Estado
                </span>

                <span class="status-badge">

                    <span class="status-dot"></span>

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

                </span>

            </div>

            <div class="progress">

                <div class="progress-info">

                    <span>
                        Progreso
                    </span>

                    <span>
                        {{ $progreso }}%
                    </span>

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
                        [
                            'propuesta' =>
                                $propuesta->id
                        ]
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
                        active
                        {{
                            $pasos['objetivos']
                                ? 'complete'
                                : ''
                        }}
                    "
                    href="{{ route(
                        'filament.docente.pages.objetivos-propuesta',
                        [
                            'propuesta' =>
                                $propuesta->id
                        ]
                    ) }}"
                >
                    <span class="step-number">
                        2
                    </span>

                    Objetivos
                </a>

                <a
                    class="
                        step
                        {{
                            $pasos['requisitos']
                                ? 'complete'
                                : ''
                        }}
                    "
                    href="{{ route(
                        'filament.docente.pages.requisitos-propuesta',
                        [
                            'propuesta' =>
                                $propuesta->id
                        ]
                    ) }}"
                >
                    <span class="step-number">
                        {{
                            $pasos['requisitos']
                                ? '✓'
                                : '3'
                        }}
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
                        [
                            'propuesta' =>
                                $propuesta->id
                        ]
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
                        [
                            'propuesta' =>
                                $propuesta->id
                        ]
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
                        [
                            'propuesta' =>
                                $propuesta->id
                        ]
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
                        [
                            'propuesta' =>
                                $propuesta->id
                        ]
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

            <div class="summary">

                <div class="summary-title">
                    Propuesta
                </div>

                <div class="summary-item">

                    <span>
                        Título
                    </span>

                    <strong>
                        {{ $propuesta->titulo }}
                    </strong>

                </div>

                <div class="summary-item">

                    <span>
                        Convocatoria
                    </span>

                    <strong>
                        {{
                            $convocatoria?->titulo
                            ?? 'No disponible'
                        }}
                    </strong>

                </div>

                <div class="summary-item">

                    <span>
                        Organismo
                    </span>

                    <strong>
                        {{
                            $convocatoria
                                ?->organismo
                                ?->nombre
                            ?? 'No especificado'
                        }}
                    </strong>

                </div>

            </div>

        </aside>

    </div>

</div>

</x-filament-panels::page>
