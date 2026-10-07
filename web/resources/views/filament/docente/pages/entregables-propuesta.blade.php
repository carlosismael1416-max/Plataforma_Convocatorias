<x-filament-panels::page>

<style>
    .itsva-deliverables {
        --text: #0F1827;
        --muted: #64748B;
        --primary: #1A4B8C;
        --border: #DDE3EE;
        --success: #059669;
        --warning: #D97706;
        --danger: #DC2626;

        color: var(--text);
    }

    .itsva-deliverables * {
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
    .deliverable-actions,
    .form-actions,
    .evidence-actions {
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
        text-decoration: none;
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

    .deliverable {
        display: grid;
        grid-template-columns:
            42px minmax(0, 1fr);
        gap: 12px;
        margin-bottom: 12px;
        padding: 16px;
        border: 1px solid var(--border);
        border-radius: 12px;
        background: #FBFCFE;
    }

    .deliverable-number {
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        background: #E8EEF8;
        color: var(--primary);
        font-size: 10px;
        font-weight: 900;
    }

    .deliverable-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
    }

    .deliverable-title {
        color: #1E293B;
        font-size: 12px;
        font-weight: 800;
    }

    .deliverable-description {
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

    .status-entregado {
        background: #D1FAE5;
        color: #065F46;
    }

    .status-aprobado {
        background: #DCFCE7;
        color: #166534;
    }

    .status-rechazado {
        background: #FEE2E2;
        color: #991B1B;
    }

    .meta-grid {
        display: grid;
        grid-template-columns:
            repeat(3, minmax(0, 1fr));
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

    .evidence-list {
        margin-top: 12px;
        padding-top: 11px;
        border-top: 1px solid var(--border);
    }

    .evidence-title {
        margin-bottom: 7px;
        color: #64748B;
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .evidence-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        padding: 8px 0;
    }

    .evidence-name {
        color: #334155;
        font-size: 9px;
        font-weight: 700;
    }

    .deliverable-actions {
        margin-top: 12px;
        padding-top: 11px;
        border-top: 1px solid var(--border);
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
        min-height: 82px;
        padding: 10px;
        resize: vertical;
    }

    .field-error {
        margin-top: 4px;
        color: #DC2626;
        font-size: 9px;
    }

    .upload-box {
        margin-top: 13px;
        padding: 15px;
        border: 1px dashed #B7C7DB;
        border-radius: 10px;
        background: white;
    }

    .upload-title {
        margin-bottom: 4px;
        font-size: 10px;
        font-weight: 800;
    }

    .upload-help {
        margin-bottom: 9px;
        color: var(--muted);
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

    .completion {
        margin-top: 16px;
        padding: 13px;
        border: 1px solid #A7F3D0;
        border-radius: 10px;
        background: #ECFDF5;
        color: #065F46;
        font-size: 10px;
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

    @media (max-width: 760px) {
        .meta-grid,
        .form-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 600px) {
        .page-top,
        .bottom-actions,
        .evidence-row {
            flex-direction: column;
        }
    }
</style>

<div class="itsva-deliverables">

    <div class="page-top">

        <div>
            <h1 class="page-title">
                Entregables de la Propuesta
            </h1>

            <div class="page-subtitle">
                Define los productos, documentos o
                resultados verificables del proyecto.
            </div>
        </div>

        <div class="top-actions">

            @if ($editable)
                <button
                    type="button"
                    class="btn"
                    wire:click="guardarBorrador"
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
            y los entregables se muestran
            en modo de solo lectura.
        </div>

    @endif

    <div class="layout">

        <main class="main">

            <div class="info-box">
                Los entregables representan resultados
                verificables del proyecto. Pueden asociarse
                opcionalmente con una actividad del cronograma
                y tener una o más evidencias.
            </div>

            <section class="card">

                <div class="card-header">

                    <div>
                        <h2 class="card-title">
                            Entregables registrados
                        </h2>

                        <div class="card-description">
                            Resultados comprometidos
                            dentro de la propuesta.
                        </div>
                    </div>

                    <span class="section-badge">
                        Sección 7 de 7
                    </span>

                </div>

                @forelse (
                    $entregables
                    as $indice => $item
                )

                    @php
                        $claseEstado =
                            match (
                                $item->estado
                            ) {
                                'EN_PROCESO' =>
                                    'status-proceso',

                                'ENTREGADO' =>
                                    'status-entregado',

                                'APROBADO' =>
                                    'status-aprobado',

                                'RECHAZADO' =>
                                    'status-rechazado',

                                default =>
                                    'status-pendiente',
                            };

                        $evidencias =
                            collect(
                                $evidenciasPorEntregable
                                    ->get(
                                        $item->id,
                                        []
                                    )
                            );
                    @endphp

                    <article class="deliverable">

                        <div class="deliverable-number">
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

                        <div>

                            <div class="deliverable-top">

                                <div>

                                    <div class="deliverable-title">
                                        {{ $item->nombre }}
                                    </div>

                                    @if ($item->descripcion)

                                        <div
                                            class="
                                                deliverable-description
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

                            <div class="meta-grid">

                                <div class="meta-box">
                                    <span class="meta-label">
                                        Fecha límite
                                    </span>

                                    <span class="meta-value">
                                        {{
                                            $item
                                                ->fecha_limite
                                                ?->format(
                                                    'd/m/Y'
                                                )
                                            ?? 'Sin definir'
                                        }}
                                    </span>
                                </div>

                                <div class="meta-box">
                                    <span class="meta-label">
                                        Actividad relacionada
                                    </span>

                                    <span class="meta-value">
                                        {{
                                            $item
                                                ->actividad
                                                ?->actividad
                                            ?? 'Sin asociación'
                                        }}
                                    </span>
                                </div>

                                <div class="meta-box">
                                    <span class="meta-label">
                                        Fecha de entrega
                                    </span>

                                    <span class="meta-value">
                                        {{
                                            $item
                                                ->fecha_entrega
                                                ?->format(
                                                    'd/m/Y H:i'
                                                )
                                            ?? 'Pendiente'
                                        }}
                                    </span>
                                </div>

                            </div>

                            @if ($evidencias->isNotEmpty())

                                <div class="evidence-list">

                                    <div class="evidence-title">
                                        Evidencias
                                    </div>

                                    @foreach (
                                        $evidencias
                                        as $evidencia
                                    )

                                        <div class="evidence-row">

                                            <div class="evidence-name">
                                                📎
                                                {{
                                                    $evidencia
                                                        ->nombre
                                                }}
                                            </div>

                                            <div class="evidence-actions">

                                                @if (
                                                    $evidencia
                                                        ->ruta_archivo
                                                )

                                                    <button
                                                        type="button"
                                                        class="mini-btn"
                                                        wire:click="
                                                            descargarEvidencia(
                                                                {{
                                                                    $evidencia
                                                                        ->id
                                                                }}
                                                            )
                                                        "
                                                    >
                                                        Descargar
                                                    </button>

                                                @elseif (
                                                    $evidencia
                                                        ->url_archivo
                                                )

                                                    <a
                                                        href="{{
                                                            $evidencia
                                                                ->url_archivo
                                                        }}"
                                                        target="_blank"
                                                        rel="
                                                            noopener
                                                            noreferrer
                                                        "
                                                        class="mini-btn"
                                                    >
                                                        Abrir
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
                                                                    $evidencia
                                                                        ->id
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

                                    @endforeach

                                </div>

                            @endif

                            @if ($editable)

                                <div class="deliverable-actions">

                                    <button
                                        type="button"
                                        class="mini-btn"
                                        wire:click="
                                            editarEntregable(
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
                                            eliminarEntregable(
                                                {{ $item->id }}
                                            )
                                        "
                                        wire:confirm="
                                            ¿Eliminar este entregable y sus evidencias?
                                        "
                                    >
                                        Eliminar
                                    </button>

                                </div>

                            @endif

                        </div>

                    </article>

                @empty

                    <div class="empty">
                        Esta propuesta todavía no tiene
                        entregables registrados.
                    </div>

                @endforelse

                @if (
                    $pasos['datos']
                    && $pasos['objetivos']
                    && $pasos['requisitos']
                    && $pasos['presupuesto']
                    && $pasos['cronograma']
                    && $pasos['entregables']
                )

                    <div class="completion">
                        <strong>
                            Secciones principales completadas.
                        </strong>
                        Ya puedes revisar la propuesta
                        completa desde Vista Previa.
                    </div>

                @endif

            </section>

            @if ($editable)

                <section class="card">

                    <div class="card-header">

                        <div>
                            <h2 class="card-title">
                                {{
                                    $entregableEditandoId
                                        ? 'Editar entregable'
                                        : 'Agregar entregable'
                                }}
                            </h2>

                            <div class="card-description">
                                Registra un resultado
                                esperado del proyecto.
                            </div>
                        </div>

                    </div>

                    <div class="form-block">

                        <div class="form-title">
                            {{
                                $entregableEditandoId
                                    ? 'Modificar entregable'
                                    : 'Nuevo entregable'
                            }}
                        </div>

                        <div class="form-grid">

                            <div class="field">

                                <label>
                                    Nombre *
                                </label>

                                <input
                                    type="text"
                                    class="input"
                                    maxlength="255"
                                    wire:model="nombre"
                                    placeholder="
                                        Nombre del entregable
                                    "
                                >

                                @error('nombre')
                                    <div class="field-error">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                            <div class="field">

                                <label>
                                    Actividad relacionada
                                </label>

                                <select
                                    class="select"
                                    wire:model="
                                        cronogramaActividadId
                                    "
                                >
                                    <option value="">
                                        Sin asociación
                                    </option>

                                    @foreach (
                                        $actividades
                                        as $actividad
                                    )

                                        <option
                                            value="{{
                                                $actividad->id
                                            }}"
                                        >
                                            {{
                                                $actividad
                                                    ->actividad
                                            }}
                                        </option>

                                    @endforeach
                                </select>

                                @error(
                                    'cronogramaActividadId'
                                )
                                    <div class="field-error">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                            <div class="field">

                                <label>
                                    Fecha límite
                                </label>

                                <input
                                    type="date"
                                    class="input"
                                    wire:model="
                                        fechaLimite
                                    "
                                >

                                @error('fechaLimite')
                                    <div class="field-error">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>

                        <div class="form-grid">

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
                                    Fecha de entrega
                                </label>

                                <input
                                    type="datetime-local"
                                    class="input"
                                    wire:model="
                                        fechaEntrega
                                    "
                                >

                                @error('fechaEntrega')
                                    <div class="field-error">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                            <div></div>

                        </div>

                        <div
                            class="field"
                            style="margin-top:11px;"
                        >

                            <label>
                                Descripción
                            </label>

                            <textarea
                                class="textarea"
                                maxlength="5000"
                                wire:model="
                                    descripcion
                                "
                                placeholder="
                                    Descripción del entregable...
                                "
                            ></textarea>

                            @error('descripcion')
                                <div class="field-error">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        <div class="upload-box">

                            <div class="upload-title">
                                Evidencia o archivo relacionado
                            </div>

                            <div class="upload-help">
                                Opcional · PDF, DOCX, XLSX,
                                ZIP, JPG o PNG · Máximo 20 MB
                            </div>

                            <input
                                type="file"
                                class="input"
                                wire:model="
                                    archivoEvidencia
                                "
                                accept="
                                    .pdf,.docx,.xlsx,.zip,
                                    .jpg,.jpeg,.png
                                "
                            >

                            <div
                                wire:loading
                                wire:target="
                                    archivoEvidencia
                                "
                                style="
                                    margin-top:6px;
                                    color:#64748B;
                                    font-size:9px;
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

                        <div class="form-actions">

                            @if ($entregableEditandoId)

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
                                    guardarEntregable
                                "
                                wire:loading.attr="
                                    disabled
                                "
                                wire:target="
                                    guardarEntregable
                                "
                            >
                                {{
                                    $entregableEditandoId
                                        ? 'Guardar cambios'
                                        : '+ Agregar entregable'
                                }}
                            </button>

                        </div>

                    </div>

                    <div class="bottom-actions">

                        <button
                            type="button"
                            class="btn"
                            wire:click="
                                volverACronograma
                            "
                        >
                            ← Volver a Cronograma
                        </button>

                        <button
                            type="button"
                            class="
                                btn
                                btn-primary
                            "
                            wire:click="
                                revisarPropuesta
                            "
                        >
                            Revisar propuesta →
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
                                volverACronograma
                            "
                        >
                            ← Volver a Cronograma
                        </button>

                        <button
                            type="button"
                            class="
                                btn
                                btn-primary
                            "
                            wire:click="
                                revisarPropuesta
                            "
                        >
                            Revisar propuesta →
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
                <span>Entregables</span>
                <strong>
                    {{ $entregables->count() }}
                </strong>
            </div>

            <div class="sidebar-row">
                <span>Pendientes</span>

                <strong
                    style="color:#D97706;"
                >
                    {{ $pendientes }}
                </strong>
            </div>

            <div class="sidebar-row">
                <span>Entregados</span>

                <strong
                    style="color:#059669;"
                >
                    {{ $entregados }}
                </strong>
            </div>

            <div class="sidebar-row">
                <span>Rechazados</span>

                <strong
                    style="color:#DC2626;"
                >
                    {{ $rechazados }}
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

                @php
                    $rutasPasos = [
                        'datos' => [
                            1,
                            'Datos de propuesta',
                            'filament.docente.pages.editor-propuesta',
                        ],

                        'objetivos' => [
                            2,
                            'Objetivos',
                            'filament.docente.pages.objetivos-propuesta',
                        ],

                        'requisitos' => [
                            3,
                            'Requisitos',
                            'filament.docente.pages.requisitos-propuesta',
                        ],

                        'presupuesto' => [
                            4,
                            'Presupuesto',
                            'filament.docente.pages.presupuesto-propuesta',
                        ],

                        'cotizaciones' => [
                            5,
                            'Cotizaciones',
                            'filament.docente.pages.cotizaciones-propuesta',
                        ],

                        'cronograma' => [
                            6,
                            'Cronograma',
                            'filament.docente.pages.cronograma-propuesta',
                        ],
                    ];
                @endphp

                @foreach (
                    $rutasPasos
                    as $clave => $config
                )

                    <a
                        class="
                            step
                            {{
                                $pasos[$clave]
                                    ? 'complete'
                                    : ''
                            }}
                        "
                        href="{{ route(
                            $config[2],
                            [
                                'propuesta' =>
                                    $propuesta->id,
                            ]
                        ) }}"
                    >
                        <span class="step-number">
                            {{
                                $pasos[$clave]
                                    ? '✓'
                                    : $config[0]
                            }}
                        </span>

                        {{ $config[1] }}
                    </a>

                @endforeach

                <a
                    class="
                        step
                        active
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
                                $propuesta->id,
                        ]
                    ) }}"
                >
                    <span class="step-number">
                        7
                    </span>

                    Entregables
                </a>

            </div>

        </aside>

    </div>

</div>

</x-filament-panels::page>
