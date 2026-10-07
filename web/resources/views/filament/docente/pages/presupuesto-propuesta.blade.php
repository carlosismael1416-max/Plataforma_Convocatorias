<x-filament-panels::page>

<style>
    .itsva-budget {
        --text: #0F1827;
        --muted: #64748B;
        --primary: #1A4B8C;
        --border: #DDE3EE;
        --success: #059669;
        --warning: #D97706;
        --danger: #DC2626;
        color: var(--text);
    }

    .itsva-budget * {
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
        flex-wrap: wrap;
        gap: 8px;
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
        white-space: nowrap;
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

    .budget-table-wrap {
        width: 100%;
        overflow-x: auto;
        border: 1px solid var(--border);
        border-radius: 11px;
    }

    .budget-table {
        width: 100%;
        min-width: 900px;
        border-collapse: collapse;
    }

    .budget-table th {
        padding: 11px 12px;
        background: #F8FAFC;
        color: #475569;
        border-bottom: 1px solid var(--border);
        text-align: left;
        font-size: 10px;
        font-weight: 800;
    }

    .budget-table td {
        padding: 12px;
        border-bottom: 1px solid #EEF2F7;
        vertical-align: top;
        font-size: 11px;
    }

    .budget-table tr:last-child td {
        border-bottom: 0;
    }

    .concept-name {
        color: #1E293B;
        font-size: 11px;
        font-weight: 800;
    }

    .concept-desc {
        margin-top: 3px;
        color: var(--muted);
        font-size: 9px;
        line-height: 1.45;
    }

    .category-tag {
        display: inline-flex;
        padding: 5px 8px;
        border-radius: 999px;
        background: #E8EEF8;
        color: var(--primary);
        font-size: 9px;
        font-weight: 800;
    }

    .money,
    .subtotal {
        white-space: nowrap;
        font-weight: 700;
    }

    .subtotal {
        font-weight: 800;
    }

    .row-actions {
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
    }

    .mini-danger {
        border-color: #FECACA;
        background: #FEF2F2;
        color: #DC2626;
    }

    .quote-count {
        margin-top: 5px;
        color: #64748B;
        font-size: 8px;
    }

    .empty {
        padding: 35px 20px;
        text-align: center;
        color: var(--muted);
        font-size: 11px;
    }

    .form-block {
        margin-top: 18px;
        padding: 18px;
        border: 1px dashed #B8C8DC;
        border-radius: 12px;
        background: #FBFCFE;
    }

    .form-title {
        margin-bottom: 15px;
        font-size: 13px;
        font-weight: 800;
    }

    .form-grid {
        display: grid;
        grid-template-columns:
            2fr 1fr 1fr;
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
        margin-top: 12px;
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
        display: flex;
        justify-content: flex-end;
        gap: 7px;
        margin-top: 13px;
    }

    .subtotal-preview {
        min-height: 40px;
        display: flex;
        align-items: center;
        padding: 0 10px;
        border: 1px solid #E2E8F0;
        border-radius: 8px;
        background: #F8FAFC;
        color: #334155;
        font-size: 11px;
        font-weight: 800;
    }

    .totals {
        width: min(100%, 410px);
        margin-top: 20px;
        margin-left: auto;
        padding-top: 14px;
        border-top: 1px solid var(--border);
    }

    .total-row {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        padding: 6px 0;
        font-size: 11px;
    }

    .total-row span {
        color: var(--muted);
    }

    .total-row.final {
        margin-top: 7px;
        padding-top: 12px;
        border-top: 1px solid var(--border);
        font-size: 14px;
    }

    .total-row.final span,
    .total-row.final strong {
        color: var(--primary);
        font-weight: 800;
    }

    .budget-limit {
        margin-top: 13px;
        padding: 11px;
        border-radius: 9px;
        font-size: 10px;
        line-height: 1.5;
    }

    .budget-ok {
        border: 1px solid #A7F3D0;
        background: #ECFDF5;
        color: #065F46;
    }

    .budget-none {
        border: 1px solid #E2E8F0;
        background: #F8FAFC;
        color: #475569;
    }

    .limit-progress {
        margin-top: 9px;
        height: 7px;
        overflow: hidden;
        border-radius: 999px;
        background: #D1FAE5;
    }

    .limit-progress-value {
        height: 100%;
        border-radius: 999px;
        background: #059669;
    }

    .bottom-actions {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        margin-top: 20px;
        padding-top: 18px;
        border-top: 1px solid var(--border);
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

    .progress-top {
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
        background: var(--primary);
        border-radius: 999px;
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
        flex: 0 0 auto;
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

    @media (max-width: 700px) {
        .page-top,
        .bottom-actions {
            flex-direction: column;
        }

        .form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="itsva-budget">

    <div class="page-top">

        <div>
            <h1 class="page-title">
                Presupuesto de la Propuesta
            </h1>

            <div class="page-subtitle">
                Registra los conceptos de gasto y
                calcula el monto total solicitado.
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
            y el presupuesto se muestra en modo de solo lectura.
        </div>
    @endif

    <div class="layout">

        <main class="main">

            <div class="info-box">
                El subtotal se calcula automáticamente como
                cantidad × precio unitario. Cuando la convocatoria
                define un monto máximo, el sistema impedirá guardar
                un presupuesto que lo exceda.
            </div>

            <section class="card">

                <div class="card-header">

                    <div>
                        <h2 class="card-title">
                            Desglose presupuestal
                        </h2>

                        <div class="card-description">
                            Conceptos registrados para esta propuesta.
                        </div>
                    </div>

                    <span class="section-badge">
                        Sección 4 de 7
                    </span>

                </div>

                @if ($items->isNotEmpty())

                    <div class="budget-table-wrap">

                        <table class="budget-table">

                            <thead>
                                <tr>
                                    <th>Concepto</th>
                                    <th>Categoría</th>
                                    <th>Cantidad</th>
                                    <th>Precio unitario</th>
                                    <th>Subtotal</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>

                            <tbody>

                                @foreach ($items as $item)

                                    @php
                                        $subtotal =
                                            (float) $item->cantidad
                                            *
                                            (float) $item->precio_unitario;
                                    @endphp

                                    <tr>

                                        <td>
                                            <div class="concept-name">
                                                {{ $item->concepto }}
                                            </div>

                                            @if ($item->descripcion)
                                                <div class="concept-desc">
                                                    {{ $item->descripcion }}
                                                </div>
                                            @endif
                                        </td>

                                        <td>
                                            <span class="category-tag">
                                                {{
                                                    $categorias[
                                                        $item->categoria_gasto
                                                        ?: 'OTRO'
                                                    ]
                                                    ?? $item->categoria_gasto
                                                    ?? 'Otro'
                                                }}
                                            </span>
                                        </td>

                                        <td>
                                            {{ number_format(
                                                (float) $item->cantidad,
                                                2
                                            ) }}
                                        </td>

                                        <td class="money">
                                            $
                                            {{ number_format(
                                                (float) $item->precio_unitario,
                                                2
                                            ) }}
                                            {{ $item->moneda }}
                                        </td>

                                        <td class="subtotal">
                                            $
                                            {{ number_format(
                                                $subtotal,
                                                2
                                            ) }}
                                            {{ $item->moneda }}
                                        </td>

                                        <td>

                                            <div class="row-actions">

                                                <button
                                                    type="button"
                                                    class="mini-btn"
                                                    wire:click="
                                                        irACotizaciones(
                                                            {{ $item->id }}
                                                        )
                                                    "
                                                >
                                                    Cotización
                                                </button>

                                                @if ($editable)

                                                    <button
                                                        type="button"
                                                        class="mini-btn"
                                                        wire:click="
                                                            editarConcepto(
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
                                                            eliminarConcepto(
                                                                {{ $item->id }}
                                                            )
                                                        "
                                                        wire:confirm="
                                                            ¿Eliminar este concepto del presupuesto?
                                                        "
                                                    >
                                                        Eliminar
                                                    </button>

                                                @endif

                                            </div>

                                            @if (
                                                $item
                                                    ->cotizaciones_count > 0
                                            )
                                                <div class="quote-count">
                                                    {{
                                                        $item
                                                            ->cotizaciones_count
                                                    }}
                                                    cotización(es)
                                                </div>
                                            @endif

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                @else

                    <div class="empty">
                        Todavía no hay conceptos registrados
                        en este presupuesto.
                    </div>

                @endif

                @if ($editable)

                    <div class="form-block">

                        <div class="form-title">
                            {{
                                $itemEditandoId
                                    ? 'Editar concepto'
                                    : 'Agregar concepto'
                            }}
                        </div>

                        <div class="form-grid">

                            <div class="field">

                                <label>
                                    Concepto *
                                </label>

                                <input
                                    class="input"
                                    type="text"
                                    maxlength="255"
                                    wire:model="concepto"
                                    placeholder="
                                        Nombre del concepto
                                    "
                                >

                                @error('concepto')
                                    <div class="field-error">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                            <div class="field">

                                <label>
                                    Categoría *
                                </label>

                                <select
                                    class="select"
                                    wire:model="categoriaGasto"
                                >
                                    @foreach (
                                        $categorias
                                        as $valor => $etiqueta
                                    )
                                        <option
                                            value="{{ $valor }}"
                                        >
                                            {{ $etiqueta }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('categoriaGasto')
                                    <div class="field-error">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                            <div class="field">

                                <label>
                                    Cantidad *
                                </label>

                                <input
                                    class="input"
                                    type="number"
                                    min="0.01"
                                    step="0.01"
                                    wire:model.live="
                                        cantidad
                                    "
                                >

                                @error('cantidad')
                                    <div class="field-error">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>

                        <div class="form-grid">

                            <div class="field">

                                <label>
                                    Precio unitario *
                                </label>

                                <input
                                    class="input"
                                    type="number"
                                    min="0.01"
                                    step="0.01"
                                    wire:model.live="
                                        precioUnitario
                                    "
                                    placeholder="0.00"
                                >

                                @error('precioUnitario')
                                    <div class="field-error">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                            <div class="field">

                                <label>
                                    Moneda
                                </label>

                                <div class="subtotal-preview">
                                    {{ $moneda }}
                                </div>

                            </div>

                            <div class="field">

                                <label>
                                    Subtotal
                                </label>

                                <div class="subtotal-preview">
                                    $
                                    {{
                                        number_format(
                                            max(
                                                0,
                                                (float) $cantidad
                                            )
                                            *
                                            max(
                                                0,
                                                (float)
                                                $precioUnitario
                                            ),
                                            2
                                        )
                                    }}
                                    {{ $moneda }}
                                </div>

                            </div>

                        </div>

                        <div class="field">

                            <label style="margin-top:12px;">
                                Descripción
                            </label>

                            <textarea
                                class="textarea"
                                maxlength="5000"
                                wire:model="descripcion"
                                placeholder="
                                    Descripción del concepto...
                                "
                            ></textarea>

                            @error('descripcion')
                                <div class="field-error">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        <div class="form-actions">

                            @if ($itemEditandoId)

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
                                    guardarConcepto
                                "
                                wire:loading.attr="
                                    disabled
                                "
                                wire:target="
                                    guardarConcepto
                                "
                            >
                                {{
                                    $itemEditandoId
                                        ? 'Guardar cambios'
                                        : '+ Agregar al presupuesto'
                                }}
                            </button>

                        </div>

                    </div>

                @endif

                <div class="totals">

                    @foreach (
                        $totalesCategoria
                        as $categoria => $monto
                    )

                        <div class="total-row">

                            <span>
                                {{
                                    $categorias[
                                        $categoria
                                    ]
                                    ?? $categoria
                                }}
                            </span>

                            <strong>
                                $
                                {{ number_format(
                                    $monto,
                                    2
                                ) }}
                            </strong>

                        </div>

                    @endforeach

                    <div class="total-row final">

                        <span>
                            Total solicitado
                        </span>

                        <strong>
                            $
                            {{ number_format(
                                $total,
                                2
                            ) }}
                            {{ $moneda }}
                        </strong>

                    </div>

                    @if ($montoMaximo !== null)

                        <div
                            class="
                                budget-limit
                                budget-ok
                            "
                        >
                            Monto máximo de la convocatoria:
                            <strong>
                                $
                                {{
                                    number_format(
                                        $montoMaximo,
                                        2
                                    )
                                }}
                                {{ $moneda }}
                            </strong>

                            <br>

                            Disponible:
                            <strong>
                                $
                                {{
                                    number_format(
                                        max(
                                            0,
                                            $disponible
                                        ),
                                        2
                                    )
                                }}
                                {{ $moneda }}
                            </strong>

                            @if (
                                $porcentajePresupuesto
                                !== null
                            )

                                <div class="limit-progress">

                                    <div
                                        class="
                                            limit-progress-value
                                        "
                                        style="
                                            width:
                                            {{
                                                $porcentajePresupuesto
                                            }}%;
                                        "
                                    ></div>

                                </div>

                            @endif
                        </div>

                    @else

                        <div
                            class="
                                budget-limit
                                budget-none
                            "
                        >
                            La convocatoria no tiene un
                            monto máximo registrado.
                        </div>

                    @endif

                </div>

                <div class="bottom-actions">

                    <button
                        type="button"
                        class="btn"
                        wire:click="
                            volverARequisitos
                        "
                    >
                        ← Volver a Requisitos
                    </button>

                    <button
                        type="button"
                        class="
                            btn
                            btn-primary
                        "
                        wire:click="
                            continuarACotizaciones
                        "
                    >
                        Continuar a Cotizaciones →
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
                <span>Conceptos</span>
                <strong>{{ $items->count() }}</strong>
            </div>

            <div class="sidebar-row">
                <span>Total</span>

                <strong>
                    $
                    {{ number_format(
                        $total,
                        2
                    ) }}
                    {{ $moneda }}
                </strong>
            </div>

            @if ($montoMaximo !== null)

                <div class="sidebar-row">
                    <span>Disponible</span>

                    <strong>
                        $
                        {{
                            number_format(
                                max(
                                    0,
                                    $disponible
                                ),
                                2
                            )
                        }}
                    </strong>
                </div>

            @endif

            <div class="sidebar-progress">

                <div class="progress-top">
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
                        active
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
                        4
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

        </aside>

    </div>

</div>

</x-filament-panels::page>
