<x-filament-panels::page>

<style>
    .prop-page {
        color: #1A2C4E;
    }

    .prop-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        margin-bottom: 22px;
    }

    .prop-head-text h2 {
        margin: 0;
        font-size: 21px;
        font-weight: 700;
    }

    .prop-head-text p {
        margin: 5px 0 0;
        color: #64748B;
        font-size: 12px;
    }

    .prop-new {
        border: 0;
        border-radius: 9px;
        background: #1A4B8C;
        color: white;
        min-height: 40px;
        padding: 0 16px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
    }

    .prop-stats {
        display: grid;
        grid-template-columns:
            repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 20px;
    }

    .prop-stat {
        background: white;
        border: 1px solid #DDE3EE;
        border-radius: 11px;
        padding: 17px;
    }

    .prop-stat-label {
        color: #64748B;
        font-size: 11px;
    }

    .prop-stat-number {
        margin-top: 5px;
        font-size: 24px;
        font-weight: 700;
        color: #1A2C4E;
    }

    .prop-table {
        background: white;
        border: 1px solid #DDE3EE;
        border-radius: 12px;
        overflow: hidden;
    }

    .prop-row {
        display: grid;
        grid-template-columns:
            minmax(240px, 2fr)
            minmax(200px, 1.5fr)
            120px
            120px
            120px
            minmax(260px, 1.5fr);
        gap: 14px;
        padding: 16px 18px;
        align-items: center;
        border-bottom: 1px solid #EEF2F7;
    }

    .prop-row:last-child {
        border-bottom: 0;
    }

    .prop-header {
        background: #F8FAFC;
        color: #64748B;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .prop-title {
        color: #1A2C4E;
        font-size: 13px;
        font-weight: 700;
    }

    .prop-version {
        color: #64748B;
        font-size: 10px;
        margin-top: 4px;
    }

    .prop-conv {
        color: #475569;
        font-size: 11px;
    }

    .prop-date {
        color: #64748B;
        font-size: 11px;
    }

    .status {
        display: inline-flex;
        width: fit-content;
        padding: 5px 9px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
    }

    .status-borrador {
        background: #F1F5F9;
        color: #475569;
    }

    .status-generada {
        background: #DBEAFE;
        color: #2563EB;
    }

    .status-editando {
        background: #EDE9FE;
        color: #7C3AED;
    }

    .status-lista {
        background: #D1FAE5;
        color: #059669;
    }

    .status-enviada {
        background: #FEF3C7;
        color: #D97706;
    }

    .status-aprobada {
        background: #D1FAE5;
        color: #047857;
    }

    .status-rechazada {
        background: #FEE2E2;
        color: #DC2626;
    }

    .prop-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
    }

    .prop-action {
        border: 1px solid #DDE3EE;
        border-radius: 7px;
        background: white;
        color: #1A4B8C;
        padding: 6px 8px;
        font-size: 10px;
        font-weight: 600;
        cursor: pointer;
    }

    .prop-action-main {
        background: #1A4B8C;
        border-color: #1A4B8C;
        color: white;
    }

    .prop-empty {
        padding: 55px 20px;
        text-align: center;
    }

    .prop-empty-icon {
        width: 54px;
        height: 54px;
        margin: 0 auto 13px;
        border-radius: 14px;
        background: #E8EEF8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }

    .prop-empty h3 {
        margin: 0 0 5px;
        color: #1A2C4E;
        font-size: 15px;
    }

    .prop-empty p {
        margin: 0;
        color: #64748B;
        font-size: 12px;
    }

    .modal-backdrop {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
        background: rgba(15, 24, 39, .45);
    }

    .prop-modal {
        width: 100%;
        max-width: 560px;
        background: white;
        border-radius: 14px;
        box-shadow:
            0 20px 60px rgba(0, 0, 0, .20);
    }

    .prop-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 19px 21px;
        border-bottom: 1px solid #EEF2F7;
    }

    .prop-modal-header h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 700;
    }

    .modal-close {
        border: 0;
        background: transparent;
        cursor: pointer;
        font-size: 21px;
    }

    .prop-modal-body {
        padding: 21px;
    }

    .prop-field {
        margin-bottom: 15px;
    }

    .prop-field label {
        display: block;
        margin-bottom: 6px;
        color: #475569;
        font-size: 11px;
        font-weight: 700;
    }

    .prop-field input,
    .prop-field select {
        width: 100%;
        min-height: 42px;
        padding: 0 11px;
        border: 1px solid #DDE3EE;
        border-radius: 8px;
        background: white;
        color: #1A2C4E;
    }

    .ia-box {
        padding: 15px;
        background: #F0F4FB;
        border-radius: 9px;
        color: #475569;
        font-size: 11px;
        line-height: 1.5;
    }

    .prop-modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        padding: 16px 21px;
        border-top: 1px solid #EEF2F7;
    }

    .modal-btn {
        border: 1px solid #DDE3EE;
        background: white;
        color: #475569;
        border-radius: 8px;
        min-height: 38px;
        padding: 0 14px;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
    }

    .modal-btn-primary {
        border-color: #1A4B8C;
        background: #1A4B8C;
        color: white;
    }

    @media (max-width: 1200px) {
        .prop-row {
            grid-template-columns:
                minmax(200px, 2fr)
                minmax(180px, 1fr)
                110px
                100px
                100px;
        }

        .prop-row > :last-child {
            grid-column: 1 / -1;
        }

        .prop-header > :last-child {
            display: none;
        }
    }

    @media (max-width: 800px) {
        .prop-stats {
            grid-template-columns:
                repeat(2, 1fr);
        }

        .prop-row {
            grid-template-columns: 1fr;
        }

        .prop-header {
            display: none;
        }

        .prop-head {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<div class="prop-page">

    <div class="prop-head">

        <div class="prop-head-text">
            <h2>Mis Propuestas</h2>

            <p>
                Administra y continúa el desarrollo
                de tus propuestas de proyecto.
            </p>
        </div>

        <a
    href="{{
        route(
            'filament.docente.pages.generar-propuesta'
        )
    }}"
    class="prop-new"
    style="
        display:inline-flex;
        align-items:center;
        text-decoration:none;
    "
