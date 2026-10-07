<x-filament-panels::page>

<style>
    .itsva-quotes {
        --text: #0F1827;
        --muted: #64748B;
        --primary: #1A4B8C;
        --border: #DDE3EE;
        --success: #059669;
        --warning: #D97706;
        --danger: #DC2626;

        color: var(--text);
    }

    .itsva-quotes * {
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
    .row-actions,
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
        color: var(--danger);
        border-color: #FECACA;
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
        line-height: 1.5;
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

    .quote-item {
        margin-bottom: 12px;
        padding: 17px;
        border: 1px solid var(--border);
        border-radius: 12px;
        background: #FBFCFE;
    }

    .quote-item:last-child {
        margin-bottom: 0;
    }

    .quote-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 15px;
    }

    .quote-concept {
        color: #1E293B;
        font-size: 13px;
        font-weight: 800;
    }

    .quote-category {
        margin-top: 4px;
        color: var(--muted);
        font-size: 10px;
    }

    .status {
        padding: 5px 8px;
        border-radius: 999px;
        font-size: 9px;
        font-weight: 800;
    }

    .status-complete {
        background: #D1FAE5;
        color: #065F46;
    }

    .status-pending {
        background: #FEF3C7;
        color: #92400E;
    }

    .quote-details {
        display: grid;
        grid-template-columns:
            repeat(4, minmax(0, 1fr));
        gap: 10px;
        margin-top: 14px;
    }

    .detail-box {
        padding: 10px;
        background: white;
        border: 1px solid #E8EDF4;
        border-radius: 8px;
    }

    .detail-label {
        display: block;
        margin-bottom: 3px;
        color: #94A3B8;
        font-size: 9px;
    }

    .detail-value {
        color: #334155;
        font-size: 10px;
        font-weight: 800;
    }

    .file-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        margin-top: 13px;
        padding-top: 12px;
        border-top: 1px solid var(--border);
    }

    .file-info {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .file-icon {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: #FEE2E2;
        color: #B91C1C;
        font-size: 8px;
        font-weight: 900;
    }

    .file-name {
        color: #334155;
        font-size: 9px;
        font-weight: 800;
    }

    .file-pending {
        color: #D97706;
        font-size: 9px;
    }

    .form-grid {
        display: grid;
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .form-grid + .form-grid {
        margin-top: 12px;
    }

    .field label {
        display: block;
        margin-bottom: 5px;
        color: #475569;
        font-size: 9px;
        font-weight: 800;
    }

    .input,
    .select {
        width: 100%;
        min-height: 40px;
        padding: 0 10px;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        background: white;
        color: var(--text);
        font: inherit;
        font-size: 11px;
        outline: none;
    }

    .input:focus,
    .select:focus {
        border-color: #2563EB;
        box-shadow:
            0 0 0 3px
            rgba(37, 99, 235, .08);
    }

    .field-error {
        margin-top: 4px;
        color: #DC2626;
        font-size: 9px;
    }

    .upload-box {
        margin-top: 14px;
        padding: 17px;
        border: 1px dashed #AFC0D8;
        border-radius: 11px;
        background: #F8FAFD;
    }

    .upload-title {
        margin-bottom: 5px;
        font-size: 10px;
        font-weight: 800;
    }

    .upload-help {
        margin-bottom: 9px;
        color: var(--muted);
        font-size: 9px;
    }

    .current-file {
        margin-top: 7px;
        color: #475569;
        font-size: 9px;
    }

    .form-actions {
        justify-content: flex-end;
        margin-top: 14px;
    }

    .bottom-actions {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        margin-top: 20px;
        padding-top: 18px;
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
        background: var(--primary);
        border-color: var(--primary);
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
        .quote-details,
        .form-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 600px) {
        .page-top,
        .bottom-actions,
        .file-row {
            flex-direction: column;
        }
    }
</style>

<div class="itsva-quotes">

    <div class="page-top">

        <div>
            <h1 class="page-title">
                Cotizaciones de la Propuesta
            </h1>

            <div class="page-subtitle">
                Registra los documentos que respaldan
                los conceptos incluidos en el presupuesto.
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
                {{ str_replace('_', ' ', $propuesta->estado) }}
            </strong>
            y las cotizaciones se muestran en modo
            de solo lectura.
        </div>
    @endif

    <div class="layout">

        <main class="main">

            <div class="info-box">
                Cada cotización se asocia a un concepto
                real del presupuesto. Los documentos se
                almacenan de forma privada y solo pueden
                descargarse después de verificar que la
                propuesta pertenece al usuario autenticado.
            </div>

            <section class="card">

                <div class="card-header">

                    <div>
                        <h2 class="card-title">
                            Cotizaciones registradas
                        </h2>

                        <div class="card-description">
                            Documentos que respaldan los
                            montos del presupuesto.
                        </div>
                    </div>

                    <span class="section-badge">
                        Sección 5 de 7
                    </span>

                </div>

                @forelse (
                    $cotizaciones
                    as $cotizacion
                )

                    @php
                        $completa =
                            filled(
                                $cotizacion->proveedor
                            )
                            &&
                            (float)
                            $cotizacion->monto > 0
                            &&
                            $cotizacion
                                ->fecha_cotizacion
                            !== null
                            &&
                            filled(
                                $cotizacion
                                    ->archivo_url
                            );

                        $esExterna =
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

                    <article class="quote-item">

                        <div class="quote-top">

                            <div>
                                <div class="quote-concept">
                                    {{
                                        $cotizacion->concepto
                                        ?? 'Cotización'
                                    }}
                                </div>

                                <div class="quote-category">
                                    Concepto presupuestal:
                                    {{
                                        $cotizacion
                                            ->presupuestoItem
                                            ?->concepto
                                        ?? 'Sin asociación'
                                    }}
                                </div>
                            </div>

                            <span
                                class="
                                    status
                                    {{
                                        $completa
                                            ? 'status-complete'
                                            : 'status-pending'
                                    }}
                                "
                            >
                                {{
                                    $completa
                                        ? 'Completa'
                                        : 'Pendiente'
                                }}
                            </span>

                        </div>

                        <div class="quote-details">

                            <div class="detail-box">
                                <span class="detail-label">
                                    Proveedor
                                </span>

                                <span class="detail-value">
                                    {{
                                        $cotizacion->proveedor
                                        ?: 'No registrado'
                                    }}
                                </span>
                            </div>

                            <div class="detail-box">
                                <span class="detail-label">
                                    Monto
                                </span>

                                <span class="detail-value">
                                    $
                                    {{
                                        number_format(
                                            (float)
                                            $cotizacion->monto,
                                            2
                                        )
                                    }}
                                    {{ $cotizacion->moneda }}
                                </span>
                            </div>

                            <div class="detail-box">
                                <span class="detail-label">
                                    Fecha
                                </span>

                                <span class="detail-value">
                                    {{
                                        $cotizacion
                                            ->fecha_cotizacion
                                            ?->format('d/m/Y')
                                        ?? 'No registrada'
                                    }}
                                </span>
                            </div>

                            <div class="detail-box">
                                <span class="detail-label">
                                    Categoría
                                </span>

                                <span class="detail-value">
                                    {{
                                        str_replace(
                                            '_',
                                            ' ',
                                            $cotizacion
                                                ->presupuestoItem
                                                ?->categoria_gasto
                                            ?? 'Sin categoría'
                                        )
                                    }}
                                </span>
                            </div>

                        </div>

                        <div class="file-row">

                            <div class="file-info">

                                <div class="file-icon">
                                    DOC
                                </div>

                                <div>

                                    @if (
                                        filled(
                                            $cotizacion
                                                ->archivo_url
                                        )
                                    )

                                        <div class="file-name">
                                            {{
                                                $esExterna
                                                    ? 'Documento externo'
                                                    : basename(
                                                        $cotizacion
                                                            ->archivo_url
                                                    )
                                            }}
                                        </div>

                                    @else

                                        <div class="file-pending">
                                            Documento pendiente
                                        </div>

                                    @endif

                                </div>

                            </div>

                            <div class="row-actions">

                                @if (
                                    filled(
                                        $cotizacion
                                            ->archivo_url
                                    )
                                )

                                    @if ($esExterna)

                                        <a
                                            href="{{
                                                $cotizacion
                                                    ->archivo_url
                                            }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="mini-btn"
                                        >
                                            Abrir
                                        </a>

                                    @else

                                        <button
                                            type="button"
                                            class="mini-btn"
                                            wire:click="
                                                descargarCotizacion(
                                                    {{
                                                        $cotizacion->id
                                                    }}
                                                )
                                            "
                                        >
                                            Descargar
                                        </button>

                                    @endif

                                @endif

                                @if ($editable)

                                    <button
                                        type="button"
                                        class="mini-btn"
                                        wire:click="
                                            editarCotizacion(
                                                {{
                                                    $cotizacion->id
                                                }}
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
                                            eliminarCotizacion(
                                                {{
                                                    $cotizacion->id
                                                }}
                                            )
                                        "
                                        wire:confirm="
                                            ¿Eliminar esta cotización?
                                        "
                                    >
                                        Eliminar
                                    </button>

                                @endif

                            </div>

                        </div>

                    </article>

                @empty

                    <div class="empty">
                        Esta propuesta todavía no tiene
                        cotizaciones registradas.
                    </div>

                @endforelse

            </section>

            @if ($editable)

                <section class="card">

                    <div class="card-header">

                        <div>
                            <h2 class="card-title">
                                {{
                                    $cotizacionEditandoId
                                        ? 'Editar cotización'
                                        : 'Agregar cotización'
                                }}
                            </h2>

                            <div class="card-description">
                                Selecciona el concepto presupuestal
                                y registra los datos del proveedor.
                            </div>
                        </div>

                    </div>

                    @if ($items->isEmpty())

                        <div class="info-box">
                            Primero debes agregar al menos un
                            concepto en la sección Presupuesto.
                        </div>

                    @else

                        <div class="form-grid">

                            <div class="field">

                                <label>
                                    Concepto del presupuesto *
                                </label>

                                <select
                                    class="select"
                                    wire:model.live="
                                        presupuestoItemId
                                    "
                                >
                                    <option value="">
                                        Seleccionar concepto
                                    </option>

                                    @foreach ($items as $item)

                                        <option
                                            value="{{ $item->id }}"
                                        >
                                            {{ $item->concepto }}
                                        </option>

                                    @endforeach
                                </select>

                                @error('presupuestoItemId')
                                    <div class="field-error">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                            <div class="field">

                                <label>
                                    Proveedor *
                                </label>

                                <input
                                    type="text"
                                    class="input"
                                    maxlength="255"
                                    wire:model="proveedor"
                                    placeholder="
                                        Nombre del proveedor
                                    "
                                >

                                @error('proveedor')
                                    <div class="field-error">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>

                        <div class="form-grid">

                            <div class="field">

                                <label>
                                    Concepto *
                                </label>

                                <input
                                    type="text"
                                    class="input"
                                    maxlength="255"
                                    wire:model="concepto"
                                >

                                @error('concepto')
                                    <div class="field-error">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                            <div class="field">

                                <label>
                                    Fecha de cotización *
                                </label>

                                <input
                                    type="date"
                                    class="input"
                                    wire:model="
                                        fechaCotizacion
                                    "
                                >

                                @error('fechaCotizacion')
                                    <div class="field-error">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>

                        <div class="form-grid">

                            <div class="field">

                                <label>
                                    Monto *
                                </label>

                                <input
                                    type="number"
                                    class="input"
                                    min="0.01"
                                    step="0.01"
                                    wire:model="monto"
                                    placeholder="0.00"
                                >

                                @error('monto')
                                    <div class="field-error">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                            <div class="field">

                                <label>
                                    Moneda *
                                </label>

                                <select
                                    class="select"
                                    wire:model="moneda"
                                >
                                    <option value="MXN">
                                        MXN
                                    </option>

                                    <option value="USD">
                                        USD
                                    </option>
                                </select>

                                @error('moneda')
                                    <div class="field-error">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>

                        <div class="upload-box">

                            <div class="upload-title">
                                Documento de cotización *
                            </div>

                            <div class="upload-help">
                                PDF, JPG o PNG · Máximo 10 MB
                            </div>

                            <input
                                type="file"
                                class="input"
                                wire:model="
                                    archivoCotizacion
                                "
                                accept="
                                    .pdf,.jpg,.jpeg,.png
                                "
                            >

                            <div
                                wire:loading
                                wire:target="
                                    archivoCotizacion
                                "
                                style="
                                    margin-top:6px;
                                    font-size:9px;
                                    color:#64748B;
                                "
                            >
                                Cargando archivo...
                            </div>

                            @if (
                                $cotizacionEditandoId
                                &&
                                $archivoActual
                            )
                                <div class="current-file">
                                    Archivo actual:
                                    {{
                                        str_starts_with(
                                            strtolower(
                                                $archivoActual
                                            ),
                                            'http'
                                        )
                                            ? 'Documento externo'
                                            : basename(
                                                $archivoActual
                                            )
                                    }}
                                </div>
                            @endif

                            @error('archivoCotizacion')
                                <div class="field-error">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        <div class="form-actions">

                            @if ($cotizacionEditandoId)

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
                                    guardarCotizacion
                                "
                                wire:loading.attr="
                                    disabled
                                "
                                wire:target="
                                    guardarCotizacion
                                "
                            >
                                {{
                                    $cotizacionEditandoId
                                        ? 'Guardar cambios'
                                        : '+ Agregar cotización'
                                }}
                            </button>

                        </div>

                    @endif

                    <div class="bottom-actions">

                        <button
                            type="button"
                            class="btn"
                            wire:click="
                                volverAPresupuesto
                            "
                        >
                            ← Volver a Presupuesto
                        </button>

                        <button
                            type="button"
                            class="
                                btn
                                btn-primary
                            "
                            wire:click="
                                continuarACronograma
                            "
                        >
                            Continuar a Cronograma →
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
                                volverAPresupuesto
                            "
                        >
                            ← Volver a Presupuesto
                        </button>

                        <button
                            type="button"
                            class="
                                btn
                                btn-primary
                            "
                            wire:click="
                                continuarACronograma
                            "
                        >
                            Continuar a Cronograma →
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
                <span>Conceptos</span>
                <strong>
                    {{ $items->count() }}
                </strong>
            </div>

            <div class="sidebar-row">
                <span>Cotizaciones</span>
                <strong>
                    {{ $cotizaciones->count() }}
                </strong>
            </div>

            <div class="sidebar-row">
                <span>Completas</span>

                <strong
                    style="color:#059669;"
                >
                    {{
                        $cotizacionesCompletasCount
                    }}
                </strong>
            </div>

            <div class="sidebar-row">
                <span>
                    Conceptos pendientes
                </span>

                <strong
                    style="color:#D97706;"
                >
                    {{ $conceptosPendientes }}
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
                        active
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
                        5
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

        </aside>

    </div>

</div>

</x-filament-panels::page>
