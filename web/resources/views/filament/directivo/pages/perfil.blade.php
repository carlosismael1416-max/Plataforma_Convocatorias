<x-filament-panels::page>
    <style>
        .directivo-profile {
            --text: #0F1827;
            --muted: #64748B;
            --primary: #7C3AED;
            --primary-dark: #6D28D9;
            --primary-soft: #F3E8FF;
            --border: #DDE3EE;
            --success: #059669;

            color: var(--text);
        }

        .directivo-profile * {
            box-sizing: border-box;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 24px;
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

        .card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: 0 1px 2px rgba(15, 24, 39, .03);
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
            background: var(--primary);
            color: white;
            font-size: 26px;
            font-weight: 900;
            box-shadow: 0 0 0 6px var(--primary-soft);
        }

        .profile-name {
            margin-top: 19px;
            color: #1E293B;
            font-size: 17px;
            font-weight: 900;
        }

        .profile-position {
            margin-top: 4px;
            color: var(--muted);
            font-size: 11px;
        }

        .profile-role {
            display: inline-flex;
            margin-top: 12px;
            padding: 6px 11px;
            border-radius: 999px;
            background: var(--primary-soft);
            color: var(--primary-dark);
            font-size: 10px;
            font-weight: 850;
        }

        .profile-separator {
            height: 1px;
            margin: 20px 0;
            background: var(--border);
        }

        .profile-info {
            text-align: left;
        }

        .info-row {
            padding: 10px 0;
            border-bottom: 1px solid #EEF2F7;
        }

        .info-row:last-child {
            border-bottom: 0;
        }

        .info-label {
            display: block;
            color: #94A3B8;
            font-size: 9px;
            text-transform: uppercase;
            font-weight: 800;
        }

        .info-value {
            display: block;
            margin-top: 4px;
            color: #475569;
            font-size: 11px;
            font-weight: 750;
        }

        .status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 4px;
            color: var(--success);
            font-size: 11px;
            font-weight: 800;
        }

        .status-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--success);
        }

        .main {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .section {
            padding: 22px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 15px;
            font-weight: 850;
        }

        .section-description {
            margin-top: 4px;
            color: var(--muted);
            font-size: 11px;
        }

        .edit-btn {
            min-height: 36px;
            padding: 0 13px;
            border: 1px solid var(--border);
            border-radius: 9px;
            background: white;
            color: var(--primary);
            font-size: 10px;
            font-weight: 850;
            cursor: pointer;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 15px;
        }

        .field.full {
            grid-column: 1 / -1;
        }

        .field label {
            display: block;
            margin-bottom: 6px;
            color: #64748B;
            font-size: 10px;
            font-weight: 800;
        }

        .input {
            width: 100%;
            height: 43px;
            padding: 0 12px;
            border: 1px solid #CBD5E1;
            border-radius: 9px;
            background: #F8FAFC;
            color: #334155;
            font: inherit;
            font-size: 12px;
            outline: none;
        }

        .input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, .10);
        }

        .permissions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .permission {
            display: flex;
            gap: 11px;
            align-items: flex-start;
            padding: 13px;
            border: 1px solid #E7ECF3;
            border-radius: 11px;
            background: #FBFCFE;
        }

        .permission-icon {
            width: 27px;
            height: 27px;
            flex: 0 0 27px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #D1FAE5;
            color: #047857;
            font-size: 10px;
            font-weight: 900;
        }

        .permission-name {
            color: #334155;
            font-size: 11px;
            font-weight: 800;
        }

        .permission-description {
            margin-top: 3px;
            color: #94A3B8;
            font-size: 9px;
            line-height: 1.45;
        }

        .security-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            padding: 14px 0;
            border-bottom: 1px solid #EEF2F7;
        }

        .security-item:first-of-type {
            padding-top: 0;
        }

        .security-item:last-child {
            padding-bottom: 0;
            border-bottom: 0;
        }

        .security-title {
            color: #334155;
            font-size: 11px;
            font-weight: 800;
        }

        .security-help {
            margin-top: 3px;
            color: #94A3B8;
            font-size: 9px;
        }

        .security-btn {
            min-height: 34px;
            padding: 0 11px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: white;
            color: #475569;
            font-size: 9px;
            font-weight: 850;
            cursor: pointer;
            white-space: nowrap;
        }

        .activity-list {
            display: flex;
            flex-direction: column;
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
            color: var(--primary);
            font-size: 10px;
            font-weight: 900;
        }

        .activity-title {
            color: #334155;
            font-size: 10px;
            font-weight: 800;
        }

        .activity-help {
            margin-top: 3px;
            color: #94A3B8;
            font-size: 9px;
        }

        .activity-date {
            color: #94A3B8;
            font-size: 9px;
            white-space: nowrap;
        }

        .visual-note {
            padding: 13px;
            border: 1px solid #DDD6FE;
            border-radius: 10px;
            background: #F5F3FF;
            color: #5B21B6;
            font-size: 10px;
            line-height: 1.5;
        }

        @media (max-width: 950px) {
            .layout {
                grid-template-columns: 1fr;
            }

            .profile-card {
                text-align: left;
            }

            .avatar {
                margin: 0;
            }
        }

        @media (max-width: 650px) {
            .page-header,
            .section-header,
            .security-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .form-grid,
            .permissions {
                grid-template-columns: 1fr;
            }

            .field.full {
                grid-column: auto;
            }

            .security-btn {
                width: 100%;
            }

            .activity {
                grid-template-columns: 34px minmax(0, 1fr);
            }

            .activity-date {
                grid-column: 2;
            }
        }
    
        .input.editable,
        .select.editable {
            background: white;
        }

        .select {
            width: 100%;
            height: 43px;
            padding: 0 12px;
            border: 1px solid #CBD5E1;
            border-radius: 9px;
            background: #F8FAFC;
            color: #334155;
            font: inherit;
            font-size: 12px;
            outline: none;
        }

        .select:focus {
            border-color: var(--primary);
            box-shadow:
                0 0 0 3px
                rgba(124, 58, 237, .10);
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 9px;
            margin-top: 18px;
        }

        .primary-btn,
        .secondary-btn {
            min-height: 38px;
            padding: 0 14px;
            border-radius: 9px;
            font-size: 10px;
            font-weight: 850;
            cursor: pointer;
        }

        .primary-btn {
            border: 1px solid var(--primary);
            background: var(--primary);
            color: white;
        }

        .secondary-btn {
            border: 1px solid var(--border);
            background: white;
            color: #475569;
        }

        .field-error {
            margin-top: 5px;
            color: #DC2626;
            font-size: 9px;
        }

        .protected-box {
            padding: 12px;
            border: 1px solid #E7ECF3;
            border-radius: 10px;
            background: #F8FAFC;
            color: #64748B;
            font-size: 9px;
            line-height: 1.5;
        }

        .password-form {
            margin-top: 17px;
            padding-top: 17px;
            border-top: 1px solid #EEF2F7;
        }

        .activity-link {
            color: inherit;
            text-decoration: none;
        }

        .activity-link:hover .activity-title {
            color: var(--primary);
        }

        .empty-activity {
            padding: 18px;
            border: 1px solid #E7ECF3;
            border-radius: 10px;
            background: #F8FAFC;
            color: #94A3B8;
            font-size: 10px;
            line-height: 1.5;
        }

        .readonly-value {
            width: 100%;
            min-height: 43px;
            display: flex;
            align-items: center;
            padding: 0 12px;
            border: 1px solid #E2E8F0;
            border-radius: 9px;
            background: #F8FAFC;
            color: #64748B;
            font-size: 12px;
        }

