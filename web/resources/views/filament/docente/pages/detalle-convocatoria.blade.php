<x-filament-panels::page>

    <style>
        .detalle-page {
            color: #1A2C4E;
        }

        .detalle-top {
            background: white;
            border: 1px solid #DDE3EE;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 18px;
        }

        .detalle-top-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 24px;
        }

        .detalle-title {
            font-size: 22px;
            font-weight: 700;
            color: #1A2C4E;
            margin-bottom: 7px;
            line-height: 1.4;
        }

        .detalle-organismo {
            color: #64748B;
            font-size: 13px;
        }

        .detalle-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 16px;
        }

        .detalle-badge {
            display: inline-flex;
            align-items: center;
            padding: 5px 10px;
            border-radius: 20px;
            background: #F0F4FB;
            color: #1A4B8C;
            font-size: 11px;
            font-weight: 600;
        }

        .detalle-badge-success {
            background: #D1FAE5;
            color: #059669;
        }

        .detalle-badge-blue {
            background: #DBEAFE;
            color: #2563EB;
        }

        .detalle-badge-purple {
            background: #EDE9FE;
            color: #6D28D9;
        }

        .detalle-tabs {
            display: flex;
            gap: 6px;
            padding: 6px;
            background: white;
            border: 1px solid #DDE3EE;
            border-radius: 10px;
            margin-bottom: 18px;
            overflow-x: auto;
        }

        .detalle-tab {
            border: 0;
            background: transparent;
            padding: 9px 14px;
            border-radius: 7px;
            color: #64748B;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
        }

        .detalle-tab:hover {
            background: #F4F6FA;
        }

        .detalle-tab.active {
            color: #1A4B8C;
            background: #E8EEF8;
        }

        .detalle-card {
            background: white;
            border: 1px solid #DDE3EE;
            border-radius: 12px;
            margin-bottom: 16px;
            overflow: hidden;
        }

        .detalle-card-header {
            padding: 17px 20px;
            border-bottom: 1px solid #EEF2F7;
        }

        .detalle-card-header h3 {
            margin: 0;
            font-size: 15px;
            font-weight: 700;
            color: #1A2C4E;
        }

        .detalle-card-header p {
            margin: 5px 0 0;
            color: #64748B;
            font-size: 11px;
        }

        .detalle-card-body {
            padding: 20px;
        }

        .detalle-grid {
            display: grid;
            grid-template-columns: repeat(
                2,
                minmax(0, 1fr)
            );
            gap: 20px 28px;
        }

        .detalle-field label {
            display: block;
            color: #64748B;
            font-size: 11px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .detalle-field div {
            font-size: 13px;
            color: #1A2C4E;
            line-height: 1.6;
        }

        .detalle-full {
            grid-column: 1 / -1;
        }

        .requisito-item,
        .archivo-item,
        .fecha-item,
        .financiamiento-item {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            padding: 14px 0;
            border-bottom: 1px solid #EEF2F7;
        }

        .requisito-item:last-child,
        .archivo-item:last-child,
        .fecha-item:last-child,
        .financiamiento-item:last-child {
            border-bottom: 0;
        }

        .item-title {
            font-size: 13px;
            font-weight: 600;
            color: #1A2C4E;
        }

        .item-sub {
            margin-top: 4px;
            font-size: 11px;
            color: #64748B;
            line-height: 1.5;
        }

        .item-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 7px;
        }

        .mini-badge {
            padding: 4px 7px;
            border-radius: 999px;
            background: #F0F4FB;
            color: #1A4B8C;
            font-size: 9px;
            font-weight: 650;
        }

        .mini-badge-required {
            background: #FEF3C7;
            color: #B45309;
        }

        .archivo-link {
            color: #2563EB;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }

        .finance-grid {
            display: grid;
            grid-template-columns: repeat(
                2,
                minmax(0, 1fr)
            );
            gap: 14px;
        }

        .finance-card {
            border: 1px solid #E2E8F0;
            border-radius: 10px;
            padding: 16px;
            background: #FBFCFE;
        }

        .finance-title {
            color: #1A2C4E;
            font-size: 13px;
            font-weight: 700;
        }

        .finance-amount {
            margin-top: 8px;
            color: #059669;
            font-size: 18px;
            font-weight: 800;
        }

        .finance-detail {
            margin-top: 7px;
            color: #64748B;
            font-size: 10px;
            line-height: 1.55;
        }

        .empty-block {
            text-align: center;
            padding: 30px;
            color: #64748B;
            font-size: 13px;
        }

        .detalle-actions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 18px;
            padding: 18px;
            background: white;
            border: 1px solid #DDE3EE;
            border-radius: 12px;
        }

        .detalle-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            padding: 0 15px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }

        .detalle-btn-primary {
            border: 1px solid #1A4B8C;
            background: #1A4B8C;
            color: white;
        }

        .detalle-btn-primary:hover {
            background: #153F77;
        }

        .detalle-btn-secondary {
            background: white;
            color: #1A4B8C;
            border: 1px solid #DDE3EE;
        }

        .detalle-btn-success {
            border: 1px solid #A7F3D0;
            background: #ECFDF5;
            color: #047857;
        }

        .detalle-btn-purple {
            border: 1px solid #DDD6FE;
            background: #F5F3FF;
            color: #6D28D9;
        }

        .detalle-spacer {
            margin-left: auto;
        }

        @media (max-width: 760px) {
            .detalle-grid,
            .finance-grid {
                grid-template-columns: 1fr;
            }

            .detalle-full {
                grid-column: auto;
            }

            .detalle-top-row {
                flex-direction: column;
            }

            .detalle-spacer {
                margin-left: 0;
            }
        }
    </style>

    <div class="detalle-page">

        <section class="detalle-top">

            <div class="detalle-top-row">

                <div>

                    <div class="detalle-title">
                        {{ $convocatoria->titulo }}
                    </div>

                    <div class="detalle-organismo">
                        {{ $convocatoria->organismo?->nombre
                            ?? 'Organismo no especificado' }}
                    </div>

                    <div class="detalle-badges">

                        @if ($convocatoria->categoria)

                            <span class="detalle-badge">
                                {{ $convocatoria
                                    ->categoria
                                    ->nombre }}
                            </span>

                        @endif

                        @if ($convocatoria->estado === 'ACTIVA')

                            <span
                                class="
                                    detalle-badge
                                    detalle-badge-success
                                "
                            >
                                Activa
                            </span>

                        @else

                            <span
                                class="
                                    detalle-badge
                                    detalle-badge-blue
                                "
                            >
                                Publicada
                            </span>

                        @endif

                        @if ($convocatoria->fecha_cierre)

                            <span class="detalle-badge">
                                Cierre:
                                {{ $convocatoria
                                    ->fecha_cierre
                                    ->format('d/m/Y') }}
                            </span>

                        @endif

                        @if (
                            $convocatoria->monto_maximo
                            !== null
                        )

                            <span
                                class="
                                    detalle-badge
                                    detalle-badge-purple
                                "
                            >
                                Hasta
                                $
                                {{ number_format(
                                    (float)
                                    $convocatoria->monto_maximo,
                                    2,
                                    '.',
                                    ','
                                ) }}
                                {{ $convocatoria->moneda ?? 'MXN' }}
                            </span>

                        @endif

                    </div>

                </div>

            </div>

        </section>

        <div class="detalle-tabs">

            <button
                type="button"
                wire:click="$set('tab', 'informacion')"
                class="detalle-tab {{
                    $tab === 'informacion'
                        ? 'active'
                        : ''
                }}"
            >
                Información general
            </button>

            <button
                type="button"
                wire:click="$set('tab', 'requisitos')"
                class="detalle-tab {{
                    $tab === 'requisitos'
                        ? 'active'
                        : ''
                }}"
            >
                Requisitos
                ({{ $convocatoria->requisitos->count() }})
            </button>

            <button
                type="button"
                wire:click="$set('tab', 'financiamiento')"
                class="detalle-tab {{
                    $tab === 'financiamiento'
                        ? 'active'
                        : ''
                }}"
            >
                Financiamiento
            </button>

            <button
                type="button"
                wire:click="$set('tab', 'archivos')"
                class="detalle-tab {{
                    $tab === 'archivos'
                        ? 'active'
                        : ''
                }}"
            >
                Archivos
                ({{ $convocatoria->archivos->count() }})
            </button>

            <button
                type="button"
                wire:click="$set('tab', 'fechas')"
                class="detalle-tab {{
                    $tab === 'fechas'
                        ? 'active'
                        : ''
                }}"
            >
                Fechas importantes
            </button>

        </div>

        @if ($tab === 'informacion')

            <section class="detalle-card">

                <div class="detalle-card-header">
                    <h3>
                        Información de la convocatoria
                    </h3>
                </div>

                <div class="detalle-card-body">

                    <div class="detalle-grid">

                        <div class="detalle-field">
                            <label>Organismo</label>

                            <div>
                                {{ $convocatoria
                                    ->organismo?->nombre
                                    ?? 'No especificado' }}
                            </div>
                        </div>

                        <div class="detalle-field">
                            <label>Categoría</label>

                            <div>
                                {{ $convocatoria
                                    ->categoria?->nombre
                                    ?? 'No especificada' }}
                            </div>
                        </div>

                        <div class="detalle-field">
                            <label>Fuente</label>

                            <div>
                                {{ $convocatoria
                                    ->fuente?->nombre
                                    ?? 'Registro manual' }}
                            </div>
                        </div>

                        <div class="detalle-field">
                            <label>Modalidad</label>

                            <div>
                                {{ $convocatoria->modalidad
                                    ?? 'No especificada' }}
                            </div>
                        </div>

                        <div class="detalle-field">
                            <label>Ubicación</label>

                            <div>
                                {{ $convocatoria->ubicacion
                                    ?? 'No especificada' }}
                            </div>
                        </div>

                        <div class="detalle-field">
                            <label>Moneda</label>

                            <div>
                                {{ $convocatoria->moneda
                                    ?? 'No especificada' }}
                            </div>
                        </div>

                        <div
                            class="
                                detalle-field
                                detalle-full
                            "
                        >
                            <label>Descripción</label>

                            <div>
                                {{ $convocatoria->descripcion
                                    ?? 'Sin descripción' }}
                            </div>
                        </div>

                        <div
                            class="
                                detalle-field
                                detalle-full
                            "
                        >
                            <label>Objetivo</label>

                            <div>
                                {{ $convocatoria->objetivo
                                    ?? 'Sin objetivo' }}
                            </div>
                        </div>

                        <div class="detalle-field">
                            <label>Monto mínimo</label>

                            <div>
                                @if (
                                    $convocatoria->monto_minimo
                                    !== null
                                )

                                    $
                                    {{ number_format(
                                        (float)
                                        $convocatoria
                                            ->monto_minimo,
                                        2,
                                        '.',
                                        ','
                                    ) }}
                                    {{ $convocatoria->moneda
                                        ?? 'MXN' }}

                                @else

                                    No especificado

                                @endif
                            </div>
                        </div>

                        <div class="detalle-field">
                            <label>Monto máximo</label>

                            <div>
                                @if (
                                    $convocatoria->monto_maximo
                                    !== null
                                )

                                    $
                                    {{ number_format(
                                        (float)
                                        $convocatoria
                                            ->monto_maximo,
                                        2,
                                        '.',
                                        ','
                                    ) }}
                                    {{ $convocatoria->moneda
                                        ?? 'MXN' }}

                                @else

                                    No especificado

                                @endif
                            </div>
                        </div>

                    </div>

                </div>

            </section>

        @elseif ($tab === 'requisitos')

            <section class="detalle-card">

                <div class="detalle-card-header">

                    <h3>
                        Requisitos de participación
                    </h3>

                    <p>
                        Requisitos, restricciones y criterios
                        registrados para esta convocatoria.
                    </p>

                </div>

                <div class="detalle-card-body">

                    @forelse (
                        $convocatoria->requisitos
                        as $requisito
                    )

                        <div class="requisito-item">

                            <div>

                                <div class="item-title">
                                    {{ $requisito->titulo
                                        ?? 'Requisito' }}
                                </div>

                                @if ($requisito->descripcion)

                                    <div class="item-sub">
                                        {{ $requisito->descripcion }}
                                    </div>

                                @endif

                                <div class="item-tags">

                                    @if ($requisito->tipo_requisito)

                                        <span class="mini-badge">
                                            {{ ucfirst(
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    $requisito
                                                        ->tipo_requisito
                                                )
                                            ) }}
                                        </span>

                                    @endif

                                    @if ($requisito->obligatorio)

                                        <span
                                            class="
                                                mini-badge
                                                mini-badge-required
                                            "
                                        >
                                            Obligatorio
                                        </span>

                                    @endif

                                </div>

                            </div>

                        </div>

                    @empty

                        <div class="empty-block">
                            No hay requisitos registrados
                            para esta convocatoria.
                        </div>

                    @endforelse

                </div>

            </section>

        @elseif ($tab === 'financiamiento')

            <section class="detalle-card">

                <div class="detalle-card-header">

                    <h3>
                        Financiamiento
                    </h3>

                    <p>
                        Montos, modalidades y apoyos registrados
                        para la convocatoria.
                    </p>

                </div>

                <div class="detalle-card-body">

                    @if (
                        $convocatoria->financiamientos->isEmpty()
                        && $convocatoria
                            ->modalidadesFinanciamiento
                            ->isEmpty()
                        && $convocatoria
                            ->apoyosFinancieros
                            ->isEmpty()
                    )

                        <div class="empty-block">
                            No hay información adicional de
                            financiamiento registrada.
                        </div>

                    @else

                        <div class="finance-grid">

                            @foreach (
                                $convocatoria->financiamientos
                                as $financiamiento
                            )

                                <div class="finance-card">

                                    <div class="finance-title">
                                        {{ $financiamiento
                                            ->grupo_clave
                                            ?? 'Financiamiento' }}
                                    </div>

                                    <div class="finance-amount">
                                        $
                                        {{ number_format(
                                            (float)
                                            $financiamiento
                                                ->monto_maximo_total,
                                            2,
                                            '.',
                                            ','
                                        ) }}

                                        {{ $financiamiento
                                            ->moneda
                                            ?? 'MXN' }}
                                    </div>

                                    <div class="finance-detail">

                                        @if (
                                            $financiamiento
                                                ->etapa_1_monto_maximo
                                            !== null
                                        )

                                            Etapa 1
                                            @if (
                                                $financiamiento
                                                    ->etapa_1_anio
                                            )
                                                ({{ $financiamiento
                                                    ->etapa_1_anio }})
                                            @endif
                                            :
                                            $
                                            {{ number_format(
                                                (float)
                                                $financiamiento
                                                    ->etapa_1_monto_maximo,
                                                2,
                                                '.',
                                                ','
                                            ) }}

                                            <br>

                                        @endif

                                        @if (
                                            $financiamiento
                                                ->etapa_2_monto_maximo
                                            !== null
                                        )

                                            Etapa 2
                                            @if (
                                                $financiamiento
                                                    ->etapa_2_anio
                                            )
                                                ({{ $financiamiento
                                                    ->etapa_2_anio }})
                                            @endif
                                            :
                                            $
                                            {{ number_format(
                                                (float)
                                                $financiamiento
                                                    ->etapa_2_monto_maximo,
                                                2,
                                                '.',
                                                ','
                                            ) }}

                                        @endif

                                    </div>

                                </div>

                            @endforeach

                            @foreach (
                                $convocatoria
                                    ->modalidadesFinanciamiento
                                as $modalidad
                            )

                                <div class="finance-card">

                                    <div class="finance-title">
                                        {{ $modalidad->nombre }}
                                    </div>

                                    <div class="finance-amount">
                                        $
                                        {{ number_format(
                                            (float)
                                            $modalidad
                                                ->monto_maximo_total,
                                            2,
                                            '.',
                                            ','
                                        ) }}

                                        {{ $modalidad->moneda
                                            ?? 'MXN' }}
                                    </div>

                                    <div class="finance-detail">

                                        @forelse (
                                            $modalidad->etapas
                                            as $etapa
                                        )

                                            Etapa
                                            {{ $etapa->numero }}

                                            @if ($etapa->anio)
                                                ({{ $etapa->anio }})
                                            @endif

                                            :
                                            $
                                            {{ number_format(
                                                (float)
                                                $etapa
                                                    ->monto_maximo,
                                                2,
                                                '.',
                                                ','
                                            ) }}

                                            @unless ($loop->last)
                                                <br>
                                            @endunless

                                        @empty

                                            Sin etapas registradas.

                                        @endforelse

                                    </div>

                                </div>

                            @endforeach

                            @foreach (
                                $convocatoria->apoyosFinancieros
                                as $apoyo
                            )

                                <div class="finance-card">

                                    <div class="finance-title">
                                        {{ $apoyo->concepto
                                            ?? $apoyo->clave
                                            ?? 'Apoyo financiero' }}
                                    </div>

                                    @if (
                                        $apoyo->monto_maximo
                                        !== null
                                    )

                                        <div class="finance-amount">

                                            $
                                            {{ number_format(
                                                (float)
                                                $apoyo
                                                    ->monto_maximo,
                                                2,
                                                '.',
                                                ','
                                            ) }}

                                            {{ $apoyo->moneda
                                                ?? 'MXN' }}

                                        </div>

                                    @endif

                                    <div class="finance-detail">

                                        @if ($apoyo->componente)

                                            {{ $apoyo->componente }}

                                            <br>

                                        @endif

                                        @if ($apoyo->unidad)

                                            Unidad:
                                            {{ $apoyo->unidad }}

                                        @endif

                                    </div>

                                </div>

                            @endforeach

                        </div>

                    @endif

                </div>

            </section>

        @elseif ($tab === 'archivos')

            <section class="detalle-card">

                <div class="detalle-card-header">

                    <h3>
                        Archivos y documentos
                    </h3>

                    <p>
                        Documentación asociada a la convocatoria.
                    </p>

                </div>

                <div class="detalle-card-body">

                    @forelse (
                        $convocatoria->archivos
                        as $archivo
                    )

                        <div class="archivo-item">

                            <div>

                                <div class="item-title">
                                    📄
                                    {{ $archivo->nombre
                                        ?? 'Documento' }}
                                </div>

                                <div class="item-sub">
                                    {{ $archivo->tipo_archivo
                                        ?? 'Archivo adjunto' }}
                                </div>

                            </div>

                            @if (
                                $archivo->url_archivo
                                ?? false
                            )

                                <a
                                    href="{{ $archivo
                                        ->url_archivo }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="archivo-link"
                                >
                                    Abrir ↗
                                </a>

                            @endif

                        </div>

                    @empty

                        <div class="empty-block">
                            No hay archivos registrados
                            para esta convocatoria.
                        </div>

                    @endforelse

                </div>

            </section>

        @elseif ($tab === 'fechas')

            <section class="detalle-card">

                <div class="detalle-card-header">
                    <h3>
                        Fechas importantes
                    </h3>
                </div>

                <div class="detalle-card-body">

                    <div class="fecha-item">

                        <div>

                            <div class="item-title">
                                Fecha de publicación
                            </div>

                            <div class="item-sub">
                                {{ $convocatoria
                                    ->fecha_publicacion
                                    ?->format('d/m/Y')
                                    ?? 'No especificada' }}
                            </div>

                        </div>

                    </div>

                    <div class="fecha-item">

                        <div>

                            <div class="item-title">
                                Inicio de convocatoria
                            </div>

                            <div class="item-sub">
                                {{ $convocatoria
                                    ->fecha_inicio
                                    ?->format('d/m/Y')
                                    ?? 'No especificada' }}
                            </div>

                        </div>

                    </div>

                    <div class="fecha-item">

                        <div>

                            <div class="item-title">
                                Cierre de convocatoria
                            </div>

                            <div class="item-sub">
                                {{ $convocatoria
                                    ->fecha_cierre
                                    ?->format('d/m/Y')
                                    ?? 'No especificada' }}
                            </div>

                        </div>

                    </div>

                </div>

            </section>

        @endif

        <div class="detalle-actions">

            <a
                href="{{ route(
                    'filament.docente.pages.buscar-convocatorias'
                ) }}"
                class="
                    detalle-btn
                    detalle-btn-secondary
                "
            >
                ← Volver a búsqueda
            </a>

            @if ($guardada)

                <button
                    type="button"
                    class="
                        detalle-btn
                        detalle-btn-success
                    "
                    disabled
                >
                    ✓ Guardada en Mis Convocatorias
                </button>

            @else

                <button
                    type="button"
                    wire:click="guardarConvocatoria"
                    wire:loading.attr="disabled"
                    wire:target="guardarConvocatoria"
                    class="
                        detalle-btn
                        detalle-btn-primary
                    "
                >
                    + Guardar en Mis Convocatorias
                </button>

            @endif

            <a
                href="{{ route(
                    'filament.docente.pages.generar-propuesta',
                    [
                        'convocatoria' =>
                            $convocatoria->id
                    ]
                ) }}"
                class="
                    detalle-btn
                    detalle-btn-purple
                "
            >
                @if ($tienePropuesta)
                    Ver / continuar propuesta
                @else
                    Generar propuesta
                @endif
            </a>

            @if ($convocatoria->url_original)

                <a
                    href="{{ $convocatoria->url_original }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="
                        detalle-btn
                        detalle-btn-secondary
                        detalle-spacer
                    "
                >
                    Sitio oficial ↗
                </a>

            @endif

        </div>

    </div>

</x-filament-panels::page>
