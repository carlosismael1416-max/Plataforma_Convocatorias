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
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 20px;
        }

        .stat {
            padding: 18px;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: white;
            box-shadow: 0 1px 2px rgba(15, 24, 39, .03);
        }

        .stat-icon {
            width: 38px;
            height: 38px;
            margin-bottom: 12px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
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
            font-size: 9px;
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
            font-size: 9px;
            font-weight: 900;
        }

        .workflow-title {
            color: #334155;
            font-size: 10px;
            font-weight: 850;
        }

        .workflow-text {
            margin-top: 4px;
            color: #94A3B8;
            font-size: 9px;
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
            font-size: 9px;
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

            <div class="stat">
                <div class="stat-icon green">C</div>
                <div class="stat-value">{{ $totalConvocatorias }}</div>
                <div class="stat-label">Convocatorias</div>
                <div class="stat-help">
                    {{ $totalScraping }} del bot ·
                    {{ $totalManuales }} manuales
                </div>
            </div>

            <div class="stat">
                <div class="stat-icon yellow">R</div>
                <div class="stat-value">{{ $pendientesRevision }}</div>
                <div class="stat-label">Pendientes de revisión</div>
                <div class="stat-help">Esperando validación</div>
            </div>

            <div class="stat">
                <div class="stat-icon blue">✓</div>
                <div class="stat-value">{{ $publicadasActivas }}</div>
                <div class="stat-label">Publicadas / activas</div>
                <div class="stat-help">Disponibles para usuarios</div>
            </div>

            <div class="stat">
                <div class="stat-icon gray">—</div>
                <div class="stat-value">{{ $cerradasArchivadas }}</div>
                <div class="stat-label">Cerradas / archivadas</div>
                <div class="stat-help">Fuera de vigencia</div>
            </div>

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
                    Revisión administrativa
                </div>

                <div class="workflow-text">
                    Se verifican datos, documentos, fechas, requisitos y consistencia del registro.
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
                    Una convocatoria validada puede quedar disponible para Docentes y Directivos.
                </div>
            </div>

        </section>

        <div class="notice">

            <div class="notice-icon">
                i
            </div>

            <div>
                Los indicadores y la tabla utilizan directamente los registros actuales
                de PostgreSQL. Las acciones de aprobación y correcciones permanecen reservadas
                para la fase de integración de flujos.
            </div>

        </div>

        <section class="table-card">

            <div class="table-heading">

                <div class="table-title">
                    Convocatorias registradas
                </div>

                <div class="table-description">
                    Busca y filtra por estado, categoría, organismo, fuente u origen.
                </div>

            </div>

            {{ $this->table }}

        </section>

    </div>
</x-filament-panels::page>
