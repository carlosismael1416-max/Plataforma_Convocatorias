<x-filament-panels::page>

<style>
    .itsva-notifications {
        --text: #0F1827;
        --muted: #64748B;
        --primary: #1A4B8C;
        --primary-light: #2563EB;
        --border: #DDE3EE;
        --success: #059669;
        --warning: #D97706;
        --danger: #DC2626;

        color: var(--text);
    }

    .itsva-notifications * {
        box-sizing: border-box;
    }

    .notifications-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
        margin-bottom: 22px;
    }

    .notifications-title {
        margin: 0;
        font-size: 25px;
        font-weight: 800;
    }

    .notifications-subtitle {
        margin-top: 7px;
        color: var(--muted);
        font-size: 13px;
    }

    .notifications-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .notification-btn,
    .item-btn {
        border: 1px solid var(--border);
        background: white;
        color: var(--text);
        cursor: pointer;
        font-weight: 700;
        text-decoration: none;
    }

    .notification-btn {
        min-height: 40px;
        padding: 0 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        font-size: 11px;
    }

    .notification-btn.primary {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
    }

    .notification-btn:disabled {
        opacity: .5;
        cursor: not-allowed;
    }

    .summary-grid {
        display: grid;
        grid-template-columns:
            repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 20px;
    }

    .summary-card {
        padding: 16px;
        background: white;
        border: 1px solid var(--border);
        border-radius: 12px;
    }

    .summary-label {
        margin-bottom: 7px;
        color: var(--muted);
        font-size: 12px;
    }

    .summary-value {
        color: var(--text);
        font-size: 22px;
        font-weight: 850;
    }

    .summary-help {
        margin-top: 4px;
        color: #94A3B8;
        font-size: 12px;
    }

    .notifications-layout {
        display: grid;
        grid-template-columns:
            minmax(0, 1fr)
            280px;
        gap: 20px;
        align-items: start;
    }

    .notifications-panel,
    .side-panel {
        background: white;
        border: 1px solid var(--border);
        border-radius: 14px;
    }

    .notifications-panel {
        overflow: hidden;
    }

    .panel-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        padding: 18px 20px;
        border-bottom: 1px solid var(--border);
    }

    .panel-title {
        font-size: 14px;
        font-weight: 800;
    }

    .notification-count {
        padding: 5px 9px;
        border-radius: 999px;
        background: #E8EEF8;
        color: var(--primary);
        font-size: 12px;
        font-weight: 800;
    }

    .filters {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
        padding: 14px 20px;
        border-bottom: 1px solid var(--border);
        background: #FBFCFE;
    }

    .filter-btn {
        min-height: 31px;
        padding: 0 11px;
        border: 1px solid var(--border);
        border-radius: 999px;
        background: white;
        color: #475569;
        cursor: pointer;
        font-size: 12px;
        font-weight: 700;
    }

    .filter-btn.active {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
    }

    .notification-item {
        position: relative;
        display: grid;
        grid-template-columns:
            42px minmax(0, 1fr) auto;
        gap: 12px;
        padding: 16px 20px;
        border-bottom: 1px solid #EEF2F7;
    }

    .notification-item:last-child {
        border-bottom: 0;
    }

    .notification-item.unread {
        background: #F8FBFF;
    }

    .notification-item.unread::before {
        content: "";
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        width: 3px;
        background: var(--primary-light);
    }

    .notification-icon {
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 11px;
        font-size: 11px;
        font-weight: 900;
    }

    .icon-blue {
        background: #E8EEF8;
        color: var(--primary);
    }

    .icon-green {
        background: #D1FAE5;
        color: #065F46;
    }

    .icon-yellow {
        background: #FEF3C7;
        color: #92400E;
    }

    .icon-red {
        background: #FEE2E2;
        color: #991B1B;
    }

    .notification-header {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 7px;
    }

    .notification-title {
        color: #1E293B;
        font-size: 12px;
        font-weight: 800;
    }

    .new-badge {
        padding: 3px 6px;
        border-radius: 999px;
        background: #DBEAFE;
        color: #1D4ED8;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .notification-type {
        margin-top: 4px;
        color: #94A3B8;
        font-size: 11px;
        font-weight: 700;
    }

    .notification-text {
        margin-top: 5px;
        color: var(--muted);
        font-size: 12px;
        line-height: 1.55;
    }

    .notification-time {
        margin-top: 7px;
        color: #94A3B8;
        font-size: 12px;
    }

    .notification-side {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: 6px;
        min-width: 82px;
    }

    .item-btn {
        min-height: 28px;
        padding: 0 8px;
        border-radius: 7px;
        font-size: 11px;
    }

    .item-btn.primary {
        background: #EFF6FF;
        border-color: #BFDBFE;
        color: #1D4ED8;
    }

    .empty-state {
        padding: 50px 20px;
        text-align: center;
    }

    .empty-icon {
        width: 48px;
        height: 48px;
        margin: 0 auto 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        background: #E8EEF8;
        color: var(--primary);
        font-size: 18px;
        font-weight: 900;
    }

    .empty-title {
        color: #334155;
        font-size: 13px;
        font-weight: 800;
    }

    .empty-text {
        max-width: 420px;
        margin: 6px auto 0;
        color: var(--muted);
        font-size: 12px;
        line-height: 1.5;
    }

    .side-panel {
        position: sticky;
        top: 90px;
        padding: 18px;
    }

    .side-title {
        margin-bottom: 13px;
        font-size: 12px;
        font-weight: 800;
    }

    .preference {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: flex-start;
        padding: 11px 0;
        border-bottom: 1px solid #EEF2F7;
    }

    .preference:last-of-type {
        border-bottom: 0;
    }

    .preference-name {
        color: #334155;
        font-size: 13px;
        font-weight: 700;
    }

    .preference-description {
        margin-top: 3px;
        color: #94A3B8;
        font-size: 12px;
        line-height: 1.4;
    }

    .switch {
        width: 36px;
        height: 20px;
        flex: 0 0 36px;
        padding: 3px;
        border: 0;
        border-radius: 999px;
        background: #CBD5E1;
        cursor: pointer;
        transition: background .15s;
    }

    .switch-knob {
        display: block;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: white;
        box-shadow:
            0 1px 2px rgba(0,0,0,.15);
        transition: transform .15s;
    }

    .switch-on {
        background: #1A4B8C;
    }

    .switch-on .switch-knob {
        transform: translateX(16px);
    }

    .alert-box {
        margin-top: 16px;
        padding: 12px;
        border: 1px solid #DBEAFE;
        border-radius: 9px;
        background: #EFF6FF;
        color: #1E40AF;
        font-size: 12px;
        line-height: 1.5;
    }

    @media (max-width: 1000px) {
        .notifications-layout {
            grid-template-columns: 1fr;
        }

        .summary-grid {
            grid-template-columns:
                repeat(2, 1fr);
        }

        .side-panel {
            position: static;
        }
    }

    @media (max-width: 650px) {
        .notifications-top {
            flex-direction: column;
        }

        .summary-grid {
            grid-template-columns: 1fr;
        }

        .notification-item {
            grid-template-columns:
                40px minmax(0, 1fr);
            padding: 15px;
        }

        .notification-side {
            grid-column: 2;
            flex-direction: row;
            min-width: 0;
        }
    }
