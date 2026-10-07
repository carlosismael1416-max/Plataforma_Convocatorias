<x-filament-panels::page>

    <style>
        .users-summary {
            display: grid;
            grid-template-columns:
                repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .user-summary-card {
            min-height: 202px;
            padding: 23px;

            background: #FFFFFF;

            border:
                1px solid
                #DDE3EE;

            border-radius: 17px;

            color: #0F1827;

            box-shadow:
                0 1px 2px
                rgba(15, 24, 39, .02);
        }

        .user-summary-icon {
            width: 47px;
            height: 47px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 12px;

            font-size: 13px;
            font-weight: 900;
        }

        .icon-users {
            background: #D1FAE5;
            color: #047857;
        }

        .icon-docente {
            background: #DBEAFE;
            color: #1D4ED8;
        }

        .icon-directivo {
            background: #F3E8FF;
            color: #7C3AED;
        }

        .icon-admin {
            background: #FEF3C7;
            color: #B45309;
        }

        .user-summary-number {
            margin-top: 25px;

            color: #020617;

            font-size: 32px;
            line-height: 1;
            font-weight: 900;
        }

        .user-summary-title {
            margin-top: 12px;

            color: #334155;

            font-size: 14px;
            font-weight: 850;
        }

        .user-summary-help {
            margin-top: 8px;

            color: #94A3B8;

            font-size: 11px;
        }

        .users-table-section {
            margin-top: 4px;
        }

        @media (max-width: 1100px) {
            .users-summary {
                grid-template-columns:
                    repeat(
                        2,
                        minmax(0, 1fr)
                    );
            }
        }

        @media (max-width: 650px) {
            .users-summary {
                grid-template-columns: 1fr;
            }

            .user-summary-card {
                min-height: 170px;
            }
        }
    </style>


    <div class="users-summary">

        <div class="user-summary-card">

            <div
                class="
                    user-summary-icon
                    icon-users
                "
            >
                U
            </div>

            <div class="user-summary-number">
                {{ $totalUsuarios }}
            </div>

            <div class="user-summary-title">
                Usuarios registrados
            </div>

            <div class="user-summary-help">
                Total de cuentas
            </div>

        </div>


        <div class="user-summary-card">

            <div
                class="
                    user-summary-icon
                    icon-docente
                "
            >
                D
            </div>

            <div class="user-summary-number">
                {{ $totalDocentes }}
            </div>

            <div class="user-summary-title">
                Docentes
            </div>

            <div class="user-summary-help">
                Usuarios con rol Docente
            </div>

        </div>


        <div class="user-summary-card">

            <div
                class="
                    user-summary-icon
                    icon-directivo
                "
            >
                DI
            </div>

            <div class="user-summary-number">
                {{ $totalDirectivos }}
            </div>

            <div class="user-summary-title">
                Directivos
            </div>

            <div class="user-summary-help">
                Usuarios con rol Directivo
            </div>

        </div>


        <div class="user-summary-card">

            <div
                class="
                    user-summary-icon
                    icon-admin
                "
            >
                A
            </div>

            <div class="user-summary-number">
                {{ $totalAdministradores }}
            </div>

            <div class="user-summary-title">
                Administradores
            </div>

            <div class="user-summary-help">
                Usuarios administradores
            </div>

        </div>

    </div>


    <div class="users-table-section">
        {{ $this->table }}
    </div>

</x-filament-panels::page>
