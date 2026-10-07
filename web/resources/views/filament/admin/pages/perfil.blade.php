<x-filament-panels::page>
    <style>
        .admin-profile {
            --text: #0F1827;
            --muted: #64748B;
            --primary: #059669;
            --primary-dark: #047857;
            --primary-soft: #D1FAE5;
            --border: #DDE3EE;

            color: var(--text);
        }

        .admin-profile * {
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

        .layout {
            display: grid;
            grid-template-columns: 290px minmax(0, 1fr);
            gap: 22px;
            align-items: start;
        }

        .sidebar,
        .main {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .card {
            border: 1px solid var(--border);
            border-radius: 15px;
            background: white;
            overflow: hidden;
        }

        .profile-card {
            padding: 24px;
            text-align: center;
        }

        .avatar {
            width: 82px;
            height: 82px;
            margin: 0 auto;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-soft);
            color: var(--primary-dark);
            font-size: 25px;
            font-weight: 900;
        }

        .profile-name {
            margin-top: 15px;
            color: #1E293B;
            font-size: 17px;
            font-weight: 900;
        }

        .profile-email {
            margin-top: 5px;
            color: var(--muted);
            font-size: 10px;
        }

        .profile-role {
            display: inline-flex;
            margin-top: 12px;
            padding: 6px 10px;
            border-radius: 999px;
            background: var(--primary-soft);
            color: var(--primary-dark);
            font-size: 9px;
            font-weight: 850;
        }

        .status-box {
            margin-top: 17px;
            padding: 12px;
            border: 1px solid #A7F3D0;
            border-radius: 10px;
            background: #ECFDF5;
            color: #047857;
            font-size: 9px;
            line-height: 1.5;
        }

        .card-header {
            padding: 18px 20px;
            border-bottom: 1px solid var(--border);
        }

        .card-title {
            font-size: 14px;
            font-weight: 850;
        }

        .card-description {
            margin-top: 4px;
            color: var(--muted);
            font-size: 10px;
        }

        .card-content {
            padding: 20px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 13px;
        }

        .info-item {
            padding: 13px;
            border: 1px solid #E7ECF3;
            border-radius: 10px;
            background: #FBFCFE;
        }

        .info-item.full {
            grid-column: 1 / -1;
        }

        .info-label {
            display: block;
            margin-bottom: 5px;
            color: #94A3B8;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .info-value {
            color: #334155;
            font-size: 11px;
            font-weight: 800;
        }

        .permission-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .permission {
            display: flex;
            gap: 10px;
            align-items: center;
            padding: 12px;
            border: 1px solid #E7ECF3;
            border-radius: 10px;
            background: #FBFCFE;
        }

        .permission-icon {
            width: 28px;
            height: 28px;
            flex: 0 0 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-soft);
            color: var(--primary-dark);
            font-size: 9px;
            font-weight: 900;
        }

        .permission-name {
            color: #475569;
            font-size: 10px;
            font-weight: 800;
        }

        .activity {
            display: grid;
            grid-template-columns: 34px minmax(0, 1fr) auto;
            gap: 11px;
            align-items: center;
            padding: 13px 0;
            border-bottom: 1px solid #EEF2F7;
        }

        .activity:last-child {
            border-bottom: 0;
        }

        .activity-icon {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-soft);
            color: var(--primary-dark);
            font-size: 9px;
            font-weight: 900;
        }

        .activity-title {
            color: #334155;
            font-size: 10px;
            font-weight: 800;
        }

        .activity-text {
            margin-top: 3px;
            color: #94A3B8;
            font-size: 9px;
        }

        .activity-date {
            color: #94A3B8;
            font-size: 8px;
            white-space: nowrap;
        }

        .security-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            padding: 13px 0;
            border-bottom: 1px solid #EEF2F7;
        }

        .security-item:last-child {
            border-bottom: 0;
        }

        .security-title {
            color: #334155;
            font-size: 10px;
            font-weight: 850;
        }

        .security-text {
            margin-top: 3px;
            color: #94A3B8;
            font-size: 9px;
        }

        .button {
            min-height: 34px;
            padding: 0 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: white;
            color: var(--primary);
            font-size: 9px;
            font-weight: 850;
            cursor: pointer;
            white-space: nowrap;
        }

        .side-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid #EEF2F7;
            font-size: 10px;
        }

        .side-row:last-child {
            border-bottom: 0;
        }

        .side-row span {
            color: var(--muted);
        }

        .side-row strong {
            color: #334155;
            text-align: right;
        }

        .visual-note {
            padding: 12px;
            border: 1px solid #A7F3D0;
            border-radius: 10px;
            background: #ECFDF5;
            color: #047857;
            font-size: 9px;
            line-height: 1.5;
            text-align: center;
        }

        @media (max-width: 950px) {
            .layout {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 650px) {
            .page-header {
                flex-direction: column;
            }

            .info-grid,
            .permission-grid {
                grid-template-columns: 1fr;
            }

            .info-item.full {
                grid-column: auto;
            }

            .activity {
                grid-template-columns: 34px minmax(0, 1fr);
            }

            .activity-date {
                grid-column: 2;
            }

            .security-item {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    
        /* PERFIL FASE 2 */

        .profile-form {
            display: grid;
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
            gap: 13px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        .form-label {
            color: #475569;
            font-size: 10px;
            font-weight: 850;
        }

        .form-input {
            width: 100%;
            min-height: 40px;
            padding: 0 12px;
            border: 1px solid #DDE3EE;
            border-radius: 9px;
            background: white;
            color: #334155;
            font-size: 11px;
            outline: none;
        }

        .form-input:focus {
            border-color: var(--primary);
            box-shadow:
                0 0 0 3px
                rgba(5, 150, 105, .10);
        }

        .form-error {
            color: #DC2626;
            font-size: 9px;
        }

        .form-actions {
            grid-column: 1 / -1;
            display: flex;
            justify-content: flex-end;
            margin-top: 3px;
        }

        .button.primary {
            border-color: var(--primary);
            background: var(--primary);
            color: white;
        }

        .button.primary:hover {
            background: var(--primary-dark);
        }

        .empty-state {
            padding: 20px;
            border: 1px dashed #CBD5E1;
            border-radius: 10px;
            background: #F8FAFC;
            color: #64748B;
            font-size: 10px;
            line-height: 1.6;
            text-align: center;
        }

        .status-box.inactive {
            border-color: #FECACA;
            background: #FEF2F2;
            color: #B91C1C;
        }

        .readonly-note {
            margin-top: 12px;
            color: #94A3B8;
            font-size: 9px;
            line-height: 1.5;
        }

        @media (max-width: 650px) {
            .profile-form {
                grid-template-columns: 1fr;
            }

            .form-group.full,
            .form-actions {
                grid-column: auto;
            }
        }



    
        </style>

<div class="admin-profile">

        <div class="layout">

            <aside class="sidebar">

                <section class="card profile-card">

                    <div class="avatar">
                        {{ $iniciales ?: 'A' }}
                    </div>

                    <div class="profile-name">
                        {{ $nombreCompleto }}
                    </div>

                    <div class="profile-email">
                        {{ $usuario->email }}
                    </div>

                    <div class="profile-role">
                        {{
                            $usuario
                                ->role
                                ?->nombre
                            ?? 'Sin rol'
                        }}
                    </div>

                    <div
                        class="
                            status-box
                            {{
                                $usuario->estado
                                    ? ''
                                    : 'inactive'
                            }}
                        "
                    >
                        @if ($usuario->estado)

                            Cuenta activa con acceso
                            administrativo a la plataforma.

                        @else

                            Cuenta inactiva.

                        @endif
                    </div>

                </section>


                <section class="card">

                    <div class="card-header">

                        <div class="card-title">
                            Resumen de cuenta
                        </div>

                    </div>

                    <div class="card-content">

                        <div class="side-row">

                            <span>Estado</span>

                            <strong
                                style="
                                    color:{{
                                        $usuario->estado
                                            ? '#059669'
                                            : '#DC2626'
                                    }};
                                "
                            >
                                {{
                                    $usuario->estado
                                        ? 'Activo'
                                        : 'Inactivo'
                                }}
                            </strong>

                        </div>


                        <div class="side-row">

                            <span>Rol</span>

                            <strong>
                                {{
                                    $usuario
                                        ->role
                                        ?->nombre
                                    ?? 'Sin rol'
                                }}
                            </strong>

                        </div>


                        <div class="side-row">

                            <span>
                                Departamento
                            </span>

                            <strong>
                                {{
                                    $usuario
                                        ->departamento
                                        ?->nombre
                                    ?? 'Sin departamento'
                                }}
                            </strong>

                        </div>


                        <div class="side-row">

                            <span>
                                Último acceso
                            </span>

                            <strong>
                                {{
                                    $usuario
                                        ->ultimo_acceso
                                        ?->format(
                                            'd/m/Y H:i'
                                        )
                                    ?? 'Sin registro'
                                }}
                            </strong>

                        </div>

                    </div>

                </section>


                <div class="visual-note">

                    Rol, estado, departamento y permisos
                    se administran desde los módulos
                    correspondientes y no pueden
                    modificarse desde esta pantalla.

                </div>

            </aside>


            <main class="main">


                <section class="card">

                    <div class="card-header">

                        <div class="card-title">
                            Información personal
                        </div>

                        <div class="card-description">
                            Actualiza los datos principales
                            de tu cuenta.
                        </div>

                    </div>


                    <div class="card-content">

                        <form
                            class="profile-form"
                            wire:submit="
                                guardarPerfil
                            "
                        >

                            <div class="form-group">

                                <label
                                    class="form-label"
                                >
                                    Nombre
                                </label>

                                <input
                                    type="text"
                                    class="form-input"
                                    wire:model="nombre"
                                    autocomplete="given-name"
                                >

                                @error('nombre')
                                    <div
                                        class="form-error"
                                    >
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>


                            <div class="form-group">

                                <label
                                    class="form-label"
                                >
                                    Apellidos
                                </label>

                                <input
                                    type="text"
                                    class="form-input"
                                    wire:model="apellidos"
                                    autocomplete="family-name"
                                >

                                @error('apellidos')
                                    <div
                                        class="form-error"
                                    >
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>


                            <div
                                class="
                                    form-group
                                    full
                                "
                            >

                                <label
                                    class="form-label"
                                >
                                    Correo institucional
                                </label>

                                <input
                                    type="email"
                                    class="form-input"
                                    wire:model="email"
                                    autocomplete="email"
                                >

                                @error('email')
                                    <div
                                        class="form-error"
                                    >
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>


                            <div class="form-actions">

                                <button
                                    type="submit"
                                    class="
                                        button
                                        primary
                                    "
                                    wire:loading.attr="
                                        disabled
                                    "
                                    wire:target="
                                        guardarPerfil
                                    "
                                >
                                    Guardar cambios
                                </button>

                            </div>

                        </form>

                    </div>

                </section>


                <section class="card">

                    <div class="card-header">

                        <div class="card-title">
                            Accesos del Administrador
                        </div>

                        <div class="card-description">
                            Permisos reales asociados
                            actualmente a tu rol.
                        </div>

                    </div>


                    <div class="card-content">

                        @if ($permisos->isNotEmpty())

                            <div class="permission-grid">

                                @foreach (
                                    $permisos
                                    as $permiso
                                )

                                    <div
                                        class="permission"
                                    >

                                        <div
                                            class="
                                                permission-icon
                                            "
                                        >
                                            ✓
                                        </div>

                                        <div
                                            class="
                                                permission-name
                                            "
                                        >
                                            {{
                                                ucfirst(
                                                    str_replace(
                                                        '_',
                                                        ' ',
                                                        $permiso
                                                    )
                                                )
                                            }}
                                        </div>

                                    </div>

                                @endforeach

                            </div>

                        @else

                            <div class="empty-state">
                                Este rol no tiene permisos
                                registrados.
                            </div>

                        @endif

                    </div>

                </section>


                <section class="card">

                    <div class="card-header">

                        <div class="card-title">
                            Seguridad
                        </div>

                        <div class="card-description">
                            Cambia tu contraseña utilizando
                            la contraseña actual.
                        </div>

                    </div>


                    <div class="card-content">

                        <form
                            class="profile-form"
                            wire:submit="
                                cambiarPassword
                            "
                        >

                            <div
                                class="
                                    form-group
                                    full
                                "
                            >

                                <label
                                    class="form-label"
                                >
                                    Contraseña actual
                                </label>

                                <input
                                    type="password"
                                    class="form-input"
                                    wire:model="
                                        passwordActual
                                    "
                                    autocomplete="
                                        current-password
                                    "
                                >

                                @error('passwordActual')
                                    <div
                                        class="form-error"
                                    >
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>


                            <div class="form-group">

                                <label
                                    class="form-label"
                                >
                                    Nueva contraseña
                                </label>

                                <input
                                    type="password"
                                    class="form-input"
                                    wire:model="
                                        passwordNueva
                                    "
                                    autocomplete="
                                        new-password
                                    "
                                >

                                @error('passwordNueva')
                                    <div
                                        class="form-error"
                                    >
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>


                            <div class="form-group">

                                <label
                                    class="form-label"
                                >
                                    Confirmar contraseña
                                </label>

                                <input
                                    type="password"
                                    class="form-input"
                                    wire:model="
                                        passwordNuevaConfirmacion
                                    "
                                    autocomplete="
                                        new-password
                                    "
                                >

                                @error(
                                    'passwordNuevaConfirmacion'
                                )
                                    <div
                                        class="form-error"
                                    >
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>


                            <div class="form-actions">

                                <button
                                    type="submit"
                                    class="
                                        button
                                        primary
                                    "
                                    wire:loading.attr="
                                        disabled
                                    "
                                    wire:target="
                                        cambiarPassword
                                    "
                                >
                                    Cambiar contraseña
                                </button>

                            </div>

                        </form>

                    </div>

                </section>


                <section class="card">

                    <div class="card-header">

                        <div class="card-title">
                            Actividad reciente
                        </div>

                        <div class="card-description">
                            Historial de acciones
                            administrativas de la cuenta.
                        </div>

                    </div>


                    <div class="card-content">

                        <div class="empty-state">

                            Actualmente no existe una
                            bitácora de actividad de usuario
                            que permita reconstruir estas
                            acciones de forma fiable.

                            <br><br>

                            No se muestran actividades
                            ficticias.

                        </div>

                    </div>

                </section>

            </main>

        </div>

    </div>

</x-filament-panels::page>
