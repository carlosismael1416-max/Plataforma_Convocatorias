<x-filament-panels::page>

<style>
    .editor-page {
        color: #1A2C4E;
    }

    .editor-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
        margin-bottom: 20px;
    }

    .editor-top h1 {
        margin: 0;
        color: #1A2C4E;
        font-size: 22px;
        font-weight: 700;
    }

    .editor-top p {
        margin: 6px 0 0;
        color: #64748B;
        font-size: 12px;
    }

    .top-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .btn-itsva {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 39px;
        padding: 0 14px;
        border-radius: 8px;
        cursor: pointer;
        text-decoration: none;
        font-size: 11px;
        font-weight: 700;
    }

    .btn-primary {
        border: 1px solid #1A4B8C;
        background: #1A4B8C;
        color: white;
    }

    .btn-secondary {
        border: 1px solid #DDE3EE;
        background: white;
        color: #1A4B8C;
    }

    .btn-itsva:disabled {
        cursor: not-allowed;
        opacity: .55;
    }

    .editor-layout {
        display: grid;
        grid-template-columns:
            minmax(0, 1fr)
            285px;
        gap: 20px;
        align-items: start;
    }

    .editor-main {
        min-width: 0;
    }

    .editor-card {
        padding: 20px;
        margin-bottom: 16px;
        background: white;
        border: 1px solid #DDE3EE;
        border-radius: 12px;
    }

    .card-heading {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 15px;
        margin-bottom: 18px;
    }

    .card-title {
        margin: 0;
        color: #1A2C4E;
        font-size: 15px;
        font-weight: 700;
    }

    .card-description {
        margin-top: 4px;
        color: #64748B;
        font-size: 10px;
        line-height: 1.5;
    }

    .section-badge {
        flex: 0 0 auto;
        padding: 5px 9px;
        border-radius: 20px;
        background: #E8EEF8;
        color: #1A4B8C;
        font-size: 9px;
        font-weight: 700;
    }

    .form-grid {
        display: grid;
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
        gap: 15px;
    }

    .form-field.full {
        grid-column: 1 / -1;
    }

    .form-field label {
        display: block;
        margin-bottom: 6px;
        color: #475569;
        font-size: 10px;
        font-weight: 700;
    }

    .required {
        color: #DC2626;
    }

    .itsva-input,
    .itsva-textarea {
        width: 100%;
        border: 1px solid #DDE3EE;
        border-radius: 8px;
        background: white;
        color: #1A2C4E;
        font-size: 12px;
    }

    .itsva-input {
        min-height: 42px;
        padding: 0 11px;
    }

    .itsva-textarea {
        min-height: 145px;
        padding: 11px;
        resize: vertical;
        line-height: 1.55;
    }

    .itsva-input:focus,
    .itsva-textarea:focus {
        outline: none;
        border-color: #2563EB;
        box-shadow:
            0 0 0 3px
            rgba(37, 99, 235, .08);
    }

    .itsva-input:disabled,
    .itsva-textarea:disabled {
        background: #F8FAFC;
        color: #64748B;
    }

    .reference-box {
        min-height: 42px;
        padding: 11px;
        border: 1px solid #E2E8F0;
        border-radius: 8px;
        background: #F8FAFC;
        color: #475569;
        font-size: 11px;
        line-height: 1.45;
    }

    .counter {
        margin-top: 5px;
        color: #94A3B8;
        text-align: right;
        font-size: 9px;
    }

    .field-error {
        margin-top: 5px;
        color: #DC2626;
        font-size: 9px;
    }

    .editor-bottom {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 17px;
    }

    .sidebar-card {
        position: sticky;
        top: 20px;
        padding: 18px;
        background: white;
        border: 1px solid #DDE3EE;
        border-radius: 12px;
    }

    .status-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
    }

    .status-label,
    .progress-label {
        color: #64748B;
        font-size: 10px;
        font-weight: 700;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 8px;
        border-radius: 20px;
        background: #F1F5F9;
        color: #475569;
        font-size: 9px;
        font-weight: 700;
    }

    .status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #2563EB;
    }

    .progress-header {
        margin-top: 18px;
    }

    .progress-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .progress-number {
        color: #1A4B8C;
        font-size: 11px;
        font-weight: 800;
    }

    .progress-track {
        height: 7px;
        margin-top: 8px;
        overflow: hidden;
        border-radius: 20px;
        background: #E8EEF8;
    }

    .progress-value {
        height: 100%;
        border-radius: 20px;
        background: #1A4B8C;
        transition: width .2s ease;
    }

    .steps {
        display: flex;
        flex-direction: column;
        gap: 5px;
        margin-top: 20px;
    }

    .step {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 9px;
        border-radius: 8px;
        color: #475569;
        text-decoration: none;
        font-size: 10px;
        font-weight: 600;
    }

    .step:hover {
        background: #F8FAFC;
    }

    .step.active {
        background: #E8EEF8;
        color: #1A4B8C;
        font-weight: 700;
    }

    .step.done .step-number {
        background: #D1FAE5;
        color: #047857;
    }

    .step-number {
        width: 25px;
        height: 25px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        border-radius: 50%;
        background: #F1F5F9;
        color: #64748B;
        font-size: 9px;
        font-weight: 700;
    }

    .step.active .step-number {
        background: #1A4B8C;
        color: white;
    }

    .summary {
        margin-top: 20px;
        padding-top: 17px;
        border-top: 1px solid #EEF2F7;
    }

    .summary-title {
        margin-bottom: 11px;
        color: #1A2C4E;
        font-size: 11px;
        font-weight: 700;
    }

    .summary-item {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 6px 0;
        color: #64748B;
        font-size: 9px;
    }

    .summary-item strong {
        max-width: 145px;
        color: #1A2C4E;
        text-align: right;
        font-weight: 700;
    }

    .autosave {
        margin-top: 16px;
        padding: 9px;
        border-radius: 8px;
        background: #F8FAFC;
        color: #64748B;
        text-align: center;
        font-size: 9px;
    }

    .readonly-warning {
        margin-bottom: 18px;
        padding: 12px 14px;
        border: 1px solid #FDE68A;
        border-radius: 9px;
        background: #FFFBEB;
        color: #92400E;
        font-size: 11px;
        line-height: 1.5;
    }

    @media (max-width: 1000px) {
        .editor-layout {
            grid-template-columns: 1fr;
        }

        .sidebar-card {
            position: static;
        }
    }

    @media (max-width: 700px) {
        .editor-top {
            flex-direction: column;
        }

        .form-grid {
            grid-template-columns: 1fr;
        }

        .form-field.full {
            grid-column: auto;
        }
    }