</style>

<div class="itsva-notifications">

    <div class="notifications-top">

        <div>

            <h1 class="notifications-title">
                Notificaciones
            </h1>

            <div class="notifications-subtitle">
                Consulta avisos sobre convocatorias,
                propuestas y fechas importantes.
            </div>

        </div>

        <div class="notifications-actions">

            <button
                type="button"
                class="
                    notification-btn
                    primary
                "
                wire:click="marcarTodasLeidas"
                wire:loading.attr="disabled"
                wire:target="marcarTodasLeidas"
                @disabled($sinLeer === 0)
            >
                Marcar todo como leído
            </button>

        </div>

    </div>

    <div class="summary-grid">

        <div class="summary-card">

            <div class="summary-label">
                Sin leer
            </div>

            <div class="summary-value">
                {{ $sinLeer }}
            </div>

            <div class="summary-help">
                Notificaciones pendientes
            </div>

        </div>

        <div class="summary-card">

            <div class="summary-label">
                Convocatorias
            </div>

            <div class="summary-value">
                {{ $totalConvocatorias }}
            </div>

            <div class="summary-help">
                Avisos relacionados
            </div>

        </div>

        <div class="summary-card">

            <div class="summary-label">
                Propuestas
            </div>

            <div class="summary-value">
                {{ $totalPropuestas }}
            </div>

            <div class="summary-help">
                Avisos relacionados
            </div>

        </div>

        <div class="summary-card">

            <div class="summary-label">
                Fechas límite
            </div>

            <div class="summary-value">
                {{ $avisosFecha }}
            </div>

            <div class="summary-help">
                Avisos de cierre o vencimiento
            </div>

        </div>

    </div>

    <div class="notifications-layout">

        <main class="notifications-panel">

            <div class="panel-header">

                <div class="panel-title">
                    Notificaciones recientes
                </div>

                <span class="notification-count">
                    {{ $total }}
                    {{
                        $total === 1
                            ? 'notificación'
                            : 'notificaciones'
                    }}
                </span>

            </div>

            <div class="filters">

                <button
                    type="button"
                    wire:click="cambiarFiltro('todas')"
                    class="
                        filter-btn
                        {{
                            $filtro === 'todas'
                                ? 'active'
                                : ''
                        }}
                    "
                >
                    Todas
                </button>

                <button
                    type="button"
                    wire:click="cambiarFiltro('sin_leer')"
                    class="
                        filter-btn
                        {{
                            $filtro
                                === 'sin_leer'
                                ? 'active'
                                : ''
                        }}
                    "
                >
                    Sin leer
                </button>

                <button
                    type="button"
                    wire:click="cambiarFiltro( 'convocatorias' )"
                    class="
                        filter-btn
                        {{
                            $filtro
                                === 'convocatorias'
                                ? 'active'
                                : ''
                        }}
                    "
                >
                    Convocatorias
                </button>

                <button
                    type="button"
                    wire:click="cambiarFiltro( 'propuestas' )"
                    class="
                        filter-btn
                        {{
                            $filtro
                                === 'propuestas'
                                ? 'active'
                                : ''
                        }}
                    "
                >
                    Propuestas
                </button>

                <button
                    type="button"
                    wire:click="cambiarFiltro('fechas')"
                    class="
                        filter-btn
                        {{
                            $filtro === 'fechas'
                                ? 'active'
                                : ''
                        }}
                    "
                >
                    Fechas límite
                </button>

            </div>

            @forelse (
                $notificaciones
                as $notificacion
            )

                @php
                    [
                        $icono,
                        $claseIcono
                    ] = match (
                        $notificacion->tipo
                    ) {
                        'NUEVA_CONVOCATORIA' =>
                            ['C', 'icon-blue'],

                        'CAMBIO_CONVOCATORIA' =>
                            ['C', 'icon-blue'],

                        'CONVOCATORIA_POR_CERRAR' =>
                            ['!', 'icon-yellow'],

                        'PROPUESTA_GENERADA' =>
                            ['P', 'icon-green'],

                        'PROPUESTA_POR_VENCER' =>
                            ['!', 'icon-yellow'],

                        'SCRAPING_ERROR' =>
                            ['!', 'icon-red'],

                        default =>
                            ['i', 'icon-blue'],
                    };

                    $tieneDestino =
                        $notificacion
                            ->propuesta_id
                        || $notificacion
                            ->convocatoria_id;
                @endphp

                <article
                    class="
                        notification-item
                        {{
                            ! $notificacion->leida
                                ? 'unread'
                                : ''
                        }}
                    "
                    wire:key="notificacion-{{ $notificacion->id }}"
                    style="cursor: pointer"
                    {{-- Toda la fila: abre su destino o, si no tiene, la marca leída. --}}
                    x-on:click="if (! $event.target.closest('a, button')) $wire.{{ $tieneDestino ? 'abrirNotificacion' : 'marcarLeida' }}({{ $notificacion->id }})"
                >

                    <div
                        class="
                            notification-icon
                            {{ $claseIcono }}
                        "
                    >
                        {{ $icono }}
                    </div>

                    <div>

                        <div class="notification-header">

                            <div class="notification-title">
                                {{
                                    $notificacion
                                        ->titulo
                                }}
                            </div>

                            @if (
                                ! $notificacion->leida
                            )
                                <span class="new-badge">
                                    Nueva
                                </span>
                            @endif

                        </div>

                        <div class="notification-type">
                            {{
                                str_replace(
                                    '_',
                                    ' ',
                                    $notificacion->tipo
                                )
                            }}
                        </div>

                        <div class="notification-text">
                            {{
                                $notificacion
                                    ->mensaje
                            }}
                        </div>

                        <div class="notification-time">

                            {{
                                $notificacion
                                    ->created_at
                                    ?->locale('es')
                                    ->diffForHumans()
                                ?? 'Sin fecha'
                            }}

                            @if (
                                $notificacion
                                    ->leida
                                && $notificacion
                                    ->fecha_lectura
                            )

                                · Leída
                                {{
                                    $notificacion
                                        ->fecha_lectura
                                        ->format(
                                            'd/m/Y H:i'
                                        )
                                }}

                            @endif

                        </div>

                    </div>

                    <div class="notification-side">

                        @if ($tieneDestino)

                            <button
                                type="button"
                                class="
                                    item-btn
                                    primary
                                "
                                wire:click="abrirNotificacion( {{ $notificacion ->id }} )"
                            >
                                Ver
                            </button>

                        @endif

                        @if (
                            ! $notificacion->leida
                        )

                            <button
                                type="button"
                                class="item-btn"
                                wire:click="marcarLeida( {{ $notificacion ->id }} )"
                            >
                                Marcar leída
                            </button>

                        @else

                            <button
                                type="button"
                                class="item-btn"
                                wire:click="marcarNoLeida( {{ $notificacion ->id }} )"
                            >
                                No leída
                            </button>

                        @endif

                    </div>

                </article>

            @empty

                <div class="empty-state">

                    <div class="empty-icon">
                        🔔
                    </div>

                    <div class="empty-title">
                        No hay notificaciones
                    </div>

                    <div class="empty-text">

                        @if (
                            $total === 0
                        )

                            Todavía no se han generado
                            notificaciones para tu cuenta.

                        @else

                            No hay notificaciones que
                            coincidan con el filtro
                            seleccionado.

                        @endif

                    </div>

                </div>

            @endforelse

        </main>

        <aside
            class="side-panel"
            id="preferencias-notificaciones"
        >

            <div class="side-title">
                Preferencias
            </div>

            @foreach ([
                'fechas_proximas' => ['Fechas de cierre', 'Aviso cuando una convocatoria guardada o tu propuesta en preparación cierra en los próximos 7 días.'],
                'convocatorias_revision' => ['Cambios en convocatorias que sigo', 'Aviso si el sitio oficial cambia la fecha, agrega documentos o actualiza el texto de una convocatoria guardada o con propuesta.'],
                'nuevas_propuestas' => ['Cambios en propuestas', 'Aviso cuando un directivo aprueba, devuelve o rechaza tu propuesta.'],
            ] as $campo => [$nombre, $descripcion])
                <div class="preference">
                    <div>
                        <div class="preference-name">{{ $nombre }}</div>
                        <div class="preference-description">{{ $descripcion }}</div>
                    </div>

                    <button
                        type="button"
                        role="switch"
                        aria-checked="{{ $preferencias[$campo] ? 'true' : 'false' }}"
                        aria-label="{{ $nombre }}"
                        class="switch {{ $preferencias[$campo] ? 'switch-on' : '' }}"
                        wire:click="alternarPreferencia('{{ $campo }}')"
                        wire:loading.attr="disabled"
                    >
                        <span class="switch-knob"></span>
                    </button>
                </div>
            @endforeach

            <div class="alert-box">
                El historial de tus propuestas siempre queda disponible
                en su vista previa, aunque desactives los avisos.
            </div>

        </aside>

    </div>

</div>

</x-filament-panels::page>