>
    + Nueva propuesta
</a>

    </div>

    @php
        $total = $propuestas->count();

        $enProceso = $propuestas
            ->whereIn(
                'estado',
                [
                    'BORRADOR',
                    'GENERADA',
                    'EDITANDO',
                ]
            )
            ->count();

        $listas = $propuestas
            ->where('estado', 'LISTA')
            ->count();

        $enviadas = $propuestas
            ->whereIn(
                'estado',
                [
                    'ENVIADA',
                    'APROBADA',
                ]
            )
            ->count();
    @endphp

    <div class="prop-stats">

        <div class="prop-stat">
            <div class="prop-stat-label">
                Total de propuestas
            </div>

            <div class="prop-stat-number">
                {{ $total }}
            </div>
        </div>

        <div class="prop-stat">
            <div class="prop-stat-label">
                En proceso
            </div>

            <div class="prop-stat-number">
                {{ $enProceso }}
            </div>
        </div>

        <div class="prop-stat">
            <div class="prop-stat-label">
                Listas
            </div>

            <div class="prop-stat-number">
                {{ $listas }}
            </div>
        </div>

        <div class="prop-stat">
            <div class="prop-stat-label">
                Enviadas
            </div>

            <div class="prop-stat-number">
                {{ $enviadas }}
            </div>
        </div>

    </div>

    <div class="prop-table">

        <div class="prop-row prop-header">
            <div>Propuesta</div>
            <div>Convocatoria</div>
            <div>Estado</div>
            <div>Creación</div>
            <div>Actualización</div>
            <div>Acciones</div>
        </div>

        @forelse ($propuestas as $propuesta)

            @php
                $estado =
                    strtoupper(
                        $propuesta['estado']
                    );

                $claseEstado =
                    match ($estado) {
                        'GENERADA' =>
                            'status-generada',

                        'EDITANDO' =>
                            'status-editando',

                        'LISTA' =>
                            'status-lista',

                        'ENVIADA' =>
                            'status-enviada',

                        'APROBADA' =>
                            'status-aprobada',

                        'RECHAZADA' =>
                            'status-rechazada',

                        default =>
                            'status-borrador',
                    };
            @endphp

            <div class="prop-row">

                <div>
                    <div class="prop-title">
                        {{ $propuesta['titulo'] }}
                    </div>

                    <div class="prop-version">
                        Versión
                        {{ $propuesta['version'] }}
                    </div>
                </div>

                <div class="prop-conv">
                    {{ $propuesta['convocatoria'] }}
                </div>

                <div>
                    <span
                        class="
                            status
                            {{ $claseEstado }}
                        "
                    >
                        {{
                            str_replace(
                                '_',
                                ' ',
                                $estado
                            )
                        }}
                    </span>
                </div>

                <div class="prop-date">
                    {{
                        $propuesta[
                            'fecha_creacion'
                        ]
                    }}
                </div>

                <div class="prop-date">
                    {{
                        $propuesta[
                            'fecha_actualizacion'
                        ]
                    }}
                </div>

                <div class="prop-actions">

                    <button
                        type="button"
                        class="
                            prop-action
                            prop-action-main
                        "
                    >
                        Continuar
                    </button>

                    <button
                        type="button"
                        class="prop-action"
                    >
                        Editar
                    </button>

                    <button
                        type="button"
                        class="prop-action"
                    >
                        Vista previa
                    </button>

                    <button
                        type="button"
                        class="prop-action"
                    >
                        Exportar
                    </button>

                </div>

            </div>

        @empty

            <div class="prop-empty">

                <div class="prop-empty-icon">
                    📄
                </div>

                <h3>
                    Aún no tienes propuestas
                </h3>

                <p>
                    Explora convocatorias y crea
                    tu primera propuesta.
                </p>

            </div>

        @endforelse

    </div>
</div>

</x-filament-panels::page>