</style>

    <div class="directivo-profile">

        <div class="page-header">

            <div>

                <h1 class="page-title">
                    Perfil
                </h1>

                <div class="page-subtitle">
                    Consulta y administra la
                    información de tu cuenta
                    institucional.
                </div>

            </div>

            <div class="role-badge">
                <span class="role-dot"></span>
                {{
                    $usuario->role?->nombre
                    ?? 'Sin rol'
                }}
            </div>

        </div>

        <div class="layout">

            <aside>

                <section class="card profile-card">

                    <div class="avatar">
                        {{ $iniciales }}
                    </div>

                    <div class="profile-name">
                        {{ $nombreCompleto }}
                    </div>

                    <div class="profile-position">
                        Instituto Tecnológico
                        Superior de Valladolid
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
                            profile-separator
                        "
                    ></div>

                    <div class="profile-info">

                        <div class="info-row">

                            <span class="info-label">
                                Correo institucional
                            </span>

                            <span class="info-value">
                                {{ $usuario->email }}
                            </span>

                        </div>

                        <div class="info-row">

                            <span class="info-label">
                                Departamento
                            </span>

                            <span class="info-value">
                                {{
                                    $usuario
                                        ->departamento
                                        ?->nombre
                                    ?? 'Sin departamento'
                                }}
                            </span>

                        </div>

                        <div class="info-row">

                            <span class="info-label">
                                Rol
                            </span>

                            <span class="info-value">
                                {{
                                    $usuario
                                        ->role
                                        ?->nombre
                                    ?? 'Sin rol'
                                }}
                            </span>

                        </div>

                        <div class="info-row">

                            <span class="info-label">
                                Estado de la cuenta
                            </span>

                            @if ($usuario->estado)

                                <span class="status">
                                    <span
                                        class="
                                            status-dot
                                        "
                                    ></span>
                                    Activa
                                </span>

                            @else

                                <span
                                    class="info-value"
                                >
                                    Inactiva
                                </span>

                            @endif

                        </div>

                    </div>

                </section>

            </aside>

            <main class="main">

                <section class="card section">

                    <div class="section-header">

                        <div>

                            <div class="section-title">
                                Información personal
                            </div>

                            <div
                                class="
                                    section-description
                                "
                            >
                                Datos reales asociados
                                a tu cuenta.
                            </div>

                        </div>

                        @if (! $editando)

                            <button
                                type="button"
                                class="edit-btn"
                                wire:click="editar"
                            >
                                Editar información
                            </button>

                        @endif

                    </div>

                    <div class="form-grid">

                        <div class="field">

                            <label>
                                Nombre
                            </label>

                            <input
                                class="
                                    input
                                    {{
                                        $editando
                                            ? 'editable'
                                            : ''
                                    }}
                                "
                                type="text"
                                wire:model="nombre"
                                @readonly(! $editando)
                            >

                            @error('nombre')
                                <div
                                    class="
                                        field-error
                                    "
                                >
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        <div class="field">

                            <label>
                                Apellidos
                            </label>

                            <input
                                class="
                                    input
                                    {{
                                        $editando
                                            ? 'editable'
                                            : ''
                                    }}
                                "
                                type="text"
                                wire:model="apellidos"
                                @readonly(! $editando)
                            >

                            @error('apellidos')
                                <div
                                    class="
                                        field-error
                                    "
                                >
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        <div class="field">

                            <label>
                                Correo institucional
                            </label>

                            <input
                                class="
                                    input
                                    {{
                                        $editando
                                            ? 'editable'
                                            : ''
                                    }}
                                "
                                type="email"
                                wire:model="email"
                                @readonly(! $editando)
                            >

                            @error('email')
                                <div
                                    class="
                                        field-error
                                    "
                                >
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        <div class="field">

                            <label>
                                Departamento
                            </label>

                            @if ($editando)

                                <select
                                    class="
                                        select
                                        editable
                                    "
                                    wire:model="
                                        departamentoId
                                    "
                                >

                                    <option value="">
                                        Sin departamento
                                    </option>

                                    @foreach (
                                        $departamentos
                                        as $departamento
                                    )

                                        <option
                                            value="{{
                                                $departamento
                                                    ->id
                                            }}"
                                        >
                                            {{
                                                $departamento
                                                    ->nombre
                                            }}
                                        </option>

                                    @endforeach

                                </select>

                            @else

                                <div
                                    class="
                                        readonly-value
                                    "
                                >
                                    {{
                                        $usuario
                                            ->departamento
                                            ?->nombre
                                        ?? 'Sin departamento'
                                    }}
                                </div>

                            @endif

                            @error(
                                'departamentoId'
                            )
                                <div
                                    class="
                                        field-error
                                    "
                                >
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        <div class="field">

                            <label>
                                Rol
                            </label>

                            <div
                                class="
                                    readonly-value
                                "
                            >
                                {{
                                    $usuario
                                        ->role
                                        ?->nombre
                                    ?? 'Sin rol'
                                }}
                            </div>

                        </div>

                        <div class="field">

                            <label>
                                Estado
                            </label>

                            <div
                                class="
                                    readonly-value
                                "
                            >
                                {{
                                    $usuario->estado
                                        ? 'Activa'
                                        : 'Inactiva'
                                }}
                            </div>

                        </div>

                    </div>

                    @if ($editando)

                        <div class="form-actions">

                            <button
                                type="button"
                                class="
                                    secondary-btn
                                "
                                wire:click="
                                    cancelarEdicion
                                "
                            >
                                Cancelar
                            </button>

                            <button
                                type="button"
                                class="primary-btn"
                                wire:click="
                                    guardarPerfil
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

                    @endif

                </section>

                <section class="card section">

                    <div class="section-header">

                        <div>

                            <div class="section-title">
                                Accesos del perfil
                            </div>

                            <div
                                class="
                                    section-description
                                "
                            >
                                Funciones disponibles
                                dentro del panel Directivo.
                            </div>

                        </div>

                    </div>

                    <div class="permissions">

                        <div class="permission">

                            <div
                                class="
                                    permission-icon
                                "
                            >
                                ✓
                            </div>

                            <div>
                                <div
                                    class="
                                        permission-name
                                    "
                                >
                                    Consultar
                                    convocatorias
                                </div>

                                <div
                                    class="
                                        permission-description
                                    "
                                >
                                    Buscar y consultar
                                    convocatorias
                                    registradas.
                                </div>
                            </div>

                        </div>

                        <div class="permission">

                            <div
                                class="
                                    permission-icon
                                "
                            >
                                ✓
                            </div>

                            <div>
                                <div
                                    class="
                                        permission-name
                                    "
                                >
                                    Revisar
                                    convocatorias
                                </div>

                                <div
                                    class="
                                        permission-description
                                    "
                                >
                                    Consultar la cola
                                    pendiente de revisión.
                                </div>
                            </div>

                        </div>

                        <div class="permission">

                            <div
                                class="
                                    permission-icon
                                "
                            >
                                ✓
                            </div>

                            <div>
                                <div
                                    class="
                                        permission-name
                                    "
                                >
                                    Consultar
                                    estadísticas
                                </div>

                                <div
                                    class="
                                        permission-description
                                    "
                                >
                                    Visualizar indicadores
                                    institucionales.
                                </div>
                            </div>

                        </div>

                        <div class="permission">

                            <div
                                class="
                                    permission-icon
                                "
                            >
                                ✓
                            </div>

                            <div>
                                <div
                                    class="
                                        permission-name
                                    "
                                >
                                    Generar reportes
                                </div>

                                <div
                                    class="
                                        permission-description
                                    "
                                >
                                    Consultar y exportar
                                    reportes.
                                </div>
                            </div>

                        </div>

                    </div>

                    <div
                        class="protected-box"
                        style="margin-top:14px;"
                    >
                        El rol y el estado de la cuenta
                        están protegidos y no pueden
                        modificarse desde esta pantalla.
                    </div>

                </section>

                <section class="card section">

                    <div class="section-header">

                        <div>

                            <div class="section-title">
                                Seguridad
                            </div>

                            <div
                                class="
                                    section-description
                                "
                            >
                                Configuración de acceso
                                de tu cuenta.
                            </div>

                        </div>

                    </div>

                    <div class="security-item">

                        <div>

                            <div
                                class="
                                    security-title
                                "
                            >
                                Contraseña
                            </div>

                            <div
                                class="
                                    security-help
                                "
                            >
                                Actualiza la contraseña
                                utilizada para ingresar.
                            </div>

                        </div>

                        <button
                            type="button"
                            class="security-btn"
                            wire:click="
                                alternarCambioPassword
                            "
                        >
                            {{
                                $mostrarCambioPassword
                                    ? 'Cancelar'
                                    : 'Cambiar contraseña'
                            }}
                        </button>

                    </div>

                    @if ($mostrarCambioPassword)

                        <div class="password-form">

                            <div class="form-grid">

                                <div class="field full">

                                    <label>
                                        Contraseña actual
                                    </label>

                                    <input
                                        class="
                                            input
                                            editable
                                        "
                                        type="password"
                                        wire:model="
                                            contrasenaActual
                                        "
                                        autocomplete="
                                            current-password
                                        "
                                    >

                                    @error(
                                        'contrasenaActual'
                                    )
                                        <div
                                            class="
                                                field-error
                                            "
                                        >
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>

                                <div class="field">

                                    <label>
                                        Nueva contraseña
                                    </label>

                                    <input
                                        class="
                                            input
                                            editable
                                        "
                                        type="password"
                                        wire:model="
                                            nuevaContrasena
                                        "
                                        autocomplete="
                                            new-password
                                        "
                                    >

                                    @error(
                                        'nuevaContrasena'
                                    )
                                        <div
                                            class="
                                                field-error
                                            "
                                        >
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>

                                <div class="field">

                                    <label>
                                        Confirmar contraseña
                                    </label>

                                    <input
                                        class="
                                            input
                                            editable
                                        "
                                        type="password"
                                        wire:model="
                                            confirmacionContrasena
                                        "
                                        autocomplete="
                                            new-password
                                        "
                                    >

                                    @error(
                                        'confirmacionContrasena'
                                    )
                                        <div
                                            class="
                                                field-error
                                            "
                                        >
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>

                            </div>

                            <div class="form-actions">

                                <button
                                    type="button"
                                    class="primary-btn"
                                    wire:click="
                                        cambiarPassword
                                    "
                                    wire:loading.attr="
                                        disabled
                                    "
                                    wire:target="
                                        cambiarPassword
                                    "
                                >
                                    Actualizar contraseña
                                </button>

                            </div>

                        </div>

                    @endif

                    <div class="security-item">

                        <div>

                            <div
                                class="
                                    security-title
                                "
                            >
                                Último acceso
                            </div>

                            <div
                                class="
                                    security-help
                                "
                            >
                                Último acceso registrado
                                para esta cuenta.
                            </div>

                        </div>

                        <strong
                            style="
                                font-size:10px;
                                color:#475569;
                            "
                        >
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

                </section>

                <section class="card section">

                    <div class="section-header">

                        <div>

                            <div class="section-title">
                                Actividad reciente
                            </div>

                            <div
                                class="
                                    section-description
                                "
                            >
                                Actividad registrada
                                realmente para esta cuenta.
                            </div>

                        </div>

                    </div>

                    @if ($actividad->isNotEmpty())

                        <div class="activity-list">

                            @foreach (
                                $actividad
                                as $item
                            )

                                @if ($item['url'])

                                    <a
                                        href="{{
                                            $item[
                                                'url'
                                            ]
                                        }}"
                                        class="
                                            activity
                                            activity-link
                                        "
                                    >

                                @else

                                    <div class="activity">

                                @endif

                                    <div
                                        class="
                                            activity-icon
                                        "
                                    >
                                        {{
                                            $item[
                                                'tipo'
                                            ]
                                        }}
                                    </div>

                                    <div>

                                        <div
                                            class="
                                                activity-title
                                            "
                                        >
                                            {{
                                                $item[
                                                    'titulo'
                                                ]
                                            }}
                                        </div>

                                        <div
                                            class="
                                                activity-help
                                            "
                                        >
                                            {{
                                                $item[
                                                    'detalle'
                                                ]
                                            }}
                                        </div>

                                    </div>

                                    <div
                                        class="
                                            activity-date
                                        "
                                    >
                                        {{
                                            $item[
                                                'fecha'
                                            ]
                                        }}
                                    </div>

                                @if ($item['url'])
                                    </a>
                                @else
                                    </div>
                                @endif

                            @endforeach

                        </div>

                    @else

                        <div class="empty-activity">
                            No existen acciones registradas
                            para esta cuenta todavía.
                        </div>

                    @endif

                </section>

            </main>

        </div>

    </div>

</x-filament-panels::page>
