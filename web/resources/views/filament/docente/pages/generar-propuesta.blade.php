<x-filament-panels::page>

<style>
    .gp-page {
        max-width: 1050px;
        color: #1A2C4E;
    }

    .gp-intro {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
        margin-bottom: 20px;
    }

    .gp-intro h2 {
        margin: 0;
        font-size: 22px;
        font-weight: 700;
    }

    .gp-intro p {
        margin: 6px 0 0;
        color: #64748B;
        font-size: 13px;
        line-height: 1.5;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        min-height: 38px;
        padding: 0 13px;
        border: 1px solid #DDE3EE;
        border-radius: 8px;
        background: white;
        color: #1A4B8C;
        text-decoration: none;
        font-size: 11px;
        font-weight: 700;
    }

    .gp-card {
        background: white;
        border: 1px solid #DDE3EE;
        border-radius: 12px;
        margin-bottom: 18px;
        overflow: hidden;
    }

    .gp-card-header {
        padding: 17px 20px;
        border-bottom: 1px solid #EEF2F7;
    }

    .gp-card-header h3 {
        margin: 0;
        font-size: 15px;
        font-weight: 700;
    }

    .gp-card-header p {
        margin: 5px 0 0;
        color: #64748B;
        font-size: 10px;
    }

    .gp-card-body {
        padding: 20px;
    }

    .gp-field {
        margin-bottom: 16px;
    }

    .gp-field:last-child {
        margin-bottom: 0;
    }

    .gp-field label {
        display: block;
        margin-bottom: 6px;
        color: #475569;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .gp-select,
    .gp-input {
        width: 100%;
        min-height: 43px;
        padding: 0 12px;
        background: white;
        border: 1px solid #DDE3EE;
        border-radius: 8px;
        color: #1A2C4E;
        font-size: 12px;
    }

    .gp-select:focus,
    .gp-input:focus {
        outline: none;
        border-color: #2563EB;
        box-shadow:
            0 0 0 3px
            rgba(37, 99, 235, .08);
    }

    .field-error {
        margin-top: 5px;
        color: #DC2626;
        font-size: 10px;
    }

    .gp-summary {
        display: grid;
        grid-template-columns:
            repeat(4, minmax(0, 1fr));
        gap: 15px;
        margin-top: 18px;
    }

    .gp-info {
        padding: 13px;
        background: #F8FAFC;
        border-radius: 9px;
    }

    .gp-info-label {
        font-size: 9px;
        color: #64748B;
        font-weight: 700;
        text-transform: uppercase;
    }

    .gp-info-value {
        margin-top: 5px;
        color: #1A2C4E;
        font-size: 11px;
        font-weight: 600;
        line-height: 1.45;
    }

    .existing-box {
        margin-top: 16px;
        padding: 13px;
        border: 1px solid #BFDBFE;
        border-radius: 9px;
        background: #EFF6FF;
        color: #1E40AF;
        font-size: 11px;
        line-height: 1.5;
    }

    .gp-generator {
        background:
            linear-gradient(
                135deg,
                #0F2A57,
                #1A4B8C 60%,
                #2563EB
            );
        color: white;
        border-radius: 14px;
        padding: 27px;
        margin-bottom: 18px;
    }

    .gp-generator-icon {
        width: 46px;
        height: 46px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 15px;
        border-radius: 12px;
        background:
            rgba(255, 255, 255, .15);
        font-size: 23px;
    }

    .gp-generator h3 {
        margin: 0;
        font-size: 19px;
        font-weight: 700;
    }

    .gp-generator p {
        max-width: 720px;
        margin: 8px 0 20px;
        color: #DBEAFE;
        font-size: 12px;
        line-height: 1.6;
    }

    .gp-button {
        min-height: 41px;
        padding: 0 16px;
        border: 0;
        border-radius: 8px;
        background: white;
        color: #1A4B8C;
        cursor: pointer;
        font-size: 12px;
        font-weight: 700;
    }

    .gp-button:disabled {
        cursor: not-allowed;
        opacity: .55;
    }

    .gp-loading {
        display: inline-flex;
        align-items: center;
        min-height: 41px;
        padding: 0 16px;
        border-radius: 8px;
        background:
            rgba(255, 255, 255, .85);
        color: #1A4B8C;
        font-size: 12px;
        font-weight: 700;
    }

    .gp-empty {
        padding: 50px 20px;
        background: white;
        border: 1px solid #DDE3EE;
        border-radius: 12px;
        text-align: center;
    }

    .gp-empty-icon {
        margin-bottom: 12px;
        font-size: 34px;
    }

    .gp-empty h3 {
        margin: 0 0 7px;
        font-size: 16px;
        color: #1A2C4E;
    }

    .gp-empty p {
        max-width: 600px;
        margin: 0 auto;
        color: #64748B;
        font-size: 12px;
        line-height: 1.6;
    }

    .gp-empty-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 38px;
        margin-top: 17px;
        padding: 0 14px;
        border-radius: 8px;
        background: #1A4B8C;
        color: white;
        text-decoration: none;
        font-size: 11px;
        font-weight: 700;
    }

    .requirements-box {
        margin-top: 18px;
        padding-top: 18px;
        border-top: 1px solid #EEF2F7;
    }

    .requirements-box h4 {
        margin: 0 0 10px;
        font-size: 12px;
        font-weight: 700;
    }

    .requirement-row {
        padding: 8px 0;
        border-bottom: 1px solid #EEF2F7;
        font-size: 10px;
        color: #475569;
    }

    .requirement-row:last-child {
        border-bottom: 0;
    }

    .required {
        color: #B45309;
        font-weight: 700;
    }

    @media (max-width: 850px) {
        .gp-summary {
            grid-template-columns:
                repeat(2, 1fr);
        }
    }

    @media (max-width: 600px) {
        .gp-summary {
            grid-template-columns: 1fr;
        }

        .gp-intro {
            flex-direction: column;
        }
    }
