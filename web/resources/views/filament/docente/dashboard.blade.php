<x-filament-panels::page>

    <style>
        .itsva-dashboard {
            font-family: Inter, sans-serif;
            color: #1A2C4E;
        }

        .itsva-welcome {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 24px;
        }

        .itsva-welcome h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            color: #1A2C4E;
        }

        .itsva-welcome p {
            margin-top: 5px;
            color: #64748B;
            font-size: 14px;
        }

        .itsva-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .itsva-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 40px;
            padding: 0 16px;
            border-radius: 9px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: 0.2s ease;
        }

        .itsva-btn-primary {
            color: white;
            background: #1A4B8C;
            border: 1px solid #1A4B8C;
        }

        .itsva-btn-primary:hover {
            background: #153F77;
        }

        .itsva-btn-secondary {
            color: #1A4B8C;
            background: white;
            border: 1px solid #DDE3EE;
        }

        .itsva-btn-secondary:hover {
            background: #F4F6FA;
        }

        .itsva-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .itsva-stat-card,
        .itsva-card {
            background: white;
            border: 1px solid #DDE3EE;
            border-radius: 12px;
            box-shadow: 0 1px 2px rgba(15, 24, 39, .04);
        }

        .itsva-stat-card {
            padding: 19px;
        }

        .itsva-stat-link {
            display: block;
            color: inherit;
            text-decoration: none;
            cursor: pointer;
            transition:
                transform .16s ease,
                box-shadow .16s ease,
                border-color .16s ease;
        }

        .itsva-stat-link:hover {
            transform: translateY(-2px);
            border-color: #B8C8E3;
            box-shadow: 0 8px 20px rgba(15, 24, 39, .08);
        }

        .itsva-stat-link:focus-visible {
            outline: 3px solid rgba(37, 99, 235, .20);
            outline-offset: 2px;
        }

        .itsva-stat-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .itsva-stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .itsva-blue {
            background: #DBEAFE;
        }

        .itsva-green {
            background: #D1FAE5;
        }

        .itsva-purple {
            background: #EDE9FE;
        }

        .itsva-yellow {
            background: #FEF3C7;
        }

        .itsva-stat-label {
            color: #64748B;
            font-size: 12px;
            font-weight: 500;
        }

        .itsva-stat-value {
            margin-top: 4px;
            font-size: 27px;
            line-height: 1;
            font-weight: 700;
            color: #1A2C4E;
        }

        .itsva-grid-main {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(280px, 1fr);
            gap: 20px;
        }

        .itsva-card-header {
            padding: 18px 20px;
            border-bottom: 1px solid #EEF2F7;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .itsva-card-header h3 {
            margin: 0;
            font-size: 15px;
            font-weight: 700;
            color: #1A2C4E;
        }

        .itsva-card-body {
            padding: 16px 20px;
        }

        .itsva-convocatoria {
            padding: 15px 0;
            border-bottom: 1px solid #EEF2F7;
        }

        .itsva-convocatoria:last-child {
            border-bottom: 0;
        }

        .itsva-convocatoria-title {
            font-weight: 600;
            font-size: 14px;
            color: #1A2C4E;
            margin-bottom: 7px;
        }

        .itsva-convocatoria-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 15px;
            color: #64748B;
            font-size: 12px;
        }

        .itsva-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 20px;
            padding: 4px 9px;
            font-size: 11px;
            font-weight: 600;
        }

        .itsva-badge-blue {
            background: #DBEAFE;
            color: #2563EB;
        }

        .itsva-badge-green {
            background: #D1FAE5;
            color: #059669;
        }

        .itsva-badge-yellow {
            background: #FEF3C7;
            color: #D97706;
        }

        .itsva-deadline {
            display: flex;
            gap: 12px;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #EEF2F7;
        }

        .itsva-deadline:last-child {
            border-bottom: 0;
        }

        .itsva-deadline-day {
            min-width: 46px;
            height: 46px;
            background: #F0F4FB;
            border-radius: 9px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: #1A4B8C;
        }

        .itsva-deadline-day strong {
            font-size: 17px;
            line-height: 17px;
        }

        .itsva-deadline-day span {
            font-size: 9px;
            text-transform: uppercase;
            margin-top: 3px;
        }

        .itsva-deadline-info strong {
            display: block;
            font-size: 12px;
            color: #1A2C4E;
        }

        .itsva-deadline-info span {
            display: block;
            margin-top: 4px;
            color: #64748B;
            font-size: 11px;
        }

        .itsva-search-box {
            margin-bottom: 24px;
            background: linear-gradient(
                135deg,
                #0F2A57 0%,
                #1A4B8C 60%,
                #2563EB 100%
            );
            border-radius: 13px;
            padding: 24px;
            color: white;
        }

        .itsva-search-box h3 {
            font-size: 18px;
            margin: 0 0 5px 0;
            font-weight: 700;
        }

        .itsva-search-box p {
            font-size: 13px;
            color: #DBEAFE;
            margin-bottom: 17px;
        }

        .itsva-search-form {
            display: flex;
            gap: 8px;
        }

        .itsva-search-input {
            width: 100%;
            border: none;
            outline: none;
            border-radius: 8px;
            height: 42px;
            padding: 0 14px;
            background: white;
            color: #1A2C4E;
        }

        @media (max-width: 1100px) {
            .itsva-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .itsva-grid-main {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 650px) {
            .itsva-welcome {
                align-items: flex-start;
                flex-direction: column;
            }

            .itsva-stats {
                grid-template-columns: 1fr;
            }

            .itsva-search-form {
                flex-direction: column;
            }
        }
    </style>

    <div class="itsva-dashboard">

        {{-- Bienvenida --}}
        <div class="itsva-welcome">

            <div>
                <h2>
                    Bienvenido, {{ $this->nombreUsuario }}
                </h2>

                <p>
                    Consulta oportunidades de financiamiento y administra tus
                    convocatorias desde un mismo lugar.
                </p>
            </div>

            <div class="itsva-actions">

                <a
                    href="{{ route('filament.docente.pages.buscar-convocatorias') }}"
                    class="itsva-btn itsva-btn-secondary"
                >
                    🔍 Buscar convocatorias
                </a>

                <a
                    href="{{ route('filament.docente.resources.convocatorias.create') }}"
                    class="itsva-btn itsva-btn-primary"
                >
                    ＋ Subir convocatoria
                </a>

            </div>

        </div>

        {{-- Buscador principal --}}
        <div class="itsva-search-box">

            <h3>
                Encuentra oportunidades para tus proyectos
            </h3>

            <p>
                Busca convocatorias de investigación, educación, tecnología,
                infraestructura y financiamiento.
            </p>

            <form
                method="GET"
                action="{{ route('filament.docente.pages.buscar-convocatorias') }}"
                class="itsva-search-form"
            >

                <input
                    class="itsva-search-input"
                    type="text"
                    name="q"
                    placeholder="Buscar por palabra clave, organismo o área..."
                    autocomplete="off"
                >

                <button
                    type="submit"
                    class="itsva-btn"
                    style="
                        background:#FFFFFF;
                        color:#1A4B8C;
                        border:none;
                        cursor:pointer;
                    "
                >
                    Buscar
                </button>

            </form>

        </div>

        {{-- Estadísticas reales y navegables --}}
        <div class="itsva-stats">

            {{-- Convocatorias disponibles --}}
            <a
                href="{{ route('filament.docente.pages.buscar-convocatorias') }}"
                class="itsva-stat-card itsva-stat-link"
                title="Ir a Buscar Convocatorias"
            >

                <div class="itsva-stat-top">

                    <div>
                        <div class="itsva-stat-label">
                            Convocatorias disponibles
                        </div>

                        <div class="itsva-stat-value">
                            {{ $this->convocatoriasDisponibles }}
                        </div>
                    </div>

                    <div class="itsva-stat-icon itsva-blue">
                        📚
                    </div>

                </div>

                <span class="itsva-badge itsva-badge-blue">
                    Publicadas / activas
                </span>

            </a>

            {{-- Mis convocatorias --}}
            <a
                href="{{ route('filament.docente.pages.mis-convocatorias') }}"
                class="itsva-stat-card itsva-stat-link"
                title="Ir a Mis Convocatorias"
            >

                <div class="itsva-stat-top">

                    <div>
                        <div class="itsva-stat-label">
                            Mis convocatorias
                        </div>

                        <div class="itsva-stat-value">
                            {{ $this->misConvocatorias }}
                        </div>
                    </div>

                    <div class="itsva-stat-icon itsva-green">
                        ★
                    </div>

                </div>

                <span class="itsva-badge itsva-badge-green">
                    En seguimiento
                </span>

            </a>

            {{-- Mis propuestas --}}
            <a
                href="{{ route('filament.docente.pages.propuestas') }}"
                class="itsva-stat-card itsva-stat-link"
                title="Ir a Mis Propuestas"
            >

                <div class="itsva-stat-top">

                    <div>
                        <div class="itsva-stat-label">
                            Mis propuestas
                        </div>

                        <div class="itsva-stat-value">
                            {{ $this->misPropuestas }}
                        </div>
                    </div>

                    <div class="itsva-stat-icon itsva-purple">
                        📄
                    </div>

                </div>

                <span class="itsva-badge itsva-badge-blue">
                    Registradas
                </span>

            </a>

            {{-- Próximas a cerrar --}}
            <a
                href="{{ route('filament.docente.pages.calendario') }}"
                class="itsva-stat-card itsva-stat-link"
                title="Ir al Calendario"
            >

                <div class="itsva-stat-top">

                    <div>
                        <div class="itsva-stat-label">
                            Próximas a cerrar
                        </div>

                        <div class="itsva-stat-value">
                            {{ $this->proximasACerrar }}
                        </div>
                    </div>

                    <div class="itsva-stat-icon itsva-yellow">
                        ⏱
                    </div>

                </div>

                <span class="itsva-badge itsva-badge-yellow">
                    Fechas futuras
                </span>

            </a>

        </div>

        <div class="itsva-grid-main">

            {{-- Convocatorias disponibles --}}
            <section class="itsva-card">

                <div class="itsva-card-header">

                    <h3>
                        Convocatorias recientes
                    </h3>

                    <a
                        href="{{ route('filament.docente.pages.buscar-convocatorias') }}"
                        style="
                            font-size:12px;
                            color:#2563EB;
                            text-decoration:none;
                        "
                    >
                        Ver todas →
                    </a>

                </div>

                <div class="itsva-card-body">

                    @forelse (
                        $this->convocatoriasRecientes
                        as $convocatoria
                    )

                        <div class="itsva-convocatoria">

                            <div class="itsva-convocatoria-title">

                                <a
                                    href="{{ route(
                                        'filament.docente.pages.detalle-convocatoria',
                                        ['record' => $convocatoria['id']]
                                    ) }}"
                                    style="
                                        color:inherit;
                                        text-decoration:none;
                                    "
                                >
                                    {{ $convocatoria['titulo'] }}
                                </a>

                            </div>

                            <div class="itsva-convocatoria-meta">

                                <span>
                                    {{ $convocatoria['organismo'] }}
                                </span>

                                <span>
                                    {{ $convocatoria['categoria'] }}
                                </span>

                                <span>
                                    {{ $convocatoria['monto'] }}
                                </span>

                                @if (
                                    $convocatoria['estado']
                                    === 'ACTIVA'
                                )

                                    <span
                                        class="
                                            itsva-badge
                                            itsva-badge-green
                                        "
                                    >
                                        Activa
                                    </span>

                                @else

                                    <span
                                        class="
                                            itsva-badge
                                            itsva-badge-blue
                                        "
                                    >
                                        Publicada
                                    </span>

                                @endif

                            </div>

                        </div>

                    @empty

                        <div
                            style="
                                padding:28px 10px;
                                text-align:center;
                            "
                        >

                            <div
                                style="
                                    font-size:28px;
                                    margin-bottom:10px;
                                "
                            >
                                📭
                            </div>

                            <strong
                                style="
                                    display:block;
                                    color:#1A2C4E;
                                    font-size:13px;
                                "
                            >
                                No hay convocatorias disponibles
                            </strong>

                            <span
                                style="
                                    display:block;
                                    margin-top:6px;
                                    color:#64748B;
                                    font-size:11px;
                                    line-height:1.5;
                                "
                            >
                                Las convocatorias aparecerán aquí cuando
                                hayan sido publicadas o activadas.
                            </span>

                        </div>

                    @endforelse

                </div>

            </section>

            {{-- Próximos cierres --}}
            <aside class="itsva-card">

                <div class="itsva-card-header">
                    <h3>
                        Próximos cierres
                    </h3>
                </div>

                <div class="itsva-card-body">

                    @forelse (
                        $this->proximosCierres
                        as $convocatoria
                    )

                        <div class="itsva-deadline">

                            <div class="itsva-deadline-day">

                                <strong>
                                    {{ $convocatoria['dia'] }}
                                </strong>

                                <span>
                                    {{ $convocatoria['mes'] }}
                                </span>

                            </div>

                            <div class="itsva-deadline-info">

                                <strong>
                                    {{ $convocatoria['titulo'] }}
                                </strong>

                                <span>
                                    {{ $convocatoria['organismo'] }}
                                    ·
                                    {{ $convocatoria['fecha'] }}
                                </span>

                            </div>

                        </div>

                    @empty

                        <div
                            style="
                                padding:28px 5px;
                                text-align:center;
                            "
                        >

                            <div
                                style="
                                    font-size:26px;
                                    margin-bottom:9px;
                                "
                            >
                                📅
                            </div>

                            <strong
                                style="
                                    display:block;
                                    color:#1A2C4E;
                                    font-size:12px;
                                "
                            >
                                Sin cierres próximos
                            </strong>

                            <span
                                style="
                                    display:block;
                                    margin-top:5px;
                                    color:#64748B;
                                    font-size:10px;
                                    line-height:1.5;
                                "
                            >
                                No existen convocatorias publicadas
                                con una fecha de cierre futura.
                            </span>

                        </div>

                    @endforelse

                </div>

            </aside>

        </div>

    </div>

</x-filament-panels::page>
