<x-filament-panels::page>
    <style>
        .admin-calls {
            --text: #0F1827;
            --muted: #64748B;
            --primary: #059669;
            --primary-dark: #047857;
            --primary-soft: #D1FAE5;
            --border: #DDE3EE;
            --warning: #D97706;
            --danger: #DC2626;
            color: var(--text);
        }

        .admin-calls * {
            box-sizing: border-box;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 22px;
        }

        .page-title {
            margin: 0;
            font-size: 26px;
            font-weight: 850;
        }

        .page-subtitle {
            margin-top: 7px;
            color: var(--muted);
            font-size: 14px;
            max-width: 760px;
        }

        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 11px;
            border-radius: 999px;
            background: var(--primary-soft);
            color: var(--primary-dark);
            font-size: 12px;
            font-weight: 850;
        }

        .role-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--primary);
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 20px;
        }

        .stat {
            display: block;
            width: 100%;
            text-align: left;
            font: inherit;
            cursor: pointer;
            transition: box-shadow .15s, transform .15s, border-color .15s;
            padding: 18px;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: white;
            box-shadow: 0 1px 2px rgba(15, 24, 39, .03);
        }

        .stat:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(15, 24, 39, .08);
        }

        .stat.activa {
            border-color: #059669;
            box-shadow: 0 0 0 2px rgba(5, 150, 105, .18);
        }

        .stat-icon {
            width: 38px;
            height: 38px;
            margin-bottom: 12px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 900;
        }

        .green {
            background: #D1FAE5;
            color: #047857;
        }

        .yellow {
            background: #FEF3C7;
            color: #B45309;
        }

        .blue {
            background: #DBEAFE;
            color: #1D4ED8;
        }

        .gray {
            background: #F1F5F9;
            color: #475569;
        }

        .red {
            background: #FEE2E2;
            color: #B91C1C;
        }

        .stat-value {
            font-size: 25px;
            font-weight: 900;
        }

        .stat-label {
            margin-top: 3px;
            color: #475569;
            font-size: 11px;
            font-weight: 800;
        }

        .stat-help {
            margin-top: 4px;
            color: #94A3B8;
            font-size: 12px;
        }

        .workflow {
            display: grid;
            grid-template-columns:
                minmax(0, 1fr)
                24px
                minmax(0, 1fr)
                24px
                minmax(0, 1fr);
            gap: 9px;
            align-items: center;
            margin-bottom: 20px;
            padding: 18px;
            border: 1px solid var(--border);
            border-radius: 15px;
            background: white;
        }

        .workflow-step {
            padding: 13px;
            border: 1px solid #E7ECF3;
            border-radius: 10px;
            background: #FBFCFE;
        }

        .workflow-number {
            width: 24px;
            height: 24px;
            margin-bottom: 9px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-soft);
            color: var(--primary-dark);
            font-size: 12px;
            font-weight: 900;
        }

        .workflow-title {
            color: #334155;
            font-size: 12px;
            font-weight: 850;
        }

        .workflow-text {
            margin-top: 4px;
            color: #94A3B8;
            font-size: 12px;
            line-height: 1.45;
        }

        .workflow-arrow {
            color: #94A3B8;
            font-size: 18px;
            text-align: center;
        }

        .notice {
            display: flex;
            gap: 11px;
            margin-bottom: 20px;
            padding: 13px 15px;
            border: 1px solid #A7F3D0;
            border-radius: 11px;
            background: #ECFDF5;
            color: #047857;
            font-size: 12px;
            line-height: 1.5;
        }

        .notice-icon {
            width: 24px;
            height: 24px;
            flex: 0 0 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #D1FAE5;
            font-weight: 900;
        }

        .table-card {
            padding: 20px;
            border: 1px solid var(--border);
            border-radius: 16px;
            background: white;
        }

        .table-heading {
            margin-bottom: 18px;
        }

        .table-title {
            font-size: 15px;
            font-weight: 850;
        }

        .table-description {
            margin-top: 4px;
            color: var(--muted);
            font-size: 11px;
        }

        @media (max-width: 1000px) {
            .stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .workflow {
                grid-template-columns: 1fr;
            }

            .workflow-arrow {
                transform: rotate(90deg);
            }
        }

        @media (max-width: 600px) {
            .page-header {
                flex-direction: column;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .table-card {
                padding: 14px;
            }
        }
    
        /* ADMIN-CONVOCATORIAS-SCROLL */

        /*
         * La tabla conserva una altura máxima.
         * Si existen muchos registros, el desplazamiento
         * vertical ocurre dentro de ella y no en toda
         * la página.
         */
        .table-card .fi-ta-content {
            position: relative;
            max-height: 68vh;
            overflow-x: auto !important;
            overflow-y: auto !important;
            scrollbar-gutter: stable;
            overscroll-behavior: contain;
        }

        /*
         * Mantener visible el encabezado de columnas
         * mientras se desplaza verticalmente la tabla.
         */
        .table-card .fi-ta-content thead th {
            position: sticky;
            top: 0;
            z-index: 8;
            background: white;
        }

        /*
         * Evita que el contenedor general crezca
         * innecesariamente por el ancho de la tabla.
         */
        .table-card {
            min-width: 0;
            overflow: hidden;
        }

        /*
         * En pantallas pequeñas usamos más altura
         * disponible para la tabla.
         */
        @media (max-width: 1000px) {
            .table-card .fi-ta-content {
                max-height: 72vh;
            }
        }

</style>

    <div class="admin-calls">

        <div class="stats">

            <button type="button" wire:click="$set('activeTab', 'todas')" @class(['stat', 'activa' => ($this->activeTab ?? 'todas') === 'todas']) aria-pressed="{{ ($this->activeTab ?? 'todas') === 'todas' ? 'true' : 'false' }}">
                <div class="stat-icon green">{!! \App\Filament\Apariencia\Iconos::svg('libro', 20) !!}</div>
                <div class="stat-value">{{ $totalConvocatorias }}</div>
                <div class="stat-label">Convocatorias</div>
                <div class="stat-help">
                    {{ $totalScraping }} del bot ·
                    {{ $totalManuales }} manuales
                </div>
            </button>

            <button type="button" wire:click="$set('activeTab', 'no_publicadas')" @class(['stat', 'activa' => ($this->activeTab ?? 'todas') === 'no_publicadas']) aria-pressed="{{ ($this->activeTab ?? 'todas') === 'no_publicadas' ? 'true' : 'false' }}">
                <div class="stat-icon yellow">{!! \App\Filament\Apariencia\Iconos::svg('portapapeles', 20) !!}</div>
                <div class="stat-value">{{ $pendientesRevision }}</div>
                <div class="stat-label">No publicadas</div>
                <div class="stat-help">El validador no las aprobó; revísalas</div>
            </button>

            <button type="button" wire:click="$set('activeTab', 'descartadas')" @class(['stat', 'activa' => ($this->activeTab ?? 'todas') === 'descartadas']) aria-pressed="{{ ($this->activeTab ?? 'todas') === 'descartadas' ? 'true' : 'false' }}">
                <div class="stat-icon red">{!! \App\Filament\Apariencia\Iconos::svg('rechazado', 20) !!}</div>
                <div class="stat-value">{{ $descartadas }}</div>
                <div class="stat-label">Descartadas</div>
                <div class="stat-help">Un administrador decidió no publicarlas</div>
            </button>

            <button type="button" wire:click="$set('activeTab', 'vigentes')" @class(['stat', 'activa' => ($this->activeTab ?? 'todas') === 'vigentes']) aria-pressed="{{ ($this->activeTab ?? 'todas') === 'vigentes' ? 'true' : 'false' }}">
                <div class="stat-icon blue">{!! \App\Filament\Apariencia\Iconos::svg('aprobado', 20) !!}</div>
                <div class="stat-value">{{ $publicadasActivas }}</div>
                <div class="stat-label">Vigentes</div>
                <div class="stat-help">Las mismas que ve el docente</div>
            </button>

            <button type="button" wire:click="$set('activeTab', 'cerradas')" @class(['stat', 'activa' => ($this->activeTab ?? 'todas') === 'cerradas']) aria-pressed="{{ ($this->activeTab ?? 'todas') === 'cerradas' ? 'true' : 'false' }}">
                <div class="stat-icon gray">{!! \App\Filament\Apariencia\Iconos::svg('rechazado', 20) !!}</div>
                <div class="stat-value">{{ $cerradasArchivadas }}</div>
                <div class="stat-label">Cerradas / archivadas</div>
                <div class="stat-help">Fuera de vigencia</div>
            </button>

        </div>

        <section class="workflow">

            <div class="workflow-step">
                <div class="workflow-number">1</div>

                <div class="workflow-title">
                    Detección o registro
                </div>

                <div class="workflow-text">
                    La convocatoria llega desde una fuente web o mediante una carga manual.
                </div>
            </div>

            <div class="workflow-arrow">
                →
            </div>

            <div class="workflow-step">
                <div class="workflow-number">2</div>

                <div class="workflow-title">
                    Validación automática
                </div>

                <div class="workflow-text">
                    El validador revisa vigencia, elegibilidad y datos mínimos; las subidas a mano se publican directamente.
                </div>
            </div>

            <div class="workflow-arrow">
                →
            </div>

            <div class="workflow-step">
                <div class="workflow-number">3</div>

                <div class="workflow-title">
                    Publicación
                </div>

                <div class="workflow-text">
                    Queda visible para docentes y directivos, y el motor le da seguimiento por si cambia.
                </div>
            </div>

        </section>

        <div class="notice">

            <div class="notice-icon">
                i
            </div>

            <div>
                Las convocatorias se publican solas cuando pasan el validador. Las que no lo pasan
                quedan en «No publicadas»: ábrelas para validarlas de nuevo y decidir si se publican
                o se descartan.
            </div>

        </div>

        <section class="table-card">

            <div class="table-heading">

                <div class="table-title">
                    {{ [
                        'todas' => 'Todas las convocatorias',
                        'no_publicadas' => 'No publicadas (internas del motor)',
                        'vigentes' => 'Vigentes',
                        'cerradas' => 'Cerradas / archivadas',
                    ][$this->activeTab ?? 'todas'] ?? 'Convocatorias' }}
                </div>

                <div class="table-description">
                    Incluye todas, en cualquier estado. Usa las tarjetas de arriba o los filtros para acotar; toda la fila abre el detalle.
                </div>

            </div>

            {{ $this->table }}

        </section>

    </div>
</x-filament-panels::page>