</style>

<div class="gp-page">

    <div class="gp-intro">

        <div>

            <h2>
                Generar propuesta
            </h2>

            <p>
                Selecciona una convocatoria publicada
                o activa para preparar tu propuesta
                de proyecto.
            </p>

        </div>

        <a
            href="{{ route(
                'filament.docente.pages.propuestas'
            ) }}"
            class="back-link"
        >
            ← Mis Propuestas
        </a>

    </div>

    @if ($convocatorias->isEmpty())

        <div class="gp-empty">

            <div class="gp-empty-icon">
                📭
            </div>

            <h3>
                No hay convocatorias disponibles
            </h3>

            <p>
                Actualmente no existen convocatorias
                PUBLICADAS o ACTIVAS para generar una
                propuesta. Cuando una convocatoria sea
                aprobada aparecerá automáticamente aquí.
            </p>

            <a
                href="{{ route(
                    'filament.docente.pages.buscar-convocatorias'
                ) }}"
                class="gp-empty-action"
            >
                Buscar convocatorias
            </a>

        </div>

    @else

        <section class="gp-card">

            <div class="gp-card-header">

                <h3>
                    Datos de la propuesta
                </h3>

                <p>
                    Selecciona la convocatoria que
                    deseas utilizar.
                </p>

            </div>

            <div class="gp-card-body">

                <div class="gp-field">

                    <label>
                        Convocatoria *
                    </label>

                    <select
                        class="gp-select"
                        wire:model.live="
                            convocatoriaId
                        "
                    >

                        <option value="">
                            Selecciona una convocatoria
                        </option>

                        @foreach (
                            $convocatorias
                            as $convocatoria
                        )

                            <option
                                value="{{
                                    $convocatoria->id
                                }}"
                            >
                                {{ $convocatoria->titulo }}
                            </option>

                        @endforeach

                    </select>

                    @error('convocatoriaId')
                        <div class="field-error">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

                @if ($seleccionada)

                    <div class="gp-field">

                        <label>
                            Título de la propuesta *
                        </label>

                        <input
                            type="text"
                            class="gp-input"
                            wire:model="
                                tituloPropuesta
                            "
                            maxlength="500"
                        >

                        @error('tituloPropuesta')
                            <div class="field-error">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="gp-summary">

                        <div class="gp-info">

                            <div class="gp-info-label">
                                Organismo
                            </div>

                            <div class="gp-info-value">
                                {{ $seleccionada
                                    ->organismo?->nombre
                                    ?? 'No especificado' }}
                            </div>

                        </div>

                        <div class="gp-info">

                            <div class="gp-info-label">
                                Categoría
                            </div>

                            <div class="gp-info-value">
                                {{ $seleccionada
                                    ->categoria?->nombre
                                    ?? 'No especificada' }}
                            </div>

                        </div>

                        <div class="gp-info">

                            <div class="gp-info-label">
                                Fecha de cierre
                            </div>

                            <div class="gp-info-value">
                                {{ $seleccionada
                                    ->fecha_cierre
                                    ?->format('d/m/Y')
                                    ?? 'No especificada' }}
                            </div>

                        </div>

                        <div class="gp-info">

                            <div class="gp-info-label">
                                Requisitos
                            </div>

                            <div class="gp-info-value">
                                {{ $seleccionada
                                    ->requisitos
                                    ->count() }}
                                registrados
                            </div>

                        </div>

                    </div>

                    @if (
                        $seleccionada
                            ->requisitos
                            ->isNotEmpty()
                    )

                        <div class="requirements-box">

                            <h4>
                                Requisitos que se agregarán
                                a la propuesta
                            </h4>

                            @foreach (
                                $seleccionada->requisitos
                                as $requisito
                            )

                                <div class="requirement-row">

                                    {{ $requisito->titulo
                                        ?? 'Requisito' }}

                                    @if (
                                        $requisito
                                            ->obligatorio
                                    )

                                        <span class="required">
                                            · Obligatorio
                                        </span>

                                    @endif

                                </div>

                            @endforeach

                        </div>

                    @endif

                    @if ($propuestaExistente)

                        <div class="existing-box">

                            Ya tienes una propuesta para
                            esta convocatoria:

                            <strong>
                                {{ $propuestaExistente
                                    ->titulo }}
                            </strong>.

                            Al continuar no se creará
                            una propuesta duplicada; se
                            abrirá la existente.

                        </div>

                    @endif

                @endif

            </div>

        </section>

        <section class="gp-generator">

            <div class="gp-generator-icon">
                ✨
            </div>

            @if ($propuestaExistente)

                <h3>
                    Continuar propuesta
                </h3>

                <p>
                    La propuesta ya existe. Puedes
                    continuar directamente en el editor
                    sin crear una copia adicional.
                </p>

            @else

                <h3>
                    Preparar propuesta base
                </h3>

                <p>
                    La plataforma utilizará los datos
                    estructurados de la convocatoria y
                    sus requisitos para crear el borrador
                    inicial. Después podrás modificar
                    resumen, objetivos, requisitos,
                    presupuesto, cronograma y entregables.
                </p>

            @endif

            @if ($seleccionada)

                <div
                    x-data="{ enviando: false }"
                >

                    <button
                        type="button"
                        class="gp-button"
                        x-show="! enviando"
                        x-on:click="
                            enviando = true;
                            $wire.crearOContinuar();
                        "
                    >
                        {{
                            $propuestaExistente
                                ? 'Continuar propuesta'
                                : 'Crear propuesta'
                        }}
                    </button>

                    <div
                        class="gp-loading"
                        x-show="enviando"
                        x-cloak
                    >
                        Preparando...
                    </div>

                </div>

            @else

                <button
                    type="button"
                    class="gp-button"
                    disabled
                >
                    Selecciona una convocatoria
                </button>

            @endif

        </section>

    @endif

</div>

</x-filament-panels::page>