</style>

<div class="editor-page">

    <div class="editor-top">

        <div>

            <h1>
                Editor de propuesta
            </h1>

            <p>
                Propuesta #{{ $propuesta->id }}
                · Versión {{ $propuesta->version }}
            </p>

        </div>

        <div class="top-actions">

            <a
                href="{{ route(
                    'filament.docente.pages.propuestas'
                ) }}"
                class="
                    btn-itsva
                    btn-secondary
                "
            >
                ← Mis Propuestas
            </a>

            <button
                type="button"
                class="
                    btn-itsva
                    btn-secondary
                "
                wire:click="irAVistaPrevia"
            >
                Vista previa
            </button>

        </div>

    </div>

    @if (! $editable)

        <div class="readonly-warning">

            Esta propuesta está en estado

            <strong>
                {{
                    str_replace(
                        '_',
                        ' ',
                        $propuesta->estado
                    )
                }}
            </strong>

            y se muestra en modo de solo lectura.

        </div>

    @endif

    <div class="editor-layout">

        <main class="editor-main">

            <section class="editor-card">

                <div class="card-heading">

                    <div>

                        <h2 class="card-title">
                            Datos de propuesta
                        </h2>

                        <div class="card-description">
                            Información principal
                            del proyecto.
                        </div>

                    </div>

                    <span class="section-badge">
                        Sección 1 de 7
                    </span>

                </div>

                <div class="form-grid">

                    <div
                        class="
                            form-field
                            full
                        "
                    >

                        <label>
                            Título de la propuesta
                            <span class="required">
                                *
                            </span>
                        </label>

                        <input
                            class="itsva-input"
                            type="text"
                            maxlength="500"
                            wire:model.live.debounce.700ms="
                                titulo
                            "
                            @disabled(! $editable)
                        >

                        <div class="counter">
                            {{ mb_strlen($titulo) }}
                            / 500 caracteres
                        </div>

                        @error('titulo')
                            <div class="field-error">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="form-field">

                        <label>
                            Responsable
                        </label>

                        <div class="reference-box">
                            {{
                                trim(
                                    $propuesta->usuario->name
                                    . ' '
                                    . (
                                        $propuesta
                                            ->usuario
                                            ->apellidos
                                        ?? ''
                                    )
                                )
                            }}
                        </div>

                    </div>

                    <div class="form-field">

                        <label>
                            Área temática
                        </label>

                        <div class="reference-box">
                            {{
                                $convocatoria
                                    ?->categoria
                                    ?->nombre
                                ?? 'No especificada'
                            }}
                        </div>

                    </div>

                    <div class="form-field">

                        <label>
                            Organismo
                        </label>

                        <div class="reference-box">
                            {{
                                $convocatoria
                                    ?->organismo
                                    ?->nombre
                                ?? 'No especificado'
                            }}
                        </div>

                    </div>

                    <div class="form-field">

                        <label>
                            Monto máximo
                        </label>

                        <div class="reference-box">
                            {{ $montoMaximo }}
                        </div>

                    </div>

                </div>

            </section>

            <section class="editor-card">

                <div class="card-heading">

                    <div>

                        <h2 class="card-title">
                            Resumen ejecutivo
                        </h2>

                        <div class="card-description">
                            Explica brevemente el
                            problema, la propuesta y
                            el resultado esperado.
                        </div>

                    </div>

                </div>

                <div class="form-field">

                    <label>
                        Resumen
                    </label>

                    <textarea
                        class="itsva-textarea"
                        maxlength="10000"
                        wire:model.live.debounce.700ms="
                            resumen
                        "
                        @disabled(! $editable)
                    ></textarea>

                    <div class="counter">
                        {{ mb_strlen($resumen) }}
                        / 10,000 caracteres
                    </div>

                    @error('resumen')
                        <div class="field-error">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

            </section>

            <section class="editor-card">

                <div class="card-heading">

                    <div>

                        <h2 class="card-title">
                            Justificación
                        </h2>

                        <div class="card-description">
                            Describe la necesidad que
                            atiende el proyecto y su
                            relevancia.
                        </div>

                    </div>

                </div>

                <div class="form-field">

                    <label>
                        Justificación del proyecto
                    </label>

                    <textarea
                        class="itsva-textarea"
                        maxlength="10000"
                        wire:model.live.debounce.700ms="
                            justificacion
                        "
                        @disabled(! $editable)
                    ></textarea>

                    <div class="counter">
                        {{ mb_strlen($justificacion) }}
                        / 10,000 caracteres
                    </div>

                    @error('justificacion')
                        <div class="field-error">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

            </section>

            <section class="editor-card">

                <div class="card-heading">

                    <div>

                        <h2 class="card-title">
                            Metodología
                        </h2>

                        <div class="card-description">
                            Indica de forma general
                            cómo se desarrollará el
                            proyecto.
                        </div>

                    </div>

                </div>

                <div class="form-field">

                    <label>
                        Metodología propuesta
                    </label>

                    <textarea
                        class="itsva-textarea"
                        maxlength="10000"
                        wire:model.live.debounce.700ms="
                            metodologia
                        "
                        placeholder="
                            Describe etapas, actividades,
                            técnicas y métodos...
                        "
                        @disabled(! $editable)
                    ></textarea>

                    <div class="counter">
                        {{ mb_strlen($metodologia) }}
                        / 10,000 caracteres
                    </div>

                    @error('metodologia')
                        <div class="field-error">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

            </section>

            <section class="editor-card">

                <div class="card-heading">

                    <div>

                        <h2 class="card-title">
                            Impacto esperado
                        </h2>

                        <div class="card-description">
                            Resume los beneficios
                            académicos, científicos,
                            tecnológicos o
                            institucionales.
                        </div>

                    </div>

                </div>

                <div class="form-field">

                    <label>
                        Impacto esperado
                    </label>

                    <textarea
                        class="itsva-textarea"
                        maxlength="10000"
                        wire:model.live.debounce.700ms="
                            impactoEsperado
                        "
                        placeholder="
                            Describe los principales
                            resultados e impactos...
                        "
                        @disabled(! $editable)
                    ></textarea>

                    <div class="counter">
                        {{
                            mb_strlen(
                                $impactoEsperado
                            )
                        }}
                        / 10,000 caracteres
                    </div>

                    @error('impactoEsperado')
                        <div class="field-error">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

                <div class="editor-bottom">

                    @if ($editable)

                        <button
                            class="
                                btn-itsva
                                btn-secondary
                            "
                            type="button"
                            wire:click="
                                guardarBorrador
                            "
                            wire:loading.attr="
                                disabled
                            "
                            wire:target="
                                guardarBorrador
                            "
                        >
                            <span
                                wire:loading.remove
                                wire:target="
                                    guardarBorrador
                                "
                            >
                                Guardar borrador
                            </span>

                            <span
                                wire:loading
                                wire:target="
                                    guardarBorrador
                                "
                            >
                                Guardando...
                            </span>
                        </button>

                    @endif

                    <button
                        class="
                            btn-itsva
                            btn-primary
                        "
                        type="button"
                        wire:click="
                            continuarAObjetivos
                        "
                        wire:loading.attr="
                            disabled
                        "
                        wire:target="
                            continuarAObjetivos
                        "
                    >
                        Continuar a Objetivos →
                    </button>

                </div>

            </section>

        </main>

        <aside class="sidebar-card">

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

            <div class="progress-header">

                <div class="progress-top">

                    <span class="progress-label">
                        Progreso
                    </span>

                    <span class="progress-number">
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
                        active
                        {{
                            $pasos['datos']
                                ? 'done'
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
                        1
                    </span>

                    Datos de propuesta
                </a>

                <a
                    class="
                        step
                        {{
                            $pasos['objetivos']
                                ? 'done'
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
                                ? 'done'
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
                        3
                    </span>

                    Requisitos
                </a>

                <a
                    class="
                        step
                        {{
                            $pasos['presupuesto']
                                ? 'done'
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
                        4
                    </span>

                    Presupuesto
                </a>

                <a
                    class="
                        step
                        {{
                            $pasos['cotizaciones']
                                ? 'done'
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
                        5
                    </span>

                    Cotizaciones
                </a>

                <a
                    class="
                        step
                        {{
                            $pasos['cronograma']
                                ? 'done'
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
                        6
                    </span>

                    Cronograma
                </a>

                <a
                    class="
                        step
                        {{
                            $pasos['entregables']
                                ? 'done'
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
                        7
                    </span>

                    Entregables
                </a>

            </div>

            <div class="summary">

                <div class="summary-title">
                    Convocatoria
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

                <div class="summary-item">

                    <span>
                        Área
                    </span>

                    <strong>
                        {{
                            $convocatoria
                                ?->categoria
                                ?->nombre
                            ?? 'No especificada'
                        }}
                    </strong>

                </div>

                <div class="summary-item">

                    <span>
                        Fecha de cierre
                    </span>

                    <strong>
                        {{
                            $convocatoria
                                ?->fecha_cierre
                                ?->format('d/m/Y')
                            ?? 'Sin fecha'
                        }}
                    </strong>

                </div>

                <div class="summary-item">

                    <span>
                        Monto máximo
                    </span>

                    <strong>
                        {{ $montoMaximo }}
                    </strong>

                </div>

                <div class="summary-item">

                    <span>
                        Requisitos
                    </span>

                    <strong>
                        {{ $requisitosCumplidos }}
                        /
                        {{
                            $propuesta
                                ->requisitos_count
                        }}
                    </strong>

                </div>

            </div>

            <div class="autosave">

                Última actualización:

                {{
                    $propuesta
                        ->updated_at
                        ?->format(
                            'd/m/Y H:i'
                        )
                    ?? 'Sin guardar'
                }}

            </div>

        </aside>

    </div>

</div>

</x-filament-panels::page>
