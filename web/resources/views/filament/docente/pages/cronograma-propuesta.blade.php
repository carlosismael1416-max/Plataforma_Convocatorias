<x-filament-panels::page>

<style>
    .itsva-cronograma {
        --text: #0F1827;
        --muted: #64748B;
        --primary: #1A4B8C;
        --border: #DDE3EE;
        --success: #059669;
        --warning: #D97706;
        --danger: #DC2626;

        color: var(--text);
    }

    .itsva-cronograma * {
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

    .top-actions,
    .activity-actions,
    .form-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .btn,
    .mini-btn {
        border: 1px solid var(--border);
        border-radius: 8px;
        background: white;
        color: var(--text);
        cursor: pointer;
        font-weight: 700;
    }

    .btn {
        min-height: 40px;
        padding: 0 14px;
        font-size: 11px;
    }

    .mini-btn {
        min-height: 29px;
        padding: 0 8px;
        font-size: 9px;
    }

    .btn-primary {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    .mini-danger {
        background: #FEF2F2;
        border-color: #FECACA;
        color: var(--danger);
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

    .timeline {
        display: flex;
        flex-direction: column;
        gap: 13px;
    }

    .activity {
        display: grid;
        grid-template-columns:
            42px minmax(0, 1fr);
        gap: 12px;
        position: relative;
    }

    .activity:not(:last-child)::before {
        content: "";
        position: absolute;
        left: 20px;
        top: 39px;
        bottom: -16px;
        width: 2px;
        background: #DCE5F1;
    }

    .activity-number {
        width: 42px;
        height: 42px;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        background: #E8EEF8;
        color: var(--primary);
        font-size: 10px;
        font-weight: 900;
    }

    .activity-card {
        padding: 16px;
        border: 1px solid var(--border);
        border-radius: 12px;
        background: #FBFCFE;
    }

    .activity-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
    }

    .activity-title {
        color: #1E293B;
        font-size: 12px;
        font-weight: 800;
    }

    .activity-description {
        margin-top: 4px;
        color: var(--muted);
        font-size: 10px;
        line-height: 1.5;
    }

    .status {
        padding: 5px 8px;
        border-radius: 999px;
        font-size: 9px;
        font-weight: 800;
        white-space: nowrap;
    }

    .status-pendiente {
        background: #FEF3C7;
        color: #92400E;
    }

    .status-proceso {
        background: #DBEAFE;
        color: #1E40AF;
    }

    .status-completada {
        background: #D1FAE5;
        color: #065F46;
    }

    .status-cancelada {
        background: #FEE2E2;
        color: #991B1B;
    }

    .activity-meta {
        display: grid;
        grid-template-columns:
            repeat(4, minmax(0, 1fr));
        gap: 9px;
        margin-top: 13px;
    }

    .meta-box {
        padding: 9px;
        border: 1px solid #E8EDF4;
        border-radius: 8px;
        background: white;
    }

    .meta-label {
        display: block;
        margin-bottom: 3px;
        color: #94A3B8;
        font-size: 8px;
    }

    .meta-value {
        color: #334155;
        font-size: 9px;
        font-weight: 800;
    }

    .activity-progress {
        margin-top: 12px;
    }

    .activity-progress-top {
        display: flex;
        justify-content: space-between;
        margin-bottom: 5px;
        color: #64748B;
        font-size: 9px;
        font-weight: 700;
    }

    .progress-track {
        height: 7px;
        overflow: hidden;
        border-radius: 999px;
        background: #E8EEF8;
    }

    .progress-value {
        height: 100%;
        border-radius: 999px;
        background: var(--primary);
    }

    .activity-actions {
        margin-top: 12px;
        padding-top: 11px;
        border-top: 1px solid var(--border);
    }

    .calendar-summary {
        display: grid;
        grid-template-columns:
            repeat(4, minmax(0, 1fr));
        gap: 10px;
        margin-top: 17px;
    }

    .summary-box {
        padding: 13px;
        border: 1px solid #E7ECF3;
        border-radius: 10px;
        background: #F8FAFC;
    }

    .summary-box span {
        display: block;
        margin-bottom: 4px;
        color: #94A3B8;
        font-size: 9px;
    }

    .summary-box strong {
        color: #1E293B;
        font-size: 11px;
    }

    .form-block {
        padding: 17px;
        border: 1px dashed #AFC0D8;
        border-radius: 11px;
        background: #F8FAFD;
    }

    .form-title {
        margin-bottom: 14px;
        font-size: 12px;
        font-weight: 800;
    }

    .form-grid {
        display: grid;
        grid-template-columns:
            2fr 1fr 1fr;
        gap: 11px;
    }

    .form-grid + .form-grid {
        margin-top: 11px;
    }

    .field label {
        display: block;
        margin-bottom: 5px;
        color: #475569;
        font-size: 9px;
        font-weight: 800;
    }

    .input,
    .select,
    .textarea {
        width: 100%;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        background: white;
        color: var(--text);
        font: inherit;
        font-size: 11px;
        outline: none;
    }

    .input,
    .select {
        min-height: 40px;
        padding: 0 10px;
    }

    .textarea {
        min-height: 80px;
        margin-top: 11px;
        padding: 10px;
        resize: vertical;
    }

    .input:focus,
    .select:focus,
    .textarea:focus {
        border-color: #2563EB;
        box-shadow:
            0 0 0 3px
            rgba(37,99,235,.08);
    }

    .field-error {
        margin-top: 4px;
        color: #DC2626;
        font-size: 9px;
    }

    .form-actions {
        justify-content: flex-end;
        margin-top: 13px;
    }

    .bottom-actions {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        margin-top: 19px;
        padding-top: 17px;
        border-top: 1px solid var(--border);
    }

    .empty {
        padding: 35px 20px;
        text-align: center;
        color: var(--muted);
        font-size: 11px;
    }

    .readonly {
        margin-bottom: 15px;
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
        margin-bottom: 9px;
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
        margin: 16px 0;
        padding: 14px 0;
        border-top: 1px solid #EEF2F7;
        border-bottom: 1px solid #EEF2F7;
    }

    .progress-info {
        display: flex;
        justify-content: space-between;
        margin-bottom: 7px;
        font-size: 10px;
        font-weight: 800;
    }

    .steps {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .step {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px;
        border-radius: 8px;
        color: #475569;
        text-decoration: none;
        font-size: 10px;
        font-weight: 700;
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

    .step.active {
        background: #E8EEF8;
        color: var(--primary);
    }

    .step.active .step-number {
        border-color: var(--primary);
        background: var(--primary);
        color: white;
    }

    @media (max-width: 1000px) {
        .layout {
            grid-template-columns: 1fr;
        }

        .sidebar {
            position: static;
        }
    }

    @media (max-width: 800px) {
        .activity-meta,
        .calendar-summary {
            grid-template-columns:
                repeat(2, 1fr);
        }

        .form-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 600px) {
        .page-top,
        .bottom-actions {
            flex-direction: column;
        }

        .activity-meta,
        .calendar-summary {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="itsva-cronograma">

    <div class="page-top">

        <div>
            <h1 class="page-title">
                Cronograma de la Propuesta
            </h1>

            <div class="page-subtitle">
                Organiza las actividades del proyecto,
                responsables y periodos de ejecución.
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
                    Guardar borrador
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
                {{
                    str_replace(
                        '_',
                        ' ',
                        $propuesta->estado
                    )
                }}
            </strong>
            y el cronograma se muestra
            en modo de solo lectura.
        </div>
    @endif

    <div class="layout">

        <main class="main">

            <div class="info-box">
                Define las actividades principales,
                fechas, responsable, estado y porcentaje
                de avance. Las actividades también podrán
                relacionarse posteriormente con entregables.
            </div>

            <section class="card">

                <div class="card-header">

                    <div>
                        <h2 class="card-title">
                            Actividades programadas
                        </h2>

                        <div class="card-description">
                            Cronograma general de ejecución
                            de la propuesta.
                        </div>
                    </div>

                    <span class="section-badge">
                        Sección 6 de 7
                    </span>

                </div>

                @if ($actividades->isNotEmpty())

                    <div class="timeline">

                        @foreach (
                            $actividades
                            as $indice => $item
                        )

                            @php
                                $claseEstado =
                                    match (
                                        $item->estado
                                    ) {
                                        'EN_PROCESO' =>
                                            'status-proceso',

                                        'COMPLETADA' =>
                                            'status-completada',

                                        'CANCELADA' =>
                                            'status-cancelada',

                                        default =>
                                            'status-pendiente',
                                    };

                                $duracion =
                                    $item->fecha_inicio
                                    && $item->fecha_fin
                                        ? $item
                                            ->fecha_inicio
                                            ->diffInDays(
                                                $item
                                                    ->fecha_fin
                                            ) + 1
                                        : null;
                            @endphp

                            <div class="activity">

                                <div class="activity-number">
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

                                <div class="activity-card">

                                    <div class="activity-top">

                                        <div>

                                            <div class="activity-title">
                                                {{ $item->actividad }}
                                            </div>

                                            @if ($item->descripcion)
                                                <div
                                                    class="
                                                        activity-description
                                                    "
                                                >
                                                    {{
                                                        $item->descripcion
                                                    }}
                                                </div>
                                            @endif

                                        </div>

                                        <span
                                            class="
                                                status
                                                {{ $claseEstado }}
                                            "
                                        >
                                            {{
                                                $estados[
                                                    $item->estado
                                                ]
                                                ?? $item->estado
                                            }}
                                        </span>

                                    </div>

                                    <div class="activity-meta">

                                        <div class="meta-box">
                                            <span class="meta-label">
                                                Inicio
                                            </span>

                                            <span class="meta-value">
                                                {{
                                                    $item
                                                        ->fecha_inicio
                                                        ?->format(
                                                            'd/m/Y'
                                                        )
                                                }}
                                            </span>
                                        </div>

                                        <div class="meta-box">
                                            <span class="meta-label">
                                                Fin
                                            </span>

                                            <span class="meta-value">
                                                {{
                                                    $item
                                                        ->fecha_fin
                                                        ?->format(
                                                            'd/m/Y'
                                                        )
                                                }}
                                            </span>
                                        </div>

                                        <div class="meta-box">
                                            <span class="meta-label">
                                                Duración
                                            </span>

                                            <span class="meta-value">
                                                {{
                                                    $duracion
                                                        !== null
                                                        ? $duracion
                                                            . ' día(s)'
                                                        : '—'
                                                }}
                                            </span>
                                        </div>

                                        <div class="meta-box">
                                            <span class="meta-label">
                                                Responsable
                                            </span>

                                            <span class="meta-value">
                                                {{
                                                    $item->responsable
                                                    ?: 'No asignado'
                                                }}
                                            </span>
                                        </div>

                                    </div>

                                    <div class="activity-progress">

                                        <div class="activity-progress-top">

                                            <span>
                                                Avance
                                            </span>

                                            <span>
                                                {{
                                                    number_format(
                                                        (float)
                                                        $item
                                                            ->porcentaje_avance,
                                                        0
                                                    )
                                                }}%
                                            </span>

                                        </div>

                                        <div class="progress-track">

                                            <div
                                                class="progress-value"
                                                style="
                                                    width:
                                                    {{
                                                        min(
                                                            100,
                                                            max(
                                                                0,
                                                                (float)
                                                                $item
                                                                    ->porcentaje_avance
                                                            )
                                                        )
                                                    }}%;
                                                "
                                            ></div>

                                        </div>

                                    </div>

                                    @if ($editable)

                                        <div class="activity-actions">

                                            <button
                                                type="button"
                                                class="mini-btn"
                                                wire:click="
                                                    moverArriba(
                                                        {{ $item->id }}
                                                    )
                                                "
                                                @disabled($loop->first)
                                            >
                                                ↑
                                            </button>

                                            <button
                                                type="button"
                                                class="mini-btn"
                                                wire:click="
                                                    moverAbajo(
                                                        {{ $item->id }}
                                                    )
                                                "
                                                @disabled($loop->last)
                                            >
                                                ↓
                                            </button>

                                            <button
                                                type="button"
                                                class="mini-btn"
                                                wire:click="
                                                    editarActividad(
                                                        {{ $item->id }}
                                                    )
                                                "
                                            >
                                                Editar
                                            </button>

                                            <button
                                                type="button"
                                                class="
                                                    mini-btn
                                                    mini-danger
                                                "
                                                wire:click="
                                                    eliminarActividad(
                                                        {{ $item->id }}
                                                    )
                                                "
                                                wire:confirm="
                                                    ¿Eliminar esta actividad?
                                                "
                                            >
                                                Eliminar
                                            </button>

                                        </div>

                                    @endif

                                </div>

                            </div>

                        @endforeach

                    </div>

                @else

                    <div class="empty">
                        Esta propuesta todavía no tiene
                        actividades registradas.
                    </div>

                @endif

                <div class="calendar-summary">

                    <div class="summary-box">
                        <span>
                            Inicio del proyecto
                        </span>

                        <strong>
                            {{
                                $inicioProyecto
                                    ? Carbon\Carbon::parse(
                                        $inicioProyecto
                                    )->format('d/m/Y')
                                    : '—'
                            }}
                        </strong>
                    </div>

                    <div class="summary-box">
                        <span>
                            Fin estimado
                        </span>

                        <strong>
                            {{
                                $finProyecto
                                    ? Carbon\Carbon::parse(
                                        $finProyecto
                                    )->format('d/m/Y')
                                    : '—'
                            }}
                        </strong>
                    </div>

                    <div class="summary-box">
                        <span>
                            Duración total
                        </span>

                        <strong>
                            {{
                                $duracionDias !== null
                                    ? $duracionDias
                                        . ' día(s)'
                                    : '—'
                            }}
                        </strong>
                    </div>

                    <div class="summary-box">
                        <span>
                            Actividades
                        </span>

                        <strong>
                            {{
                                $actividades->count()
                            }}
                            registrada(s)
                        </strong>
                    </div>

                </div>

            </section>

            @if ($editable)

                <section class="card">

                    <div class="card-header">

                        <div>
                            <h2 class="card-title">
                                {{
                                    $actividadEditandoId
                                        ? 'Editar actividad'
                                        : 'Agregar actividad'
                                }}
                            </h2>

                            <div class="card-description">
                                Registra la información
                                de la actividad.
                            </div>
                        </div>

                    </div>

                    <div class="form-block">

                        <div class="form-title">
                            {{
                                $actividadEditandoId
                                    ? 'Modificar actividad'
                                    : 'Nueva actividad'
                            }}
                        </div>

                        <div class="form-grid">

                            <div class="field">

                                <label>
                                    Actividad *
                                </label>

                                <input
                                    class="input"
                                    type="text"
                                    maxlength="255"
                                    wire:model="actividad"
                                    placeholder="
                                        Nombre de la actividad
                                    "
                                >

                                @error('actividad')
                                    <div class="field-error">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                            <div class="field">

                                <label>
                                    Fecha de inicio *
                                </label>

                                <input
                                    class="input"
                                    type="date"
                                    wire:model="
                                        fechaInicio
                                    "
                                >

                                @error('fechaInicio')
                                    <div class="field-error">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                            <div class="field">

                                <label>
                                    Fecha final *
                                </label>

                                <input
                                    class="input"
                                    type="date"
                                    wire:model="
                                        fechaFin
                                    "
                                >

                                @error('fechaFin')
                                    <div class="field-error">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>

                        <div class="form-grid">

                            <div class="field">

                                <label>
                                    Responsable
                                </label>

                                <input
                                    class="input"
                                    type="text"
                                    maxlength="255"
                                    wire:model="
                                        responsable
                                    "
                                    placeholder="
                                        Responsable
                                    "
                                >

                                @error('responsable')
                                    <div class="field-error">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                            <div class="field">

                                <label>
                                    Estado *
                                </label>

                                <select
                                    class="select"
                                    wire:model="estado"
                                >
                                    @foreach (
                                        $estados
                                        as $valor => $etiqueta
                                    )
                                        <option
                                            value="{{ $valor }}"
                                        >
                                            {{ $etiqueta }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('estado')
                                    <div class="field-error">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                            <div class="field">

                                <label>
                                    Avance (%) *
                                </label>

                                <input
                                    class="input"
                                    type="number"
                                    min="0"
                                    max="100"
                                    step="0.01"
                                    wire:model="
                                        porcentajeAvance
                                    "
                                >

                                @error('porcentajeAvance')
                                    <div class="field-error">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>

                        <div class="field">

                            <label style="margin-top:11px;">
                                Descripción
                            </label>

                            <textarea
                                class="textarea"
                                maxlength="5000"
                                wire:model="
                                    descripcion
                                "
                                placeholder="
                                    Descripción de la actividad...
                                "
                            ></textarea>

                            @error('descripcion')
                                <div class="field-error">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        <div class="form-actions">

                            @if ($actividadEditandoId)

                                <button
                                    type="button"
                                    class="btn"
                                    wire:click="
                                        cancelarEdicion
                                    "
                                >
                                    Cancelar
                                </button>

                            @endif

                            <button
                                type="button"
                                class="
                                    btn
                                    btn-primary
                                "
                                wire:click="
                                    guardarActividad
                                "
                                wire:loading.attr="
                                    disabled
                                "
                                wire:target="
                                    guardarActividad
                                "
                            >
                                {{
                                    $actividadEditandoId
                                        ? 'Guardar cambios'
                                        : '+ Agregar actividad'
                                }}
                            </button>

                        </div>

                    </div>

                    <div class="bottom-actions">

                        <button
                            type="button"
                            class="btn"
                            wire:click="
                                volverACotizaciones
                            "
                        >
                            ← Volver a Cotizaciones
                        </button>

                        <button
                            type="button"
                            class="
                                btn
                                btn-primary
                            "
                            wire:click="
                                continuarAEntregables
                            "
                        >
                            Continuar a Entregables →
                        </button>

                    </div>

                </section>

            @else

                <section class="card">

                    <div class="bottom-actions">

                        <button
                            type="button"
                            class="btn"
                            wire:click="
                                volverACotizaciones
                            "
                        >
                            ← Volver a Cotizaciones
                        </button>

                        <button
                            type="button"
                            class="
                                btn
                                btn-primary
                            "
                            wire:click="
                                continuarAEntregables
                            "
                        >
                            Continuar a Entregables →
                        </button>

                    </div>

                </section>

            @endif

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
                <span>Actividades</span>
                <strong>
                    {{ $actividades->count() }}
                </strong>
            </div>

            <div class="sidebar-row">
                <span>Completadas</span>

                <strong
                    style="color:#059669;"
                >
                    {{ $completadas }}
                </strong>
            </div>

            <div class="sidebar-row">
                <span>Avance promedio</span>

                <strong>
                    {{
                        number_format(
                            $avancePromedio,
                            0
                        )
                    }}%
                </strong>
            </div>

            <div class="sidebar-progress">

                <div class="progress-info">

                    <span>
                        Progreso general
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
                        active
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
                        6
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

        </aside>

    </div>

</div>

</x-filament-panels::page>
