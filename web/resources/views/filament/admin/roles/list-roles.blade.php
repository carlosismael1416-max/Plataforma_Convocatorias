<x-filament-panels::page>
    <style>
        .admin-roles {
            --text: #0F1827;
            --muted: #64748B;
            --primary: #059669;
            --primary-dark: #047857;
            --primary-soft: #D1FAE5;
            --border: #DDE3EE;
            color: var(--text);
        }

        .admin-roles * {
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
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 22px;
        }

        .stat {
            padding: 18px;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: white;
        }

        .stat-icon {
            width: 38px;
            height: 38px;
            margin-bottom: 12px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-soft);
            color: var(--primary-dark);
            font-size: 11px;
            font-weight: 900;
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

        .section-title {
            margin-bottom: 13px;
            font-size: 15px;
            font-weight: 850;
        }

        .role-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 22px;
        }

        .role-card {
            padding: 19px;
            border: 1px solid var(--border);
            border-radius: 15px;
            background: white;
        }

        .role-card-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
        }

        .role-icon {
            width: 42px;
            height: 42px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 900;
        }

        .docente {
            background: #DBEAFE;
            color: #1D4ED8;
        }

        .directivo {
            background: #F3E8FF;
            color: #7C3AED;
        }

        .admin {
            background: #D1FAE5;
            color: #047857;
        }

        .status {
            padding: 5px 8px;
            border-radius: 999px;
            background: #D1FAE5;
            color: #047857;
            font-size: 9px;
            font-weight: 850;
        }

        .role-name {
            margin-top: 14px;
            color: #1E293B;
            font-size: 15px;
            font-weight: 900;
        }

        .role-description {
            min-height: 45px;
            margin-top: 5px;
            color: var(--muted);
            font-size: 10px;
            line-height: 1.5;
        }

        .permissions-title {
            margin-top: 17px;
            padding-top: 15px;
            border-top: 1px solid #EEF2F7;
            color: #64748B;
            font-size: 9px;
            font-weight: 850;
            text-transform: uppercase;
        }

        .permissions {
            display: flex;
            flex-direction: column;
            gap: 7px;
            margin-top: 10px;
        }

        .permission {
            display: flex;
            gap: 8px;
            align-items: flex-start;
            color: #475569;
            font-size: 10px;
        }

        .check {
            width: 18px;
            height: 18px;
            flex: 0 0 18px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #D1FAE5;
            color: #047857;
            font-size: 8px;
            font-weight: 900;
        }

        .more {
            margin-top: 11px;
            color: var(--primary);
            font-size: 9px;
            font-weight: 850;
        }

        .notice {
            display: flex;
            gap: 11px;
            padding: 14px;
            margin-bottom: 22px;
            border: 1px solid #A7F3D0;
            border-radius: 11px;
            background: #ECFDF5;
            color: #047857;
            font-size: 10px;
            line-height: 1.55;
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

        .table-header {
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
            .stats,
            .role-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 650px) {
            .page-header {
                flex-direction: column;
            }

            .table-card {
                padding: 14px;
            }
        }
    </style>

    <div class="admin-roles">

        <div class="page-header">

            <div>
                <h1 class="page-title">
                    Roles y Permisos
                </h1>

                <div class="page-subtitle">
                    Administra los perfiles de acceso y las funciones disponibles para cada tipo de usuario.
                </div>
            </div>

            <div class="role-badge">
                <span class="role-dot"></span>
                Administrador
            </div>

        </div>

        <div class="stats">

            <div class="stat">
                <div class="stat-icon">R</div>

                <div class="stat-value">3</div>

                <div class="stat-label">
                    Roles principales
                </div>

                <div class="stat-help">
                    Docente, Directivo y Administrador
                </div>
            </div>

            <div class="stat">
                <div class="stat-icon">U</div>

                <div class="stat-value">32</div>

                <div class="stat-label">
                    Usuarios asignados
                </div>

                <div class="stat-help">
                    Cuentas asociadas a roles
                </div>
            </div>

            <div class="stat">
                <div class="stat-icon">P</div>

                <div class="stat-value">18</div>

                <div class="stat-label">
                    Permisos definidos
                </div>

                <div class="stat-help">
                    Funciones consideradas en la plataforma
                </div>
            </div>

        </div>

        <div class="section-title">
            Permisos principales por rol
        </div>

        <div class="role-grid">

            <article class="role-card">

                <div class="role-card-top">
                    <div class="role-icon docente">D</div>
                    <span class="status">Activo</span>
                </div>

                <div class="role-name">
                    Docente
                </div>

                <div class="role-description">
                    Consulta oportunidades de financiamiento y desarrolla propuestas relacionadas con convocatorias.
                </div>

                <div class="permissions-title">
                    Accesos principales
                </div>

                <div class="permissions">

                    <div class="permission">
                        <span class="check">✓</span>
                        Buscar convocatorias
                    </div>

                    <div class="permission">
                        <span class="check">✓</span>
                        Administrar Mis Convocatorias
                    </div>

                    <div class="permission">
                        <span class="check">✓</span>
                        Consultar calendario
                    </div>

                    <div class="permission">
                        <span class="check">✓</span>
                        Generar y editar propuestas
                    </div>

                    <div class="permission">
                        <span class="check">✓</span>
                        Exportar propuestas
                    </div>

                </div>

                <div class="more">
                    Permisos del rol Docente
                </div>

            </article>

            <article class="role-card">

                <div class="role-card-top">
                    <div class="role-icon directivo">DI</div>
                    <span class="status">Activo</span>
                </div>

                <div class="role-name">
                    Directivo
                </div>

                <div class="role-description">
                    Consulta convocatorias y supervisa el proceso institucional de revisión.
                </div>

                <div class="permissions-title">
                    Accesos principales
                </div>

                <div class="permissions">

                    <div class="permission">
                        <span class="check">✓</span>
                        Buscar convocatorias
                    </div>

                    <div class="permission">
                        <span class="check">✓</span>
                        Revisar convocatorias
                    </div>

                    <div class="permission">
                        <span class="check">✓</span>
                        Aprobar o rechazar registros
                    </div>

                    <div class="permission">
                        <span class="check">✓</span>
                        Consultar estadísticas
                    </div>

                    <div class="permission">
                        <span class="check">✓</span>
                        Generar reportes
                    </div>

                </div>

                <div class="more">
                    Permisos del rol Directivo
                </div>

            </article>

            <article class="role-card">

                <div class="role-card-top">
                    <div class="role-icon admin">A</div>
                    <span class="status">Activo</span>
                </div>

                <div class="role-name">
                    Administrador
                </div>

                <div class="role-description">
                    Administra usuarios, configuración, fuentes web y funcionamiento general de la plataforma.
                </div>

                <div class="permissions-title">
                    Accesos principales
                </div>

                <div class="permissions">

                    <div class="permission">
                        <span class="check">✓</span>
                        Gestionar usuarios
                    </div>

                    <div class="permission">
                        <span class="check">✓</span>
                        Gestionar roles y permisos
                    </div>

                    <div class="permission">
                        <span class="check">✓</span>
                        Gestionar fuentes web
                    </div>

                    <div class="permission">
                        <span class="check">✓</span>
                        Supervisar motor de extracción
                    </div>

                    <div class="permission">
                        <span class="check">✓</span>
                        Gestionar convocatorias
                    </div>

                </div>

                <div class="more">
                    Permisos del rol Administrador
                </div>

            </article>

        </div>

        <div class="notice">

            <div class="notice-icon">
                i
            </div>

            <div>
                Los permisos mostrados arriba corresponden al diseño visual de los tres
                perfiles principales. La asignación real de permisos mediante
                <strong>rol_permisos</strong> se conectará en la fase de funcionalidad.
            </div>

        </div>

        <section class="table-card">

            <div class="table-header">

                <div class="table-title">
                    Roles registrados
                </div>

                <div class="table-description">
                    Consulta los roles existentes, su estado y sus datos administrativos.
                </div>

            </div>

            {{ $this->table }}

        </section>

    </div>
</x-filament-panels::page>
