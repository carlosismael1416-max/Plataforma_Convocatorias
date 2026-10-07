<x-filament-panels::page>

<style>
    .cal-page {
        color: #1A2C4E;
    }

    .cal-summary {
        display: grid;
        grid-template-columns:
            repeat(2, minmax(0, 210px));
        gap: 12px;
        margin-bottom: 18px;
    }

    .summary-card {
        background: white;
        border: 1px solid #DDE3EE;
        border-radius: 10px;
        padding: 14px;
    }

    .summary-value {
        font-size: 22px;
        font-weight: 800;
    }

    .summary-label {
        margin-top: 3px;
        color: #64748B;
        font-size: 10px;
    }

    .cal-layout {
        display: grid;
        grid-template-columns:
            minmax(0, 1fr)
            310px;
        gap: 20px;
    }

    .cal-card {
        background: #FFFFFF;
        border: 1px solid #DDE3EE;
        border-radius: 12px;
        overflow: hidden;
    }

    .cal-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        padding: 18px 20px;
        border-bottom: 1px solid #EEF2F7;
    }

    .cal-toolbar-left,
    .cal-toolbar-right {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .cal-month {
        font-size: 17px;
        font-weight: 700;
        text-transform: capitalize;
        min-width: 170px;
        text-align: center;
    }

    .cal-btn {
        border: 1px solid #DDE3EE;
        background: #FFFFFF;
        color: #1A2C4E;
        min-height: 36px;
        padding: 0 12px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        font-size: 11px;
    }

    .cal-btn-primary {
        background: #1A4B8C;
        border-color: #1A4B8C;
        color: white;
    }

    .cal-week {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        background: #F8FAFC;
        border-bottom: 1px solid #EEF2F7;
    }

    .cal-week div {
        padding: 11px;
        text-align: center;
        color: #64748B;
        font-size: 11px;
        font-weight: 700;
    }

    .cal-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
    }

    .cal-day {
        position: relative;
        min-height: 125px;
        padding: 8px;
        border-right: 1px solid #EEF2F7;
        border-bottom: 1px solid #EEF2F7;
    }

    .cal-day:nth-child(7n) {
        border-right: 0;
    }

    .cal-day-other {
        background: #FAFBFD;
    }

    .cal-day-other .cal-number {
        color: #CBD5E1;
    }

    .cal-day-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .cal-number {
        width: 27px;
        height: 27px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 600;
        border-radius: 50%;
    }

    .cal-today {
        background: #1A4B8C;
        color: white;
    }

    .day-add {
        border: 0;
        background: transparent;
        color: #94A3B8;
        cursor: pointer;
        font-size: 15px;
        border-radius: 5px;
    }

    .day-add:hover {
        color: #2563EB;
        background: #EFF6FF;
    }

    .cal-event {
        display: block;
        width: 100%;
        margin-top: 5px;
        padding: 5px 6px;
        border: 0;
        border-radius: 6px;
        font-size: 9px;
        line-height: 1.25;
        text-align: left;
        text-decoration: none;
        cursor: pointer;
        overflow: hidden;
    }

    .event-cierre {
        background: #DBEAFE;
        color: #1D4ED8;
    }

    .event-reunion {
        background: #EDE9FE;
        color: #6D28D9;
    }

    .event-recordatorio {
        background: #FEF3C7;
        color: #B45309;
    }

    .event-entregable {
        background: #D1FAE5;
        color: #047857;
    }

    .event-otro {
        background: #F1F5F9;
        color: #475569;
    }

    .event-saved {
        box-shadow:
            inset 3px 0 0 #059669;
    }

    .side-header {
        padding: 18px 20px;
        border-bottom: 1px solid #EEF2F7;
    }

    .side-header h3 {
        margin: 0;
        font-size: 15px;
        font-weight: 700;
    }

    .side-body {
        padding: 8px 18px 18px;
    }

    .next-event {
        padding: 14px 0;
        border-bottom: 1px solid #EEF2F7;
    }

    .next-event:last-child {
        border-bottom: 0;
    }

    .next-date {
        color: #2563EB;
        font-size: 10px;
        font-weight: 700;
    }

    .next-title {
        margin-top: 4px;
        color: #1A2C4E;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.4;
    }

    .next-type {
        margin-top: 4px;
        color: #64748B;
        font-size: 10px;
    }

    .next-actions {
        display: flex;
        gap: 6px;
        margin-top: 8px;
    }

    .tiny-btn {
        border: 1px solid #DDE3EE;
        border-radius: 6px;
        background: white;
        color: #1A4B8C;
        padding: 5px 7px;
        cursor: pointer;
        font-size: 9px;
        text-decoration: none;
    }

    .tiny-danger {
        color: #DC2626;
        border-color: #FECACA;
    }

    .legend {
        padding: 18px;
        border-top: 1px solid #EEF2F7;
    }

    .legend-title {
        font-size: 11px;
        color: #64748B;
        margin-bottom: 10px;
        font-weight: 700;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 8px;
        font-size: 11px;
    }

    .dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
    }

    .dot-blue {
        background: #2563EB;
    }

    .dot-purple {
        background: #7C3AED;
    }

    .dot-yellow {
        background: #D97706;
    }

    .dot-green {
        background: #059669;
    }

    .dot-gray {
        background: #64748B;
    }

    .modal-backdrop {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(15, 24, 39, .45);
    }

    .event-modal {
        width: 100%;
        max-width: 620px;
        max-height: 92vh;
        overflow-y: auto;
        background: white;
        border-radius: 14px;
        box-shadow:
            0 20px 60px rgba(0, 0, 0, .20);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 19px 22px;
        border-bottom: 1px solid #EEF2F7;
    }

    .modal-header h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 700;
    }

    .modal-close {
        border: 0;
        background: transparent;
        cursor: pointer;
        font-size: 20px;
    }

    .modal-body {
        padding: 22px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }

    .field-full {
        grid-column: 1 / -1;
    }

    .field label {
        display: block;
        margin-bottom: 6px;
        color: #475569;
        font-size: 11px;
        font-weight: 700;
    }

    .field input,
    .field select,
    .field textarea {
        width: 100%;
        border: 1px solid #DDE3EE;
        border-radius: 8px;
        background: white;
        padding: 10px 11px;
        color: #1A2C4E;
        font-size: 11px;
    }

    .checkbox-row {
        display: flex;
        align-items: center;
        gap: 8px;
        min-height: 40px;
    }

    .checkbox-row input {
        width: auto;
    }

    .field-error {
        margin-top: 5px;
        color: #DC2626;
        font-size: 9px;
    }

    .modal-footer {
        display: flex;
        justify-content: space-between;
        gap: 9px;
        padding: 16px 22px;
        border-top: 1px solid #EEF2F7;
    }

    .footer-right {
        display: flex;
        gap: 9px;
    }

    .empty-side {
        padding: 30px 0;
        text-align: center;
        color: #64748B;
        font-size: 11px;
    }

    @media (max-width: 1000px) {
        .cal-layout {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 700px) {
        .cal-summary {
            grid-template-columns: 1fr 1fr;
        }

        .cal-toolbar {
            align-items: flex-start;
            flex-direction: column;
        }

        .cal-day {
            min-height: 90px;
            padding: 5px;
        }

        .cal-event {
            font-size: 7px;
            padding: 4px;
        }

        .form-grid {
            grid-template-columns: 1fr;
        }

        .field-full {
            grid-column: auto;
        }
    }
</style>

<div class="cal-page">

    <div class="cal-summary">

        <div class="summary-card">

            <div class="summary-value">
                {{ $cierresMes }}
            </div>

            <div class="summary-label">
                Cierres de convocatorias
                en {{ $nombreMes }}
            </div>

        </div>

        <div class="summary-card">

            <div class="summary-value">
                {{ $eventosPersonalesMes }}
            </div>

            <div class="summary-label">
                Eventos personales
                en {{ $nombreMes }}
            </div>

        </div>

    </div>

    <div class="cal-layout">

        <section class="cal-card">

            <div class="cal-toolbar">

                <div class="cal-toolbar-left">

                    <button
                        type="button"
                        class="cal-btn"
                        wire:click="mesAnterior"
                    >
                        ←
                    </button>

                    <div class="cal-month">
                        {{ $nombreMes }}
                    </div>

                    <button
                        type="button"
                        class="cal-btn"
                        wire:click="mesSiguiente"
                    >
                        →
                    </button>

                    <button
                        type="button"
                        class="cal-btn"
                        wire:click="irHoy"
                    >
                        Hoy
                    </button>

                </div>

                <div class="cal-toolbar-right">

                    <button
                        type="button"
                        class="
                            cal-btn
                            cal-btn-primary
                        "
                        wire:click="abrirNuevoEvento"
                    >
                        + Nuevo evento
                    </button>

                </div>

            </div>

            <div class="cal-week">
                <div>Lun</div>
                <div>Mar</div>
                <div>Mié</div>
                <div>Jue</div>
                <div>Vie</div>
                <div>Sáb</div>
                <div>Dom</div>
            </div>

            <div class="cal-grid">

                @foreach ($dias as $dia)

                    @php
                        $fecha =
                            $dia->format('Y-m-d');

                        $esMesActual =
                            $dia->month === $mes;

                        $esHoy =
                            $dia->isToday();

                        $eventos =
                            $eventosPorDia->get(
                                $fecha,
                                collect()
                            );
                    @endphp

                    <div
                        class="
                            cal-day
                            {{
                                ! $esMesActual
                                    ? 'cal-day-other'
                                    : ''
                            }}
                        "
                    >

                        <div class="cal-day-top">

                            <div
                                class="
                                    cal-number
                                    {{
                                        $esHoy
                                            ? 'cal-today'
                                            : ''
                                    }}
                                "
                            >
                                {{ $dia->day }}
                            </div>

                            @if ($esMesActual)

                                <button
                                    type="button"
                                    class="day-add"
                                    title="Agregar evento"
                                    wire:click="
                                        abrirNuevoEvento(
                                            '{{ $fecha }}'
                                        )
                                    "
                                >
                                    +
                                </button>

                            @endif

                        </div>

                        @foreach ($eventos as $evento)

                            @php
                                $claseEvento = match (
                                    $evento['tipo']
                                ) {
                                    'CIERRE' =>
                                        'event-cierre',

                                    'REUNION' =>
                                        'event-reunion',

                                    'RECORDATORIO' =>
                                        'event-recordatorio',

                                    'ENTREGABLE' =>
                                        'event-entregable',

                                    default =>
                                        'event-otro',
                                };
                            @endphp

                            @if (
                                $evento['tipo']
                                === 'CIERRE'
                            )

                                <a
                                    href="{{ route(
                                        'filament.docente.pages.detalle-convocatoria',
                                        [
                                            'record' =>
                                                $evento['id']
                                        ]
                                    ) }}"
                                    class="
                                        cal-event
                                        {{ $claseEvento }}
                                        {{
                                            $evento['guardada']
                                                ? 'event-saved'
                                                : ''
                                        }}
                                    "
                                    title="{{
                                        $evento['titulo']
                                    }}"
                                >
                                    Cierre:
                                    {{ $evento['titulo'] }}

                                    @if (
                                        $evento['guardada']
                                    )
                                        ★
                                    @endif
                                </a>

                            @else

                                <button
                                    type="button"
                                    wire:click="
                                        editarEvento(
                                            {{ $evento['id'] }}
                                        )
                                    "
                                    class="
                                        cal-event
                                        {{ $claseEvento }}
                                    "
                                    title="
                                        Editar:
                                        {{ $evento['titulo'] }}
                                    "
                                >
                                    @if ($evento['hora'])
                                        {{ $evento['hora'] }}
                                    @endif

                                    {{ $evento['titulo'] }}
                                </button>

                            @endif

                        @endforeach

                    </div>

                @endforeach

            </div>

        </section>

        <aside class="cal-card">

            <div class="side-header">
                <h3>Próximos eventos</h3>
            </div>

            <div class="side-body">

                @forelse ($proximosEventos as $evento)

                    <div class="next-event">

                        <div class="next-date">

                            {{ $evento['fecha_texto'] }}

                            @if ($evento['hora'])
                                · {{ $evento['hora'] }}
                            @endif

                        </div>

                        <div class="next-title">
                            {{ $evento['titulo'] }}
                        </div>

                        <div class="next-type">

                            @if (
                                $evento['tipo']
                                === 'CIERRE'
                            )

                                Fecha de cierre

                                @if ($evento['guardada'])
                                    · ★ Guardada
                                @endif

                            @else

                                {{ ucfirst(
                                    strtolower(
                                        $evento['tipo']
                                    )
                                ) }}

                            @endif

                        </div>

                        <div class="next-actions">

                            @if (
                                $evento['tipo']
                                === 'CIERRE'
                            )

                                <a
                                    href="{{ route(
                                        'filament.docente.pages.detalle-convocatoria',
                                        [
                                            'record' =>
                                                $evento['id']
                                        ]
                                    ) }}"
                                    class="tiny-btn"
                                >
                                    Ver detalle
                                </a>

                            @else

                                <button
                                    type="button"
                                    class="tiny-btn"
                                    wire:click="
                                        editarEvento(
                                            {{ $evento['id'] }}
                                        )
                                    "
                                >
                                    Editar
                                </button>

                                <button
                                    type="button"
                                    class="
                                        tiny-btn
                                        tiny-danger
                                    "
                                    wire:click="
                                        eliminarEvento(
                                            {{ $evento['id'] }}
                                        )
                                    "
                                    wire:confirm="
                                        ¿Eliminar este evento?
                                    "
                                >
                                    Eliminar
                                </button>

                            @endif

                        </div>

                    </div>

                @empty

                    <div class="empty-side">
                        No hay próximos eventos.
                    </div>

                @endforelse

            </div>

            <div class="legend">

                <div class="legend-title">
                    Tipos de evento
                </div>

                <div class="legend-item">
                    <span
                        class="
                            dot
                            dot-blue
                        "
                    ></span>
                    Cierre de convocatoria
                </div>

                <div class="legend-item">
                    <span
                        class="
                            dot
                            dot-purple
                        "
                    ></span>
                    Reunión
                </div>

                <div class="legend-item">
                    <span
                        class="
                            dot
                            dot-yellow
                        "
                    ></span>
                    Recordatorio
                </div>

                <div class="legend-item">
                    <span
                        class="
                            dot
                            dot-green
                        "
                    ></span>
                    Entregable
                </div>

                <div class="legend-item">
                    <span
                        class="
                            dot
                            dot-gray
                        "
                    ></span>
                    Otro
                </div>

            </div>

        </aside>

    </div>

    @if ($mostrarNuevoEvento)

        <div class="modal-backdrop">

            <div class="event-modal">

                <div class="modal-header">

                    <h3>
                        {{
                            $eventoEditandoId
                                ? 'Editar evento'
                                : 'Nuevo evento'
                        }}
                    </h3>

                    <button
                        type="button"
                        class="modal-close"
                        wire:click="
                            cerrarNuevoEvento
                        "
                    >
                        ×
                    </button>

                </div>

                <div class="modal-body">

                    <div class="form-grid">

                        <div
                            class="
                                field
                                field-full
                            "
                        >

                            <label>
                                Título *
                            </label>

                            <input
                                type="text"
                                wire:model="tituloEvento"
                                placeholder="
                                    Nombre del evento
                                "
                            >

                            @error('tituloEvento')
                                <div class="field-error">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        <div
                            class="
                                field
                                field-full
                            "
                        >

                            <label>
                                Convocatoria relacionada
                            </label>

                            <select
                                wire:model="
                                    convocatoriaEvento
                                "
                            >
                                <option value="">
                                    Sin convocatoria
                                </option>

                                @foreach (
                                    $convocatoriasSeleccionables
                                    as $convocatoria
                                )

                                    <option
                                        value="{{
                                            $convocatoria->id
                                        }}"
                                    >
                                        {{
                                            $convocatoria
                                                ->titulo
                                        }}
                                    </option>

                                @endforeach

                            </select>

                            @error(
                                'convocatoriaEvento'
                            )
                                <div class="field-error">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        <div class="field">

                            <label>
                                Tipo *
                            </label>

                            <select
                                wire:model="tipoEvento"
                            >
                                <option
                                    value="REUNION"
                                >
                                    Reunión
                                </option>

                                <option
                                    value="RECORDATORIO"
                                >
                                    Recordatorio
                                </option>

                                <option
                                    value="ENTREGABLE"
                                >
                                    Entregable
                                </option>

                                <option value="OTRO">
                                    Otro
                                </option>
                            </select>

                        </div>

                        <div class="field">

                            <label>
                                Recordatorio
                            </label>

                            <div class="checkbox-row">

                                <input
                                    type="checkbox"
                                    wire:model.live="
                                        recordatorioEvento
                                    "
                                >

                                <span>
                                    Activar recordatorio
                                </span>

                            </div>

                        </div>

                        <div class="field">

                            <label>
                                Fecha *
                            </label>

                            <input
                                type="date"
                                wire:model="fechaEvento"
                            >

                            @error('fechaEvento')
                                <div class="field-error">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        <div class="field">

                            <label>
                                Hora *
                            </label>

                            <input
                                type="time"
                                wire:model="horaEvento"
                            >

                            @error('horaEvento')
                                <div class="field-error">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        @if ($recordatorioEvento)

                            <div
                                class="
                                    field
                                    field-full
                                "
                            >

                                <label>
                                    Avisar antes
                                </label>

                                <select
                                    wire:model="
                                        minutosRecordatorio
                                    "
                                >
                                    <option value="">
                                        Sin anticipación
                                    </option>

                                    <option value="60">
                                        1 hora antes
                                    </option>

                                    <option value="1440">
                                        1 día antes
                                    </option>

                                    <option value="4320">
                                        3 días antes
                                    </option>

                                    <option value="10080">
                                        7 días antes
                                    </option>
                                </select>

                            </div>

                        @endif

                        <div
                            class="
                                field
                                field-full
                            "
                        >

                            <label>
                                Descripción
                            </label>

                            <textarea
                                rows="4"
                                wire:model="
                                    descripcionEvento
                                "
                                placeholder="
                                    Descripción del evento
                                "
                            ></textarea>

                            @error(
                                'descripcionEvento'
                            )
                                <div class="field-error">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                    </div>

                </div>

                <div class="modal-footer">

                    <div>

                        @if ($eventoEditandoId)

                            <button
                                type="button"
                                class="
                                    cal-btn
                                    tiny-danger
                                "
                                wire:click="
                                    eliminarEvento(
                                        {{
                                            $eventoEditandoId
                                        }}
                                    )
                                "
                                wire:confirm="
                                    ¿Eliminar este evento?
                                "
                            >
                                Eliminar
                            </button>

                        @endif

                    </div>

                    <div class="footer-right">

                        <button
                            type="button"
                            class="cal-btn"
                            wire:click="
                                cerrarNuevoEvento
                            "
                        >
                            Cancelar
                        </button>

                        <div
                            x-data="{ enviando: false }"
                        >
                            <button
                                type="button"
                                x-show="! enviando"
                                x-on:click="
                                    enviando = true;
                                    $wire.guardarEvento();
                                "
                                class="
                                    cal-btn
                                    cal-btn-primary
                                "
                            >
                                {{
                                    $eventoEditandoId
                                        ? 'Guardar cambios'
                                        : 'Crear evento'
                                }}
                            </button>

                            <button
                                type="button"
                                x-show="enviando"
                                disabled
                                class="
                                    cal-btn
                                    cal-btn-primary
                                "
                                style="
                                    opacity:.65;
                                    cursor:not-allowed;
                                "
                            >
                                Guardando...
                            </button>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    @endif

</div>

</x-filament-panels::page>
