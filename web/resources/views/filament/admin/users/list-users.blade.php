<x-filament-panels::page>
    <style>
        .admin-users {
            --text: #0F1827;
            --muted: #64748B;
            --primary: #059669;
            --primary-dark: #047857;
            --primary-soft: #D1FAE5;
            --border: #DDE3EE;

            color: var(--text);
        }

        .admin-users * {
            box-sizing: border-box;
        }

        .users-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 22px;
        }

        .users-title {
            margin: 0;
            font-size: 26px;
            font-weight: 850;
        }

        .users-subtitle {
            margin-top: 7px;
            color: var(--muted);
            font-size: 14px;
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 22px;
        }

        .stat-card {
            padding: 18px;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: white;
            box-shadow: 0 1px 2px rgba(15, 24, 39, .03);
        }

        .stat-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 13px;
            font-size: 11px;
            font-weight: 900;
        }

        .icon-all {
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

        .table-card {
            padding: 20px;
            border: 1px solid var(--border);
            border-radius: 16px;
            background: white;
            box-shadow: 0 1px 2px rgba(15, 24, 39, .03);
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

        .info-box {
            display: flex;
            gap: 11px;
            margin-bottom: 18px;
            padding: 13px 15px;
            border: 1px solid #A7F3D0;
            border-radius: 11px;
            background: #ECFDF5;
            color: #047857;
            font-size: 10px;
            line-height: 1.5;
        }

        .info-icon {
            width: 23px;
            height: 23px;
            flex: 0 0 23px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #D1FAE5;
            font-weight: 900;
        }

        @media (max-width: 900px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 600px) {
            .users-header {
                flex-direction: column;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .table-card {
                padding: 14px;
            }
        }
    </style>

    <div class="admin-users">

        <div class="users-header">
            <div>
                <h1 class="users-title">
                    Gestión de Usuarios
                </h1>

                <div class="users-subtitle">
                    Administra las cuentas, roles, departamentos y estado de acceso de los usuarios.
                </div>
            </div>

            <div class="role-badge">
                <span class="role-dot"></span>
                Administrador
            </div>
        </div>

        <div class="stats-grid">

            <div class="stat-card">
                <div class="stat-icon icon-all">
                    U
                </div>

                <div class="stat-value">
                    32
                </div>

                <div class="stat-label">
                    Usuarios registrados
                </div>

                <div class="stat-help">
                    Total de cuentas
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon icon-docente">
                    D
                </div>

                <div class="stat-value">
                    24
                </div>

                <div class="stat-label">
                    Docentes
                </div>

                <div class="stat-help">
                    Usuarios con rol Docente
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon icon-directivo">
                    DI
                </div>

                <div class="stat-value">
                    5
                </div>

                <div class="stat-label">
                    Directivos
                </div>

                <div class="stat-help">
                    Usuarios con rol Directivo
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon icon-admin">
                    A
                </div>

                <div class="stat-value">
                    3
                </div>

                <div class="stat-label">
                    Administradores
                </div>

                <div class="stat-help">
                    Usuarios administradores
                </div>
            </div>

        </div>

        <div class="info-box">
            <div class="info-icon">
                i
            </div>

            <div>
                En esta etapa las tarjetas superiores contienen datos visuales de muestra.
                El listado inferior continúa utilizando los usuarios registrados actualmente.
            </div>
        </div>

        <section class="table-card">

            <div class="table-heading">
                <div class="table-title">
                    Usuarios
                </div>

                <div class="table-description">
                    Busca, filtra y administra las cuentas registradas en la plataforma.
                </div>
            </div>

            {{ $this->table }}

        </section>

    </div>
</x-filament-panels::page>
