<x-filament-panels::page>

<style>
    .itsva-profile {
        --text: #0F1827;
        --muted: #64748B;
        --primary: #1A4B8C;
        --primary-light: #2563EB;
        --border: #DDE3EE;
        --success: #059669;
        --danger: #DC2626;

        color: var(--text);
    }

    .itsva-profile * {
        box-sizing: border-box;
    }

    .profile-heading {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
        margin-bottom: 22px;
    }

    .page-title {
        margin: 0;
        font-size: 25px;
        font-weight: 800;
    }

    .page-subtitle {
        margin-top: 7px;
        color: var(--muted);
        font-size: 13px;
    }

    .save-btn,
    .secondary-btn,
    .cancel-btn {
        min-height: 40px;
        padding: 0 14px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 11px;
        font-weight: 800;
    }

    .save-btn {
        border: 1px solid var(--primary);
        background: var(--primary);
        color: white;
    }

    .secondary-btn,
    .cancel-btn {
        border: 1px solid var(--border);
        background: white;
        color: var(--primary);
    }

    .layout {
        display: grid;
        grid-template-columns:
            290px minmax(0, 1fr);
        gap: 20px;
        align-items: start;
    }

    .card {
        background: white;
        border: 1px solid var(--border);
        border-radius: 14px;
    }

    .profile-card {
        overflow: hidden;
    }

    .profile-cover {
        height: 86px;
        background:
            linear-gradient(
                135deg,
                #1A4B8C 0%,
                #2563EB 100%
            );
    }

    .profile-content {
        padding: 0 20px 20px;
    }

    .avatar {
        width: 82px;
        height: 82px;
        margin-top: -41px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 5px solid white;
        border-radius: 50%;
        background: #E8EEF8;
        color: var(--primary);
        font-size: 24px;
        font-weight: 900;
        box-shadow:
            0 2px 7px rgba(
                15,
                24,
                39,
                .12
            );
    }

    .avatar-help {
        margin-top: 8px;
        color: #94A3B8;
        font-size: 9px;
    }

    .profile-name {
        margin-top: 15px;
        color: #1E293B;
        font-size: 17px;
        font-weight: 850;
    }

    .profile-email {
        margin-top: 4px;
        color: var(--muted);
        font-size: 10px;
        word-break: break-word;
    }

    .role-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 11px;
        padding: 5px 9px;
        border-radius: 999px;
        background: #E8EEF8;
        color: var(--primary);
        font-size: 9px;
        font-weight: 850;
    }

    .role-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--primary-light);
    }

    .profile-data {
        margin-top: 18px;
        padding-top: 16px;
        border-top: 1px solid var(--border);
    }

    .profile-data-item {
        margin-bottom: 12px;
    }

    .profile-data-label {
        display: block;
        margin-bottom: 3px;
        color: #94A3B8;
        font-size: 8px;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .profile-data-value {
        color: #334155;
        font-size: 10px;
        font-weight: 750;
    }

    .account-status {
        margin-top: 15px;
        padding: 11px;
        border-radius: 9px;
    }

    .account-status.active {
        border: 1px solid #A7F3D0;
        background: #ECFDF5;
    }

    .account-status.inactive {
        border: 1px solid #FECACA;
        background: #FEF2F2;
    }

    .status-title {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 10px;
        font-weight: 850;
    }

    .active .status-title {
        color: #065F46;
    }

    .inactive .status-title {
        color: #991B1B;
    }

    .status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
    }

    .active .status-dot {
        background: var(--success);
    }

    .inactive .status-dot {
        background: var(--danger);
    }

    .status-help {
        margin-top: 4px;
        color: #64748B;
        font-size: 8px;
        line-height: 1.4;
    }

    .main {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .section-card {
        padding: 21px;
    }

    .section-header {
        margin-bottom: 18px;
    }

    .section-title {
        color: #1E293B;
        font-size: 14px;
        font-weight: 850;
    }

    .section-description {
        margin-top: 5px;
        color: var(--muted);
        font-size: 10px;
        line-height: 1.5;
    }

    .form-grid {
        display: grid;
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .field.full {
        grid-column: 1 / -1;
    }

    .field label {
        display: block;
        margin-bottom: 6px;
        color: #334155;
        font-size: 10px;
        font-weight: 800;
    }

    .input,
    .select {
        width: 100%;
        height: 41px;
        padding: 0 11px;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        background: white;
        color: var(--text);
        font: inherit;
        font-size: 11px;
        outline: none;
    }

    .input:focus,
    .select:focus {
        border-color: var(--primary-light);
        box-shadow:
            0 0 0 3px
            rgba(37, 99, 235, .10);
    }

    .input:disabled {
        background: #F8FAFC;
        color: #64748B;
    }

    .field-help {
        margin-top: 5px;
        color: #94A3B8;
        font-size: 8px;
        line-height: 1.4;
    }

    .field-error {
        margin-top: 5px;
        color: var(--danger);
        font-size: 8px;
    }

    .info-box {
        padding: 12px;
        border: 1px solid #DBEAFE;
        border-radius: 9px;
        background: #EFF6FF;
        color: #1E40AF;
        font-size: 9px;
        line-height: 1.5;
    }

    .security-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 18px;
        padding: 14px 0;
        border-bottom: 1px solid #EEF2F7;
    }

    .security-row:last-child {
        border-bottom: 0;
    }

    .security-title {
        color: #334155;
        font-size: 10px;
        font-weight: 800;
    }

    .security-description {
        margin-top: 4px;
        color: #94A3B8;
        font-size: 8px;
        line-height: 1.45;
    }

    .password-box {
        margin-top: 14px;
        padding: 16px;
        border: 1px solid #DCE5F0;
        border-radius: 10px;
        background: #F8FAFD;
    }

    .password-actions,
    .bottom-save {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        margin-top: 15px;
    }

    .bottom-save {
        padding-top: 17px;
        border-top: 1px solid var(--border);
    }

    @media (max-width: 960px) {
        .layout {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 650px) {
        .profile-heading {
            flex-direction: column;
        }

        .save-btn {
            width: 100%;
        }

        .form-grid {
            grid-template-columns: 1fr;
        }

        .field.full {
            grid-column: auto;
        }

        .security-row {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<div class="itsva-profile">

    <div class="profile-heading">

        <div>

            <h1 class="page-title">
                Perfil de Usuario
            </h1>

            <div class="page-subtitle">
                Consulta y actualiza la información
                asociada a tu cuenta.
            </div>

        </div>

        <button
            type="button"
            class="save-btn"
            wire:click="guardarCambios"
            wire:loading.attr="disabled"
            wire:target="guardarCambios"
        >
            Guardar cambios
        </button>

    </div>

    <div class="layout">

        <aside class="card profile-card">

            <div class="profile-cover"></div>

            <div class="profile-content">

                <div class="avatar">
                    {{ $iniciales }}
                </div>

                <div class="avatar-help">
                    La cuenta no dispone actualmente
                    de un campo para fotografía.
                </div>

                <div class="profile-name">
                    {{
                        $nombreCompleto
                        !== ''
                            ? $nombreCompleto
                            : 'Usuario'
                    }}
                </div>

                <div class="profile-email">
                    {{ $usuario->email }}
                </div>

                <div class="role-badge">
                    <span class="role-dot"></span>

                    {{
                        $usuario
                            ->role
                            ?->nombre
                        ?? 'Sin rol'
                    }}
                </div>

                <div class="profile-data">

                    <div class="profile-data-item">

                        <span class="profile-data-label">
                            Departamento
                        </span>

                        <span class="profile-data-value">
                            {{
                                $usuario
                                    ->departamento
                                    ?->nombre
                                ?? 'Sin asignar'
                            }}
                        </span>

                    </div>

                    <div class="profile-data-item">

                        <span class="profile-data-label">
                            Institución
                        </span>

                        <span class="profile-data-value">
                            Instituto Tecnológico
                            Superior de Valladolid
                        </span>

                    </div>

                    <div class="profile-data-item">

                        <span class="profile-data-label">
                            Miembro desde
                        </span>

                        <span class="profile-data-value">
                            {{
                                $usuario
                                    ->created_at
                                    ?->locale('es')
                                    ->translatedFormat(
                                        'F \d\e Y'
                                    )
                                ?? 'Sin fecha'
                            }}
                        </span>

                    </div>

                    <div class="profile-data-item">

                        <span class="profile-data-label">
                            Último acceso
                        </span>

                        <span class="profile-data-value">
                            {{
                                $usuario
                                    ->ultimo_acceso
                                    ?->format(
                                        'd/m/Y H:i'
                                    )
                                ?? 'Sin registro'
                            }}
                        </span>

                    </div>

                </div>

                <div
                    class="
                        account-status
                        {{
                            $usuario->estado
                                ? 'active'
                                : 'inactive'
                        }}
                    "
                >

                    <div class="status-title">

                        <span class="status-dot"></span>

                        {{
                            $usuario->estado
                                ? 'Cuenta activa'
                                : 'Cuenta inactiva'
                        }}

                    </div>

                    <div class="status-help">

                        @if ($usuario->estado)

                            Tu cuenta está habilitada
                            para utilizar la plataforma.

                        @else

                            Tu cuenta se encuentra
                            deshabilitada.

                        @endif

                    </div>

                </div>

            </div>

        </aside>

        <main class="main">

            <section class="card section-card">

                <div class="section-header">

                    <div class="section-title">
                        Información personal
                    </div>

                    <div class="section-description">
                        Solo se muestran campos que
                        realmente existen en la cuenta.
                    </div>

                </div>

                <div class="form-grid">

                    <div class="field">

                        <label>
                            Nombre *
                        </label>

                        <input
                            type="text"
                            class="input"
                            maxlength="255"
                            wire:model="name"
                        >

                        @error('name')
                            <div class="field-error">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="field">

                        <label>
                            Apellidos
                        </label>

                        <input
                            type="text"
                            class="input"
                            maxlength="150"
                            wire:model="apellidos"
                        >

                        @error('apellidos')
                            <div class="field-error">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="field">

                        <label>
                            Correo institucional *
                        </label>

                        <input
                            type="email"
                            class="input"
                            maxlength="255"
                            wire:model="email"
                        >

                        @error('email')
                            <div class="field-error">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="field">

                        <label>
                            Departamento
                        </label>

                        <select
                            class="select"
                            wire:model="departamentoId"
                        >
                            <option value="">
                                Sin asignar
                            </option>

                            @foreach (
                                $departamentos
                                as $departamento
                            )

                                <option
                                    value="{{
                                        $departamento->id
                                    }}"
                                >
                                    {{
                                        $departamento
                                            ->nombre
                                    }}
                                </option>

                            @endforeach

                        </select>

                        @error('departamentoId')
                            <div class="field-error">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="field">

                        <label>
                            Rol
                        </label>

                        <input
                            type="text"
                            class="input"
                            value="{{
                                $usuario
                                    ->role
                                    ?->nombre
                                ?? 'Sin rol'
                            }}"
                            disabled
                        >

                        <div class="field-help">
                            El rol solo puede ser
                            administrado por un
                            Administrador.
                        </div>

                    </div>

                    <div class="field">

                        <label>
                            Estado de cuenta
                        </label>

                        <input
                            type="text"
                            class="input"
                            value="{{
                                $usuario->estado
                                    ? 'Activa'
                                    : 'Inactiva'
                            }}"
                            disabled
                        >

                        <div class="field-help">
                            El usuario no puede cambiar
                            el estado de su propia cuenta.
                        </div>

                    </div>

                </div>

                <div class="bottom-save">

                    <button
                        type="button"
                        class="cancel-btn"
                        wire:click="cancelarCambios"
                    >
                        Cancelar
                    </button>

                    <button
                        type="button"
                        class="save-btn"
                        wire:click="guardarCambios"
                        wire:loading.attr="disabled"
                        wire:target="guardarCambios"
                    >
                        Guardar cambios
                    </button>

                </div>

            </section>

            <section class="card section-card">

                <div class="section-header">

                    <div class="section-title">
                        Preferencias
                    </div>

                    <div class="section-description">
                        Configuración de avisos y
                        preferencias personales.
                    </div>

                </div>

                <div class="info-box">
                    La base de datos actual todavía
                    no contiene campos o una tabla
                    para preferencias del usuario.
                    Por ello esta sección no simula
                    configuraciones que no puedan
                    persistirse.
                </div>

            </section>

            <section class="card section-card">

                <div class="section-header">

                    <div class="section-title">
                        Seguridad y cuenta
                    </div>

                    <div class="section-description">
                        Administra la contraseña
                        utilizada para acceder a la
                        plataforma.
                    </div>

                </div>

                <div class="security-row">

                    <div>

                        <div class="security-title">
                            Contraseña
                        </div>

                        <div class="security-description">
                            Para cambiarla deberás
                            confirmar primero tu
                            contraseña actual.
                        </div>

                    </div>

                    <button
                        type="button"
                        class="secondary-btn"
                        wire:click="
                            abrirCambioPassword
                        "
                    >
                        Cambiar contraseña
                    </button>

                </div>

                <div class="security-row">

                    <div>

                        <div class="security-title">
                            Último acceso registrado
                        </div>

                        <div class="security-description">
                            {{
                                $usuario
                                    ->ultimo_acceso
                                    ?->format(
                                        'd/m/Y H:i:s'
                                    )
                                ?? 'Todavía no existe un registro de último acceso.'
                            }}
                        </div>

                    </div>

                </div>

                @if ($mostrarCambioPassword)

                    <div class="password-box">

                        <div class="form-grid">

                            <div class="field full">

                                <label>
                                    Contraseña actual *
                                </label>

                                <input
                                    type="password"
                                    class="input"
                                    autocomplete="
                                        current-password
                                    "
                                    wire:model="
                                        passwordActual
                                    "
                                >

                                @error('passwordActual')
                                    <div class="field-error">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                            <div class="field">

                                <label>
                                    Nueva contraseña *
                                </label>

                                <input
                                    type="password"
                                    class="input"
                                    autocomplete="
                                        new-password
                                    "
                                    wire:model="
                                        passwordNueva
                                    "
                                >

                                @error('passwordNueva')
                                    <div class="field-error">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                            <div class="field">

                                <label>
                                    Confirmar nueva
                                    contraseña *
                                </label>

                                <input
                                    type="password"
                                    class="input"
                                    autocomplete="
                                        new-password
                                    "
                                    wire:model="
                                        passwordNuevaConfirmacion
                                    "
                                >

                                @error(
                                    'passwordNuevaConfirmacion'
                                )
                                    <div class="field-error">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>

                        <div class="password-actions">

                            <button
                                type="button"
                                class="cancel-btn"
                                wire:click="
                                    cerrarCambioPassword
                                "
                            >
                                Cancelar
                            </button>

                            <button
                                type="button"
                                class="save-btn"
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

            </section>

        </main>

    </div>

</div>

</x-filament-panels::page>
