<x-filament-panels::page>

    <style>
        .itsva-mis {
            color: #1A2C4E;
        }

        .mis-intro {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .mis-intro h2 {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
        }

        .mis-intro p {
            margin: 6px 0 0;
            color: #64748B;
            font-size: 13px;
        }

        .mis-stats {
            display: grid;
            grid-template-columns:
                repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 20px;
        }

        .mis-stat {
            padding: 16px;
            background: white;
            border: 1px solid #DDE3EE;
            border-radius: 12px;
        }

        .mis-stat-value {
            font-size: 23px;
            font-weight: 800;
        }

        .mis-stat-label {
            margin-top: 4px;
            color: #64748B;
            font-size: 10px;
        }

        .mis-toolbar {
            background: white;
            border: 1px solid #DDE3EE;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
        }

        .filter-grid {
            display: grid;
            grid-template-columns:
                minmax(220px, 2fr)
                repeat(3, minmax(150px, 1fr))
                auto;
            gap: 10px;
            align-items: end;
        }

        .filter-group label {
            display: block;
            margin-bottom: 5px;
            color: #64748B;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .mis-input,
        .mis-select {
            width: 100%;
            min-height: 40px;
            border: 1px solid #DDE3EE;
            border-radius: 8px;
            padding: 0 12px;
            color: #1A2C4E;
            background: white;
            font-size: 11px;
        }

        .mis-input:focus,
        .mis-select:focus {
            outline: none;
            border-color: #2563EB;
        }

        .clear-button {
            min-height: 40px;
            border: 1px solid #DDE3EE;
            border-radius: 8px;
            padding: 0 13px;
            background: white;
            color: #475569;
            cursor: pointer;
            font-size: 10px;
            font-weight: 700;
            white-space: nowrap;
        }

        .mis-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .mis-card {
            padding: 19px;
            background: white;
            border: 1px solid #DDE3EE;
            border-radius: 12px;
        }

        .mis-card.unavailable {
            background: #FFFBEB;
            border-color: #FDE68A;
        }

        .mis-card-top {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            align-items: flex-start;
        }

        .mis-title {
            font-weight: 700;
            font-size: 15px;
            line-height: 1.4;
            color: #1A2C4E;
        }

        .mis-sub {
            font-size: 11px;
            color: #64748B;
            margin-top: 4px;
        }

        .badges {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
            margin-top: 11px;
        }

        .status {
            display: inline-flex;
            padding: 5px 9px;
            border-radius: 20px;
            background: #DBEAFE;
            color: #2563EB;
            font-size: 9px;
            font-weight: 700;
        }

        .status-green {
            background: #D1FAE5;
            color: #047857;
        }

        .status-warning {
            background: #FEF3C7;
            color: #B45309;
        }

        .status-purple {
            background: #EDE9FE;
            color: #6D28D9;
        }

        .card-grid {
            display: grid;
            grid-template-columns:
                minmax(150px, .7fr)
                minmax(240px, 1.3fr);
            gap: 18px;
            margin-top: 17px;
            padding-top: 17px;
            border-top: 1px solid #EEF2F7;
        }

        .field-label {
            display: block;
            margin-bottom: 6px;
            color: #64748B;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .priority-select {
            width: 100%;
            min-height: 38px;
            border: 1px solid #DDE3EE;
            border-radius: 8px;
            padding: 0 10px;
            background: white;
            color: #1A2C4E;
            font-size: 10px;
        }

        .notes-textarea {
            width: 100%;
            min-height: 72px;
            resize: vertical;
            padding: 10px;
            border: 1px solid #DDE3EE;
            border-radius: 8px;
            background: white;
            color: #1A2C4E;
            font-size: 10px;
            line-height: 1.5;
        }

        .notes-actions {
            margin-top: 7px;
            text-align: right;
        }

        .mis-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 16px;
        }

        .action-button {
            display: inline-flex;
            min-height: 36px;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            padding: 0 12px;
            text-decoration: none;
            cursor: pointer;
            font-size: 10px;
            font-weight: 700;
        }

        .primary-button {
            border: 1px solid #1A4B8C;
            background: #1A4B8C;
            color: white;
        }

        .secondary-button {
            border: 1px solid #DDE3EE;
            background: white;
            color: #1A4B8C;
        }

        .favorite-button {
            border: 1px solid #FDE68A;
            background: #FEF3C7;
            color: #B45309;
        }

        .remove-button {
            margin-left: auto;
            border: 1px solid #FECACA;
            background: #FEF2F2;
            color: #DC2626;
        }

        .disabled-button {
            border: 1px solid #E2E8F0;
            background: #F8FAFC;
            color: #94A3B8;
            cursor: not-allowed;
        }

        .save-note {
            min-height: 32px;
            border: 1px solid #DDE3EE;
            border-radius: 7px;
            padding: 0 10px;
            background: white;
            color: #1A4B8C;
            cursor: pointer;
            font-size: 9px;
            font-weight: 700;
        }

        .availability-warning {
            margin-top: 14px;
            padding: 11px;
            border: 1px solid #FDE68A;
            border-radius: 9px;
            background: #FFFBEB;
            color: #92400E;
            font-size: 10px;
            line-height: 1.5;
        }

        .mis-empty {
            text-align: center;
            padding: 55px 20px;
            color: #64748B;
            background: white;
            border: 1px solid #DDE3EE;
            border-radius: 12px;
        }

        .mis-empty strong {
            display: block;
            color: #1A2C4E;
            font-size: 14px;
            margin-bottom: 6px;
        }

        .pagination {
            margin-top: 20px;
        }

        @media (max-width: 1050px) {
            .filter-grid {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }

            .mis-stats {
                grid-template-columns:
                    repeat(2, 1fr);
            }
        }

        @media (max-width: 700px) {
            .filter-grid,
            .mis-stats,
            .card-grid {
                grid-template-columns: 1fr;
            }

            .mis-card-top,
            .mis-intro {
                flex-direction: column;
            }

            .remove-button {
                margin-left: 0;
            }
        }
    </style>

    <div class="itsva-mis">

        <div class="mis-intro">

            <div>
                <h2>Mis Convocatorias</h2>

                <p>
                    Administra las convocatorias que has guardado
                    y da seguimiento a tu participación.
                </p>
            </div>

            <a
                href="{{ route(
                    'filament.docente.pages.buscar-convocatorias'
                ) }}"
                class="action-button primary-button"
            >
                + Buscar convocatorias
            </a>

        </div>

        <div class="mis-stats">

            <div class="mis-stat">
                <div class="mis-stat-value">
                    {{ $total }}
                </div>

                <div class="mis-stat-label">
                    En Mis Convocatorias
                </div>
            </div>

            <div class="mis-stat">
                <div class="mis-stat-value">
                    {{ $favoritas }}
                </div>

                <div class="mis-stat-label">
                    Favoritas
                </div>
            </div>

            <div class="mis-stat">
                <div class="mis-stat-value">
                    {{ $preparando }}
                </div>

                <div class="mis-stat-label">
                    Preparando propuesta
                </div>
            </div>

            <div class="mis-stat">
                <div class="mis-stat-value">
                    {{ $noDisponibles }}
                </div>

                <div class="mis-stat-label">
                    Pendientes / no disponibles
                </div>
            </div>

        </div>

        <div class="mis-toolbar">

            <div class="filter-grid">

                <div class="filter-group">

                    <label>Buscar</label>

                    <input
                        type="search"
                        wire:model.live.debounce.400ms="buscar"
                        class="mis-input"
                        placeholder="Título, organismo o categoría..."
                    >

                </div>

                <div class="filter-group">

                    <label>Seguimiento</label>

                    <select
                        wire:model.live="estado"
                        class="mis-select"
                    >
                        <option value="">
                            Todos
                        </option>

                        <option value="GUARDADA">
                            Guardada
                        </option>

                        <option value="REVISANDO">
                            Revisando
                        </option>

                        <option value="PREPARANDO_PROPUESTA">
                            Preparando propuesta
                        </option>

                        <option value="ENVIADA">
                            Enviada
                        </option>

                        <option value="ACEPTADA">
                            Aceptada
                        </option>

                        <option value="RECHAZADA">
                            Rechazada
                        </option>

                        <option value="FINALIZADA">
                            Finalizada
                        </option>
                    </select>

                </div>

                <div class="filter-group">

                    <label>Prioridad</label>

                    <select
                        wire:model.live="prioridad"
                        class="mis-select"
                    >
                        <option value="">
                            Todas
                        </option>

                        <option value="ALTA">
                            Alta
                        </option>

                        <option value="MEDIA">
                            Media
                        </option>

                        <option value="BAJA">
                            Baja
                        </option>
                    </select>

                </div>

                <div class="filter-group">

                    <label>Disponibilidad</label>

                    <select
                        wire:model.live="disponibilidad"
                        class="mis-select"
                    >
                        <option value="">
                            Todas
                        </option>

                        <option value="disponible">
                            Publicadas / activas
                        </option>

                        <option value="no_disponible">
                            No disponibles
                        </option>
                    </select>

                </div>

                <button
                    type="button"
                    wire:click="limpiarFiltros"
                    class="clear-button"
                >
                    Limpiar
                </button>

            </div>

        </div>

        <div class="mis-list">

            @forelse ($seguimientos as $seguimiento)

                @php
                    $convocatoria =
                        $seguimiento->convocatoria;

                    $disponible =
                        $convocatoria
                        && in_array(
                            $convocatoria->estado,
                            ['PUBLICADA', 'ACTIVA'],
                            true
                        );

                    $tienePropuesta = in_array(
                        (int) $seguimiento->convocatoria_id,
                        $convocatoriasConPropuesta,
                        true
                    );

                    $estadoConvocatoria = match (
                        $convocatoria?->estado
                    ) {
                        'PUBLICADA' => 'Publicada',
                        'ACTIVA' => 'Activa',
                        'PENDIENTE_REVISION' =>
                            'Pendiente de revisión',
                        'REQUIERE_CORRECCIONES' =>
                            'Requiere correcciones',
                        'CERRADA' => 'Cerrada',
                        'DESCARTADA' => 'Descartada',
                        'ARCHIVADA' => 'Archivada',
                        'BORRADOR' => 'Borrador',
                        default =>
                            $convocatoria?->estado
                            ?? 'Sin estado',
                    };

                    $estadoSeguimiento = match (
                        $seguimiento->estado_seguimiento
                    ) {
                        'GUARDADA' => 'Guardada',
                        'REVISANDO' => 'Revisando',
                        'PREPARANDO_PROPUESTA' =>
                            'Preparando propuesta',
                        'ENVIADA' => 'Enviada',
                        'ACEPTADA' => 'Aceptada',
                        'RECHAZADA' => 'Rechazada',
                        'FINALIZADA' => 'Finalizada',
                        default =>
                            $seguimiento
                                ->estado_seguimiento,
                    };
                @endphp

                <article
                    class="
                        mis-card
                        {{ $disponible
                            ? ''
                            : 'unavailable' }}
                    "
                    wire:key="seguimiento-{{ $seguimiento->id }}"
                >

                    <div class="mis-card-top">

                        <div>

                            <div class="mis-title">
                                {{ $convocatoria?->titulo
                                    ?? 'Convocatoria no disponible' }}
                            </div>

                            <div class="mis-sub">
                                {{ $convocatoria
                                    ?->organismo
                                    ?->nombre
                                    ?? 'Sin organismo' }}

                                @if (
                                    $convocatoria
                                    ?->categoria
                                )
                                    ·
                                    {{ $convocatoria
                                        ->categoria
                                        ->nombre }}
                                @endif
                            </div>

                            <div class="badges">

                                <span
                                    class="
                                        status
                                        {{
                                            $disponible
                                                ? 'status-green'
                                                : 'status-warning'
                                        }}
                                    "
                                >
                                    {{ $estadoConvocatoria }}
                                </span>

                                <span class="status">
                                    {{ $estadoSeguimiento }}
                                </span>

                                @if (
                                    $seguimiento->es_favorita
                                )

                                    <span
                                        class="
                                            status
                                            status-warning
                                        "
                                    >
                                        ★ Favorita
                                    </span>

                                @endif

                                @if ($tienePropuesta)

                                    <span
                                        class="
                                            status
                                            status-purple
                                        "
                                    >
                                        Tiene propuesta
                                    </span>

                                @endif

                            </div>

                        </div>

                    </div>

                    @unless ($disponible)

                        <div class="availability-warning">

                            Esta convocatoria permanece en tu
                            historial, pero actualmente está en
                            estado
                            <strong>
                                {{ $estadoConvocatoria }}
                            </strong>.

                            El detalle público y la generación de
                            propuestas estarán disponibles cuando
                            la convocatoria sea publicada o
                            activada.

                        </div>

                    @endunless

                    <div class="card-grid">

                        <div>

                            <label class="field-label">
                                Prioridad
                            </label>

                            <select
                                class="priority-select"
                                wire:change="
                                    actualizarPrioridad(
                                        {{ $seguimiento->id }},
                                        $event.target.value
                                    )
                                "
                            >
                                <option
                                    value="ALTA"
                                    @selected(
                                        $seguimiento->prioridad
                                        === 'ALTA'
                                    )
                                >
                                    Alta
                                </option>

                                <option
                                    value="MEDIA"
                                    @selected(
                                        $seguimiento->prioridad
                                        === 'MEDIA'
                                    )
                                >
                                    Media
                                </option>

                                <option
                                    value="BAJA"
                                    @selected(
                                        $seguimiento->prioridad
                                        === 'BAJA'
                                    )
                                >
                                    Baja
                                </option>
                            </select>

                        </div>

                        <div>

                            <label class="field-label">
                                Notas personales
                            </label>

                            <textarea
                                class="notes-textarea"
                                wire:model.defer="
                                    notas.{{ $seguimiento->id }}
                                "
                                placeholder="Escribe notas sobre esta convocatoria..."
                            ></textarea>

                            <div class="notes-actions">

                                <button
                                    type="button"
                                    class="save-note"
                                    wire:click="
                                        guardarNotas(
                                            {{ $seguimiento->id }}
                                        )
                                    "
                                >
                                    Guardar notas
                                </button>

                            </div>

                        </div>

                    </div>

                    <div class="mis-actions">

                        @if ($disponible)

                            <button
                                type="button"
                                wire:click="
                                    verDetalle(
                                        {{ $seguimiento->id }}
                                    )
                                "
                                class="
                                    action-button
                                    secondary-button
                                "
                            >
                                Ver detalle
                            </button>

                            <button
                                type="button"
                                wire:click="
                                    irAPropuesta(
                                        {{ $seguimiento->id }}
                                    )
                                "
                                class="
                                    action-button
                                    primary-button
                                "
                            >
                                {{ $tienePropuesta
                                    ? 'Continuar propuesta'
                                    : 'Generar propuesta' }}
                            </button>

                        @else

                            <button
                                type="button"
                                disabled
                                class="
                                    action-button
                                    disabled-button
                                "
                            >
                                Detalle no disponible
                            </button>

                            <button
                                type="button"
                                disabled
                                class="
                                    action-button
                                    disabled-button
                                "
                            >
                                Propuesta no disponible
                            </button>

                        @endif

                        <button
                            type="button"
                            wire:click="
                                cambiarFavorita(
                                    {{ $seguimiento->id }}
                                )
                            "
                            class="
                                action-button
                                favorite-button
                            "
                        >
                            {{ $seguimiento->es_favorita
                                ? '★ Quitar favorita'
                                : '☆ Marcar favorita' }}
                        </button>

                        <button
                            type="button"
                            wire:click="
                                quitar(
                                    {{ $seguimiento->id }}
                                )
                            "
                            wire:confirm="
                                ¿Seguro que deseas quitar esta convocatoria de Mis Convocatorias?
                            "
                            class="
                                action-button
                                remove-button
                            "
                        >
                            Quitar
                        </button>

                    </div>

                </article>

            @empty

                <div class="mis-empty">

                    <strong>
                        No hay convocatorias para mostrar
                    </strong>

                    @if (
                        trim($this->buscar) !== ''
                        || $this->estado !== ''
                        || $this->prioridad !== ''
                        || $this->disponibilidad !== ''
                    )

                        No existen registros que coincidan
                        con los filtros seleccionados.

                        <br><br>

                        <button
                            type="button"
                            wire:click="limpiarFiltros"
                            class="clear-button"
                        >
                            Limpiar filtros
                        </button>

                    @else

                        Todavía no tienes convocatorias
                        guardadas.

                        <br><br>

                        <a
                            href="{{ route(
                                'filament.docente.pages.buscar-convocatorias'
                            ) }}"
                            class="
                                action-button
                                primary-button
                            "
                        >
                            Buscar convocatorias
                        </a>

                    @endif

                </div>

            @endforelse

        </div>

        @if ($seguimientos->hasPages())

            <div class="pagination">
                {{ $seguimientos->links() }}
            </div>

        @endif

    </div>

</x-filament-panels::page>
