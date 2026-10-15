<x-filament-panels::page>

    <style>
        .fuentes-page {
            --text: #0F1827;
            --muted: #64748B;
            --primary: #059669;
            --border: #DDE3EE;

            color: var(--text);
        }

        .fuentes-page * {
            box-sizing: border-box;
        }

        .fuentes-summary {
            display: grid;
            grid-template-columns:
                repeat(
                    4,
                    minmax(0, 1fr)
                );
            gap: 16px;
            margin-bottom: 24px;
        }

        .fuente-stat {

            padding: 22px;

            background: #FFFFFF;

            border:
                1px solid
                var(--border);

            border-radius: 17px;

            box-shadow:
                0 1px 2px
                rgba(15, 24, 39, .03);
        }

        .stat-icon {
            width: 46px;
            height: 46px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 12px;

            font-size: 12px;
            font-weight: 900;
        }

        .icon-total {
            background: #D1FAE5;
            color: #047857;
        }

        .icon-active {
            background: #DCFCE7;
            color: #15803D;
        }

        .icon-inactive {
            background: #F1F5F9;
            color: #64748B;
        }

        .icon-js {
            background: #FEF3C7;
            color: #B45309;
        }

        .stat-value {
            margin-top: 24px;

            color: #020617;

            font-size: 31px;
            line-height: 1;
            font-weight: 900;
        }

        .stat-label {
            margin-top: 11px;

            color: #334155;

            font-size: 14px;
            font-weight: 850;
        }

        .stat-help {
            margin-top: 7px;

            color: #94A3B8;

            font-size: 11px;
        }

        .fuentes-table {
            margin-top: 4px;
        }

        @media (
            max-width: 1100px
        ) {
            .fuentes-summary {
                grid-template-columns:
                    repeat(
                        2,
                        minmax(0, 1fr)
                    );
            }
        }

        @media (
            max-width: 650px
        ) {
            .fuentes-summary {
                grid-template-columns:
                    1fr;
            }
        }
    </style>


    <div class="fuentes-page">

        <div class="fuentes-summary">

            <div class="stat fuente-stat">

                <div
                    class="
                        stat-icon
                        icon-total
                    "
                >{!! \App\Filament\Apariencia\Iconos::svg('globo', 20) !!}</div>

                <div class="stat-value">
                    {{ $totalFuentes }}
                </div>

                <div class="stat-label">
                    Fuentes registradas
                </div>

                <div class="stat-help">
                    Total configurado
                    en el sistema
                </div>

            </div>


            <div class="stat fuente-stat">

                <div
                    class="
                        stat-icon
                        icon-active
                    "
                >{!! \App\Filament\Apariencia\Iconos::svg('aprobado', 20) !!}</div>

                <div class="stat-value">
                    {{ $fuentesActivas }}
                </div>

                <div class="stat-label">
                    Fuentes activas
                </div>

                <div class="stat-help">
                    Habilitadas para
                    extracción
                </div>

            </div>


            <div class="stat fuente-stat">

                <div
                    class="
                        stat-icon
                        icon-inactive
                    "
                >{!! \App\Filament\Apariencia\Iconos::svg('rechazado', 20) !!}</div>

                <div class="stat-value">
                    {{ $fuentesInactivas }}
                </div>

                <div class="stat-label">
                    Fuentes inactivas
                </div>

                <div class="stat-help">
                    Actualmente
                    deshabilitadas
                </div>

            </div>


            <div class="stat fuente-stat">

                <div
                    class="
                        stat-icon
                        icon-js
                    "
                >{!! \App\Filament\Apariencia\Iconos::svg('cpu', 20) !!}</div>

                <div class="stat-value">
                    {{ $fuentesJavascript }}
                </div>

                <div class="stat-label">
                    Requieren JavaScript
                </div>

                <div class="stat-help">
                    Fuentes con carga
                    dinámica
                </div>

            </div>

        </div>


        <div class="fuentes-table">
            {{ $this->table }}
        </div>

    </div>

</x-filament-panels::page>
